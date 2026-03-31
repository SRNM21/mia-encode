<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;
use App\Http\Request\Request;
use App\Models\Bank;
use App\Models\BankApplication;
use App\Models\Client;
use App\Services\BankApplicationService;
use App\Services\ExportService;
use Exception;
use Throwable;

class BankApplicationController extends Controller
{
    public function __construct(
        private BankApplicationService $bankApplicationService,
        private ExportService $exportService
    ) {}

    public function show(Request $request)
    {
        $banks = Bank::getAll();
        $data = $this->bankApplicationService->getApplicationsData($request);
        
        return $this->view('applications', [
            'banks' => $banks,
            'applications' => $data['applications'],
            'meta' => $data['meta'],
            'request_map' => $data['request_map'],
        ]);
    }

    public function table(Request $request)
    {
        $banks = Bank::getAll();
        $data = $this->bankApplicationService->getApplicationsData($request);

        $html = render_component('applications-table', [
            'banks' => $banks,
            'applications' => $data['applications'],
            'request_map' => $data['request_map'],
            'user' => Auth::user()
        ]);

        return $this->responseJson([
            'html' => $html
        ]);
    }

    public function update(Request $request)
    {
        try
        {
            $update = $request->input('update');

            $this->bankApplicationService->update($update['app_id'], $update['data']);
            
            $this->responseJson([
                'title' => 'Edit Request Approved',
                'message' => 'Edit request is successfully approved.',
            ]);
        }
        catch (Throwable $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Approve request edit failed. [' . $e->getCode() .']'
            ], 500);
        }
    }
    
    public function checkEdit(Request $request) 
    {
        $id = $request->post('app_id');

        $bankApplication = BankApplication::get($id);
        $dateSubmitted = $bankApplication->date_submitted;

        $banks = model_list_to_array(Bank::class, Bank::getAll());

        $maxMonth = 0;
        foreach ($banks as $bank) 
        {
            $maxMonth = max($maxMonth, $bank['expiry_months']);
        }

        $isEditable = $this->bankApplicationService->isPastMaxMonths($dateSubmitted, $maxMonth);
        return $this->responseJson($isEditable);
    }

    public function edit(Request $request) 
    {
        try
        {
            $id = $request->get('id');
            $application = BankApplication::get($id);
            if (!$application) throw new Exception('Application not found');

            $client = Client::get($application->client_id);
            if (!$client) throw new Exception('Application not found');

            $latest_submission = $this->bankApplicationService->getClientApplications($client->id);
            $banks = model_list_to_array(Bank::class, Bank::getAll());
            $bank_list = sort_by($banks, 'name');
        }
        catch (Throwable $e)
        {
            return $this->view('error.error-page');
        }

        $bankRows = $this->bankApplicationService->buildTableRows(
            $bank_list,
            $latest_submission,
            $application
        );

        return $this->view('bank-application-edit', [
            'id' => $id,
            'application' => $application,
            'client' => $client,
            'banks' => $bank_list,
            'latest_submission' => $latest_submission,
            'bank_rows' => $bankRows
        ]);
    }

    public function preExport(Request $request) 
    {   
        $startDate = $request->post('start_date');
        $endDate = $request->post('end_date');

        $start = date('Y-m-d 00:00:00', strtotime($startDate));
        $end = date('Y-m-d 23:59:59', strtotime($endDate));

        $result = $this->bankApplicationService
            ->getEstimateTimeOfExport($start, $end);

        return $this->responseJson($result);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $this->exportService->exportBankApplications($startDate, $endDate);
    }
}
