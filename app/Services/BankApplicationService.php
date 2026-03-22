<?php

namespace App\Services;

use App\Models\BankApplication;
use App\Models\Client;
use App\Models\RequestEdit;
use App\Repository\BankApplicationRepository;
use DateTime;

class BankApplicationService
{
    public function __construct(
        private BankApplicationRepository $bankApplicationRepository
    ) {}

    public function update(string $id, array $data) 
    {
        $birthdate = $data['birthdate'];
        $birthdate = date('Y-m-d', strtotime($birthdate));

        // check if the updated data is an existing client
        $client = Client::query()
            ->where('first_name', '=', $data['firstname'])
            ->where('middle_name', '=', $data['middlename'])
            ->where('last_name', '=', $data['lastname'])
            ->where('birthdate', '=', $birthdate)
            ->where('mobile_num', '=', $data['mobile'])
            ->first();
        
        $bank_app = BankApplication::get($id);

        // update client data

        if ($client)
        {   
            // if the updated data is an existing client, 
            // replace the client of this bank application
            BankApplication::update(['id' => $bank_app->id], [
                'client_id' => $client->id,
            ]);
        }
        else     
        {
            // if the updated data does not match any 
            // client, update the client data
            Client::update(['id' => $bank_app->client_id], [
                'first_name' => $data['firstname'],
                'middle_name' => $data['middlename'],
                'last_name' => $data['lastname'],
                'birthdate' => $birthdate,
                'mobile_num' => $data['mobile'],
            ]);
        }

        // update client's bank application data

        // all of applications of client
        $applications = $this->getClientApplications($client->id);

        // store all submitted bank applications of an existing client
        $bank_application_map = [];
        foreach ($applications as $app) 
        {
            $app_banks_submitted = json_decode($app['bank_submitted_id'], true) ?? [];
            $app_submitted_date = $app['date_submitted'] ? new DateTime($app['date_submitted']) : null;

            // overwrite previous applications (to make sure only latest appears)
            foreach ($app_banks_submitted as $bank_id) 
            {   
                $bank_application_map[(string)$bank_id] = [
                    'app_id' => $app['id'],
                    'date_submitted' => $app_submitted_date,
                ];
            }
        }

        // the submitted bank applications of the current client to update
        $banks_submitted = $data['banks'];
        $to_be_removed = [];

        foreach ($banks_submitted as $bank_id)
        {
            $can_overwrite = array_key_exists((string)$bank_id, $bank_application_map);

            if (!$can_overwrite) continue;

            // check if the current edited application can overwrite the other application
            // e.g.:
            //      [9,1,8,4] - submitted
            //      [9,3,7,5,2,10,6] - all application of client
            //      (9 will be remove from other applicationa)

            $application = $bank_application_map[$bank_id];

            // skip this current edit application
            if ($application['app_id'] === $bank_app->id) continue;
            
            // check if that application is past this current edit application
            // e.g. Mar 21, 2026 > Mar 20, 2026
            // 
            // this make sure only past date_submitted of current edit application
            // will be overwritten
            if ($application['date_submitted'] > $bank_app->date_submitted)
            {
                $to_be_removed[$application['app_id']][] = $bank_id;
            }
        }

        // if `to_be_removed` is empty then no overwrite happened in edit mode
        if (count($to_be_removed) > 0) 
        {
            // overwrite applications if overlapped to other applications
            foreach ($to_be_removed as $bank_app_id => $bank_submitted_id)
            {
                $to_be_update_bank_app = BankApplication::get($bank_app_id);

                $to_be_update_submitted_banks = json_decode($to_be_update_bank_app->bank_submitted_id, true) ?? [];

                // overwrite bank applications
                $to_be_update_submitted_banks = array_values(
                    array_diff($to_be_update_submitted_banks, $bank_submitted_id)
                );
                
                // delete empty bank application due to overwrite
                // update bank application otherwise
                if (count($to_be_update_submitted_banks) > 0)
                {
                    BankApplication::update(['id' => $bank_app_id], [
                        'bank_submitted_id' => json_encode(array_map('intval', $to_be_update_submitted_banks))
                    ]);
                }
                else 
                {
                    BankApplication::delete($bank_app_id);

                    // also delete request regarding this submitted application
                    $requests = RequestEdit::query()
                        ->where('app_id', '=', $bank_app_id)
                        ->getArray();

                    foreach ($requests as $req) 
                    {
                        RequestEdit::delete($req['id']);
                    }
                }
            }
        }

        // finally, update the current bank application
        BankApplication::update(['id' => $id], [
            'bank_submitted_id' => json_encode(array_map('intval', $banks_submitted))
        ]);
    }

