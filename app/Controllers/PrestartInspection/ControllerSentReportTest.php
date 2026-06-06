<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options;
use Config\Database;

class ControllerSentReportTest extends BaseController
{
    public function psiDailyDetailReport()
    {
        // 1. Memory Optimization (Crucial when handling base64 image data)
        ini_set('memory_limit', '512M');

        // 2. Database Connection
        $db = Database::connect();

        // 3. Fetch Main Records (Query 1: Details & Items)
        $records = $db->table('view_psi_daily_recording')
            ->orderBy('type', 'ASC')
            ->orderBy('equipment_id', 'ASC')
            ->get()
            ->getResultArray();

        // 4. Fetch Validation & Signature Records (Query 2)
        // Make sure you have created this view in your database from your second query
        $validators = $db->table('view_psi_validation_status')
            ->get()
            ->getResultArray();

        // 5. Map Signatures to a Lookup Array
        // We use equipment_id + shift + date as a unique key to match records
        $validator_map = [];
        foreach ($validators as $val) {
            // Note: Your second query uses "AS DATE" (uppercase), so we use $val['DATE']
            $key = $val['equipment_id'] . '_' . $val['shift'] . '_' . $val['DATE'];

            // Convert MEDIUMBLOB to Base64 URI so DOMPDF can render it directly
            $fm_sign_base64 = !empty($val['fm_sign']) ? 'data:image/png;base64,' . base64_encode($val['fm_sign']) : null;
            $spv_sign_base64 = !empty($val['spv_sign']) ? 'data:image/png;base64,' . base64_encode($val['spv_sign']) : null;

            $validator_map[$key] = [
                'fm_name'  => $val['fm_name'],
                'fm_note'  => $val['fm_note'],
                'fm_sign'  => $fm_sign_base64,
                'spv_name' => $val['spv_name'],
                'spv_note' => $val['spv_note'],
                'spv_sign' => $spv_sign_base64,
            ];
        }

        // 6. Data Aggregation (Combine Main Records with their Signatures)
        $combined_data = [];
        foreach ($records as $row) {
            // Note: Your first query uses "AS date" (lowercase), so we use $row['date']
            $key = $row['equipment_id'] . '_' . $row['shift'] . '_' . $row['date'];

            // Attach the validation data if it exists for this specific equipment/shift/date
            $row['validation'] = $validator_map[$key] ?? null;

            // Group by equipment type
            $combined_data[$row['type']][] = $row;
        }

        // 7. Initialize DOMPDF with Options
        $options = new Options();
        $options->setDebugLayoutLines(false); // Set to false for clean production output
        $options->setIsJavascriptEnabled(true);
        $options->setIsRemoteEnabled(true);
        $options->setChroot(FCPATH); // Allow access to FCPATH for your logo image
        $dompdf = new Dompdf($options);

        // 8. Pass combined data to the View
        $data = [
            'combined_data' => $combined_data,
        ];

        $html = view('pages/psi/pdf/pdf-psi-report', $data);

        // 9. Render and Stream PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output to browser
        $dompdf->stream("P2H_Report_" . date('Y-m-d H:i:s') . ".pdf", ['Attachment' => 1]);
        exit(); // Crucial to prevent output pollution
    }
}
