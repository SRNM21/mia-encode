<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\BankApplication;
use App\Models\Client;
use App\Models\RequestEdit;
use App\Repository\BankApplicationRepository;
use DateTime;
use DateTimeImmutable;

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
        $banks = Bank::getAll();
        $applications = $this->bankApplicationRepository->getTodaysApplicationsByBank();

        $bankMap = [];
        foreach ($banks as $bank) 
        {
            $bankMap[$bank->id] = [
                'name' => $bank->name,
                'count' => 0
            ];
        }

        foreach ($applications as $app) 
        {
            if (empty($app['bank_submitted_id'])) continue;

            $bankIds = json_decode($app['bank_submitted_id'], true);

            if (!is_array($bankIds)) continue;

            foreach ($bankIds as $bankId) 
            {
                $bankId = (int) $bankId;

                if (isset($bankMap[$bankId])) 
                {
                    $bankMap[$bankId]['count']++;
                }
            }
        }

        $labels = [];
        $counts = [];

        foreach ($bankMap as $bank) 
        {
            if ($bank['count'] === 0) continue;

            $labels[] = $bank['name'];
            $counts[] = $bank['count'];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }

    public function bankApplicationsSeries(?string $scope, ?string $year): array
    {
        $rows = $this->bankApplicationRepository->getAllWithBankAndDate();
        $years_rows = $this->bankApplicationRepository->getAvailableYears();
        $banks = Bank::getAll();

        $bank_map = [];
        foreach ($banks as $bank) $bank_map[$bank->id] = $bank;

        $years = array_map(fn($r) => (string)($r['year'] ?? ''), $years_rows);
        sort($years);

        $selected_year = $year ?: (count($years) ? max($years) : date('Y'));
        $scope = $scope ?: 'daily';

        $series = [];
        if (in_array($scope, ['daily', 'weekly', 'monthly'], true)) 
        {
            $countsByDay = $this->aggregateDailyCountsForYear($rows, $selected_year, $bank_map);

            if ($scope === 'daily') 
            {
                $series['daily'] = $this->buildDailyBankSeries($countsByDay, $selected_year, $bank_map);
            } 
            elseif ($scope === 'weekly') 
            {
                $series['weekly'] = $this->buildWeeklyBankSeries($countsByDay, $selected_year, $bank_map);
            } 
            else 
            {
                $series['monthly'] = $this->buildMonthlyBankSeries($countsByDay, $selected_year, $bank_map);
            }
        } 
        elseif ($scope === 'yearly') 
        {
            $series['yearly'] = $this->buildYearlyBankSeries($rows, $years, $bank_map);
        }

        return [
            'years' => $years,
            'selected_year' => (string)$selected_year,
            'series' => $series,
        ];
    }

    private function aggregateDailyCountsForYear(array $rows, string $year, array $bankMap): array
    {
        $counts = [];

        foreach ($rows as $row) 
        {
            $day = isset($row['date_submitted']) ? date('Y-m-d', strtotime($row['date_submitted'])) : null;
            $banks_submitted = json_decode($row['bank_submitted_id'], true) ?? [];

            if (!$day || empty($banks_submitted)) continue;


            if (substr($day, 0, 4) !== $year) continue; // skip not scope year
            if (!isset($counts[$day])) $counts[$day] = []; 

            foreach ($banks_submitted as $bs)
            {
                if (!isset($counts[$day][$bs])) $counts[$day][$bs] = 0;
                $counts[$day][$bs]++;
            }
        }

        ksort($counts);
        return $counts;
    }

    private function buildDailyBankSeries(array $countsByDay, string $year, array $bankMap): array
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

        $bankIds = array_keys($bankMap);
        sort($bankIds);

        $datasets = [];

        foreach ($bankIds as $bankId)
        {
            $data = [];

            foreach ($keys as $k)
            {
                $data[] = $countsByDay[$k][$bankId] ?? 0;
            }

            $datasets[] = [
                'label' => $bankMap[$bankId]->name,
                'data' => $data
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function buildWeeklyBankSeries(array $countsByDay, string $year, array $bankMap): array
    {
        $labels = [];
        $weekRanges = [];

        for ($m = 1; $m <= 12; $m++)
        {
            $monthStr = sprintf('%02d', $m);
            $monthNameShort = date('M', strtotime($year . '-' . $monthStr . '-01'));

            $first = new \DateTimeImmutable($year . '-' . $monthStr . '-01');
            $last = $first->modify('last day of this month');

            $firstMonday = $first;
            if ((int)$firstMonday->format('N') !== 1)
            {
                $firstMonday = $firstMonday->modify('next monday');
            }

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

        $bankIds = array_keys($bankMap);
        sort($bankIds);

        $datasets = [];

        foreach ($bankIds as $bankId)
        {
            $data = [];

            foreach ($weekRanges as $days)
            {
                $sum = 0;

                foreach ($days as $k)
                {
                    $sum += ($countsByDay[$k][$bankId] ?? 0);
                }

                $data[] = $sum;
            }

            $datasets[] = [
                'label' => $bankMap[$bankId]->name,
                'data' => $data
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function buildMonthlyBankSeries(array $countsByDay, string $year, array $bankMap): array
    {
        $labels = [];
        $monthRanges = [];

        for ($m = 1; $m <= 12; $m++)
        {
            $monthStr = sprintf('%02d', $m);
            $labels[] = date('F', strtotime($year . '-' . $monthStr . '-01'));

            $first = new \DateTimeImmutable($year . '-' . $monthStr . '-01');
            $last = $first->modify('last day of this month');

            $days = [];

            for ($d = $first; $d <= $last; $d = $d->add(new \DateInterval('P1D')))
            {
                if ((int)$d->format('m') !== $m) continue;
                $days[] = $d->format('Y-m-d');
            }

            $monthRanges[] = $days;
        }

        $bankIds = array_keys($bankMap);
        sort($bankIds);

        $datasets = [];

        foreach ($bankIds as $bankId)
        {
            $data = [];

            foreach ($monthRanges as $days)
            {
                $sum = 0;

                foreach ($days as $k)
                {
                    $sum += ($countsByDay[$k][$bankId] ?? 0);
                }

                $data[] = $sum;
            }

            $datasets[] = [
                'label' => $bankMap[$bankId]->name,
                'data' => $data
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function buildYearlyBankSeries(array $rows, array $years, array $bankMap): array
    {
        $labels = $years;

        $counts = [];
        foreach ($years as $yr) $counts[$yr] = [];

        foreach ($rows as $row)
        {
            $day = isset($row['date_submitted']) ? date('Y-m-d', strtotime($row['date_submitted'])) : null;
            $yr = $day ? substr($day, 0, 4) : null;

            $banks_submitted = json_decode($row['bank_submitted_id'], true) ?? [];

            if (!$yr || empty($banks_submitted)) continue;

            foreach ($banks_submitted as $bankId)
            {
                if (!isset($counts[$yr][$bankId])) $counts[$yr][$bankId] = 0;
                $counts[$yr][$bankId]++;
            }
        }

        $bankIds = array_keys($bankMap);
        sort($bankIds);

        $datasets = [];

        foreach ($bankIds as $bankId)
        {
            $data = [];

            foreach ($labels as $yr)
            {
                $data[] = $counts[$yr][$bankId] ?? 0;
            }

            $datasets[] = [
                'label' => $bankMap[$bankId]->name,
                'data' => $data
            ];
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

    private function getWeekRange(int $year, int $month, int $weekOffset = 0): array
    {
        $firstDay = new DateTimeImmutable("$year-$month-01");
        $lastDay  = $firstDay->modify('last day of this month');

        if ($weekOffset === 0) 
        {
            $start = $firstDay;
            $weekday = (int)$start->format('N');

            if ($weekday >= 6) {
                $start = $start->modify('next monday');
                $weekday = (int)$start->format('N');
            }

            if ($start > $lastDay) {
                return ['start' => null, 'end' => null];
            }

            $daysToFriday = 5 - $weekday;
            $end = $start->modify("+{$daysToFriday} days");

            if ($end > $lastDay) $end = $lastDay;

            return [
                'start' => $start,
                'end'   => $end
            ];
        }

        $firstMonday = ($firstDay->format('N') == 1)
            ? $firstDay
            : $firstDay->modify('next monday');

        $start = $firstMonday->modify('+' . ($weekOffset - 1) . ' weeks');

        if ($start > $lastDay) 
        {
            return [
                'start' => null,
                'end'   => null
            ];
        }

        $end = $start->modify('+4 days');

        if ($end > $lastDay) 
        {
            $end = $lastDay;
        }

        return [
            'start' => $start,
            'end'   => $end
        ];
    }

    private function getLastWeekOffset(int $year, int $month): int
    {
        $firstDay = new DateTimeImmutable("$year-$month-01");
        $lastDay  = $firstDay->modify('last day of this month');

        $weekOffset = 0;

        while (true)
        {
            $range = $this->getWeekRange($year, $month, $weekOffset);

            if (!$range['start'] || $range['start'] > $lastDay) break;

            $weekOffset++;
        }

        return max(0, $weekOffset - 1);
    }

    public function getMonthlyWeeklyCalendar(int $year, int $month): array
    {
        $firstDay = new DateTimeImmutable("$year-$month-01");
        $lastDay  = $firstDay->modify('last day of this month');

        $years = $this->bankApplicationRepository->getAvailableYears();

        $lastWeek = $this->getLastWeekOffset($year, $month);

        $weeks = [];

        for ($week = 0; $week <= $lastWeek; $week++)
        {
            $range = $this->getWeekRange($year, $month, $week);

            // Skip invalid weeks
            if (!$range['start'] || !$range['end']) continue;

            $start = $range['start']->format('Y-m-d');
            $end   = $range['end']->format('Y-m-d');

            $rows = $this->bankApplicationRepository->getByDateRange($start, $end);

            $weeks[] = [
                'week' => $week,
                'range' => [
                    'start' => $start,
                    'end' => $end
                ],
                'data' => $this->aggregateWeekData($rows)
            ];
        }

        return [
            'years' => $years,
            'range' => [
                'start' => $firstDay->format('Y-m-01'),
                'end'   => $lastDay->format('Y-m-d')
            ],
            'weeks' => $weeks
        ];
    }

    private function aggregateWeekData(array $rows): array
    {
        $banks = Bank::getAll();

        $bankMap = [];
        foreach ($banks as $b) {
            $bankMap[$b->id] = $b->name;
        }

        $counts = [];

        foreach ($bankMap as $id => $name) {
            $counts[$id] = [
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                'total' => 0
            ];
        }

        foreach ($rows as $row)
        {
            $date = new DateTime($row['date_submitted']);
            $weekday = (int)$date->format('N');

            if ($weekday > 5) continue;

            $bankIds = json_decode($row['bank_submitted_id'], true) ?? [];

            foreach ($bankIds as $bankId)
            {
                if (!isset($counts[$bankId])) continue;

                $counts[$bankId][$weekday]++;
                $counts[$bankId]['total']++;
            }
        }

        $result = [];

        foreach ($counts as $bankId => $c)
        {
            $result[] = [
                'bank' => $bankMap[$bankId],
                'mon' => $c[1],
                'tue' => $c[2],
                'wed' => $c[3],
                'thu' => $c[4],
                'fri' => $c[5],
                'total' => $c['total'],
            ];
        }

        return $result;
    }
}
