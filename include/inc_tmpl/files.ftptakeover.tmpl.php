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


// If there are files marked to be deleted
$deleteFiles = array();

if(isset($_POST['ftp_mark']) && is_array($_POST['ftp_mark']) && count($_POST['ftp_mark'])) {
    foreach($_POST['ftp_mark'] as $key => $value) {
        $deleteFiles[$_POST['ftp_file'][$key]] = $_POST['ftp_filename'][$key];
    }
}

// file information is edited per language, the default language has no suffix
$_upload_panes = array($phpwcms['default_lang'] => '');

foreach($phpwcms['allowed_lang'] as $_upload_lang) {

    $_upload_lang = strtolower($_upload_lang);

    if($_upload_lang === $phpwcms['default_lang']) {
        continue;
    }

    $_upload_panes[$_upload_lang] = '_' . $_upload_lang;

}

// available files of the FTP takeover directory
$multiple_upload_files = returnFileListAsArray(PHPWCMS_ROOT . $phpwcms["ftp_path"], $extfilter = '');

$_upload_files  = array();
$_upload_size   = 0;

if (is_array($multiple_upload_files) && count($multiple_upload_files)) {

    ksort($multiple_upload_files);

    foreach ($multiple_upload_files as $file) {

        // test if the file should be deleted
        $file_base64 = base64_encode($file['filename']);

        if(isset($deleteFiles[$file_base64]) && @unlink($file['path'])) {
            continue;
        }

        // there is a big problem with special chars on Mac OS X and seems Windows too
        $filename = PHPWCMS_CHARSET !== 'utf-8' && phpwcms_seems_utf8($file['filename']) ? str_replace('?', '', mb_convert_encoding($file['filename'], PHPWCMS_CHARSET)) : $file['filename'];

        $_upload_files[] = array(
            'filename' => html($filename),
            'base64'   => $file_base64,
            'ext'      => $file['ext'],
            'filesize' => $file['filesize'],
        );

        $_upload_size += $file['filesize'];

    }

}

$fx = count($_upload_files);

?>
<div class="card mb-5">

    <div class="card-header"><h2 class="h6 mb-0"><?php echo $BL['be_file_multiple_upload'] ?></h2></div>

    <div class="card-body">

        <div class="border rounded p-7 text-center" data-upload-dropzone="1">

            <p class="fg-secondary mb-3"><?php echo $BL['be_fileuploader_dragText'] ?></p>

            <input type="file" multiple class="form-control" id="upload-file-select" data-upload-input="1" data-upload-action="include/inc_act/act_upload.php?<?php echo get_token_get_string() ?>" data-upload-done="OK" data-upload-failed="<?php echo html($BL['be_error_while_save']) ?>">

        </div>

        <div class="list-group list-group-flush mt-5 d-none" data-upload-list="1"></div>

    </div>

</div>

<form action="include/inc_act/act_ftptakeover.php" method="post" name="ftptakeover" id="ftptakeover">

<div class="card mb-5">

    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">

        <h2 class="h6 mb-0"><?php echo $BL['be_files_select_available'] ?></h2>

        <span class="badge theme-secondary"><?php echo $BL['be_ftptakeover_size'] ?>: <?php echo fsizelong($_upload_size) ?></span>

    </div>

    <div class="list-group list-group-flush">
<?php if($fx): ?>
<?php foreach($_upload_files as $fx_key => $file): ?>
        <div class="list-group-item d-flex align-items-center gap-2">

            <input class="check flex-shrink-0 ftp_mark" type="checkbox" name="ftp_mark[<?php echo $fx_key ?>]" id="ftp_mark_<?php echo $fx_key ?>" value="1">

            <span class="flex-shrink-0 fg-secondary"><?php echo backend_icon(backend_file_icon($file['ext'])) ?></span>

            <div class="flex-grow-1 text-truncate">
                <label class="d-block text-truncate mb-0" for="ftp_mark_<?php echo $fx_key ?>"><?php echo $file['filename'] ?></label>
                <div class="small fg-secondary"><?php echo fsizelong($file['filesize']) ?></div>
            </div>

            <input name="ftp_file[<?php echo $fx_key ?>]" type="hidden" value="<?php echo $file['base64'] ?>">
            <input name="ftp_filename[<?php echo $fx_key ?>]" type="hidden" value="<?php echo $file['filename'] ?>">

        </div>
<?php endforeach; ?>
<?php else: ?>
        <div class="list-group-item fg-secondary"><?php echo $BL['be_ftptakeover_nofile'] ?></div>
<?php endif; ?>
    </div>

<?php if($fx): ?>
    <div class="card-footer d-flex flex-column md:flex-row md:align-items-center gap-3">

        <div class="form-field flex-shrink-0">
            <input class="check" type="checkbox" name="toggle" id="toggle" value="1">
            <label for="toggle"><?php echo $BL['be_ftptakeover_all'] ?></label>
        </div>

        <button type="button" id="delete-selected-files" class="btn theme-danger btn-solid btn-styled btn-sm d-none"><?php echo $BL['be_delete_selected_files'] ?></button>

        <span class="badge theme-secondary ms-auto"><?php echo fsizelong($_upload_size) ?></span>

    </div>
