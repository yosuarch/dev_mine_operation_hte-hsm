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

                // Retrieve raw values
                $valDate  = $row[$headerMap['date'] ?? ''] ?? null;
                $nEquip   = $this->normalize($row[$headerMap['equipment_id'] ?? ''] ?? '');
                $nShift   = $this->normalize($row[$headerMap['shift'] ?? ''] ?? '');
                $nOp      = $this->normalize($row[$headerMap['operator_name'] ?? ''] ?? '');
                $nFM      = $this->normalize($row[$headerMap['fm_name'] ?? ''] ?? '');
                $nSPV     = $this->normalize($row[$headerMap['spv_name'] ?? ''] ?? '');
                $nPart    = $this->normalize($row[$headerMap['checking_part'] ?? ''] ?? '');

                // Date Parsing
                $dateFormatted = (is_numeric($valDate) && $valDate > 25569)
                    ? Date::excelToDateTimeObject($valDate)->format('Y-m-d')
                    : (strtotime($valDate) ? date('Y-m-d', strtotime($valDate)) : null);

                // Validation
                $missing = [];
                if (!isset($equipList[$nEquip])) $missing[] = 'Equipment';
                if (!isset($shiftList[$nShift])) $missing[] = 'Shift';
                if (!isset($mpList[$nOp]))       $missing[] = 'Operator';
                if (!isset($mpList[$nFM]))       $missing[] = 'FM Name';
                if (!isset($mpList[$nSPV]))      $missing[] = 'SPV Name';
                if (!isset($partList[$nPart]))   $missing[] = 'Part';
                if (!$dateFormatted)             $missing[] = 'Date';

                if (!empty($missing)) {
                    $summary['errors'][] = ['row' => $index, 'reason' => 'Missing: ' . implode(', ', $missing)];
                    continue;
                }

                // Data mapping
                $data = [
                    'equipment_id'    => (int)$equipList[$nEquip],
                    'date'            => $dateFormatted,
                    'shift'           => (int)$shiftList[$nShift],
                    'operator_name'   => (int)$mpList[$nOp],
                    'fm_name'         => (int)$mpList[$nFM],
                    'fm_note'         => $row[$headerMap['fm_note'] ?? ''] ?? 'this is data is a dummy_please check the actual sheet to make confirmation or ask the admin',
                    'spv_name'        => (int)$mpList[$nSPV],
                    'spv_note'        => $row[$headerMap['spv_note'] ?? ''] ?? 'this is data is a dummy_please check the actual sheet to make confirmation or ask the admin',
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
