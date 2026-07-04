<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;

class CheckPart extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Display + FK values in one query
        $rows = $db->table('psi_unique_observed_item u')
            ->select('u.idx, u.checking_part, u.checking_part_idn, u.spot AS spot_fk, u.danger_tag AS danger_tag_fk, s.code AS spot_position, d.code AS danger_tag_code, d.description AS danger_level')
            ->join('psi_spoting_position s', 'u.spot = s.idx', 'left')
            ->join('ohse_danger_code d', 'u.danger_tag = d.idx', 'inner')
            ->orderBy('u.checking_part_idn')
            ->get()->getResultArray();

        $spotPositions = $db->table('psi_spoting_position')
            ->select('idx, code')->orderBy('code')->get()->getResultArray();
        $dangerCodes   = $db->table('ohse_danger_code')
            ->select('idx, code, description')->orderBy('code')->get()->getResultArray();

        return view('pages/check_part/index', [
            'pageTitle'     => 'Check Part Management',
            'rows'          => $rows,
            'spotPositions' => $spotPositions,
            'dangerCodes'   => $dangerCodes,
        ]);
    }

    public function store()
    {
        $json      = $this->request->getJSON(true);
        $idn       = trim($json['checking_part_idn'] ?? '');
        $en        = trim($json['checking_part']     ?? '');
        $spot      = (int) ($json['spot']            ?? 0);
        $dangerTag = (int) ($json['danger_tag']      ?? 0);

        if (!$idn || !$en || !$dangerTag) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Name (IDN), Name (EN), and Danger Tag are required.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        try {
            \Config\Database::connect()
                ->table('psi_unique_observed_item')
                ->insert([
                    'checking_part_idn' => $idn,
                    'checking_part'     => $en,
                    'spot'              => $spot ?: null,
                    'danger_tag'        => $dangerTag,
                ]);
        } catch (\Throwable) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => 'A check part with this name already exists.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['status' => 'ok', 'csrf_hash' => csrf_hash()]);
    }

    public function update(int $idx)
    {
        $json      = $this->request->getJSON(true);
        $idn       = trim($json['checking_part_idn'] ?? '');
        $en        = trim($json['checking_part']     ?? '');
        $spot      = (int) ($json['spot']            ?? 0);
        $dangerTag = (int) ($json['danger_tag']      ?? 0);

        if (!$idn || !$en || !$dangerTag) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Name (IDN), Name (EN), and Danger Tag are required.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        try {
            \Config\Database::connect()
                ->table('psi_unique_observed_item')
                ->where('idx', $idx)
                ->update([
                    'checking_part_idn' => $idn,
                    'checking_part'     => $en,
                    'spot'              => $spot ?: null,
                    'danger_tag'        => $dangerTag,
                ]);
        } catch (\Throwable) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => 'A check part with this name already exists.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['status' => 'ok', 'csrf_hash' => csrf_hash()]);
    }

    public function destroy(int $idx)
    {
        $db    = \Config\Database::connect();
        $inUse = $db->table('psi_observed_by_unit_type')
            ->where('checking_part', $idx)
            ->countAllResults();

        if ($inUse > 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => "Cannot delete — referenced by {$inUse} checklist item(s). Remove those first.",
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db->table('psi_unique_observed_item')->where('idx', $idx)->delete();

        return $this->response->setJSON(['status' => 'ok', 'csrf_hash' => csrf_hash()]);
    }
}
