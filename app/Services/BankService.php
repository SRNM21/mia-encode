<?php

namespace App\Services;

use App\Http\Request\Request;
use App\Repository\BankRepository;

class BankService
{
    public function __construct(
        private BankRepository $bankRepository
    ) {}

    public function createFromRequest(Request $request) 
    {
        return $this->create([
            'name' => $request->post('bank_name'),
            'short_name' => $request->post('short_bank_name'),
            'expiry_months' => $request->post('expiry_months'),
            'is_active' => $request->post('bank_status') == 'ACTIVE'
        ]);
    }

    public function create(array $data) 
    {
        return $this->bankRepository->create($data);
    }

    public function updateFromRequest(Request $request) 
    {
        return $this->update(
            $request->input('id'), 
            [
                'name' => $request->input('bank_name'),
                'short_name' => $request->input('short_bank_name'),
                'expiry_months' => $request->input('expiry_months'),
                'is_active' => $request->input('bank_status') == 'ACTIVE'
            ]
        );
    }

    public function update(string $id, array $data)
    {
        return $this->bankRepository->update($id, $data);
    }

    public function getBanksWithApplicationCount(
        int $page = 1,
        int $perPage = 25,
        string $sort = 'name',
        string $order = 'desc'
    ) {
        return $this->bankRepository->getBanksWithApplicationCount($page, $perPage, $sort, $order);
    }

}