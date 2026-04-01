<div id='add-bank-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p class="add-bank-modal-title">Bank Details</p>
            <button data-modal='add-bank-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <div class="flex-col grid gap-16">
                <div class="flex-col gap-8">
                    <label for="bank-name">Name</label>
                    <input 
                        type="text" 
                        id="bank-name"
                        name="bank-name"
                        class="bank-name"
                        placeholder="e.g. KeysiBank"
                        required
                    >
                </div>

                <div class="flex-col gap-8">
                    <label for="bank-short-name">Short name</label>
                    <input 
                        type="text" 
                        id="bank-short-name"
                        name="bank-short-name"
                        class="bank-short-name"
                        placeholder="e.g. KB"
                        required
                    >
                </div>
                
                <div class="flex-col gap-8 expiry-months-container">
                    <label for="bank-expiry-months">Expiry Months</label>
                    <div class="number-input flex-row gap-4">
                        <button type="button" class="decrement" aria-label="Decrease">−</button>
                        <input 
                            type="number"
                            id="bank-expiry-months"
                            name="bank-expiry-months"
                            class="bank-expiry-months"
                            min="1"
                            max="60"
                            value="1"
                            required
                        >
                        <button type="button" class="increment" aria-label="Increase">+</button>
                    </div>
                </div>

                <div class="flex-col gap-8">
                    <label for="bank-status">Status</label>
                    <select id="bank-status">
                        <option value="" selected disabled>Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>
            
            <?php get_component('error-card', [
                'class' => 'bank-error-card',
            ]) ?>

            <div class="last-update-note-container flex-row gap-4">
                <p>Last Updated at: </p>
                <p class="last-update-note"></p>
            </div>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id="add-bank-cancel" class="outline sm modal-cancel-btn" data-modal='add-bank-modal'>Cancel</button>
            <button id="add-bank-confirm" class="primary sm">
                <p>Add Bank</p>
                <?php get_component('loader', [
                    'size' => 'sm',
                ]) ?>
            </button>
        </div>
    </div>
</div>