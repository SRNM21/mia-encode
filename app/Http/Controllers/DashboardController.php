<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;

class DashboardController extends Controller
{
    public function show()
    {
        $this->view('dashboard');
    }
}
