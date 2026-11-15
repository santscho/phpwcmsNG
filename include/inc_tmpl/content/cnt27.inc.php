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

//FAQ

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="faq_template"><?php echo $BL['be_admin_struct_template'] ?></label>
        <select name="faq_template" id="faq_template" class="form-control">
<?php

// templates for recipes
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/faq');
if(is_array($tmpllist) && count($tmpllist)) {
    foreach($tmpllist as $val) {
        if(isset($content['faq']['faq_template']) && $val == $content['faq']['faq_template']) {
            $selected_val = ' selected="selected"';
        } else {
            $selected_val = '';
        }
        $val = html($val);
        echo '  <option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
    }
}

?>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label" for="faq_question"><?php echo $BL['be_cnt_question'] ?></label>
        <textarea name="faq_question" rows="4" class="form-control autosize" id="faq_question"><?php

    echo empty($content["faq_question"]) ? '' : $content["faq_question"];

?></textarea>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_answer'] ?></div>
<?php

$wysiwyg_editor = array(
    'value'     => $content["faq_answer"] ?? '',
    'field'     => 'faq_answer',
    'height'    => '300px',
    'width'     => '100%',
    'rows'      => '15',
    'editor'    => $_SESSION["WYSIWYG_EDITOR"],
    'lang'      => 'en'
);

include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';

?>
    </div>

    <div class="col-12">
        <label class="form-label" for="cimage_name"><?php echo  $BL['be_cnt_image'] ?></label>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <input name="cimage_name" type="text" id="cimage_name" class="form-control w-auto" value="<?php echo  isset($content["image_name"]) ? html($content["image_name"]) : '' ?>" size="40" maxlength="250" onfocus="this.blur()">
            <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=0&amp;target=nolist')"><?php echo  $BL['be_cnt_openimagebrowser'] ?></a>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delimage'], 'normal', ' onclick="document.articlecontent.cimage_name.value=\'\';document.articlecontent.cimage_id.value=\'0\';return false;"', 'danger') ?>
            <input name="cimage_id" type="hidden" value="<?php echo $content["image_id"] ?? '' ?>">
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cimage_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                <input name="cimage_width" type="text" id="cimage_width" class="form-control" size="3" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content["image_width"] ?? '' ?>">
            </div>
            <div>
                <label class="form-label" for="cimage_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                <input name="cimage_height" type="text" id="cimage_height" class="form-control" size="3" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content["image_height"] ?? '' ?>">
            </div>
            <div class="form-field">
                <input class="check" name="cimage_zoom" type="checkbox" id="cimage_zoom" value="1" <?php is_checked(1, $content["image_zoom"] ?? 0); ?>>
                <label for="cimage_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="cimage_caption"><?php echo $BL['be_cnt_caption'] ?></label>
        <div class="d-flex flex-wrap align-items-start gap-3">
            <textarea name="cimage_caption" cols="30" rows="4" class="form-control autosize" id="cimage_caption" style="min-width: 300px"><?php echo  isset($content["image_caption"]) ? html($content["image_caption"]) : '' ?></textarea>
<?php

if(isset($content["image_hash"])) {
    $thumb_image = get_cached_image(array(
        "target_ext"    =>  $content["image_ext"],
        "image_name"    =>  $content["image_hash"] . '.' . $content["image_ext"],
        "thumb_name"    =>  md5($content["image_hash"].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
    ));

    if($thumb_image != false) {
        echo '<img src="' . $thumb_image['src'] .'" alt="" '.$thumb_image[3].' class="img-fluid">';
    }
}

?>
        </div>
    </div>

</div>
