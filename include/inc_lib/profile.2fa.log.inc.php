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

/*
 * Admin wide view of the two factor audit log: the newest events of every
 * account instead of only the own ones. Reachable from the two factor screen
 * and limited to administrators, like that screen itself.
 */

$two_factor_audit = PhpwcmsTwoFactorLog::recentAll(100);
