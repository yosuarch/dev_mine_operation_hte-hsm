<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options; // Import Options
use Config\Database;

class ControllerSentReportTest extends BaseController
{
    public function psiDailyDetailReport()
    {
        // 1. Memory Optimization
        ini_set('memory_limit', '512M');

        // 3. Database
        $db = Database::connect();
        $records = $db->table('view_psi_daily_recording')->get()->getResultArray();

        // 4. Data Aggregation
        $groupedData = [];
        foreach ($records as $row) {
            $groupedData[$row['type']][] = $row;
        }

        // 5. Initialize DOMPDF with Options Object
        $options = new Options();
        $options->setDebugLayoutLines(true);
        $options->setIsJavascriptEnabled(true);
        // $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);
        $options->setChroot(FCPATH); // Allow access to FCPATH for images
        $dompdf = new Dompdf($options);

        // 6. Pass data correctly to View
        // Ensure keys match the variables you use in the View
        $data = [
            'groupedData' => $groupedData,
        ];

        $html = view('pages/psi/pdf/pdf-psi-report', $data);

        // 7. Render
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("P2H_Report_" . date('Y-m-d') . ".pdf", ['Attachment' => 1]);
        exit(); // Crucial to prevent output pollution
    }
}
