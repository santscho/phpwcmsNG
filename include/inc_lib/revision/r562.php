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
 * Revision 562:
 * - Add the outbox for two factor lock warnings. The mail is written before
 *   the answer is finished and delivered after it, so a send can neither be
 *   lost with a killed worker nor hold up the login
 *
 * The table is only created, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r562() {

    $status = true;

    $result = _dbQuery('SHOW TABLES LIKE ' . _dbEscape(DB_PREPEND . 'phpwcms_user_2fa_outbox'));

    if (!isset($result[0])) {
        $status = (bool) _dbQuery(
            'CREATE TABLE `' . DB_PREPEND . 'phpwcms_user_2fa_outbox` ('
            . ' `outbox_id` bigint unsigned NOT NULL AUTO_INCREMENT,'
            . ' `outbox_user_id` int unsigned NOT NULL DEFAULT \'0\','
            . ' `outbox_login` varchar(100) NOT NULL DEFAULT \'\','
            . ' `outbox_kind` varchar(16) NOT NULL DEFAULT \'password\','
            . ' `outbox_recipient` varchar(150) NOT NULL DEFAULT \'\','
            . ' `outbox_to_name` varchar(150) NOT NULL DEFAULT \'\','
            . ' `outbox_subject` varchar(255) NOT NULL DEFAULT \'\','
            . ' `outbox_body` mediumtext NOT NULL,'
            . ' `outbox_created` int unsigned NOT NULL DEFAULT \'0\','
            . ' `outbox_attempts` smallint unsigned NOT NULL DEFAULT \'0\','
            . ' `outbox_last_attempt` int unsigned NOT NULL DEFAULT \'0\','
            . ' PRIMARY KEY (`outbox_id`),'
            . ' KEY `outbox_created` (`outbox_created`)'
            . ')',
            'CREATE'
        );
    }

    return $status;
}
