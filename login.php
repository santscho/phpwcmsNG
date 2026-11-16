<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/

$phpwcms = ['SESSION_START' => true];
$BL = [];

// Check if config is still at the old position
if(
    !is_file(__DIR__ . '/include/config/conf.inc.php')
    && is_file(__DIR__ . '/config/phpwcms/conf.inc.php')
    && !@rename(__DIR__ . '/config/phpwcms', __DIR__ . '/include/config')
):
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>phpwcms configuration error</title>
        <style>
            body {
                background-color: #fff;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                font-size: 18px;
                color: #000;
            }
            h1 {
                font-size: 28px;
                color:#cc3300;
            }
        </style>
    </head>
    <body>
        <h4 style="">
            <strong>Your configuration is placed at the wrong position.</strong>
        </h4>
        <p>
            Beginning with <strong>phpwcms v1.7.8</strong> base config files were moved from
            directory <code>config/phpwcms</code> to directory <code>include/config</code>. The fallback
            to do it automatically has failed. Please do it manually before you continue.
        </p>
    </body>
</html>
<?php
    die();
endif;

require_once __DIR__.'/include/config/conf.inc.php';
require_once __DIR__.'/include/inc_lib/default.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/helper.session.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/dbcon.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/general.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/backend.functions.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lang/code.lang.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/classes/class.totp.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/classes/class.recoverycode.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/classes/class.twofactorguard.php';

if (isset($_GET['reason']) && str_starts_with($_GET['reason'], 'csrf-')) {
    headerAvoidPageCaching();
}

logdir_exists();

$_SESSION['REFERER_URL'] = PHPWCMS_URL.get_login_file();

// make compatibility check
if(phpwcms_revision_check_temp($phpwcms['revision']) !== true) {
    $revision_status = phpwcms_revision_check($phpwcms['revision']);
}

// define vars
$err = 0;
$wcs_user = '';

// where user should be redirected too after login
if(isset($_POST['ref_url']) || isset($_GET['ref'])) {
    $ref_url = xss_clean(isset($_GET['ref']) ? rawurldecode($_GET['ref']) : $_POST['ref_url']);
    if (!str_starts_with($ref_url, PHPWCMS_URL)) {
        $ref_url = '';
    }
} else {
    $ref_url = '';
}

$csrf_error = $_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_POST['logintoken']) || $_POST['logintoken'] !== get_token_get_value());

define('LOGIN_TOKEN', generate_get_token());

// reset all inactive users
$sql  = 'UPDATE ' . DB_PREPEND . "phpwcms_userlog SET logged_in=0, logged_change='" . time() . "' ";
$sql .= 'WHERE logged_in=1 AND (' . time() . '-logged_change) > ' . (int)$phpwcms['max_time'];
_dbQuery($sql, 'UPDATE');

//load default language EN
require_once PHPWCMS_ROOT.'/include/inc_lang/backend/en/lang.inc.php';

//define language and check if language file is available
if(isset($_COOKIE['phpwcmsBELang'])) {
    $temp_lang = strtoupper(substr(trim($_COOKIE['phpwcmsBELang']), 0, 2));
    if (isset($BL[$temp_lang])) {
        $_SESSION['wcs_user_lang'] = strtolower($temp_lang);
    } else {
        setcookie('phpwcmsBELang', '', time() - 3600, '/', getCookieDomain(), PHPWCMS_SSL, true);
    }
}
if(isset($_POST['form_lang'])) {
    $temp_lang = strtolower(substr(clean_slweg($_POST['form_lang']), 0, 2));
    $_SESSION['wcs_user_lang'] = $temp_lang;
    set_language_cookie($temp_lang);
}
if(empty($_SESSION['wcs_user_lang'])) {
    $_SESSION['wcs_user_lang'] = strtolower( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 ) : $phpwcms['default_lang'] );
} else {
    $_SESSION['wcs_user_lang'] = strtolower( substr($_SESSION['wcs_user_lang'], 0, 2 ) );
}
if(isset($BL[strtoupper($_SESSION['wcs_user_lang'])]) && is_file(PHPWCMS_ROOT.'/include/inc_lang/backend/'.$_SESSION['wcs_user_lang'].'/lang.inc.php')) {
    $_SESSION['wcs_user_lang_custom'] = 1;
} else {
    $_SESSION['wcs_user_lang'] = 'en'; //by ono
    $_SESSION['wcs_user_lang_custom'] = 0;
}
if(!empty($_SESSION['wcs_user_lang_custom'])) {
    //use custom lang if available -> was set in login.php
    $BL['merge_lang_array'][0] = $BL['be_admin_optgroup_label'];
    $BL['merge_lang_array'][1] = $BL['be_cnt_field'];
    include_once PHPWCMS_ROOT.'/include/inc_lang/backend/'.$_SESSION['wcs_user_lang'].'/lang.inc.php';
    $BL['be_admin_optgroup_label'] = array_merge($BL['merge_lang_array'][0], $BL['be_admin_optgroup_label']);
    $BL['be_cnt_field'] = array_merge($BL['merge_lang_array'][1], $BL['be_cnt_field']);
}

