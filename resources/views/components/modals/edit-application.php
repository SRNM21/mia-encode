<div id='edit-application-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p class="edit-application-modal-title">Edit Application Details</p>
            <button data-modal='edit-application-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>

            <div class="field-set form-grid">
                <div class="flex-col field-group">
                    <label for="ea-firstname">Firstname</label>
                    <input type="text" id="ea-firstname" name="ea-firstname" class="client-details-input" placeholder="e.g. Juan" required>
                </div>

                <div class="flex-col field-group">
                    <label for="ea-middlename">Middlename</label>
                    <input type="text" id="ea-middlename" name="ea-middlename" class="client-details-input" placeholder="e.g. Dela" required>
                </div>

                <div class="flex-col field-group">
                    <label for="ea-lastname">Lastname</label>
                    <input type="text" id="ea-lastname" name="ea-lastname" class="client-details-input" placeholder="e.g. Cruz" required>
                </div>

                <div class="flex-col field-group">
                    <label for="ea-birthdate">Birthdate</label>
                    <input type="text" id="ea-birthdate" name="ea-birthdate" class="client-details-input" placeholder="e.g. 01/01/2000" autocomplete="off" required>
                </div>
                
                <div class="flex-col field-group">
                    <label for="ea-mobile">Mobile Number</label>
                    <input type="tel" id="ea-mobile" name="ea-mobile" class="client-details-input" placeholder="e.g. 09XXXXXXXXX" maxlength="11" required>
                </div>

            </div>

            <?php get_component('error-card', [
                'class' => 'application-edit-error-card',
            ]) ?>
            
            <?php get_component('info-card', [
                'class' => 'application-edit-info-card',
            ]) ?>

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

        </div>
        <hr>
        <div class='modal-actions'>
            <button id="edit-application-cancel" class="outline sm modal-cancel-btn" data-modal='edit-application-modal'>Cancel</button>
            <button id="edit-application-confirm" class="primary sm">
                <div>Save</div>
                <?php get_component('loader')?>
            </button>
        </div>
    </div>
</div>