<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 *
 * @author Marus Köhl <info@pagewerkstatt.ch>
 * @link http://www.pagewerkstatt.ch
 *
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

$file_action = array(
    'file_dir' => empty($_POST['file_dir']) ? 0 : intval($_POST['file_dir']),
    'mark' => empty($_POST['ftp_mark']) || !is_array($_POST['ftp_mark']) ? array() : $_POST['ftp_mark'],
    'newdir' => empty($_POST['file_newdir']) ? 0 : intval($_POST['file_newdir'])
);

//Get post variables
if(isset($_POST['file_action'])) {
    $file_action['action']  = intval($_POST['file_action']);
    $file_action_msg        = $BL['file_actions_msg_error'];
} else {
    $file_action['action']  = 0;
    $file_action_msg        = '';
}

if($file_action['action'] === 1 && $file_action["mark"]) {
    if(is_array($file_action["mark"]) && count($file_action["mark"])) {
        foreach($file_action["mark"] as $key => $value) {
            $key = intval($key);
            if($key) {
                $sql = 'UPDATE '.DB_PREPEND.'phpwcms_file SET f_trash=1 WHERE f_id='.$key;
                @_dbQuery($sql, 'UPDATE');
            } else {
                unset($key);
            }
        }
        $file_action_msg = $BL['file_actions_msg_delete'];
    }
} elseif($file_action['action'] === 2 && $file_action["mark"]) {
    $newdir = intval($file_action["newdir"]);
    if(is_array($file_action["mark"]) && count($file_action["mark"])) {
        foreach($file_action["mark"] as $key => $value) {
            $key = intval($key);
            if($key) {
                $sql  = 'UPDATE '.DB_PREPEND.'phpwcms_file SET f_pid='.$newdir.' WHERE f_id='.$key;
                @_dbQuery($sql, 'UPDATE');
            } else {
                unset($key);
            }
        }
        $file_action_msg = $BL['file_actions_msg_move'];
    }
} elseif($file_action['action'] === 3 && $file_action["mark"]) {
    $file_action["aktiv"]   = empty($_POST["file_aktiv"]) ? 0 : 1;
    $file_action["public"]  = empty($_POST["file_public"]) ? 0 : 1;
    if(is_array($file_action["mark"]) && count($file_action["mark"])) {
        foreach($file_action["mark"] as $key => $value) {
            $key = intval($key);
            if($key) {
                $sql  = 'UPDATE '.DB_PREPEND.'phpwcms_file SET ';
                $sql .= "f_aktiv= " . $file_action["aktiv"] . ", ";
                $sql .= "f_public= " . $file_action["public"] . " ";
                $sql .= "WHERE f_id='".$key."'";
                @_dbQuery($sql, 'UPDATE');
            } else {
                unset($key);
            }
        }
        $file_action_msg = $BL['file_actions_msg_status'];
    }
} elseif($file_action['action'] === 4 && $file_action["mark"]) {
    $file_action["file_user"]   = intval($_POST["file_user"]);
    if(is_array($file_action["mark"]) && count($file_action["mark"]) && $file_action["file_user"] > 0) {
        foreach($file_action["mark"] as $key => $value) {
            $key = intval($key);
            if($key) {
                $sql  = 'UPDATE '.DB_PREPEND.'phpwcms_file SET ';
                $sql .= "f_pid=0, ";
                $sql .= "f_uid= " . $file_action["file_user"] . " ";
                $sql .= "WHERE f_id='".$key."'";
                @_dbQuery($sql, 'UPDATE');
            } else {
                unset($key);
            }
        }
        $file_action_msg = $BL['file_actions_msg_user'];
    }
}

// an action was performed on at least one marked file
$_files_action_ok = $file_action['action'] > 0 && !empty($file_action['mark']) && $file_action_msg !== $BL['file_actions_msg_error'];

// browse files of the selected folder
$fx = 0;
$_files_action_list = array();
$file_sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE f_pid=" . $file_action["file_dir"] .
            " AND f_trash=0 AND f_kid = 1 AND f_uid = " . $_SESSION["wcs_user_id"] . " ORDER BY f_name";
$file_result = _dbQuery($file_sql);
if(isset($file_result[0]['f_id'])) {
    foreach($file_result as $file_row) {
        // there is a big problem with special chars on Mac OS X and seems Windows too
        if(PHPWCMS_CHARSET !== 'utf-8' && phpwcms_seems_utf8($file_row["f_name"])) {
            $filename = str_replace('?', '', mb_convert_encoding($file_row["f_name"], PHPWCMS_CHARSET));
        } else {
            $filename = $file_row["f_name"];
        }
        $file_row['filename'] = html($filename);
        $_files_action_list[] = $file_row;
        $fx++;
    }
}

