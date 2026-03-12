<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>MIA VENTURE SERVICES OPC</title>
        <link rel='shortcut icon' href='<?= get_favicon() ?>' type='image/x-icon'>
        <?= css('login') ?>
    </head>
    <body class='dark'>
        <div class='login-page flex-col flex-center'>
            <div class='header'>
                <img src='<?= get_image('logo.jpg') ?>' alt='MIA Venture Logo' class='logo'>

                <h1 class='title'>MIA VENTURE SERVICES OPC</h1>
                <p class='subtitle'>Maximizing Intelligent Access to credit</p>
            </div>
            <form action="<?= url('/login') ?>" method="post" id="login-form" class="form flex-col">
                <div class="flex-col field-group">
                    <label for="email">Username</label>
                    <input 
                        type="text" 
                        id="username"
                        name="username"
                        placeholder="@JuanCruz"
                        required
                    >
                </div>

                <div class="flex-col field-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password"
                            name="password"
                            required
                        >
                        <button type="button" class="password-toggle">
                            <span class="icon-eye">
                                <?= get_icon('eye') ?>
                            </span>
                            <span class="icon-eye-slash hidden">
                                <?= get_icon('eye-slash') ?>
                            </span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    <p>Login</p>
                    <?php get_component('loader', [
                        'size' => 'sm',
                    ]) ?>
                </button>

                <?php get_component('error-card', [
                    'class' => 'login-error-card',
                ]) ?>
            </form>
        </div>
        
        <?= js_jq('login') ?>
    </body>
</html>