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

?>
                    <h1 class="h3">phpwcms path settings</h1>
                    <p>Please check that any path value does NOT begin or end with a slash. I recommend to use the default values. It's the best way for 1st time usage.</p>

                    <form action="setup.php?step=3" method="post">

                        <div class="row mb-5">
                            <label for="doc_root" class="md:col-3 col-form-label">root path</label>
                            <div class="md:col-9">
                                <input name="doc_root" type="text" class="form-control" id="doc_root" value="<?php echo html_specialchars($phpwcms["DOC_ROOT"]) ?>" maxlength="100">
                                <div class="form-text">default: $_SERVER['DOCUMENT_ROOT'] &#8212; document root of your web account; empty for default value<br><?php echo html_specialchars($_SERVER['DOCUMENT_ROOT']) ?></div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <label for="root" class="md:col-3 col-form-label">phpwcms root</label>
                            <div class="md:col-9">
                                <input name="root" type="text" class="form-control" id="root" value="<?php echo html_specialchars($phpwcms["root"]) ?>" maxlength="100">
                                <div class="form-text">default: (empty) &#8212; if you install in another directory than your base webserver root insert the directory name here, most times this is the part after your base URL like http://mysite.com/<strong>phpwcms_root</strong></div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <label for="file_path" class="md:col-3 col-form-label">filestorage</label>
                            <div class="md:col-9">
                                <input name="file_path" type="text" class="form-control" id="file_path" value="<?php echo html_specialchars($phpwcms["file_path"]) ?>" maxlength="100">
                                <div class="form-text">default: filearchive &#8212; it's recommend to put this directory outside a folder available via web, you can use it like this: ../filearchive</div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <label for="templates" class="md:col-3 col-form-label">templates</label>
                            <div class="md:col-9">
                                <input name="templates" type="text" class="form-control" id="templates" value="<?php echo html_specialchars($phpwcms["templates"]) ?>" maxlength="100">
                                <div class="form-text">default: template &#8212; here are the template directories located where all template stuff is stored</div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <label for="ftp_path" class="md:col-3 col-form-label">ftp takeover</label>
                            <div class="md:col-9">
                                <input name="ftp_path" type="text" class="form-control" id="ftp_path" value="<?php echo html_specialchars($phpwcms["ftp_path"]) ?>" maxlength="100">
                                <div class="form-text">default: upload &#8212; this is the directory in which you can upload files for ftp takeover functionality. You can also use it like this: ../../myftpdir</div>
                            </div>
                        </div>

                        <div class="mt-7">
                            <button type="submit" name="Submit" value="send path values" class="btn theme-primary btn-solid btn-styled btn-sm">send path values</button>
                        </div>
                        <input name="do" type="hidden" value="1">
                    </form>
