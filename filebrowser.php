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

/**
 * Sept. 2009
 * enhancement to enable phpwcms filebrowser support in FCK Editor
 * based on concept and work of Markus Köhl <www.pagewerkstatt.ch>
 *
 * April 2011
 * - enhancement to enable phpwcms filebrowser support in CKEditor
 *   based on concept and work of Markus Köhl <www.leanux.ch>
 * - Issue 265 based on TB's post
 */

$phpwcms            = array('SESSION_START' => true);
$js_files_all       = array();
$js_files_select    = array();

require_once __DIR__ . '/include/config/conf.inc.php';
require_once __DIR__ . '/include/inc_lib/default.inc.php';
require_once PHPWCMS_ROOT . '/include/inc_lib/helper.session.php';

if( empty($_SESSION["wcs_user_lang"]) ) {

    $_SESSION = array();
    @session_destroy();
    headerRedirect(PHPWCMS_URL, 401);

} else {

    $user_lang = strtolower(substr($_SESSION["wcs_user_lang"], 0, 2));

    require PHPWCMS_ROOT.'/include/inc_lang/backend/en/lang.inc.php';
    require PHPWCMS_ROOT.'/include/inc_lang/backend/en/lang.ext.inc.php';
    $cust_lang = PHPWCMS_ROOT.'/include/inc_lang/backend/' . $user_lang . '/lang.inc.php';
    if(is_file($cust_lang)) {
        include $cust_lang;
    }
    $cust_lang = PHPWCMS_ROOT.'/include/inc_lang/backend/' . $user_lang . '/lang.ext.inc.php';
    if(is_file($cust_lang)) {
        include $cust_lang;
    }

}

$js_aktion = empty($_GET["opt"]) ? 0 : intval($_GET["opt"]);

// set target for article summary/list image
if(isset($_GET['target'])) {

    $_SESSION['filebrowser_image_target'] = $_GET['target'] === 'list' ? '_list_' :  '_';

} elseif(empty($_SESSION['filebrowser_image_target'])) {

    $_SESSION['filebrowser_image_target'] = '_';

}

if(isset($_GET['entry_id'])) {
    $_SESSION['filebrowser_image_entry_id'] = preg_replace('/[^a-z0-9_\-]/', '', $_GET['entry_id']);
}
if(isset($_GET['CKEditorFuncNum'])) {
    $_SESSION['CKEditorFuncNum'] = intval($_GET['CKEditorFuncNum']);
}

require_once PHPWCMS_ROOT.'/include/inc_lib/dbcon.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/general.inc.php';

checkLogin();

require_once PHPWCMS_ROOT.'/include/inc_lib/backend.functions.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/imagick.convert.inc.php';

$phpwcms_filestorage = PHPWCMS_FILES;

switch($js_aktion) {

    case 0:
    case 1:
    case 3:
    case 7:
    case 8:
    case 5:
    case 11:
    case 17:
        $titel      = $BL['IMAGE_TITLE'];
        $filetype   = $BL['IMAGE_FILES'];
        break;

    case 4:
    case 9:
    case 10:
    case 16:
    case 15:
    case 18:
    case 19:
        $titel      = $BL['FILE_TITLE'];
        $filetype   = $BL['FILES'];
        break;

    case 2:
    case 6:
    case 12:
    case 13:
    case 14:
        $titel      = $BL['MEDIA_TITLE'];
        $filetype   = $BL['MEDIA_FILES'];
        break;

}

$folder = array();

if(isset($_SESSION["folder"])) {
    $folder = $_SESSION["folder"];
}

if(isset($_GET["folder"])) {
    list($folder_id, $folder_value) = explode('|', $_GET["folder"]);
    $folder_value       = intval($folder_value);
    $folder[$folder_id] = $folder_value;
    $_SESSION["folder"] = $folder; // Return array with current opened folder session values
    if($folder_value) {
        $_SESSION["imgdir"] = $folder_id;
    }
}
$_SESSION["list_zaehler"] = 0;

// Which folder is active
if(isset($_GET["files"])) {

    $_SESSION["imgdir"] = intval($_GET["files"]);

} elseif(!isset($_SESSION["imgdir"])) {

    $_SESSION["imgdir"] = 0;

} elseif(isset($_SESSION["imgdir"])) {

    $_SESSION["imgdir"] = intval($_SESSION["imgdir"]);

}

