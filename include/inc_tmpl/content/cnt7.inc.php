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


//file list

if(empty($content["file_descr"])) $content["file_descr"] = '';
$content['file']['direct_download'] = empty($content['file']['direct_download']) ? 0 : 1;

?>
<div class="row g-4">

	<div class="col-12 md:col-6">
		<label class="form-label" for="cfile_template"><?php echo $BL['be_admin_struct_template'] ?></label>
		<select name="cfile_template" id="cfile_template" class="form-control">
<?php

echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for the file list content part
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/filelist');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		if(isset($content['file_template']) && $val == $content['file_template']) {
			$selected_val = ' selected="selected"';
		} else {
			$selected_val = '';
		}
		$val = htmlspecialchars($val);
		echo "  " . '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
	}
}

if(is_file(PHPWCMS_ROOT.'/'.PHPWCMS_FILES.'.htaccess') && ($content['file']['direct_download_deny'] = file_get_contents(PHPWCMS_ROOT.'/'.PHPWCMS_FILES.'.htaccess'))) {
    $content['file']['direct_download_deny'] = strtolower($content['file']['direct_download_deny']);
    if(str_contains($content['file']['direct_download_deny'], 'deny')) {
        $content['file']['direct_download_deny'] = true;
    } else {
        $content['file']['direct_download_deny'] = false;
    }
} else {
    $content['file']['direct_download_deny'] = false;
}


?>
		</select>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_cnt_download'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="check" name="cfile_direct" id="cfile_direct" type="checkbox" value="1" <?php
					is_checked(1, $content['file']['direct_download']);
					if($content['file']['direct_download_deny'] && !$content['file']['direct_download']) {
						echo ' disabled="disabled"';
					}
				?>>
				<label for="cfile_direct"><?php echo $BL['be_cnt_download_direct'] ?></label>
			</div>
<?php
	if($content['file']['direct_download_deny']) {
		echo '<div class="alert theme-warning w-100" role="alert">';
		printf($BL['be_filedownload_direct_blocked'], PHPWCMS_ROOT.'/'.PHPWCMS_FILES.'.htaccess');
		echo '</div>';
	}
?>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="cfile_list"><?php echo $BL['be_cnt_files'] ?></label>
		<div class="d-flex flex-wrap align-items-start gap-3">
			<select name="cfile_list[]" size="8" multiple class="form-control w-auto" id="cfile_list">
<?php

        if(isset($content["file_list"]) && is_array($content["file_list"]) && count($content["file_list"])) {
    		$fx  = 0;
    		$fxa = "";
    		$fxb = array();
    		foreach($content["file_list"] as $key => $value) {
    			if($fx) $fxa .= " OR ";
    			$fxa .= "f_id=".intval($value);
    			$fxb[$key]["fid"] = intval($value);
    			$fx++;
    		}
    		if($fx) {
    			$file_sql = "SELECT f_id, f_name FROM ".DB_PREPEND."phpwcms_file WHERE f_public=1 AND f_aktiv=1 AND f_kid=1 AND f_trash=0 AND (".$fxa.")";
    			$file_result = _dbQuery($file_sql);
    			if(isset($file_result[0]['f_id'])) {
    				foreach($file_result as $file_row) {
    					foreach($fxb as $key => $value) {
    						if($value["fid"] == $file_row['f_id']) {
    							$fxb[$key]["fname"] = html($file_row['f_name']);
    						}
    					}
    				}
    			}
    			foreach($fxb as $key => $value) {
    				if(!empty($value["fname"])) {
    					echo "<option value=\"". $value["fid"]."\">". $value["fname"]."</option>\n";
    				}
    			}
    			unset($fxb, $content["file_list"]);
    		}
	    }

?>
			</select>
			<div class="d-flex flex-column gap-2">
				<a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=4&amp;target=nolist')"><?php echo $BL['be_cnt_openfilebrowser'] ?></a>
				<div class="d-flex gap-2">
<?php echo backend_action('#', 'chevron-up', $BL['be_cnt_sortup'], 'normal', ' onclick="moveOptionUp(document.articlecontent.cfile_list);return false;"') ?>
<?php echo backend_action('#', 'chevron-down', $BL['be_cnt_sortdown'], 'normal', ' onclick="moveOptionDown(document.articlecontent.cfile_list);return false;"') ?>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delfile'], 'normal', ' onclick="removeSelectedOptions(document.articlecontent.cfile_list);return false;"', 'danger') ?>
				</div>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="cfile_descr"><?php echo $BL['be_cnt_description'] ?></label>
		<textarea name="cfile_descr" cols="40" rows="5" class="form-control autosize" id="cfile_descr"><?php

	if(!empty($content["file_descr"]) && (str_starts_with($content["file_descr"], "\r") || str_starts_with($content["file_descr"], "\n"))) {
		echo ' ';
	}
	echo html($content["file_descr"]);

  ?></textarea>
		<div class="small fg-secondary mt-3">
			<?php echo $BL['be_cnt_description']; ?>
			|
			<?php echo $BL['be_fprivedit_filename']; ?>
			|
			<?php echo $BL['be_caption_file_title']; ?>
			|
			<?php echo $BL['be_admin_page_link']; ?> <em><?php echo $BL['be_cnt_target']; ?></em>
			|
			<?php echo $BL['be_caption_file_imagesize']; ?>
			|
			<?php echo $BL['be_copyright']; ?>&nbsp;&crarr;&nbsp;&hellip;
		</div>
	</div>

	<div class="col-12">
<?php

$wysiwyg_editor = array(
	'value'		=> $content["html"] ?? '',
	'field'		=> 'chtml',
	'height'	=> '250px',
	'width'		=> '100%',
	'rows'		=> '15',
	'editor'	=> $_SESSION["WYSIWYG_EDITOR"],
	'lang'		=> 'en'
);

include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';


?>
	</div>

</div>
