<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use App\Models\PrestartInspection\ModelPsiRecord;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerEmailReport extends BaseController
{
    public function sentEmail()
    {
        //
    }

    public function getData1()
    {
        // 
        $model = new ModelPsiRecord();
        $data = $model->getDailyUniqueEquipmentType();
        return $this->response->setJSON($data);
    }
}
