<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Services\LeaderboardService;

class LeaderboardsController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService
    ) {}

    public function show(Request $request) 
    {
        $filter = $request->get('filter') ?? 'today';
        $leaderboard = $this->leaderboardService->getTopAgents($filter);

        $podium = $leaderboard['podium'];
        $rankings = $leaderboard['rankings'];

        $this->view('leaderboards', [
            'filter' => $filter,
            'podium' => $podium,
            'rankings' => $rankings
        ]);
    }
}