//WYSIWYG EDITOR:
//0 = no wysiwyg editor (default)
//1 = CKEditor
$phpwcms['wysiwyg_editor'] = empty($phpwcms['wysiwyg_editor']) ? 0 : (int)$GLOBALS['phpwcms']['wysiwyg_editor'];
$_SESSION['WYSIWYG_EDITOR'] = $phpwcms['wysiwyg_editor'];

destroyBackendSessionData();

$json_check = isset($_POST['json']) ? (int)$_POST['json'] : 0;

// two factor authentication proof of concept, off unless $phpwcms['backend_2fa'] is set
$backend_2fa     = !empty($phpwcms['backend_2fa']);
$login_passed      = 0;
$login_step2       = false;
$login_step2_err   = false;
$login_step2_wait  = 0;
$login_pending   = (isset($_SESSION['wcs_2fa_pending']) && is_array($_SESSION['wcs_2fa_pending'])) ? $_SESSION['wcs_2fa_pending'] : null;
$form_action     = isset($_POST['form_aktion']) ? (string) $_POST['form_aktion'] : '';

// shared completion: online list entry, welcome message and the redirect
$login_finish = static function(string $user, string $redirect_url): void {

    if(!($check = _dbQuery('SELECT COUNT(*) FROM ' . DB_PREPEND . 'phpwcms_userlog WHERE logged_user=' . _dbEscape($user) . ' AND logged_in=1', 'COUNT'))) {
        $sql  = 'INSERT INTO ' . DB_PREPEND . 'phpwcms_userlog (logged_user, logged_username, logged_start, logged_change, logged_in, logged_ip) VALUES (';
        $sql .= _dbEscape($user) . ', ' . _dbEscape($_SESSION['wcs_user_name']) . ', ' . time() . ', ' . time() . ', 1, ' . _dbEscape(PHPWCMS_GDPR_MODE ? getAnonymizedIp() : getRemoteIP()) . ')';
        _dbQuery($sql, 'INSERT');
    }

    $_SESSION['PHPWCMS_ROOT'] = PHPWCMS_ROOT;
    set_status_message('Welcome ' . $user . '!');

    if($redirect_url) {

        if(($token_position = strpos($redirect_url, 'csrftoken')) !== false) {
            $redirect_url = substr_replace($redirect_url, '', $token_position, 42);
            $redirect_url = str_replace(['?&', '&&'], ['?', '&'], $redirect_url);
        }

        $backend_redirect = $redirect_url . '&';

    } else {

        $backend_redirect = PHPWCMS_URL . 'phpwcms.php?';

    }

    $_SESSION['PHPWCMS_BROWSER_HASH'] = $GLOBALS['phpwcms']['USER_AGENT']['hash'];

    headerRedirect($backend_redirect . get_token_get_string() . '&' . session_name() . '=' . session_id());
};

