<div id='view-edit-request-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Edit Request Details</p>
            <button data-modal='view-edit-request-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <div class="flex-row">
                <div class="flex-col gap-8">
                    <p>Current</p>
                    <div class="field-grid">
                        <div class="field-group">
                            <p class="field-title">Firstname</p>
                            <p id='original-firstname' class="field-value original-firstname">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Middlename</p>
                            <p id='original-middlename' class="field-value original-middlename">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Lastname</p>
                            <p id='original-lastname' class="field-value original-lastname">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Birthdate</p>
                            <p id='original-birthdate' class="field-value original-birthdate">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Mobile Number</p>
                            <p id='original-mobile' class="field-value original-mobile">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Agent</p>
                            <p id='original-agent' class="field-value original-agent">Test</p>
                        </div>
                    </div>
                </div>
                <div class="flex-center">
                    <div class="transform-icon">
                        <?php get_icon('chevrons-right') ?>
                    </div>
                </div>
                <div class="flex-col gap-8">
                    <p>New</p>
                    <div class="field-grid">
                        <div class="field-group">
                            <p class="field-title">Firstname</p>
                            <p id='edit-firstname' class="field-value edit-firstname">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Middlename</p>
                            <p id='edit-middlename' class="field-value edit-middlename">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Lastname</p>
                            <p id='edit-lastname' class="field-value edit-lastname">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Birthdate</p>
                            <p id='edit-birthdate' class="field-value edit-birthdate">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Mobile Number</p>
                            <p id='edit-mobile' class="field-value edit-mobile">Test</p>
                        </div>
                        
                        <div class="field-group">
                            <p class="field-title">Agent</p>
                            <p id='edit-agent' class="field-value edit-agent">Test</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="edit-request-datetime-container flex-col gap-8">
                <p class="edit-request-datetime"></p>
                <p class="action-request-datetime hidden"></p>
            </div>
            <?php if (isset($role) && $role == 'ADMIN'): ?>
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