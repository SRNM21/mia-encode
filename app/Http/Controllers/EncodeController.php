<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\CheckClientApplicationRequest;
use App\Http\Request\StoreClientApplicationRequest;
use App\Models\Bank;
use App\Models\BankApplication;
use App\Services\BankApplicationService;
use App\Services\ClientService;
use App\Services\EncodeService;
use Exception;
use Throwable;

class EncodeController extends Controller
{   
    public function __construct(
        private BankApplicationService $bankApplicationRepository,
        private ClientService $clientService,
        private EncodeService $encodeService
    ) {}

    public function show()
    {
        $this->view('encode');
    }

    public function check(CheckClientApplicationRequest $request) 
    {
        $error = get_error();

        if ($error)
        {
            return $this->responseJson([
                'title' => 'Error Occured',
                'errors' => $error
            ], 400);
        }

        try 
        {   
            $client = $this->clientService->getClient($request);
        }
        catch (Exception $e) 
        {
            return $this->responseJson([
                'title' => 'Error Occured',
                'message' => $e->getMessage()
            ], 500);
        }

        $banks = model_list_to_array(Bank::class, Bank::getAll());
        
        if ($client) 
        {
            $applications = $this->bankApplicationRepository->getClientApplications($client->id);

            return $this->responseJson([
                'client' => $client->toArray(),
                'applications' => model_list_to_array(BankApplication::class, $applications),
                'banks' => $banks,
            ]);
        }
        else 
        {
            return $this->responseJson([
                'banks' => $banks,
            ]);
        }
    }

    public function store(StoreClientApplicationRequest $request) 
    {   
        $error = get_error();

        if ($error)
        {
            return $this->responseJson([
                'title' => 'Error Occured',
                'errors' => $error
            ], 400);
        }

        try
        {
            $this->encodeService->saveApplication($request);

            $this->responseJson([
                'title' => 'Application Submitted',
                'message' => 'Your application has been submitted successfully.',
            ]);
        }
        catch (Throwable $e)
        {
            return $this->responseJson([
                'title' => 'Error Occured',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