// shared session bootstrap: both the password login and the second factor use it
$login_start_session = static function(array $user, string $login): void {

    // auth boundary: drop any pre-auth session ID to prevent fixation
    session_regenerate_id(true);

    $_SESSION['wcs_user']             = $login;
    $_SESSION['wcs_user_name']        = empty($user['usr_name']) ? $login : $user['usr_name'];
    $_SESSION['wcs_user_id']          = $user['usr_id'];
    $_SESSION['wcs_user_aktiv']       = $user['usr_aktiv'];
    $_SESSION['wcs_user_rechte']      = $user['usr_rechte'];
    $_SESSION['wcs_user_email']       = $user['usr_email'];
    $_SESSION['wcs_user_avatar']      = $user['usr_avatar'];
    $_SESSION['wcs_user_logtime']     = time();
    $_SESSION['wcs_user_admin']       = (int)$user['usr_admin'];
    $_SESSION['wcs_user_thumb']       = 1;
    $_SESSION['wcs_user_theme']       = in_array($user['usr_theme'] ?? 'auto', ['light', 'dark'], true) ? $user['usr_theme'] : 'auto';
    $_SESSION['wcs_user_date_format'] = in_array($user['usr_date_format'] ?? 'iso', ['iso', 'swiss', 'uk', 'us'], true) ? $user['usr_date_format'] : 'iso';
    $_SESSION['wcs_user_weekstart']   = backend_weekstart($user['usr_weekstart'] ?? '');

    if(empty($_POST['customlang']) && !empty($user['usr_lang'])) {
        $_SESSION['wcs_user_lang'] = $user['usr_lang'];
        set_language_cookie($user['usr_lang']);
    } elseif (!empty($_SESSION['wcs_user_lang'])) {
        set_language_cookie($_SESSION['wcs_user_lang']);
    } else {
        set_language_cookie();
    }

    $_SESSION['structure'] = @unserialize($user['usr_var_structure'], ['allowed_classes' => false]);
    $_SESSION['klapp']     = @unserialize($user['usr_var_privatefile'], ['allowed_classes' => false]);
    $_SESSION['pklapp']    = @unserialize($user['usr_var_publicfile'], ['allowed_classes' => false]);
    $user['usr_vars']      = @unserialize($user['usr_vars'], ['allowed_classes' => false]);

    if(!is_array($_SESSION['structure'])) {
        $_SESSION['structure'] = [];
    }
    if(!is_array($_SESSION['klapp'])) {
        $_SESSION['klapp'] = [];
    }
    if(!is_array($_SESSION['pklapp'])) {
        $_SESSION['pklapp'] = [];
    }
    if(!is_array($user['usr_vars'])) {
        $user['usr_vars'] = [];
    }

    $_SESSION['WYSIWYG_EDITOR'] = (int)$user['usr_wysiwyg'];
    $_SESSION['wcs_user_cp']    = isset($user['usr_vars']['selected_cp']) && is_array($user['usr_vars']['selected_cp']) ? $user['usr_vars']['selected_cp'] : [];
    $_SESSION['wcs_allowed_cp'] = isset($user['usr_vars']['allowed_cp']) && is_array($user['usr_vars']['allowed_cp']) ? $user['usr_vars']['allowed_cp'] : [];

    // Remove selected CPs the user is no longer allowed to use
    if(count($_SESSION['wcs_allowed_cp'])) {
        if(count($_SESSION['wcs_user_cp'])) {
            foreach($_SESSION['wcs_user_cp'] as $key => $value) {
                if(!isset($_SESSION['wcs_allowed_cp'][$key])) {
                    unset($_SESSION['wcs_user_cp'][$key]);
                }
            }
        } else {
            $_SESSION['wcs_user_cp'] = $_SESSION['wcs_allowed_cp'];
        }
    }
};

