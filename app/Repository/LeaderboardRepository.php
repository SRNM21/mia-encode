<?php

namespace App\Repository;

use App\Core\Facades\DB;
use App\Models\Bank;
use App\Models\BankApplication;

class LeaderboardRepository
{
    public function getTopAgents(string $filter = 'today'): array
    {
        $normalizedAgent = "
            TRIM(
                REGEXP_REPLACE(
                    TRIM(SUBSTRING_INDEX(agent, '/', 1)),
                    ' (P|TM)$',
                    ''
                )
            )";

        $query = BankApplication::query()
            ->select(
                "$normalizedAgent AS agent",
                "SUM(JSON_LENGTH(bank_submitted_id)) AS submissions"
            )
            ->whereNotNull('bank_submitted_id')
            ->groupBy($normalizedAgent)
            ->orderBy('submissions', 'DESC');

        switch ($filter) 
        {
            case 'today':
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;

            case 'week':
                $yearWeek = DB::getPDO()
                    ->query("SELECT YEARWEEK(CURDATE(),1)")
                    ->fetchColumn();

                $query->where('YEARWEEK(date_submitted,1)', '=', $yearWeek);
                break;

            case 'month':
                $query->where('YEAR(date_submitted)', '=', date('Y'))
                      ->where('MONTH(date_submitted)', '=', date('m'));
                break;

            case 'year':
                $query->where('YEAR(date_submitted)', '=', date('Y'));
                break;

            case 'all':
                break;
            default:
                // Default today
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;
        }

        $results = $query->getArray();

        return $this->formatLeaderboard($results);
    }

    private function formatLeaderboard(array $agents): array
    {
        $podium = array_slice($agents, 0, 3);
        $table = array_slice($agents, 3);

        return [
            'podium' => [
                'first' => $podium[0] ?? null,
                'second' => $podium[1] ?? null,
                'third' => $podium[2] ?? null,
            ],
            'rankings' => $table
        ];
    }

    public function getBankLeaderboard(string $filter = 'today') 
    {
        $normalizedAgent = "
            TRIM(
                REGEXP_REPLACE(
                    TRIM(SUBSTRING_INDEX(agent, '/', 1)),
                    ' (P|TM)$',
                    ''
                )
            )";

        $banks = Bank::query()->getArray();

        $bankMap = [];

        foreach ($banks as $bank) $bankMap[$bank['id']] = $bank['name'];

        $query = BankApplication::query()
            ->select("id, bank_submitted_id, $normalizedAgent AS agent");
        
        switch ($filter) 
        {
            case 'today':
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;

            case 'week':
                $yearWeek = DB::getPDO()
                    ->query("SELECT YEARWEEK(CURDATE(),1)")
                    ->fetchColumn();

                $query->where('YEARWEEK(date_submitted,1)', '=', $yearWeek);
                break;

            case 'month':
                $query->where('YEAR(date_submitted)', '=', date('Y'))
                      ->where('MONTH(date_submitted)', '=', date('m'));
                break;

            case 'year':
                $query->where('YEAR(date_submitted)', '=', date('Y'));
                break;

            case 'all':
                break;
            default:
                // Default today
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;
        }

        $applications = $query->getArray();
        $agentBankCounts = [];

        foreach ($applications as $app) 
        {
            $agent = $app['agent'];

            if (!isset($agentBankCounts[$agent])) 
            {
                $agentBankCounts[$agent] = [
                    'agent' => $agent,
                    'banks' => [],
                    'total' => 0
                ];

                foreach ($bankMap as $bankId => $bankName) 
                {
                    $agentBankCounts[$agent]['banks'][$bankId] = 0;
                }
            }

            $bankIds = json_decode($app['bank_submitted_id'], true) ?: [];

            foreach ($bankIds as $bankId) 
            {
                if (!isset($agentBankCounts[$agent]['banks'][$bankId])) continue;

                $agentBankCounts[$agent]['banks'][$bankId]++;
                $agentBankCounts[$agent]['total']++;
            }
        }

        $result = [];

        foreach ($agentBankCounts as $agentData) 
        {
            $row = [
                'agent' => $agentData['agent'],
                'total' => $agentData['total'],
            ];

            foreach ($bankMap as $bankId => $bankName)
            {
                $row[$bankName] = $agentData['banks'][$bankId];
            }

            $result[] = $row;
        }

        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);

        return $result;
    }
}