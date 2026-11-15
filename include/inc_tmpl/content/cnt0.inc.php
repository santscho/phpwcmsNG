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


// Plain Text

initMootools('1.2');

if(empty($content['ctext_format'])) {
	$content['ctext_format'] = 'plain';
}

?>
<div class="row g-4">

	<div class="col-12 md:col-6">
		<label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
		<select name="template" id="template" class="form-control">
<?php

	echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for frontend login
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/plaintext');
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
		<div class="form-label"><?php echo $BL['be_media_format'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" name="ctext_format" type="radio" id="ctext_format0" value="plain"<?php is_checked('plain', $content['ctext_format']); ?>>
				<label for="ctext_format0"><?php echo $BL['be_ctype_plaintext'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="ctext_format" type="radio" id="ctext_format1" value="markdown"<?php is_checked('markdown', $content['ctext_format']); ?>>
				<label for="ctext_format1">MarkDown</label>
				<a href="http://en.wikipedia.org/wiki/Markdown" target="_blank" class="link small" data-bs-toggle="tooltip" title="Wikipedia: Markdown">(?)</a>
			</div>
			<div class="form-field">
				<input class="radio" name="ctext_format" type="radio" id="ctext_format2" value="textile"<?php is_checked('textile', $content['ctext_format']); ?>>
				<label for="ctext_format2">Textile</label>
				<a href="http://en.wikipedia.org/wiki/Textile_%28markup_language%29" target="_blank" class="link small" data-bs-toggle="tooltip" title="Wikipedia: Textile">(?)</a>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="ctext"><?php echo $BL['be_cnt_plaintext'] ?></label>
		<textarea name="ctext" rows="20" class="form-control autosize" id="ctext" style="font-family: var(--bs-font-mono)"><?php
		if(empty($content["text"])) {

			echo '';

		} else {

			if(substr($content["text"], 0, 1) === LF || str_starts_with($content["text"], "\r")) {
				echo ' '; // keep 1st linebreak;
			}
			echo html($content["text"]);

		}

	?></textarea>
	</div>

</div>
