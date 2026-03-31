<?php

namespace App\Repository;

use App\Models\BankApplication;
use App\Core\Facades\Auth;

class BankApplicationRepository
{
    public function getTodaysApplicationsByBank(): array
    {
        return BankApplication::query()
            ->select('bank_submitted_id')
            ->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
            ->where('date_submitted', '<',  date('Y-m-d 00:00:00', strtotime('+1 day')))
            ->getRaw(true)['results'];
    }

    public function getClientApplications(int $client_id)
    {
        return BankApplication::query()
            ->select('id, date_submitted, bank_submitted_id, agent')
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
                'bank_application_tbl.bank_submitted_id AS bank_submitted_id',
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
    ): array {

        $query = BankApplication::query()
            ->select(
                'bank_application_tbl.id',
                'bank_application_tbl.date_submitted',
                'bank_application_tbl.agent',

                'client_tbl.id AS client_id',
                'client_tbl.first_name',
                'client_tbl.middle_name',
                'client_tbl.last_name',
                'client_tbl.birthdate',
                'client_tbl.mobile_num',

                'bank_application_tbl.bank_submitted_id AS banks'
            )
            ->join(
                'client_tbl',
                'bank_application_tbl.client_id',
                '=',
                'client_tbl.id',
                'LEFT'
            );

        // Date Filters
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        if ($startDate && $endDate) 
        {
            $query->where('bank_application_tbl.date_submitted', '>=', $startDate)
                ->where('bank_application_tbl.date_submitted', '<=', $endDate);
        } 
        elseif ($startDate) 
        {
            $query->where('bank_application_tbl.date_submitted', '>=', $startDate);
        } 
        elseif ($endDate) 
        {
            $query->where('bank_application_tbl.date_submitted', '<=', $endDate);
        }

        // Only on team
        // $user = Auth::user();
        // if ($user && $user->team) 
        // {
        //     $query->where('bank_application_tbl.team', '=', $user->team);
        // }

        unset($filters['start_date'], $filters['end_date']);

        // Other Filters
        foreach ($filters as $key => $value) 
        {
            if ($value === '' || $value === null) continue;
            $query->where($key, 'LIKE', '%' . $value . '%');
        }

        // Sorting
        $sortMap = [
            'date_submitted' => 'bank_application_tbl.date_submitted',
            'last_name' => 'client_tbl.last_name',
            'first_name' => 'client_tbl.first_name',
            'middle_name' => 'client_tbl.middle_name',
            'birthdate' => 'client_tbl.birthdate',
        ];

        if (isset($sortMap[$sort])) 
        {
            $sort = $sortMap[$sort];
        }

        // Pagination
        return $query
            ->orderBy($sort, strtoupper($order))
            ->paginate($page, $perPage, true);
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
            ->select('*')
            ->orderBy('date_submitted', 'ASC')
            ->getArray();
    }

    public function getAgentsApplicationCountByRange(string $startDate, string $endDate): array
    {
        dd([
            'start' => $startDate,
            'end' => $endDate
        ]);
        
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

    public function getByDateRange(string $start, string $end): array 
    { 
        return BankApplication::query() 
        ->select( 
            'bank_application_tbl.date_submitted', 
            'bank_application_tbl.bank_submitted_id' 
        ) 
        ->where('DATE(bank_application_tbl.date_submitted)', '>=', $start) 
        ->where('DATE(bank_application_tbl.date_submitted)', '<=', $end) 
        ->orderBy('date_submitted', 'ASC') 
        ->getArray(); 
    }
}
