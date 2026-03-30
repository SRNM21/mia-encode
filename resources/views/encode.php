<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Encode - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('encode') ?>
    </head>

    <body>
        <div class='home-page flex-col'>
            
            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Encode'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-col'>
                        <div class='container client-details-container flex-col'>
                            <div class="container-header flex-row">
                                <h1 class='title'>Client Details</h1>
                                <div class="flex-row gap-16">
                                    <button type="button" id="clear-client-button" class="outline clear-client-button" tabindex="7">
                                        <?= get_icon('eraser') ?>
                                        Clear
                                    </button>
                                    <button type="button" id="check-client-button" class="outline check-client-button" tabindex="8">
                                        <?= get_icon('file-search-corner') ?>
                                        Check Client
                                    </button>
                                </div>
                            </div>
                            <div class="field-set form-grid">
                                
                                <div class="flex-col field-group">
                                    <label for="lastname">Lastname</label>
                                    <input type="text" id="lastname" name="lastname" class="client-details-input" placeholder="e.g. Cruz" required tabindex="1">
                                </div>
                                
                                <div class="flex-col field-group">
                                    <label for="middlename">Middlename</label>
                                    <input type="text" id="middlename" name="middlename" class="client-details-input" placeholder="e.g. Dela" required tabindex="2">
                                </div>

                                <div class="flex-col field-group">
                                    <label for="firstname">Firstname</label>
                                    <input type="text" id="firstname" name="firstname" class="client-details-input" placeholder="e.g. Juan" required tabindex="3">
                                </div>

                                <div class="flex-col field-group">
                                    <label for="birthdate">Birthdate</label>
                                    <input type="text" id="birthdate" name="birthdate" class="client-details-input" placeholder="e.g. mm/dd/yyyy" autocomplete="off" required tabindex="4">
                                </div>
                                
                                <div class="flex-col field-group">
                                    <label for="mobile">Mobile Number</label>
                                    <input type="tel" id="mobile" name="mobile" class="client-details-input" placeholder="e.g. 09XXXXXXXXX" maxlength="11" required tabindex="5">
                                </div>

                                <div class="flex-col field-group">
                                    <label for="agent">Agent</label>
                                    <input type="text" id="agent" name="agent" class="client-details-input" placeholder="e.g. Greg" required tabindex="6">
                                </div>
                            </div>

                            <?php get_component('error-card', [
                                'class' => 'check-client-error-card',
                            ]) ?>

                        </div>
                        <div id="bank-applications-container" class='hidden bank-applications-container flex-col'>
                            <hr>
                            <div class="container-header flex-row">
                                <h1 class='title'>Bank Applications</h1>
                                <span class='hidden client-badge new-client'>New Client</span>
                                <span class='hidden client-badge old-client'>Old Client</span>
                            </div>
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

                                    <tbody id="bank-table-body"></tbody>
                                </table>
                            </div>

                            <?php get_component('error-card', [
                                'class' => 'store-client-error-card',
                            ]) ?>

                        </div>

                        <div class='hidden container submit-content'>
                            <p class="submit-notes">Please select at least one bank and assign an agent to proceed.</p>
                            <button type="button" class="submit-button">
                                Submit
                            </button>
                        </div>

                    </div>
                </div>
            </main>
        </div>
        <?= js_jq('encode') ?>
    </body>
</html>
