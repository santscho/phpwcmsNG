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


//code

?>
<div class="row g-4">

	<div class="col-12 md:col-6">
		<label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
		<select name="template" id="template" class="form-control">
<?php

	echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for the code content part
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/code');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		$selected_val = (isset($content["template"]) && $val == $content["template"]) ? ' selected="selected"' : '';
		$val = html($val);
		echo "\t<option value=" . $val . "" . $selected_val . ">" . $val . "</option>" . LF;
	}
}

?>
		</select>
	</div>

	<div class="col-12">
		<label class="form-label" for="ccode"><?php echo $BL['be_cnt_code'] ?></label>
		<textarea name="ccode" rows="20" class="form-control font-monospace autosize" id="ccode"><?php

	if(!empty($content["code"])) {

		$firstchar = substr($content["code"], 0, 1);

		if($firstchar == "\r" || $firstchar == "\n") {
			$content["code"] = ' '.$content["code"];
		}
		echo html($content["code"], true);
	}

	?></textarea>
	</div>

</div>
