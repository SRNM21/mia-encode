<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Services\BankApplicationService;
use App\Services\BankService;
use Exception;

class BankController extends Controller
{
    public function __construct(
        private BankService $bankService
    ) {}    

    public function show(Request $request) 
    {
        $data = $this->bankService->getPaginatedBanks($request);

        $this->view('banks', [
            'banks' => $data['banks'],
            'meta' => $data['meta']
        ]);
    }

    public function store(Request $request) 
    {
        try 
        {
            $bank = $this->bankService->createFromRequest($request);

            $this->responseJson([
                'title' => 'Bank Added',
                'message' => $request->post('bank_name') . ' is successfully added.',
                'bank' => $bank->toArray()
            ]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Bank failed to add. [' . $e->getCode() .']'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try 
        {
            $bank = $this->bankService->updateFromRequest($request);

            $this->responseJson([
                'title' => 'Bank Updated',
                'message' => $request->input('bank_name') . ' is successfully updated.',
                'bank' => $bank[0]->toArray()
            ]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Bank failed to update. [' . $e->getCode() .']'
            ], 500);
        }
    }

}