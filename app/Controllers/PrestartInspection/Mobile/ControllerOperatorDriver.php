<?php

namespace App\Controllers\PrestartInspection\Mobile;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerOperatorDriver extends BaseController
{
    public function index()
    {
        // data
        $data = [
            'pageTitle' => 'Operator-Driver | PSI',
        ];

        return view('pages/psi/mobile/mobile-operator_driver', $data);
    }
}
