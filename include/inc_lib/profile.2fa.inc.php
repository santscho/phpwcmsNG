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

/*
 * Two factor authentication proof of concept: enrollment and switch off.
 *
 * Deliberately limited to backend administrators and to the account of the
 * logged in user; there is no way to enroll or reset somebody else here.
 */

$two_factor_message = '';
$two_factor_error   = '';
$two_factor_user_id = (int) ($_SESSION['wcs_user_id'] ?? 0);

if (
    $two_factor_user_id
    && !empty($_SESSION['wcs_user_admin'])
    && isset($_POST['form_aktion'])
    && $_POST['form_aktion'] === 'update_2fa'
) {

    $two_factor_do   = isset($_POST['do_2fa']) ? (string) $_POST['do_2fa'] : '';
    $two_factor_code = isset($_POST['code_2fa']) ? (string) $_POST['code_2fa'] : '';

    if ($two_factor_do === 'begin') {

        // the secret stays in the session until one code proved that the
        // authenticator app was set up correctly
        $_SESSION['wcs_2fa_enroll'] = array(
            'secret' => PhpwcmsTotp::randomSecret(),
            'time'   => time(),
        );

    } elseif ($two_factor_do === 'confirm') {

        $enroll = $_SESSION['wcs_2fa_enroll'] ?? null;

        if (!is_array($enroll) || empty($enroll['secret'])) {
            $two_factor_error = $BL['be_2fa_error_session'];
        } elseif (time() - (int) $enroll['time'] > 600) {
            unset($_SESSION['wcs_2fa_enroll']);
            $two_factor_error = $BL['be_2fa_error_session'];
        } elseif (($slice = PhpwcmsTotp::verify($enroll['secret'], $two_factor_code, 1, 0)) === false) {
            $two_factor_error = $BL['be_2fa_error_code'];
        } else {
            _dbQuery(
                'UPDATE ' . DB_PREPEND . 'phpwcms_user SET '
                . 'usr_2fa_secret=' . _dbEscape($enroll['secret']) . ', '
                . 'usr_2fa_enabled=1, '
                . 'usr_2fa_last_slice=' . (int) $slice
                . ' WHERE usr_id=' . $two_factor_user_id,
                'UPDATE'
            );
            unset($_SESSION['wcs_2fa_enroll']);
            $two_factor_message = $BL['be_2fa_enabled_ok'];
        }

    } elseif ($two_factor_do === 'disable') {

        // switching it off needs a valid code too, so a stolen session
        // cannot simply remove the second factor
        $result = _dbQuery(
            'SELECT usr_2fa_secret, usr_2fa_last_slice FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $two_factor_user_id
        );

        if (!isset($result[0]['usr_2fa_secret']) || $result[0]['usr_2fa_secret'] === '') {
            $two_factor_error = $BL['be_2fa_error_session'];
        } elseif (PhpwcmsTotp::verify($result[0]['usr_2fa_secret'], $two_factor_code, 1, (int) $result[0]['usr_2fa_last_slice']) === false) {
            $two_factor_error = $BL['be_2fa_error_code'];
        } else {
            _dbQuery(
                'UPDATE ' . DB_PREPEND . 'phpwcms_user SET '
                . "usr_2fa_secret='', usr_2fa_enabled=0, usr_2fa_last_slice=0"
                . ' WHERE usr_id=' . $two_factor_user_id,
                'UPDATE'
            );
            unset($_SESSION['wcs_2fa_enroll']);
            $two_factor_message = $BL['be_2fa_disabled_ok'];
        }

    }
}

// current state of the logged in account
$two_factor_state = array('enabled' => false, 'secret' => '');

if ($two_factor_user_id) {
    $result = _dbQuery(
        'SELECT usr_2fa_secret, usr_2fa_enabled FROM ' . DB_PREPEND . 'phpwcms_user'
        . ' WHERE usr_id=' . $two_factor_user_id
    );
    if (isset($result[0])) {
        $two_factor_state['enabled'] = !empty($result[0]['usr_2fa_enabled']);
        $two_factor_state['secret']  = (string) ($result[0]['usr_2fa_secret'] ?? '');
    }
}

// a pending enrollment is shown until it is confirmed
$two_factor_enroll = ($two_factor_state['enabled'] === false && !empty($_SESSION['wcs_2fa_enroll']['secret']))
    ? $_SESSION['wcs_2fa_enroll']
    : null;
