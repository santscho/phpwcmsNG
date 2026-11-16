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

/**
 * Revision 564:
 * - Add the request throttle: failed password and code checks are counted per
 *   address and across the install, so a single address can neither guess
 *   freely nor drive an account into its lockout on its own
 *
 * The table is only created, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r564() {

    $status = true;

    $result = _dbQuery('SHOW TABLES LIKE ' . _dbEscape(DB_PREPEND . 'phpwcms_user_2fa_throttle'));

    if (!isset($result[0])) {
        $status = (bool) _dbQuery(
            'CREATE TABLE `' . DB_PREPEND . 'phpwcms_user_2fa_throttle` ('
            . " `throttle_key` VARCHAR(64) NOT NULL DEFAULT '',"
            . " `throttle_failures` SMALLINT UNSIGNED NOT NULL DEFAULT '0',"
            . " `throttle_window_start` INT UNSIGNED NOT NULL DEFAULT '0',"
            . " `throttle_blocked_until` INT UNSIGNED NOT NULL DEFAULT '0',"
            . ' PRIMARY KEY (`throttle_key`)'
            . ')',
            'CREATE'
        );
    }

    return $status;
}
