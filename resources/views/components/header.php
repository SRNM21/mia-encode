<header class='flex'>
    <button class="toggle-btn flex" id="toggle-btn" title="Toggle Sidebar">
        <?= get_icon('hamburger') ?>
    </button>
    <div class='header-content flex-row'>
        <p>
            <?= $title ?? env('APP_NAME') ?? 'Mia Ventures OPC' ?>
        </p>
        <p>
            <?= $user->username ?? 'Anonymous' ?> | <?= $user->role ?? 'Guest' ?>
        </p>
    </div>
</header>

<?= get_modal('logout-confirm') ?>
