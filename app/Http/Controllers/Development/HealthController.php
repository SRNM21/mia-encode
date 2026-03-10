<?php

namespace App\Http\Controllers\Development;

use App\Core\Facades\DB;
use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;

class HealthController extends Controller
{
    public function __invoke()
    {
        echo 'Database is ' . (DB::isConnected() ? 'connected' : 'disconnected');
        echo '<br/>';
        echo Auth::check() ? 'Signed In' : 'Signed Out';
    }
}