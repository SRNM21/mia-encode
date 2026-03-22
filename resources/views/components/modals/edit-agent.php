<div id='edit-agent-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p class="edit-agent-modal-title">Edit Agent Details</p>
            <button data-modal='edit-agent-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <div class="flex-col field-group">
                <label for="ea-agent">Agent</label>
                <input type="text" id="ea-agent" name="ea-agent" class="client-details-input" placeholder="e.g. greg" required>
            </div>
            <p class="edit-agent-note">
                Editing the agent will send an edit request to the team admin.
            </p>

            <?php get_component('error-card', [
                'class' => 'application-edit-error-card',
            ]) ?>
            
            <?php get_component('info-card', [
                'class' => 'application-edit-info-card',
            ]) ?>

        </div>
        <hr>
        <div class='modal-actions'>
            <button id="edit-agent-cancel" class="outline sm modal-cancel-btn" data-modal='edit-agent-modal'>Cancel</button>
            <button id="edit-agent-confirm" class="primary sm">
                <p>Send an edit request</p>
                <?php get_component('loader', [
                    'size' => 'sm',
                ]) ?>
            </button>
        </div>
    </div>
</div>