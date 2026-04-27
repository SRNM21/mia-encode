<?php 

namespace App\Services;

use App\Repository\LeaderboardRepository;

class LeaderboardService 
{
    public function __construct(
        private LeaderboardRepository $leaderboardRepository,
    ) {}

    public function getTopAgents(string $filter = 'today', ?string $searchAgent = null, ?string $fromDate = null, ?string $toDate = null)
    {
        return $this->leaderboardRepository->getTopAgents($filter, $searchAgent, $fromDate, $toDate);
    }

    public function getBankLeaderboard(string $filter = 'today', ?string $searchAgent = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        return $this->leaderboardRepository->getBankLeaderboard($filter, $searchAgent, $fromDate, $toDate);
    }
}