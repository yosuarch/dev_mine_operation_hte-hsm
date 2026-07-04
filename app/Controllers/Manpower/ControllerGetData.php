<?php

namespace App\Controllers\Manpower;

use App\Controllers\BaseController;
use App\Models\ManPowerList;
use Hermawan\DataTables\DataTable;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerGetData extends BaseController
{

    public function fetchManPowerList()
    {
        //
        $data = new ManPowerList();
        $query = $data->fetchManpPowerList();

        // returnAsObject=true so the view can read named fields (row.idx, row.name, ...)
        return DataTable::of($query)->toJson(true);
    }

    public function kpiData()
    {
        $model = new ManPowerList();

        return $this->response->setJSON([
            'kpi'    => $model->fetchGenderKpi(),
            'gender' => $model->fetchGenderOptions(),
            'roles'  => $model->fetchRoleOptions(),
        ]);
    }
}
