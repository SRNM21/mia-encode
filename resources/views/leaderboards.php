<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Leaderboards - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('leaderboards') ?>
    </head>

    <body>
        <div class='home-page flex-col'>
            
            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Leaderboards'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div class="flex-row">
                            <div class="tabs-container">
                                <div class="tab-indicator"></div>
                                <a href="#submissions" class="tabs" data-tab="submissions-card">Submissions</a>
                                <a href="#bank-types" class="tabs" data-tab="bank-type-leaderboard-card">Bank Types</a>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="card submissions-card">
                                <!-- Header -->
                                <div class="leaderboard-header flex-row">
                                    <p>Top Agents</p>
                                    <select id="leaderboards-filter" class="size-sm">
                                        <option <?= $filter_sub == 'today' ? 'selected' : '' ?> value="today">Today</option>
                                        <option <?= $filter_sub == 'week' ? 'selected' : '' ?> value="week">Weekly</option>
                                        <option <?= $filter_sub == 'month' ? 'selected' : '' ?> value="month">Monthly</option>
                                        <option <?= $filter_sub == 'year' ? 'selected' : '' ?> value="year">Yearly</option>
                                        <option <?= $filter_sub == 'all' ? 'selected' : '' ?> value="all">All Time</option>
                                    </select>
                                </div>

                                <!-- Podium -->
                                <div class="leaderboard-podium flex-row">
                                    <?php if ($podium['first'] == null): ?>
                                        <div class="empty-leaderboards flex-center flex-col">
                                            <?= get_icon('mia_icon') ?>
                                            <p>No submissions found.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="podium-wrapper flex-row">
                                            <?php if ($podium['second'] != null): ?>
                                                <div class="podium-container podium-container-second">
                                                    <img src="resources/images/podium-2.png" alt="">
                                                    <div class="podium podium-second">
                                                        <p class="podium-agent-name">
                                                            <?= $podium['second']['agent'] ?? '-' ?>
                                                        </p>
                                                        <p class="podium-agent-score">
                                                            <?= $podium['second']['submissions'] ?? 0 ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endif ?>

                                            <div class="podium-container podium-container-first">
                                                <img src="resources/images/podium-1.png" alt="">
                                                <div class="podium podium-first">
                                                    <p class="podium-agent-name">
                                                        <?= $podium['first']['agent'] ?? '-' ?>
                                                    </p>
                                                    <p class="podium-agent-score">
                                                        <?= $podium['first']['submissions'] ?? 0 ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <?php if ($podium['third'] != null): ?>
                                                <div class="podium-container podium-container-third">
                                                    <img src="resources/images/podium-3.png" alt="">
                                                    <div class="podium podium-third">
                                                        <p class="podium-agent-name">
                                                            <?= $podium['third']['agent'] ?? '-' ?>
                                                        </p>
                                                        <p class="podium-agent-score">
                                                            <?= $podium['third']['submissions'] ?? 0 ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                    <?php endif ?>
                                </div>

                                <?php if ($rankings != null): ?>
                                    <div class="leaderboard-table-wrapper">
                                        <div class="leaderboard-table-container">
                                            <table class="leaderboard-table">
                                                <thead>
                                                    <th>Rank</th>
                                                    <th>Agent</th>
                                                    <th>Submissions</th>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $rank = 4;
                                                        foreach ($rankings as $row):
                                                    ?>

                                                        <tr>
                                                            <td>#<?= $rank++ ?></td>
                                                            <td><?= htmlspecialchars($row['agent']) ?></td>
                                                            <td><?= $row['submissions'] ?></td>
                                                        </tr>

                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif ?>
                            </div>
                            <div class="card bank-type-leaderboard-card">
                                <div class="leaderboard-header flex-row">
                                    <p>Bank Applications per Agent</p>
                                    
                                    <select id="bank-type-leaderboard-filter" class="size-sm">
                                        <option <?= $filter_bank == 'today' ? 'selected' : '' ?> value="today">Today</option>
                                        <option <?= $filter_bank == 'week' ? 'selected' : '' ?> value="week">Weekly</option>
                                        <option <?= $filter_bank == 'month' ? 'selected' : '' ?> value="month">Monthly</option>
                                        <option <?= $filter_bank == 'year' ? 'selected' : '' ?> value="year">Yearly</option>
                                        <option <?= $filter_bank == 'all' ? 'selected' : '' ?> value="all">All Time</option>
                                    </select>
                                </div>

                                <div class="leaderboard-table-wrapper">
                                    <div class="leaderboard-table-container horizontal-scroll">
                                        <table class="leaderboard-table bank-breakdown-table">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Agent</th>
                                                    
                                                    <?php foreach ($banks as $bank): ?>
                                                        <th><?= htmlspecialchars($bank->short_name ?: $bank->name) ?></th>
                                                    <?php endforeach; ?>

                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($bank_leaderboards)): ?>
                                                    <?php 
                                                        $rank = 1; 
                                                    ?>
                                                    <?php foreach ($bank_leaderboards as $row): ?>
                                                        <tr>
                                                            <td>#<?= $rank++ ?></td>
                                                            <td ><strong><?= htmlspecialchars($row['agent']) ?></strong></td>
                                                            
                                                            <?php foreach ($banks as $bank): ?>
                                                                <?php 
                                                                    $bankKey = strtoupper($bank->name); 
                                                                    $submissionCount = $row[$bankKey] ?? 0; 
                                                                ?>
                                                                <td class="<?= $submissionCount == 0 ? 'zero' : '' ?>"><?= $submissionCount ?></td>
                                                            <?php endforeach; ?>

                                                            <td class="total-highlight"><?= number_format($row['total']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="<?= count($banks) + 3 ?>" style="text-align:center;" class="no-data">No bank data available.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?= js_jq('leaderboards') ?>
    </body>
</html>
