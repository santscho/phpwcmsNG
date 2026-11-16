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

// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------

/**
 * Audit trail for the two factor proof of concept.
 *
 * Enrollment, rejected codes, used recovery codes, switching the factor off
 * and getting locked out are recorded per account, so an administrator can
 * see what happened to their own second factor. The newest entries are kept,
 * older ones are dropped on write.
 */
class PhpwcmsTwoFactorLog {

    public const ENROLLED      = 'enrolled';
    public const CODE_FAILED   = 'code_failed';
    public const RECOVERY_USED = 'recovery_used';
    public const DISABLED      = 'disabled';
    public const LOCKED        = 'locked';

    /** entries kept per account */
    public const KEEP = 50;

    public static function events(): array
    {
        return array(
            self::ENROLLED,
            self::CODE_FAILED,
            self::RECOVERY_USED,
            self::DISABLED,
            self::LOCKED,
        );
    }

    /**
     * Record one event. Unknown events and missing accounts are ignored.
     */
    public static function add(string $event, int $userId, string $login = ''): void
    {
        if ($userId <= 0 || !in_array($event, self::events(), true)) {
            return;
        }

        $ip = PHPWCMS_GDPR_MODE ? getAnonymizedIp() : getRemoteIP();

        _dbQuery(
            'INSERT INTO ' . DB_PREPEND . 'phpwcms_user_2fa_log'
            . ' (log_user_id, log_login, log_event, log_ip, log_created) VALUES ('
            . $userId . ', ' . _dbEscape($login) . ', ' . _dbEscape($event) . ', '
            . _dbEscape((string) $ip) . ', ' . time() . ')',
            'INSERT'
        );

        // drop everything older than the last KEEP entries of this account
        $oldest = _dbQuery(
            'SELECT log_id FROM ' . DB_PREPEND . 'phpwcms_user_2fa_log'
            . ' WHERE log_user_id=' . $userId
            . ' ORDER BY log_id DESC LIMIT 1 OFFSET ' . self::KEEP
        );

        if (!empty($oldest[0]['log_id'])) {
            _dbQuery(
                'DELETE FROM ' . DB_PREPEND . 'phpwcms_user_2fa_log'
                . ' WHERE log_user_id=' . $userId . ' AND log_id <= ' . (int) $oldest[0]['log_id'],
                'DELETE'
            );
        }
    }

    /**
     * The newest entries of one account, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function recent(int $userId, int $limit = 10): array
    {
        if ($userId <= 0) {
            return array();
        }

        $result = _dbQuery(
            'SELECT log_event, log_ip, log_created FROM ' . DB_PREPEND . 'phpwcms_user_2fa_log'
            . ' WHERE log_user_id=' . $userId
            . ' ORDER BY log_id DESC LIMIT ' . max(1, min(100, $limit))
        );

        return is_array($result) ? $result : array();
    }
}
