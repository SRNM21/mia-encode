<div id='cancel-edit-request-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Cancel Edit Agent Request</p>
            <button data-modal='cancel-edit-request-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <p>Are you sure you want to cancel this edit agent request?</p>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id='close-edit-request-btn' class='outline sm modal-cancel-btn' data-modal='cancel-edit-request-modal'>Close</button>
            <button id='confirm-edit-request-btn' class='primary sm confirm-edit-request-btn'>
                <div>Yes, Cancel Request</div>
                <?php get_component('loader')?>
            </button>
        </div>
    </div>
</div>