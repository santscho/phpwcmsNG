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
                    <h1 class="h3 d-flex align-items-center gap-2">
                        <span class="badge text-bg-primary">3</span>
                        MySQL database settings
                    </h1>
<?php
if(isset($_POST["dbsavesubmit"]) && $err) {
    echo '<div class="alert alert-danger" role="alert"><strong>Please proof your database settings!</strong></div>';
    $_SESSION['admin_set'] = false;
}
?>
                    <form action="setup.php?step=1" method="post" autocomplete="off">

                        <div class="row mb-3">
                            <label for="db_host" class="col-md-3 col-form-label">MySQL host and port</label>
                            <div class="col-md-9">
                                <div class="row g-2">
                                    <div class="col-sm-8">
                                        <input name="db_host" type="text" class="form-control" id="db_host" value="<?php echo html_specialchars($phpwcms["db_host"]) ?>" placeholder="localhost">
                                    </div>
                                    <div class="col-sm-4">
                                        <input name="db_port" type="text" class="form-control" id="db_port" value="<?php echo $phpwcms["db_port"] ?>" placeholder="3306">
                                    </div>
                                </div>
                                <div class="form-text">default: localhost / 3306</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="db_user" class="col-md-3 col-form-label">DB user</label>
                            <div class="col-md-9">
                                <input name="db_user" type="text" class="form-control" id="db_user" value="<?php echo html_specialchars($phpwcms["db_user"]) ?>" placeholder="database user">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="db_pass" class="col-md-3 col-form-label">DB password</label>
                            <div class="col-md-9">
                                <input name="db_pass" type="text" class="form-control" id="db_pass" value="<?php echo html_specialchars($phpwcms["db_pass"]) ?>" placeholder="database password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="db_table" class="col-md-3 col-form-label">DB database</label>
                            <div class="col-md-9">
                                <input name="db_table" type="text" class="form-control" id="db_table" value="<?php echo html_specialchars($phpwcms["db_table"]) ?>" placeholder="database name" maxlength="255">
                                <div class="form-text">you have to create it <strong>before</strong> setup!!!</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="db_prepend" class="col-md-3 col-form-label">DB table prefix</label>
                            <div class="col-md-9">
                                <input name="db_prepend" type="text" class="form-control" id="db_prepend" value="<?php echo html_specialchars($prepend) ?>" maxlength="10">
                                <div class="form-text">default: none (&quot;&quot;), if filled in it will be <strong>prefix</strong>+<strong>_</strong></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-9 offset-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" name="db_pers" type="checkbox" id="db_pers" value="1" <?php echo ($phpwcms["db_pers"]) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="db_pers">use persistent database connection</label>
                                </div>
                                <div class="form-text">it should be safe to enable it</div>
                            </div>
                        </div>

<?php
if(!empty($db_additional)) {

?>
                        <hr class="my-4">

                        <h2 class="h3 d-flex align-items-center gap-2">
                            <span class="badge text-bg-primary">4</span>
                            Charset &amp; MySQL <span class="fs-5 text-body-secondary">(v<?php echo html_specialchars($row[0]) ?>)</span>
                        </h2>

                        <div class="row mb-3">
                            <label for="charset" class="col-md-3 col-form-label">Charset</label>
                            <div class="col-md-9">
                                <select name="charset" id="charset" class="form-select">
<?php

            foreach($available_languages as $key => $value) {

                list(, $_lang_charset)  = explode('-', $value[1], 2);
                list(, $_lang_en)       = explode('|', $value[0]);

                echo '<option value="'.$key.'"';

                if($key === strtolower(str_replace('-', '', $phpwcms['default_lang']) .'-'. $phpwcms['charset'])) {
                    echo ' selected="selected"';
                }

                echo '>';
                echo empty($value[3]) ? '' : $value[3].' - ';
                echo ucfirst($_lang_en);
                echo ' ['.$_lang_charset;
                if(!empty($mysql_charset_map[$_lang_charset])) {
                    echo ' / '.$mysql_charset_map[$_lang_charset];
                }
                echo ']';
                echo "</option>";
            }

?>
                                </select>
                                <div class="form-text">
                                    <a href="https://dev.mysql.com/doc/refman/5.7/en/charset.html" target="_blank" rel="noopener">MySQL charset information</a>,
                                    <a href="https://www.w3.org/International/articles/http-charset/index" target="_blank" rel="noopener">HTTP charset information</a>
                                </div>
                                <input type="hidden" name="collation" value="utf8mb4_general_ci">
                            </div>
                        </div>

<?php

}

