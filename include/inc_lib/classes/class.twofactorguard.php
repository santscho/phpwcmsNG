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
    /** seconds a lock warning may spend on a hanging mail server */
    public const WARNING_TIMEOUT = 5;
    /** seconds another administrator's reset waits before it takes effect */
    public const RESET_COOLDOWN = 1800;
    /** send attempts before a queued warning is given up */
    public const OUTBOX_ATTEMPTS = 5;
    /** seconds before a failed warning is tried again */
    public const OUTBOX_RETRY = 300;
    /** seconds between two queue checks triggered by ordinary requests */
    public const OUTBOX_INTERVAL = 60;
    /**
     * failures one address may cause inside the window before it is refused.
     * Deliberately below MAX_PASSWORD_FAILURES: a single address must not be
     * able to push somebody else's account over its lockout counter
     */
    public const MAX_ADDRESS_FAILURES = 4;
    /** seconds an address is refused once it used its failures up */
    public const ADDRESS_BLOCK = 900;
    /** failures across the install before everybody is slowed down */
    public const INSTALL_SOFT_FAILURES = 30;
    public const INSTALL_HARD_FAILURES = 100;
    /** longest pause one request may spend on a failure, in seconds */
    public const MAX_PAUSE = 4;

    /** the outbox is drained once per request at most */
    private static bool $drainQueued = false;

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
     * The state of the address and of the install for the incoming request.
     *
     * `blocked` means the address used its failures up: the login then verifies
     * nothing and moves no counter, so the block cannot be aimed at an account.
     * `delay` is the pause the request has to take before it answers, grown by
     * the failures of the address and of the whole install.
     *
     * @return array{blocked: bool, wait: int, delay: int, failures: int}
     */
    public static function requestState(): array
    {
        $now  = time();
        $row  = self::throttleRow(self::requestKey(), $now);
        $state = array(
            'blocked'  => false,
            'wait'     => 0,
            'delay'    => 0,
            'failures' => $row['failures'],
        );

        if ($row['blocked_until'] > $now) {
            $state['blocked'] = true;
            $state['wait']    = max(1, (int) ceil(($row['blocked_until'] - $now) / 60));
            return $state;
        }

        $delay = self::escalatingDelay($row['failures']) + self::installDelay();
        $state['delay'] = min(self::MAX_PAUSE, $delay);

        return $state;
    }

    /**
     * Count one rejected password or code against the address and the install.
     */
    public static function requestFail(): void
    {
        self::throttleFail(self::requestKey(), true);
        self::throttleFail('install', false);
    }

    /**
     * Spend the pause the request state asks for, so guessing gets slower the
     * more failures an address or the whole install has. Capped, because a
     * request that sleeps holds a worker.
     */
    public static function requestPause(int $delay): void
    {
        if ($delay > 0) {
            usleep(min(self::MAX_PAUSE, $delay) * 1000000);
        }
    }

    /**
     * The key the throttle counts under. The address is only ever used as the
     * key, never sent anywhere.
     */
    private static function requestKey(): string
    {
        $ip = function_exists('getRemoteIP') ? (string) getRemoteIP() : '';

        return substr('ip:' . $ip, 0, 64);
    }

    /**
     * The pause an address takes after its own failures: none for the first
     * two, then one, two and four seconds.
     */
    private static function escalatingDelay(int $failures): int
    {
        return match (true) {
            $failures >= 4 => 4,
            $failures === 3 => 2,
            $failures === 2 => 1,
            default => 0,
        };
    }

    /**
     * The pause everybody takes while the install sees a wave of failures.
     */
    private static function installDelay(): int
    {
        $row = self::throttleRow('install', time());

        return match (true) {
            $row['failures'] >= self::INSTALL_HARD_FAILURES => 2,
            $row['failures'] >= self::INSTALL_SOFT_FAILURES => 1,
            default => 0,
        };
    }

    /**
     * Read one throttle row, treating an expired window as empty.
     *
     * @return array{failures: int, blocked_until: int}
     */
    private static function throttleRow(string $key, int $now): array
    {
        $row = array('failures' => 0, 'blocked_until' => 0);

        $result = _dbQuery(
            'SELECT throttle_failures, throttle_window_start, throttle_blocked_until'
            . ' FROM ' . DB_PREPEND . 'phpwcms_user_2fa_throttle'
            . ' WHERE throttle_key=' . _dbEscape($key)
        );

        if (!isset($result[0]) || $result[0]['throttle_window_start'] <= $now - self::WINDOW) {
            return $row;
        }

        $row['failures']      = (int) $result[0]['throttle_failures'];
        $row['blocked_until'] = (int) $result[0]['throttle_blocked_until'];

        return $row;
    }

    /**
     * Count one failure for a key and, for an address, refuse it once it is
     * over its budget.
     */
    private static function throttleFail(string $key, bool $allow_block): void
    {
        $now      = time();
        $row      = self::throttleRow($key, $now);
        $failures = $row['failures'] + 1;
        $blocked  = $allow_block && $failures >= self::MAX_ADDRESS_FAILURES
            ? $now + self::ADDRESS_BLOCK
            : 0;

        _dbQuery(
            'INSERT INTO ' . DB_PREPEND . 'phpwcms_user_2fa_throttle'
            . ' (throttle_key, throttle_failures, throttle_window_start, throttle_blocked_until) VALUES ('
            . _dbEscape($key) . ', ' . $failures . ', ' . $now . ', ' . $blocked . ')'
            . ' ON DUPLICATE KEY UPDATE throttle_failures=VALUES(throttle_failures),'
            . ' throttle_window_start=VALUES(throttle_window_start),'
            . ' throttle_blocked_until=VALUES(throttle_blocked_until)',
            'INSERT'
        );
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
     * @return array{recipient: string, toName: string, subject: string, text: string, login: string}
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
            'login'     => $login,
        );
    }

    /**
     * Queue a lock warning. The row is written during the request, so a worker
     * that dies after the answer loses the send, not the message: a later
     * request picks it up.
     */
    private static function notifyLock(int $userId, string $kind): void
    {
        $mail = self::lockMail($userId, $kind);

        // no address or no text to send: nothing can ever be delivered
        if (!$mail) {
            self::logNotificationFailure($userId);
            return;
        }

        self::queueMessage($userId, $kind, $mail);
    }

    /**
     * Write one message into the outbox and let a later request deliver it.
     *
     * @param array{recipient: string, toName: string, subject: string, text: string, login?: string} $mail
     */
    private static function queueMessage(int $userId, string $kind, array $mail): void
    {
        if ($userId <= 0 || empty($mail['recipient']) || !is_valid_email($mail['recipient'])) {
            return;
        }

        _dbQuery(
            'INSERT INTO ' . DB_PREPEND . 'phpwcms_user_2fa_outbox'
            . ' (outbox_user_id, outbox_login, outbox_kind, outbox_recipient, outbox_to_name,'
            . ' outbox_subject, outbox_body, outbox_created, outbox_attempts, outbox_last_attempt)'
            . ' VALUES (' . $userId
            . ', ' . _dbEscape((string) ($mail['login'] ?? ''))
            . ', ' . _dbEscape($kind)
            . ', ' . _dbEscape($mail['recipient'])
            . ', ' . _dbEscape($mail['toName'])
            . ', ' . _dbEscape($mail['subject'])
            . ', ' . _dbEscape($mail['text'])
            . ', ' . time() . ', 0, 0)',
            'INSERT'
        );

        // the answer goes out before the mail server is contacted
        self::drainQueueAfterResponse();
    }

    /**
     * Schedule the removal of another administrator's second factor. The factor
     * keeps working until the cooling-off period is over, so the owner has time
     * to notice the mail and cancel it in the two factor settings.
     */
    public static function scheduleAdminReset(int $targetId, int $actorId, string $actor, string $targetLogin = ''): bool
    {
        if ($targetId <= 0 || $actorId <= 0 || $targetId === $actorId) {
            return false;
        }

        if ($targetLogin === '') {
            $result      = _dbQuery('SELECT usr_login FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $targetId);
            $targetLogin = (string) ($result[0]['usr_login'] ?? '');
        }

        $effective = time() + self::RESET_COOLDOWN;

        _dbQuery(
            'REPLACE INTO ' . DB_PREPEND . 'phpwcms_user_2fa_reset'
            . ' (reset_user_id, reset_actor_id, reset_actor, reset_created, reset_effective) VALUES ('
            . $targetId . ', ' . $actorId . ', ' . _dbEscape($actor) . ', ' . time() . ', ' . $effective . ')',
            'INSERT'
        );

        PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::RESET_SCHEDULED, $targetId, $targetLogin, $actor);
        self::notifyReset($targetId, $actor, $effective);

        return true;
    }

    /**
     * The reset that waits for its cooling-off period, if there is one.
     *
     * @return array{actor: string, created: int, effective: int}
     */
    public static function pendingReset(int $userId): array
    {
        if ($userId <= 0) {
            return array();
        }

        $result = _dbQuery(
            'SELECT reset_actor, reset_created, reset_effective FROM ' . DB_PREPEND . 'phpwcms_user_2fa_reset'
            . ' WHERE reset_user_id=' . $userId
        );

        if (!isset($result[0])) {
            return array();
        }

        return array(
            'actor'     => (string) $result[0]['reset_actor'],
            'created'   => (int) $result[0]['reset_created'],
            'effective' => (int) $result[0]['reset_effective'],
        );
    }

    /**
     * Withdraw a scheduled reset. Only the account it concerns may do this, and
     * only while its second factor still works.
     */
    public static function cancelReset(int $userId, string $login = ''): bool
    {
        if ($userId <= 0 || !self::pendingReset($userId)) {
            return false;
        }

        _dbQuery(
            'DELETE FROM ' . DB_PREPEND . 'phpwcms_user_2fa_reset WHERE reset_user_id=' . $userId,
            'DELETE'
        );

        PhpwcmsTwoFactorLog::add(PhpwcmsTwoFactorLog::RESET_CANCELLED, $userId, $login);

        return true;
    }

    /**
     * Run every scheduled reset whose cooling-off period is over. Called on the
     * login screen, so a reset lands even when the account cannot sign in.
     */
    public static function applyDueResets(int $limit = 5): void
    {
        $result = _dbQuery(
            'SELECT reset_user_id, reset_actor FROM ' . DB_PREPEND . 'phpwcms_user_2fa_reset'
            . ' WHERE reset_effective <= ' . time() . ' ORDER BY reset_effective LIMIT ' . max(1, $limit)
        );

        if (!is_array($result)) {
            return;
        }

        foreach ($result as $row) {
            $userId = (int) ($row['reset_user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $account = _dbQuery(
                'SELECT usr_login FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $userId
            );
            $login = (string) ($account[0]['usr_login'] ?? '');

            _dbQuery(
                'UPDATE ' . DB_PREPEND . 'phpwcms_user SET '
                . "usr_2fa_secret='', usr_2fa_enabled=0, usr_2fa_last_slice=0,"
                . " usr_2fa_recovery='', usr_2fa_failed=0, usr_2fa_failed_at=0"
                . ' WHERE usr_id=' . $userId,
                'UPDATE'
            );

            _dbQuery(
                'DELETE FROM ' . DB_PREPEND . 'phpwcms_user_2fa_reset WHERE reset_user_id=' . $userId,
                'DELETE'
            );

            PhpwcmsTwoFactorLog::add(
                PhpwcmsTwoFactorLog::RESET,
                $userId,
                $login,
                (string) ($row['reset_actor'] ?? '')
            );
        }
    }

    /**
     * Mail the account that its second factor is about to be removed.
     */
    private static function notifyReset(int $userId, string $actor, int $effective): void
    {
        if ($userId <= 0 || !function_exists('is_valid_email')) {
            return;
        }

        $result  = _dbQuery(
            'SELECT usr_login, usr_email, usr_name, usr_lang FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $userId
        );
        $account = isset($result[0]) ? $result[0] : array();
        $email   = trim((string) ($account['usr_email'] ?? ''));

        if ($email === '' || !is_valid_email($email)) {
            self::logNotificationFailure($userId);
            return;
        }

        $BL = array();
        self::loadMailLanguage((string) ($account['usr_lang'] ?? ''), $BL);

        if (empty($BL['be_2fa_mail_reset_subject']) || empty($BL['be_2fa_mail_reset_body'])) {
            self::logNotificationFailure($userId);
            return;
        }

        $login = (string) ($account['usr_login'] ?? '');
        $name  = trim((string) ($account['usr_name'] ?? '')) !== '' ? (string) $account['usr_name'] : $login;

        self::queueMessage($userId, 'reset', array(
            'recipient' => $email,
            'toName'    => $name,
            'subject'   => sprintf((string) $BL['be_2fa_mail_reset_subject'], $login),
            'text'      => sprintf(
                (string) $BL['be_2fa_mail_reset_body'],
                $name,
                $actor !== '' ? $actor : '?',
                $login,
                date('Y-m-d H:i:s', $effective),
                max(1, (int) ceil(self::RESET_COOLDOWN / 60))
            ),
            'login'     => $login,
        ));
    }

    /**
     * Deliver the outbox after the response, so a send can neither be lost
     * (the row stays until it went out) nor hold up the answer.
     */
    public static function drainQueueAfterResponse(int $limit = 5): void
    {
        if (self::$drainQueued) {
            return;
        }

        self::$drainQueued = true;

        register_shutdown_function(static function () use ($limit): void {
            if (function_exists('fastcgi_finish_request')) {
                self::finishResponse();
                fastcgi_finish_request();
            } elseif (function_exists('litespeed_finish_request')) {
                self::finishResponse();
                litespeed_finish_request();
            }

            self::drainQueue($limit);
        });
    }

    /**
     * Retry the outbox from any request, frontend hits included, without paying
     * for it every time: a single stat against a stamp file decides whether
     * this request looks at the queue at all, and the work happens after the
     * response, so the page latency stays as it is.
     */
    public static function drainQueuePeriodically(int $interval = self::OUTBOX_INTERVAL): void
    {
        $stamp = self::outboxStampFile();

        if (is_file($stamp) && @filemtime($stamp) > time() - max(1, $interval)) {
            return;
        }

        // a temporary directory that cannot be stamped turns the gate into a
        // query per request, but losing warnings is worse, so it still runs
        @touch($stamp);

        self::drainQueueAfterResponse();
    }

    /**
     * The stamp the cheap gate reads. It lives in the temporary directory the
     * install already uses for caches, so reading it is one stat.
     */
    private static function outboxStampFile(): string
    {
        return PHPWCMS_TEMP . '2fa-outbox.stamp';
    }

    /**
     * Send what the outbox still holds. Runs after a response on the login and
     * backend screens and now and then from a frontend request, so a send that
     * failed is retried instead of forgotten.
     */
    public static function drainQueue(int $limit = 5): void
    {
        $result = _dbQuery(
            'SELECT * FROM ' . DB_PREPEND . 'phpwcms_user_2fa_outbox'
            . ' WHERE outbox_attempts < ' . self::OUTBOX_ATTEMPTS
            . ' AND (outbox_last_attempt = 0 OR outbox_last_attempt <= ' . (time() - self::OUTBOX_RETRY) . ')'
            . ' ORDER BY outbox_id LIMIT ' . max(1, $limit)
        );

        if (!is_array($result)) {
            return;
        }

        foreach ($result as $row) {
            if (is_array($row) && !empty($row['outbox_id'])) {
                self::deliverQueued($row);
            }
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function deliverQueued(array $row): void
    {
        $id     = (int) $row['outbox_id'];
        $userId = (int) ($row['outbox_user_id'] ?? 0);
        $login  = (string) ($row['outbox_login'] ?? '');
        $sent   = array(false, '');

        if (function_exists('sendEmail')) {
            try {
                $sent = sendEmail(array(
                    'recipient' => (string) ($row['outbox_recipient'] ?? ''),
                    'toName'    => (string) ($row['outbox_to_name'] ?? ''),
                    'subject'   => (string) ($row['outbox_subject'] ?? ''),
                    'isHTML'    => false,
                    'text'      => (string) ($row['outbox_body'] ?? ''),
                    // nobody waits for this message, so a hanging server costs
                    // seconds instead of tying up the worker for minutes
                    'timeout'   => self::WARNING_TIMEOUT,
                ));
            } catch (\Throwable $e) {
                $sent = array(false, $e->getMessage());
            }
        }

        if (!empty($sent[0])) {
            _dbQuery(
                'DELETE FROM ' . DB_PREPEND . 'phpwcms_user_2fa_outbox WHERE outbox_id=' . $id,
                'DELETE'
            );
            return;
        }

        $attempts = (int) ($row['outbox_attempts'] ?? 0) + 1;

        if ($attempts >= self::OUTBOX_ATTEMPTS) {
            // given up, but the activity list keeps the trail
            _dbQuery(
                'DELETE FROM ' . DB_PREPEND . 'phpwcms_user_2fa_outbox WHERE outbox_id=' . $id,
                'DELETE'
            );
            self::logNotificationFailure($userId, $login);
            return;
        }

        _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user_2fa_outbox SET outbox_attempts=' . $attempts
            . ', outbox_last_attempt=' . time()
            . ', outbox_error=' . _dbEscape(self::errorText($sent[1] ?? ''))
            . ' WHERE outbox_id=' . $id,
            'UPDATE'
        );
    }

    /**
     * The mailer's report in the form the column and the pending table can take:
     * one line, no control characters, short enough for the varchar.
     */
    private static function errorText(mixed $error): string
    {
        $text = trim((string) preg_replace('/[\s\x00-\x1f]+/u', ' ', (string) $error));

        return $text === '' ? '' : mb_substr($text, 0, 250);
    }

    /**
     * Release what the request no longer needs while the mail is on the wire:
     * the session lock, so a second request of the same session is not blocked,
     * and the connection to the client.
     */
    private static function finishResponse(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        ignore_user_abort(true);
    }

    /**
     * Record that a lock warning never reached the mailer, so the audit list
     * can tell a silent mailer from a warning that was handed over.
     */
    private static function logNotificationFailure(int $userId, string $login = ''): void
    {
        if ($userId <= 0) {
            return;
        }

        if ($login === '') {
            $result = _dbQuery(
                'SELECT usr_login FROM ' . DB_PREPEND . 'phpwcms_user WHERE usr_id=' . $userId
            );
            $login  = (string) ($result[0]['usr_login'] ?? '');
        }

        PhpwcmsTwoFactorLog::add(
            PhpwcmsTwoFactorLog::NOTIFICATION_FAILED,
            $userId,
            $login
        );
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
