<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\PrestartInspection\ModelPsiRecord;
use CodeIgniter\HTTP\ResponseInterface;

class UploadPSIRecord extends BaseController
{
    public function index()
    {
        // prepare the file -> fetch it first
        $file = $this->request->getFile('psiRecording');

        // iterating the file for upload
        if ($file->isValid() && !$file->hasMoved()) {
            // prepare the temporary location
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newName;

            // load the spreadsheet libs for extracting data
            $spreasheet = IOFactory::load($filePath);
            $sheetData = $spreasheet->getActiveSheet()->toArray(null, true, true, true);

            // connect to database
            $db = \Config\Database::connect();

            $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
            $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
            $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
            $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

            // prepare to insert, extracting data for each row
            $model = new ModelPsiRecord();

            $inserted = 0;
            $skipped = 0;
            $errors = [];

            foreach ($sheetData as $index => $row) {
                // extracting the data from excel file
                if ($index == 1) continue;

                // date formatting
                $rawDate = $row['C'];
                $dateFormated = null;

                if (is_numeric($rawDate)) {
                    // Jika data adalah angka (format tanggal Excel standar)
                    $dateFormated = Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                } else {
                    // Jika data adalah teks, coba parse manual atau beri error
                    // Contoh: menganggap format teks adalah 'Y-m-d' atau 'd/m/Y'
                    $timestamp = strtotime($rawDate);
                    if ($timestamp) {
                        $dateFormated = date('Y-m-d', $timestamp);
                    } else {
                        $errors[] = "Row $index: Format tanggal tidak valid ('{$rawDate}')";
                        continue; // Lewati baris ini jika tanggal tidak bisa dibaca
                    }
                }

                // error data array
                $missing = [];

                // transforming the equipment_id
                // 1. search the result at memory
                // validate the result per row
                $equipIdx = $equipList[mb_strtolower($row['B'])] ?? null;
                if (!$equipIdx) $missing[] = "Equipment Code ('{$row['B']}')";

                $shiftIdx = $shiftList[mb_strtolower($row['D'])] ?? null;
                if (!$shiftIdx) $missing[] = "Shift ('{$row['D']}')";

                $opIdx    = $mpList[mb_strtolower($row['E'])] ?? null;
                if (!$opIdx)    $missing[] = "Operator ('{$row['E']}')";

                $partIdx  = $partList[mb_strtolower($row['H'])] ?? null;
                if (!$partIdx)  $missing[] = "Check Part ('{$row['H']}')";

                // no regrence then record the data
                if (!empty($missing)) {
                    $skipped++;
                    $errors[] = "Row $index: Tidak ditemukan di database -> " . implode(', ', $missing);
                    continue;
                }

                $data = [
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

                if ($model->insert($data)) {
                    $inserted++;
                } else {
                    $errors[] = "Row $index: Database error: " . implode(', ', $model->errors());
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
