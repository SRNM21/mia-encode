<div id='view-edit-request-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Edit Agent Request Details</p>
            <button data-modal='view-edit-request-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <div>
                <p>Agent</p>
                <div class="flex-row agent-details-container">
                    <div class="field-group old-field">
                        <p class="field-title">Old</p>
                        <p id='data-old' class="field-value data-old"></p>
                    </div>
                    <div class="flex-center">
                        <div class="transform-icon">
                            <?php get_icon('chevrons-right') ?>
                        </div>
                    </div>
                    <div class="field-group">
                        <p class="field-title">New</p>
                        <p id='data-new' class="field-value data-new"></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="flex-col gap-16">
                <div class="flex-row client-details-header">
                    <p>Client application</p>
                    <p id="client-bank-date-submitted" class="client-bank-date-submitted"></p>
                </div>
                <div class="client-details-container">
                    <div class="field-group">
                        <p class="field-title">Firstname</p>
                        <p id='original-firstname' class="field-value original-firstname">N/A</p>
                    </div>
                    
                    <div class="field-group">
                        <p class="field-title">Middlename</p>
                        <p id='original-middlename' class="field-value original-middlename">N/A</p>
                    </div>
                    
                    <div class="field-group">
                        <p class="field-title">Lastname</p>
                        <p id='original-lastname' class="field-value original-lastname">N/A</p>
                    </div>
                    
                    <div class="field-group">
                        <p class="field-title">Birthdate</p>
                        <p id='original-birthdate' class="field-value original-birthdate">N/A</p>
                    </div>
                    
                    <div class="field-group">
                        <p class="field-title">Mobile Number</p>
                        <p id='original-mobile' class="field-value original-mobile">N/A</p>
                    </div>
                </div>
            </div>
            <div class="application-details-container">
                <div class="field-group">
                    <p class="field-title">Bank Applications</p>
                    <div id='original-banks' class="field-value original-banks flex-row">N/A</div>
                </div>
            </div>
            <hr>
            <div class="edit-request-datetime-container flex-col gap-8">
                <p class="edit-request-datetime"></p>
                <p class="action-request-datetime hidden"></p>
            </div>
            <?php if (isset($user) && $user->isAdmin()): ?>
                <div class="request-choice flex-row gap-16">
                    <button id='reject-edit-request-btn' class='outline reject-edit-request-btn'>
                        <p>
                            <?php get_icon('x') ?>
                            Reject
                        </p>
                        <?php get_component('loader', [
                            'size' => 'sm',
                        ]) ?>
                    </button>
                    <button id='approve-edit-request-btn' class='outline approve-edit-request-btn'>
                        <p>
                            <?php get_icon('check') ?>
                            Approve
                        </p>
                        <?php get_component('loader', [
                            'size' => 'sm',
                        ]) ?>
                    </button>
                </div>
            <?php endif ?>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id='cancel-view-edit-request-btn' class='outline sm modal-cancel-btn' data-modal='view-edit-request-modal'>Close</button>
        </div>
    </div>
</div>