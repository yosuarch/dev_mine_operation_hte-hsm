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

            $equipList = array_column($db->table('equipment_register')->select("CONCAT(text_code, num_code) as code, idx")->get()->getResultArray(), 'idx', 'code');
            $shiftList = array_column($db->table('general_working_shift')->select("code, idx")->get()->getResultArray(), 'idx', 'code');
            $mpList    = array_column($db->table('mp_list')->select("name, idx")->get()->getResultArray(), 'idx', 'name');
            $partList  = array_column($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray(), 'idx', 'checking_part_idn');

            // prepare to insert, extracting data for each row
            $model = new ModelPsiRecord();

            $inserted = 0;
            $skipped = 0;
            $errors = [];

            foreach ($sheetData as $index => $row) {
                // extracting the data from excel file
                if ($index == 1) continue;

                // transform the date
                $excelDate = $row['C'];
                $dateFormated = Date::excelToDateTimeObject($excelDate)->format('Y-m-d');

                // transforming the equipment_id
                // 1. search the result at memory
                $equipIdx = $equipList[$row['B']] ?? null;
                $shiftIdx = $shiftList[$row['D']] ?? null;
                $opIdx    = $mpList[$row['E']] ?? null;
                $partIdx  = $partList[$row['H']] ?? null;

                // 2. get iterate for each row
                // 2.1 equipment_ID
                // $rawECode = $equipmentRegister->select('idx')
                //     ->where("CONCAT(text_code, num_code) =", $rawECode)
                //     ->get()
                //     ->getRow();

                // // 2.2 shift
                // $rawShift = $workingShift->select('idx')
                //     ->where("code =", $rawShift)
                //     ->get()
                //     ->getRow();

                // // 2.3 operator name
                // $rawOpName = $manPowerList->select('idx')
                //     ->where("name =", $rawOpName)
                //     ->get()
                //     ->getRow();

                // // 2.3 checking part
                // $rawCheckPart = $checkPart->select('idx')
                //     ->where("checking_part_idn =", $rawCheckPart)
                //     ->get()
                //     ->getRow();

                if ($equipIdx && $shiftIdx && $opIdx && $partIdx) {
                    $data = [
                        'equipment_id'    => (int)$equipIdx,
                        'date'            => Date::excelToDateTimeObject($row['C'])->format('Y-m-d'),
                        'shift'           => (int)$shiftIdx,
                        'operator_name'   => (int)$opIdx,
                        'hourmeter_start' => is_numeric($row['F']) ? (int)$row['F'] : 0,
                        'hourmeter_end'   => is_numeric($row['G']) ? (int)$row['G'] : 0,
                        'checking_part'   => (int)$partIdx,
                        'checking_status' => (strcasecmp($row['I'], 'TRUE') === 0) ? 1 : 0,
                        'checking_note'   => (string)$row['J'],
                    ];
                    if ($model->insert($data)) {
                        $inserted++;
                    } else {
                        $errors[] = "Row $index: Insert failed";
                    }
                } else {
                    $skipped++;
                    $errors[] = "Row $index: Lookup failed (Check data integrity)";
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
