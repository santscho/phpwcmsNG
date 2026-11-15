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

// list trashed files
$file_sql = "SELECT * FROM ".DB_PREPEND."phpwcms_file WHERE f_uid=".$_SESSION["wcs_user_id"]." AND f_kid=1 AND f_trash=1 ORDER BY f_name";
$file_result = _dbQuery($file_sql);

if(isset($file_result[0]['f_id'])) {

    foreach($file_result as $file_row) {

        $filename = html($file_row["f_name"]);
        $info_url = 'fileinfo.php?fid='.$file_row['f_id'];
        $info_js  = " onclick=\"flevPopupLink(this.href,'filedetail','scrollbars=yes,resizable=yes,width=500,height=400',1);return document.MM_returnValue;\"";

        $meta = strtoupper(html($file_row["f_ext"]));
        if(!empty($file_row["f_size"])) {
            $meta .= ' &middot; '.fsize($file_row["f_size"]);
        }

        // restore or delete for good, both with the existing confirm questions
        $restore_js = " onclick=\"return confirm('".str_replace(array("\n", "'"), array("\\n", "\\'"), str_replace('{VAL}', $filename, $BL['be_ftrash_restore']))."');\"";
        $delete_js  = " onclick=\"return confirm('".str_replace(array("\n", "'"), array("\\n", "\\'"), str_replace('{VAL}', $filename, $BL['be_ftrash_delete']))."');\"";

        echo '<div class="list-group-item d-flex align-items-center gap-2">';

        echo '<span class="flex-shrink-0 invisible">'.backend_icon('chevron-right').'</span>';
        echo '<span class="flex-shrink-0 fg-secondary">'.backend_icon(backend_file_icon($file_row["f_ext"])).'</span>';

        echo '<div class="flex-grow-1 text-truncate">';
        echo '<div class="text-truncate"><a href="'.$info_url.'" target="_blank" class="text-decoration-none" data-bs-toggle="tooltip" title="'.$filename.'"'.$info_js.'>'.$filename.'</a></div>';
        echo '<div class="small fg-secondary text-truncate">'.$meta.'</div>';
        echo '</div>';

        echo '<div class="d-flex align-items-center gap-2 flex-shrink-0">';
        echo backend_action('include/inc_act/act_file.php?trash='.$file_row["f_id"].'%7C0', 'restore', $BL['be_ftrash_undo'].': '.$filename, 'normal', $restore_js);
        echo backend_action('include/inc_act/act_file.php?trash='.$file_row["f_id"].'%7C9', 'trash', $BL['be_ftrash_delfinal'].': '.$filename, 'normal', $delete_js, 'danger');
        echo '</div>';

        echo '</div>' . LF;

    }

}
