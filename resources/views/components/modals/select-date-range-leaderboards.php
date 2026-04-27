
<div id='select-date-leaderboards-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Select Date Range</p>
            <button data-modal='select-date-leaderboards-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <p>Please select a custom date range to filter the leaderboard.</p>

            <div class="date-range-grid gap-16">
                <div class="flex-col field-group gap-8">
                    <label for="leaderboard-start-date">Start</label>
                    <input type="text" id="leaderboard-start-date" name="start-date" class="range-date-input" placeholder="e.g. 01/01/2000" autocomplete="off" required>
                </div>
                <div class="flex-col field-group gap-8">
                    <label for="leaderboard-end-date">End</label>
                    <input type="text" id="leaderboard-end-date" name="end-date" class="range-date-input" placeholder="e.g. 01/01/2000" autocomplete="off" required>
                </div>
            </div>

            <?php get_component('error-card', [
                'class' => 'range-date-error-card',
            ]) ?>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id='cancel-leaderboard-date-btn' class='outline sm modal-cancel-btn' data-modal='select-date-leaderboards-modal'>Cancel</button>
            <button id='confirm-leaderboard-date-btn' class='primary sm confirm-export-btn'>
                <div class="flex-row gap-8 align-center">
                    Apply
                </div>
                <?php get_component('loader', ['size' => '16px']) ?>
            </button>
        </div>
    </div>
</div>
