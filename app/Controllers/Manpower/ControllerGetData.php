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

        return DataTable::of($query)->toJson();
    }
}
