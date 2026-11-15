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


//bullet list table

?>
<div class="row g-4">

	<div class="col-12 md:col-6">
		<label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
		<select name="template" id="template" class="form-control">
<?php

	echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for frontend login
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/bulletlist');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		$selected_val = (isset($content["template"]) && $val == $content["template"]) ? ' selected="selected"' : '';
		$val = html($val);
		echo "\t" . '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
	}
}

?>
		</select>
	</div>

	<div class="col-12">
		<label class="form-label" for="ctext"><?php echo $BL['be_cnt_bullet'] ?></label>
		<textarea name="ctext" rows="20" class="form-control autosize" id="ctext"><?php echo $content["text"] ?? '' ?></textarea>
	</div>

</div>