    public function getClientApplications(int $client_id)
    {
        return $this->bankApplicationRepository->getClientApplications($client_id);
    }

    public function buildTableRows($banks, $latestSubmissions, $application): array
    {
        $appsById = [];

        foreach ($latestSubmissions as $sub) 
        {
            $bankIds = json_decode($sub['bank_submitted_id'], true);

            foreach ($bankIds as $id) 
            {
                $appsById[(string)$id] = [
                    'date_submitted' => $sub['date_submitted'],
                    'agent' => $sub['agent'],
                    'is_edit' => false
                ];
            }
        }

        $editBankIds = json_decode($application->bank_submitted_id, true);

        foreach ($editBankIds as $id) 
        {
            $appsById[(string)$id] = [
                'date_submitted' => $application->date_submitted,
                'agent' => $application->agent,
                'is_edit' => true
            ];
        }

        $rows = [];

        foreach ($banks as $bank) 
        {
            if (!$bank['is_active']) continue;

            $app = $appsById[(string)$bank['id']] ?? null;

            $isEditMode = $app['is_edit'] ?? false;

            $isPrevious = $app
                ? strtotime($app['date_submitted']) < strtotime($application->date_submitted)
                : false;

            $isExpiredApplication = $app
                ? $this->isExpired($app['date_submitted'], $bank['expiry_months'])
                : false;

            $isUnavailable = ($app && $isExpiredApplication) || $isPrevious;

            if ($isUnavailable) 
            {
                $statusText = 'Unavailable';
                $statusClass = 'status-unavailable';
            } 
            elseif ($isEditMode || $app === null) 
            {
                $statusText = 'Available';
                $statusClass = 'status-available';
            }
            else 
            {
                $statusText = 'Can Overwrite';
                $statusClass = 'status-overwrite';
            }

            $rows[] = [
                'bank_name' => $bank['name'],
                'bank_id' => $bank['id'],
                'date' => $app ? formatDate($app['date_submitted']) : '—',
                'agent' => $app['agent'] ?? '—',
                'status_text' => ucfirst($statusText),
                'status_class' => $statusClass,
                'row_class' => $isUnavailable ? 'row-disabled' : '',
                'action_class' => implode(' ', array_filter([
                    'bank-select-cell',
                    (!$isUnavailable && $isEditMode) ? 'selected' : null,
                    $isUnavailable ? 'disabled unavailable' : null
                ])),
                'is_unavailable' => $isUnavailable,
                'is_edit_mode' => $isEditMode
            ];
        }

        return $rows;
    }

    public function isExpired(string $dateSubmitted, int $expirationMonths): bool
    {
        $submissionDate = new DateTime($dateSubmitted);
        $expirationDate = clone $submissionDate;

        $expirationDate->modify("+{$expirationMonths} months");
        $currentDate = new DateTime();

        return $currentDate > $expirationDate;
    }

    public function isPastMaxMonths(string $date_submitted, int $max_month): bool
    {
        if (!$date_submitted || $max_month <= 0) {
            return false;
        }

        $submittedDate = new DateTime($date_submitted);
        $cutoffDate = new DateTime();
        $cutoffDate->modify("-{$max_month} months");

        return $submittedDate > $cutoffDate;
    }

    public function applicationsTodayByBank(): array
    {
        $applications = $this->bankApplicationRepository->getTodaysApplicationsByBank();

        $labels = array_column($applications, 'bank_name');
        $counts = array_column($applications, 'count');

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }

