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
 * Revision 556:
 * - Add usr_theme to phpwcms_user, storing the screen mode the user selected
 *   for the backend (auto, light or dark)
 *
 * @return bool
 */
function phpwcms_revision_r556() {

    $status = true;

    $result = _dbQuery('SHOW COLUMNS FROM `' . DB_PREPEND . 'phpwcms_user` WHERE Field=' . _dbEscape('usr_theme'));

    if (!isset($result[0]['Field'])) {
        $status = (bool) _dbQuery(
            'ALTER TABLE `' . DB_PREPEND . 'phpwcms_user` ADD `usr_theme` VARCHAR(10) NOT NULL DEFAULT ' . _dbEscape('auto') . ' AFTER `usr_fe`',
            'ALTER'
        );
    }

    return $status;
}
