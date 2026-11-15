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


//recipe

unset($_SESSION['filebrowser_image_target']);

if(!isset($content['recipe']) || !is_array($content['recipe'])) {
	$content['recipe'] = array();
}

// base values
if(empty($content['recipe']['calorificvalue_add']))		$content['recipe']['calorificvalue_add']	= '';
if(empty($content['recipe']['preparation']))			$content['recipe']['preparation']			= '';
if(empty($content['recipe']['ingredients']))			$content['recipe']['ingredients']			= '';
if(empty($content['recipe']['time_add']))				$content['recipe']['time_add']				= '';
if(empty($content['recipe']['category']))				$content['recipe']['category']				= '';
if(empty($content['recipe']['severity']))				$content['recipe']['severity']				= 1;


// retrieve all available keywords
$content['recipe']['get_keywords'] = _dbQuery('SELECT acontent_text FROM '.DB_PREPEND.'phpwcms_articlecontent WHERE acontent_type=26 AND acontent_trash=0');
$content['recipe']['all_keywords'] = '';
if($content['recipe']['get_keywords']) {
	foreach($content['recipe']['get_keywords'] as $temp_val) {
		if($temp_val['acontent_text']) {
			if($content['recipe']['all_keywords']) $content['recipe']['all_keywords'] .= ', ';
			$content['recipe']['all_keywords'] .= $temp_val['acontent_text'];
		}
	}
}
$content['recipe']['all_keywords'] = convertStringToArray($content['recipe']['all_keywords']);

?>
<div class="row g-4">

	<div class="col-12">
		<label class="form-label" for="recipe_category"><?php echo $BL['be_ftptakeover_keywords'] ?></label>
		<div class="d-flex flex-wrap align-items-start gap-2">
			<textarea name="recipe_category" id="recipe_category" cols="40" rows="2" class="form-control autosize"><?php echo html($content['recipe']['category']) ?></textarea>
<?php

if(count($content['recipe']['all_keywords'])) {

	echo '<div class="d-flex align-items-center gap-2">';
	echo '<select name="ph1" id="ph1" class="form-control w-auto" aria-label="' . html($BL['be_ftptakeover_keywords']) . '" ';
	echo 'onchange="insertAtCursorPos(document.articlecontent.recipe_category, ';
	echo '\', \'+document.articlecontent.ph1.options[document.articlecontent.ph1.selectedIndex].value);">';

	foreach($content['recipe']['all_keywords'] as $temp_val) {
		$temp_val = html($temp_val);
		echo '<option value="'.$temp_val.'">'.$temp_val.'</option>'.LF;
	}

	echo '</select>';
	echo backend_action('#', 'plus', $BL['be_ftptakeover_keywords'], 'normal', ' onclick="insertAtCursorPos(document.articlecontent.recipe_category, \', \'+document.articlecontent.ph1.options[document.articlecontent.ph1.selectedIndex].value);return false;"');
	echo '</div>';

}
?>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="recipe_template"><?php echo $BL['be_admin_struct_template'] ?></label>
		<select name="recipe_template" id="recipe_template" class="form-control">
<?php

// templates for recipes
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/recipe');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		if(isset($content['recipe']['template']) && $val == $content['recipe']['template']) {
			$selected_val = ' selected="selected"';
		} else {
			$selected_val = '';
		}
		$val = htmlspecialchars($val);
		echo '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
	}
}

?>
		</select>
	</div>

	<div class="col-12">
		<label class="form-label" for="recipe_ingredients">Zutaten</label>
		<textarea name="recipe_ingredients" id="recipe_ingredients" cols="40" rows="6" class="form-control autosize"><?php echo html($content['recipe']['ingredients']) ?></textarea>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="recipe_time">Zuber.Zeit</label>
		<div class="d-flex flex-wrap align-items-center gap-3">
			<div class="d-flex align-items-center gap-2">
				<input name="recipe_time" type="text" id="recipe_time" class="form-control w-auto" value="<?php echo empty($content['recipe']['time']) ? '' : intval($content['recipe']['time']) ?>" onkeyup="this.value=int_only(this.value);" size="5">
				<span class="small fg-secondary"><?php echo $BL['be_date_minutes'] ?></span>
			</div>
			<div class="d-flex align-items-center gap-2 flex-grow-1">
				<span class="small fg-secondary"><?php echo $BL['be_cnt_additional'] ?></span>
				<input name="recipe_time_add" type="text" id="recipe_time_add" class="form-control" value="<?php echo html($content['recipe']['time_add']) ?>" size="40">
			</div>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="recipe_calorificvalue">N&auml;hrwert</label>
		<div class="d-flex flex-wrap align-items-center gap-3">
			<div class="d-flex align-items-center gap-2">
				<input name="recipe_calorificvalue" type="text" id="recipe_calorificvalue" class="form-control w-auto" value="<?php echo empty($content['recipe']['calorificvalue']) ? '' : intval($content['recipe']['calorificvalue']) ?>" size="5" onkeyup="this.value=int_only(this.value);">
				<span class="small fg-secondary">kJ</span>
			</div>
			<div class="d-flex align-items-center gap-2 flex-grow-1">
				<span class="small fg-secondary"><?php echo $BL['be_cnt_additional'] ?></span>
				<input name="recipe_calorificvalue_add" type="text" id="recipe_calorificvalue_add" class="form-control" value="<?php echo html($content['recipe']['calorificvalue_add']) ?>" size="40">
			</div>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label">Schwierigkeit</div>
		<div class="d-flex flex-wrap gap-4">
<?php
for($recipe_severity = 1; $recipe_severity <= 5; $recipe_severity++) {
	echo '<div class="form-field">';
	echo '<input class="radio" name="recipe_severity" id="recipe_severity_' . $recipe_severity . '" type="radio" value="' . $recipe_severity . '" ';
	is_checked($recipe_severity, $content['recipe']['severity']);
	echo '>';
	echo '<label for="recipe_severity_' . $recipe_severity . '">' . $recipe_severity . '</label>';
	echo '</div>';
}
?>
		</div>
	</div>

	<div class="col-12">
		<div class="form-label">Zubereitung</div>
<?php

$wysiwyg_editor = array(
	'value'		=> $content['recipe']['preparation'],
	'field'		=> 'recipe_preparation',
	'height'	=> '250px',
	'width'		=> '100%',
	'rows'		=> '10',
	'editor'	=> $_SESSION["WYSIWYG_EDITOR"],
	'lang'		=> 'en'
);

include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';

?>
	</div>

</div>
