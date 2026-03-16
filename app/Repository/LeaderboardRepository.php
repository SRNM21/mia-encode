<?php

namespace App\Repository;

use App\Core\Facades\DB;
use App\Http\Request\Request;
use App\Models\BankApplication;

class LeaderboardRepository
{
    public function getTopAgents(string $filter = 'today'): array
    {
        $normalizedAgent = "
            TRIM(
                REGEXP_REPLACE(
                    TRIM(SUBSTRING_INDEX(agent, '/', 1)),
                    ' P$',
                    ''
                )
            )";

        $query = BankApplication::query()
            ->select("$normalizedAgent AS agent", "COUNT(*) AS submissions")
            ->groupBy($normalizedAgent)
            ->orderBy('submissions', 'DESC')
            ->limit(100);

        switch ($filter) {

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
}