<?php endif; ?>

</div>

<div class="card mb-5">

    <div class="card-body row g-5">

        <div class="col-12 md:col-6">
            <label class="form-label" for="file_dir"><?php echo $BL['be_ftptakeover_directory'] ?></label>
            <select name="file_dir" id="file_dir" class="form-control">
                <option value="0"><?php echo $BL['be_ftptakeover_rootdir'] ?></option>
                <?php dir_menu(0, 0, "-", $_SESSION["wcs_user_id"], "-"); ?>
            </select>
        </div>

        <div class="col-12 md:col-6">
            <label class="form-label" for="file_dir_new"><?php echo $BL['be_ftptakeover_new_folder'] ?></label>
            <input type="text" name="file_dir_new" id="file_dir_new" class="form-control" placeholder="<?php echo $BL['be_ftptakeover_new_folder_placeholder'] ?>">
        </div>

        <div class="col-12">
            <div class="form-field">
                <input class="check" type="checkbox" name="file_iptc_as_caption" id="file_iptc_as_caption" value="1"<?php if(!empty($phpwcms['iptc_as_caption'])): ?> checked<?php endif; ?>>
                <label for="file_iptc_as_caption"><?php echo $BL['be_iptc_as_caption'] ?></label>
            </div>
            <div id="iptc-info"></div>
        </div>

    </div>

</div>

<div class="card mb-5">

<?php if(count($_upload_panes) > 1) { ?>
    <div class="card-header">
        <div class="nav nav-pills flex-wrap" data-upload-lang-tabs="1">
<?php

    $_upload_first = true;

    foreach($_upload_panes as $_upload_lang => $_upload_suffix) {

        echo '<a href="#" class="nav-link' . ($_upload_first ? ' active' : '') . '" data-upload-lang="' . $_upload_lang . '"';
        echo $_upload_first ? ' aria-current="page"' : '';
        echo '>' . ($_upload_lang === $phpwcms['default_lang'] ? $BL['be_admin_tmpl_default'] : strtoupper($_upload_lang)) . '</a>';

        $_upload_first = false;

    }

?>
        </div>
    </div>
<?php } ?>

<?php

$_upload_first = true;

foreach($_upload_panes as $_upload_lang => $_upload_suffix) {

?>
    <div class="card-body<?php echo $_upload_first ? '' : ' d-none' ?>" data-upload-lang-pane="<?php echo $_upload_lang ?>">

        <div class="row g-5">

            <div class="col-12">
                <label class="form-label" for="file_title<?php echo $_upload_suffix ?>"><?php echo $BL['be_attr_title'] ?></label>
                <input type="text" name="file_title<?php echo $_upload_suffix ?>" id="file_title<?php echo $_upload_suffix ?>" class="form-control" maxlength="1000" value="">
            </div>

            <div class="col-12">
                <label class="form-label" for="file_longinfo<?php echo $_upload_suffix ?>"><?php echo $BL['be_cnt_description'] ?></label>
                <textarea name="file_longinfo<?php echo $_upload_suffix ?>" id="file_longinfo<?php echo $_upload_suffix ?>" class="form-control autosize" rows="4"></textarea>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="file_copyright<?php echo $_upload_suffix ?>"><?php echo $BL['be_copyright'] ?></label>
                <input type="text" name="file_copyright<?php echo $_upload_suffix ?>" id="file_copyright<?php echo $_upload_suffix ?>" class="form-control" maxlength="1000" value="">
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="file_alt<?php echo $_upload_suffix ?>"><?php echo $BL['be_attr_alt'] ?></label>
                <input type="text" name="file_alt<?php echo $_upload_suffix ?>" id="file_alt<?php echo $_upload_suffix ?>" class="form-control" maxlength="1000" value="">
            </div>

        </div>

    </div>
<?php

    $_upload_first = false;

}

?>

</div>

<div class="card mb-5">

    <div class="card-body row g-5">

<?php

    //Auswahlliste vordefinierte Keywörter
    $sql = "SELECT * FROM ".DB_PREPEND."phpwcms_filecat WHERE fcat_deleted=0 ORDER BY fcat_sort, fcat_name";
    $result = _dbQuery($sql);

    if(isset($result[0]['fcat_id'])) {

        foreach($result as $row) {

            if(!get_filecat_childcount($row["fcat_id"])) {
                continue;
            }

?>
        <div class="col-12 md:col-6">
            <label class="form-label" for="file_keywords_<?php echo $row["fcat_id"] ?>"><?php echo html($row["fcat_name"]) ?></label>
            <select name="file_keywords[<?php echo $row["fcat_id"] ?>]" id="file_keywords_<?php echo $row["fcat_id"] ?>" class="form-control">
                <option value="<?php echo $row["fcat_needed"] ? "0_".$row["fcat_needed"] : "0" ?>"><?php echo $row["fcat_needed"] ? $BL['be_ftptakeover_needed'] : $BL['be_ftptakeover_optional'] ?></option>
<?php

            $ksql = "SELECT * FROM ".DB_PREPEND."phpwcms_filekey WHERE fkey_deleted=0 AND fkey_cid=".$row["fcat_id"]." ORDER BY fkey_name";
            $kresult = _dbQuery($ksql);

            if(isset($kresult[0]['fkey_id'])) {
                foreach($kresult as $krow) {
                    echo '<option value="'.$krow["fkey_id"].'">'.html($krow["fkey_name"]).'</option>';
                }
            }

?>
            </select>
        </div>
<?php

        }

    }

    //Ende vordefinierte Keywörter

