<?php

namespace App\Controllers\PrestartInspection;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerPSI extends BaseController
{
    public function index()
    {
        // render the view
        $data = [
            'pageTitle' => 'Inspection Sheet | PSI',
            'pageSubTitle' => 'none'
        ];
        return view('/pages/psi/idx-psi', $data);
    }
}