$_files_action_status = array(
    'active'    => '<span class="badge theme-success" title="' . html($BL['be_ftptakeover_active']) . '">' . backend_icon('toggle-on') . '</span>',
    'inactive'  => '<span class="badge theme-secondary" title="' . html($BL['be_inactive']) . '">' . backend_icon('toggle-off') . '</span>',
    'public'    => '<span class="badge theme-success" title="' . html($BL['be_ftptakeover_public']) . '">' . backend_icon('globe') . '</span>',
    'private'   => '<span class="badge theme-secondary" title="' . html($BL['be_fsearch_personal']) . '">' . backend_icon('lock') . '</span>',
);
?>
<script type="text/javascript">
function showAction() {
    var select = document.getElementById('file_action');
    var action = select ? parseInt(select.value, 10) : 0;
    var panel = {
        folder: document.getElementById('div_folder'),
        status: document.getElementById('div_status'),
        user:   document.getElementById('div_user'),
        button: document.getElementById('div_button')
    };
    if (!panel.button) {
        return;
    }
    panel.folder.classList.toggle('d-none', action !== 2);
    panel.status.classList.toggle('d-none', action !== 3);
    panel.user.classList.toggle('d-none', action !== 4);
    panel.button.classList.toggle('d-none', action === 0);
}

document.addEventListener('DOMContentLoaded', function () {
    showAction();

    var form = document.getElementById('filetakeover');
    var toggle = document.getElementById('toggle');
    if (!form || !toggle) {
        return;
    }
    toggle.addEventListener('change', function () {
        var marks = form.querySelectorAll('input.ftp_mark');
        for (var i = 0; i < marks.length; i++) {
            marks[i].checked = toggle.checked;
        }
    });
});
</script>

<?php if($file_action_msg) { ?>
<div class="alert <?php echo $_files_action_ok ? 'theme-success' : 'theme-danger' ?> mb-5" role="alert"><?php echo html($file_action_msg) ?></div>
<?php } ?>

<div class="card mb-5">

    <div class="card-header d-flex flex-column md:flex-row md:align-items-center gap-3">
        <h2 class="h6 mb-0"><?php echo $BL['be_subnav_file_actions'] ?></h2>
    </div>

    <div class="card-body">

        <form action="phpwcms.php?do=files&amp;p=4" method="post" name="folderform" id="folderform">

            <div class="row g-3">
                <div class="col-12 md:col-6">
                    <label class="form-label" for="file_dir"><?php echo $BL['file_actions_step1'] ?></label>
                    <select name="file_dir" id="file_dir" class="form-control" onchange="submit();">
                        <option value="0"><?php echo $BL['be_ftptakeover_rootdir'] ?></option>
                        <?php //get folders for user
                            dir_menu(0, $file_action["file_dir"], "+", $_SESSION["wcs_user_id"], "+");
                        ?>
                    </select>
                </div>
            </div>

        </form>

    </div>

</div>

<form action="phpwcms.php?do=files&amp;p=4" method="post" name="filetakeover" id="filetakeover">

    <input name="file_dir" type="hidden" value="<?php echo $file_action["file_dir"] ?>" />

    <div class="card mb-5">

        <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
            <h2 class="h6 mb-0"><?php echo $BL['file_actions_step2'] ?></h2>
            <span class="badge theme-secondary"><?php echo $fx ?> <?php echo $BL['be_ftptakeover_available'] ?></span>
        </div>

        <div class="list-group list-group-flush">
<?php if($fx): ?>
<?php foreach($_files_action_list as $_files_action_index => $_files_action_file): ?>
            <div class="list-group-item d-flex align-items-center gap-2">

                <input class="check flex-shrink-0 ftp_mark" type="checkbox" name="ftp_mark[<?php echo $_files_action_file["f_id"] ?>]" id="ftp_mark_<?php echo $_files_action_file["f_id"] ?>" value="1" />

                <span class="flex-shrink-0 fg-secondary"><?php echo backend_icon(backend_file_icon($_files_action_file["f_ext"])) ?></span>

                <label class="flex-grow-1 text-truncate mb-0" for="ftp_mark_<?php echo $_files_action_file["f_id"] ?>"><?php echo $_files_action_file['filename'] ?></label>

                <span class="flex-shrink-0 d-flex gap-2">
                    <?php echo $_files_action_file["f_aktiv"] ? $_files_action_status['active'] : $_files_action_status['inactive'] ?>
                    <?php echo $_files_action_file["f_public"] ? $_files_action_status['public'] : $_files_action_status['private'] ?>
                </span>

                <input name="ftp_fileid[<?php echo $_files_action_index ?>]" type="hidden" value="<?php echo $_files_action_file["f_id"] ?>" />

            </div>
