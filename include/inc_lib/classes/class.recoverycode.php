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
 * Single use recovery codes for the two factor proof of concept.
 *
 * A lost phone must not lock an administrator out, so enrollment hands out a
 * list of codes. Only their hashes are stored; a code is removed the moment it
 * was used, which makes every one of them work exactly once.
 *
 * The alphabet leaves out the characters that are easy to mix up when a code
 * is copied from paper (0, 1, I, L and O).
 */
class PhpwcmsRecoveryCode {

    public const COUNT  = 8;
    public const LENGTH = 10;

    private const ALPHABET  = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const SEPARATOR = "\n";

    /**
     * A fresh list of codes, formatted for the screen.
     *
     * @return array<int, string>
     */
    public static function generate(int $count = self::COUNT): array
    {
        $codes = [];
        while (count($codes) < $count) {
            $code = '';
            for ($i = 0; $i < self::LENGTH; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $codes[self::normalize($code)] = self::format($code);
        }

        return array_values($codes);
    }

    /**
     * Half a code, a dash, the other half: easier to read from paper.
     */
    public static function format(string $code): string
    {
        $code = self::normalize($code);
        if (strlen($code) !== self::LENGTH) {
            return $code;
        }
        return substr($code, 0, intdiv(self::LENGTH, 2)) . '-' . substr($code, intdiv(self::LENGTH, 2));
    }

    /**
     * Whatever the user typed, reduced to the characters the code consists of.
     */
    public static function normalize(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    public static function hash(string $code): string
    {
        return hash('sha256', self::normalize($code));
    }

    /**
     * @param array<int, string> $codes
     */
    public static function hashList(array $codes): string
    {
        $hashes = [];
        foreach ($codes as $code) {
            $hashes[] = self::hash($code);
        }
        return implode(self::SEPARATOR, $hashes);
    }

    public static function remaining(string $stored): int
    {
        $stored = trim($stored);
        if ($stored === '') {
            return 0;
        }
        return count(array_filter(preg_split('/\R/', $stored) ?: [], static fn($line) => trim($line) !== ''));
    }

    /**
     * Use one code up: returns the remaining hashes, or null when no code
     * matched. A matching code is dropped from the list.
     */
    public static function consume(string $stored, string $code): ?string
    {
        $code = self::normalize($code);
        if ($stored === '' || strlen($code) !== self::LENGTH) {
            return null;
        }

        $given = self::hash($code);
        $lines = array_values(array_filter(
            preg_split('/\R/', trim($stored)) ?: [],
            static fn($line) => trim($line) !== ''
        ));

        // compare against every hash, so the time taken does not tell which one matched
        $match = null;
        foreach ($lines as $index => $line) {
            if (hash_equals(trim($line), $given) && $match === null) {
                $match = $index;
            }
        }
        if ($match === null) {
            return null;
        }

        unset($lines[$match]);

        return implode(self::SEPARATOR, $lines);
    }
}
