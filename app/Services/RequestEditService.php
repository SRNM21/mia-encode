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

    public function markAsRead(string $id)
    {
        RequestEdit::update(['id' => $id], [
            'is_read' => true
        ]);
    }

    public function createRequestEdit(string $encoder, string $appId, ?string $oldAgent, ?string $newAgent)
    {
        return RequestEdit::create([
            'encoder' => $encoder,
            'app_id' => $appId,
            'old' => $oldAgent,
            'new' => $newAgent
        ]);
    }

    public function cancelRequestEdit(string $id)
    {
        RequestEdit::delete($id);
    }

    public function rejectRequestEdit(string $id)
    {
        RequestEdit::update(['id' => $id], [
            'status' => 'rejected',
            'datetime_action' => date('Y-m-d H:i:s')
        ]);
    }
}