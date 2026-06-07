<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

            // Define your date columns here for easy maintenance
            $dateColumns = ['date'];

            $rowNumber = 2;
            foreach ($result as $dataRow) {
                $colLetter = 'A';

                foreach ($dataRow as $columnName => $cellValue) {
                    // If the column name is in our date list, convert it
                    if (in_array($columnName, $dateColumns) && !empty($cellValue)) {
                        // 1. Create the date object and force time to midnight
                        $dateObj = \DateTime::createFromFormat('Y-m-d', substr($cellValue, 0, 10));
                        $dateObj->setTime(0, 0, 0); // Force time to midnight exactly

                        // 2. Convert to Excel format
                        $cellValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateObj);
                    }

                    $sheet->setCellValue($colLetter . $rowNumber, $cellValue);
                    $colLetter++;
                }
                $rowNumber++;
            }

            // 1. Define the format code
            $dateStyle = 'yyyy-mm-dd'; // You can also use 'dd/mm/yyyy' etc.

            // 2. Get the highest row to cover the whole column
            $highestRow = $sheet->getHighestRow();

            // 3. Apply the format to column A (from row 2 to the last row)
            $sheet->getStyle('A2:A' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode($dateStyle);

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
