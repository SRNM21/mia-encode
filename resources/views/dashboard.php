<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Dashboard - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('dashboard') ?>
    </head>

    <body>
        <div class='home-page flex-col'>
            
            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Dashboard'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>

                    <!-- Upper Charts -->
                    <div class="upper-chart-row flex-row">
                        <div class="sub-upper-chart-row flex-row">
                            <div class="card chart-card flex-col">
                                <div class="chart-control">
                                    <p class="chart-title">Clients Today</p>  
                                </div>
                                <div class="flex-row clients-today-container">
                                    <?= get_component('empty-chart', [
                                        'class' => 'empty-clients-today load',
                                        'text' => 'No clients today.'
                                    ]) ?>
                                    <div class="chart-container">
                                        <canvas id="clients-type-chart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="card chart-card flex-col">
                                <div class="chart-control">
                                    <p class="chart-title">Bank Applications Today</p>  
                                </div>
                                <div class="flex-row banks-today-container">
                                    <?= get_component('empty-chart', [
                                        'class' => 'empty-banks-today load',
                                        'text' => 'No bank applications today.'
                                    ]) ?>
                                    <div class="chart-container">
                                        <canvas id="bank-apps-type-chart"></canvas>
                                    </div>
                                    <div class="chart-legend flex-col gap-4" id="bank-apps-type-legend"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card chart-card leaderboards-card flex-col">
                            <div class="chart-control flex-row">
                                <a href="leaderboards" class="chart-title anchor">Agent Leaderboards</a>  
                                <select id="agent-leaderboards-select" class="size-sm">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                            <div class="leaderboards-wrapper flex-row">
                                <?= get_component('empty-chart', [
                                    'class' => 'empty-leaderboards load',
                                    'text' => 'No submissions as of now.'
                                ]) ?>
                                <div class="leaderboards-table-content"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lower Charts -->
                    <div class="lower-chart-col flex-col">
                        <div class="card bank-calendar-container">
                            <div class="flex-row bank-calendar-header">
                                <div id="bank-calendar-range-display"></div>

                                <div class="bank-calendar-filters flex-row gap-8">
                                    <select id="bank-calendar-week-select" class="size-sm"></select>
                                    <select id="bank-calendar-month-select" class="size-sm"></select>
                                    <select id="bank-calendar-year-select" class="size-sm"></select>
                                </div>
                            </div>

                            <div class="bank-calendar-table-content">
                                <?= get_component('empty-chart', [
                                    'class' => 'empty-bank-calendar load',
                                    'text' => 'No submissions as of now.'
                                ]) ?>
                                <table class="bank-calendar-table">
                                    <thead>
                                        <tr>
                                            <th>Bank</th>
                                            <th class="bank-calendar-th">Mon</th>
                                            <th class="bank-calendar-th">Tue</th>
                                            <th class="bank-calendar-th">Wed</th>
                                            <th class="bank-calendar-th">Thu</th>
                                            <th class="bank-calendar-th">Fri</th>
                                            <th class="bank-calendar-th">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>
                        <div class="card line-chart-card flex-col">
                            <div class="chart-control flex-row">
                                <p class="chart-title">Clients Trend</p>
                                <div class="line-chart-range flex-row">
                                    <select id="clients-series-select" class="size-sm">
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                    <select id="clients-year-select" class="size-sm"></select>
                                </div>
                            </div>
                            <div class="line-chart-container clients-series-container">
                                <?= get_component('empty-chart', [
                                    'class' => 'empty-clients-series load',
                                    'text' => 'No clients found.'
                                ]) ?>
                                <canvas id="clients-type-line"></canvas>
                            </div>
                        </div>

                        <div class="card line-chart-card flex-col">
                            <div class="chart-control flex-row">
                                <p class="chart-title">Bank Applications Trend</p>
                                <div class="line-chart-range flex-row">
                                    <select id="bank-apps-series-select" class="size-sm">
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                    <select id="bank-apps-year-select" class="size-sm"></select>
                                </div>
                            </div>
                            <div class="line-chart-container banks-series-container">     
                                <?= get_component('empty-chart', [
                                    'class' => 'empty-banks-series load',
                                    'text' => 'No bank applications found.'
                                ]) ?>
                                <canvas id="bank-applications-type-line"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?= js('vendor.chart_js') ?>
        <?= js_jq('dashboard') ?>
    </body>
</html>
