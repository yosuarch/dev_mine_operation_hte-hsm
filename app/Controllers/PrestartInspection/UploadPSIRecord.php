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
        if (!$file->isValid() || $file->hasMoved()) return "Error while uploading file";

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);
        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $newName;

        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $db = \Config\Database::connect();

        $equipList = array_change_key_case(array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $shiftList = array_change_key_case(array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code'), CASE_LOWER);
        $mpList    = array_change_key_case(array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name'), CASE_LOWER);
        $partList  = array_change_key_case(array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn'), CASE_LOWER);

        $inserted = 0;
        $updated = 0;
        $errorDetails = [];
        $totalHandled = 0;

        foreach ($sheetData as $index => $row) {
            if ($index == 1) continue;
            $totalHandled++;

            $rawDate = $row['C'];
            $dateFormated = is_numeric($rawDate) ? Date::excelToDateTimeObject($rawDate)->format('Y-m-d') : (date('Y-m-d', strtotime($rawDate)) ?: null);

            $equipIdx = $equipList[mb_strtolower($row['B'])] ?? null;
            $shiftIdx = $shiftList[mb_strtolower($row['D'])] ?? null;
            $opIdx    = $mpList[mb_strtolower($row['E'])] ?? null;
            $partIdx  = $partList[mb_strtolower($row['H'])] ?? null;

            if (!$equipIdx || !$shiftIdx || !$opIdx || !$partIdx || !$dateFormated) {
                $errorDetails[] = ['row' => $index, 'reason' => 'Missing reference data or invalid date.'];
                continue;
            }

            $data = [
                'equipment_id' => (int)$equipIdx,
                'date' => $dateFormated,
                'shift' => (int)$shiftIdx,
                'operator_name' => (int)$opIdx,
                'hourmeter_start' => is_numeric($row['F']) ? (float)$row['F'] : 0,
                'hourmeter_end' => is_numeric($row['G']) ? (float)$row['G'] : 0,
                'checking_part' => (int)$partIdx,
                'checking_status' => (strcasecmp($row['I'], 'TRUE') === 0) ? 1 : 0,
                'checking_note' => !empty($row['J']) ? (string)$row['J'] : 'no issue'
            ];

            // Check existence
            $exists = $db->table('psi_record')
                ->where(['equipment_id' => $data['equipment_id'], 'date' => $data['date'], 'shift' => $data['shift'], 'operator_name' => $data['operator_name'], 'hourmeter_start' => $data['hourmeter_start'], 'hourmeter_end' => $data['hourmeter_end'], 'checking_part' => $data['checking_part']])
                ->get()->getRow();

            if (!$exists) {
                $db->table('psi_record')->insert($data);
                $inserted++;
            } else {
                $db->table('psi_record')->where('idx', $exists->idx)->update([
                    'checking_status' => $data['checking_status'],
                    'checking_note' => $data['checking_note'],
                    'modified_at' => date('Y-m-d H:i:s')
                ]);
                $updated++;
            }
        }

        unlink($filePath);
        session()->setFlashdata('summary', ['total' => $totalHandled, 'inserted' => $inserted, 'updated' => $updated, 'errors' => $errorDetails]);
        return redirect()->back();
    }
}
