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


// Page / ext. Content

if(!isset($content["page_file"]) || !is_array($content["page_file"])) {

	$content["page_file"]["source"] = 0;
	$content["page_file"]["pfile"] = '';

}

?>
<div class="row g-4">

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_pages_from'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" name="cpage_source" type="radio" id="cpage_source0" value="0"<?php is_checked(0, $content["page_file"]["source"]) ?>>
				<label for="cpage_source0"><?php echo $BL['be_cnt_pages_fromfile'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="cpage_source" type="radio" id="cpage_source1" value="1"<?php is_checked(1, $content["page_file"]["source"]) ?>>
				<label for="cpage_source1"><?php echo $BL['be_cnt_pages_manually'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="cpage_custom"><?php echo $BL['be_cnt_pages_cust'] ?></label>
		<input name="cpage_custom" type="text" class="form-control" id="cpage_custom" value="<?php echo  html($content["page_file"]["pfile"]) ?>" size="40">
	</div>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_pages_select'] ?></div>
		<div class="border rounded p-3 overflow-auto" style="max-height: 220px">
<?php

// browse pages subdirectory

browse_pages_dir($phpwcms['content_path'].'pages');

function browse_pages_dir($dir) {

	$da = array(); //directory array
	$fa = array(); //file array
	if(is_dir($dir)) {
		$ph = opendir($dir);
		while($pf = readdir($ph)) {
   			if(!str_starts_with($pf, '.')) {

				if(is_dir($dir.'/'.$pf)) {

					$da[] = $pf; //add $pf to folder array for current dir

				} elseif( preg_match('/(\.html|\.htm|\.txt|\.php|\.inc|\.tmpl)$/i', $pf) ) {

					$fa[] = $pf; //add $pf to file array for current dir

				}
			}
		}
		closedir($ph);

		// list files
		if(count($fa)) {
			$x = 0;
			foreach($fa as $value) {
				if(!$x) {
					echo '<div class="small fg-secondary pt-2 pb-1">' . html($dir) . '</div>';
				}
				$page_id = 'cpage_file_' . substr(md5($dir.'/'.$value), 0, 8);
				echo '<div class="form-field">';
				echo '<input class="radio" name="cpage_file" type="radio" id="' . $page_id . '" value="' . html($dir.'/'.$value) . '"';
				if($GLOBALS['content']['page_file']['pfile'] == ($dir.'/'.$value)) {
					echo ' checked="checked"';
				}
				echo '>';
				echo '<label for="' . $page_id . '">' . html($value) . '</label>';
				echo '</div>';
				$x++;
			}
		}

		// check all subdirs
		if(count($da)) {
			foreach($da as $value) browse_pages_dir($dir.'/'.$value);
		}

	}
}

?>
		</div>
	</div>
<?php
if (empty($phpwcms['enable_inline_php'])) {
    echo '<div class="col-12"><small class="fg-danger">' . $BL['be_cnt_pages_php_render_warning'] . '</small></div>';
}
?>

</div>
