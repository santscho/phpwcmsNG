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


//Default for listing public files
$vor = 0;

if(!isset($_SESSION["pklapp"]) || (isset($_GET["all"]) && $_GET["all"] == "close")) {
    $_SESSION["pklapp"] = array();
}

if(isset($_GET["all"]) && $_GET["all"] == "open") { // Alle öffentlichen Verzeichnisse aufklappen

    $_SESSION["pklapp"] = array();

    //Besitzer öffnen
    $sql = "SELECT DISTINCT f.f_uid FROM ".DB_PREPEND."phpwcms_file f ".
           "WHERE f.f_public=1 AND f.f_aktiv=1 AND f.f_trash=0";

    $result = _dbQuery($sql);

    if(isset($result[0]['f_uid'])) {
        foreach($result as $row) {
            $_SESSION["pklapp"]["u".intval($row["f_uid"])] = 1;
        }
    }

    //Verzeichnisse öffnen
    $sql = "SELECT f_id FROM ".DB_PREPEND."phpwcms_file ".
           "WHERE f_kid=0 AND f_public=1 AND f_aktiv=1 AND f_trash=0";

    $result = _dbQuery($sql);

    if(isset($result[0]['f_id'])) {
        foreach($result as $row) {
            $_SESSION["pklapp"][intval($row["f_id"])] = 1;
        }
    }

    _dbQuery("UPDATE ".DB_PREPEND."phpwcms_user SET usr_var_publicfile="._dbEscape(serialize($_SESSION["pklapp"]))." WHERE usr_id=".intval($_SESSION["wcs_user_id"]), 'UPDATE');

}

if(isset($_GET["pklapp"])) {

    list($pklapp_id, $pklapp_value) = explode("|", $_GET["pklapp"]);

    if(intval($pklapp_value)) {
        $_SESSION["pklapp"][$pklapp_id] = 1;
    } else {
        unset($_SESSION["pklapp"][$pklapp_id]);
    }

    foreach($_SESSION["pklapp"] as $pklapp_id => $pklapp_value) {
        if(!$pklapp_value) {
            unset($_SESSION["pklapp"][$pklapp_id]);
        }
    }

    _dbQuery("UPDATE ".DB_PREPEND."phpwcms_user SET usr_var_publicfile="._dbEscape(serialize($_SESSION["pklapp"]))." WHERE usr_id=".intval($_SESSION["wcs_user_id"]), 'UPDATE');
}

$_SESSION["list_zaehler"] = 0; // set counter

//Feststellen, ob überhaupt Dateien/Ordner des Users vorhanden sind
$count_user_files = _dbQuery("SELECT COUNT(f_id) FROM ".DB_PREPEND."phpwcms_file WHERE f_public=1 AND f_aktiv=1 AND f_trash=0", 'COUNT');

if($count_user_files) { //Wenn überhaupt Public-Dateien vorhanden, dann Listing

    //Prüfen, für welche User überhaupt Public Files vorhanden sind
    $sql = "SELECT DISTINCT ".DB_PREPEND."phpwcms_file.f_uid, ".DB_PREPEND."phpwcms_user.usr_login, ".DB_PREPEND."phpwcms_user.usr_name ".
           "FROM ".DB_PREPEND."phpwcms_file INNER JOIN ".DB_PREPEND."phpwcms_user ON ".DB_PREPEND."phpwcms_file.f_uid=".DB_PREPEND."phpwcms_user.usr_id ".
           "WHERE ".DB_PREPEND."phpwcms_file.f_public=1 AND ".DB_PREPEND."phpwcms_file.f_aktiv=1 AND ".DB_PREPEND."phpwcms_file.f_trash=0 ".
           "ORDER BY ".DB_PREPEND."phpwcms_user.usr_name, ".DB_PREPEND."phpwcms_user.usr_login";
    $result = _dbQuery($sql);

    echo '<div class="card mb-5">' . LF;
    echo '<div class="list-group list-group-flush">' . LF;

    if(isset($result[0]['f_uid'])) {
        foreach($result as $row) {

            $pklapp_status = empty($_SESSION["pklapp"]["u".$row["f_uid"]]) ? 1 : 0;
            $user_naming   = html($row["usr_name"]." (".$row["usr_login"].")");

            //Zeile mit den Benutzerinfos
            echo '<div class="list-group-item d-flex align-items-center gap-2">';
            echo '<a class="flex-shrink-0 link-secondary text-decoration-none" href="phpwcms.php?do=files&amp;f=1&amp;pklapp=u'.$row["f_uid"].'%7C'.$pklapp_status.'"';
            echo ' data-bs-toggle="tooltip" title="'.($pklapp_status ? $BL['be_fprivfunc_opendir'] : $BL['be_fprivfunc_closedir']).': '.$user_naming.'">';
            echo backend_icon($pklapp_status ? 'chevron-right' : 'chevron-down');
            echo '</a>';
            echo '<span class="flex-shrink-0 fg-secondary">'.backend_icon('person-fill').'</span>';
            echo '<div class="flex-grow-1 text-truncate">';
            echo '<span class="fw-semibold">'.$user_naming.'</span> <span class="badge theme-secondary">'.$BL['be_fpublic_user'].'</span>';
            echo '</div>';
            echo '</div>' . LF;

            if(!$pklapp_status) {
                echo '<div class="list-group list-group-flush ms-5">' . LF;
                list_public(0, $vor + 1, "phpwcms.php?do=files&amp;f=1", $row["f_uid"], $phpwcms);
                list_public_files(0, $row["f_uid"], $phpwcms);
                echo '</div>' . LF;
            }

        }
    }

    echo '</div>' . LF;
    echo '</div>' . LF;

} else { //Wenn keinerlei Datensatz innerhalb Files durchlaufen wurde, dann

    echo '<div class="card mb-5">' . LF;
    echo '<div class="card-body d-flex flex-column md:flex-row md:align-items-center gap-3">' . LF;
    echo '<span class="fg-secondary">'.$BL['be_fpublic_nofiles'].'</span>' . LF;
    echo '<a class="btn theme-primary btn-solid btn-styled btn-sm" href="phpwcms.php?do=files&amp;f=0&amp;mkdir=0">'.$BL['be_fpriv_button'].'</a>' . LF;
    echo '</div>' . LF;
    echo '</div>' . LF;

}
