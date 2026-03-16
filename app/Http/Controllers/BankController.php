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
        private BankApplicationService $bankApplicationService,
        private BankService $bankService
    ) {}    

    public function show(Request $request) 
    {
        $page = (int) ($request->get('page') ?? 1);
        if ($page < 1) $page = 1;

        $perPage = (int) ($request->get('per_page') ?? 25);
        $allowed = [25, 50, 100, 500];
        if (!in_array($perPage, $allowed, true)) $perPage = 25;

        $sort = $request->get('sort') ?? 'name';
        $order = strtolower($request->get('order') ?? 'desc');
        $allowedSort = [
            'name',
            'short_name',
            'expiry_months',
            'is_active',
            'total',
            'created_at',
            'updated_at'
        ];

        if (!in_array($sort, $allowedSort, true)) 
        {
            $sort = 'name';
        }

        if (!in_array($order, ['asc','desc'], true)) 
        {
            $order = 'desc';
        }

        $result = $this->bankService
            ->getBanksWithApplicationCount($page, $perPage, $sort, $order);

        $banks = $result['data'] ?? [];
        $meta = $result['meta'] ?? [
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => 1
        ];

        $this->view('banks', [
            'banks' => $banks,
            'meta' => $meta
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