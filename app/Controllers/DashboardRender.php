<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardRender extends BaseController
{

    public function index()
    {
        $data = [
            'name' => 'timoti',
            'message' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quod modi commodi velit doloremque molestias expedita maxime quis quia recusandae aliquid repudiandae ad magnam omnis est deserunt obcaecati voluptatem quae, eos voluptates illo nulla at quos debitis iste. Minus quidem velit eligendi, sapiente repudiandae laboriosam repellat blanditiis nihil aliquam voluptatum cumque.'
        ];
        return view('pages/idx-dashboard', $data);
    }
}
