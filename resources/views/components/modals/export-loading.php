<div id='export-loading-modal' class='modal-backdrop'>
    <div class='export-loading-modal-content modal-content card'>
        <div class='modal-header flex-row'>
            <p>Exporting to Excel</p>
        </div>
        <hr>
        <div class='modal-body'>
            <div class="export-message flex-row gap-8"></div>
            <div class="export-content">
                <div class="empty load flex-col flex-center <?= $class ?? '' ?>">
                    <?= get_icon('mia_icon') ?>
                    <p>Exporting please wait...</p>
                </div>
                <div class="download-link-container hidden flex-center">
                    <a 
                        href="#"
                        id="export-download-link"
                        class="export-download-link flex-row gap-4"
                    >
                        <?= get_icon('download') ?>
                        <p></p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>