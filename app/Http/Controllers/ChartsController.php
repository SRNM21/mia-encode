<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Services\BankApplicationService;
use App\Services\ClientService;
use App\Services\LeaderboardService;

class ChartsController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService,
        private BankApplicationService $bankApplicationService,
        private ClientService $clientService
    ) {}

    public function clientTypeToday()
    {
        $data = $this->clientService->clientTypeToday();
        $this->responseJson($data);
    }

    public function clientTypeSeries(Request $request)
    {   
        $data = $this->clientService->clientTypeSeries(
            $request->post('scope'),
            $request->post('year')
        );

        $this->responseJson($data);
    }

    public function bankAppsToday()
    {
        $applications = $this->bankApplicationService->applicationsTodayByBank();
        $this->responseJson($applications);
    }

    public function bankAppsSeries(Request $request)
    {
        $data = $this->bankApplicationService->bankApplicationsSeries(
            $request->post('scope'),
            $request->post('year')
        );

        $this->responseJson($data);
    }

    public function agentsLeaderboards(Request $request)
    {   
        $filter = $request->post('scope');
        $leaderboard = $this->leaderboardService->getTopAgents($filter);

        $result = [];

        foreach (['first', 'second', 'third'] as $place) 
        {
            if (!empty($leaderboard['podium'][$place])) 
            {
                $result[] = $leaderboard['podium'][$place];
            }
        }

        $result = array_merge($result, $leaderboard['rankings'] ?? []);

        $this->responseJson($result);
    }

    public function weeklyCalendar(Request $request)
    {
        $year = (int)$request->post('year');
        $month = (int)$request->post('month');

        $data = $this->bankApplicationService->getMonthlyWeeklyCalendar($year, $month);

        $this->responseJson($data);
    }
}