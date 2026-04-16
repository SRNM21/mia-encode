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

        <div class="flex-center flex-row gap-8">
            <div id="wifi-indicator" class="wifi-indicator flex-center" title="Checking connection status...">
                
            </div>
            <span class='user-name flex-center flex-row gap-8'>
                <p><?= $user->username ?? 'Anonymous' ?></p> 
                <span class="user-role <?= $user->role->value ?>"><?= $user->role->value ?? 'Guest' ?></span>
            </span>
        </div>
    </div>
</header>

<?= get_modal('logout-confirm') ?>
<?= get_modal('page-loader') ?>