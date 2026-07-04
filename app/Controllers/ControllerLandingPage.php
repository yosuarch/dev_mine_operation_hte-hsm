<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerLandingPage extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Welcome | MOIMS',
            'pageSubTitle' => 'none'
        ];
        return view('/pages/landing-page/index', $data);
    }
}
