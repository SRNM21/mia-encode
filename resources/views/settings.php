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

            <?= get_component('Settings', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Requests'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-row'>
                        <div class="settings-nav-container flex-col gap-8">
                            <button class="settings-link active">Account</button>
                            <button class="settings-link">Theme </button>
                        </div>
                        <div class="settings-content">
                            <div class="account-setting-container setting-container">
                                <div class="account-setting-content">
                                    <div class="profile-section section">
                                        <p class="section-title">Profile</p>
                                        <div class="section-content">
                                            <div class="profile-form-wrapper">
                                                <div class="flex-col field-group">
                                                    <label for="username">Username</label>
                                                    <input 
                                                        type="text" 
                                                        id="username"
                                                        name="username"
                                                        required
                                                    >
                                                </div>
                                                <div class="flex-col field-group">
                                                    <label for="email">Email</label>
                                                    <input 
                                                        type="email" 
                                                        id="email"
                                                        name="email"
                                                        required
                                                    >
                                                </div>
                                                <div class="flex-col field-group">
                                                    <label for="team">Team</label>
                                                    <input 
                                                        type="text" 
                                                        id="team"
                                                        name="team"
                                                        required
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="security-section section">
                                        <p class="section-title">Security</p>
                                        <div class="section-content">

                                        </div>
                                    </div>
                                    <div class="activity-section section">
                                        <p class="section-title">Activity</p>
                                        <div class="section-content">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden theme-setting-container setting-container">
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