// now show setting which enables creating database
if(!empty($db_init)) {

?>
                        <hr class="my-4">

                        <h2 class="h3 d-flex align-items-center gap-2">
                            <span class="badge text-bg-primary">5</span>
                            Default phpwcms database schema
                        </h2>
<?php

    if(empty($db_no_create) && !empty($_db_prepend_error) && isset($_POST['db_sql_hidden'])) {
        echo '<div class="alert alert-warning" role="alert"><strong>phpwcms tables still exists in choosen database. Rename table prefix might help!</strong></div>';
        $_SESSION['admin_set'] = false;
    }
    if(isset($db_create_err) && count($db_create_err)) {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<p><strong>Errors while creating initial phpwcms tables. Solve it manually:</strong></p>';
        echo '<pre class="mb-0 small">' . html_specialchars(implode(";\n\n", $db_create_err) . ';') . '</pre>';
        echo '</div>';

        $_SESSION['admin_set']  = false;
        $sql_data               = false;
        $db_sql                 = false;

    } elseif(isset($db_create_err) || !empty($db_no_create)) {

        // OK fine - initial tables were created without error
        $_db_prepend = $phpwcms["db_prepend"] ? mysqli_real_escape_string($db, $phpwcms["db_prepend"]) . '_' : '';
        $check = _dbQuery("SHOW TABLES LIKE '".$_db_prepend."phpwcms_%'");

        if($check && count($check)) {

            $sql_data   = false;
            $db_sql     = false;
            $db_fine    = true;

?>
                        <div class="alert alert-success" role="alert">
                            <strong>Fine!</strong> All initial phpwcms tables were created or still exists.
                            <input type="hidden" name="db_sql_hidden" value="1">
                        </div>
<?php

        } else {

            $_SESSION['admin_set']  = false;

            $sql_data               = false;
            $db_sql                 = false;

            echo '<div class="alert alert-warning" role="alert"><strong>No phpwcms database table exists. Check before you continue!</strong>';
            echo '<input type="hidden" name="db_sql_hidden" value="1"></div>';

        }

    }


    if(empty($db_fine)) {
        // show info

?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" name="db_sql" type="checkbox" id="db_sql" value="1"<?php if(!empty($db_sql)) echo ' checked="checked"' ?>>
                            <label class="form-check-label" for="db_sql">create phpwcms db tables</label>
                            <input type="hidden" name="db_sql_hidden" value="1">
                        </div>
<?php

    }

    if(!empty($sql_data)) {

        $sql_data = explode(';', $sql_data);
        $statements = array();
        foreach($sql_data as $value) {

            $value = trim(preg_replace('/--\s/', '', trim($value)));

            if(empty($value)) {
                continue;
            }

            $value = html_specialchars($value);

            if(!str_starts_with(strtoupper(trim($value)), 'INSERT')) {
                $value .= ' DEFAULT';
                $value .= ' CHARACTER SET '.$phpwcms['db_charset'];
                $value .= ' COLLATE '.$phpwcms['db_collation'];
            }

            $statements[] = $value . ';';
        }

        if(empty($_db_prepend_error) && isset($_POST['db_sql_hidden'])) {
            echo '<input type="hidden" name="db_create" value="1">';
        }

        echo '<pre class="border rounded bg-body-tertiary p-3 small">' . implode("\n", $statements) . '</pre>';

    }
}


