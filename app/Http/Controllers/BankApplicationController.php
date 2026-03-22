<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;
use App\Http\Request\Request;
use App\Models\Bank;
use App\Models\BankApplication;
use App\Models\Client;
use App\Models\RequestEdit;
use App\Services\BankApplicationService;
use avadim\FastExcelWriter\Excel;
use avadim\FastExcelWriter\Style\Style;
use Exception;
use Throwable;

class BankApplicationController extends Controller
{
    public function __construct(
        private BankApplicationService $bankApplicationService,
    ) {}

    private function getApplicationsData(Request $request): array
    {
        $page = (int) ($request->get('page') ?? 1);
        if ($page < 1) $page = 1;

        $perPage = (int) ($request->get('per_page') ?? 25);
        $allowed = [25, 50, 100, 500];
        if (!in_array($perPage, $allowed, true)) $perPage = 25;

        $sort = $request->get('sort') ?? 'date_submitted';
        $order = strtolower($request->get('order') ?? 'desc');

        $allowedSort = [
            'date_submitted',
            'last_name',
            'first_name',
            'middle_name',
            'birthdate'
        ];

        if (!in_array($sort, $allowedSort, true)) 
        {
            $sort = 'date_submitted';
        }

        if (!in_array($order, ['asc','desc'], true)) 
        {
            $order = 'desc';
        }

        $available_queries = [
            'last_name',
            'first_name',
            'middle_name',
            'birthdate',
            'mobile_num',
            'start_date',
            'end_date',
        ];

        $filters = [];
        foreach ($available_queries as $key) 
        {
            if ($request->get($key)) 
            {
                $filters[$key] = $request->get($key);
            }
        }

        $result = $this->bankApplicationService
            ->getClientsPage($page, $perPage, $filters, $sort, $order);

        $clientsApplications = $result['data'] ?? [];
        $meta = $result['meta'] ?? [
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => 1
        ];

        foreach ($clientsApplications as &$application) 
        {
            if (isset($application['banks'])) 
            {
                $application['banks'] = is_array($application['banks'])
                    ? $application['banks']
                    : json_decode($application['banks'], true) ?? [];
            } 
            else 
            {
                $application['banks'] = [];
            }
        }

        return [
            'applications' => $clientsApplications,
            'meta' => $meta
        ];
    }

    public function show(Request $request)
    {
        $banks = Bank::getAll();
        $data = $this->getApplicationsData($request);
        $requests = RequestEdit::query()
            ->where('status', '=', 'pending')
            ->orderBy('datetime_request', 'ASC')
            ->getArray();

        $request_map = [];
        foreach ($requests as $request)
        {
            $request_map[$request['app_id']] = $request;
        }
        
        // dd($request_map);

        return $this->view('applications', [
            'banks' => $banks,
            'applications' => $data['applications'],
            'meta' => $data['meta'],
            'request_map' => $request_map
        ]);
    }

    public function table(Request $request)
    {
        $banks = Bank::getAll();
        $data = $this->getApplicationsData($request);

        $html = render_component('applications-table', [
            'banks' => $banks,
            'applications' => $data['applications'],
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

        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        $result = $this->bankApplicationService
            ->getEstimateTimeOfExport($start, $end);

        return $this->responseJson($result);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        set_time_limit(0);

        // Get banks and prepare header mapping
        $banks = model_list_to_array(Bank::class, Bank::getAll());
        $bank_list = sort_by($banks, 'name');
        $banks_short_name = array_map(fn($b) => $b['short_name'], $bank_list);

        $headers = array_merge(
            ['DATE SUBMITTED', 'LAST NAME', 'FIRST NAME', 'MIDDLE NAME', 'BIRTHDATE'],
            $banks_short_name,
            ['MOBILE NUMBER', 'AGENT']
        );

        $bankCount = count($banks_short_name);
        $bankTemplate = array_fill(0, $bankCount, '');
        $bankIdMap = [];

        foreach ($bank_list as $index => $bank) 
        {
            $bankIdMap[$bank['id']] = [
                'short_name' => $bank['short_name'],
                'index' => $index
            ];
        }

        $excel = Excel::create(['Sheet1']);
        $sheet = $excel->getSheet();

        $headerStyle = (new Style())
            ->setFontStyleBold()
            ->setFontColor('#FFFFFF')
            ->setBgColor('#000000')
            ->setTextAlign('center', 'center');

        $contentStyle = [
            'border-style'   => 'thin',
            'border-color'   => '#000000',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $sheet->writeRow($headers, $headerStyle);
        $sheet->setRowHeight(1, 20);

        $widths = [];
        foreach ($headers as $i => $header) 
        {
            $widths[$i] = strlen($header) + 5;
        }

        $agent_col = (count($headers) - 1);
        foreach ([1, 2, 3, $agent_col] as $col) 
        {
            $widths[$col] = 25;
        }

        $sheet->setColWidths($widths);

        $cursor = $this->bankApplicationService->getExportCursor($start, $end);

        $buffer = [];
        $batchSize = 5000;

        foreach ($cursor as $row) 
        {
            $submittedBanks = [];
            if (!empty($row['bank_submitted_id'])) 
            {
                $bankSubmittedId = $row['bank_submitted_id'];

                if (is_string($bankSubmittedId)) 
                {
                    $submittedBanks = json_decode($bankSubmittedId, true) ?: [];
                } 
                elseif (is_array($bankSubmittedId)) 
                {
                    $submittedBanks = $bankSubmittedId;
                }
            }

            // Initialize row with blank bank columns
            $rowData = [
                formatDate($row['date_submitted'], 'm/d/Y'),
                $row['lastname'],
                $row['firstname'],
                $row['middlename'],
                formatDate($row['birthdate'], 'm/d/Y'),
                ...$bankTemplate,
                $row['mobile_num'],
                $row['agent'] ?? ''
            ];

            // Fill bank columns with "X" if submitted
            foreach ($submittedBanks as $bankId) 
            {
                if (isset($bankIdMap[$bankId])) 
                {
                    $colIndex = 5 + $bankIdMap[$bankId]['index']; // 5 = starting column for banks
                    $rowData[$colIndex] = 'X';
                }
            }

            $buffer[] = $rowData;

            if (count($buffer) >= $batchSize) 
            {
                $sheet->writeRows($buffer, $contentStyle);
                $buffer = [];
            }
        }

        if (!empty($buffer)) 
        {
            $sheet->writeRows($buffer, $contentStyle);
        }

        $filename = "TRANSMITAL-{$start}-TO-{$end}.xlsx";
        $excel->download($filename);
    }
}
