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
 * Revision 565:
 * - Add the scheduled reset of another administrator's second factor. The
 *   removal takes effect after a cooling-off period and the account is
 *   notified by mail, so one administrator cannot strip it silently
 *
 * The table is only created, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r565() {

    $status = true;

    $result = _dbQuery('SHOW TABLES LIKE ' . _dbEscape(DB_PREPEND . 'phpwcms_user_2fa_reset'));

    if (!isset($result[0])) {
        $status = (bool) _dbQuery(
            'CREATE TABLE `' . DB_PREPEND . 'phpwcms_user_2fa_reset` ('
            . " `reset_user_id` INT UNSIGNED NOT NULL DEFAULT '0',"
            . " `reset_actor_id` INT UNSIGNED NOT NULL DEFAULT '0',"
            . " `reset_actor` VARCHAR(100) NOT NULL DEFAULT '',"
            . " `reset_created` INT UNSIGNED NOT NULL DEFAULT '0',"
            . " `reset_effective` INT UNSIGNED NOT NULL DEFAULT '0',"
            . ' PRIMARY KEY (`reset_user_id`),'
            . ' KEY `reset_effective` (`reset_effective`)'
            . ')',
            'CREATE'
        );
    }

    return $status;
}
