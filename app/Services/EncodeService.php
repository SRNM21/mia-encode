<?php 

namespace App\Services;

use App\Http\Request\Request;
use App\Models\BankApplication;

class EncodeService 
{
    public function __construct(
        private ClientService $clientService, 
    ) {}

    public function saveApplication(Request $request)
    {
        if ($request->post('client_id') != null)
        {
            $client_id = $request->post('client_id');
            $this->clientService->updateLatestApplicationDate($client_id);
        }
        else 
        {
            $client = $this->clientService->createClientFromRequest($request);
            $client_id = $client->id;
        }

        $bank_applications = array_map('intval', $request->post('banks'));

        BankApplication::create([
            'client_id' => $client_id,
            'bank_submitted_id' => json_encode($bank_applications),
            'agent' => $request->post('agent'),
        ]);
    }
}