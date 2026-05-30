<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use App\Models\PrestartInspection\ModelPsiRecord;
use Hermawan\DataTables\DataTable;
use CodeIgniter\HTTP\ResponseInterface;


class ControllerGetData extends BaseController
{
    // public function fetchRawPSIRecord()
    // {
    //     // fetching the raw data
    //     $data = new ModelPsiRecord();
    //     $data->select('equipment_id, date, shift, operator_name, checking_part');

    //     return DataTable::of($data)->toJson();
    // }

    public function fetchPSIDetail()
    {
        $data = new ModelPsiRecord();
        $query = $data->getPSIRecordDetails();

        return DataTable::of($query)->toJson();
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
}