if($form_action === 'login2fa' && $json_check === 1) {

    // second step: the pending login is completed by a valid code only
    if(!$csrf_error && $login_pending !== null && time() - (int) $login_pending['time'] <= 300) {

        $result = _dbQuery(
            'SELECT * FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . (int) $login_pending['uid']
            . " AND usr_aktiv=1 AND usr_2fa_enabled=1 AND usr_2fa_secret != ''"
        );

        if(isset($result[0]['usr_id'])) {

            $second_step_passed = false;
            $second_step_limit  = PhpwcmsTwoFactorGuard::state((int) $result[0]['usr_id']);

            if($second_step_limit['locked']) {

                // too many codes were wrong, so even a correct one waits for the window
                $login_step2      = true;
                $login_step2_err  = true;
                $login_step2_wait = (int) $second_step_limit['wait'];

            } else {

                $submitted = isset($_POST['form_2fa_code']) ? (string) $_POST['form_2fa_code'] : '';
                $slice     = PhpwcmsTotp::verify(
                    $result[0]['usr_2fa_secret'],
                    $submitted,
                    1,
                    (int) $result[0]['usr_2fa_last_slice']
                );

                if($slice !== false) {
                    _dbQuery(
                        'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_last_slice=' . (int) $slice
                        . ' WHERE usr_id=' . (int) $result[0]['usr_id'],
                        'UPDATE'
                    );
                    $second_step_passed = true;
                } elseif (
                    ($recovery_left = PhpwcmsRecoveryCode::consume((string) ($result[0]['usr_2fa_recovery'] ?? ''), $submitted)) !== null
                ) {
                    // a recovery code replaces the authenticator when the phone is gone
                    _dbQuery(
                        'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_recovery=' . _dbEscape($recovery_left)
                        . ' WHERE usr_id=' . (int) $result[0]['usr_id'],
                        'UPDATE'
                    );
                    $second_step_passed = true;
                }
            }

            if($second_step_passed) {
                PhpwcmsTwoFactorGuard::reset((int) $result[0]['usr_id']);
                unset($_SESSION['wcs_2fa_pending']);
                $login_pending = null;
                $wcs_user      = $result[0]['usr_login'];
                $login_passed  = 1;
            } elseif(!$second_step_limit['locked']) {
                PhpwcmsTwoFactorGuard::fail((int) $result[0]['usr_id']);
            }
        }
    }

    if($login_passed) {

        $login_start_session($result[0], $wcs_user);
        $login_finish($wcs_user, $ref_url);
    }

    if(!$login_passed) {

        // stay on the code form and count the attempt
        $login_step2     = true;
        $login_step2_err = true;

        if($login_pending !== null) {
            $login_pending['tries'] = (int) ($login_pending['tries'] ?? 0) + 1;
            if($login_pending['tries'] >= 5) {
                unset($_SESSION['wcs_2fa_pending']);
            } else {
                $_SESSION['wcs_2fa_pending'] = $login_pending;
            }
        }
    }

} elseif($form_action === 'login' && $json_check === 1) {

    if(!$csrf_error) {

        $wcs_user = slweg($_POST['form_loginname']);
        $wcs_pass = slweg($_POST['md5pass'] ?? '');
        $plain_pass = isset($_POST['form_password']) ? slweg($_POST['form_password']) : '';

        $sql_query  = 'SELECT * FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_login=' . _dbEscape($wcs_user) . ' AND usr_aktiv=1 AND (usr_fe=1 OR usr_fe=2)';

        $result = _dbQuery($sql_query);

        if(isset($result[0]['usr_id'])) {

            $db_pass = $result[0]['usr_pass'];
            if ($plain_pass !== '') {
                if (str_starts_with($db_pass, '$')) {
                    if (password_verify($plain_pass, $db_pass)) {
                        $login_passed = 1;
                    } else {
                        $md5_pass = md5(makeCharsetConversion($plain_pass, PHPWCMS_CHARSET, 'utf-8'));
                        if (password_verify($md5_pass, $db_pass)) {
                            $login_passed = 1;
                            $new_hash = password_hash($plain_pass, PASSWORD_DEFAULT);
                            _dbQuery('UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_pass=' . _dbEscape($new_hash) . ' WHERE usr_id=' . (int)$result[0]['usr_id'], 'UPDATE');
                        }
                    }
                } else {
                    $md5_pass = md5(makeCharsetConversion($plain_pass, PHPWCMS_CHARSET, 'utf-8'));
                    if ($md5_pass === $db_pass) {
                        $login_passed = 1;
                        $new_hash = password_hash($plain_pass, PASSWORD_DEFAULT);
                        _dbQuery('UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_pass=' . _dbEscape($new_hash) . ' WHERE usr_id=' . (int)$result[0]['usr_id'], 'UPDATE');
                    }
                }
            } else {
                if (str_starts_with($db_pass, '$')) {
                    if (password_verify($wcs_pass, $db_pass)) {
                        $login_passed = 1;
                    }
                } else {
                    if ($wcs_pass === $db_pass) {
                        $login_passed = 1;
                        $new_hash = password_hash($wcs_pass, PASSWORD_DEFAULT);
                        _dbQuery('UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_pass=' . _dbEscape($new_hash) . ' WHERE usr_id=' . (int)$result[0]['usr_id'], 'UPDATE');
                    }
                }
            }
        }

        // Second factor for backend administrators: no session before the code is valid
        if(
            $login_passed && $backend_2fa && !empty($result[0]['usr_admin'])
            && !empty($result[0]['usr_2fa_enabled']) && !empty($result[0]['usr_2fa_secret'])
        ) {
            session_regenerate_id(true);
            $_SESSION['wcs_2fa_pending'] = array(
                'uid'   => (int) $result[0]['usr_id'],
                'time'  => time(),
                'tries' => 0,
            );
            $login_pending = $_SESSION['wcs_2fa_pending'];
            $login_step2   = true;
            $login_passed  = 0;
        }

        if($login_passed) {

            $login_start_session($result[0], $wcs_user);
            $login_passed = 1;
        }
    }

    if($login_passed) {

        $login_finish($wcs_user, $ref_url);

    } elseif(!$login_step2) {

        $err = 1;

    }

} elseif(isset($_POST['form_loginname']) && $json_check !== 2) {

    $err = 1;

}

