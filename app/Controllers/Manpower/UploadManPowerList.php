<?php

namespace App\Controllers\Manpower;

use App\Controllers\BaseController;
use App\Models\ManPowerList;
use PhpOffice\PhpSpreadsheet\IOFactory;
use CodeIgniter\HTTP\ResponseInterface;

class UploadManPowerList extends BaseController
{
    public function uploadManPower()
    {
        //
        $file = $this->request->getFile('manpowerList');
        if ($file->isValid() && !$file->hasMoved()) {
            # code...
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads' . $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            $spreadsheet = IOFactory::load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $userModel = new ManPowerList();
            foreach ($sheetData as $index => $row) {
                # code...
                if ($index == 1) continue;

                $data = [
                    'name' => $row['A'],
                    'employee_id' => $row['B'],
                    'gender' => $row['C'],
                    'first_phone_number' => $row['D'],
                    'second_phone_number' => $row['E'],
                    'emergency_contact_number' => $row['F'],
                ];

                $userModel->insert($data);
            }
            unlink($filePath);
            return "File imported successfullly!";
        }
        return "Error while uploading file";
    }
}