?>
        <div class="col-12">
            <label class="form-label" for="file_shortinfo"><?php echo $BL['be_ftptakeover_additional'] ?></label>
            <input type="text" name="file_shortinfo" id="file_shortinfo" class="form-control" maxlength="250" value="">
        </div>

        <div class="col-12">
            <label class="form-label" for="file_tags_autosuggest"><?php echo $BL['be_tags'] ?></label>
            <input type="text" id="file_tags_autosuggest" class="form-control">
            <input name="file_tags" type="hidden" id="file_tags" value="">
        </div>

    </div>

</div>

<div class="card">

    <div class="card-body d-flex flex-column gap-5">

        <div class="d-flex flex-wrap gap-5">

            <div class="form-field">
                <input class="check" type="checkbox" name="file_aktiv" id="file_aktiv" value="1"<?php is_checked($phpwcms['set_file_active'], 1) ?>>
                <label for="file_aktiv"><?php echo $BL['be_ftptakeover_active'] ?></label>
            </div>

            <div class="form-field">
                <input class="check" type="checkbox" name="file_public" id="file_public" value="1"<?php is_checked($phpwcms['set_file_active'], 1) ?>>
                <label for="file_public"><?php echo $BL['be_ftptakeover_public'] ?></label>
            </div>

            <div class="form-field">
                <input class="check" type="checkbox" name="file_replace" id="file_replace" value="1">
                <label for="file_replace"><?php echo $BL['be_file_replace'] ?></label>
            </div>

        </div>

        <div class="d-flex align-items-center gap-3">
            <input name="file_aktion" type="hidden" id="file_aktion" value="1">
            <button type="submit" name="Submit" value="<?php echo $BL['be_ftptakeover_button'] ?>" class="btn theme-primary btn-solid btn-styled btn-sm"><?php echo $BL['be_ftptakeover_button'] ?></button>
        </div>

    </div>

</div>

</form>
<?php

initJsAutocompleter();

$GLOBALS['BE']['HEADER']['fileupload.js'] = getJavaScriptSourceLink('include/inc_js/backend.upload.js');

?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    var form = document.getElementById('ftptakeover');

    if (!form) {
        return;
    }

    var toggle = document.getElementById('toggle');
    var markers = form.querySelectorAll('input.ftp_mark');
    var deleteButton = document.getElementById('delete-selected-files');

    function refreshSelection() {

        var checked = form.querySelectorAll('input.ftp_mark:checked').length;

        if (deleteButton) {
            deleteButton.classList.toggle('d-none', !checked);
        }

        if (!checked && toggle) {
            toggle.checked = false;
        }

    }

    if (toggle) {
        toggle.addEventListener('change', function () {
            Array.prototype.forEach.call(markers, function (marker) {
                marker.checked = toggle.checked;
            });
            refreshSelection();
        });
    }

    Array.prototype.forEach.call(markers, function (marker) {
        marker.addEventListener('change', refreshSelection);
    });

    if (deleteButton) {
        deleteButton.addEventListener('click', function () {
            if (confirm('<?php echo str_replace("'", "\\'", html_entity_decode($BL['be_delete_selected_files_confirm'], ENT_QUOTES, PHPWCMS_CHARSET)) ?>')) {
                form.action = 'phpwcms.php?<?php echo get_token_get_string() ?>&do=files&p=8';
                form.submit();
            }
        });
    }

    form.addEventListener('submit', function (event) {

        if (!form.querySelectorAll('input.ftp_mark:checked').length) {
            event.preventDefault();
            return;
        }

        if (window.jQuery && jQuery('#as-values-keyword-autosuggest').length) {
            jQuery('#file_tags').val(jQuery('#as-values-keyword-autosuggest').val());
        }

    });

    // language tabs of the file information
    var langTabs = document.querySelector('[data-upload-lang-tabs]');

    if (langTabs) {
        langTabs.querySelectorAll('[data-upload-lang]').forEach(function (tab) {
            tab.addEventListener('click', function (event) {

                var lang = tab.getAttribute('data-upload-lang');

                event.preventDefault();

                langTabs.querySelectorAll('[data-upload-lang]').forEach(function (item) {
                    var selected = item === tab;
                    item.classList.toggle('active', selected);
                    if (selected) {
                        item.setAttribute('aria-current', 'page');
                    } else {
                        item.removeAttribute('aria-current');
                    }
                });

                form.querySelectorAll('[data-upload-lang-pane]').forEach(function (pane) {
                    pane.classList.toggle('d-none', pane.getAttribute('data-upload-lang-pane') !== lang);
                });

            });
        });
    }

});
</script>
