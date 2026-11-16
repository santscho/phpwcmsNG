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

$two_factor_enabled = !empty($two_factor_state['enabled']);
$two_factor_account = $_SESSION['wcs_user'] ?? '';
$two_factor_uri     = $two_factor_enroll
    ? PhpwcmsTotp::provisioningUri($two_factor_enroll['secret'], $two_factor_account, PHPWCMS_HOST)
    : '';

// a very long account or host name would not fit, the secret below stays
$two_factor_qr = '';
if ($two_factor_uri !== '') {
    try {
        $two_factor_qr = PhpwcmsQrCode::svg($two_factor_uri, PhpwcmsQrCode::ECC_M, $BL['be_2fa_qr_alt']);
    } catch (RuntimeException $e) {
        $two_factor_qr = '';
    }
}
?>
<div class="row g-4">

    <div class="col-12 xl:col-8">

        <div class="card">

            <h2 class="card-header h6 mb-0 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span><?php echo $BL['be_2fa_title'] ?></span>
                <?php if($two_factor_enabled): ?>
                <span class="badge theme-success"><?php echo $BL['be_2fa_status_on'] ?></span>
                <?php else: ?>
                <span class="badge theme-secondary"><?php echo $BL['be_2fa_status_off'] ?></span>
                <?php endif; ?>
            </h2>

            <div class="card-body">

<?php if($two_factor_message): ?>
                <div class="alert theme-success" role="alert"><div><?php echo html($two_factor_message) ?></div></div>
<?php endif; ?>
<?php if($two_factor_error): ?>
                <div class="alert theme-danger" role="alert"><div><?php echo html($two_factor_error) ?></div></div>
<?php endif; ?>

<?php if($two_factor_new_codes): ?>
                <div class="alert theme-warning" role="alert">
                    <h3 class="h6 mb-3"><?php echo $BL['be_2fa_recovery_new'] ?></h3>
                    <p><?php echo $BL['be_2fa_recovery_note'] ?></p>
                    <ul class="list-unstyled row g-3 mb-0">
<?php foreach($two_factor_new_codes as $two_factor_code_item): ?>
                        <li class="col-12 md:col-6"><code><?php echo html($two_factor_code_item) ?></code></li>
<?php endforeach; ?>
                    </ul>
                </div>
<?php endif; ?>

                <p class="fg-secondary"><?php echo $BL['be_2fa_intro'] ?></p>

<?php if($two_factor_enabled): ?>

                <p class="mb-0"><?php echo $BL['be_2fa_active_note'] ?></p>
                <p class="small fg-secondary mt-3 mb-0"><?php echo $BL['be_2fa_recovery_left'] ?>: <?php echo (int) $two_factor_state['recovery_left'] ?></p>

            </div>

            <div class="card-footer">
                <form action="phpwcms.php?do=profile&amp;p=2" method="post" class="row g-3 align-items-end">
                    <input type="hidden" name="form_aktion" value="update_2fa" />
                    <input type="hidden" name="do_2fa" value="disable" />
                    <div class="col-12 md:col-4">
                        <label class="form-label" for="password_2fa_disable"><?php echo $BL['be_2fa_password_label'] ?></label>
                        <input type="password" class="form-control" name="password_2fa" id="password_2fa_disable" autocomplete="current-password" required />
                    </div>
                    <div class="col-12 md:col-4">
                        <label class="form-label" for="code_2fa_disable"><?php echo $BL['be_2fa_code_label'] ?></label>
                        <input type="text" class="form-control" name="code_2fa" id="code_2fa_disable" autocomplete="one-time-code" maxlength="11" required />
                    </div>
                    <div class="col-12 md:col-4">
                        <button type="submit" class="btn btn-outline theme-danger btn-sm"><?php echo $BL['be_2fa_disable'] ?></button>
                    </div>
                </form>
            </div>

<?php elseif($two_factor_enroll): ?>

                <div class="alert theme-warning" role="alert"><div><?php echo $BL['be_2fa_scan_note'] ?></div></div>

<?php if($two_factor_qr): ?>
                <div class="mb-4"><?php echo $two_factor_qr ?></div>
<?php endif; ?>

                <h3 class="h6 mb-4"><?php echo $BL['be_2fa_secret_label'] ?></h3>
                <p class="mb-4"><code><?php echo html($two_factor_enroll['secret']) ?></code></p>

                <p class="mb-4"><small class="fg-secondary"><?php echo $BL['be_2fa_uri_label'] ?></small><br />
                    <code class="small"><?php echo html($two_factor_uri) ?></code></p>

            </div>

            <div class="card-footer">
                <form action="phpwcms.php?do=profile&amp;p=2" method="post" class="row g-3 align-items-end">
                    <input type="hidden" name="form_aktion" value="update_2fa" />
                    <input type="hidden" name="do_2fa" value="confirm" />
                    <div class="col-12 md:col-6">
                        <label class="form-label" for="code_2fa_confirm"><?php echo $BL['be_2fa_code_label'] ?></label>
                        <input type="text" class="form-control" name="code_2fa" id="code_2fa_confirm" inputmode="numeric" autocomplete="one-time-code" maxlength="6" autofocus />
                    </div>
                    <div class="col-12 md:col-6">
                        <button type="submit" class="btn theme-primary btn-solid btn-styled btn-sm"><?php echo $BL['be_2fa_confirm'] ?></button>
                    </div>
                </form>
            </div>

<?php else: ?>

                <p class="mb-0"><?php echo $BL['be_2fa_inactive_note'] ?></p>

            </div>

            <div class="card-footer">
                <form action="phpwcms.php?do=profile&amp;p=2" method="post" class="row g-3 align-items-end">
                    <input type="hidden" name="form_aktion" value="update_2fa" />
                    <input type="hidden" name="do_2fa" value="begin" />
                    <div class="col-12 md:col-6">
                        <label class="form-label" for="password_2fa_begin"><?php echo $BL['be_2fa_password_label'] ?></label>
                        <input type="password" class="form-control" name="password_2fa" id="password_2fa_begin" autocomplete="current-password" required />
                    </div>
                    <div class="col-12 md:col-6">
                        <button type="submit" class="btn theme-primary btn-solid btn-styled btn-sm"><?php echo $BL['be_2fa_begin'] ?></button>
                    </div>
                </form>
            </div>

<?php endif; ?>

        </div>

        <div class="card mt-5">

            <h2 class="card-header h6 mb-0"><?php echo $BL['be_2fa_log_title'] ?></h2>

<?php if($two_factor_log): ?>
            <ul class="list-group list-group-flush">
<?php foreach($two_factor_log as $two_factor_log_row): ?>
                <li class="list-group-item d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <span><?php echo html($BL['be_2fa_log_' . (string) ($two_factor_log_row['log_event'] ?? '')] ?? (string) ($two_factor_log_row['log_event'] ?? '')) ?></span>
                    <small class="fg-secondary"><?php echo html(date(($backend_date_format['php'] ?? 'Y-m-d H:i:s'), (int) ($two_factor_log_row['log_created'] ?? 0))) ?></small>
                </li>
<?php endforeach; ?>
            </ul>
<?php else: ?>
            <div class="card-body">
                <p class="mb-0 fg-secondary"><?php echo $BL['be_2fa_log_empty'] ?></p>
            </div>
<?php endif; ?>

        </div>

    </div>

</div>
