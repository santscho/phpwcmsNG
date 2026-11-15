<?php
/**
 * phpwcms content management system
 *
 * Throwaway self test for the two factor proof of concept. It only checks the
 * TOTP helper against the published RFC 6238 test vectors and the replay rule.
 * Run with: php include/inc_act/act_2fa_selftest.php
 *
 **/

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

define('PHPWCMS_ROOT', dirname(__DIR__, 2));
require_once PHPWCMS_ROOT . '/include/inc_lib/classes/class.totp.php';

$failures = 0;
$check = static function (string $label, $expected, $actual) use (&$failures): void {
    $ok = $expected === $actual;
    if (!$ok) {
        $failures++;
    }
    printf("%-46s %s\n", $label, $ok ? 'ok' : 'FAIL (' . var_export($actual, true) . ' != ' . var_export($expected, true) . ')');
};

// RFC 6238 appendix B, SHA-1, secret "12345678901234567890", 8 digits.
// The helper is fixed to six digits, so the last six digits are compared.
$secret = PhpwcmsTotp::base32Encode('12345678901234567890');
$check('base32 of the RFC secret', 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', $secret);
$check('base32 round trip', '12345678901234567890', PhpwcmsTotp::base32Decode($secret));

$vectors = array(
    59          => '287082',
    1111111109  => '081804',
    1111111111  => '050471',
    1234567890  => '005924',
    2000000000  => '279037',
    20000000000 => '353130',
);

foreach ($vectors as $time => $expected) {
    $check('RFC vector at ' . $time, $expected, PhpwcmsTotp::code($secret, PhpwcmsTotp::slice((int) $time)));
}

// width of a generated secret and its alphabet
$generated = PhpwcmsTotp::randomSecret(20);
$check('generated secret length', 32, strlen($generated));
$check('generated secret alphabet', 0, preg_match('/[^A-Z2-7]/', $generated) ?? 0);

$slice = PhpwcmsTotp::slice(1111111109);
$code  = PhpwcmsTotp::code($secret, $slice);
$check('accepts current slice', $slice, PhpwcmsTotp::verify($secret, $code, 1, 0, 1111111109));
$check('accepts previous slice', $slice - 1, PhpwcmsTotp::verify($secret, PhpwcmsTotp::code($secret, $slice - 1), 1, 0, 1111111109));
$check('rejects two slices back', false, PhpwcmsTotp::verify($secret, PhpwcmsTotp::code($secret, $slice - 2), 1, 0, 1111111109));
$check('rejects replay of used slice', false, PhpwcmsTotp::verify($secret, $code, 1, $slice, 1111111109));
$check('rejects wrong code', false, PhpwcmsTotp::verify($secret, '000000', 1, 0, 1111111109));
$check('rejects short code', false, PhpwcmsTotp::verify($secret, '1234', 1, 0, 1111111109));
$check('rejects empty secret', false, PhpwcmsTotp::verify('', $code, 1, 0, 1111111109));
$check('accepts spaces in the input', $slice, PhpwcmsTotp::verify($secret, ' 081 804 ', 1, 0, 1111111109));

$uri = PhpwcmsTotp::provisioningUri('ABC234', 'admin', 'phpwcmsng.test');
$check('provisioning uri scheme', 'otpauth://totp/phpwcmsng.test%3Aadmin?secret=ABC234&issuer=phpwcmsng.test&algorithm=SHA1&digits=6&period=30', $uri);

printf("\n%s\n", $failures === 0 ? 'all checks passed' : $failures . ' check(s) failed');
exit($failures === 0 ? 0 : 1);
