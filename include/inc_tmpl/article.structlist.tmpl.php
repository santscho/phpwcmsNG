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


//19-11-2004 Fernando Batista -> Copy article, Copy strutures http://fernandobatista.net
//31-03-2005 Fernando Batista -> Copy/Cut Article Content http://fernandobatista.net

$listmode = 0;
$cut_id = (isset($_GET["cut"])) ? intval($_GET["cut"]) : 0;
$cut_article = (isset($_GET["acut"])) ? intval($_GET["acut"]) : 0;

$copy_id = (isset($_GET["cop"])) ? intval($_GET["cop"]) : 0;
$copy_article = (isset($_GET["acopy"])) ? intval($_GET["acopy"]) : 0;

if(isset($_GET["open"])) {
    list($open_id, $open_value) = explode(":", $_GET["open"]);
    $open_id = intval($open_id);
    if(empty($open_value)) {
        unset($_SESSION["structure"][$open_id]);
    }
    $_SESSION["structure"][$open_id] = $open_value;
     _dbQuery("UPDATE ".DB_PREPEND."phpwcms_user SET usr_var_structure="._dbEscape(serialize($_SESSION["structure"]))." WHERE usr_id=".aporeplace($_SESSION["wcs_user_id"]), 'UPDATE');
}

//31-03-2005 Fernando Batista  start---------------------------------------------------------------------------
$cut_article_content = (isset($_GET["accut"])) ? intval($_GET["accut"]) : 0;
$copy_article_content = (isset($_GET["accopy"])) ? intval($_GET["accopy"]) : 0;
if(isset($_GET["opena"])) {
    list($open_id, $open_value) = explode(":", $_GET["opena"]);
    $open_id = intval($open_id);
    if(empty($open_value)) {
        unset($_SESSION["structure"]["article"][$open_id]);
    } else {
        $_SESSION["structure"]["article"][$open_id] = $open_value;
    }
    _dbQuery("UPDATE ".DB_PREPEND."phpwcms_user SET usr_var_structure="._dbEscape(serialize($_SESSION["structure"]))." WHERE usr_id=".aporeplace($_SESSION["wcs_user_id"]), 'UPDATE');
}
//31-03-2005 Fernando Batista  end-------------------

$child_count = get_root_childcount(0);
$an          = $indexpage['acat_name'];
$is_open     = !empty($_SESSION['structure'][0]);
?>
<div class="card">

    <div class="card-header"><?php echo $BL['be_article_title'] ?></div>

    <div class="list-group list-group-flush" data-backend-sortable="1" data-backend-sort-token="<?php echo get_token_get_value() ?>" data-backend-sort-error="<?php echo html($BL['be_sort_drag_error'] ?? 'Reordering failed.') ?>">

        <div class="list-group-item d-flex align-items-center gap-2">
<?php
echo '<span class="fg-secondary flex-shrink-0" data-bs-toggle="tooltip" title="'.html($BL['be_admin_struct_title']).'">'.backend_icon('grid-1x2-fill').'</span>';
if($child_count) {
    echo '<a href="phpwcms.php?do=articles&amp;open=0:'.($is_open ? 0 : 1).'" class="link-secondary text-decoration-none flex-shrink-0"';
    echo ' aria-label="'.html($an).'" data-bs-toggle="tooltip" title="'.html($an).'">'.backend_icon($is_open ? 'chevron-down' : 'chevron-right').'</a>';
} else {
    echo '<span class="flex-shrink-0" style="width:16px"></span>';
}
?>
            <div class="flex-grow-1 text-truncate"><strong><?php echo $an ?></strong></div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
<?php
echo backend_action('phpwcms.php?do=articles&amp;p=1&amp;struct=0', 'plus', $BL['be_func_struct_new_article'].' ['.$an.']');
echo backend_action('phpwcms.php?do=articles', 'scissors', $BL['be_func_struct_no_cut'], 'disabled');
echo backend_action('phpwcms.php?do=articles', 'copy', $BL['be_func_struct_no_copy'], 'disabled');
?>
            </div>
        </div>

<?php
if(is_array($_SESSION["structure"]) && !empty($_SESSION["structure"][0])) {
    struct_articlelist(0, 0, $copy_article_content, $cut_article_content, $copy_article, $cut_article, $indexpage['acat_order']);
    struct_list(0, $copy_article_content, $cut_article_content, $copy_id, $copy_article, $cut_id, $cut_article, $listmode);
}
?>

    </div>

</div>