$reason_types = [
    'default' => 'secondary',
    'info'    => 'info',
    'error'   => 'danger',
    'warning' => 'warning',
    'success' => 'success',
    'danger'  => 'danger'
];

$layout_rtl = (isset($_SESSION['wcs_user_lang']) && ($_SESSION['wcs_user_lang'] === 'ar' || $_SESSION['wcs_user_lang'] === 'he')) || ($phpwcms['default_lang'] === 'ar' || $phpwcms['default_lang'] === 'he');

?><!DOCTYPE html>
<html lang="<?php echo $_SESSION['wcs_user_lang']; ?>"<?php if($layout_rtl): ?> dir="rtl"<?php endif; ?>>
<head>
    <meta charset="<?php echo PHPWCMS_CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $BL['be_page_title'] . ' - ' . PHPWCMS_HOST ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link href="include/inc_ext/bootstrap/bootstrap.min.css" rel="stylesheet" type="text/css">
    <script src="include/inc_js/jquery/jquery.min.js"></script>
    <script type="module" src="include/inc_ext/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="include/inc_js/phpwcms.min.js"></script>
    <script src="include/inc_js/md5.js"></script>
</head>
<body class="bg-1">

<nav class="navbar bg-primary" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="index.php" target="_top">
            <img src="img/backend/phpwcms-logo.svg" alt="phpwcms" width="140" height="39">
            <span class="badge theme-inverse"><?php echo $BL['login_button'] ?></span>
        </a>
    </div>
</nav>

<main class="container py-7 md:py-12">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 md:col-7 lg:col-5">
            <div class="card shadow-sm">
                <div class="card-body p-7 md:p-12">

                    <h1 class="h3 mb-5"><?php echo $BL['login_text'] ?></h1>

<?php if(isset($_GET['reason'])): ?>
<?php $reason_theme = $reason_types[(isset($_GET['type']) && isset($reason_types[$_GET['type']])) ? $_GET['type'] : 'default']; ?>
                    <div class="alert theme-<?php echo $reason_theme ?>" role="alert">
                        <div>
                            <?php
                                if($_GET['reason'] === 'csrf-post-failed') {
                                    echo $BL['CSRF_POST_FAILED'];
                                } elseif($_GET['reason'] === 'csrf-post-invalid') {
                                    echo $BL['CSRF_POST_INVALID'];
                                } elseif($_GET['reason'] === 'csrf-get-failed') {
                                    echo $BL['CSRF_GET_FAILED'];
                                } elseif($_GET['reason'] === 'csrf-get-invalid') {
                                    echo $BL['CSRF_GET_INVALID'];
                                }
                            ?>
                        </div>
                    </div>