// screen mode of the logged in user
$backend_theme      = (isset($_SESSION['wcs_user_theme']) && in_array($_SESSION['wcs_user_theme'], ['light', 'dark'], true)) ? $_SESSION['wcs_user_theme'] : 'auto';
$backend_theme_attr = $backend_theme === 'auto' ? '' : ' data-bs-theme="' . $backend_theme . '"';

//Does user have files and folders that can be used
$sql = "SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE f_aktiv=1 AND (f_public=1 OR f_uid=".intval($_SESSION["wcs_user_id"]).") AND f_trash=0";
$count_user_files = _dbQuery($sql, 'COUNT');

?><!DOCTYPE html>
<html lang="<?php echo $user_lang; ?>"<?php echo $backend_theme_attr; ?>>
<head>

    <title><?php echo $titel ?></title>

    <meta charset="<?php echo PHPWCMS_CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <link href="include/inc_ext/bootstrap/bootstrap.min.css" rel="stylesheet">

    <script src="include/inc_js/jquery/jquery.min.js"></script>
    <script src="include/inc_js/phpwcms.min.js?v=<?php echo @filemtime(PHPWCMS_ROOT . '/include/inc_js/phpwcms.min.js') ?>"></script>
    <script src="include/inc_js/jquery/jquery.autoSuggest.min.js"></script>
    <script src="include/inc_js/backend.upload.js"></script>
    <script type="module" src="include/inc_ext/bootstrap/bootstrap.bundle.min.js"></script>
    <script type="module" src="include/inc_js/backend.bootstrap.js"></script>
    <script type="application/json" id="backend-confirm-labels"><?php echo json_encode(
        array(
            'title'  => $BL['be_confirm_title'],
            'ok'     => $BL['be_confirm_ok'],
            'cancel' => $BL['be_confirm_cancel'],
        ),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
    ) ?></script>
    <script type="module" src="include/inc_js/backend.confirm.js"></script>
    <script type="module" src="include/inc_js/backend.tags.js"></script>

    <script>
        function addFile(obj,text,value) {
            if(obj!=null && obj.options!=null) {
                var newOpt = new Option(text, value);
                obj.options.length++;
                obj.options[obj.length-1].text  = newOpt.text;
                obj.options[obj.length-1].value = newOpt.value;
                obj.options[obj.length-1].selected = false;
            }
        }
    </script>
</head>
<body class="d-flex flex-column min-vh-100 bg-2">

<nav class="navbar bg-primary" data-bs-theme="dark">
    <div class="container-fluid gap-3">
        <span class="navbar-brand d-inline-flex align-items-center gap-3">
            <?php echo backend_icon('postcard-fill') ?>
            <span><?php echo $titel ?></span>
            <span class="badge theme-secondary"><?php echo $filetype ?></span>
        </span>
    </div>
</nav>

<main class="container-fluid p-5 flex-grow-1">

<details class="card mb-5" id="filebrowser-uploader">

    <summary class="card-header d-flex flex-row align-items-center gap-2">
        <span class="flex-shrink-0 fg-secondary"><?php echo backend_icon('upload') ?></span>
        <h2 class="h6 mb-0 flex-grow-1"><?php echo $BL['be_file_multiple_upload'] ?></h2>
    </summary>

    <div class="card-body">

        <div class="border rounded p-7 text-center mb-5" data-upload-dropzone="1">

            <p class="fg-secondary mb-3"><?php echo $BL['be_fileuploader_dragText'] ?></p>

            <input type="file" multiple class="form-control" id="upload-file-select" data-upload-input="1" data-upload-action="include/inc_act/act_upload.php?<?php echo get_token_get_string() ?>" data-upload-done="OK" data-upload-failed="<?php echo html($BL['be_error_while_save']) ?>">

        </div>

        <div class="row g-4" data-upload-fields="1">

            <input type="hidden" name="file_dir" value="<?php echo $_SESSION["imgdir"] ?>">
            <input type="hidden" name="file_aktiv" value="1">
            <input type="hidden" name="file_public" value="1">
            <input type="hidden" name="file_tags" id="file_tags" value="">

            <div class="col-12">
                <label class="form-label" for="file_longinfo"><?php echo $BL['be_ftptakeover_longinfo'] ?></label>
                <textarea cols="40" rows="3" id="file_longinfo" name="file_longinfo" class="form-control"></textarea>
            </div>

            <div class="col-12">
                <label class="form-label" for="file_copyright"><?php echo $BL['be_copyright'] ?></label>
                <input name="file_copyright" type="text" id="file_copyright" class="form-control" size="40" maxlength="255" value="">
            </div>

            <div class="col-12">
                <label class="form-label" for="file_tags_autosuggest"><?php echo $BL['be_tags'] ?></label>
                <input type="text" id="file_tags_autosuggest" class="form-control">
            </div>

        </div>

        <div class="list-group list-group-flush mt-5 d-none" data-upload-list="1"></div>

    </div>

