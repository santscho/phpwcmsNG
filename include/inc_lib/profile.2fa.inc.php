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

// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------

require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.totp.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.qrcode.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.recoverycode.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.twofactorguard.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.twofactorlog.php';

/*
 * Two factor authentication proof of concept: enrollment and switch off.
 *
 * Deliberately limited to backend administrators and to the account of the
 * logged in user; there is no way to enroll or reset somebody else here.
 */

$two_factor_message = '';
$two_factor_error   = '';
$two_factor_new_codes = array();
$two_factor_user_id = (int) ($_SESSION['wcs_user_id'] ?? 0);

if (
    $two_factor_user_id
    && !empty($_SESSION['wcs_user_admin'])
    && isset($_POST['form_aktion'])
    && $_POST['form_aktion'] === 'update_2fa'
) {

    $two_factor_do       = isset($_POST['do_2fa']) ? (string) $_POST['do_2fa'] : '';
    $two_factor_code     = isset($_POST['code_2fa']) ? trim((string) $_POST['code_2fa']) : '';
    if ($two_factor_code === '' && isset($_POST['code_2fa_recovery'])) {
        // the recovery field next to the code control is the alternative
        $two_factor_code = trim((string) $_POST['code_2fa_recovery']);
    }
    $two_factor_password = isset($_POST['password_2fa']) ? (string) $_POST['password_2fa'] : '';
    $two_factor_limit    = PhpwcmsTwoFactorGuard::state($two_factor_user_id);

    // attaching or removing a factor is a security decision, so the session
    // alone is not enough: the password has to be confirmed first, and a wrong
    // one is counted so a stolen session cannot keep guessing it
    $two_factor_password_state = PhpwcmsTwoFactorGuard::passwordState($two_factor_user_id);
    $two_factor_password_ok    = false;

    if ($two_factor_password !== '' && !$two_factor_password_state['locked']) {
        $two_factor_password_ok = PhpwcmsTwoFactorGuard::passwordMatches($two_factor_user_id, $two_factor_password);
        if ($two_factor_password_ok) {
            PhpwcmsTwoFactorGuard::resetPassword($two_factor_user_id);
        } else {
            PhpwcmsTwoFactorGuard::failPassword($two_factor_user_id, (string) ($_SESSION['wcs_user'] ?? ''));
            $two_factor_password_state = PhpwcmsTwoFactorGuard::passwordState($two_factor_user_id);
        }
    }

    if ($two_factor_do === 'begin') {

        if ($two_factor_password_state['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_password_locked'], (int) $two_factor_password_state['wait']);
        } elseif (!$two_factor_password_ok) {
            $two_factor_error = $BL['be_2fa_error_password'];
        } elseif ($two_factor_limit['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_locked'], (int) $two_factor_limit['wait']);
        } else {
            // the secret stays in the session until one code proved that the
            // authenticator app was set up correctly
            $_SESSION['wcs_2fa_enroll'] = array(
                'secret' => PhpwcmsTotp::randomSecret(),
                'time'   => time(),
            );
        }

    } elseif ($two_factor_do === 'confirm') {

        $enroll = $_SESSION['wcs_2fa_enroll'] ?? null;

        if ($two_factor_limit['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_locked'], (int) $two_factor_limit['wait']);
        } elseif (!is_array($enroll) || empty($enroll['secret'])) {
            $two_factor_error = $BL['be_2fa_error_session'];
        } elseif (time() - (int) $enroll['time'] > 600) {
            unset($_SESSION['wcs_2fa_enroll']);
            $two_factor_error = $BL['be_2fa_error_session'];
        } elseif (!PhpwcmsRecoveryCode::configured()) {
            // without the key no recovery codes can be handed out, and enrolling
            // without them would be a trap
            $two_factor_error = $BL['be_2fa_error_key'];
        } elseif (($slice = PhpwcmsTotp::verify($enroll['secret'], $two_factor_code, 1, 0)) === false) {
            PhpwcmsTwoFactorGuard::fail($two_factor_user_id);
            $two_factor_error = $BL['be_2fa_error_code'];
        } else {
            PhpwcmsTwoFactorGuard::reset($two_factor_user_id);
            // a lost phone must not lock the account out, so the enrollment hands
            // out recovery codes; only their hashes are stored
            $two_factor_new_codes = PhpwcmsRecoveryCode::generate();
            _dbQuery(
                'UPDATE ' . DB_PREPEND . 'phpwcms_user SET '
                . 'usr_2fa_secret=' . _dbEscape($enroll['secret']) . ', '
                . 'usr_2fa_enabled=1, '
                . 'usr_2fa_last_slice=' . (int) $slice . ', '
                . 'usr_2fa_recovery=' . _dbEscape(PhpwcmsRecoveryCode::hashList($two_factor_new_codes))
                . ' WHERE usr_id=' . $two_factor_user_id,
                'UPDATE'
            );
            PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::ENROLLED, $two_factor_user_id, (string) ($_SESSION['wcs_user'] ?? ''));
            PhpwcmsTwoFactorGuard::notifyChange($two_factor_user_id, 'enrolled');
            unset($_SESSION['wcs_2fa_enroll']);
            $two_factor_message = $BL['be_2fa_enabled_ok'];
        }

    } elseif ($two_factor_do === 'reissue') {

        // fresh recovery codes for an account that still has its authenticator:
        // exchanging the list keeps the old codes from working, they are not
        // added to it. The password alone must not be enough to mint login
        // codes, so a current authenticator or recovery code is required too
        $result = _dbQuery(
            'SELECT usr_2fa_enabled, usr_2fa_secret, usr_2fa_last_slice, usr_2fa_recovery'
            . ' FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $two_factor_user_id
        );

        if ($two_factor_password_state['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_password_locked'], (int) $two_factor_password_state['wait']);
        } elseif (!$two_factor_password_ok) {
            $two_factor_error = $BL['be_2fa_error_password'];
        } elseif ($two_factor_limit['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_locked'], (int) $two_factor_limit['wait']);
        } elseif (!PhpwcmsRecoveryCode::configured()) {
            $two_factor_error = $BL['be_2fa_error_key'];
        } elseif (empty($result[0]['usr_2fa_enabled'])) {
            $two_factor_error = $BL['be_2fa_error_session'];
        } elseif ($two_factor_code === '') {
            $two_factor_error = $BL['be_2fa_error_code'];
        } else {

            $two_factor_codes_old = (string) ($result[0]['usr_2fa_recovery'] ?? '');
            $two_factor_secret    = (string) ($result[0]['usr_2fa_secret'] ?? '');
            $two_factor_slice     = PhpwcmsTotp::verify(
                $two_factor_secret,
                $two_factor_code,
                1,
                (int) $result[0]['usr_2fa_last_slice']
            );

            // a recovery code works as well, it is replaced together with the list
            $two_factor_code_ok = $two_factor_slice !== false
                || PhpwcmsRecoveryCode::consume($two_factor_codes_old, $two_factor_code) !== null;

            if (!$two_factor_code_ok) {
                PhpwcmsTwoFactorGuard::fail($two_factor_user_id);
                $two_factor_error = $BL['be_2fa_error_code'];
            } else {

                $two_factor_codes_new = PhpwcmsRecoveryCode::generate();

                // the used authenticator code is consumed the way the login does
                // it, and the list is only exchanged while it is still the one
                // that was read
                $two_factor_write = _dbQuery(
                    'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_recovery='
                    . _dbEscape(PhpwcmsRecoveryCode::hashList($two_factor_codes_new))
                    . ($two_factor_slice !== false ? ', usr_2fa_last_slice=' . (int) $two_factor_slice : '')
                    . ' WHERE usr_id=' . $two_factor_user_id
                    . ' AND usr_2fa_recovery=' . _dbEscape($two_factor_codes_old)
                    . ($two_factor_slice !== false ? ' AND usr_2fa_last_slice < ' . (int) $two_factor_slice : ''),
                    'UPDATE'
                );

                if (((int) ($two_factor_write['AFFECTED_ROWS'] ?? 0)) === 1) {
                    PhpwcmsTwoFactorGuard::reset($two_factor_user_id);
                    PhpwcmsTwoFactorLog::add(
                        PhpwcmsTwoFactorLog::REISSUED,
                        $two_factor_user_id,
                        (string) ($_SESSION['wcs_user'] ?? ''),
                        (string) ($_SESSION['wcs_user'] ?? '')
                    );
                    PhpwcmsTwoFactorGuard::notifyChange($two_factor_user_id, 'reissued');
                    $two_factor_new_codes = $two_factor_codes_new;
                    $two_factor_message   = $BL['be_2fa_reissued_ok'];
                } else {
                    $two_factor_error = $BL['be_2fa_error_session'];
                }
            }
        }

    } elseif ($two_factor_do === 'disable') {

        // switching it off needs the password and a valid code, either the
        // authenticator or one of the recovery codes, so a stolen session
        // cannot simply remove the second factor
        $result = _dbQuery(
            'SELECT usr_2fa_secret, usr_2fa_last_slice, usr_2fa_recovery FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $two_factor_user_id
        );

        if ($two_factor_password_state['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_password_locked'], (int) $two_factor_password_state['wait']);
        } elseif (!$two_factor_password_ok) {
            $two_factor_error = $BL['be_2fa_error_password'];
        } elseif ($two_factor_limit['locked']) {
            $two_factor_error = sprintf($BL['be_2fa_locked'], (int) $two_factor_limit['wait']);
        } elseif (!isset($result[0]['usr_2fa_secret']) || $result[0]['usr_2fa_secret'] === '') {
            $two_factor_error = $BL['be_2fa_error_session'];
        } else {
            $two_factor_slice    = PhpwcmsTotp::verify($result[0]['usr_2fa_secret'], $two_factor_code, 1, (int) $result[0]['usr_2fa_last_slice']);
            $two_factor_recovery = $two_factor_slice === false
                ? PhpwcmsRecoveryCode::consume((string) ($result[0]['usr_2fa_recovery'] ?? ''), $two_factor_code)
                : null;

            if ($two_factor_slice === false && $two_factor_recovery === null) {
                PhpwcmsTwoFactorGuard::fail($two_factor_user_id);
                $two_factor_error = $BL['be_2fa_error_code'];
            } else {
                if ($two_factor_recovery !== null) {
                    PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::RECOVERY_USED, $two_factor_user_id, (string) ($_SESSION['wcs_user'] ?? ''));
                }
                PhpwcmsTwoFactorGuard::reset($two_factor_user_id);
                _dbQuery(
                    'UPDATE ' . DB_PREPEND . 'phpwcms_user SET '
                    . "usr_2fa_secret='', usr_2fa_enabled=0, usr_2fa_last_slice=0, usr_2fa_recovery=''"
                    . ' WHERE usr_id=' . $two_factor_user_id,
                    'UPDATE'
                );
                PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::DISABLED, $two_factor_user_id, (string) ($_SESSION['wcs_user'] ?? ''));
                PhpwcmsTwoFactorGuard::notifyChange($two_factor_user_id, 'disabled');
                unset($_SESSION['wcs_2fa_enroll']);
                $two_factor_message = $BL['be_2fa_disabled_ok'];
            }
        }

    } elseif ($two_factor_do === 'cancel_reset') {

        // the owner keeps the factor, so a scheduled removal can be withdrawn
        // from here without asking for anything else
        if (PhpwcmsTwoFactorGuard::cancelReset($two_factor_user_id, (string) ($_SESSION['wcs_user'] ?? ''))) {
            $two_factor_message = $BL['be_2fa_reset_cancelled'];
        }

    }
}