<?php endif; ?>

                    <div id="loginFormArea">
                        <div class="alert theme-warning" role="alert">
                            <div><?php echo $BL['be_login_jsinfo']; ?></div>
                        </div>
                    </div>

                    <p class="small fg-secondary mt-7 mb-0">
                        <strong><a href="https://www.phpwcms.org" target="_blank" rel="noopener">phpwcms</a></strong>
                        Copyright &copy; 2002&#8212;<?php echo date('Y'); ?>
                        Oliver Georgi. Extensions are copyright of their respective owners.
                        Visit <a href="https://www.phpwcms.org" target="_blank" rel="noopener">phpwcms.org</a> for
                        details. phpwcms is free software released under
                        <a href="https://www.fsf.org/licensing/licenses/gpl.html" target="_blank" rel="noopener">GPL</a>
                        and comes WITHOUT ANY WARRANTY. Obstructing the appearance of this notice is prohibited by law.
                    </p>

                </div>
            </div>
        </div>
    </div>
</main>
<?php

// get whole login form and keep in buffer
ob_start();

?>
<form action="<?php echo PHPWCMS_URL.get_login_file() ?>" method="post" id="login_formular" onsubmit="return login(this);"<?php if(empty($phpwcms['login_autocomplete'])): ?> autocomplete="off"<?php endif; ?>>
<input type="hidden" name="json" id="json" value="0" />
<input type="hidden" name="customlang" id="customlang" value="<?php if(!empty($_POST['customlang'])): ?>1<?php endif; ?>" />
<input type="hidden" name="md5pass" id="md5pass" value="" autocomplete="off" />
<input type="hidden" name="ref_url" value="<?php echo html_specialchars($ref_url); ?>" />
<input type="hidden" name="logintoken" value="<?php echo LOGIN_TOKEN; ?>" />
<input name="form_aktion" type="hidden" id="form_aktion" value="login" />
<?php

    if(file_exists(PHPWCMS_ROOT.'/setup')) {
        echo '<div class="alert theme-danger mb-5" role="alert"><div>' . $BL['setup_dir_exists'] . '</div></div>';
    }

    if(isset($_POST['json']) && $_POST['json'] == 2) {
        $err = 0;
    }

    if(file_exists(PHPWCMS_ROOT.'/phpwcms_code_snippets')) {
        echo '<div class="alert theme-danger mb-5" role="alert"><div>' . $BL['phpwcms_code_snippets_dir_exists'] . '</div></div>';
    }

    if(($phpwcms['image_library'] === 'gd' || $phpwcms['image_library'] === 'gd2') && (!extension_loaded('gd') || !function_exists('gd_info'))) {
        echo '<div class="alert theme-danger mb-5" role="alert"><div>' . $BL['gd_not_loaded'] . '</div></div>';
    }

    echo '<div class="alert theme-danger mb-5"';
    if(!$err) {
        echo ' style="display:none"';
    }
    echo ' id="jserr"><div>' . $BL['login_error'] . '</div></div>';

?>
<div class="mb-5">
    <label class="form-label" for="form_loginname"><?php echo $BL['login_username'] ?></label>
    <input name="form_loginname" type="text" class="form-control" id="form_loginname" maxlength="30" value="<?php echo html_specialchars($wcs_user); ?>" required />
</div>
<div class="mb-5">
    <label class="form-label" for="form_password"><?php echo $BL['login_userpass'] ?></label>
    <input name="form_password" type="password" class="form-control" id="form_password" maxlength="40" required<?php if(empty($phpwcms['login_autocomplete'])): ?> autocomplete="new-password"<?php endif; ?> />
</div>
<div class="mb-5">
    <label class="form-label" for="form_lang"><?php echo $BL['login_lang'] ?></label>
    <select name="form_lang" id="form_lang" class="form-control" onchange="getObjectById('json').value='2';login(this.form);">
