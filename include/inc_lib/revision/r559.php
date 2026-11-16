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
 * Revision 559:
 * - Add the two factor authentication fields to phpwcms_user: the shared
 *   secret, the switch, the last accepted time slice (replay protection),
 *   the single use recovery codes and the attempt counter
 * - Add the audit log table for the two factor events
 *
 * The columns are only added, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r559() {

    $status = true;

    $columns = array(
        'usr_2fa_secret'     => "ADD `usr_2fa_secret` VARCHAR(64) NOT NULL DEFAULT '' AFTER `usr_weekstart`",
        'usr_2fa_enabled'    => "ADD `usr_2fa_enabled` TINYINT(1) NOT NULL DEFAULT '0' AFTER `usr_2fa_secret`",
        'usr_2fa_last_slice' => "ADD `usr_2fa_last_slice` BIGINT UNSIGNED NOT NULL DEFAULT '0' AFTER `usr_2fa_enabled`",
        'usr_2fa_recovery'   => "ADD `usr_2fa_recovery` MEDIUMTEXT NULL AFTER `usr_2fa_last_slice`",
        'usr_2fa_failed'     => "ADD `usr_2fa_failed` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `usr_2fa_recovery`",
        'usr_2fa_failed_at'  => "ADD `usr_2fa_failed_at` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `usr_2fa_failed`",
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

    // the audit log, one row per event
    if ($status && _dbQuery('SHOW TABLES LIKE ' . _dbEscape(DB_PREPEND . 'phpwcms_user_2fa_log'), 'COUNT_SHOW') === 0) {
        $status = (bool) _dbQuery(
            'CREATE TABLE IF NOT EXISTS `' . DB_PREPEND . 'phpwcms_user_2fa_log` ('
            . "`log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,"
            . "`log_user_id` INT UNSIGNED NOT NULL DEFAULT '0',"
            . "`log_login` VARCHAR(100) NOT NULL DEFAULT '',"
            . "`log_event` VARCHAR(40) NOT NULL DEFAULT '',"
            . "`log_ip` VARCHAR(64) NOT NULL DEFAULT '',"
            . "`log_created` INT UNSIGNED NOT NULL DEFAULT '0',"
            . 'PRIMARY KEY (`log_id`),'
            . 'KEY `log_user_id` (`log_user_id`, `log_id`)'
            . ')',
            'CREATE'
        );
    }

    return $status;
}
