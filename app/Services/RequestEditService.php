<?php

namespace App\Services;

use App\Models\BankApplication;
use App\Models\RequestEdit;
use App\Repository\RequestEditRepository;

class RequestEditService
{
    public function __construct(
        private RequestEditRepository $requestEditRepository
    ) {}

    public function fetchRequest(string $order, string $filter) 
    {
        return $this->requestEditRepository->fetchRequest($order, $filter);
    }

    public function stillExists(string $id)
    {
        return RequestEdit::get($id);
    }

    public function update(string $id, array $data) 
    {
        RequestEdit::update(['id' => $id], [
            'status' => 'approved',
            'datetime_action' => date('Y-m-d H:i:s')
        ]);

        BankApplication::update(['id' => $data['app_id']], [
            'agent' => $data['new_data']
        ]);
    }

}