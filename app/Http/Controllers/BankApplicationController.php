<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Http\Request\Request;
use App\Models\Bank;
use App\Services\BankApplicationService;
use avadim\FastExcelWriter\Excel;

class BankApplicationController extends Controller
{
    public function __construct(
        private BankApplicationService $bankApplicationService,
    ) {}

    public function show(Request $request)
    {
        $banks = Bank::getAll();

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
            if ($request->get($key)) $filters[$key] = $request->get($key);
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

        $this->view('applications', [
            'banks' => $banks,
            'applications' => $clientsApplications,
            'meta' => $meta,
        ]);
    }

    public function preExport(Request $request) 
    {   
        $startDate = $request->post('startDate');
        $endDate = $request->post('endDate');

        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        $result = $this->bankApplicationService
            ->getEstimateTimeOfExport($start, $end);

        return $this->responseJson($result);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        set_time_limit(0);
        
        $banks = model_list_to_array(Bank::class, Bank::getAll());
        $bank_list = sort_by($banks, 'name');
        $banks_short_name = array_map(fn($b) => $b['short_name'], $bank_list);

        $headers = array_merge(
            ['Date Submitted', 'Last Name', 'First Name', 'Middle Name'],
            $banks_short_name,
            ['Mobile Number', 'Agent']
        );

        $bankCount = count($banks_short_name);
        $bankIndexMap = array_flip($banks_short_name);
        $bankTemplate = array_fill(0, $bankCount, '');

        $excel = Excel::create(['Sheet1']);
        $sheet = $excel->getSheet();
        $sheet->writeRow($headers);

        $cursor = $this->bankApplicationService->getExportCursor($start, $end);

        $buffer = [];
        $batchSize = 5000;

        foreach ($cursor as $row) 
        {
            $rowData = [
                $row['date_submitted'],
                $row['lastname'],
                $row['firstname'],
                $row['middlename'],
                ...$bankTemplate,
                $row['mobile_num'],
                $row['agent'] ?? ''
            ];

            if (isset($bankIndexMap[$row['bank_short_name']])) 
            {
                $rowData[4 + $bankIndexMap[$row['bank_short_name']]] = 'X';
            }

            $buffer[] = $rowData;

            if (count($buffer) >= $batchSize) 
            {
                $sheet->writeRows($buffer);
                $buffer = [];
            }
        }

        if (!empty($buffer)) 
        {
            $sheet->writeRows($buffer);
        }

        $excel->download('bank_applications_fast.xlsx');
    }
}
