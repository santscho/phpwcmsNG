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
 * Minimal time based one time password helper (RFC 6238).
 *
 * Self contained on purpose: the proof of concept should not need a new
 * composer package. SHA-1 with six digits and a 30 second period is what
 * every current authenticator app supports.
 */
class PhpwcmsTotp {

    public const PERIOD   = 30;
    public const DIGITS   = 6;
    public const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Create a new base32 encoded shared secret.
     */
    public static function randomSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    public static function base32Encode(string $data): string
    {
        if ($data === '') {
            return '';
        }
        $bits = '';
        for ($i = 0, $length = strlen($data); $i < $length; $i++) {
            $bits .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }
        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }
        return $encoded;
    }

    public static function base32Decode(string $encoded): string
    {
        $encoded = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $encoded));
        if ($encoded === '') {
            return '';
        }
        $bits = '';
        for ($i = 0, $length = strlen($encoded); $i < $length; $i++) {
            $position = strpos(self::ALPHABET, $encoded[$i]);
            if ($position === false) {
                return '';
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }
        $decoded = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $decoded .= chr(bindec($byte));
            }
        }
        return $decoded;
    }

    /**
     * The current time slice (counter) for a timestamp.
     */
    public static function slice(int $time = 0): int
    {
        return intdiv($time ?: time(), self::PERIOD);
    }

    /**
     * The six digit code for one time slice.
     */
    public static function code(string $secret, int $slice): string
    {
        $key = self::base32Decode($secret);
        if ($key === '') {
            return '';
        }
        $hash   = hash_hmac('sha1', pack('N2', 0, $slice), $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value  = ((ord($hash[$offset]) & 0x7F) << 24)
                | (ord($hash[$offset + 1]) << 16)
                | (ord($hash[$offset + 2]) << 8)
                | ord($hash[$offset + 3]);
        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a submitted code and return the accepted time slice.
     *
     * @param int $window    accepted neighbours, 1 allows 30 seconds of drift
     * @param int $lastSlice highest slice already used, prevents replaying a code
     * @return int|false
     */
    public static function verify(string $secret, string $code, int $window = 1, int $lastSlice = 0, int $time = 0): int|false
    {
        $code = preg_replace('/\D/', '', $code);
        if ($secret === '' || strlen($code) !== self::DIGITS) {
            return false;
        }
        $current = self::slice($time);
        for ($offset = -$window; $offset <= $window; $offset++) {
            $slice = $current + $offset;
            if ($slice <= 0 || $slice <= $lastSlice) {
                continue;
            }
            if (hash_equals(self::code($secret, $slice), $code)) {
                return $slice;
            }
        }
        return false;
    }

    /**
     * The otpauth:// URI an authenticator app reads from a QR code.
     */
    public static function provisioningUri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=' . self::DIGITS
            . '&period=' . self::PERIOD;
    }
}
