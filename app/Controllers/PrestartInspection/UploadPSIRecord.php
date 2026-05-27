<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            $file->move(WRITEPATH . 'uploads' . $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            // load the spreadsheet libs for extracting data
            $spreasheet = IOFactory::load($filePath);
            $sheetData = $spreasheet->getActiveSheet()->toArray(null, true, true, true);

            // prepare to insert, extracting data for each row
            $model = new ModelPsiRecord();
            foreach ($sheetData as $index => $row) {
                // extracting the data from excel file
                if ($index == 1) continue;

                $data = [
                    'equipment_id' => ['B'],
                    'date' => ['C'],
                    'shift' => ['D'],
                    'operator_name' => ['E'],
                    'hourmeter_start' => ['F'],
                    'hourmeter_end' => ['G'],
                    'checking_part' => ['H'],
                    'checking_status' => ['I'],
                    'checking_note' => ['J'],
                ];
                $model->insert($data);
            }
            unlink($filePath);
            return "File imported successfullly!";
        }
        return "Error while uploading file";
    }
}
