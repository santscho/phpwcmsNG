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

$two_factor_labels = array();
foreach (PhpwcmsTwoFactorLog::events() as $two_factor_event) {
    $two_factor_labels[$two_factor_event] = $BL['be_2fa_log_' . $two_factor_event] ?? $two_factor_event;
}

$two_factor_marks = array(
    PhpwcmsTwoFactorLog::CODE_FAILED     => 'theme-danger',
    PhpwcmsTwoFactorLog::LOCKED          => 'theme-danger',
    PhpwcmsTwoFactorLog::RECOVERY_USED   => 'theme-warning',
    PhpwcmsTwoFactorLog::PASSWORD_FAILED   => 'theme-danger',
    PhpwcmsTwoFactorLog::PASSWORD_LOCKED   => 'theme-danger',
    PhpwcmsTwoFactorLog::NOTIFICATION_FAILED => 'theme-warning',
    PhpwcmsTwoFactorLog::RESET           => 'theme-danger',
    PhpwcmsTwoFactorLog::RESET_SCHEDULED => 'theme-warning',
    PhpwcmsTwoFactorLog::RESET_CANCELLED => 'theme-success',
);
?>
<div class="row g-4">

    <div class="col-12 xl:col-8">

        <div class="card">

            <h2 class="card-header h6 mb-0 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span><?php echo $BL['be_2fa_log_all_title'] ?></span>
                <a class="btn btn-outline theme-secondary btn-sm" href="phpwcms.php?do=profile&amp;p=2"><?php echo $BL['be_2fa_log_back'] ?></a>
            </h2>

