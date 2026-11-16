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
 * Revision 563:
 * - Keep the mailer's report on a queued lock warning, so the pending list can
 *   tell a refused connection from a rejected recipient instead of only
 *   counting attempts
 *
 * The column is only added, nothing existing is touched.
 *
 * @return bool
 */
function phpwcms_revision_r563() {

    $status = true;

    $result = _dbQuery(
        'SHOW COLUMNS FROM `' . DB_PREPEND . 'phpwcms_user_2fa_outbox` WHERE Field=' . _dbEscape('outbox_error')
    );

    if (!isset($result[0]['Field'])) {
        $status = (bool) _dbQuery(
            'ALTER TABLE `' . DB_PREPEND . 'phpwcms_user_2fa_outbox`'
            . " ADD `outbox_error` VARCHAR(255) NOT NULL DEFAULT '' AFTER `outbox_last_attempt`",
            'ALTER'
        );
    }

    return $status;
}
