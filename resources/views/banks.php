<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Banks - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('banks') ?>
    </head>

    <body>
        <div class='home-page flex-col'>
            
            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Banks'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div class="container-header flex-row">
                            <button id="add-bank-btn" class="size-sm primary add-bank-btn">
                                <?= get_icon('circle-plus') ?>
                                Add Bank
                            </button>
                        </div>
                        <div 
                            id='banks-container'
                            class="banks-container"
                            data-page='<?= $meta['page'] ?>'
                            data-per-page='<?= $meta['per_page'] ?>'
                            data-last-page='<?= $meta['last_page'] ?>'
                            data-total='<?= $meta['total'] ?>'
                        >
                            <div class="table-wrapper">
                                <?php get_component('banks-table', ['banks' => $banks]) ?>
                            </div>
                        </div>
                        <div class="pagination-controls gap-16">
                            <div class="pagination-right gap-16">
                                <label for="banks-per-page">Rows per page</label>
                                <select id="banks-per-page" class="size-sm">
                                    <option value="25" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 25) ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 50) ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 100) ? 'selected' : '' ?>>100</option>
                                    <option value="500" <?= (isset($meta) && ($meta['per_page'] ?? 25) == 500) ? 'selected' : '' ?>>500</option>
                                </select>
                                <span id="banks-page-info" class="banks-page-info">
                                    <?php if (isset($meta)): ?>
                                        Page <?= (int) $meta['page'] ?> of <?= (int) $meta['last_page'] ?> • <?= (int) $meta['total'] ?> rows
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="pagination-left gap-16">
                                <button id="banks-start" class="outline" <?= (int) $meta['page'] == 1 ? 'disabled' : '' ?>>Start</button>
                                <button id="banks-prev" class="outline" <?= (int) $meta['page'] == 1 ? 'disabled' : '' ?>>Previous</button>
                                <button id="banks-next" class="outline" <?= (int) $meta['page'] >= (int) $meta['last_page'] ? 'disabled' : '' ?>>Next</button>
                                <button id="banks-last" class="outline" <?= (int) $meta['page'] >= (int) $meta['last_page'] ? 'disabled' : '' ?>>Last</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <?= get_modal('add-bank') ?>

        <?= js_jq('banks') ?>
    </body>
</html>