    public function bankApplicationsSeries(?string $scope, ?string $year): array
    {
        $rows = $this->bankApplicationRepository->getAllWithBankAndDate();
        $yearsRows = $this->bankApplicationRepository->getAvailableYears();
        $years = array_map(fn($r) => (string)($r['year'] ?? ''), $yearsRows);
        sort($years);

        $selectedYear = $year ?: (count($years) ? max($years) : date('Y'));
        $scope = $scope ?: 'daily';

        $banks = [];
        foreach ($rows as $r) 
        {
            $bn = $r['bank_name'] ?? null;
            if ($bn) $banks[$bn] = true;
        }

        $bankList = array_keys($banks);
        sort($bankList);

        $series = [];
        if (in_array($scope, ['daily', 'weekly', 'monthly'], true)) 
        {
            $countsByDay = $this->aggregateDailyCountsForYear($rows, $selectedYear);

            if ($scope === 'daily') 
            {
                $series['daily'] = $this->buildDailyBankSeries($countsByDay, $selectedYear, $bankList);
            } 
            elseif ($scope === 'weekly') 
            {
                $series['weekly'] = $this->buildWeeklyBankSeries($countsByDay, $selectedYear, $bankList);
            } 
            else 
            {
                $series['monthly'] = $this->buildMonthlyBankSeries($countsByDay, $selectedYear, $bankList);
            }
        } 
        elseif ($scope === 'yearly') 
        {
            $series['yearly'] = $this->buildYearlyBankSeries($rows, $years, $bankList);
        }

        return [
            'years' => $years,
            'selected_year' => (string)$selectedYear,
            'series' => $series,
        ];
    }

    private function aggregateDailyCountsForYear(array $rows, string $year): array
    {
        $counts = [];

        foreach ($rows as $r) 
        {
            $day = isset($r['date_submitted']) ? date('Y-m-d', strtotime($r['date_submitted'])) : null;
            $bank = $r['bank_name'] ?? null;

            if (!$day || !$bank) continue;
            if (substr($day, 0, 4) !== $year) continue;
            if (!isset($counts[$day])) $counts[$day] = [];
            if (!isset($counts[$day][$bank])) $counts[$day][$bank] = 0;

            $counts[$day][$bank]++;
        }

        ksort($counts);
        return $counts;
    }

