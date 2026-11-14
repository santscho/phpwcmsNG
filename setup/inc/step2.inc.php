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

if (!defined('PHP8')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}

if ($err) {
    echo '<div class="alert alert-danger" role="alert"><strong>Check your admin user name and password!</strong></div>';
}

?>
                    <h1 class="h3 d-flex align-items-center gap-2">
                        <span class="badge text-bg-primary">7</span>
                        Path settings
                    </h1>
                    <form action="setup.php?step=2" method="post">

                        <div class="row mb-3">
                            <label for="site" class="col-md-3 col-form-label">Site basis</label>
                            <div class="col-md-9">
                                <input name="site" type="text" class="form-control" id="site"
                                       value="<?php echo html_specialchars($phpwcms["site"]) ?>"
                                       placeholder="<?php echo get_url_origin(true); ?>">
                                <div class="form-text">current: <?php echo get_url_origin(true); ?></div>
                                <div class="form-text">do NOT add any subdir here like <?php echo get_url_origin(true); ?>/wcms/</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h2 class="h5">Settings for handling email sending in phpwcms</h2>

                        <div class="row mb-3">
                            <label for="smtp_from_email" class="col-md-3 col-form-label">from/reply-to email</label>
                            <div class="col-md-9">
                                <input name="smtp_from_email" type="text" class="form-control" id="smtp_from_email"
                                       value="<?php echo ($phpwcms['SMTP_FROM_EMAIL']) ? html_specialchars($phpwcms['SMTP_FROM_EMAIL']) : html_specialchars($phpwcms["admin_email"]) ?>">
                                <div class="form-text">default: <?php echo html_specialchars($phpwcms["admin_email"]) ?></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_from_name" class="col-md-3 col-form-label">from/reply-to name</label>
                            <div class="col-md-9">
                                <input name="smtp_from_name" type="text" class="form-control" id="smtp_from_name"
                                       value="<?php echo ($phpwcms['SMTP_FROM_NAME']) ? html_specialchars($phpwcms['SMTP_FROM_NAME']) : 'webmaster' ?>">
                                <div class="form-text">default: webmaster</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_host" class="col-md-3 col-form-label">SMTP server</label>
                            <div class="col-md-9">
                                <input name="smtp_host" type="text" class="form-control" id="smtp_host"
                                       value="<?php echo ($phpwcms['SMTP_HOST']) ? html_specialchars($phpwcms['SMTP_HOST']) : 'localhost' ?>">
                                <div class="form-text">default: localhost</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_port" class="col-md-3 col-form-label">SMTP port</label>
                            <div class="col-md-9">
                                <input name="smtp_port" type="text" class="form-control" id="smtp_port"
                                       value="<?php echo ($phpwcms['SMTP_PORT']) ? intval($phpwcms['SMTP_PORT']) : '25'; ?>">
                                <div class="form-text">default: 25</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_mailer" class="col-md-3 col-form-label">mail method</label>
                            <div class="col-md-9">
                                <select name="smtp_mailer" id="smtp_mailer" class="form-select">
                                    <option value="mail"<?php if (strtolower($phpwcms['SMTP_MAILER']) == 'mail') echo ' selected="selected"'; ?>>PHP mail()</option>
                                    <option value="smtp"<?php if (strtolower($phpwcms['SMTP_MAILER']) == 'smtp') echo ' selected="selected"'; ?>>SMTP</option>
                                    <option value="sendmail"<?php if (strtolower($phpwcms['SMTP_MAILER']) == 'sendmail') echo ' selected="selected"'; ?>>UNIX sendmail</option>
                                </select>
                                <div class="form-text">default: mail (smtp, sendmail)</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-9 offset-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" name="smtp_auth" type="checkbox" id="smtp_auth"
                                           value="1"<?php if (intval($phpwcms['SMTP_AUTH']) == 1) echo ' checked="checked"'; ?>>
                                    <label class="form-check-label" for="smtp_auth">use SMTP_AUTH</label>
                                </div>
                                <div class="form-text">default: ON</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_user" class="col-md-3 col-form-label">SMTP login</label>
                            <div class="col-md-9">
                                <input name="smtp_user" type="text" class="form-control" id="smtp_user"
                                       value="<?php echo html_specialchars($phpwcms['SMTP_USER']) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_pass" class="col-md-3 col-form-label">SMTP password</label>
                            <div class="col-md-9">
                                <input name="smtp_pass" type="text" class="form-control" id="smtp_pass"
                                       value="<?php echo html_specialchars($phpwcms['SMTP_PASS']) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="smtp_secure" class="col-md-3 col-form-label">secure connection</label>
                            <div class="col-md-9">
                                <select name="smtp_secure" id="smtp_secure" class="form-select">
                                    <option value=""<?php if (empty($phpwcms['SMTP_SECURE'])) echo ' selected="selected"'; ?>>default (no secure connection)</option>
                                    <option value="ssl"<?php if (strtolower($phpwcms['SMTP_SECURE']) == 'ssl') echo ' selected="selected"'; ?>>SSL</option>
                                    <option value="tls"<?php if (strtolower($phpwcms['SMTP_SECURE']) == 'tls') echo ' selected="selected"'; ?>>TLS</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="Submit" value="send site data" class="btn theme-primary btn-solid btn-styled btn-sm">send site data</button>
                        </div>
                        <input name="do" type="hidden" value="1">
                    </form>
