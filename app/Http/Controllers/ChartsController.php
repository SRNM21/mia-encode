<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Services\BankApplicationService;
use App\Services\ClientService;

class ChartsController extends Controller
{
    public function __construct(
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
        $data = $this->bankApplicationService->getAgentsLeaderboards($request->post('scope'));

        $this->responseJson($data);
    }
}
