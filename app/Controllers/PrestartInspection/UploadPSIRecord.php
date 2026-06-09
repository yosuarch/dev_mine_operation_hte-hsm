<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UploadPSIRecord extends BaseController
{
    /**
     * Normalizes strings by removing all special characters, 
     * converting to lowercase, and trimming.
     */
    private function normalize($value)
    {
        $str = mb_strtolower(trim((string)$value));
        return preg_replace('/[^a-z0-9]/', '', $str);
    }

    private function safeTrim($value)
    {
        return trim((string)$value);
    }

    public function index()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $file = $this->request->getFile('psiRecording');
        if (!$file->isValid()) return redirect()->back()->with('error', 'Invalid file.');

        $spreadsheet = IOFactory::load($file->getTempName());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Header mapping
        $headerRow = array_map(function ($value) {
            return $this->safeTrim($value);
        }, $sheetData[1]);

        $headerMap = [];
        foreach ($headerRow as $colLetter => $headerName) {
            if (!empty($headerName)) {
                $headerMap[mb_strtolower($headerName)] = $colLetter;
            }
        }

        $db = \Config\Database::connect();

        // Pre-load and normalize reference lists
        $equipList = [];
        foreach ($db->table('equipment_register')->select("CONCAT(text_code, num_code) as full_code, idx")->get()->getResultArray() as $row) {
            $equipList[$this->normalize($row['full_code'])] = $row['idx'];
        }

        $partList = [];
        foreach ($db->table('psi_unique_observed_item')->select("checking_part_idn, idx")->get()->getResultArray() as $row) {
            $partList[$this->normalize($row['checking_part_idn'])] = $row['idx'];
        }

        $mpList = [];
        foreach ($db->table('mp_list')->select("name, idx")->get()->getResultArray() as $row) {
            $mpList[$this->normalize($row['name'])] = $row['idx'];
        }

        $shiftList = ['day' => 1, 'night' => 2];

        $summary = ['total' => 0, 'inserted' => 0, 'errors' => []];

        $db->transBegin();
        try {
            foreach ($sheetData as $index => $row) {
                if ($index == 1) continue;
                if (empty(array_filter($row))) continue;
                $summary['total']++;

                // Retrieve raw values for detailed error reporting
                $rawDate  = $row[$headerMap['date'] ?? ''] ?? null;
                $rawEquip = $row[$headerMap['equipment_id'] ?? ''] ?? '';
                $rawShift = $row[$headerMap['shift'] ?? ''] ?? '';
                $rawOp    = $row[$headerMap['operator_name'] ?? ''] ?? '';
                $rawFM    = $row[$headerMap['fm_name'] ?? ''] ?? '';
                $rawSPV   = $row[$headerMap['spv_name'] ?? ''] ?? '';
                $rawPart  = $row[$headerMap['checking_part'] ?? ''] ?? '';

                // Normalize values for DB lookup
                $nEquip   = $this->normalize($rawEquip);
                $nShift   = $this->normalize($rawShift);
                $nOp      = $this->normalize($rawOp);
                $nFM      = $this->normalize($rawFM);
                $nSPV     = $this->normalize($rawSPV);
                $nPart    = $this->normalize($rawPart);

                // Date Parsing
                $dateFormatted = (is_numeric($rawDate) && $rawDate > 25569)
                    ? Date::excelToDateTimeObject($rawDate)->format('Y-m-d')
                    : (strtotime($rawDate) ? date('Y-m-d', strtotime($rawDate)) : null);

                // Detailed Validation
                $detailedErrors = [];
                if (!isset($equipList[$nEquip])) $detailedErrors[] = "Equipment '" . ($rawEquip ?: 'BLANK') . "' not found in DB";
                if (!isset($shiftList[$nShift])) $detailedErrors[] = "Shift '" . ($rawShift ?: 'BLANK') . "' invalid";
                if (!isset($mpList[$nOp]))       $detailedErrors[] = "Operator '" . ($rawOp ?: 'BLANK') . "' not found in DB";
                // if (!isset($mpList[$nFM]))       $detailedErrors[] = "FM Name '" . ($rawFM ?: 'BLANK') . "' not found in DB";
                // if (!isset($mpList[$nSPV]))      $detailedErrors[] = "SPV Name '" . ($rawSPV ?: 'BLANK') . "' not found in DB";
                if (!isset($partList[$nPart]))   $detailedErrors[] = "Part '" . ($rawPart ?: 'BLANK') . "' not found in DB";
                if (!$dateFormatted)             $detailedErrors[] = "Date '" . ($rawDate ?: 'BLANK') . "' invalid";

                if (!empty($detailedErrors)) {
                    $summary['errors'][] = [
                        'row'    => $index,
                        'reason' => implode(' | ', $detailedErrors)
                    ];
                    continue;
                }

                // Data mapping
                $data = [
                    'equipment_id'    => (int)$equipList[$nEquip],
                    'date'            => $dateFormatted,
                    'shift'           => (int)$shiftList[$nShift],
                    'operator_name'   => (int)$mpList[$nOp],
                    'fm_name'         => (isset($mpList[$nFM])) ? (int)$mpList[$nFM] : 0,
                    'fm_note'         => $row[$headerMap['fm_note'] ?? ''] ?? 'no additional note',
                    'spv_name'        => (isset($mpList[$nSPV])) ? (int)$mpList[$nSPV] : 0,
                    'spv_note'        => $row[$headerMap['spv_note'] ?? ''] ?? 'no additional note',
                    'hourmeter_start' => (is_numeric($row[$headerMap['hourmeter_start'] ?? ''])) ? (float)$row[$headerMap['hourmeter_start']] : 0,
                    'hourmeter_end'   => (is_numeric($row[$headerMap['hourmeter_end'] ?? ''])) ? (float)$row[$headerMap['hourmeter_end']] : 0,
                    'checking_part'   => (int)$partList[$nPart],
                    'checking_status' => 1,
                    'checking_note'   => $row[$headerMap['checking_note'] ?? ''] ?? 'no issue',
                    'modified_at'     => date('Y-m-d H:i:s')
                ];

                $db->table('psi_record')->upsert($data);
                $summary['inserted']++;
            }
            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'DB Error: ' . $e->getMessage());
        }

        $db->table('psi_record_upload_results')->insert(['summary_json' => json_encode($summary)]);
        return redirect()->back()->with('result_id', $db->insertID());
    }
}
