<?php

namespace App\Repository;

use App\Models\Bank;

class BankRepository
{
    public function create(array $data) 
    {
        return Bank::create($data);
    }

    public function update(string $id, array $data) 
    {
        return Bank::update(['id' => $id], $data);
    }

    public function getBanksWithApplicationCount(
        int $page,
        int $perPage,
        string $sort,
        string $order
    ): array {
        return Bank::query()
            ->select(
                'bank_list_tbl.*',
                'COUNT(bank_application_tbl.id) AS total'
            )
            ->join(
                'bank_application_tbl',
                'bank_list_tbl.id',
                '=',
                'bank_application_tbl.bank_submitted_id',
                'LEFT'
            )
            ->groupBy('bank_list_tbl.id')
            ->orderBy($sort, strtoupper($order))
            ->paginate($page, $perPage);
    }
}