<?php

// This is where you can initialize the main settings of the application
// including the directories/paths, files, cores, and others.

return [

    'app_name' => env('APP_NAME'), 
    'time_zone' => 'Asia/Manila',

    // Directory mapper for easier path requirements and configuration
    'public' => 'public/',
    'vendor' => 'vendor/',
    'routes' => 'routes/',
    'public_img' => 'public/images/',

    'resources' => [

        'views'         => 'resources/views/',
        'components'    => 'resources/views/components',
        'css'           => 'resources/css/',
        'js'            => 'resources/js/',
        'images'        => 'resources/images/',
        'icons'         => 'resources/icons/',
    ]

];