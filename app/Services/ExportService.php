<?php

namespace App\Services;

use App\Models\Bank;
use avadim\FastExcelWriter\Excel;
use avadim\FastExcelWriter\Style\Style;

class ExportService
{
    public function __construct(
        private BankApplicationService $bankApplicationService
    ) {}

    public function exportBankApplications(string $startDate, string $endDate)
    {
        $start = date('Y-m-d 00:00:00', strtotime($startDate));
        $end = date('Y-m-d 23:59:59', strtotime($endDate));

        set_time_limit(0);

        $banks = model_list_to_array(Bank::class, Bank::getAll());
        $banks_short_name = array_map(fn($b) => $b['short_name'], $banks);

        $headers = array_merge(
            ['DATE SUBMITTED', 'LAST NAME', 'FIRST NAME', 'MIDDLE NAME', 'BIRTHDATE'],
            $banks_short_name,
            ['MOBILE NUMBER', 'AGENT']
        );

        $bankCount = \count($banks_short_name);
        $bankTemplate = array_fill(0, $bankCount, '');
        $bankIdMap = [];

        foreach ($banks as $index => $bank) 
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
            $widths[$i] = \strlen($header) + 5;
        }

        $agent_col = (\count($headers) - 1);
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

                if (\is_string($bankSubmittedId)) 
                {
                    $submittedBanks = json_decode($bankSubmittedId, true) ?: [];
                } 
                elseif (\is_array($bankSubmittedId)) 
                {
                    $submittedBanks = $bankSubmittedId;
                }
            }

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

            if (\count($buffer) >= $batchSize) 
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
