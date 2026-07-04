<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use CodeIgniter\HTTP\ResponseInterface;

class PreviewBeforeUpload extends BaseController
{
    public function index()
    {
        $file = $this->request->getFile('psiRecording');

        if (!$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid file'
            ]);
        }

        try {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            $spreadsheet = IOFactory::load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $data = [];
            foreach ($sheetData as $index => $row) {
                if ($index == 1) continue; // Skip header row

                // convert the excel date
                $excelDate = $row['C'];
                $dateFormated = Date::excelToDateTimeObject($excelDate)->format('Y-m-d');

                $data[] = [
                    'equipment_id' => $row['B'],
                    'date' => $dateFormated,
                    'shift' => $row['D'],
                    'operator_name' => $row['E'],
                    'hourmeter_start' => $row['F'],
                    'hourmeter_end' => $row['G'],
                    'checking_part' => $row['H'],
                    'checking_note' => $row['J'],
                ];
            }

            unlink($filePath);

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