<?php if($two_factor_audit): ?>

            <div class="overflow-auto">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo $BL['be_2fa_log_col_time'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_log_col_user'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_log_col_event'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach($two_factor_audit as $two_factor_audit_row):
    $two_factor_event = (string) ($two_factor_audit_row['log_event'] ?? '');
?>
                        <tr>
                            <td class="small fg-secondary"><?php echo html(date(($backend_date_format['php'] ?? 'Y-m-d H:i:s'), (int) ($two_factor_audit_row['log_created'] ?? 0))) ?></td>
                            <td><?php echo html((string) ($two_factor_audit_row['log_login'] ?? '')) ?></td>
                            <td>
                                <span class="badge <?php echo $two_factor_marks[$two_factor_event] ?? 'theme-secondary' ?>"><?php echo html($two_factor_labels[$two_factor_event] ?? $two_factor_event) ?></span>
<?php if(!empty($two_factor_audit_row['log_actor']) && ($two_factor_audit_row['log_actor'] ?? '') !== ($two_factor_audit_row['log_login'] ?? '')): ?>
                                <small class="fg-secondary"><?php echo $BL['be_2fa_log_by'] . ' ' . html((string) $two_factor_audit_row['log_actor']) ?></small>
<?php endif; ?>
                            </td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

<?php else: ?>

            <div class="card-body">
                <p class="mb-0 fg-secondary"><?php echo $BL['be_2fa_log_all_empty'] ?></p>
            </div>

<?php endif; ?>

        </div>

        <div class="card mt-5">

            <h2 class="card-header h6 mb-0 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span><?php echo $BL['be_2fa_outbox_title'] ?></span>
                <span class="badge <?php echo $two_factor_outbox ? 'theme-warning' : 'theme-success' ?>"><?php echo count($two_factor_outbox) ?></span>
            </h2>

<?php if($two_factor_outbox): ?>

            <div class="overflow-auto">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo $BL['be_2fa_outbox_col_account'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_outbox_col_kind'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_outbox_col_created'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_outbox_col_attempts'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_outbox_col_last'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach($two_factor_outbox as $two_factor_pending):
    $two_factor_attempts = (int) ($two_factor_pending['outbox_attempts'] ?? 0);
    $two_factor_last     = (int) ($two_factor_pending['outbox_last_attempt'] ?? 0);
    $two_factor_error    = trim((string) ($two_factor_pending['outbox_error'] ?? ''));
    $two_factor_kind     = in_array($two_factor_pending['outbox_kind'] ?? '', array('code', 'reset'), true)
        ? (string) $two_factor_pending['outbox_kind']
        : 'password';
?>
                        <tr>
                            <td><?php echo html((string) ($two_factor_pending['outbox_login'] ?? '')) ?></td>
                            <td><span class="badge theme-secondary"><?php echo $BL['be_2fa_outbox_kind_' . $two_factor_kind] ?></span></td>
                            <td class="small fg-secondary"><?php echo html(date(($backend_date_format['php'] ?? 'Y-m-d H:i:s'), (int) ($two_factor_pending['outbox_created'] ?? 0))) ?></td>
                            <td><span class="badge <?php echo $two_factor_attempts >= 3 ? 'theme-danger' : 'theme-secondary' ?>"><?php echo sprintf($BL['be_2fa_outbox_attempts'], $two_factor_attempts, PhpwcmsTwoFactorGuard::OUTBOX_ATTEMPTS) ?></span>
<?php if($two_factor_error !== ''): ?>
                                <div class="small <?php echo $two_factor_attempts >= 3 ? 'fg-danger' : 'fg-secondary' ?>"><?php echo html($BL['be_2fa_outbox_error_label'] . ': ' . $two_factor_error) ?></div>
<?php endif; ?>
                            </td>
                            <td class="small fg-secondary"><?php echo $two_factor_last > 0 ? html(date(($backend_date_format['php'] ?? 'Y-m-d H:i:s'), $two_factor_last)) : '&mdash;' ?></td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <small class="fg-secondary"><?php echo $BL['be_2fa_outbox_hint'] ?></small>
            </div>

<?php else: ?>

            <div class="card-body">
                <p class="mb-0 fg-secondary"><?php echo $BL['be_2fa_outbox_empty'] ?></p>
            </div>

<?php endif; ?>

        </div>

        <div class="card mt-5">

            <h2 class="card-header h6 mb-0"><?php echo $BL['be_2fa_admins_title'] ?></h2>

            <div class="card-body">

<?php if($two_factor_reset_done): ?>
                <div class="alert theme-success" role="alert"><div><?php echo html($two_factor_reset_done) ?></div></div>
<?php endif; ?>
<?php if($two_factor_reset_error): ?>
                <div class="alert theme-danger" role="alert"><div><?php echo html($two_factor_reset_error) ?></div></div>
<?php endif; ?>

                <p class="fg-secondary mb-0"><?php echo $BL['be_2fa_admins_note'] ?></p>

            </div>

            <div class="overflow-auto">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo $BL['be_2fa_log_col_user'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_col_status'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_col_codes'] ?></th>
                            <th scope="col"><?php echo $BL['be_2fa_col_action'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach($two_factor_admins as $two_factor_admin):
    $two_factor_admin_id   = (int) ($two_factor_admin['usr_id'] ?? 0);
    $two_factor_admin_on   = !empty($two_factor_admin['usr_2fa_enabled']);
    $two_factor_admin_self = $two_factor_admin_id === (int) ($_SESSION['wcs_user_id'] ?? 0);
?>
                        <tr>
                            <td>
                                <?php echo html((string) ($two_factor_admin['usr_login'] ?? '')) ?>
<?php if($two_factor_admin_self): ?>
                                <span class="badge theme-secondary"><?php echo $BL['be_2fa_you'] ?></span>
<?php endif; ?>
                            </td>
                            <td>
<?php if($two_factor_admin_on): ?>
                                <span class="badge theme-success"><?php echo $BL['be_2fa_status_on'] ?></span>
<?php else: ?>
                                <span class="badge theme-secondary"><?php echo $BL['be_2fa_status_off'] ?></span>
<?php endif; ?>
                            </td>
                            <td><?php echo $two_factor_admin_on ? (int) PhpwcmsRecoveryCode::remaining((string) ($two_factor_admin['usr_2fa_recovery'] ?? '')) : 0; ?></td>
                            <td>
<?php if($two_factor_admin_on && !$two_factor_admin_self): ?>
                                <form action="phpwcms.php?do=profile&amp;p=3" method="post" class="d-flex flex-wrap gap-2 align-items-end">
                                    <input type="hidden" name="form_aktion" value="reset_2fa" />
                                    <input type="hidden" name="reset_uid" value="<?php echo $two_factor_admin_id ?>" />
                                    <div>
                                        <label class="form-label" for="password_2fa_reset_<?php echo $two_factor_admin_id ?>"><?php echo $BL['be_2fa_password_label'] ?></label>
                                        <input type="password" class="form-control" name="password_2fa" id="password_2fa_reset_<?php echo $two_factor_admin_id ?>" autocomplete="current-password" required />
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-outline theme-danger btn-sm"><?php echo $BL['be_2fa_reset'] ?></button>
                                    </div>
                                </form>
<?php endif; ?>
                            </td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>
