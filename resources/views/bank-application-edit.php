<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Edit Bank Application - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('application-edit') ?>
    </head>

    <body>
        <div class='home-page flex-col'>

        <?php
            $lastName = trim($client->last_name ?? 'Client');
            $possessive = preg_match('/s$/i', $lastName)
                ? $lastName . "'"
                : $lastName . "'s";
        ?>

        <?= get_component('header', [
            'user' => $user,
            'breadcrumbs' => [
                ['label' => 'Bank Applications', 'url' => 'bank-applications'],
                ['label' => 'Edit ' . $possessive . ' application']
            ]
        ]) ?>

            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div class='container client-details-container flex-col'>
                            <div class="container-header flex-row">
                                <h1 class='title'>Edit Client Details</h1>
                                <div class="flex-row gap-16">
                                </div>
                            </div>
                            <div class="field-set form-grid">
                                <div class="flex-col field-group">
                                    <label for="firstname">Firstname</label>
                                    <input 
                                        type="text" 
                                        id="firstname" 
                                        name="firstname" 
                                        class="client-details-input" 
                                        placeholder="e.g. Juan" 
                                        required 
                                        data-raw="<?= htmlspecialchars($client->first_name ?? '') ?>"
                                        value="<?= htmlspecialchars($client->first_name ?? '') ?>"
                                    >
                                </div>

                                <div class="flex-col field-group">
                                    <label for="middlename">Middlename</label>
                                    <input 
                                        type="text" 
                                        id="middlename" 
                                        name="middlename" 
                                        class="client-details-input" 
                                        placeholder="e.g. Dela" 
                                        required 
                                        data-raw="<?= htmlspecialchars($client->middle_name ?? '') ?>"
                                        value="<?= htmlspecialchars($client->middle_name ?? '') ?>"
                                    >
                                </div>

                                <div class="flex-col field-group">
                                    <label for="lastname">Lastname</label>
                                    <input 
                                        type="text" 
                                        id="lastname" 
                                        name="lastname" 
                                        class="client-details-input" 
                                        placeholder="e.g. Cruz" 
                                        required 
                                        data-raw="<?= htmlspecialchars($client->last_name ?? '') ?>"
                                        value="<?= htmlspecialchars($client->last_name ?? '') ?>"
                                    >
                                </div>

                                <div class="flex-col field-group">
                                    <label for="birthdate">Birthdate</label>
                                    <input 
                                        type="text" 
                                        id="birthdate" 
                                        name="birthdate" 
                                        class="client-details-input" 
                                        placeholder="e.g. 01/01/2000" 
                                        autocomplete="off" 
                                        required 
                                        data-raw="<?= htmlspecialchars(formatDate($client->birthdate, 'm/d/Y') ?? '') ?>"
                                        value="<?= htmlspecialchars(formatDate($client->birthdate, 'm/d/Y') ?? '') ?>"
                                    >
                                </div>
                                
                                <div class="flex-col field-group">
                                    <label for="mobile">Mobile Number</label>
                                    <input 
                                        type="tel" 
                                        id="mobile" 
                                        name="mobile" 
                                        class="client-details-input" 
                                        placeholder="e.g. 09XXXXXXXXX" 
                                        maxlength="11" 
                                        required 
                                        data-raw="<?= htmlspecialchars($client->mobile_num ?? '') ?>"
                                        value="<?= htmlspecialchars($client->mobile_num ?? '') ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div id="bank-applications-container" class='bank-applications-container flex-col'>
                            <hr>
                            <div class="banks-container field-set">
                                <table class="bank-table">
                                    <thead>
                                        <tr>
                                            <th>Bank Name</th>
                                            <th>Last Submitted</th>
                                            <th>Agent</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($bank_rows as $row): ?>
                                            <tr class="<?= $row['row_class'] ?>">
                                                <td><?= htmlspecialchars($row['bank_name']) ?></td>
                                                <td><?= $row['date'] ?></td>
                                                <td><?= htmlspecialchars($row['agent']) ?></td>
                                                <td class="<?= $row['status_class'] ?>">
                                                    <?= $row['status_text'] ?>
                                                </td>
                                                <td
                                                    class="<?= $row['action_class'] ?>"
                                                    data-bank-name="<?= htmlspecialchars($row['bank_name']) ?>"
                                                    data-bank-id="<?= $row['bank_id'] ?>"
                                                >
                                                    <?= $row['is_unavailable'] ? get_icon('x') : ($row['is_edit_mode'] ? get_icon('check') : '') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php get_component('error-card', [
                                'class' => 'application-edit-error-card',
                            ]) ?>

                            <?php get_component('info-card', [
                                'class' => 'application-edit-info-card',
                            ]) ?>

                        </div>

                        <div class='flex-row container submit-content'>
                            <p class="submit-notes">You can edit any available application submitted on or after <?= formatDate($application->date_submitted) ?>.Past applications are locked for processing.</p>
                            <button type="button" class="save-button">
                                <p>Save</p>
                                <?php get_component('loader', [
                                    'size' => 'sm',
                                ]) ?>
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <script>
            const client = <?= json_encode($client->toArray()) ?>;
            const application = <?= json_encode($application->toArray()) ?>;
        </script>
        <?= js_jq('application-edit') ?>
    </body>
</html>
