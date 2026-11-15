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

// 19-11-2004 Fernando Batista

// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
	die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------


?>

<?php

$listmode				= 1;
$cut_id					= empty($_GET["cut"]) ? 0 : intval($_GET["cut"]);
$cut_article			= empty($_GET["acut"]) ? 0 : intval($_GET["acut"]);
$copy_id				= empty($_GET["cop"]) ? 0 : intval($_GET["cop"]);
$copy_article			= empty($_GET["acopy"]) ? 0 : intval($_GET["acopy"]);
$cut_article_content	= empty($_GET["accut"]) ? 0 : intval($_GET["accut"]);
$copy_article_content	= empty($_GET["accopy"]) ? 0 : intval($_GET["accopy"]);

if(isset($_GET["open"])) {
	list($open_id, $open_value) = explode(":", $_GET["open"]);
	$open_id = intval($open_id);
	if(empty($open_value)) {
		unset($_SESSION["structure"][$open_id]);
	} else {
		$_SESSION["structure"][$open_id] = $open_value;
	}
}

$child_count		= get_root_childcount(0);
$child_sort			= ( $child_count + 1 ) * 10;
$struct_template	= _dbQuery('SELECT template_default, template_name FROM '.DB_PREPEND.'phpwcms_template WHERE template_trash=0 AND template_id='.intval($indexpage['acat_template']));
$an                 = $indexpage['acat_name'];
$is_open            = !empty($_SESSION['structure'][0]);
?>
<div class="card">

    <div class="card-header"><?php echo $BL['be_admin_struct_title'] ?></div>

    <div class="list-group list-group-flush" data-backend-sortable="1" data-backend-sort-token="<?php echo get_token_get_value() ?>" data-backend-sort-error="<?php echo html($BL['be_sort_drag_error'] ?? 'Reordering failed.') ?>">

        <div class="list-group-item d-flex align-items-center gap-2">
<?php
if($child_count) {
    echo '<a href="phpwcms.php?do=admin&amp;p=6&amp;open=0:'.($is_open ? 0 : 1).'" class="link-secondary text-decoration-none flex-shrink-0"';
    echo ' aria-label="'.html($an).'" title="'.html($an).'">'.backend_icon($is_open ? 'chevron-down' : 'chevron-right').'</a>';
} else {
    echo '<span class="flex-shrink-0" style="width:16px"></span>';
}
?>
            <div class="flex-grow-1 text-truncate"><strong><?php echo $an ?></strong><?php
if(isset($struct_template[0]['template_name'])) {
    echo ' <span class="badge theme-secondary">'.html($struct_template[0]["template_name"]).'</span>';
    if($struct_template[0]["template_default"]) {
        echo ' <span class="badge theme-info">'.$BL['be_admin_tmpl_default'].'</span>';
    }
}
?></div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
<?php
echo backend_action('phpwcms.php?do=admin&amp;p=6&amp;struct=0&amp;sort='.$child_sort, 'plus', $BL['be_func_struct_insert_level'].' ['.$an.']');
echo backend_action('phpwcms.php?do=admin&amp;p=6', 'cut', $BL['be_func_struct_no_cut'], 'disabled');
echo backend_action('phpwcms.php?do=admin&amp;p=6', 'copy', $BL['be_func_struct_no_copy'], 'disabled');
echo backend_action('phpwcms.php?do=admin&amp;p=6&amp;struct=index', 'pencil', $BL['be_func_struct_sedit'].' ['.$an.']');
?>
            </div>
        </div>

<?php
if(!empty($_SESSION["structure"][0])) {
    struct_list(0, $copy_article_content, $cut_article_content, $copy_id, $copy_article, $cut_id, $cut_article, $listmode);
}
?>

    </div>

</div>
