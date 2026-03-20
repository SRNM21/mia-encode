<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Leaderboards - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('leaderboards') ?>
    </head>

    <body class='dark'>
        <div class='home-page flex-col'>

            <?= get_component('header', [
                'title' => 'Leaderboards',
                'user' => $user,
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div class="card leaderboard-card">
                            <!-- Header -->
                            <div class="leaderboard-header flex-row">
                                <p>Top 100 Agents</p>
                                <select id="leaderboards-filter" class="size-sm">
                                    <option <?= $filter == 'today' ? 'selected' : '' ?> value="today">Today</option>
                                    <option <?= $filter == 'week' ? 'selected' : '' ?> value="week">Weekly</option>
                                    <option <?= $filter == 'month' ? 'selected' : '' ?> value="month">Monthly</option>
                                    <option <?= $filter == 'year' ? 'selected' : '' ?> value="year">Yearly</option>
                                    <option <?= $filter == 'all' ? 'selected' : '' ?> value="all">All Time</option>
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
                                                        <td><?= $rank++ ?></td>
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
                    </div>
                </div>
            </main>
        </div>
        <?= js_jq('leaderboards') ?>
    </body>
</html>
