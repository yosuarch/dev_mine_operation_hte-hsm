<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UploadPSIRecord extends BaseController
{
    public function index()
    {
        // 1. Resource and Timeout Management
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $file = $this->request->getFile('psiRecording');
        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Invalid file upload.');
        }

        $filePath = $file->getTempName();
        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $db = \Config\Database::connect();

        // Cleanup old logs (Garbage Collector)
        $db->query("DELETE FROM psi_record_upload_results WHERE created_at < NOW() - INTERVAL 1 HOUR");

        // Pre-load reference lists
        $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
        $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

        $summary = ['total' => 0, 'inserted' => 0, 'updated' => 0, 'errors' => []];

        $db->transBegin();
        try {
            foreach ($sheetData as $index => $row) {
                if ($index == 1) continue;
                $summary['total']++;

                // Date Parsing
                // Inside your foreach loop:
                $cell = $spreadsheet->getActiveSheet()->getCell('C' . $index);
                $cellValue = $cell->getValue();
                $dateFormated = null;

                if (is_numeric($cellValue) && $cellValue > 25569) {
                    // Correct way: parse to object, THEN format to string immediately
                    $dateFormated = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cellValue)->format('Y-m-d');
                } elseif (!empty($cellValue)) {
                    // If it's a string, try to parse it
                    $d = \DateTime::createFromFormat('d/m/Y', $cellValue) ?:
                        \DateTime::createFromFormat('m/d/Y', $cellValue) ?:
                        \DateTime::createFromFormat('Y-m-d', $cellValue);

                    $dateFormated = $d ? $d->format('Y-m-d') : null;
                }

                // Ensure we have a string before adding to array
                if (!$dateFormated) {
                    $summary['errors'][] = ['row' => $index, 'reason' => 'Invalid date format: ' . $cellValue];
                    continue;
                }

                $equipIdx = $equipList[mb_strtolower($row['B'])] ?? null;
                $shiftIdx = $shiftList[mb_strtolower($row['D'])] ?? null;
                $opIdx    = $mpList[mb_strtolower($row['E'])] ?? null;
                $partIdx  = $partList[mb_strtolower($row['H'])] ?? null;

                if (!$equipIdx || !$shiftIdx || !$opIdx || !$partIdx || !$dateFormated) {
                    $summary['errors'][] = ['row' => $index, 'reason' => 'Missing reference data or invalid date.'];
                    continue;
                }

                $data = [
                    'equipment_id'    => (int)$equipIdx,
                    'date'            => $dateFormated,
                    'shift'           => (int)$shiftIdx,
                    'operator_name'   => (int)$opIdx,
                    'hourmeter_start' => is_numeric($row['F']) ? (float)$row['F'] : 0,
                    'hourmeter_end'   => is_numeric($row['G']) ? (float)$row['G'] : 0,
                    'checking_part'   => (int)$partIdx,
                    'checking_status' => (strcasecmp($row['I'] ?? '', 'TRUE') === 0) ? 1 : 0,
                    'checking_note'   => !empty($row['J']) ? (string)$row['J'] : 'no issue',
                    'modified_at'     => date('Y-m-d H:i:s')
                ];

                $db->table('psi_record')->upsert($data);
                $summary['inserted']++;
            }
            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Critical DB Error: ' . $e->getMessage());
        }

        // Store result in DB, pass only the ID to the session
        $db->table('psi_record_upload_results')->insert(['summary_json' => json_encode($summary)]);
        $logId = $db->insertID();

        return redirect()->back()->with('result_id', $logId);
    }
}
