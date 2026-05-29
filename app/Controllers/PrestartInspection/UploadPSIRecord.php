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

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newName;

            // Load spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // Connect database & prepare reference lookups
            $db = \Config\Database::connect();
            $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
            $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
            $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
            $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

            $model = new ModelPsiRecord();
            $inserted = 0;
            $skipped = 0;
            $errors = [];
            $batchData = [];

            // 1. Iterasi untuk validasi & mapping
            foreach ($sheetData as $index => $row) {
                if ($index == 1) continue; // Skip header

                // Date Parsing
                $rawDate = $row['C'];
                $dateFormated = null;
                if (is_numeric($rawDate)) {
                    $dateFormated = Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                } else {
                    $timestamp = strtotime($rawDate);
                    if ($timestamp) {
                        $dateFormated = date('Y-m-d', $timestamp);
                    } else {
                        $errors[] = "Row $index: Format tanggal tidak valid ('{$rawDate}')";
                        $skipped++;
                        continue;
                    }
                }

                // Lookup matching
                $missing = [];
                $equipIdx = $equipList[mb_strtolower($row['B'])] ?? null;
                if (!$equipIdx) $missing[] = "Equipment ('{$row['B']}')";

                $shiftIdx = $shiftList[mb_strtolower($row['D'])] ?? null;
                if (!$shiftIdx) $missing[] = "Shift ('{$row['D']}')";

                $opIdx = $mpList[mb_strtolower($row['E'])] ?? null;
                if (!$opIdx) $missing[] = "Operator ('{$row['E']}')";

                $partIdx = $partList[mb_strtolower($row['H'])] ?? null;
                if (!$partIdx) $missing[] = "Part ('{$row['H']}')";

                if (!empty($missing)) {
                    $skipped++;
                    $errors[] = "Row $index: Tidak ditemukan di database -> " . implode(', ', $missing);
                    continue;
                }

                // Add to batch array
                $batchData[] = [
                    'equipment_id'    => (int)$equipIdx,
                    'date'            => $dateFormated,
                    'shift'           => (int)$shiftIdx,
                    'operator_name'   => (int)$opIdx,
                    'hourmeter_start' => is_numeric($row['F']) ? (int)$row['F'] : 0,
                    'hourmeter_end'   => is_numeric($row['G']) ? (int)$row['G'] : 0,
                    'checking_part'   => (int)$partIdx,
                    'checking_status' => (strcasecmp($row['I'], 'TRUE') === 0) ? 1 : 0,
                    'checking_note'   => !empty($row['J']) ? (string)$row['J'] : '',
                ];
            }

            // 2. Database Operation (Batch Insert in Transaction)
            if (!empty($batchData)) {
                $db->transStart();
                $model->insertBatch($batchData);
                $db->transComplete();

                if ($db->transStatus() === false) {
                    $errors[] = "Gagal menyimpan data ke database.";
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

        return "Error while uploading file";
    }
}