</details>

<div class="row g-5 align-items-start">

    <div class="col-12 sm:col-5">

        <div class="card">

            <div class="card-header"><h2 class="h6 mb-0"><?php echo $BL['FOLDER_LIST'] ?></h2></div>

<?php

if(!empty($count_user_files)) { //Listing in case of user files/folders

    //Anzeige des Festplattensymbols
    $dirname = $BL['ROOT_DIR'];
    if(!isset($folder[0])) {
        $folder[0] = 0;
    }
    $folder_status = true_false($folder[0]);

    $count_sql = "SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE f_pid=0 AND f_aktiv=1 AND f_trash=0 AND (f_public=1 OR f_uid=".$_SESSION["wcs_user_id"].")";

    if(($count_wert = _dbQuery($count_sql, 'COUNT'))) {
        $count  = '<a class="flex-shrink-0 link-secondary text-decoration-none" href="filebrowser.php?opt='.$js_aktion.'&amp;folder=0';
        $count .= '%7C'.$folder_status.'" data-bs-toggle="tooltip" title="'.($folder_status ? $BL['OPEN_DIR'] : $BL['CLOSE_DIR']).': '.html($dirname).'">';
        $count .= backend_icon($folder_status ? 'chevron-right' : 'chevron-down').'</a>';
    } else {
        $count = '<span class="flex-shrink-0 invisible">' . backend_icon('chevron-right') . '</span>';
    }

    // define current directory name
    $current_dirname = $dirname;

    $dirname = '<a href="filebrowser.php?opt='.$js_aktion.'&amp;files=0" class="text-decoration-none" data-bs-toggle="tooltip" title="'.html($BL['SHOW_FILES']).'">'.$dirname.'</a>';

    echo '<div class="list-group list-group-flush">' . LF;
    echo '<div class="list-group-item d-flex align-items-center gap-2' . (empty($_SESSION["imgdir"]) ? ' bg-2' : '') . '">';
    echo $count;
    echo '<span class="flex-shrink-0 fg-secondary">' . backend_icon('folder-fill') . '</span>';
    echo '<div class="flex-grow-1 text-truncate fw-semibold">' . $dirname . '</div>';
    echo '</div>' . LF;

    //Wenn überhaupt Ordner für User vorhanden, dann Listing
    if(!$folder_status && $count_wert) {
        folder_list(0, 1, "filebrowser.php?opt=".$js_aktion."&amp;");
    }

    echo '</div>' . LF;

} else {

    echo '<div class="list-group list-group-flush"><div class="list-group-item fg-secondary">no files available</div></div>' . LF;

}

?>

        </div>

    </div>

    <div class="col-12 sm:col-7">

        <div class="card">

            <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
                <h2 class="h6 mb-0"><?php echo $filetype ?></h2>
            </div>

            <div class="list-group list-group-flush" id="filebrowser-files">

