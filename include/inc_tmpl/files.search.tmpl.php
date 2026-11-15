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

// keep existing search data - outdated but seems to be enough
if(!isset($_POST["file_search"]) && isset($_SESSION['file_search_query'])) {
    $_POST = $_SESSION['file_search_query'];
}

//Search
if(isset($_POST["file_search"])) {

    $_POST["file_search"] = clean_slweg($_POST["file_search"]);

    $_SESSION['file_search_query'] = array(
        "file_search" => $_POST["file_search"],
        "file_andor" => $_POST["file_andor"],
        "file_which" => $_POST["file_which"]
    );

    $search_string  = explode(" ", $_POST["file_search"]);
    if(count($search_string)) {
        foreach($search_string as $key => $value) {
            if(($value = trim($value))) {
                $search["key"][$key] = $value;
            }
        }
        unset($search_string);
        if(isset($search["key"]) && sizeof($search["key"])) {
        //check for AND or OR
            $search["andor"] = (intval($_POST["file_andor"])) ? 1 : 0;
            $search["which"] = intval($_POST["file_which"]);
            switch($search["which"]) {
                case 0: $search["which"]="f_uid=".$_SESSION["wcs_user_id"]; break;
                case 1: $search["which"]="f_public=1"; break;
                default: $search["which"]="(f_public=1 OR f_uid=".$_SESSION["wcs_user_id"].")"; break;
            }

            $file_key = get_list_of_file_keywords(); //Auslesen der File Schlüsselwörter

            //Aufbau des eigentlichen Suchstrings
            $sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE f_aktiv=1 AND f_trash=0 AND f_kid=1 AND ".$search["which"]; //ob public oder private order keine Angabe
            $result = _dbQuery($sql);
            if(isset($result[0]['f_id'])) {
                foreach($result as $row) {
                    $search["string"]  = $row["f_name"]." ".$row["f_shortinfo"]." ".$row["f_longinfo"];
                    $search["string"]  = str_replace(array("\r\n", "\n"), " ", $search["string"]);
                    $search["string"] .= add_keywords_to_search ($file_key, $row["f_keywords"]); //fügt freie Keywords zum Suchstring hinzu

                    foreach($search["key"] as $value) {
                        if(preg_match("/".preg_quote($value,"/")."/i", $search["string"])) {
                            if($search["andor"]) {
                                if(!isset($search["result"][$row["f_id"]])) {
                                    $search["result"][$row["f_id"]] = 1;
                                } else {
                                    $search["result"][$row["f_id"]]++;
                                }
                            } else {
                                $search["result"][$row["f_id"]] = 1; //OR clause
                            }
                        }
                    }
                }
                if(isset($search["result"]) && sizeof($search["result"]) && $search["andor"]) {
                    //Prüfen, ob die AND bedingung erfüllt ist
                    //gilt nur, wenn Anzahl Suchworte = Anzahl Funde im String
                    $search["count_key"] = sizeof($search["key"]);
                    foreach($search["result"] as $key => $value) {
                        if($search["count_key"] != $value) unset($search["result"][$key]);
                    }
                }
            }
        } else {
            $search["error"][1] = $BL['be_fsearch_err1'];
        }
    } else {
        $search["error"][1] = $BL['be_fsearch_err1'];
    }
}

?>
<form action="phpwcms.php?do=files&amp;f=3" method="post" enctype="multipart/form-data" name="searchfile" id="searchfile">
<div class="card mb-5">
    <div class="card-header">
        <h2 class="h6 mb-0"><?php echo $BL['be_fsearch_title'] ?></h2>
    </div>
    <div class="card-body">
        <p class="small fg-secondary"><?php echo str_replace('<br />', ' ', $BL['be_fsearch_infotext']) ?></p>
<?php if(isset($search["error"])) { ?>
        <div class="alert theme-danger">
<?php   $zz = 0;
        foreach($search["error"] as $value) {
            if($zz) {
                echo '<br>';
            }
            echo html($value);
            $zz++;
        }
?>
        </div>
<?php }
if(!isset($search["string"]) && !isset($search["result"])) { ?>
        <p class="form-text"><?php echo $BL['be_fsearch_fillin'] ?></p>
<?php } ?>
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label" for="file_search"><?php echo $BL['be_fsearch_searchlabel'] ?></label>
                <input name="file_search" type="search" class="form-control" id="file_search" value="<?php
                if(!empty($_SESSION['file_search_query']["file_search"])) {
                    echo html($_SESSION['file_search_query']["file_search"]);
                }
            ?>" maxlength="250" autofocus>
            </div>
<?php

$s1 = $_POST["file_andor"] ?? 1;
$s2 = $_POST["file_which"] ?? 2;

?>
            <div class="col-12 md:col-6">
                <select name="file_andor" id="file_andor" class="form-control" aria-label="<?php echo $BL['be_fsearch_and'] . ' / ' . $BL['be_fsearch_or'] ?>">
                    <option value="1" <?php is_selected("1", $s1) ?>><?php echo $BL['be_fsearch_and'] ?></option>
                    <option value="0" <?php is_selected("0", $s1) ?>><?php echo $BL['be_fsearch_or'] ?></option>
                </select>
            </div>
            <div class="col-12 md:col-6">
                <select name="file_which" id="file_which" class="form-control" aria-label="<?php echo $BL['be_fsearch_personal'] . ' / ' . $BL['be_fsearch_public'] ?>">
                    <option value="2" <?php is_selected("2", $s2) ?>><?php echo $BL['be_fsearch_all'] ?></option>
                    <option value="0" <?php is_selected("0", $s2) ?>><?php echo $BL['be_fsearch_personal'] ?></option>
                    <option value="1" <?php is_selected("1", $s2) ?>><?php echo $BL['be_fsearch_public'] ?></option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
        <button type="submit" name="submit" value="1" class="btn theme-primary btn-solid btn-styled btn-sm"><?php echo $BL['be_fsearch_startsearch'] ?></button>
    </div>
