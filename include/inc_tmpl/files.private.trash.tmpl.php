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

//Feststellen, ob überhaupt Dateien/Ordner im Papierkorb des Users vorhanden sind
$count_user_files = _dbQuery("SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE f_uid=".$_SESSION["wcs_user_id"]." AND f_trash=1", 'COUNT');

//Wenn überhaupt Papierkorb-Dateien für User vorhanden, dann Listing
if($count_user_files) {

    $delall_js = " onclick=\"return confirm('".str_replace("\n", "\\n", $BL['be_ftrash_delall'])."');\"";

    echo '<div class="card mb-5">' . LF;
    echo '<div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">' . LF;
    echo '<h2 class="h6 mb-0">'.$BL['be_ftab_trash'].'</h2>' . LF;
    echo '<span class="badge theme-secondary">'.$count_user_files.'</span>' . LF;
    echo '</div>' . LF;
    echo '<div class="list-group list-group-flush">' . LF;

    include_once PHPWCMS_ROOT."/include/inc_lib/files.private-delfilelist.inc.php";

    echo '</div>' . LF;
    echo '<div class="card-footer d-flex flex-wrap gap-2 justify-content-end">' . LF;
    echo '<a class="btn theme-danger btn-solid btn-styled btn-sm" href="include/inc_act/act_file.php?trash=0%7C9" title="'.$BL['be_ftrash_delallfiles'].'"'.$delall_js.'>'.$BL['be_ftrash_delallfiles'].'</a>' . LF;
    echo '</div>' . LF;
    echo '</div>' . LF;

} else { //Wenn keinerlei Datensatz innerhalb Files durchlaufen wurde, dann

    echo '<div class="card mb-5">' . LF;
    echo '<div class="card-header">' . LF;
    echo '<h2 class="h6 mb-0">'.$BL['be_ftab_trash'].'</h2>' . LF;
    echo '</div>' . LF;
    echo '<div class="card-body d-flex flex-column md:flex-row md:align-items-center gap-3">' . LF;
    echo '<span class="fg-secondary">'.$BL['be_ftrash_nofiles'].'</span>' . LF;
    echo '<a class="btn btn-outline theme-secondary btn-sm" href="phpwcms.php?do=files&amp;f=0">'.$BL['be_ftrash_show'].'</a>' . LF;
    echo '</div>' . LF;
    echo '</div>' . LF;

}
