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
 * Revision 558:
 * - Add usr_weekstart to phpwcms_user, storing the first day of the week the
 *   user picked for the backend calendars (1 Monday, 0 Sunday)
 *
 * @return bool
 */
function phpwcms_revision_r558() {

    $status = true;

    $result = _dbQuery('SHOW COLUMNS FROM `' . DB_PREPEND . 'phpwcms_user` WHERE Field=' . _dbEscape('usr_weekstart'));

    if (!isset($result[0]['Field'])) {
        $status = (bool) _dbQuery(
            'ALTER TABLE `' . DB_PREPEND . 'phpwcms_user` ADD `usr_weekstart` TINYINT(1) NOT NULL DEFAULT ' . _dbEscape('1') . ' AFTER `usr_date_format`',
            'ALTER'
        );
    }

    return $status;
}
