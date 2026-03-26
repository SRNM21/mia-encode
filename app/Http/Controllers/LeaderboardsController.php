<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Models\Bank;
use App\Services\LeaderboardService;

class LeaderboardsController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService
    ) {}

    public function show(Request $request) 
    {
        $filter_sub = $request->get('filter-submission') ?? 'today';
        $leaderboard = $this->leaderboardService->getTopAgents($filter_sub);

        $podium = $leaderboard['podium'];
        $rankings = $leaderboard['rankings'];

        $banks = Bank::getAll();
        $filter_bank = $request->get('filter-banks') ?? 'today';
        $bank_leaderboards = $this->leaderboardService->getBankLeaderboard($filter_bank);

        $this->view('leaderboards', [
            'filter_sub' => $filter_sub,
            'filter_bank' => $filter_bank,
            'podium' => $podium,
            'rankings' => $rankings,
            'bank_leaderboards' => $bank_leaderboards,
            'banks' => $banks
        ]);
    }
}