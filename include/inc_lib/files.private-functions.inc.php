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

//Funktionen zum Listen der privaten Dateien

/**
 * Single row of the private file listing
 */
function backend_file_item($file_row, $zieldatei, $cutID, $phpwcms) {

    $BL = $GLOBALS['BL'];

    $filename    = html($file_row['f_name']);
    $filename_js = html(str_replace("'", "\\'", $file_row['f_name']), false);
    $edit_url    = $zieldatei . '&amp;editfile=' . $file_row['f_id'];

    $meta = strtoupper(html($file_row['f_ext']));
    if(!empty($file_row['f_size'])) {
        $meta .= ' &middot; ' . fsize($file_row['f_size']);
    }
    if(!empty($file_row['f_copyright'])) {
        $meta .= ' &middot; &copy; ' . html($file_row['f_copyright']);
    }

    // thumbnail preview, if the user has it enabled and the file is an image
    $thumb = '';
    if(!empty($_SESSION['wcs_user_thumb']) && in_array(strtolower($file_row['f_ext']), array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'avif', 'heic', 'ico', 'svg'), true)) {
        if(empty($file_row['f_svg'])) {
            $thumb_image = get_cached_image(array(
                'target_ext' => $file_row['f_ext'],
                'image_name' => $file_row['f_hash'] . '.' . $file_row['f_ext'],
                'thumb_name' => md5($file_row['f_hash'] . $phpwcms['img_list_width'] . $phpwcms['img_list_height'] . $phpwcms['sharpen_level'] . $phpwcms['colorspace'])
            ));
            if($thumb_image != false) {
                $thumb = '<img src="' . $thumb_image['src'] . '" class="rounded mw-100" alt="' . $filename . '">';
            }
        } else {
            $thumb = '<img src="' . PHPWCMS_RESIZE_IMAGE . '/' . $phpwcms['img_list_width'] . 'x' . $phpwcms['img_list_height'] . '/' . $file_row['f_hash'] . '.' . $file_row['f_ext'] . '" class="rounded mw-100" alt="' . $filename . '">';
        }
        if($thumb) {
            $thumb = '<div class="mt-3"><a href="' . $edit_url . '">' . $thumb . '</a></div>';
        }
    }

    echo '<div class="list-group-item d-flex align-items-center gap-2">';

    echo '<span class="flex-shrink-0 invisible">' . backend_icon('chevron-right') . '</span>';
    echo '<span class="flex-shrink-0 fg-secondary">' . backend_icon(backend_file_icon($file_row['f_ext'])) . '</span>';

    echo '<div class="flex-grow-1 text-truncate">';
    echo '<div class="text-truncate"><a href="' . $edit_url . '" class="text-decoration-none" title="' . $BL['be_fprivfunc_editfile'] . ': ' . $filename . '">' . $filename . '</a></div>';
    echo '<div class="small fg-secondary text-truncate">' . $meta . '</div>';
    echo $thumb;
    echo '</div>';

    echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">';

    // download
    echo backend_action('include/inc_act/act_download.php?dl=' . $file_row['f_id'], 'download', $BL['be_fprivfunc_dlfile'] . ': ' . $filename);
    // cut or already on the clipboard
    if($cutID == $file_row['f_id']) {
        echo backend_action('#', 'scissors', $BL['be_fprivfunc_clipfile'] . ': ' . $filename, 'active', '', 'warning');
    } else {
        echo backend_action($zieldatei . '&amp;cut=' . $file_row['f_id'], 'scissors', $BL['be_fprivfunc_cutfile'] . ': ' . $filename);
    }
    // file information
    echo backend_action($edit_url, 'pen', $BL['be_fprivfunc_editfile'] . ': ' . $filename);
    // active/inactive
    echo backend_action('include/inc_act/act_file.php?aktiv=' . $file_row['f_id'] . '%7C' . true_false($file_row['f_aktiv']), $file_row['f_aktiv'] ? 'toggle-on' : 'toggle-off', $BL['be_fprivfunc_cactivefile'] . ': ' . $filename, $file_row['f_aktiv'] ? 'active' : 'normal', '', $file_row['f_aktiv'] ? 'success' : 'secondary');
    // public/private
    echo backend_action('include/inc_act/act_file.php?public=' . $file_row['f_id'] . '%7C' . true_false($file_row['f_public']), $file_row['f_public'] ? 'globe' : 'lock', $BL['be_fprivfunc_cpublicfile'] . ': ' . $filename, $file_row['f_public'] ? 'active' : 'normal', '', $file_row['f_public'] ? 'success' : 'secondary');

    // move to trash
    if($file_row['f_uid'] === intval($_SESSION['wcs_user_id']) || !empty($_SESSION['wcs_user_admin'])) {
        $confirm = ' onclick="return confirm(\'' . $BL['be_fprivfunc_jsmovetrash1'] . '\n[' . $filename_js . ']\n' . $BL['be_fprivfunc_jsmovetrash2'] . '\');"';
        echo backend_action('include/inc_act/act_file.php?trash=' . $file_row['f_id'] . '%7C1', 'trash', $BL['be_fprivfunc_movetrash'] . ': ' . $filename, 'normal', $confirm);
    } else {
        echo backend_action('#', 'trash', $BL['be_fprivfunc_movetrash'] . ': ' . $filename, 'disabled');
    }

    echo '</div>';

    echo '</div>' . LF;

}

