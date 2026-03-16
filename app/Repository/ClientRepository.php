<?php

namespace App\Repository;

use App\Http\Request\Request;
use App\Models\Client;

class ClientRepository
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

    public function create(array $data) 
    {
        return Client::create($data);
    }

    public function updateLatestApplicationDate(int $id) 
    {
        return Client::update(['id' => $id], [
            'latest_application' => date('Y-m-d'),
        ]);
    }
}