<?php

namespace App\Services;

use App\Repository\BankApplicationRepository;

class BankApplicationService
{
    public function __construct(
        private BankApplicationRepository $bankApplicationRepository
    ) {}

    public function getClientApplications(int $client_id): array
    {
        return $this->bankApplicationRepository->getClientApplications($client_id);
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

    // TODO: FIX ALGO
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
