<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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

        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $db = \Config\Database::connect();

        // 1. Master Data Lookup
        $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
        $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

        $batchData = [];
        $errors = [];

        // 2. Mapping
        foreach ($sheetData as $index => $row) {
            if ($index == 1) continue;

            $rawDate = $row['C'];
            $dateFormated = is_numeric($rawDate) ? Date::excelToDateTimeObject($rawDate)->format('Y-m-d') : (date('Y-m-d', strtotime($rawDate)) ?: null);

            $equipIdx = $equipList[mb_strtolower($row['B'])] ?? null;
            $shiftIdx = $shiftList[mb_strtolower($row['D'])] ?? null;
            $opIdx    = $mpList[mb_strtolower($row['E'])] ?? null;
            $partIdx  = $partList[mb_strtolower($row['H'])] ?? null;

            if (!$equipIdx || !$shiftIdx || !$opIdx || !$partIdx || !$dateFormated) {
                $errors[] = "Row $index: Missing reference data or invalid date.";
                continue;
            }

            $batchData[] = [
                'equipment_id'    => (int)$equipIdx,
                'date'            => $dateFormated,
                'shift'           => (int)$shiftIdx,
                'operator_name'   => (int)$opIdx,
                'hourmeter_start' => is_numeric($row['F']) ? (float)$row['F'] : 0,
                'hourmeter_end'   => is_numeric($row['G']) ? (float)$row['G'] : 0,
                'checking_part'   => (int)$partIdx,
                'checking_status' => (strcasecmp($row['I'], 'TRUE') === 0) ? 1 : 0,
                'checking_note'   => !empty($row['J']) ? (string)$row['J'] : 'no issue',
            ];
        }

        // 3. Perform Batch Upsert
        $totalRows = count($batchData);
        $upserted = ($totalRows > 0) ? $this->performUpsert($db, $batchData) : 0;

        unlink($filePath);

        session()->setFlashdata('message', "Processing complete. Total rows handled: $totalRows. Database changes: $upserted");
        session()->setFlashdata('errors', $errors);

        return redirect()->back();
    }

    private function performUpsert($db, $batchData)
    {
        $actualAffected = 0;
        $chunks = array_chunk($batchData, 100);

        foreach ($chunks as $chunk) {
            $sql = "INSERT INTO psi_record (equipment_id, date, shift, operator_name, hourmeter_start, hourmeter_end, checking_part, checking_status, checking_note) VALUES ";

            $values = [];
            foreach ($chunk as $row) {
                $values[] = sprintf(
                    "(%d, '%s', %d, %d, %.2f, %.2f, %d, %d, '%s')",
                    $row['equipment_id'],
                    $row['date'],
                    $row['shift'],
                    $row['operator_name'],
                    $row['hourmeter_start'],
                    $row['hourmeter_end'],
                    $row['checking_part'],
                    $row['checking_status'],
                    $db->escapeString($row['checking_note'])
                );
            }
            $sql .= implode(',', $values);

            // UPSERT Syntax
            $sql .= " ON DUPLICATE KEY UPDATE 
                        checking_status = VALUES(checking_status),
                        checking_note = VALUES(checking_note),
                        modified_at = NOW()";

            $db->query($sql);
            $actualAffected += $db->affectedRows();
        }

        return $actualAffected;
    }
}
