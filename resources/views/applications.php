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
                'title' => 'Bank Applications',
                'user' => $user,
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
                                    <table class="applications-table">
                                        <thead>
                                            <tr>
                                                <th class="sortable" data-key="date_submitted">Date Submitted</th>
                                                <th class="sortable" data-key="last_name">Lastname</th>
                                                <th class="sortable" data-key="first_name">Firstname</th>
                                                <th class="sortable" data-key="middle_name">Middlename</th>
                                                <th class="sortable" data-key="birthdate">Birthdate</th>

                                                <?php foreach ($banks as $bank): ?>
                                                    <th class='bank-name'><?= $bank->short_name ?></th>
                                                <?php endforeach; ?>

                                                <th>Mobile Number</th>
                                                <th>Agent</th>
                                                
                                                <?php if ($user->isEncoder()): ?>
                                                    <th>Action</th>
                                                <?php endif ?>
                                            </tr>
                                        </thead>

                                        <tbody id="application-table-body">
                                        <?php if (count($applications) > 0): ?>
                                            <?php foreach ($applications as $application): ?>
                                                <tr 
                                                    data-id='<?= $application['id'] ?>'
                                                    data-firstname='<?= $application['first_name'] ?>'
                                                    data-middlename='<?= $application['middle_name'] ?>'
                                                    data-lastname='<?= $application['last_name'] ?>'
                                                    data-birthdate='<?= $application['birthdate'] ?>'
                                                    data-mobile='<?= $application['mobile_num'] ?>'
                                                    data-agent='<?= $application['agent'] ?>'

                                                    <?php if (isset($application['request_edit_id'])): ?> 
                                                        data-request-edit-id='<?= $application['request_edit_id'] ?>' 
                                                        data-request-new-content='<?= $application['request_new_content'] ?>' 
                                                        data-request-datetime='<?= $application['request_datetime'] ?>' 
                                                    <?php endif ?>
                                                >
                                                    <td data-raw='<?= $application['date_submitted'] ?>'>
                                                        <?= formatDate($application['date_submitted']) ?>
                                                    </td>

                                                    <td class="lastname"><?= $application['last_name'] ?></td>
                                                    <td class="firstname"><?= $application['first_name'] ?></td>
                                                    <td class="middlename"><?= $application['middle_name'] ?></td>

                                                    <td data-raw='<?= $application['birthdate'] ?>'>
                                                        <?= formatDate($application['birthdate']) ?>
                                                    </td>

                                                    <?php 
                                                        $submittedBanks = array_flip($application['banks'] ?? []);
                                                        foreach ($banks as $bank):
                                                    ?>
                                                        <td class="bank-check" data-bank-id="<?= $bank->id ?>">
                                                            <?= isset($submittedBanks[$bank->id]) ? get_icon('check') : '' ?>
                                                        </td>
                                                    <?php endforeach; ?>

                                                    <td><?= $application['mobile_num'] ?></td>
                                                    <td><?= $application['agent'] ?></td>

                                                    <?php 
                                                        $request_edit_status = $application['request_status'] ?? '';
                                                        if ($user->isEncoder()):
                                                    ?>
                                                        <td>
                                                            <?php if ($request_edit_status == 'pending'): ?>
                                                                <div class="flex-row gap-8">
                                                                    <button 
                                                                        data-request-edit-id='<?= $application['request_edit_id'] ?>'
                                                                        class="outline sm cancel-edit-application-btn"
                                                                    >
                                                                        <?php get_icon('x') ?>
                                                                        Cancel Request
                                                                    </button>

                                                                    <button class="outline sm view-edit-application-btn">
                                                                        <?php get_icon('eye') ?>
                                                                        View
                                                                    </button>
                                                                </div>
                                                            <?php else: ?>
                                                                <button class="outline sm edit-application-btn">
                                                                    <?php get_icon('pencil') ?>
                                                                    Request Edit
                                                                </button>
                                                            <?php endif ?>
                                                        </td>
                                                    <?php endif ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="100%" class="empty-application-row">
                                                    No applications found.
                                                </td>
                                            </tr>
                                        <?php endif ?>
                                        </tbody>
                                    </table>
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
        
        <?= get_modal('view-edit-request') ?>
        <?= get_modal('cancel-edit-request') ?>
        <?= get_modal('edit-application') ?>
        <?= get_modal('add-filter') ?>
        <?= get_modal('select-date-range-export') ?>
        <?= get_modal('export-loading') ?>

        <?= js_jq('applications') ?>
    </body>
</html>