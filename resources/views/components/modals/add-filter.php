<div id='add-filter-modal' class='modal-backdrop'>
    <div class='modal-content card'>
        <div class='modal-header flex-row'>
            <p>Select Column to Filter</p>
            <button data-modal='add-filter-modal' class='modal-close-btn ghost sm'>
                <?= get_icon('x') ?>
            </button>
        </div>
        <hr>
        <div class='modal-body'>
            <div class="flex-col gap-16">
                <select id="filter-column">
                    <option value="" disabled selected>Select column</option>
                    <option value="last_name">Lastname</option>
                    <option value="first_name">Firstname</option>
                    <option value="middle_name">Middlename</option> 
                    <option value="birthdate">Birthdate</option>
                    <option value="mobile_num">Mobile Number</option>
                    <option value="start_date">Start Date</option>
                    <option value="end_date">End Date</option>
                </select>
                <input id="filter-value" type="text" placeholder="Value">
            </div>
        </div>
        <hr>
        <div class='modal-actions'>
            <button id="add-filter-cancel" class="outline sm modal-cancel-btn" data-modal='add-filter-modal'>Cancel</button>
            <button id="add-filter-confirm" class="primary sm">Add Filter</button>
        </div>
    </div>
</div>