<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;

class EquipmentObservedList extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Display data — read directly from view
        $rows = $db->table('view_equipment_observed_list')->get()->getResultArray();

        // FK values for edit modal pre-population (keyed by idx)
        $rawMap = array_column(
            $db->table('psi_observed_by_unit_type')
                ->select('idx, checking_part, spot, danger_tag, equipment_type')
                ->get()->getResultArray(),
            null,
            'idx'
        );

        // Lookup lists for edit dropdowns
        $checkParts    = $db->table('psi_unique_observed_item')
            ->select('idx, checking_part_idn, checking_part')->orderBy('checking_part_idn')->get()->getResultArray();
        $spotPositions = $db->table('psi_spoting_position')
            ->select('idx, code')->orderBy('code')->get()->getResultArray();
        $dangerCodes   = $db->table('ohse_danger_code')
            ->select('idx, code, description')->orderBy('code')->get()->getResultArray();
        $equipTypes    = $db->table('equipment_unique_type')
            ->select('idx, code, abbreviation')->orderBy('code')->get()->getResultArray();

        return view('pages/equipment_observed_list/index', [
            'pageTitle'     => 'Checklist Configuration',
            'rows'          => $rows,
            'rawMap'        => $rawMap,
            'checkParts'    => $checkParts,
            'spotPositions' => $spotPositions,
            'dangerCodes'   => $dangerCodes,
            'equipTypes'    => $equipTypes,
        ]);
    }

    public function store()
    {
        $json          = $this->request->getJSON(true);
        $checkingPart  = (int) ($json['checking_part']  ?? 0);
        $spot          = (int) ($json['spot']           ?? 0);
        $dangerTag     = (int) ($json['danger_tag']     ?? 0);
        $equipmentType = (int) ($json['equipment_type'] ?? 0);

        if (!$checkingPart || !$spot || !$dangerTag || !$equipmentType) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'All fields are required.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        try {
            \Config\Database::connect()
                ->table('psi_observed_by_unit_type')
                ->insert([
                    'checking_part'  => $checkingPart,
                    'spot'           => $spot,
                    'danger_tag'     => $dangerTag,
                    'equipment_type' => $equipmentType,
                ]);
        } catch (\Throwable) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => 'Duplicate entry — this combination already exists.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['status' => 'ok', 'csrf_hash' => csrf_hash()]);
    }

    public function update(int $idx)
    {
        $json          = $this->request->getJSON(true);
        $checkingPart  = (int) ($json['checking_part']  ?? 0);
        $spot          = (int) ($json['spot']           ?? 0);
        $dangerTag     = (int) ($json['danger_tag']     ?? 0);
        $equipmentType = (int) ($json['equipment_type'] ?? 0);

        if (!$checkingPart || !$spot || !$dangerTag || !$equipmentType) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'All fields are required.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        try {
            \Config\Database::connect()
                ->table('psi_observed_by_unit_type')
                ->where('idx', $idx)
                ->update([
                    'checking_part'  => $checkingPart,
                    'spot'           => $spot,
                    'danger_tag'     => $dangerTag,
                    'equipment_type' => $equipmentType,
                ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => 'Duplicate entry — this combination already exists.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['status' => 'ok', 'csrf_hash' => csrf_hash()]);
    }

    public function destroy(int $idx)
    {
        \Config\Database::connect()
            ->table('psi_observed_by_unit_type')
            ->where('idx', $idx)
            ->delete();

        return $this->response->setJSON(['status' => 'ok', 'csrf_hash' => csrf_hash()]);
    }
}