/**
 * Single directory row of the private file listing
 */
function backend_dir_item($row, $zieldatei, $cutID, $child_count, $open) {

    $BL = $GLOBALS['BL'];

    $dirname = html($row['f_name']);
    if($_SESSION['wcs_user_id'] != $row['f_uid']) {
        $dirname .= ' (' . html($row['usr_login']) . ')';
    }
    $dirname_js = html(str_replace("'", "\\'", $row['f_name'] . ($_SESSION['wcs_user_id'] != $row['f_uid'] ? ' (' . $row['usr_login'] . ')' : '')), false);

    switch($row['f_gallerystatus']) {
        case 2:
            $icon  = 'images';
            $label = $BL['be_gallery_root'];
            break;
        case 3:
            $icon  = 'image';
            $label = $BL['be_gallery_directory'];
            break;
        default:
            $icon  = 'folder-fill';
            $label = $row['f_name'];
    }

    echo '<div class="list-group-item d-flex align-items-center gap-2">';

    // open/close the directory
    if($child_count) {
        $klapp_status = $open ? 0 : 1;
        echo '<a class="flex-shrink-0 link-secondary text-decoration-none" href="' . $zieldatei . '&amp;klapp=' . $row['f_id'] . '%7C' . $klapp_status . '"';
        echo ' title="' . ($klapp_status ? $BL['be_fprivfunc_opendir'] : $BL['be_fprivfunc_closedir']) . ': ' . $dirname . '">';
        echo backend_icon($klapp_status ? 'chevron-right' : 'chevron-down');
        echo '</a>';
    } else {
        echo '<span class="flex-shrink-0 invisible">' . backend_icon('chevron-right') . '</span>';
    }

    echo '<span class="flex-shrink-0 fg-secondary" title="' . html($label) . '">' . backend_icon($icon) . '</span>';

    echo '<div class="flex-grow-1 text-truncate"><span class="fw-semibold">' . $dirname . '</span></div>';

    echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">';

    // upload a file into this directory
    echo backend_action($zieldatei . '&amp;upload=' . $row['f_id'], 'upload', $BL['be_fprivfunc_upload'] . ': ' . $dirname);
    // create a sub directory or paste the clipboard file into it
    if(!$cutID) {
        echo backend_action($zieldatei . '&amp;mkdir=' . $row['f_id'], 'folder-plus', $BL['be_fprivfunc_makenew'] . ': ' . $dirname);
    } else {
        echo backend_action('include/inc_act/act_file.php?paste=' . $cutID . '%7C' . $row['f_id'], 'clipboard-plus', $BL['be_fprivfunc_paste'] . ': ' . $dirname);
    }
    // directory data
    echo backend_action($zieldatei . '&amp;editdir=' . $row['f_id'], 'pen', $BL['be_fprivfunc_edit'] . ': ' . $dirname);
    // active/inactive
    echo backend_action('include/inc_act/act_file.php?aktiv=' . $row['f_id'] . '%7C' . true_false($row['f_aktiv']), $row['f_aktiv'] ? 'toggle-on' : 'toggle-off', $BL['be_fprivfunc_cactive'] . ': ' . $dirname, $row['f_aktiv'] ? 'active' : 'normal', '', $row['f_aktiv'] ? 'success' : 'secondary');
    // public/private
    echo backend_action('include/inc_act/act_file.php?public=' . $row['f_id'] . '%7C' . true_false($row['f_public']), $row['f_public'] ? 'globe' : 'lock', $BL['be_fprivfunc_cpublic'] . ': ' . $dirname, $row['f_public'] ? 'active' : 'normal', '', $row['f_public'] ? 'success' : 'secondary');

    // an empty directory can be deleted
    if(!$child_count) {
        $confirm = ' onclick="return confirm(\'' . $BL['be_fprivfunc_jsdeldir'] . '\n[' . $dirname_js . ']? \');"';
        echo backend_action('include/inc_act/act_file.php?delete=' . $row['f_id'] . '%7C9', 'trash', $BL['be_fprivfunc_deldir'] . ': ' . $dirname, 'normal', $confirm);
    } else {
        echo backend_action('#', 'trash', str_replace('{VAL}', $dirname, $BL['be_fprivfunc_notempty']), 'disabled');
    }

    echo '</div>';

    echo '</div>' . LF;

}

