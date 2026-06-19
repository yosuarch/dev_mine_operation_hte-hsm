<?php

namespace App\Controllers\PrestartInspection\Mobile;

use App\Controllers\BaseController;
use App\Models\PrestartInspection\ModelPsiRecord;


class ControllerOperatorDriver extends BaseController
{
    public function index()
    {
        $data = ['pageTitle' => 'Operator-Driver | PSI'];
        return view('pages/psi/mobile/mobile-operator_driver', $data);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

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
            ->select('idx, employee_id')
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

        // All items normal — nothing to insert, no Redis entry needed
        if (empty($abnormalItems)) {
            return $this->response->setJSON([
                'status'    => 'ok',
                'message'   => 'P2H submitted. All items normal — no issues recorded.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        // Resolve check_part text → psi_unique_observed_item.idx
        $checkPartTexts = array_values(array_unique(
            array_filter(array_map(fn($i) => trim($i['check_part'] ?? ''), $abnormalItems))
        ));

        $textToPartId = [];
        if (!empty($checkPartTexts)) {
            $placeholders = implode(',', array_fill(0, count($checkPartTexts), '?'));
            $lookupRows   = $db->query(
                "SELECT DISTINCT UCASE(REPLACE(puoi.checking_part,'_',' ')) AS text_name,
                        puoi.idx AS part_id
                 FROM psi_observed_by_unit_type AS pobu
                 INNER JOIN psi_unique_observed_item AS puoi ON pobu.checking_part = puoi.idx
                 WHERE UCASE(REPLACE(puoi.checking_part,'_',' ')) IN ({$placeholders})",
                $checkPartTexts
            )->getResultArray();

            foreach ($lookupRows as $row) {
                $textToPartId[$row['text_name']] = (int) $row['part_id'];
            }
        }

        // Build batch rows
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

        if (empty($rows)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Could not resolve any checking parts from submitted data.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db->transStart();
        (new ModelPsiRecord())->insertBatch($rows);
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'    => 'error',
                'message'   => 'Database error. Please try again.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        // Write open-shift session to Redis — TTL 72000 s (20 h)
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

        return $this->response->setJSON([
            'status'    => 'ok',
            'message'   => 'P2H submitted successfully.',
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

        // Guard: verify rows still open
        $count = $db->table('psi_record')
            ->where('operator_name',   $s['operator_idx'])
            ->where('equipment_id',    $s['equipment_idx'])
            ->where('date',            $s['date'])
            ->where('shift',           $s['shift'])
            ->where('hourmeter_start', $s['hourmeter_start'])
            ->where('hourmeter_end IS NULL', null, false)
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        if ($count === 0) {
            $redis->del($redisKey);
            return $this->response->setStatusCode(404)->setJSON([
                'status'    => 'error',
                'message'   => 'Records already closed or not found.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        // Bulk UPDATE — all rows for this session get hourmeter_end
        $db->transStart();
        $db->table('psi_record')
            ->where('operator_name',   $s['operator_idx'])
            ->where('equipment_id',    $s['equipment_idx'])
            ->where('date',            $s['date'])
            ->where('shift',           $s['shift'])
            ->where('hourmeter_start', $s['hourmeter_start'])
            ->where('hourmeter_end IS NULL', null, false)
            ->where('deleted_at IS NULL', null, false)
            ->update([
                'hourmeter_end' => $hmEnd,
                'modified_at'   => date('Y-m-d H:i:s'),
            ]);
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