<?php

// check available languages installed and build language selector menu
$lang_dirs = opendir(PHPWCMS_ROOT.'/include/inc_lang/backend');
$lang_options = [];
while($lang_code = readdir($lang_dirs)) {
    if( !str_starts_with($lang_code, '.') && is_file(PHPWCMS_ROOT.'/include/inc_lang/backend/'.$lang_code. '/lang.inc.php')) {
        $_lang_code = strtoupper($lang_code);
        $lang_options[$_lang_code]  = '<option value="'.$lang_code.'"';
        $lang_options[$_lang_code] .= ($lang_code == $_SESSION['wcs_user_lang']) ? ' selected="selected"' : '';
        $lang_options[$_lang_code] .= '>';
        $lang_options[$_lang_code] .= (isset($BL[$_lang_code])) ? $BL[$_lang_code] : $_lang_code;
        $lang_options[$_lang_code] .= '</option>';
    }
}
closedir($lang_dirs);

ksort($lang_options);
echo implode('', $lang_options);

?>
    </select>
</div>
<button type="submit" class="btn theme-primary btn-solid btn-styled btn-sm w-100"><?php echo $BL['login_button'] ?></button>
</form>
<?php

$formAll = str_replace( ["'", "\r", "\n", '<'], ["\'", '', ' ', "<'+'"], ob_get_clean() );

// second step form: the code, the CSRF token and the redirect target only
if($login_step2) {

    ob_start();

?>
<form action="<?php echo PHPWCMS_URL.get_login_file() ?>" method="post" id="login_formular_2fa">
<input type="hidden" name="json" id="json" value="1" />
<input type="hidden" name="ref_url" value="<?php echo html_specialchars($ref_url); ?>" />
<input type="hidden" name="logintoken" value="<?php echo LOGIN_TOKEN; ?>" />
<input name="form_aktion" type="hidden" id="form_aktion" value="login2fa" />
<?php if($login_step2_err): ?>
<div class="alert theme-danger mb-5" role="alert"><div><?php echo $login_step2_wait > 0 ? sprintf($BL['login_2fa_locked'], $login_step2_wait) : $BL['login_2fa_error'] ?></div></div>
<?php endif; ?>
<div class="mb-5">
    <label class="form-label" for="form_2fa_code"><?php echo $BL['login_2fa_code'] ?></label>
    <input name="form_2fa_code" type="text" class="form-control" id="form_2fa_code" maxlength="11" autocomplete="one-time-code" required />
    <p class="small fg-secondary mt-3 mb-0"><?php echo $BL['login_2fa_recovery_hint'] ?></p>
</div>
<button type="submit" class="btn theme-primary btn-solid btn-styled btn-sm w-100"><?php echo $BL['login_2fa_button'] ?></button>
<p class="small fg-secondary mt-5 mb-0"><a href="<?php echo PHPWCMS_URL.get_login_file() ?>"><?php echo $BL['login_2fa_cancel'] ?></a></p>
</form>
<?php

    $formAll = str_replace( ["'", "\r", "\n", '<'], ["\\'", '', ' ', "<'+'"], ob_get_clean() );

}

?>
<script>
    getObjectById('loginFormArea').innerHTML = '<?php echo $formAll ?>';
    getObjectById('<?php echo $login_step2 ? 'form_2fa_code' : 'form_loginname' ?>').focus();
<?php if(!empty($phpwcms['browser_check']['be'])):
    $buoop = ['insecure' => !isset($phpwcms['browser_check']['insecure']) || (bool)$phpwcms['browser_check']['insecure']];
    if(!empty($phpwcms['browser_check']['vs'])) {
        $buoop['vs'] = $phpwcms['browser_check']['vs'];
    }
    if(!empty($phpwcms['browser_check']['required'])) {
        $buoop['required'] = '{' . trim($phpwcms['browser_check']['required'], '{}') . '}';
    }
?>
    var $buoop = <?php echo json_encode($buoop); ?>;
</script><script src="https://browser-update.org/update.min.js"><?php endif; ?></script>
</body>
</html>
