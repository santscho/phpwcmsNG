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

if(isset($_GET["all"])) { // Hide/Show

    $_SESSION["klapp"] = array();

    if($_GET["all"] == "open") { // All

        $sql = "SELECT f_id FROM ".DB_PREPEND."phpwcms_file WHERE f_kid=0 AND f_trash=0";
        if(empty($_SESSION["wcs_user_admin"])) {
            $sql .= " AND f_uid=".$_SESSION["wcs_user_id"];
        }

        $result = _dbQuery($sql);

        if(isset($result[0]['f_id'])) {
            foreach($result as $row) {
                $_SESSION["klapp"][intval(['f_id'])] = 1;
            }
        }
    }

    _dbQuery("UPDATE ".DB_PREPEND."phpwcms_user SET usr_var_privatefile="._dbEscape(serialize($_SESSION["klapp"]))." WHERE usr_id=".intval($_SESSION["wcs_user_id"]), 'UPDATE');

} elseif(!isset($_SESSION["klapp"])) {

    $_SESSION["klapp"] = array();

}

if(isset($_GET["klapp"])) {

    list($klapp_id, $klapp_value) = explode("|", $_GET["klapp"]);
    $klapp_id = intval($klapp_id);

    if(intval($klapp_value)) {
        $_SESSION["klapp"][$klapp_id] = 1;
    } else {
        unset($_SESSION["klapp"][$klapp_id]);
    }

    foreach($_SESSION["klapp"] as $klapp_id => $klapp_value) {
        if(!$klapp_value) {
            unset($_SESSION["klapp"][$klapp_id]);
        }
    }

    _dbQuery("UPDATE ".DB_PREPEND."phpwcms_user SET usr_var_privatefile="._dbEscape(serialize($_SESSION["klapp"]))." WHERE usr_id=".intval($_SESSION["wcs_user_id"]), 'UPDATE');
}

// Set counter for listing
$_SESSION["list_zaehler"] = 0;

// Are there any files or folders
$sql = "SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE f_trash=0";
if(empty($_SESSION["wcs_user_admin"])) {
    $sql .= " AND f_uid=".$_SESSION["wcs_user_id"];
}
$sql .= " LIMIT 1";
$count_user_files = _dbCount($sql);

// Does the user have files to list
if($count_user_files) {

    $_files_target = 'phpwcms.php?do=files&amp;f=0';

    echo '<div class="card mb-5">';
    echo '<div class="list-group list-group-flush">';

    list_private(0, 0, $_files_target, $_SESSION["wcs_user_id"], $cutID, $phpwcms);
    list_private_files(0, $_files_target, $_SESSION["wcs_user_id"], $cutID, $phpwcms);

    echo '</div>';
    echo '</div>';

} else {

    // Nothing to list
    echo '<div class="card mb-5">';
    echo '<div class="card-body d-flex flex-column md:flex-row md:align-items-center gap-3">';
    echo '<span class="fg-secondary">'.$BL['be_fprivadd_nofolders'].'</span>';
    echo '<a class="btn theme-primary btn-solid btn-styled btn-sm" href="phpwcms.php?do=files&amp;f=0&amp;mkdir=0">'.$BL['be_fpriv_button'].'</a>';
    echo '</div>';
    echo '</div>';

}
