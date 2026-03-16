<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Banks - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('banks') ?>
    </head>

    <body class='dark'>
        <div class='home-page flex-col'>

            <?= get_component('header', [
                'title' => 'Banks',
                'user' => $user,
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['role' => $user->role]) ?>

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
                                <table class="banks-table">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-key="name">Name</th>
                                            <th class="sortable" data-key="short_name">Short name</th>
                                            <th class="sortable" data-key="expiry">Expiration Months</th>
                                            <th class="sortable" data-key="is_active">Status</th>
                                            <th class="sortable" data-key="total">No. of Applications</th>
                                            <th class="sortable" data-key="created_at">Created at</th>
                                            <th class="sortable" data-key="updated_at">Updated at</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="banks-table-body">
                                        <?php if (count($banks) > 0): ?>
                                            <?php foreach ($banks as $bank): ?>      
                                                <tr 
                                                    data-id="<?= $bank->id ?>"
                                                    data-name="<?= $bank->name ?>"
                                                    data-short-name="<?= $bank->short_name ?>"
                                                    data-expiry-months="<?= $bank->expiry_months ?>"
                                                    data-is-active="<?= $bank->is_active ?>"
                                                    data-total="<?= $bank->total ?>"
                                                    data-last-update="<?= $bank->updated_at ?>"
                                                >
                                                    <td><?= $bank->name ?></td>
                                                    <td><?= $bank->short_name ?></td>
                                                    <td><?= $bank->expiry_months ?></td>
                                                    <td><?= $bank->is_active ? 'Active' : 'Inactive' ?></td>
                                                    <td><?= number_format($bank->total ?? 0) ?></td>
                                                    <td><?= formatDate($bank->created_at, 'M d, Y h:i A') ?></td>
                                                    <td><?= formatDate($bank->updated_at, 'M d, Y h:i A') ?></td>
                                                    <td>
                                                        <button data-row-id='<?= $bank->id ?>' class="edit-bank-btn outline sm">
                                                            <?php get_icon('pencil') ?>
                                                            Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="100%" class="empty-banks-row">No banks found.</td>
                                            </tr>
                                        <?php endif?>
                                    </tbody>
                                </table>
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
