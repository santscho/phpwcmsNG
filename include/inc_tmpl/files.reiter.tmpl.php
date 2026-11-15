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

// files
$files_folder = (isset($_GET["f"])) ? intval($_GET["f"]) : 0; //Ermitteln, welcher Unterreiter angezeigt wird

// if cut/paste is active
$cutID = (isset($_GET["cut"])) ? intval($_GET["cut"]) : 0;

// action buttons of the current folder
$_files_actions = '';

$_files_help = ' onclick="flevPopupLink(this.href,\'filehelp\',\'scrollbars=yes,resizable=yes,width=320,height=300\',0);return document.MM_returnValue"';

if($files_folder === 0) {

    // upload a file to the root directory
    $_files_actions .= backend_action('phpwcms.php?do=files&amp;f=0&amp;upload=0', 'upload', $BL['be_ftab_upload']);

    // create a new directory, or paste the cut file into the root directory
    if(!$cutID) {
        $_files_actions .= backend_action('phpwcms.php?do=files&amp;f=0&amp;mkdir=0', 'plus', $BL['be_ftab_createnew']);
    } else {
        $_files_actions .= backend_action('include/inc_act/act_file.php?paste=' . $cutID . '%7C0', 'clipboard-plus', $BL['be_ftab_paste']);
    }

}

if($files_folder === 0 || $files_folder === 1) {
    $_files_actions .= backend_action('phpwcms.php?do=files&amp;f=' . $files_folder . '&amp;all=open', 'chevron-bar-expand', $BL['be_ftab_open']);
    $_files_actions .= backend_action('phpwcms.php?do=files&amp;f=' . $files_folder . '&amp;all=close', 'chevron-bar-contract', $BL['be_ftab_close']);
}

if($files_folder !== 2) {
    $_files_actions .= backend_action('include/inc_help/filehelp.htm', 'question-circle', $BL['be_ftab_filehelp'], 'normal', $_files_help);
}

// thumbnail listing on/off
if($files_folder !== 2) {
    if($_SESSION["wcs_user_thumb"]) {
        $_files_actions .= backend_action('include/inc_act/act_file.php?thumbnail=0', 'image-fill', $BL['be_ftab_disablethumb'], 'active');
    } else {
        $_files_actions .= backend_action('include/inc_act/act_file.php?thumbnail=1', 'image', $BL['be_ftab_enablethumb']);
    }
}

$_files_tabs = array(
    0 => array('link' => 'phpwcms.php?do=files&amp;f=0', 'label' => $BL['be_ftab_private']),
    1 => array('link' => 'phpwcms.php?do=files&amp;f=1', 'label' => $BL['be_ftab_public']),
    3 => array('link' => 'phpwcms.php?do=files&amp;f=3', 'label' => $BL['be_ftab_search']),
    2 => array('link' => 'phpwcms.php?do=files&amp;f=2', 'label' => $BL['be_ftab_trash']),
);

?>
<div class="card mb-5">

    <div class="card-header d-flex flex-column lg:flex-row lg:align-items-center gap-3">

        <h2 class="h6 mb-0"><?php echo $BL['be_ftab_title'] ?></h2>

        <div class="nav nav-pills flex-wrap me-auto">
<?php foreach($_files_tabs as $_files_key => $_files_tab): ?>
            <a href="<?php echo $_files_tab['link'] ?>" class="nav-link<?php echo $files_folder === $_files_key ? ' active' : '' ?>"<?php echo $files_folder === $_files_key ? ' aria-current="page"' : '' ?>><?php echo $_files_tab['label'] ?></a>
<?php endforeach; ?>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0"><?php echo $_files_actions ?></div>

    </div>

</div>