    private function buildDailyBankSeries(array $countsByDay, string $year, array $bankList): array
    {
        $labels = [];
        $keys = [];
        $start = new \DateTimeImmutable($year . '-01-01');
        $end = new \DateTimeImmutable($year . '-12-31');

        for ($d = $start; $d <= $end; $d = $d->add(new \DateInterval('P1D'))) 
        {
            $key = $d->format('Y-m-d');
            $keys[] = $key;
            $labels[] = $d->format('D, M j');
        }

        $datasets = [];
        foreach ($bankList as $bank) 
        {
            $data = [];
            foreach ($keys as $k) 
            {
                $data[] = $countsByDay[$k][$bank] ?? 0;
            }

            $datasets[] = ['label' => $bank, 'data' => $data];
        }
        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function buildWeeklyBankSeries(array $countsByDay, string $year, array $bankList): array
    {
        $labels = [];
        $weekRanges = [];

        for ($m = 1; $m <= 12; $m++) 
        {
            $monthStr = sprintf('%02d', $m);
            $monthNameShort = date('M', strtotime($year . '-' . $monthStr . '-01'));
            $first = new \DateTimeImmutable($year . '-' . $monthStr . '-01');
            $last = (new \DateTimeImmutable($year . '-' . $monthStr . '-01'))->modify('last day of this month');
            $firstMonday = $first;

            if ((int)$firstMonday->format('N') !== 1) $firstMonday = $firstMonday->modify('next monday');
            $idx = 1;
            
            for ($ws = $firstMonday; $ws <= $last; $ws = $ws->add(new \DateInterval('P7D'))) 
            {
                $we = $ws->add(new \DateInterval('P6D'));
                if ((int)$ws->format('m') !== $m) break;

                $labels[] = $monthNameShort . ' W' . $idx;
                $range = [];

                for ($cur = $ws; $cur <= $we; $cur = $cur->add(new \DateInterval('P1D'))) 
                {
                    if ((int)$cur->format('m') !== $m) continue;

                    $range[] = $cur->format('Y-m-d');
                }

                $weekRanges[] = $range;
                $idx++;
            }
        }

        $datasets = [];
        foreach ($bankList as $bank) 
        {
            $data = [];
            foreach ($weekRanges as $days) 
            {
                $sum = 0;
                foreach ($days as $k) $sum += ($countsByDay[$k][$bank] ?? 0);
                $data[] = $sum;
            }

            $datasets[] = ['label' => $bank, 'data' => $data];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function buildMonthlyBankSeries(array $countsByDay, string $year, array $bankList): array
    {
        $labels = [];
        $monthRanges = [];

        for ($m = 1; $m <= 12; $m++) 
        {
            $monthStr = sprintf('%02d', $m);
            $labels[] = date('F', strtotime($year . '-' . $monthStr . '-01'));
            $first = new \DateTimeImmutable($year . '-' . $monthStr . '-01');
            $last = (new \DateTimeImmutable($year . '-' . $monthStr . '-01'))->modify('last day of this month');
            $days = [];

            for ($d = $first; $d <= $last; $d = $d->add(new \DateInterval('P1D'))) 
            {
                if ((int)$d->format('m') !== $m) continue;

                $days[] = $d->format('Y-m-d');
            }

            $monthRanges[] = $days;
        }

        $datasets = [];
        foreach ($bankList as $bank) 
        {
            $data = [];

            foreach ($monthRanges as $days) 
            {
                $sum = 0;
                foreach ($days as $k) $sum += ($countsByDay[$k][$bank] ?? 0);

                $data[] = $sum;
            }

            $datasets[] = ['label' => $bank, 'data' => $data];
        }
        
        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function buildYearlyBankSeries(array $rows, array $years, array $bankList): array
    {
        $labels = $years;
        $counts = [];

        foreach ($years as $yr) $counts[$yr] = [];
        
        foreach ($rows as $r) 
        {
            $day = isset($r['date_submitted']) ? date('Y-m-d', strtotime($r['date_submitted'])) : null;
            $yr = $day ? substr($day, 0, 4) : null;
            $bank = $r['bank_name'] ?? null;

            if (!$yr || !$bank) continue;
            if (!isset($counts[$yr][$bank])) $counts[$yr][$bank] = 0;

            $counts[$yr][$bank]++;
        }

        $datasets = [];
        foreach ($bankList as $bank) 
        {
            $data = [];

            foreach ($labels as $yr) $data[] = $counts[$yr][$bank] ?? 0;

            $datasets[] = ['label' => $bank, 'data' => $data];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    public function getAgentsLeaderboards(string $scope): array
    {
        $now = new \DateTimeImmutable('now');
        $start = null;
        $end = null;

        switch (strtolower($scope)) {
            case 'today':
                $start = $now->format('Y-m-d') . ' 00:00:00';
                $end = $now->format('Y-m-d') . ' 23:59:59';
                break;
            case 'week':
            case 'this_week':
                $monday = (int) $now->format('N') === 1 ? $now : $now->modify('monday this week');
                $sunday = $monday->modify('sunday this week');

                $start = $monday->format('Y-m-d') . ' 00:00:00';
                $end = $sunday->format('Y-m-d') . ' 23:59:59';
                break;
            case 'month':
            case 'this_month':
                $first = $now->modify('first day of this month');
                $last = $now->modify('last day of this month');

                $start = $first->format('Y-m-d') . ' 00:00:00';
                $end = $last->format('Y-m-d') . ' 23:59:59';
                break;
            case 'year':
            case 'this_year':
                $yearStart = $now->setDate((int)$now->format('Y'), 1, 1);
                $yearEnd = $now->setDate((int)$now->format('Y'), 12, 31);

                $start = $yearStart->format('Y-m-d') . ' 00:00:00';
                $end = $yearEnd->format('Y-m-d') . ' 23:59:59';
                break;
            default:
                $start = $now->format('Y-m-d') . ' 00:00:00';
                $end = $now->format('Y-m-d') . ' 23:59:59';
        }

        $rows = $this->bankApplicationRepository->getAgentsApplicationCountByRange($start, $end);
        
        return array_slice($rows, 0, 5);
    }

    public function getEstimateTimeOfExport(string $startDate, string $endDate)
    {
        $total = $this->bankApplicationRepository
            ->getNumberOfDataWithinDateRange($startDate, $endDate)[0]['total'];

        // estimate rows per second
        $bestSpeed  = 2500;
        $worstSpeed = 1200;

        $bestMinutes  = ceil(($total / $bestSpeed) / 60);
        $worstMinutes = ceil(($total / $worstSpeed) / 60);

        return [
            'total' => $total,
            'bestMinute' => $bestMinutes,
            'worstMinute' => $worstMinutes
        ];
    }

    public function getApplicationsToExportChunk(int $limit, int $lastId)
    {
        return $this->bankApplicationRepository
            ->getApplicationsToExportChunk($limit, $lastId);
    }

    public function getExportCursor(string $startDate, string $endDate): \Generator
    {
        return $this->bankApplicationRepository->getExportCursor($startDate, $endDate);
    }

    public function getClientsPage(
        int $page = 1,
        int $perPage = 25,
        array $filters = [],
        string $sort = 'date_submitted',
        string $order = 'desc'
    ): array
    {
        return $this->bankApplicationRepository
            ->getPaginatedApplicationsWithRelations($page,$perPage,$filters,$sort,$order);
    }
}
