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
    PhpwcmsTwoFactorLog::CODE_FAILED => 'theme-danger',
    PhpwcmsTwoFactorLog::LOCKED      => 'theme-danger',
    PhpwcmsTwoFactorLog::RECOVERY_USED => 'theme-warning',
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
                            <td><span class="badge <?php echo $two_factor_marks[$two_factor_event] ?? 'theme-secondary' ?>"><?php echo html($two_factor_labels[$two_factor_event] ?? $two_factor_event) ?></span></td>
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

    </div>

</div>
