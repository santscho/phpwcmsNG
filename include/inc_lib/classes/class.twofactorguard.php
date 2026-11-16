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
 * Guards for the two factor proof of concept.
 *
 * The attempt counter lives in the database, not in the session: a fresh
 * password login would otherwise reset it and every six digit code could be
 * tried as often as somebody wants. The same counter covers the authenticator
 * and the recovery codes.
 *
 * `passwordMatches()` is the re-authentication used before the second factor is
 * enrolled or switched off, so a stolen session alone cannot attach another
 * device to the account.
 */
class PhpwcmsTwoFactorGuard {

    /** failures inside the window before the account stops accepting codes */
    public const MAX_FAILURES = 10;
    /** minutes until the failures are forgotten */
    public const WINDOW = 900;

    /**
     * Whether the account is locked right now.
     *
     * @return array{locked: bool, wait: int}
     */
    public static function state(int $userId): array
    {
        $state = array('locked' => false, 'wait' => 0);

        if ($userId <= 0) {
            return $state;
        }

        $result = _dbQuery(
            'SELECT usr_2fa_failed, usr_2fa_failed_at FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $userId
        );

        $failed = (int) ($result[0]['usr_2fa_failed'] ?? 0);
        $at     = (int) ($result[0]['usr_2fa_failed_at'] ?? 0);

        if ($failed >= self::MAX_FAILURES && $at > 0) {
            $elapsed = time() - $at;
            if ($elapsed < self::WINDOW) {
                $state['locked'] = true;
                $state['wait']   = max(1, (int) ceil((self::WINDOW - $elapsed) / 60));
            }
        }

        return $state;
    }

    /**
     * Count one rejected code.
     */
    public static function fail(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $result = _dbQuery(
            'SELECT usr_2fa_failed, usr_2fa_failed_at FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $userId
        );

        $failed = (int) ($result[0]['usr_2fa_failed'] ?? 0);
        $at     = (int) ($result[0]['usr_2fa_failed_at'] ?? 0);

        // a failure older than the window starts a new count
        if ($at === 0 || time() - $at >= self::WINDOW) {
            $failed = 0;
        }

        _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_failed=' . ($failed + 1)
            . ', usr_2fa_failed_at=' . time()
            . ' WHERE usr_id=' . $userId,
            'UPDATE'
        );
    }

    /**
     * A code was accepted, so the failures are no longer relevant.
     */
    public static function reset(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_failed=0, usr_2fa_failed_at=0'
            . ' WHERE usr_id=' . $userId,
            'UPDATE'
        );
    }

    /**
     * Check the current password of the account, the way the login does it.
     */
    public static function passwordMatches(int $userId, string $password): bool
    {
        if ($userId <= 0 || $password === '') {
            return false;
        }

        $result = _dbQuery(
            'SELECT usr_pass FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $userId
        );

        $stored = (string) ($result[0]['usr_pass'] ?? '');
        if ($stored === '') {
            return false;
        }

        if (str_starts_with($stored, '$')) {
            if (password_verify($password, $stored)) {
                return true;
            }
            // hashes created from the md5 password of older versions
            $md5 = md5(makeCharsetConversion($password, PHPWCMS_CHARSET, 'utf-8'));
            return password_verify($md5, $stored);
        }

        // accounts that still carry the md5 or the plain password
        $md5 = md5(makeCharsetConversion($password, PHPWCMS_CHARSET, 'utf-8'));

        return hash_equals($stored, $md5) || hash_equals($stored, $password);
    }
}
