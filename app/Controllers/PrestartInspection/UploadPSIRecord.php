<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UploadPSIRecord extends BaseController
{
    public function index()
    {
        // 1. Optimize PHP environment for processing
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
        $builder = $db->table('psi_record');

        // Pre-load reference lists to memory for O(1) lookups
        $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
        $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

        $summary = ['total' => 0, 'inserted' => 0, 'updated' => 0, 'errors' => []];

        $db->transBegin();
        try {
            foreach ($sheetData as $index => $row) {
                if ($index == 1) continue; // Skip header
                $summary['total']++;

                // Date parsing
                $rawDate = $row['C'];
                $dateFormated = is_numeric($rawDate) ? Date::excelToDateTimeObject($rawDate)->format('Y-m-d') : (date('Y-m-d', strtotime($rawDate)) ?: null);

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

                // Upsert logic relies on your UNIQUE index in the database
                // Using Query Builder's upsert handles the check-then-update/insert
                $builder->upsert($data);

                // Track stats (upsert doesn't return affected rows easily in all drivers)
                // For a more precise count, check if record exists first, but upsert is faster
                $summary['inserted']++;
            }

            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }

        return redirect()->back()->with('summary', $summary);
    }
}
