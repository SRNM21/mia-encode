<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Requests - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('requests') ?>
    </head>

    <body>
        <div class='home-page flex-col'>

            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Requests'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div class="header flex-row">
                            <select id="request-order-select" class="">
                                <option <?= $order == 'desc' ? 'selected' : '' ?> value="desc">Latest to Oldest</option>
                                <option <?= $order == 'asc' ? 'selected' : '' ?> value="asc">Oldest to Latest</option>
                            </select>
                            
                            <select id="request-filter-select" class="">
                                <option <?= $filter == 'all' ? 'selected' : '' ?> value="all">All</option>
                                <option <?= $filter == 'pending' ? 'selected' : '' ?> value="pending">Pending</option>
                                <option <?= $filter == 'approved' ? 'selected' : '' ?> value="approved">Approved</option>
                                <option <?= $filter == 'rejected' ? 'selected' : '' ?> value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="flex-col gap-16">
                            <?php if (count($requests) > 0): ?>
                                <?php foreach ($requests as $request): ?>
                                    <?php get_component('request-card', [
                                        'request' => $request,
                                        'banks' => $banks
                                    ]) ?>
                                <?php endforeach ?>
                            <?php else: ?>
                                <?php get_component('empty-chart', ['text' => 'No request for now.']) ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <?= get_modal('view-edit-request', [
            'user' => $user
        ]) ?>

        <?= js_jq('requests') ?>
    </body>
</html>
