<div id='select-date-export-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Select Date Range</p>
            <button data-modal='select-date-export-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <p>Please select what date you want to export.</p>

            <div class="date-range-grid gap-16">
                <div class="flex-col field-group gap-8">
                    <label for="start-date">Start</label>
                    <input type="text" id="start-date" name="start-date" class="range-date-input" placeholder="e.g. 01/01/2000" autocomplete="off" required>
                </div>
                <div class="flex-col field-group gap-8">
                    <label for="end-date">End</label>
                    <input type="text" id="end-date" name="end-date" class="range-date-input" placeholder="e.g. 01/01/2000" autocomplete="off" required>
                </div>
            </div>
            
            <?php get_component('error-card', [
                'class' => 'range-date-error-card',
            ]) ?>

            <p class="export-note">Note: The export can not be cancelled once started.</p>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id='cancel-export-btn' class='outline sm modal-cancel-btn' data-modal='select-date-export-modal'>Cancel</button>
            <button id='confirm-export-btn' class='primary sm confirm-export-btn'>Export</button>
        </div>
    </div>
</div>