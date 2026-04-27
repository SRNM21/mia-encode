<?php

namespace App\Repository;

use App\Core\Facades\DB;
use App\Models\Bank;
use App\Models\BankApplication;

class LeaderboardRepository
{
    public function getTopAgents(string $filter = 'today', ?string $searchAgent = null, ?string $fromDate = null, ?string $toDate = null): array
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
            ->whereNotNull('bank_submitted_id');

        if (!empty($searchAgent)) {
            $query->where('agent', 'LIKE', "%{$searchAgent}%");
        }

        switch ($filter) 
        {
            case 'today':
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;

            case 'week':
                $now = new \DateTimeImmutable('now');
                $monday = (int) $now->format('N') === 1 ? $now : $now->modify('monday this week');
                $friday = $monday->modify('+4 days');

                $start = $monday->format('Y-m-d') . ' 00:00:00';
                $end = $friday->format('Y-m-d') . ' 23:59:59';

                $query->where('date_submitted', '>=', $start)
                      ->where('date_submitted', '<=', $end);
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
            case 'custom':
                if ($fromDate && $toDate) {
                    $query->where('date_submitted', '>=', $fromDate . ' 00:00:00')
                          ->where('date_submitted', '<=', $toDate . ' 23:59:59');
                }
                break;
            default:
                // Default today
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;
        }

        $query->groupBy($normalizedAgent)
              ->orderBy('submissions', 'DESC');

        $results = $query->getArray();

        return $this->formatLeaderboard($results, !empty($searchAgent));
    }

    private function formatLeaderboard(array $agents, bool $isSearch = false): array
    {
        if ($isSearch) {
            return [
                'podium' => [
                    'first' => null,
                    'second' => null,
                    'third' => null,
                ],
                'rankings' => $agents
            ];
        }

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

    public function getBankLeaderboard(string $filter = 'today', ?string $searchAgent = null, ?string $fromDate = null, ?string $toDate = null) 
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
        
        if (!empty($searchAgent)) {
            $query->where('agent', 'LIKE', "%{$searchAgent}%");
        }

        switch ($filter) 
        {
            case 'today':
                $query->where('date_submitted', '>=', date('Y-m-d 00:00:00'))
                      ->where('date_submitted', '<=', date('Y-m-d 23:59:59'));
                break;

            case 'week':
                $now = new \DateTimeImmutable('now');
                $monday = (int) $now->format('N') === 1 ? $now : $now->modify('monday this week');
                $friday = $monday->modify('+4 days');

                $start = $monday->format('Y-m-d') . ' 00:00:00';
                $end = $friday->format('Y-m-d') . ' 23:59:59';

                $query->where('date_submitted', '>=', $start)
                      ->where('date_submitted', '<=', $end);
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
            case 'custom':
                if ($fromDate && $toDate) {
                    $query->where('date_submitted', '>=', $fromDate . ' 00:00:00')
                          ->where('date_submitted', '<=', $toDate . ' 23:59:59');
                }
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