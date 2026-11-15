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
 * Revision 557:
 * - Add usr_date_format to phpwcms_user, storing the date and time format the
 *   user picked for the backend (iso, swiss, uk or us)
 *
 * @return bool
 */
function phpwcms_revision_r557() {

    $status = true;

    $result = _dbQuery('SHOW COLUMNS FROM `' . DB_PREPEND . 'phpwcms_user` WHERE Field=' . _dbEscape('usr_date_format'));

    if (!isset($result[0]['Field'])) {
        $status = (bool) _dbQuery(
            'ALTER TABLE `' . DB_PREPEND . 'phpwcms_user` ADD `usr_date_format` VARCHAR(20) NOT NULL DEFAULT ' . _dbEscape('iso') . ' AFTER `usr_theme`',
            'ALTER'
        );
    }

    return $status;
}
