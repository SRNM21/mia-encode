<?php

return [

    'pdo_construct' => [env('DB_CONNECTION') . ":host=" . env('DB_HOSTNAME') . ";dbname=" . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD')],
    
    'pdo_errmode' => [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]

];