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
        $banks = $this->bankRepository->getBanksWithApplicationCount($page, $perPage, $sort, $order);
        $counts = $this->countBankApplications();

        foreach ($banks['data'] as &$bank) 
        {
            $bank['total'] = $counts[$bank['id']] ?? 0;
        }

        return $banks;
    }

    public function getPaginatedBanks(Request $request): array
    {
        $page = (int) ($request->get('page') ?? 1);
        if ($page < 1) $page = 1;

        $perPage = (int) ($request->get('per_page') ?? 25);
        $allowed = [25, 50, 100, 500];
        if (!\in_array($perPage, $allowed, true)) $perPage = 25;

        $sort = $request->get('sort') ?? 'id';
        $order = strtolower($request->get('order') ?? 'asc');
        $allowedSort = [
            'id',
            'name',
            'short_name',
            'expiry_months',
            'is_active',
            'total',
            'created_at',
            'updated_at'
        ];

        if (!\in_array($sort, $allowedSort, true)) 
        {
            $sort = 'id';
        }

        if (!\in_array($order, ['asc','desc'], true)) 
        {
            $order = 'asc';
        }

        $result = $this->getBanksWithApplicationCount($page, $perPage, $sort, $order);

        $banks = $result['data'] ?? [];
        $meta = $result['meta'] ?? [
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => 1
        ];
        
        return [
            'banks' => $banks,
            'meta' => $meta
        ];
    }

    public function countBankApplications(): array
    {
        $applications = $this->bankRepository->getBankApplicationBanks();
        $counts = [];

        foreach ($applications as $app) 
        {
            $banks = json_decode($app['bank_submitted_id'], true);

            if (!\is_array($banks)) continue;

            foreach ($banks as $bankId) 
            {
                if (!isset($counts[$bankId])) $counts[$bankId] = 0;

                $counts[$bankId]++;
            }
        }

        return $counts;
    }

}