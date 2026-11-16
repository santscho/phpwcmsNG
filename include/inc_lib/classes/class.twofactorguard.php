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
require_once __DIR__ . '/class.twofactorlog.php';

class PhpwcmsTwoFactorGuard {

    /** failures inside the window before the account stops accepting codes */
    public const MAX_FAILURES = 10;
    /** wrong passwords before the password gated actions stop accepting any */
    public const MAX_PASSWORD_FAILURES = 5;
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

        // one statement counts the failure, so parallel requests cannot lose
        // increments; a count older than the window starts at one again
        $now = time();
        _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_failed='
            . 'IF(usr_2fa_failed_at = 0 OR usr_2fa_failed_at <= ' . ($now - self::WINDOW) . ', 1, usr_2fa_failed + 1)'
            . ', usr_2fa_failed_at=' . $now
            . ' WHERE usr_id=' . $userId,
            'UPDATE'
        );

        $result = _dbQuery(
            'SELECT usr_login, usr_2fa_failed FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $userId
        );

        $login  = (string) ($result[0]['usr_login'] ?? '');
        $failed = (int) ($result[0]['usr_2fa_failed'] ?? 0);

        PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::CODE_FAILED, $userId, $login);

        if ($failed === self::MAX_FAILURES) {
            PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::LOCKED, $userId, $login);
            self::notifyLock($userId, 'code');
        }
    }

    /**
     * Whether the password gated actions are locked right now.
     *
     * A separate counter from the codes: guessing the password must not lock
     * the account out of its own login, and a wrong code must not stop the
     * owner from switching the factor off.
     *
     * @return array{locked: bool, wait: int}
     */
    public static function passwordState(int $userId): array
    {
        $state = array('locked' => false, 'wait' => 0);

        if ($userId <= 0) {
            return $state;
        }

        $result = _dbQuery(
            'SELECT usr_2fa_pw_failed, usr_2fa_pw_failed_at FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $userId
        );

        $failed = (int) ($result[0]['usr_2fa_pw_failed'] ?? 0);
        $at     = (int) ($result[0]['usr_2fa_pw_failed_at'] ?? 0);

        if ($failed >= self::MAX_PASSWORD_FAILURES && $at > 0) {
            $elapsed = time() - $at;
            if ($elapsed < self::WINDOW) {
                $state['locked'] = true;
                $state['wait']   = max(1, (int) ceil((self::WINDOW - $elapsed) / 60));
            }
        }

        return $state;
    }

    /**
     * Count one rejected password and record it.
     */
    public static function failPassword(int $userId, string $login = ''): void
    {
        if ($userId <= 0) {
            return;
        }

        $now = time();
        _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_pw_failed='
            . 'IF(usr_2fa_pw_failed_at = 0 OR usr_2fa_pw_failed_at <= ' . ($now - self::WINDOW) . ', 1, usr_2fa_pw_failed + 1)'
            . ', usr_2fa_pw_failed_at=' . $now
            . ' WHERE usr_id=' . $userId,
            'UPDATE'
        );

        $result = _dbQuery(
            'SELECT usr_2fa_pw_failed FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $userId
        );

        $failed = (int) ($result[0]['usr_2fa_pw_failed'] ?? 0);

        PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::PASSWORD_FAILED, $userId, $login, $login);

        if ($failed === self::MAX_PASSWORD_FAILURES) {
            PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::PASSWORD_LOCKED, $userId, $login, $login);
            self::notifyLock($userId, 'password');
        }
    }

    /**
     * The warning mail for a locked password check, or an empty array when the
     * account has no usable address.
     *
     * @return array{recipient: string, toName: string, subject: string, text: string}
     */
    public static function passwordLockMail(int $userId, int $minutes = 0): array
    {
        return self::lockMail($userId, 'password', $minutes);
    }

    /**
     * The warning mail for a locked authenticator code check.
     *
     * @return array{recipient: string, toName: string, subject: string, text: string}
     */
    public static function codeLockMail(int $userId, int $minutes = 0): array
    {
        return self::lockMail($userId, 'code', $minutes);
    }

    /**
     * Build a lock warning. The recipient is read from the account and never
     * from the request, so a wrong secret cannot be used to send mail anywhere
     * else, and the text follows the backend language stored on the account.
     *
     * @return array{recipient: string, toName: string, subject: string, text: string}
     */
    private static function lockMail(int $userId, string $kind, int $minutes = 0): array
    {
        if ($userId <= 0 || !function_exists('is_valid_email')) {
            return array();
        }

        $result = _dbQuery(
            'SELECT usr_login, usr_email, usr_name, usr_lang FROM ' . DB_PREPEND . 'phpwcms_user'
            . ' WHERE usr_id=' . $userId
        );

        $account = isset($result[0]) ? $result[0] : array();
        $email   = trim((string) ($account['usr_email'] ?? ''));

        if ($email === '' || !is_valid_email($email)) {
            return array();
        }

        $BL = array();
        self::loadMailLanguage((string) ($account['usr_lang'] ?? ''), $BL);

        $kind    = $kind === 'code' ? 'code' : 'password';
        $subject = 'be_2fa_mail_' . $kind . '_subject';
        $body    = 'be_2fa_mail_' . $kind . '_body';

        if (empty($BL[$subject]) || empty($BL[$body])) {
            return array();
        }

        $minutes = $minutes > 0 ? $minutes : (int) (self::WINDOW / 60);
        $login   = (string) ($account['usr_login'] ?? '');
        $name    = trim((string) ($account['usr_name'] ?? '')) !== ''
            ? (string) $account['usr_name']
            : $login;

        return array(
            'recipient' => $email,
            'toName'    => $name,
            'subject'   => sprintf((string) $BL[$subject], $login),
            'text'      => sprintf(
                (string) $BL[$body],
                $name,
                $login,
                $minutes,
                date('Y-m-d H:i:s'),
                PHPWCMS_GDPR_MODE ? getAnonymizedIp() : getRemoteIP()
            ),
        );
    }

    /**
     * Send a lock warning. A mail server that is down or unconfigured must
     * never break the login or the lock itself, so failures are swallowed.
     */
    private static function notifyLock(int $userId, string $kind): void
    {
        $mail = self::lockMail($userId, $kind);

        if (!$mail || !function_exists('sendEmail')) {
            return;
        }

        try {
            sendEmail(array(
                'recipient' => $mail['recipient'],
                'toName'    => $mail['toName'],
                'subject'   => $mail['subject'],
                'isHTML'    => false,
                'text'      => $mail['text'],
            ));
        } catch (\Throwable $e) {
            // nothing to do: the lock is already recorded in the audit trail
        }
    }

    /**
     * Load the mail texts in the account's backend language, falling back to
     * English when that language does not carry them.
     */
    private static function loadMailLanguage(string $lang, array &$BL): void
    {
        $base = PHPWCMS_ROOT . '/include/inc_lang/backend/';
        $lang = strtolower($lang);

        if (preg_match('/^[a-z]{2}$/', $lang) && is_file($base . $lang . '/lang.inc.php')) {
            include $base . $lang . '/lang.inc.php';
        }

        if (empty($BL['be_2fa_mail_password_subject']) || empty($BL['be_2fa_mail_password_body'])) {
            $BL = array();
            include $base . 'en/lang.inc.php';
        }
    }

    /**
     * A password was accepted, so the wrong attempts are no longer relevant.
     */
    public static function resetPassword(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_2fa_pw_failed=0, usr_2fa_pw_failed_at=0'
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
