<?php

namespace App\Controllers\Manpower;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerManpower extends BaseController
{
    public function index()
    {
        // render the view
        $data = [
            'pageTitle' => 'Manpower Database | Manpower',
            'pageSubTitle' => 'none'
        ];
        return view('/pages/manpower/idx-manpower', $data);
    }
}
