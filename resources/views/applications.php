<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Applications - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('applications') ?>
    </head>

    <body class='dark'>
        <div class='home-page flex-col'>
            
            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Bank Applications'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div 
                            id="applications-container" 
                            class='container applications-container flex-col'
                            data-page='<?= isset($meta) ? ($meta['page'] ?? 1) : 1 ?>'
                            data-per-page='<?= isset($meta) ? ($meta['per_page'] ?? 25) : 25 ?>'
                            data-last-page='<?= isset($meta) ? ($meta['last_page'] ?? 1) : 1 ?>'
                            data-total='<?= isset($meta) ? ($meta['total'] ?? 0) : 0 ?>'
                        >
                            <div class="container-header flex-row">
                                <button id="add-filter-btn" class="outline">
                                    Add Filter
                                    <?= get_icon('funnel-plus') ?>
                                </button>
                                <button id="export-excel" class="size-sm outline export-excel">
                                    <?= get_icon('sheet') ?>
                                    Export to Excel
                                </button>
                            </div>
                            <div id="filter-bar" class="hidden filter-container flex-row gap-8">
                            </div>
                            <div 
                                id='applications-container'
                                class="applications-container"
                                data-page='<?= $meta['page'] ?>'
                                data-per-page='<?= $meta['per_page'] ?>'
                                data-last-page='<?= $meta['last_page'] ?>'
                                data-total='<?= $meta['total'] ?>'
                            >
                                <div class="table-wrapper">
                                    <?php get_component('applications-table', [
                                        'user' => $user,
                                        'banks' => $banks,
                                        'applications' => $applications,
                                        'request_map' => $request_map
                                    ]) ?>
                                </div>
                            </div>
                            <div class="pagination-controls gap-16">
                                <div class="pagination-right gap-16">
                                    <label for="applications-per-page">Rows per page</label>
                                    <select id="applications-per-page" class="size-sm">
                                        <option value="25" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 25) ? 'selected' : '' ?>>25</option>
                                        <option value="50" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 50) ? 'selected' : '' ?>>50</option>
                                        <option value="100" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 100) ? 'selected' : '' ?>>100</option>
                                        <option value="500" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 500) ? 'selected' : '' ?>>500</option>
                                    </select>
                                    <span id="applications-page-info" class="applications-page-info">
                                        <?php if (isset($meta)): ?>
                                            Page <?= (int) $meta['page'] ?> of <?= (int) $meta['last_page'] ?> • <?= (int) $meta['total'] ?> rows
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="pagination-left gap-16">
                                    <button id="applications-start" class="outline" <?= (int) $meta['page'] == 1 ? 'disabled' : '' ?>>Start</button>
                                    <button id="applications-prev" class="outline" <?= (int) $meta['page'] == 1 ? 'disabled' : '' ?>>Previous</button>
                                    <button id="applications-next" class="outline" <?= (int) $meta['page'] >= (int) $meta['last_page'] ? 'disabled' : '' ?>>Next</button>
                                    <button id="applications-last" class="outline" <?= (int) $meta['page'] >= (int) $meta['last_page'] ? 'disabled' : '' ?>>Last</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <div id="global-dropdown-root" class="global-dropdown-root"></div>
        
        <?= get_modal('edit-application') ?>
        <?= get_modal('edit-agent') ?>

        <?= get_modal('view-edit-request') ?>
        <?= get_modal('cancel-edit-request') ?>
        
        <?= get_modal('add-filter') ?>

        <?= get_modal('select-date-range-export') ?>
        <?= get_modal('export-loading') ?>

        <?= js_jq('applications') ?>
    </body>
</html>