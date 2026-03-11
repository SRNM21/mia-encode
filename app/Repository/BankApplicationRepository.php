<?php

namespace App\Repository;

use App\Models\BankApplication;

class BankApplicationRepository
{
    public function getTodaysApplicationsByBank(): array
    {
        return BankApplication::query()
            ->select(
                'bank_list_tbl.name AS bank_name',
                'COUNT(bank_application_tbl.id) AS count'
            )
            ->join(
                'bank_list_tbl',
                'bank_application_tbl.bank_submitted_id',
                '=',
                'bank_list_tbl.id',
                'LEFT'
            )            
            ->where('date_submitted', '=', date('Y-m-d'))
            ->groupBy('bank_name')
            ->orderBy('bank_name', 'ASC')
            ->getArray();
    }

    public function getClientApplications(int $client_id): array
    {
        return BankApplication::query()
            ->select(
                'bank_application_tbl.*', 
                'bank_list_tbl.name AS bank_name',
                'bank_list_tbl.expiry_months AS expiry_months',
            )
            ->join('bank_list_tbl', 'bank_application_tbl.bank_submitted_id', '=', 'bank_list_tbl.id', 'LEFT')
            ->where('client_id', '=', $client_id)
            ->orderBy('date_submitted', 'ASC')
            ->getArray();
    }

    public function getNumberOfDataWithinDateRange(string $startDate, string $endDate)
    {
        return BankApplication::query()
            ->select('COUNT(*) AS total')
            ->where('date_submitted', '>=', $startDate)
            ->where('date_submitted', '<=', $endDate)
            ->getRaw()['results'];
    }

    public function getApplicationsToExportChunk(int $limit, int $lastId)
    {
        return BankApplication::query()
            ->select(
                'bank_application_tbl.id AS app_id',
                'bank_application_tbl.date_submitted AS date_submitted',
                'bank_application_tbl.agent AS agent',
                'client_tbl.first_name AS firstname',
                'client_tbl.middle_name AS middlename',
                'client_tbl.last_name AS lastname',  
                'client_tbl.birthdate AS birthdate',  
                'client_tbl.mobile_num AS mobile_num',
                'bank_list_tbl.short_name AS bank_short_name'
            )
            ->join('client_tbl', 'bank_application_tbl.client_id', '=', 'client_tbl.id', 'LEFT')
            ->join('bank_list_tbl', 'bank_application_tbl.bank_submitted_id', '=', 'bank_list_tbl.id', 'LEFT')
            ->where('bank_application_tbl.id','>', $lastId)
            ->orderBy('bank_application_tbl.id','ASC')
            ->limit($limit)
            ->getRaw()['results'] ?? [];
    }

    /**
     * Get a raw cursor for all applications to export.
     *
     * @return \Generator<array>
     */
    public function getExportCursor(string $startDate, string $endDate): \Generator
    {
        return BankApplication::query()
            ->select(
                'bank_application_tbl.id AS app_id',
                'bank_application_tbl.date_submitted AS date_submitted',
                'bank_application_tbl.agent AS agent',
                'client_tbl.first_name AS firstname',
                'client_tbl.middle_name AS middlename',
                'client_tbl.last_name AS lastname',  
                'client_tbl.birthdate AS birthdate',  
                'client_tbl.mobile_num AS mobile_num',
                'bank_list_tbl.short_name AS bank_short_name'
            )
            ->join('client_tbl', 'bank_application_tbl.client_id', '=', 'client_tbl.id', 'LEFT')
            ->join('bank_list_tbl', 'bank_application_tbl.bank_submitted_id', '=', 'bank_list_tbl.id', 'LEFT')
            ->where('date_submitted', '>=', $startDate)
            ->where('date_submitted', '<=', $endDate)
            ->orderBy('bank_application_tbl.id', 'ASC')
            ->rawCursor();
    }

    public function getPaginatedApplicationsWithRelations(
        int $page = 1,
        int $perPage = 25,
        array $filters = [],
        string $sort = 'date_submitted',
        string $order = 'desc'
    ): array
    {
        $query = BankApplication::query()
            ->select(
                'bank_application_tbl.*',
                'client_tbl.first_name AS firstname',
                'client_tbl.middle_name AS middlename',
                'client_tbl.last_name AS lastname',  
                'client_tbl.birthdate AS birthdate',  
                'client_tbl.mobile_num AS mobile_num',
                'bank_list_tbl.name AS bank_name',
                'bank_list_tbl.id AS bank_id',
                'bank_list_tbl.short_name AS bank_short_name'
            )
            ->join('client_tbl', 'bank_application_tbl.client_id', '=', 'client_tbl.id', 'LEFT')
            ->join('bank_list_tbl', 'bank_application_tbl.bank_submitted_id', '=', 'bank_list_tbl.id', 'LEFT');
        
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        if ($startDate && $endDate) 
        {
            $query->where('date_submitted', '>=', $startDate);
            $query->where('date_submitted', '<=', $endDate);
        } 
        else if ($startDate) 
        {
            $query->where('date_submitted', '<=', $startDate);
        } 
        else if ($endDate)
        {
            $query->where('date_submitted', '>=', $endDate);
        } 

        unset($filters['start_date'], $filters['end_date']);

        foreach ($filters as $key => $value) 
        {
            if ($value === '' || $value === null) continue;
            $query->where($key, '=', $value);
        }

        return $query
            ->orderBy($sort, strtoupper($order))
            ->paginate($page, $perPage);
    }

    public function getAvailableYears(): array
    {
        return BankApplication::query()
            ->select('YEAR(date_submitted) AS year')
            ->groupBy('year')
            ->orderBy('year', 'ASC')
            ->getArray();
    }

    public function getAllWithBankAndDate(): array
    {
        return BankApplication::query()
            ->select(
                'bank_application_tbl.date_submitted AS date_submitted',
                'bank_application_tbl.bank_submitted_id AS bank_id',
                'bank_list_tbl.name AS bank_name'
            )
            ->join('bank_list_tbl', 'bank_application_tbl.bank_submitted_id', '=', 'bank_list_tbl.id', 'LEFT')
            ->orderBy('date_submitted', 'ASC')
            ->getArray();
    }

    public function getAgentsApplicationCountByRange(string $startDate, string $endDate): array
    {
        return BankApplication::query()
            ->select(
                'agent',
                'COUNT(id) AS count'
            )
            ->where('date_submitted', '>=', $startDate)
            ->where('date_submitted', '<=', $endDate)
            ->whereNotNull('agent')
            ->groupBy('agent')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->getRaw()['results'];
    }
}