</div>
</form>
<?php

if(isset($search["result"])) {

    $search["filelist"] = " ";
    $sl = 0;

    foreach($search["result"] as $key => $value) {
        if($sl) {
            $search["filelist"] .= " OR ";
        }
        $search["filelist"] .= "f_id=".intval($key);
        $sl++;
    }

    //Listing der gefundenen Dateien
    $file_sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE (" . (trim($search["filelist"]) ? $search["filelist"] : 0) . ") AND f_kid=1 AND f_trash=0 ORDER BY f_name";
    $file_result = _dbQuery($file_sql);

    echo '<div class="card mb-5">' . LF;
    echo '<div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">' . LF;
    echo '<h2 class="h6 mb-0">'.$BL['be_fsearch_title'].'</h2>' . LF;
    echo '<span class="badge theme-secondary">'.(isset($file_result[0]['f_id']) ? count($file_result) : 0).'</span>' . LF;
    echo '</div>' . LF;

    if(isset($file_result[0]['f_id'])) {

        echo '<div class="list-group list-group-flush">' . LF;

        foreach($file_result as $file_row) {

            $filename = html($file_row["f_name"]);
            $can_edit = !(empty($_SESSION["wcs_user_admin"]) && $file_row["f_uid"] != $_SESSION["wcs_user_id"]);

            $edit_url = 'phpwcms.php?do=files&amp;f=0&amp;editfile='.$file_row["f_id"];
            $info_url = 'fileinfo.php?public&amp;fid='.$file_row["f_id"];
            $info_js  = " onclick=\"flevPopupLink(this.href,'filedetail','scrollbars=yes,resizable=yes,width=500,height=400',1);return document.MM_returnValue;\"";

            if($can_edit) {
                $file_link = '<a href="'.$edit_url.'" class="text-decoration-none" title="'.$BL['be_fprivfunc_editfile'].': '.$filename.'">';
            } else {
                $file_link = '<a href="'.$info_url.'" target="_blank" class="text-decoration-none" title="'.$filename.'"'.$info_js.'>';
            }

            $meta = strtoupper(html($file_row["f_ext"]));
            if(!empty($file_row["f_size"])) {
                $meta .= ' &middot; '.fsize($file_row["f_size"]);
            }

            // thumbnail preview, if the user has it enabled and the file is an image
            $thumb = '';
            if(!empty($_SESSION["wcs_user_thumb"]) && in_array(strtolower($file_row["f_ext"]), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'avif', 'heic', 'ico', 'svg'), true)) {
                if(empty($file_row["f_svg"])) {
                    $thumb_image = get_cached_image(array(
                        "target_ext" => $file_row["f_ext"],
                        "image_name" => $file_row["f_hash"] . '.' . $file_row["f_ext"],
                        "thumb_name" => md5($file_row["f_hash"].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
                    ));
                    if($thumb_image != false) {
                        $thumb = '<img src="'.$thumb_image['src'].'" class="rounded mw-100" alt="'.$filename.'">';
                    }
                } else {
                    $thumb = '<img src="'.PHPWCMS_RESIZE_IMAGE.'/'.$phpwcms["img_list_width"].'x'.$phpwcms["img_list_height"].'/'.$file_row["f_hash"].'.'.$file_row["f_ext"].'" class="rounded mw-100" alt="'.$filename.'">';
                }
                if($thumb) {
                    $thumb = '<div class="mt-3">'.$file_link.$thumb.'</a></div>';
                }
            }

            echo '<div class="list-group-item d-flex align-items-center gap-2">';
            echo '<span class="flex-shrink-0 invisible">'.backend_icon('chevron-right').'</span>';
            echo '<span class="flex-shrink-0 fg-secondary">'.backend_icon(backend_file_icon($file_row["f_ext"])).'</span>';

            echo '<div class="flex-grow-1 text-truncate">';
            echo '<div class="text-truncate">'.$file_link.$filename.'</a></div>';
            echo '<div class="small fg-secondary text-truncate">'.$meta.'</div>';
            echo $thumb;
            echo '</div>';

            echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">';
            if($can_edit) {
                echo backend_action($edit_url, 'pen', $BL['be_fprivfunc_editfile'].': '.$filename);
            }
            echo '<a class="btn btn-icon btn-outline theme-secondary btn-sm" href="include/inc_act/act_download.php?pl=1&amp;dl='.$file_row["f_id"].'" target="_blank" title="'.$BL['be_fprivfunc_dlfile'].': '.$filename.'" aria-label="'.$BL['be_fprivfunc_dlfile'].': '.$filename.'">'.backend_icon('download').'</a>';
            echo '</div>';

            echo '</div>' . LF;

        }

        echo '</div>' . LF;

    } else {

        echo '<div class="card-body">' . LF;
        echo '<div class="alert theme-warning mb-0">'.$BL['be_fsearch_nonfound'].'</div>' . LF;
        echo '</div>' . LF;

    }

    echo '</div>' . LF;

} elseif(isset($search["string"])) { //kein gültiges Suchergebnis

    echo '<div class="card mb-5">' . LF;
    echo '<div class="card-body">' . LF;
    echo '<div class="alert theme-warning mb-0">'.$BL['be_fsearch_nonfound'].'</div>' . LF;
    echo '</div>' . LF;
    echo '</div>' . LF;

}
