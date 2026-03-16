<?php 

namespace App\Services;

use App\Repository\LeaderboardRepository;

class LeaderboardService 
{
    public function __construct(
        private LeaderboardRepository $leaderboardRepository
    ) {}

    public function getTopAgents(string $filter = 'today')
    {
        return $this->leaderboardRepository->getTopAgents($filter);
    }
}