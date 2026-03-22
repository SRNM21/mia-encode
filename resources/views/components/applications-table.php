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

                <?php 
                    if (isset($request_map[$application['id']])):
                    $request = $request_map[$application['id']];
                ?> 
                    data-request-edit-id='<?= $request['id'] ?>' 
                    data-request-new='<?= $request['new'] ?>' 
                    data-request-status='<?= $request['status'] ?>' 
                    data-request-datetime='<?= $request['datetime_request'] ?>' 
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
                        <button 
                            class="outline sm dropdown-trigger"
                            data-id="<?= $application['id'] ?>"
                        >
                            Actions
                            <?php get_icon('chevron-down') ?> 
                        </button>
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