<?php

namespace App\Controllers\HaulingActivity;

use App\Controllers\BaseController;

class ControllerHaulingActivity extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Hauling Activity | Reports',
        ];
        return view('pages/hauling_activity/idx-hauling-activity', $data);
    }
}
