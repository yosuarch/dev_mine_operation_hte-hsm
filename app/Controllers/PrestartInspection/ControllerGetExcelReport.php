<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\xlsx;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerGetExcelReport extends BaseController
{

    protected $table = 'view_psi_record_detail_download';

    public function index()
    {

        // 1. properties
        $table = $this->table;

        // 2. make connection to database
        $db = \Config\Database::connect();

        // 3. fetching the view result
        $result = $db->table($table)->get()->getResultArray();

        // 4. init the phpspreadsheet
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if (!empty($result)) {
            $columns = array_keys($result[0]);
            $colLetter = 'A';

            foreach ($columns as $column) {
                // sanitize the value from database
                $headerTitle = ucwords(str_replace('_', ' ', $column));
                $sheet->setCellValue($colLetter . '1', $headerTitle);
                $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
                $colLetter++;
            }

            $rowNumber = 2; // row 1 is header row 2 is the data
            $rowNumber = 2;
            foreach ($result as $dataRow) {
                $colLetter = 'A';

                // FIX: Iterate over $dataRow, not $result
                foreach ($dataRow as $cellValue) {
                    // Set the value into the cell
                    $sheet->setCellValue($colLetter . $rowNumber, $cellValue);
                    $colLetter++;
                }
                $rowNumber++;
            }

            $columnWidths = [
                'A' => 15,
                'B' => 15,
                'C' => 15,
                'D' => 15,
                'E' => 15,
                'F' => 25,
                'G' => 15,
                'H' => 65
            ];

            foreach ($columnWidths as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            // 1. Calculate the range of your data
            $highestRow = $sheet->getHighestRow(); // e.g., 50
            $highestColumn = $sheet->getHighestColumn(); // e.g., 'H'
            $range = 'A1:' . $highestColumn . $highestRow;

            // 2. Define the border style
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'], // Black color
                    ],
                ],
            ];

            // Add this after your border styling
            $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'], // Light Gray
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // 3. Apply the style to the entire range
            $sheet->getStyle($range)->applyFromArray($styleArray);

            $fileName = date('Y-m-d_H-i-s') . 'P2H_notif_list.xlsx';

            // force the header to be downloaded
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            exit();
        }
    }
}