<?php endforeach; ?>
<?php else: ?>
            <div class="list-group-item fg-secondary"><?php echo $BL['file_actions_no'] ?></div>
<?php endif; ?>
        </div>

<?php if($fx): ?>
        <div class="card-footer d-flex flex-column md:flex-row md:align-items-center gap-3">

            <div class="form-field flex-shrink-0">
                <input class="check" name="toggle" type="checkbox" id="toggle" value="1" />
                <label for="toggle"><?php echo $BL['be_ftptakeover_all'] ?></label>
            </div>

            <button type="button" id="delete-selected-files" class="btn theme-danger btn-solid btn-styled btn-sm d-none"><?php echo $BL['be_delete_selected_files'] ?></button>

        </div>
<?php endif; ?>

    </div>

    <div class="card">

        <div class="card-header">
            <h2 class="h6 mb-0"><?php echo $BL['file_actions_step3'] ?></h2>
        </div>

        <div class="card-body">

            <div class="row g-5">

                <div class="col-12 md:col-6">
                    <label class="visually-hidden" for="file_action"><?php echo $BL['file_actions_step3'] ?></label>
                    <select name="file_action" id="file_action" class="form-control" onchange="showAction();">
                        <option value="0">- <?php echo $BL['file_actions_pdl_empty'] ?> -</option>
                        <option value="1"><?php echo $BL['file_actions_pdl_delete'] ?></option>
                        <option value="2"><?php echo $BL['file_actions_pdl_move'] ?></option>
                        <option value="3"><?php echo $BL['file_actions_pdl_status'] ?></option>
                        <option value="4"><?php echo $BL['file_actions_pdl_user'] ?></option>
                    </select>
                </div>

                <div class="col-12 md:col-6 d-none" id="div_folder">
                    <label class="form-label" for="file_newdir"><?php echo $BL['be_ftptakeover_directory'] ?></label>
                    <select name="file_newdir" id="file_newdir" class="form-control">
                        <option value="0"><?php echo $BL['be_ftptakeover_rootdir'] ?></option>
                        <?php dir_menu(0, 0, "+", $_SESSION["wcs_user_id"], "+"); ?>
                    </select>
                    <div class="form-text"><?php echo $BL['file_actions_bemfolder'] ?></div>
                </div>

                <div class="col-12 d-none" id="div_status">
                    <div class="form-label"><?php echo $BL['be_ftptakeover_status'] ?></div>
                    <div class="d-flex flex-wrap gap-5">
                        <div class="form-field">
                            <input class="check" name="file_aktiv" type="checkbox" id="file_aktiv" value="1" />
                            <label for="file_aktiv"><?php echo $BL['be_ftptakeover_active'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="file_public" type="checkbox" id="file_public" value="1" />
                            <label for="file_public"><?php echo $BL['be_ftptakeover_public'] ?></label>
                        </div>
                    </div>
                </div>

                <div class="col-12 md:col-6 d-none" id="div_user">
                    <label class="form-label" for="file_user"><?php echo $BL['login_username'] ?></label>
                    <select name="file_user" id="file_user" class="form-control">
                        <option value="">--- <?php echo $BL['be_selection'] ?> ---</option>
<?php
    $result = _dbGet('phpwcms_user', 'usr_id, usr_name, usr_aktiv', '', '', 'usr_name');
    if(isset($result[0]['usr_id'])):
        $this_uid = intval($_SESSION["wcs_user_id"]);
        foreach($result as $row):
?>
                        <option value="<?php echo $row['usr_id']; ?>"<?php if (intval($row['usr_id']) === $this_uid): ?> disabled="disabled"<?php endif; ?>>
                            <?php echo html($row['usr_name']); ?><?php if (!$row['usr_aktiv']): ?> (<?php echo $BL['be_inactive']; ?>)<?php elseif ($row['usr_aktiv'] == 9): ?> (<?php echo $BL['be_msg_del']; ?>)<?php endif; ?>
                        </option>
<?php
        endforeach;
    endif;
?>
                    </select>
                    <div class="form-text"><?php echo $BL['file_actions_bemuser'] ?></div>
                </div>

            </div>

        </div>

        <div class="card-footer d-none" id="div_button">
            <input name="Submit" type="submit" class="btn theme-primary btn-solid btn-styled btn-sm" value="<?php echo $BL['file_actions_button'] ?>" />
        </div>

    </div>

</form>