// a removal another administrator asked for is shown until it is due or withdrawn
$two_factor_pending_reset = $two_factor_user_id ? PhpwcmsTwoFactorGuard::pendingReset($two_factor_user_id) : array();

// current state of the logged in account
$two_factor_state = array('enabled' => false, 'secret' => '', 'recovery_left' => 0);

if ($two_factor_user_id) {
    $result = _dbQuery(
        'SELECT usr_2fa_secret, usr_2fa_enabled, usr_2fa_recovery FROM ' . DB_PREPEND . 'phpwcms_user'
        . ' WHERE usr_id=' . $two_factor_user_id
    );
    if (isset($result[0])) {
        $two_factor_state['enabled'] = !empty($result[0]['usr_2fa_enabled']);
        $two_factor_state['secret']  = (string) ($result[0]['usr_2fa_secret'] ?? '');
        $two_factor_state['recovery_left'] = PhpwcmsRecoveryCode::remaining((string) ($result[0]['usr_2fa_recovery'] ?? ''));
    }
}

// a pending enrollment is shown until it is confirmed
$two_factor_enroll = ($two_factor_state['enabled'] === false && !empty($_SESSION['wcs_2fa_enroll']['secret']))
    ? $_SESSION['wcs_2fa_enroll']
    : null;

// the newest audit entries for this account
$two_factor_log = PhpwcmsTwoFactorLog::recent($two_factor_user_id, 10);
