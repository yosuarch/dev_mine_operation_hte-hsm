<?php

namespace App\Controllers\PrestartInspection\Mobile;

use App\Controllers\BaseController;


class ControllerOperatorDriver extends BaseController
{
    public function index()
    {
        $data = ['pageTitle' => 'Operator-Driver | PSI'];
        return view('pages/psi/mobile/mobile-operator_driver', $data);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function sendEmail(string $toAddress, string $toName, string $subject, string $htmlBody): void
    {
        // Suppress PHP warnings (e.g. fsockopen) so CI4's error handler doesn't
        // convert them to exceptions and pollute the log with stack traces.
        $prev = set_error_handler(static fn() => true);
        try {
            $email = \Config\Services::email();
            $email->initialize(['mailType' => 'html']);
            $email->setTo("\"{$toName}\" <{$toAddress}>");
            $email->setSubject($subject);
            $email->setMessage($htmlBody);

            if (!$email->send(false)) {
                log_message('error', '[EMAIL] send failed — to: ' . $toAddress);
            }
            $email->clear();
        } catch (\Throwable $e) {
            log_message('error', '[EMAIL] exception — to: ' . $toAddress . ' — ' . $e->getMessage());
        } finally {
            set_error_handler($prev);
        }
    }

    private function sendWhatsApp(string $phone, string $text): void
    {
        $cfg = config('Waha');
        $ch  = curl_init(rtrim($cfg->baseUrl, '/') . '/api/sendText');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'session' => $cfg->session,
                'chatId'  => $phone . '@c.us',
                'text'    => $text,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 1,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Api-Key: ' . $cfg->apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);

        if ($curlErr || $httpCode < 200 || $httpCode >= 300) {
            log_message('error', "[WAHA] sendText failed — phone: {$phone}, HTTP: {$httpCode}, curl_error: {$curlErr}, response: {$response}");
        }
    }

    private function redis(): \Redis
    {
        $cfg = config('Cache')->redis;
        $r   = new \Redis();
        $r->connect($cfg['host'] ?? '127.0.0.1', (int) ($cfg['port'] ?? 6379));
        if (!empty($cfg['password'])) {
            $r->auth($cfg['password']);
        }
        if (!empty($cfg['database'])) {
            $r->select((int) $cfg['database']);
        }
        return $r;
    }

    private function redisKey(int $employeeId, int $equipIdx): string
    {
        return "psi_open_shift:{$employeeId}:{$equipIdx}";
    }

    // ── POST /operator-driver/submit-psi ─────────────────────────────────────

    public function submitPSI()
    {
        $json = $this->request->getJSON(true);
        if (!$json) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'    => 'error',
                'message'   => 'Invalid request body.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $opDrIdx        = (int)   ($json['opDrIdx']       ?? 0);
        $equipIdx       = (int)   ($json['equipIdx']       ?? 0);
        $date           =          $json['date']           ?? '';
        $shift          = (int)   ($json['shift']          ?? 0);
        $hourMeterStart = (float) ($json['hourMeterStart'] ?? -1);
        $abnormalItems  =          $json['abnormalItems']  ?? [];

        if (
            !$opDrIdx || !$equipIdx || !$date
            || !in_array($shift, [1, 2], true) || $hourMeterStart < 0
        ) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Missing or invalid required fields.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db = \Config\Database::connect();

        // Validate operator exists and get employee_id for Redis key
        $operator = $db->table('mp_list')
            ->select('idx, employee_id, name')
            ->where('idx', $opDrIdx)
            ->get()->getRow();

        if (!$operator) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Operator not found.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $employeeId = (int) $operator->employee_id;

        // Always record session to wh_recording — covers all-normal shifts too
        $db->query(
            "INSERT INTO wh_recording (equipment_id, date, shift, operator_name, hourmeter_start, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE modified_at = NOW()",
            [$equipIdx, $date, $shift, $opDrIdx, $hourMeterStart]
        );

        // Upsert abnormal items into psi_record only when they exist
        if (!empty($abnormalItems)) {
            $checkPartTexts = array_values(array_unique(
                array_filter(array_map(fn($i) => trim($i['check_part'] ?? ''), $abnormalItems))
            ));

            $textToPartId = [];
            if (!empty($checkPartTexts)) {
                $placeholders = implode(',', array_fill(0, count($checkPartTexts), '?'));
                $lookupRows   = $db->query(
                    "SELECT DISTINCT UCASE(REPLACE(puoi.checking_part_idn,'_',' ')) AS text_name,
                            puoi.idx AS part_id
                     FROM psi_observed_by_unit_type AS pobu
                     INNER JOIN psi_unique_observed_item AS puoi ON pobu.checking_part = puoi.idx
                     WHERE UCASE(REPLACE(puoi.checking_part_idn,'_',' ')) IN ({$placeholders})",
                    $checkPartTexts
                )->getResultArray();

                foreach ($lookupRows as $row) {
                    $textToPartId[$row['text_name']] = (int) $row['part_id'];
                }
            }

            $rows = [];
            foreach ($abnormalItems as $item) {
                $checkPartText = trim($item['check_part'] ?? '');
                $itemNote      = trim($item['note']       ?? '');
                $partId        = $textToPartId[$checkPartText] ?? null;

                if (!$partId || !$itemNote) continue;

                $rows[] = [
                    'equipment_id'    => $equipIdx,
                    'date'            => $date,
                    'shift'           => $shift,
                    'operator_name'   => $opDrIdx,
                    'hourmeter_start' => $hourMeterStart,
                    'checking_part'   => $partId,
                    'checking_status' => 1,
                    'checking_note'   => $itemNote,
                ];
            }

            if (!empty($rows)) {
                $placeholders = implode(', ', array_fill(0, count($rows), '(?, ?, ?, ?, ?, ?, ?, ?)'));
                $bindings = [];
                foreach ($rows as $row) {
                    $bindings[] = $row['equipment_id'];
                    $bindings[] = $row['date'];
                    $bindings[] = $row['shift'];
                    $bindings[] = $row['operator_name'];
                    $bindings[] = $row['hourmeter_start'];
                    $bindings[] = $row['checking_part'];
                    $bindings[] = $row['checking_status'];
                    $bindings[] = $row['checking_note'];
                }

                $db->transStart();
                $db->query(
                    "INSERT INTO psi_record
                        (equipment_id, date, shift, operator_name, hourmeter_start, checking_part, checking_status, checking_note)
                     VALUES {$placeholders}
                     ON DUPLICATE KEY UPDATE
                        checking_status = VALUES(checking_status),
                        checking_note   = VALUES(checking_note),
                        modified_at     = NOW()",
                    $bindings
                );
                $db->transComplete();

                if (!$db->transStatus()) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'status'    => 'error',
                        'message'   => 'Database error. Please try again.',
                        'csrf_hash' => csrf_hash(),
                    ]);
                }
            }
        }

        // Always write Redis so HM End can be recorded at shift end
        $redis = $this->redis();
        $redis->setex(
            $this->redisKey($employeeId, $equipIdx),
            72000,
            json_encode([
                'operator_idx'    => $opDrIdx,
                'employee_id'     => $employeeId,
                'equipment_idx'   => $equipIdx,
                'date'            => $date,
                'shift'           => $shift,
                'hourmeter_start' => $hourMeterStart,
            ])
        );

        // Notifications — synchronous, log failures but never block submit
        $recipients = $db->table('notification_target')
            ->where('active', 1)
            ->where('deleted_at IS NULL', null, false)
            ->get()->getResultArray();

        if (!empty($recipients)) {
            $equipRow = $db->table('equipment_register')
                ->select('CONCAT(text_code, num_code) AS label', false)
                ->where('idx', $equipIdx)
                ->get()->getRow();

            $hasAbnormal  = !empty($abnormalItems);
            $shiftLabel   = $shift === 1 ? 'Day' : 'Night';
            $operatorName = $operator->name ?? "Operator #{$opDrIdx}";
            $equipLabel   = $equipRow ? $equipRow->label : "Unit #{$equipIdx}";

            $subject = '[P2H] ' . $equipLabel . ' — ' . $date . ' | Shift ' . $shiftLabel
                     . ($hasAbnormal ? ' — ' . count($abnormalItems) . ' item tidak normal' : '');

            $statusBlock = $hasAbnormal
                ? '<p style="color:#dc3545;font-weight:bold;margin:20px 0 8px;">&#9888;&#65039; ' . count($abnormalItems) . ' item tidak normal:</p>'
                  . '<ul style="margin:0;padding-left:20px;color:#333;">'
                  . implode('', array_map(fn($i) =>
                      '<li style="margin-bottom:4px;"><strong>' . htmlspecialchars(trim($i['check_part'] ?? '')) . '</strong>'
                      . ' &mdash; &ldquo;' . htmlspecialchars(trim($i['note'] ?? '')) . '&rdquo;</li>',
                      $abnormalItems))
                  . '</ul>'
                : '<p style="color:#198754;margin-top:20px;">&#9989; Semua item normal.</p>';

            $htmlBody = '
            <div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#333;">
                <h2 style="margin:0 0 4px;">P2H Submitted</h2>
                <p style="margin:0 0 20px;color:#888;font-size:13px;">Pre-Start Inspection Record</p>
                <table style="width:100%;border-collapse:collapse;">
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 0;color:#888;width:120px;">Operator</td>
                        <td style="padding:10px 0;font-weight:bold;">' . htmlspecialchars($operatorName) . '</td>
                    </tr>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 0;color:#888;">Unit</td>
                        <td style="padding:10px 0;font-weight:bold;">' . htmlspecialchars($equipLabel) . '</td>
                    </tr>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 0;color:#888;">Tanggal</td>
                        <td style="padding:10px 0;">' . htmlspecialchars($date) . ' &middot; Shift ' . $shiftLabel . '</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#888;">HM Start</td>
                        <td style="padding:10px 0;">' . $hourMeterStart . '</td>
                    </tr>
                </table>
                ' . $statusBlock . '
            </div>';

            // Plain text for WhatsApp (future official API)
            $plainMsg  = "[P2H SUBMITTED]\nOperator : {$operatorName}\nUnit     : {$equipLabel}\n"
                       . "Tanggal  : {$date} | Shift {$shiftLabel}\nHM Start : {$hourMeterStart}\n\n";
            $plainMsg .= $hasAbnormal
                ? implode('', array_map(fn($i) => '• ' . trim($i['check_part'] ?? '') . ' — "' . trim($i['note'] ?? '') . '"' . "\n", $abnormalItems))
                : '✅ Semua item normal.';

            foreach ($recipients as $r) {
                if ($r['notify_on'] === 'abnormal_only' && !$hasAbnormal) continue;

                if ($r['channel'] === 'email') {
                    $this->sendEmail($r['contact'], $r['name'], $subject, $htmlBody);
                } elseif ($r['channel'] === 'whatsapp') {
                    $this->sendWhatsApp($r['contact'], $plainMsg);
                }
            }
        }

        $message = !empty($abnormalItems)
            ? 'P2H submitted successfully.'
            : 'P2H submitted. All items normal — working hours recorded.';

        return $this->response->setJSON([
            'status'    => 'ok',
            'message'   => $message,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // ── GET /operator-driver/check-open-shift ────────────────────────────────

    public function checkOpenShift()
    {
        $employeeId = (int) $this->request->getGet('employee_id');
        if (!$employeeId) {
            return $this->response->setJSON(['openShifts' => [], 'csrf_hash' => csrf_hash()]);
        }

        $redis   = $this->redis();
        $pattern = "psi_open_shift:{$employeeId}:*";

        // Scan Redis for all open shifts belonging to this employee
        $rawKeys = [];
        $iterator = null;
        do {
            $batch = $redis->scan($iterator, $pattern, 100);
            if ($batch !== false) {
                $rawKeys = array_merge($rawKeys, $batch);
            }
        } while ($iterator != 0);

        if (empty($rawKeys)) {
            return $this->response->setJSON(['openShifts' => [], 'csrf_hash' => csrf_hash()]);
        }

        // Batch-fetch values
        $sessions = [];
        foreach ($rawKeys as $key) {
            $val = $redis->get($key);
            if ($val) {
                $sessions[] = json_decode($val, true);
            }
        }

        // Batch-lookup equipment labels
        $equipIds = array_unique(array_column($sessions, 'equipment_idx'));
        $equipMap = [];
        if (!empty($equipIds)) {
            $placeholders = implode(',', array_fill(0, count($equipIds), '?'));
            $rows = \Config\Database::connect()->query(
                "SELECT idx, CONCAT(text_code, num_code) AS label
                 FROM equipment_register WHERE idx IN ({$placeholders})",
                $equipIds
            )->getResultArray();
            foreach ($rows as $row) {
                $equipMap[(int)$row['idx']] = $row['label'];
            }
        }

        $openShifts = [];
        foreach ($sessions as $s) {
            $s['equipment_label'] = $equipMap[$s['equipment_idx']] ?? 'Unknown';
            $openShifts[] = $s;
        }

        return $this->response->setJSON([
            'openShifts' => $openShifts,
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    // ── POST /operator-driver/submit-hm-end ──────────────────────────────────

    public function submitHmEnd()
    {
        $json = $this->request->getJSON(true);
        if (!$json) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'    => 'error',
                'message'   => 'Invalid request body.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $employeeId = (int)   ($json['employee_id']  ?? 0);
        $equipIdx   = (int)   ($json['equipment_idx'] ?? 0);
        $hmEnd      = (float) ($json['hourmeter_end'] ?? -1);

        if (!$employeeId || !$equipIdx || $hmEnd < 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Missing required fields.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        // Read session from Redis
        $redis      = $this->redis();
        $redisKey   = $this->redisKey($employeeId, $equipIdx);
        $sessionVal = $redis->get($redisKey);

        if (!$sessionVal) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'    => 'error',
                'message'   => 'No open shift found. It may have already been closed.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $s = json_decode($sessionVal, true);

        if ($hmEnd <= $s['hourmeter_start']) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Hour-meter end must be greater than start (' . $s['hourmeter_start'] . ').',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db = \Config\Database::connect();

        // Guard: wh_recording is the source of truth — exists for all sessions including all-normal
        $whOpen = $db->table('wh_recording')
            ->where('operator_name',   $s['operator_idx'])
            ->where('equipment_id',    $s['equipment_idx'])
            ->where('date',            $s['date'])
            ->where('shift',           $s['shift'])
            ->where('hourmeter_start', $s['hourmeter_start'])
            ->where('hourmeter_end IS NULL', null, false)
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        if ($whOpen === 0) {
            $redis->del($redisKey);
            return $this->response->setStatusCode(404)->setJSON([
                'status'    => 'error',
                'message'   => 'Shift already closed or not found.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        // Check if abnormal psi_record rows exist for this session
        $psiOpen = $db->table('psi_record')
            ->where('operator_name',   $s['operator_idx'])
            ->where('equipment_id',    $s['equipment_idx'])
            ->where('date',            $s['date'])
            ->where('shift',           $s['shift'])
            ->where('hourmeter_start', $s['hourmeter_start'])
            ->where('hourmeter_end IS NULL', null, false)
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // Always close wh_recording
        $db->table('wh_recording')
            ->where('operator_name',   $s['operator_idx'])
            ->where('equipment_id',    $s['equipment_idx'])
            ->where('date',            $s['date'])
            ->where('shift',           $s['shift'])
            ->where('hourmeter_start', $s['hourmeter_start'])
            ->where('hourmeter_end IS NULL', null, false)
            ->where('deleted_at IS NULL', null, false)
            ->update(['hourmeter_end' => $hmEnd, 'modified_at' => $now]);

        // Close psi_record only if abnormal rows exist
        if ($psiOpen > 0) {
            $db->table('psi_record')
                ->where('operator_name',   $s['operator_idx'])
                ->where('equipment_id',    $s['equipment_idx'])
                ->where('date',            $s['date'])
                ->where('shift',           $s['shift'])
                ->where('hourmeter_start', $s['hourmeter_start'])
                ->where('hourmeter_end IS NULL', null, false)
                ->where('deleted_at IS NULL', null, false)
                ->update(['hourmeter_end' => $hmEnd, 'modified_at' => $now]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'    => 'error',
                'message'   => 'Database error. Please try again.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        // Clear Redis session
        $redis->del($redisKey);

        return $this->response->setJSON([
            'status'    => 'ok',
            'message'   => 'Shift closed successfully.',
            'csrf_hash' => csrf_hash(),
        ]);
    }
}
