<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Models\Bank;
use App\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService
    ) {}

    public function show(Request $request) 
    {
        $filter_sub = $request->get('filter-submission') ?? 'today';
        $filter_bank = $request->get('filter-banks') ?? 'today';
        $search_agent = $request->get('search');
        
        $custom_from = $request->get('custom_from');
        $custom_to = $request->get('custom_to');

        $query_from = $custom_from ? date('Y-m-d', strtotime($custom_from)) : null;
        $query_to = $custom_to ? date('Y-m-d', strtotime($custom_to)) : null;

        $filter_sub_display = $this->getFilterDateDisplay($filter_sub, $custom_from, $custom_to);
        $filter_bank_display = $this->getFilterDateDisplay($filter_bank, $custom_from, $custom_to);

        $leaderboard = $this->leaderboardService->getTopAgents($filter_sub, $search_agent, $query_from, $query_to);
        $podium = $leaderboard['podium'];
        $rankings = $leaderboard['rankings'];

        $banks = Bank::getAll();
        $bank_leaderboards = $this->leaderboardService->getBankLeaderboard($filter_bank, $search_agent, $query_from, $query_to);

        $this->view('leaderboards', [
            'filter_sub' => $filter_sub,
            'filter_bank' => $filter_bank,
            'filter_sub_display' => $filter_sub_display,
            'filter_bank_display' => $filter_bank_display,
            'search_agent' => $search_agent,
            'custom_from' => $custom_from,
            'custom_to' => $custom_to,
            'podium' => $podium,
            'rankings' => $rankings,
            'bank_leaderboards' => $bank_leaderboards,
            'banks' => $banks
        ]);
    }

    private function getFilterDateDisplay($filter, $custom_from, $custom_to) 
    {
        if ($filter == 'custom' && $custom_from && $custom_to) {
            return date('M d, Y', strtotime($custom_from)) . ' - ' . date('M d, Y', strtotime($custom_to));
        }

        switch ($filter) {
            case 'week':
                $dto = new \DateTime();
                $dto->setISODate((int)$dto->format('o'), (int)$dto->format('W')); // Monday
                $start = $dto->format('M d, Y');
                $dto->modify('+4 days');
                $end = $dto->format('M d, Y');
                return $start . ' - ' . $end;
            case 'month':
                return date('F Y');
            case 'year':
                return date('Y');
            case 'all':
                return 'All Time';
            case 'today':
            default:
                return date('M d, Y');
        }
    }
}