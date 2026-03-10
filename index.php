<?php

use Dotenv\Dotenv;
use App\Core\Facades\DB;
use App\Core\Support\Session;

require 'vendor/autoload.php';
require 'bootstrap/app.php';

Session::start();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

DB::initializeDB();
date_default_timezone_set(config('app.time_zone'));

// Load the router file
require 'routes/web.php';

// echo '404';
require get_view('error.error-page');