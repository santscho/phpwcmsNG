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

require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.twofactorlog.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.twofactorguard.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.recoverycode.php';

/*
 * Admin wide view of the two factor audit log: the newest events of every
 * account instead of only the own ones, plus the reset another administrator
 * needs when both the phone and the recovery codes are gone.
 */

$two_factor_actor_id   = (int) ($_SESSION['wcs_user_id'] ?? 0);
$two_factor_actor      = (string) ($_SESSION['wcs_user'] ?? '');
$two_factor_reset_done = '';
$two_factor_reset_error = '';

if (
    $two_factor_actor_id
    && !empty($_SESSION['wcs_user_admin'])
    && isset($_POST['form_aktion'])
    && $_POST['form_aktion'] === 'reset_2fa'
) {

    $two_factor_reset_uid      = (int) ($_POST['reset_uid'] ?? 0);
    $two_factor_password       = (string) ($_POST['password_2fa'] ?? '');
    $two_factor_password_state = PhpwcmsTwoFactorGuard::passwordState($two_factor_actor_id);

    if ($two_factor_reset_uid === $two_factor_actor_id) {
        // removing the own factor is the two factor screen's job, it asks for a code
        $two_factor_reset_error = $BL['be_2fa_reset_self'];
    } elseif ($two_factor_password_state['locked']) {
        // the attempts are counted against the account performing the reset
        $two_factor_reset_error = sprintf($BL['be_2fa_password_locked'], (int) $two_factor_password_state['wait']);
    } elseif ($two_factor_password === '' || !PhpwcmsTwoFactorGuard::passwordMatches($two_factor_actor_id, $two_factor_password)) {
        if ($two_factor_password !== '') {
            PhpwcmsTwoFactorGuard::failPassword($two_factor_actor_id, $two_factor_actor);
        }
        $two_factor_reset_error = $BL['be_2fa_error_password'];
    } else {

        PhpwcmsTwoFactorGuard::resetPassword($two_factor_actor_id);

        $two_factor_reset_target = _dbQuery(
            'SELECT usr_id, usr_login, usr_2fa_enabled FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $two_factor_reset_uid . ' AND usr_admin=1 AND usr_aktiv=1'
        );

        if (!isset($two_factor_reset_target[0]['usr_id'])) {
            $two_factor_reset_error = $BL['be_2fa_reset_unknown'];
        } elseif (empty($two_factor_reset_target[0]['usr_2fa_enabled'])) {
            $two_factor_reset_done = sprintf($BL['be_2fa_reset_none'], $two_factor_reset_target[0]['usr_login']);
                } else {

            // the removal waits out a cooling-off period and the account is told
            // by mail, so one administrator cannot strip it silently
            $two_factor_effective = time() + PhpwcmsTwoFactorGuard::RESET_COOLDOWN;

            if (!PhpwcmsTwoFactorGuard::scheduleAdminReset(
                (int) $two_factor_reset_target[0]['usr_id'],
                $two_factor_actor_id,
                $two_factor_actor,
                (string) $two_factor_reset_target[0]['usr_login']
            )) {
                $two_factor_reset_error = $BL['be_2fa_reset_unknown'];
            } else {
                $two_factor_reset_done = sprintf(
                    $BL['be_2fa_reset_scheduled'],
                    $two_factor_reset_target[0]['usr_login'],
                    date(($backend_date_format['php'] ?? 'Y-m-d H:i:s'), $two_factor_effective)
                );
            }
        }
    }
}

$two_factor_audit = PhpwcmsTwoFactorLog::recentAll(100);

// lock warnings that are still waiting for the mailer, so a stuck one is
// visible here instead of only when the fifth attempt gives up
$two_factor_outbox = _dbQuery(
    'SELECT outbox_login, outbox_kind, outbox_created, outbox_attempts, outbox_last_attempt, outbox_error'
    . ' FROM ' . DB_PREPEND . 'phpwcms_user_2fa_outbox ORDER BY outbox_id DESC'
);
if (!is_array($two_factor_outbox)) {
    $two_factor_outbox = array();
}

// the accounts the reset can apply to
$two_factor_admins = _dbQuery(
    'SELECT usr_id, usr_login, usr_2fa_enabled, usr_2fa_recovery FROM ' . DB_PREPEND . 'phpwcms_user'
    . ' WHERE usr_admin=1 AND usr_aktiv=1 ORDER BY usr_login'
);
if (!is_array($two_factor_admins)) {
    $two_factor_admins = array();
}
