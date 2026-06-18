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
        // 
        $data = new ModelPsiRecord();
        $query = $data->getDangerStatFreq()->get()->getResultArray();

        return $this->response->setJSON($query);
    }

    public function getSumIssue()
    {
        // 
        $data = new ModelPsiRecord();
        $query = $data->getSumIssue()->get()->getResultArray();

        return $this->response->setJSON($query);
    }

    public function fetchMPList()
    {

        // 1. Turn off the debug toolbar so it doesn't break the JSON
        // if (ENVIRONMENT !== 'production') {
        //     \Config\Services::toolbar()->;
        // }

        // fetch Manpower name and id
        $db = Database::connect();

        // table name
        $mpList = $db->table('view_mp_list')
            ->select('idx, name, employee_id')
            ->orderBy('idx', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($mpList);
    }

    public function getUniqueEquipmentType()
    {
        // connect to database
        $db = Database::connect();

        // fetch data from database
        $equipType = $db->table('view_unique_equipment_type')
            ->get()
            ->getResultArray();

        // return the result as json for frontend
        return $this->response->setJSON($equipType);
    }

    public function getEquipmentID()
    {
        $db = Database::connect();

        $typeIdx = $this->request->getGet('type_idx');

        $builder = $db->table('view_equipment_id_list')
            ->select('idx, equipment_id')
            ->orderBy('equipment_id', 'ASC');

        if ($typeIdx !== null && $typeIdx !== '') {
            $builder->where('abbreviation', (int) $typeIdx);
        }

        return $this->response->setJSON($builder->get()->getResultArray());
    }
}
