<?php

namespace App\Services;

use App\Http\Request\Request;
use App\Models\BankApplication;
use App\Models\Client;
use App\Repository\ClientRepository;
use DateInterval;
use DateTimeImmutable;

class ClientService
{
    public function __construct(
        private ClientRepository $clientRepository, 
    ) {}

    // -------------------------------
    // Encoding
    // -------------------------------

    public function getClient(Request $request): ?Client
    {
        return $this->clientRepository->getClient($request);
    }

    public function create(array $data): Client
    {
        return $this->clientRepository->create($data);
    }

    public function createClientFromRequest(Request $request)
    {
        $date = $request->post('birthdate');
        $date = date('Y-m-d', strtotime($date));

        return $this->create([
            'last_name' => $request->post('lastname'),
            'middle_name' => $request->post('middlename'),
            'first_name' => $request->post('firstname'),
            'birthdate' => $date,
            'mobile_num' => $request->post('mobile'),
            'first_application' => date('Y-m-d'),
            'latest_application' => date('Y-m-d'),
        ]);
    }

    public function updateLatestApplicationDate(int $clientId)
    {
        return $this->clientRepository->updateLatestApplicationDate($clientId);
    }

    // -------------------------------
    // Dashboard
    // -------------------------------

    public function clientTypeToday(): array
    {
        $applications = BankApplication::query()
            ->select('client_id', 'date_submitted')
            ->get();

        $today = date('Y-m-d');

        $earliestDayByClient = [];
        foreach ($applications as $app) 
        {
            $cid = $app->client_id ?? null;
            $day = $app->date_submitted ? date('Y-m-d', strtotime($app->date_submitted)) : null;

            if (!$cid || !$day) continue;

            $earliestDayByClient[$cid] = isset($earliestDayByClient[$cid])
                ? min($earliestDayByClient[$cid], $day)
                : $day;
        }

        $seenClientToday = [];
        $newCount = 0;
        $oldCount = 0;
        foreach ($applications as $app) 
        {
            $cid = $app->client_id ?? null;
            $day = $app->date_submitted ? date('Y-m-d', strtotime($app->date_submitted)) : null;

            if (!$cid || !$day) continue;
            if ($day !== $today) continue;
            if (isset($seenClientToday[$cid])) continue;

            $seenClientToday[$cid] = true;

            $isNewToday = isset($earliestDayByClient[$cid]) && $earliestDayByClient[$cid] === $today;

            if ($isNewToday) $newCount++;
            else $oldCount++;
        }
       
        return [
            'new' => $newCount,
            'old' => $oldCount,
        ];
    }

    public function clientTypeSeries($scope, $year): array
    {
        $applications = $this->getApplications();
        $availableYears = $this->getAvailableYearsFromApplications($applications);
        $scope = $scope ?: 'monthly';
        $requestedYear = $year ?: (\count($availableYears) ? max($availableYears) : date('Y'));
        $year = (string) $requestedYear;

        $earliestByClient = $this->computeEarliestDayByClient($applications);
        $series = [];

        if (\in_array($scope, ['weekly', 'monthly'], true)) 
        {
            $countsByDay = $this->countClientTypesByDayForYear($applications, $year, $earliestByClient);
            
            if ($scope === 'weekly') 
            {
                $series['weekly'] = $this->buildWeeklySeries($countsByDay, $year);
            } 
            else 
            {
                $series['monthly'] = $this->buildMonthlySeries($countsByDay, $year);
            }
        } 
        elseif ($scope === 'yearly') 
        {
            $series['yearly'] = $this->buildYearlySeries($applications, $earliestByClient);
        }

        return [
            'years' => $availableYears,
            'selected_year' => $year,
            'series' => $series,
        ];
    }

    private function getApplications(): array
    {
        return BankApplication::query()->select('client_id', 'date_submitted')->get();
    }

    private function getAvailableYearsFromApplications(array $applications): array
    {
        $years = [];

        foreach ($applications as $app) 
        {
            if (!$app->date_submitted) continue;
            $years[date('Y', strtotime($app->date_submitted))] = true;
        }

        $years = array_keys($years);
        sort($years);

        return $years;
    }

    private function computeEarliestDayByClient(array $applications): array
    {
        $map = [];
        
        foreach ($applications as $app) 
        {
            $cid = $app->client_id ?? null;
            $day = $app->date_submitted ? date('Y-m-d', strtotime($app->date_submitted)) : null;
            
            if (!$cid || !$day) continue;
            $map[$cid] = isset($map[$cid]) ? min($map[$cid], $day) : $day;
        }

        return $map;
    }

