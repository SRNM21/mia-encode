<aside class='sidebar collapsed' id='sidebar'>
    <ul class='sidebar-nav'>

        <?php if ($role == 'ADMIN'): ?>
            <a href='dashboard'>
                <span class='icon'><?= get_icon('chart-bar-big') ?></span>
                <span class='label'>Dashboard</span>
            </a>
        <?php endif ?>

        <?php if ($role == 'ADMIN'): ?>
            <a href='leaderboards'>
                <span class='icon'><?= get_icon('chart-no-axes-column') ?></span>
                <span class='label'>Leaderboards</span>
            </a>
        <?php endif ?>

        <?php if ($role == 'ENCODER'): ?>
            <a href='encode'>
                <span class='icon'><?= get_icon('file-user') ?></span>
                <span class='label'>Encode</span>
            </a>
        <?php endif ?>

        <a href='bank-applications'>
            <span class='icon'><?= get_icon('file-text') ?></span>
            <span class='label'>Bank Applications</span>
        </a>

        <?php if ($role == 'ADMIN'): ?>
            <a href='banks'>
                <span class='icon'><?= get_icon('landmark') ?></span>
                <span class='label'>Banks</span>
            </a>
        <?php endif ?>

        <!-- <a href='settings'>
            <span class='icon'><?= get_icon('settings') ?></span>
            <span class='label'>Settings</span>
        </a> -->

        <button id='logout-btn' class='ghost logout-btn'>
            <span class='icon'><?= get_icon('log-out') ?></span>
            <span class='label'>Logout</span>
        </button>
    </ul>
</aside>
