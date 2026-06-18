<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use App\Models\PrestartInspection\ModelPsiRecord;
use Hermawan\DataTables\DataTable;
use Config\Database;
use CodeIgniter\HTTP\ResponseInterface;


class ControllerGetData extends BaseController
{
    public function fetchPSIDetail()
    {
        $db = \Config\Database::connect();

        // We select specifically to ensure the key names are clean and lowercase
        $builder = $db->table('view_psi_details')
            ->select('date, equipment_id, type, model, check_item, danger_code, note');

        return DataTable::of($builder)->toJson();
    }

    public function fetchGetDangerCodeFreq()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'psi_danger_code_freq';

        $query = $cache->get($cacheKey);
        if ($query === null) {
            $data  = new ModelPsiRecord();
            $query = $data->getDangerStatFreq()->get()->getResultArray();
            $cache->save($cacheKey, $query, 300);
        }

        return $this->response->setJSON($query);
    }

    public function getSumIssue()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'psi_sum_issue';

        $query = $cache->get($cacheKey);
        if ($query === null) {
            $data  = new ModelPsiRecord();
            $query = $data->getSumIssue()->get()->getResultArray();
            $cache->save($cacheKey, $query, 300);
        }

        return $this->response->setJSON($query);
    }

    public function fetchMPList()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'psi_mp_list';

        $mpList = $cache->get($cacheKey);
        if ($mpList === null) {
            $db     = Database::connect();
            $mpList = $db->table('view_mp_list')
                ->select('idx, name, employee_id')
                ->orderBy('idx', 'ASC')
                ->get()
                ->getResultArray();
            $cache->save($cacheKey, $mpList, 3600);
        }

        return $this->response->setJSON($mpList);
    }

    public function getUniqueEquipmentType()
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'psi_unique_equipment_type';

        $equipType = $cache->get($cacheKey);
        if ($equipType === null) {
            $db        = Database::connect();
            $equipType = $db->table('view_unique_equipment_type')
                ->get()
                ->getResultArray();
            $cache->save($cacheKey, $equipType, 3600);
        }

        return $this->response->setJSON($equipType);
    }

    public function getEquipmentID()
    {
        $typeIdx  = $this->request->getGet('type_idx');
        $cache    = \Config\Services::cache();
        $cacheKey = 'psi_equipment_id_' . ($typeIdx ?? 'all');

        $result = $cache->get($cacheKey);
        if ($result === null) {
            $db      = Database::connect();
            $builder = $db->table('view_equipment_id_list')
                ->select('idx, equipment_id, where_index')
                ->orderBy('equipment_id', 'ASC');

            if ($typeIdx !== null && $typeIdx !== '') {
                $builder->where('where_index', (int) $typeIdx);
            }

            $result = $builder->get()->getResultArray();
            $cache->save($cacheKey, $result, 3600);
        }

        return $this->response->setJSON($result);
    }

    public function getEmptyPSIForm()
    {
        $equipIdx = $this->request->getGet('equip_idx');
        $cache    = \Config\Services::cache();
        $cacheKey = 'psi_empty_form_' . ($equipIdx ?? 'all');

        $result = $cache->get($cacheKey);
        if ($result === null) {
            $db      = Database::connect();
            $builder = $db->table('view_psi_empty_from')
                ->select('idx, int_type, text_type, check_part, hazard_code, hazard_code_description, spot_position');

            if ($equipIdx !== null && $equipIdx !== '') {
                $builder->where('int_type', (int) $equipIdx);
            }

            $result = $builder->get()->getResultArray();
            $cache->save($cacheKey, $result, 3600);
        }

        return $this->response->setJSON($result);
    }
}
