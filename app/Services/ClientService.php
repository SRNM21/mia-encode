<?php

namespace App\Services;

use App\Http\Request\Request;
use App\Models\BankApplication;
use App\Repository\ClientRepository;

class ClientService
{
    public function __construct(
        private ClientRepository $clientRepository, 
    ) {}

    // -------------------------------
    // Encoding
    // -------------------------------

    public function getClient(Request $request)
    {
        return $this->clientRepository->getClient($request);
    }

    public function createClientFromRequest(Request $request)
    {
        return $this->clientRepository->createClientFromRequest($request);
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
        
        // TODO: BEFORE PROD
        // return [
        //     'new' => 10,
        //     'old' => 14,
        // ];

        return [
            'new' => $newCount,
            'old' => $oldCount,
        ];
    }

    public function clientTypeSeries($scope, $year): array
    {
        $applications = $this->getApplications();
        $availableYears = $this->getAvailableYearsFromApplications($applications);
        $scope = $scope ?: 'daily';
        $requestedYear = $year ?: (count($availableYears) ? max($availableYears) : date('Y'));
        $year = (string) $requestedYear;

        $earliestByClient = $this->computeEarliestDayByClient($applications);
        $series = [];

        if (in_array($scope, ['daily', 'weekly', 'monthly'], true)) 
        {
            $countsByDay = $this->countClientTypesByDayForYear($applications, $year, $earliestByClient);
            
            if ($scope === 'daily') 
            {
                $series['daily'] = $this->buildDailySeries($countsByDay, $year);
            } 
            elseif ($scope === 'weekly') 
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

            $k = $cid . '|' . $day;
            if (isset($seen[$k])) continue;

            $seen[$k] = true;
            $isNew = isset($earliestByClient[$cid]) && $earliestByClient[$cid] === $day;

            if (!isset($counts[$day])) $counts[$day] = ['new' => 0, 'old' => 0];
            $counts[$day][$isNew ? 'new' : 'old']++;
        }

        ksort($counts);
        return $counts;
    }

    private function buildDailySeries(array $countsByDay, string $year): array
    {
        $out = [];
        $start = new \DateTimeImmutable($year . '-01-01');
        $end = new \DateTimeImmutable($year . '-12-31');

        for ($d = $start; $d <= $end; $d = $d->add(new \DateInterval('P1D'))) 
        {
            $key = $d->format('Y-m-d');
            $out[] = [
                'label' => $d->format('D, M j'),
                'new' => $countsByDay[$key]['new'] ?? 0,
                'old' => $countsByDay[$key]['old'] ?? 0,
            ];
        }

        return $out;
    }

    private function buildWeeklySeries(array $countsByDay, string $year): array
    {
        $weekly = [];

        for ($m = 1; $m <= 12; $m++) 
        {
            $monthStr = sprintf('%02d', $m);
            $monthNameShort = date('M', strtotime($year . '-' . $monthStr . '-01'));
            $firstOfMonth = new \DateTimeImmutable($year . '-' . $monthStr . '-01');
            $lastOfMonth = (new \DateTimeImmutable($year . '-' . $monthStr . '-01'))->modify('last day of this month');
            $firstMonday = $firstOfMonth;
            
            if ((int)$firstMonday->format('N') !== 1) $firstMonday = $firstMonday->modify('next monday');

            $weekIndex = 1;
            for ($ws = $firstMonday; $ws <= $lastOfMonth; $ws = $ws->add(new \DateInterval('P7D'))) 
            {
                $we = $ws->add(new \DateInterval('P6D'));
                if ((int) $ws->format('m') !== $m) break;

                $sumNew = 0;
                $sumOld = 0;

                for ($cur = $ws; $cur <= $we; $cur = $cur->add(new \DateInterval('P1D'))) 
                {
                    if ((int) $cur->format('m') !== $m) continue;
                    $key = $cur->format('Y-m-d');
                    $sumNew += $countsByDay[$key]['new'] ?? 0;
                    $sumOld += $countsByDay[$key]['old'] ?? 0;
                }

                $weekly[] = ['label' => $monthNameShort . ' W' . $weekIndex, 'new' => $sumNew, 'old' => $sumOld];
                $weekIndex++;
            }
        }

        return $weekly;
    }

    private function buildMonthlySeries(array $countsByDay, string $year): array
    {
        $monthly = [];

        for ($m = 1; $m <= 12; $m++) 
        {
            $monthStr = sprintf('%02d', $m);
            $monthNameFull = date('F', strtotime($year . '-' . $monthStr . '-01'));
            $sumNew = 0;
            $sumOld = 0;
            $firstOfMonth = new \DateTimeImmutable($year . '-' . $monthStr . '-01');
            $lastOfMonth = (new \DateTimeImmutable($year . '-' . $monthStr . '-01'))->modify('last day of this month');

            for ($d = $firstOfMonth; $d <= $lastOfMonth; $d = $d->add(new \DateInterval('P1D'))) 
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
            $k = $cid . '|' . $day;
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