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

            // prepare to insert, extracting data for each row
            $model = new ModelPsiRecord();
            // load the table from database
            $db = \Config\Database::connect();
            $equipmentRegister = $db->table('equipment_register');
            $workingShift = $db->table('general_working_shift');
            $manPowerList = $db->table('mp_list');
            $checkPart = $db->table('psi_unique_observed_item');

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
                // 1. get the value from column B
                $rawECode = $row['B'];
                $rawShift = $row['D'];
                $rawOpName = $row['E'];
                $rawCheckPart = $row['H'];
                $rawCheckStatus = $row['I'];

                // 2. get iterate for each row
                // 2.1 equipment_ID
                $rawECode = $equipmentRegister->select('idx')
                    ->where("CONCAT(text_code, num_code) =", $rawECode)
                    ->get()
                    ->getRow();

                // 2.2 shift
                $rawShift = $workingShift->select('idx')
                    ->where("code =", $rawShift)
                    ->get()
                    ->getRow();

                // 2.3 operator name
                $rawOpName = $manPowerList->select('idx')
                    ->where("name =", $rawOpName)
                    ->get()
                    ->getRow();

                // 2.3 checking part
                $rawCheckPart = $checkPart->select('idx')
                    ->where("checking_part_idn =", $rawCheckPart)
                    ->get()
                    ->getRow();

                // 2.4 boolean check status
                $checkStatus = (strcasecmp($rawCheckStatus, 'TRUE') === 0) ? 1 : 0;

                // 3. if found then use the idx, else return the not match value and continue the existing data
                if ($rawECode && $rawShift && $rawOpName && $rawCheckPart) {
                    $data = [
                        'equipment_id' => (int)$rawECode->idx,
                        'date' => $dateFormated,
                        'shift' => (int)$rawShift->idx,
                        'operator_name' => (int)$rawOpName->idx,
                        'hourmeter_start' => (string)$row['F'],
                        'hourmeter_end' => (string)$row['G'],
                        'checking_part' => (int)$rawCheckPart->idx,
                        'checking_status' => (int)$checkStatus,
                        'checking_note' => (string)$row['J'],
                    ];
                    if ($model->insert($data)) {
                        $inserted++;
                    } else {
                        $errors[] = "Row $index: Insert failed - " . implode(', ', $model->errors());
                    }
                } else {
                    $skipped++;
                    $errors[] = "Row $index: Missing lookup - Equipment: " . ($rawECode ? 'OK' : 'FAIL') .
                        ", Shift: " . ($rawShift ? 'OK' : 'FAIL') .
                        ", Operator: " . ($rawOpName ? 'OK' : 'FAIL') .
                        ", CheckPart: " . ($rawCheckPart ? 'OK' : 'FAIL');
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
