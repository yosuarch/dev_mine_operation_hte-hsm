<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\PrestartInspection\ModelPsiRecord;

class UploadPSIRecord extends BaseController
{
    public function index()
    {
        $file = $this->request->getFile('psiRecording');

        if (!$file->isValid() || $file->hasMoved()) {
            return "Error while uploading file";
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);
        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newName;

        // Load spreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $db = \Config\Database::connect();
        $model = new ModelPsiRecord();

        // 1. Ambil fingerprint dari DB untuk validasi unik (Kombinasi 6 kolom)
        $existingRecords = $model->select("CONCAT(equipment_id, '-', shift, '-', operator_name, '-', hourmeter_start, '-', hourmeter_end, '-', checking_part) as fingerprint")
            ->findAll();
        $existingFingerprints = array_column($existingRecords, 'fingerprint');

        // Master Data Lookup
        $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
        $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

        $inserted = 0;
        $skipped = 0;
        $errors = [];
        $batchData = [];
        $tracker = []; // Mencegah duplikat di dalam satu file Excel

        // 2. Iterasi untuk validasi & mapping
        foreach ($sheetData as $index => $row) {
            if ($index == 1) continue;

            // Date Parsing
            $rawDate = $row['C'];
            $dateFormated = null;
            if (is_numeric($rawDate)) {
                $dateFormated = Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
            } else {
                $timestamp = strtotime($rawDate);
                $dateFormated = $timestamp ? date('Y-m-d', $timestamp) : null;
            }

            if (!$dateFormated) {
                $skipped++;
                $errors[] = "Row $index: Invalid date format ('{$rawDate}')";
                continue;
            }

            // Lookup matching
            $missing = [];
            $equipIdx = $equipList[mb_strtolower($row['B'])] ?? null;
            $shiftIdx = $shiftList[mb_strtolower($row['D'])] ?? null;
            $opIdx    = $mpList[mb_strtolower($row['E'])] ?? null;
            $partIdx  = $partList[mb_strtolower($row['H'])] ?? null;

            if (!$equipIdx) $missing[] = "Equipment ('{$row['B']}')";
            if (!$shiftIdx) $missing[] = "Shift ('{$row['D']}')";
            if (!$opIdx)    $missing[] = "Operator ('{$row['E']}')";
            if (!$partIdx)  $missing[] = "Part ('{$row['H']}')";

            if (!empty($missing)) {
                $skipped++;
                $errors[] = "Row $index: Reference not found -> " . implode(', ', $missing);
                continue;
            }

            // 3. Cek Fingerprint (Unique constraint logic)
            $hmStart = is_numeric($row['F']) ? (int)$row['F'] : 0;
            $hmEnd   = is_numeric($row['G']) ? (int)$row['G'] : 0;
            $currentFingerprint = "{$equipIdx}-{$shiftIdx}-{$opIdx}-{$hmStart}-{$hmEnd}-{$partIdx}";

            if (in_array($currentFingerprint, $existingFingerprints) || isset($tracker[$currentFingerprint])) {
                $skipped++;
                $errors[] = "Row $index: Duplicate record skipped (Equipment: {$row['B']}, HM Start: $hmStart)";
                continue;
            }

            // Add to batch
            $tracker[$currentFingerprint] = true;
            $batchData[] = [
                'equipment_id'    => (int)$equipIdx,
                'date'            => $dateFormated,
                'shift'           => (int)$shiftIdx,
                'operator_name'   => (int)$opIdx,
                'hourmeter_start' => $hmStart,
                'hourmeter_end'   => $hmEnd,
                'checking_part'   => (int)$partIdx,
                'checking_status' => (strcasecmp($row['I'], 'TRUE') === 0) ? 1 : 0,
                'checking_note'   => !empty($row['J']) ? (string)$row['J'] : '',
            ];
        }

        // 4. Batch Insert (Atomic Operation)
        if (!empty($batchData)) {
            $db->transStart();
            $model->insertBatch($batchData);
            $db->transComplete();

            if ($db->transStatus() === false) {
                $errors[] = "Database error: Failed to save data.";
            } else {
                $inserted = count($batchData);
            }
        }

        unlink($filePath);
        session()->setFlashdata('inserted', $inserted);
        session()->setFlashdata('skipped', $skipped);
        session()->setFlashdata('errors', $errors);

        return redirect()->back();
    }
}
