<header class='flex'>
    <button class="toggle-btn flex" id="toggle-btn" title="Toggle Sidebar">
        <?= get_icon('hamburger') ?>
    </button>

    <div class='header-content flex-row'>
        
        <?php if (!empty($breadcrumbs)): ?>
            <nav class="breadcrumbs">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if (!empty($crumb['url'])): ?>
                        <a href="<?= base_url($crumb['url']) ?>">
                            <?= htmlspecialchars($crumb['label']) ?>
                        </a>
                    <?php else: ?>
                        <span class="current">
                            <?= htmlspecialchars($crumb['label']) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($index < count($breadcrumbs) - 1): ?>
                        <span class="separator">/</span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <p class='user-name'>
            <?= $user->username ?? 'Anonymous' ?> | <?= $user->role->value ?? 'Guest' ?>
        </p>
    </div>
</header>

<?= get_modal('logout-confirm') ?>
<?= get_modal('page-loader') ?>