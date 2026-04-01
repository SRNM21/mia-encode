<table class="banks-table">
    <thead>
        <tr>
            <th class="sortable" data-key="name">Name</th>
            <th class="sortable" data-key="short_name">Short name</th>
            <th class="sortable" data-key="expiry">Expiration Months</th>
            <th class="sortable" data-key="is_active">Status</th>
            <!-- Not sortable as of now -->
            <th data-key="total">No. of Applications</th>
            <th class="sortable" data-key="created_at">Created at</th>
            <th class="sortable" data-key="updated_at">Updated at</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody id="banks-table-body">
        <?php if (count($banks) > 0): ?>
            <?php foreach ($banks as $bank): ?>      
                <tr 
                    data-id="<?= $bank['id'] ?>"
                    data-name="<?= $bank['name'] ?>"
                    data-short-name="<?= $bank['short_name'] ?>"
                    data-expiry-months="<?= $bank['expiry_months'] ?>"
                    data-is-active="<?= $bank['is_active'] ?>"
                    data-total="<?= $bank['total'] ?>"
                    data-last-update="<?= $bank['updated_at'] ?>"
                >
                    <td><?= $bank['name'] ?></td>
                    <td><?= $bank['short_name'] ?></td>
                    <td><?= $bank['expiry_months'] ?></td>
                    <td><?= $bank['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= number_format($bank['total'] ?? 0) ?></td>
                    <td><?= formatDate($bank['created_at'], 'M d, Y h:i A') ?></td>
                    <td><?= formatDate($bank['updated_at'], 'M d, Y h:i A') ?></td>
                    <td>
                        <button data-row-id='<?= $bank['id'] ?>' class="edit-bank-btn outline sm">
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
