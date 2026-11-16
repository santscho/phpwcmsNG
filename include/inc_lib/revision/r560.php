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
 * Revision 560:
 * - Add the acting account to the two factor audit log, so an event that was
 *   caused by somebody else (an administrator resetting another factor) names
 *   the account that did it
 *
 * The column is only added, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r560() {

    $status = true;

    // without the log table there is nothing to extend
    if (_dbQuery('SHOW TABLES LIKE ' . _dbEscape(DB_PREPEND . 'phpwcms_user_2fa_log'), 'COUNT_SHOW') === 0) {
        return true;
    }

    $result = _dbQuery('SHOW COLUMNS FROM `' . DB_PREPEND . 'phpwcms_user_2fa_log` WHERE Field=' . _dbEscape('log_actor'));

    if (!isset($result[0]['Field'])) {
        $status = (bool) _dbQuery(
            'ALTER TABLE `' . DB_PREPEND . 'phpwcms_user_2fa_log`'
            . " ADD `log_actor` VARCHAR(100) NOT NULL DEFAULT '' AFTER `log_event`",
            'ALTER'
        );
    }

    return $status;
}
