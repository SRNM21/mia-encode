<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Settings - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('settings') ?>
    </head>

    <body class='dark'>
        <div class='home-page flex-col'>

            <?= get_component('header', [
                'title' => 'Settings',
                'user' => $user,
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['role' => $user->role]) ?>

                <div class='content flex-col'>
                    <div class='flex-row'>
                        <div class="flex-col gap-8">
                            <a href="Account">
                                Account
                            </a>
                            <a href="Theme">
                                Theme
                            </a>
                        </div>
                        <div class="settings-content">
                            <div class="hidden account-setting-container setting-container">

                            </div>
                            <div class="theme-setting-container setting-container">
                                THEME
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?= js_jq('settings') ?>
    </body>
</html>
