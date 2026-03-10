<?php

namespace App\Repository;

use App\Http\Request\Request;
use App\Models\Client;

class EncodeRepository
{
    public function getClient(Request $request)
    {
        $date = $request->post('birthdate');
        $date = date('Y-m-d', strtotime($date));

        return Client::where('first_name', '=', $request->post('firstname'))
            ->where('middle_name', '=', $request->post('middlename'))
            ->where('last_name', '=', $request->post('lastname'))
            ->where('birthdate', '=', $date)
            ->where('mobile_num', '=', $request->post('mobile'))
            ->first();
    }
}