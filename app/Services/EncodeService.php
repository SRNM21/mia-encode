<?php 

namespace App\Services;

use App\Core\Facades\Auth;
use App\Http\Request\Request;
use App\Models\BankApplication;
use Exception;

class EncodeService 
{
    public function __construct(
        private ClientService $clientService, 
    ) {}

    public function saveApplication(Request $request)
    {
        $user = Auth::user();

        if (!$user->isEncoder()) 
        {
            throw new Exception('User must be an ecoder to encode data.');
        }

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
            'team' => $user->team
        ]);
    }
}