<?php

    $file_sql  = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE f_pid=".$_SESSION["imgdir"]." AND ";
    switch($js_aktion) {

        case 6:
            $file_sql .= "f_ext IN ('swf', 'mp3', 'flv', 'mp4', 'm4v', 'f4v', 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'aac', 'webp') AND ";
            break;

            // H.264
        case 12:
            $file_sql .= "f_ext IN ('mp4', 'm4p', 'mov', 'm4p', 'm4a', 'm4v', 'mp3', 'mpeg', 'aac') AND ";
            break;

            // WebM
        case 13:
            $file_sql .= "f_ext IN ('webm') AND ";
            break;

            // Ogg
        case 14:
            $file_sql .= "f_ext IN ('ogg', 'ogv', 'oga', 'ogx') AND ";
            break;

            // Typical Doc files
        case 18:
            $file_sql .= "f_ext IN ('pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'pages', 'key', 'numbers') AND ";
            // no break here
        case 15:
            $entry_id  = empty($_SESSION['filebrowser_image_entry_id']) ? '' : $_SESSION['filebrowser_image_entry_id'];
            break;

            // Custom content part file selector
        case 19:
            $allowed_ext = empty($_SESSION['filebrowser_allowed_ext']) ? array() : $_SESSION['filebrowser_allowed_ext'];
            if(!empty($_GET['allowed'])) {
                $allowed_ext = convertStringToArray(strtolower($_GET['allowed']));
                if(count($allowed_ext)) {
                    foreach($allowed_ext as $key => $ext) {
                        $allowed_ext[$key] = _dbEscape($ext);
                    }
                    $_SESSION['filebrowser_allowed_ext'] = $allowed_ext;
                }
            }
            if(count($allowed_ext)) {
                $file_sql .= 'f_ext IN (' . implode(',', $allowed_ext) . ') AND ';
            }
            $entry_id = empty($_SESSION['filebrowser_field']) ? null : $_SESSION['filebrowser_field'];
            if(!empty($_GET['field']) && ($entry_id = preg_replace('/[^a-z0-9_-]/i', '', $_GET['field']))) {
                $_SESSION['filebrowser_field'] = $entry_id;
            }
            break;

        case 11:
        case 17:
        case 8:
            $entry_id  = empty($_SESSION['filebrowser_image_entry_id']) ? '' : $_SESSION['filebrowser_image_entry_id'];
            // no break here
        case 7:
            $file_sql .= "f_ext IN ('jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'";
            if($phpwcms['image_library'] !== 'gd2') {
                $file_sql .= ", 'pdf', 'ai', 'psd', 'tif', 'tiff', 'bmp', 'eps', 'webp'";
            }
            $file_sql .= ") AND ";
            break;

        case 2:
            $default_ext  = "f_ext IN ('aif', 'aiff', 'mov', 'movie', 'mp3', 'mpeg', 'mpeg4', ";
            $default_ext .= "'mpeg2', 'wav', 'swf', 'ram', 'ra', 'wma', 'wmv', ";
            $default_ext .= "'avi', 'au', 'midi', 'moov', 'rm', 'rpm', 'mid', 'midi')";

            if(!empty($phpwcms["multimedia_ext"])) {

                $allowed_ext = convertStringToArray(strtolower($phpwcms["multimedia_ext"]));
                if(count($allowed_ext)) {
                    $default_ext = "f_ext IN ('" . implode("', '", $allowed_ext) . "')";
                }

            }

            $file_sql .= $default_ext." AND ";

            break;

    }
    $file_sql .= "f_aktiv=1 AND f_kid=1 AND f_trash=0 AND ";
    $file_sql .= "(f_public=1 OR f_uid=".$_SESSION["wcs_user_id"].") ";
    $file_sql .= "ORDER BY f_sort, f_name";

    if (empty($_SESSION['CKEditorFuncNum'])) {
        $ckeditor_action = isset($_GET['CKEditorFuncNum']) ? intval($_GET['CKEditorFuncNum']) : 0;
    } else {
        $ckeditor_action = $_SESSION['CKEditorFuncNum'];
    }

    $file_result = _dbQuery($file_sql);

    if(isset($file_result[0]['f_id'])) {

        $target_form = (empty($_SESSION['image_browser_article'])) ? 'articlecontent' : 'article';

        foreach($file_result as $file_durchlauf => $file_row) {

            $filename = html($file_row["f_name"]);

            $thumb_image = true;
            if( !$file_row['f_svg'] && !in_array($js_aktion, array(2, 4, 9, 10, 16, 18, 19)) ) {
                // check if file can have thumbnail - if so it can be choosen for usage
                $thumb_image = get_cached_image(array(
                    "target_ext"    =>  $file_row["f_ext"],
                    "image_name"    =>  $file_row["f_hash"] . '.' . $file_row["f_ext"],
                    "thumb_name"    =>  md5($file_row["f_hash"].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
                ));
            }

            if($thumb_image != false || in_array($js_aktion, array(6, 10, 12, 13, 14, 16, 18, 19))) {

                $js_files_select[$file_durchlauf] = '     [' . $file_durchlauf .', ' . $file_row["f_id"] . ', "' . $filename . '"]';
                $add_all = false;

                switch($js_aktion) {
                    case 0:
                        $jst = empty($_SESSION['filebrowser_image_target']) ? '_' : $_SESSION['filebrowser_image_target'];

                        $js  = "window.opener.document.".$target_form.".cimage".$jst."name.value='".$filename."';";
                        $js .= "window.opener.document.".$target_form.".cimage".$jst."id.value='".$file_row["f_id"]."';";
                        break;

                    case 2:
                        $js  = "window.opener.document.articlecontent.cmedia_name.value='".$filename."';";
                        $js .= "window.opener.document.articlecontent.cmedia_id.value='".$file_row["f_id"]."';";
                        break;

                    case 6:
                    case 12:
                    case 13:
                    case 14:
                        $js = "window.opener.setIdName('".$file_row["f_id"]."', '".$filename."', ".$js_aktion.");";
                        break;

                    case 19:
                        $js = "window.opener.setIdName('".$entry_id."', '".$file_row["f_id"]."', '".$filename."');tmt_winControl('self','close()');";
                        break;

                    case 18:
                    case 15:
                        $js = "window.opener.setIdName('".$entry_id."', '".$file_row["f_id"]."', '".$filename."');";
                        break;

                    case 7:
                        $js = "window.opener.setImgIdName('".$file_row["f_id"]."', '".$filename."');";
                        break;

                    case 8:
                        $js = "window.opener.setImgIdName('".$entry_id."', '".$file_row["f_id"]."', '".$filename."');";
                        break;

                    case 4:
                        $js = "addFile(window.opener.document.articlecontent.cfile_list,'".$filename."','".$file_row["f_id"]."');";
                        $js_files_all[] = $js;
                        $add_all = true;
                        break;

                    case 9:
                        $js = "window.opener.addFile('".$file_row["f_id"]."', '".$filename."');";
                        $js_files_all[] = $js;
                        $add_all = true;
                        break;

                    case 5:
                        $js = "addFile(window.opener.img_field,'".$filename."','".$file_row["f_id"]."');";
                        $js_files_all[] = $js;
                        $add_all = true;
                        break;

                    //mod
                    case 10:
                        $js  = "window.opener.SetUrl('download.php?f=".$file_row["f_hash"] . "');";
                        break;

                    case 11:
                        $js  = "window.opener.SetUrl('".PHPWCMS_RESIZE_IMAGE."/".$phpwcms['img_prev_width']."x".$phpwcms['img_prev_height']."/" . $file_row["f_hash"] . '.' . $file_row["f_ext"] . "');";
                        break;

                     //TinyMCE / CKEditor
                     case 16:
                         $js  = "if(window.opener && window.opener.activeTinyMceCallback){window.opener.activeTinyMceCallback('download.php?f=" . $file_row["f_hash"] . "');window.close();}else{window.opener.CKEDITOR.tools.callFunction(".$ckeditor_action.", 'download.php?f=" . $file_row["f_hash"] . "');}";
                         break;

                     case 17:
                         $resize_url = PHPWCMS_RESIZE_IMAGE."/".$phpwcms['img_prev_width']."x".$phpwcms['img_prev_height']."/" . $file_row["f_hash"] . '.' . $file_row["f_ext"];
                         $js  = "if(window.opener && window.opener.activeTinyMceCallback){window.opener.activeTinyMceCallback('".$resize_url."');window.close();}else{window.opener.CKEDITOR.tools.callFunction(".$ckeditor_action.", '".$resize_url."');}";
                         break;

                    default:
                        $js = "addFile(window.opener.document.articlecontent.cimage_list,'".$filename."','".$file_row["f_id"]."');";
                        $js_files_all[] = $js;
                        $add_all = true;
                }

                // show "add all files"
                if($file_durchlauf === 0 && $add_all) {

                    echo '<div class="list-group-item" id="addAllFilesLink">';
                    echo '<a class="btn btn-outline theme-secondary btn-sm" href="#" onclick="addAllFiles();return false;">';
                    echo backend_icon('plus') . ' ' . $BL['ADD_ALL_FILES'];
                    echo '</a></div>' . LF;

                }

                echo '<div class="list-group-item">' . LF;
                echo '<div class="d-flex align-items-center gap-2">' . LF;
                echo '<span class="flex-shrink-0 fg-secondary">' . backend_icon(backend_file_icon($file_row["f_ext"])) . '</span>';

                if($js_aktion != 4 && $js_aktion != 10 && $js_aktion != 16) {
                    echo '<div class="flex-grow-1 text-truncate"><span class="text-truncate d-block">' . $filename . '</span></div>';
                    echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">' . backend_action('#', 'plus', $BL['TAKE_IMAGE'], 'normal', ' onclick="'.$js.'return false;"') . '</div>';
                } else {
                    echo '<div class="flex-grow-1 text-truncate"><a href="#" class="text-decoration-none" onclick="'.$js."tmt_winControl('self','close()');".'">'.$filename.'</a></div>';
                    echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">' . backend_action('#', 'plus', $BL['TAKE_IMAGE'], 'normal', ' onclick="'.$js.'return false;"') . '</div>';
                }

                echo '</div>' . LF;

                if((!empty($thumb_image[0]) || $file_row['f_svg']) && in_array( $js_aktion, array(0, 1, 3, 5, 6, 7, 8, 10, 11, 17, 18, 19) ) ) {
                    echo '<div class="mt-3"><a href="#" onclick="'.$js;
                    echo "tmt_winControl('self','close()');\">";
                    if($file_row['f_svg']) {
                        echo '<img src="'.PHPWCMS_RESIZE_IMAGE.'/'.$phpwcms["img_list_width"].'x'.$phpwcms["img_list_height"].'/'.$file_row['f_hash'].'.'.$file_row['f_ext'].'" class="rounded mw-100" alt="'.$filename.'">';
                    } else {
                        echo '<img src="'.PHPWCMS_IMAGES . $thumb_image[0] .'" class="rounded mw-100" alt="'.$filename.'">';
                    }
                    echo '</a></div>' . LF;
                }

                echo '</div>' . LF;

            }

        }
        if(empty($filename)) { //Abschluss der Filelisten-Tabelle
            echo '<div class="list-group-item fg-secondary">' . $BL['NO_FILE'] . '</div>' . LF;
        }

    } else {

        echo '<div class="list-group-item fg-secondary">' . $BL['NO_FILE'] . '</div>' . LF;

    }

?>

            </div>

        </div>

    </div>

</div>

</main>

<?php

    if( count($js_files_select) ) {

        echo LF . '<script>';
        echo LF . SCRIPT_CDATA_START . LF;

        echo 'var files_all = new Array(' . LF;
        echo implode(','.LF, $js_files_select);
        echo LF . ' );';
        echo LF . 'var files_total = ' . $file_durchlauf . ';';

        echo LF . LF;
        echo 'function addAllFiles() {' . LF;
        echo implode(LF, $js_files_all) . LF;
        echo ' var addAllFilesLink = document.getElementById("addAllFilesLink");' . LF;
        echo ' if(addAllFilesLink) { addAllFilesLink.classList.add("d-none"); }' . LF;
        $confirm = str_replace('{VAL}', $current_dirname, $BL['ADD_ALL_CONFIRM']);
        // the message carries JavaScript \n escapes, the dialog splits at real line breaks
        $confirm = str_replace(array('\\r\\n', '\\n'), array("\n", "\n"), $confirm);
        if(PHPWCMS_CHARSET !== 'utf-8') {
            $confirm = mb_convert_encoding($confirm, PHPWCMS_CHARSET);
        }
        echo ' phpwcmsConfirm(\'' . js_singlequote($confirm) . '\').then(function(confirmed) {' . LF;
        echo '  if(confirmed) { tmt_winControl("self", "close()"); }' . LF;
        echo ' });' . LF;
        echo '}' . LF;

        echo SCRIPT_CDATA_END;
        echo LF . '</script>' . LF;

    }

?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // the tag field keeps the values the upload sends
    var tagValues = document.getElementById('as-values-keyword-autosuggest');
    var tagField = document.getElementById('file_tags');

    function syncTags() {
        if (tagValues && tagField) {
            tagField.value = tagValues.value;
        }
    }

    document.addEventListener('change', syncTags, true);
    document.addEventListener('click', syncTags, true);
    document.addEventListener('keyup', syncTags, true);

    syncTags();

    jQuery(function ($) {

        $("#file_tags_autosuggest").autoSuggest('<?php echo PHPWCMS_URL ?>include/inc_act/ajax_connector.php', {
            selectedItemProp: "cat_name",
            selectedValuesProp: 'cat_name',
            searchObjProps: "cat_name",
            queryParam: 'value',
            extraParams: '&method=json&action=category',
            startText: '',
            neverSubmit: true,
            asHtmlID: 'keyword-autosuggest'
        });

    });

});
</script>
</body>
</html>
<?php

