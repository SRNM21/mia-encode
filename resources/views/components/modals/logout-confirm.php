<div id='logout-confirm-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Log out Confirmation</p>
            <button data-modal='logout-confirm-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <p>Are you sure you want to log out?</p>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id='cancel-logout-btn' class='outline sm modal-cancel-btn' data-modal='logout-confirm-modal'>Cancel</button>
            <button id='confirm-logout-btn' class='primary sm confirm-logout-btn'>
                <div>Logout</div>
                <?php get_component('loader')?>
            </button>
        </div>
    </div>
</div>