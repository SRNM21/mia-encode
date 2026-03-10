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
                <?= get_component('sidebar') ?>

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
                            <div id="filter-popover" class="filter-popover hidden">
                                <div class="filter-modal card flex-col gap-16">
                                    <select id="filter-column">
                                        <option value="" disabled selected>Select column</option>
                                        <option value="last_name">Lastname</option>
                                        <option value="first_name">Firstname</option>
                                        <option value="middle_name">Middlename</option> 
                                        <option value="birthdate">Birthdate</option>
                                        <option value="mobile_num">Mobile Number</option>
                                        <option value="start_date">Start Date</option>
                                        <option value="end_date">End Date</option>
                                    </select>
                                    <input id="filter-value" type="text" placeholder="Value">
                                    <div class="flex-row filter-actions gap-16">
                                        <button id="filter-cancel" class="ghost">Cancel</button>
                                        <button id="filter-add" class="outline">Add</button>
                                    </div>
                                </div>
                            </div>
                            <div id="filter-bar" class="hidden filter-container flex-row gap-8">
                                
                            </div>
                            <div class="applications-container field-set">
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
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="application-table-body">
                                        <?php if (count($applications) > 0): ?>
                                            <?php foreach ($applications as $application): ?>
                                                <tr data-agent='<?= $application->agent_username ?>'>
                                                    <td data-raw='<?= $application->date_submitted ?>'><?= formatDate($application->date_submitted) ?></td>
                                                    <td class="lastname"><?= $application->lastname ?></td>
                                                    <td class="firstname"><?= $application->firstname ?></td>
                                                    <td class="middlename"><?= $application->middlename ?></td>
                                                    <td data-raw='<?= $application->birthdate ?>'><?= formatDate($application->birthdate) ?></td>
                                                    
                                                    <?php foreach ($banks as $bank): ?>
                                                        <td class="bank-check" data-bank-id="<?= $bank->id ?>">
                                                            <?= $application->bank_submitted_id == $bank->id ? get_icon('check') : '' ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td><?= $application->mobile_num ?></td>
                                                    <td><?= $application->agent ?></td>
                                                    <td>

                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="100%" class="empty-application-row">No applications found.</td>
                                            </tr>
                                        <?php endif?>
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
        
        <?= get_modal('select-date-range-export') ?>
        <?= get_modal('export-loading') ?>

        <?= js_jq('applications') ?>
    </body>
</html>