    private function countClientTypesByDayForYear(array $applications, string $year, array $earliestByClient): array
    {
        $seen = [];
        $counts = [];
        
        foreach ($applications as $app) 
        {
            $cid = $app->client_id ?? null;
            $day = $app->date_submitted ? date('Y-m-d', strtotime($app->date_submitted)) : null;
            
            if (!$cid || !$day) continue;
            if (substr($day, 0, 4) !== $year) continue;

            $k = "{$cid}|{$day}";
            if (isset($seen[$k])) continue;

            $seen[$k] = true;
            $isNew = isset($earliestByClient[$cid]) && $earliestByClient[$cid] === $day;

            if (!isset($counts[$day])) $counts[$day] = ['new' => 0, 'old' => 0];
            $counts[$day][$isNew ? 'new' : 'old']++;
        }

        ksort($counts);
        return $counts;
    }

    private function buildWeeklySeries(array $countsByDay, string $year): array
    {
        $weekly = [];

        for ($m = 1; $m <= 12; $m++) 
        {
            $monthStr = \sprintf('%02d', $m);
            $monthNameShort = date('M', strtotime("{$year}-{$monthStr}-01"));
            
            $currentDay = new DateTimeImmutable("{$year}-{$monthStr}-01");
            $lastOfMonth = $currentDay->modify('last day of this month');

            while ($currentDay <= $lastOfMonth) 
            {
                // skip weekends (N returns 6 for Saturday, 7 for Sunday)
                $dayOfWeek = (int)$currentDay->format('N');
                if ($dayOfWeek > 5) {
                    $daysToMonday = 8 - $dayOfWeek;
                    $currentDay = $currentDay->add(new DateInterval("P{$daysToMonday}D"));
                }

                // if skipping the weekend pushed us into the next month, stop the loop
                if ($currentDay > $lastOfMonth) break;

                $ws = $currentDay;

                // find Friday (day 5) of the current week
                $daysToFriday = 5 - (int)$ws->format('N');
                $we = $ws->add(new DateInterval("P{$daysToFriday}D"));

                // cap the week to the last day of the month if it spills over
                if ($we > $lastOfMonth) $we = $lastOfMonth;

                $sumNew = 0;
                $sumOld = 0;

                // loop through Monday-Friday to sum the counts
                for ($cur = $ws; $cur <= $we; $cur = $cur->add(new DateInterval('P1D'))) 
                {
                    $key = $cur->format('Y-m-d');
                    $sumNew += $countsByDay[$key]['new'] ?? 0;
                    $sumOld += $countsByDay[$key]['old'] ?? 0;
                }

                // format the label (e.g., Jan 5-9)
                $startDay = $ws->format('j'); 
                $endDay = $we->format('j');
                
                $label = $startDay === $endDay
                    ? "{$monthNameShort} {$startDay}"
                    : "{$monthNameShort} {$startDay}-{$endDay}";

                $weekly[] = [
                    'label' => $label, 
                    'new' => $sumNew, 
                    'old' => $sumOld
                ];

                // move to the day after this workweek ends (this will typically be Saturday)
                $currentDay = $we->add(new DateInterval('P1D'));
            }
        }

        return $weekly;
    }

    private function buildMonthlySeries(array $countsByDay, string $year): array
    {
        $monthly = [];

        for ($m = 1; $m <= 12; $m++) 
        {
            $monthStr = \sprintf('%02d', $m);
            $monthNameFull = date('F', strtotime("{$year}-{$monthStr}-01"));
            $sumNew = 0;
            $sumOld = 0;
            $firstOfMonth = new DateTimeImmutable("{$year}-{$monthStr}-01");
            $lastOfMonth = (new DateTimeImmutable("{$year}-{$monthStr}-01"))->modify('last day of this month');

            for ($d = $firstOfMonth; $d <= $lastOfMonth; $d = $d->add(new DateInterval('P1D'))) 
            {
                $key = $d->format('Y-m-d');
                $sumNew += $countsByDay[$key]['new'] ?? 0;
                $sumOld += $countsByDay[$key]['old'] ?? 0;
            }

            $monthly[] = ['label' => $monthNameFull, 'new' => $sumNew, 'old' => $sumOld];
        }

        return $monthly;
    }

    private function buildYearlySeries(array $applications, array $earliestByClient): array
    {
        $seen = [];
        $counts = [];

        foreach ($applications as $app) 
        {
            $cid = $app->client_id ?? null;
            $day = $app->date_submitted ? date('Y-m-d', strtotime($app->date_submitted)) : null;
            if (!$cid || !$day) continue;

            $yr = substr($day, 0, 4);
            $k = "{$cid}|{$day}";
            if (isset($seen[$k])) continue;

            $seen[$k] = true;
            $isNew = isset($earliestByClient[$cid]) && $earliestByClient[$cid] === $day;
            if (!isset($counts[$yr])) $counts[$yr] = ['new' => 0, 'old' => 0];

            $counts[$yr][$isNew ? 'new' : 'old']++;
        }

        ksort($counts);
        $out = [];

        foreach ($counts as $yr => $c) 
        {
            $out[] = ['label' => (string) $yr, 'new' => $c['new'], 'old' => $c['old']];
        }

        return $out;
    }
    
}