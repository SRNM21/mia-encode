<!DOCTYPE html>
<html lang="en" class="<?= $theme ?>">
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Settings - MIA</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('settings') ?>
    </head>

    <body>
        <div class='home-page flex-col'>

            <?= get_component('header', [
                'user' => $user,
                'breadcrumbs' => [
                    ['label' => 'Settings'] 
                ]
            ]) ?>
            
            <main class='flex-row '>
                <?= get_component('sidebar', ['user' => $user]) ?>

                <div class='content flex-col'>
                    <div class='flex-row'>
                        <div class="settings-nav-container flex-col gap-8">
                            <button data-tab="account" class="settings-link">Account</button>
                            <button data-tab="theme" class="settings-link">Theme </button>
                        </div>
                        <div class="settings-content">
                            <div class="account-setting-container setting-container hide" data-tab="account">
                                <div class="account-setting-content">
                                    
                                    <div class="profile-section section">
                                        <div class="flex-row section-header">
                                            <p class="section-title">Profile</p>
                                            <button type="button" class="outline sm profile-edit-btn">Edit Account</button>
                                            <div class="action-buttons flex-row gap-8 hidden">
                                                <button class="outline sm profile-cancel-btn" type="button">Cancel</button>
                                                <button class="primary sm profile-save-btn" type="button">
                                                    <div>Save Changes</div>
                                                    <?php get_component('loader') ?>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="section-content">
                                            <div class="profile-form-wrapper">
                                                <div class="flex-col field-group">
                                                    <label for="username">Username</label>
                                                    <input type="text" id="username" name="username" value="<?= $user->username ?>" readonly disabled required>
                                                </div>
                                                <div class="flex-col field-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" id="email" name="email" value="<?= $user->email ?>" readonly disabled required>
                                                </div>
                                            </div>
                                            
                                            <?php get_component('error-card', [
                                                'class' => 'profile-error-card',
                                            ]) ?>
                                            
                                        </div>
                                    </div>

                                    <div class="security-section section">
                                        <div class="flex-row section-header">
                                            <p class="section-title">Security</p>
                                            
                                            <button type="button" class="outline sm security-edit-btn">Edit Password</button>
                                            
                                            <div class="action-buttons flex-row gap-8 hidden">
                                                <button class="outline sm security-cancel-btn" type="button">Cancel</button>
                                                <button class="primary sm security-save-btn" type="button">
                                                    <div>Update Password</div>
                                                    <?php get_component('loader') ?>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="section-content">
                                            <div class="password-info card flex-row">
                                                <div class="flex-col gap-4">
                                                    <p class="password-info-datetime"><?= formatDate($user->last_password_update, 'F j, Y \a\t h:i A') ?? 'ASD' ?></p>
                                                    <p class="password-info-note">Password last updated at</p>
                                                </div>
                                            </div>


                                            <div class="profile-form-wrapper hidden">
                                                <div class="flex-col field-group">
                                                    <label for="current-password">Current Password</label>
                                                    <input type="password" id="current-password" name="current-password">
                                                </div>
                                                <div class="flex-col field-group">
                                                    <label for="new-password">New Password</label>
                                                    <input type="password" id="new-password" name="new-password">
                                                </div>
                                                <div class="flex-col field-group">
                                                    <label for="confirm-password">Confirm New Password</label>
                                                    <input type="password" id="confirm-password" name="confirm-password">
                                                </div>
                                            </div>
                                            
                                            <?php get_component('error-card', [
                                                'class' => 'security-error-card',
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="theme-setting-container setting-container hide" data-tab="theme">
                                <div class="section">
                                    <div class="flex-col gap-4">
                                        <p class="section-title">Appearance</p>
                                        <p class="section-desc">Choose how MIA Encode System looks to you.</p>
                                    </div>
                                    <div class="section-content">
                                        <div class="flex-row gap-16 themes-selection-container">
                                            <div class="card flex-col flex-center gap-8 <?= $theme === 'dark' ? 'active' : '' ?>" data-theme="dark">
                                                <p>Dark Mode</p>
                                            </div>
                                            <div class="card flex-col flex-center gap-8 <?= $theme === 'light' ? 'active' : '' ?>" data-theme="light">
                                                <p>Light Mode</p>
                                            </div>
                                            <div class="card flex-col flex-center gap-8 <?= $theme === 'system' ? 'active' : '' ?>" data-theme="system">
                                                <p>System</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?= js_jq('settings') ?>
    </body>
</html>
