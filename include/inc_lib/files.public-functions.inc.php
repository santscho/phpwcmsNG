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

/*
 * Single file row of the public file listing
 */
function backend_public_file_item($file_row, $phpwcms) {

    $BL = $GLOBALS['BL'];

    $filename = html($file_row['f_name']);
    $info_url = 'fileinfo.php?public&amp;fid=' . $file_row['f_id'];
    $info_js  = " onclick=\"flevPopupLink(this.href,'filedetail','scrollbars=yes,resizable=yes,width=500,height=400',1);return document.MM_returnValue;\"";

    $meta = strtoupper(html($file_row['f_ext']));
    if(!empty($file_row['f_size'])) {
        $meta .= ' &middot; ' . fsize($file_row['f_size']);
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
            $thumb = '<div class="mt-3"><a href="' . $info_url . '" target="_blank"' . $info_js . '>' . $thumb . '</a></div>';
        }
    }

    echo '<div class="list-group-item d-flex align-items-center gap-2">';
    echo '<span class="flex-shrink-0 invisible">' . backend_icon('chevron-right') . '</span>';
    echo '<span class="flex-shrink-0 fg-secondary">' . backend_icon(backend_file_icon($file_row['f_ext'])) . '</span>';

    echo '<div class="flex-grow-1 text-truncate">';
    echo '<div class="text-truncate"><a href="' . $info_url . '" target="_blank" class="text-decoration-none" title="' . $filename . '"' . $info_js . '>' . $filename . '</a></div>';
    echo '<div class="small fg-secondary text-truncate">' . $meta . '</div>';
    echo $thumb;
    echo '</div>';

    echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">';
    echo '<a class="btn btn-icon btn-outline theme-secondary btn-sm" href="include/inc_act/act_download.php?pl=1&amp;dl=' . $file_row['f_id'] . '" target="_blank" title="' . $BL['be_fprivfunc_dlfile'] . ': ' . $filename . '" aria-label="' . $BL['be_fprivfunc_dlfile'] . ': ' . $filename . '">' . backend_icon('download') . '</a>';
    echo '</div>';

    echo '</div>' . LF;

}

/*
 * Files stored in a directory of the public file listing
 */
function list_public_files($pid, $userID, $phpwcms) {

    $file_sql  = 'SELECT * FROM ' . DB_PREPEND . 'phpwcms_file WHERE f_pid=' . intval($pid) . ' AND f_uid=' . intval($userID);
    $file_sql .= ' AND f_public=1 AND f_aktiv=1 AND f_kid=1 AND f_trash=0 ORDER BY f_sort, f_name';

    $file_result = _dbQuery($file_sql);

    if(isset($file_result[0]['f_id'])) {
        foreach($file_result as $file_row) {
            backend_public_file_item($file_row, $phpwcms);
        }
    }

}

function list_public($pid, $vor, $zieldatei, $userID, $phpwcms) {

    $pid = intval($pid);
    $userID = intval($userID);

    //Folder Listing für Public files
    $sql = 'SELECT f_id, f_name FROM ' . DB_PREPEND . 'phpwcms_file WHERE ' .
           'f_pid=' . $pid . ' AND ' .
           'f_public=1 AND f_aktiv=1 AND ' .
           'f_uid=' . $userID . ' AND ' .
           'f_kid=0 AND f_trash=0 ORDER BY f_sort, f_name';

    $result = _dbQuery($sql);
    if(!isset($result[0]['f_id'])) {
        return $vor;
    }

    foreach($result as $row) {

        $dirname = html($row['f_name']);
        $dir_id  = intval($row['f_id']);

        //Ermitteln des Aufklappwertes
        $klapp_status = empty($_SESSION['pklapp'][$dir_id]) ? 1 : 0;

        //Ermitteln, ob überhaupt abhängige Dateien/Ordner existieren
        $count_sql = 'SELECT COUNT(f_id) FROM ' . DB_PREPEND . 'phpwcms_file WHERE ' .
                     'f_pid=' . $dir_id . ' AND f_uid=' . $userID . ' AND ' .
                     'f_public=1 AND f_aktiv=1 AND f_trash=0';

        $count_wert = _dbQuery($count_sql, 'COUNT');

        echo '<div class="list-group-item d-flex align-items-center gap-2">';

        // open/close the directory
        if($count_wert) {
            echo '<a class="flex-shrink-0 link-secondary text-decoration-none" href="' . $zieldatei . '&amp;pklapp=' . $dir_id . '%7C' . $klapp_status . '"';
            echo ' title="' . ($klapp_status ? $GLOBALS['BL']['be_fprivfunc_opendir'] : $GLOBALS['BL']['be_fprivfunc_closedir']) . ': ' . $dirname . '">';
            echo backend_icon($klapp_status ? 'chevron-right' : 'chevron-down');
            echo '</a>';
        } else {
            echo '<span class="flex-shrink-0 invisible">' . backend_icon('chevron-right') . '</span>';
        }

        echo '<span class="flex-shrink-0 fg-secondary">' . backend_icon('folder-fill') . '</span>';
        echo '<div class="flex-grow-1 text-truncate"><span class="fw-semibold">' . $dirname . '</span></div>';

        echo '</div>' . LF;

        //Weiter, wenn Unterstruktur
        if(!$klapp_status && $count_wert) {
            echo '<div class="list-group list-group-flush ms-5">' . LF;
            list_public($dir_id, $vor + 1, $zieldatei, $userID, $phpwcms);
            list_public_files($dir_id, $userID, $phpwcms);
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
        return ($art == 1) ? "+" : "<img src=\"img/symbols/klapp_zu.gif\" border=\"0\" title=\"".$GLOBALS['BL']['be_fprivfunc_opendir']." ".$string."\">";
    } else {
        return ($art == 1) ? "-" : "<img src=\"img/symbols/klapp_auf.gif\" border=\"0\" title=\"".$GLOBALS['BL']['be_fprivfunc_closedir']." ".$string."\">";
    }
}

function list_public_root($wert) {
    //Checken ob public root files für user gezeigt werden sollen
    return ($wert) ? 1 : 0;
}
