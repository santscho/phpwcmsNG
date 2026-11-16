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
 * Revision 561:
 * - Add the attempt counter for the password that guards the two factor
 *   settings. It is separate from the code counter, so guessing the password
 *   cannot lock the account out of its own login
 *
 * The columns are only added, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r561() {

    $status = true;

    $columns = array(
        'usr_2fa_pw_failed'    => "ADD `usr_2fa_pw_failed` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `usr_2fa_failed_at`",
        'usr_2fa_pw_failed_at' => "ADD `usr_2fa_pw_failed_at` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `usr_2fa_pw_failed`",
    );

    foreach ($columns as $column => $alter) {
        $result = _dbQuery('SHOW COLUMNS FROM `' . DB_PREPEND . 'phpwcms_user` WHERE Field=' . _dbEscape($column));
        if (!isset($result[0]['Field'])) {
            $status = (bool) _dbQuery(
                'ALTER TABLE `' . DB_PREPEND . 'phpwcms_user` ' . $alter,
                'ALTER'
            );
            if (!$status) {
                break;
            }
        }
    }

    return $status;
}