/**
 * Files stored in a directory of the private file listing
 */
function list_private_files($pid, $zieldatei, $userID, $cutID, $phpwcms) {

    $file_sql = 'SELECT * FROM ' . DB_PREPEND . "phpwcms_file WHERE f_pid=" . intval($pid);
    if(empty($_SESSION['wcs_user_admin'])) {
        $file_sql .= ' AND f_uid=' . intval($userID);
    }
    $file_sql .= ' AND f_kid=1 AND f_trash=0 ORDER BY f_sort, f_name';

    $file_result = _dbQuery($file_sql);

    if(isset($file_result[0]['f_id'])) {
        foreach($file_result as $file_row) {
            backend_file_item($file_row, $zieldatei, $cutID, $phpwcms);
        }
    }

}

/**
 * @param $pid
 * @param $vor
 * @param $zieldatei
 * @param $userID
 * @param $cutID
 * @param $phpwcms
 *
 * @return mixed
 */
function list_private($pid, $vor, $zieldatei, $userID, $cutID, $phpwcms) {

    $cutID = intval($cutID);
    $pid   = intval($pid);

    $sql  = 'SELECT * FROM ' . DB_PREPEND . 'phpwcms_file f ';
    $sql .= 'LEFT JOIN ' . DB_PREPEND . 'phpwcms_user u ON u.usr_id=f.f_uid ';
    $sql .= 'WHERE f.f_pid=' . intval($pid) . ' AND ';
    if(empty($_SESSION['wcs_user_admin'])) {
        $sql .= 'f.f_uid=' . intval($userID) . ' AND ';
    }
    $sql .= 'f.f_kid=0 AND f.f_trash=0 ORDER BY f_sort, f_name';

    $result = _dbQuery($sql);

    if(!isset($result[0]['f_id'])) {
        return $vor;
    }

    foreach($result as $row) {

        //Ermitteln des Aufklappwertes
        $klapp_status = empty($_SESSION['klapp'][$row['f_id']]) ? 1 : 0;

        //Ermitteln, ob überhaupt abhängige Dateien/Ordner existieren
        $count_sql  = 'SELECT COUNT(f_id) FROM ' . DB_PREPEND . 'phpwcms_file WHERE ';
        $count_sql .= 'f_pid=' . $row['f_id'] . ' AND ';
        if(empty($_SESSION['wcs_user_admin'])) {
            $count_sql .= 'f_uid=' . intval($userID) . ' AND ';
        }
        $count_sql .= 'f_trash=0';

        $count_wert = _dbQuery($count_sql, 'COUNT');

        backend_dir_item($row, $zieldatei, $cutID, $count_wert, !$klapp_status);

        //Weiter, wenn Unterstruktur
        if(!$klapp_status && $count_wert) {
            echo '<div class="list-group list-group-flush ms-5">' . LF;
            list_private($row['f_id'], 0, $zieldatei, $userID, $cutID, $phpwcms);
            list_private_files($row['f_id'], $zieldatei, $userID, $cutID, $phpwcms);
            echo '</div>' . LF;
        }

        //Zaehler mitführen
        $_SESSION['list_zaehler']++;
    }

    return $vor;
}

function true_false($wert) {
    //Wechselt den Wahr/Falsch wert zum Gegenteil: 1=>0 und 0=>1
    return (intval($wert)) ? 0 : 1;
}

function on_off($wert, $string, $art = 1) {
    //Erzeugt das Status-Zeichen für Klapp-Auf/Zu
    //Wenn Art = 1 dann als Zeichen, ansonsten als Bild
    if($wert) {
        return ($art == 1) ? "+" : "<img src=\"img/symbols/klapp_zu.gif\" title=\"".$GLOBALS['BL']['be_fprivfunc_opendir'].": ".$string."\" border=\"0\" alt=\"\" />";
    } else {
        return ($art == 1) ? "-" : "<img src=\"img/symbols/klapp_auf.gif\" title=\"".$GLOBALS['BL']['be_fprivfunc_closedir'].": ".$string."\" border=\"0\" alt=\"\" />";
    }
}