function folder_list($pid, $level, $zieldatei) {

    global $current_dirname, $BL;

    $folder = $_SESSION["folder"];
    $pid = intval($pid);
    $userID = intval($_SESSION["wcs_user_id"]);
    $sql = "SELECT f_id, f_name, f_aktiv, f_public FROM ".DB_PREPEND."phpwcms_file WHERE ".
           "f_pid=".intval($pid)." AND f_aktiv=1 AND f_kid=0 AND f_trash=0 AND ".
           "(f_public=1 OR f_uid=".$userID.") ORDER BY f_sort, f_name";

    $result = _dbQuery($sql);

    if(isset($result[0]['f_id'])) {
        foreach($result as $row) {

            $dirname = html($row["f_name"]);

            //Ermitteln des Aufolderwertes
            if(empty($folder[$row["f_id"]])) {
                $folder[ $row["f_id"] ] = 0;
            }
            $folder_status = true_false($folder[ $row["f_id"] ]);

            //Ermitteln, ob überhaupt abhängige Dateien/Ordner existieren
            $count_sql = "SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE f_pid=".$row["f_id"]." AND f_trash=0 AND f_aktiv=1 AND (f_public=1 OR f_uid=".$userID.")";

            if(($count_wert = _dbQuery($count_sql, 'COUNT'))) {
                $count  = '<a class="flex-shrink-0 link-secondary text-decoration-none" href="'.$zieldatei."folder=".$row["f_id"];
                $count .= '%7C'.$folder_status.'" data-bs-toggle="tooltip" title="'.($folder_status ? $BL['OPEN_DIR'] : $BL['CLOSE_DIR']).': '.$dirname.'">';
                $count .= backend_icon($folder_status ? 'chevron-right' : 'chevron-down').'</a>';
            } else {
                $count = '<span class="flex-shrink-0 invisible">' . backend_icon('chevron-right') . '</span>';
            }

            $dirname = '<a href="'.$zieldatei."files=".$row["f_id"].'" class="text-decoration-none" data-bs-toggle="tooltip" title="'.$BL['SHOW_FILES1'].'">'. $dirname . '</a>';

            $current = $row["f_id"] == $_SESSION["imgdir"];
            if($current) {
                $current_dirname = $row["f_name"];
            }

            echo '<div class="list-group-item d-flex align-items-center gap-2' . folder_indent($level) . ($current ? ' bg-2' : '') . '">';
            echo $count;
            echo '<span class="flex-shrink-0 fg-secondary">' . backend_icon('folder-fill') . '</span>';
            echo '<div class="flex-grow-1 text-truncate fw-semibold">' . $dirname . '</div>';
            echo '</div>' . LF;

            if(!$folder_status && $count_wert) {
                folder_list($row["f_id"], $level + 1, $zieldatei);
            }

            $_SESSION["list_zaehler"]++;
        }
    }
}

function folder_indent($level) {

    // a nested folder row keeps its full width and its divider, only the content
    // moves in by one step per level - a margin on the row would also indent the
    // separators and the row background
    $steps = array('', ' ps-9', ' ps-12');

    return $steps[intval($level)] ?? ' ps-12';

}

function true_false($wert) {
    // Swap true / false
    return intval($wert) ? 0 : 1;
}