//  OK now lets create superuser
if(!empty($_SESSION['admin_set'])) {

?>
                        <hr class="my-4">

                        <h2 class="h3 d-flex align-items-center gap-2">
                            <span class="badge text-bg-primary">6</span>
                            Superuser settings
                        </h2>
<?php

    // as long as admin info wasn't written
    if(empty($_SESSION['admin_save'])) {

?>
                        <div class="row mb-3">
                            <label for="admin_name" class="col-md-3 col-form-label">Name</label>
                            <div class="col-md-9">
                                <input name="admin_name" type="text" class="form-control" id="admin_name" value="<?php echo empty($phpwcms["admin_name"]) ? "Webmaster" : html_specialchars($phpwcms["admin_name"]) ?>">
                                <div class="form-text">default: Webmaster</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="admin_user" class="col-md-3 col-form-label">Admin login</label>
                            <div class="col-md-9">
                                <input name="admin_user" type="text" class="form-control" id="admin_user" value="<?php echo empty($phpwcms["admin_user"]) ? "webmaster" : html_specialchars($phpwcms["admin_user"]) ?>">
                                <div class="form-text">default: admin</div>
                            </div>
                        </div>

<?php
    if(!empty($admin_err_pass)) {
        echo '<div class="alert alert-danger" role="alert"><strong>Invalid password! Password is case senitive, empty password not allowed.</strong></div>';
    }
?>

                        <div class="row mb-3">
                            <label for="admin_pass" class="col-md-3 col-form-label">Admin password</label>
                            <div class="col-md-9">
                                <input name="admin_pass" type="password" class="form-control" id="admin_pass" autocomplete="new-password">
                                <div class="form-text">default: phpwcms</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="admin_passrepeat" class="col-md-3 col-form-label">Repeat password</label>
                            <div class="col-md-9">
                                <input name="admin_passrepeat" type="password" class="form-control" id="admin_passrepeat" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="admin_email" class="col-md-3 col-form-label">Admin email</label>
                            <div class="col-md-9">
                                <input name="admin_email" type="text" class="form-control" id="admin_email" value="<?php echo html_specialchars($phpwcms["admin_email"]) ?>">
                                <div class="form-text">is used site wide</div>
                            </div>
                        </div>
<?php

    } else {

        $_db_prepend = $phpwcms["db_prepend"] ? mysqli_real_escape_string($db, $phpwcms["db_prepend"]) . '_' : '';

        //show Info that admin info was saved
        //and also if stored in database

        $user_check = _dbQuery('SELECT * FROM '.$_db_prepend."phpwcms_user WHERE usr_login='".mysqli_real_escape_string($db, $phpwcms['admin_user'])."'");

        if($user_check !== false && count($user_check)) {

            //hm user still exists - so try to update
            $sql  = "UPDATE ".$_db_prepend."phpwcms_user SET ";
            $sql .= "usr_login      = '".mysqli_real_escape_string($db, $phpwcms['admin_user'])."', ";
            $sql .= "usr_pass       = '".mysqli_real_escape_string($db, $phpwcms["admin_pass"])."', ";
            $sql .= "usr_email      = '".mysqli_real_escape_string($db, $phpwcms["admin_email"])."', ";
            $sql .= "usr_admin      = 1, ";
            $sql .= "usr_aktiv      = 1, ";
            $sql .= "usr_name       = '".mysqli_real_escape_string($db, $phpwcms['admin_name'])."', ";
            $sql .= "usr_lang       = '".mysqli_real_escape_string($db, $phpwcms['default_lang'])."', ";
            $sql .= "usr_wysiwyg    = 2, ";
            $sql .= "usr_fe         = 2 ";
            $sql .= "WHERE usr_login='".mysqli_real_escape_string($db, $phpwcms['admin_user'])."' LIMIT 1";

            $update_user = _dbQuery($sql, 'UPDATE');

        } elseif($user_check !== false) {

            //fine lets create new user
            $sql  = "INSERT INTO ".$_db_prepend."phpwcms_user (";
            $sql .= "usr_login, usr_pass, usr_email, ";
            $sql .= "usr_admin, usr_aktiv, usr_name, ";
            $sql .= "usr_var_structure, usr_var_publicfile, usr_var_privatefile, ";
            $sql .= "usr_lang, usr_wysiwyg, usr_fe, usr_vars";
            $sql .= ") VALUES (";
            $sql .= "'".mysqli_real_escape_string($db, $phpwcms['admin_user'])."', ";
            $sql .= "'".mysqli_real_escape_string($db, $phpwcms["admin_pass"])."', ";
            $sql .= "'".mysqli_real_escape_string($db, $phpwcms["admin_email"])."', ";
            $sql .= "1, 1, ";
            $sql .= "'".mysqli_real_escape_string($db, $phpwcms['admin_name'])."', ";
            $sql .= "'', ";
            $sql .= "'', ";
            $sql .= "'', ";
            $sql .= "'".mysqli_real_escape_string($db, $phpwcms['default_lang'])."', ";
            $sql .= "2, 2, ''";
            $sql .= ")";

            $create_user = _dbQuery($sql, 'INSERT');

        } else {

            $user_check = false;

        }

        if(!empty($create_user)) {

            // update
            echo '<div class="alert alert-success" role="alert"><strong>Done!</strong> Account for user <b>'.html_specialchars($phpwcms['admin_user']).'</b> was created.';
            echo '<input type="hidden" name="user_account" value="1"></div>';

        }

        if(!empty($update_user)) {

            // update
            echo '<div class="alert alert-success" role="alert"><strong>Done!</strong> Account of user <b>'.html_specialchars($phpwcms['admin_user']).'</b> was updated.';
            echo '<input type="hidden" name="user_account" value="1"></div>';

        }

        if($user_check === false) {

            // db error
            echo '<div class="alert alert-danger" role="alert"><strong>There is a database problem!</strong>';
            echo '<p class="mb-0">Account for user <b>'.html_specialchars($phpwcms['admin_user']).'</b> was not created or updated.<br>Click <b>continue</b> to try again.</p></div>';
            $_SESSION['admin_save'] = false;

        }

    }

}

?>
                        <div class="mt-4">
                            <button type="submit" name="dbsavesubmit" value="Continue" class="btn theme-primary btn-solid btn-styled btn-sm">Continue</button>
                        </div>
                        <input name="do" type="hidden" value="1">
                    </form>
