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


//image with text

$template_default['article']['image_default_width'] = $template_default['article']['image_default_width'] ?? '';
$template_default['article']['image_default_height']= $template_default['article']['image_default_height'] ?? '';

if(empty($content['cimage']['cimage_crop'])) {
    $content['cimage']['cimage_crop'] = 0;
}

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
        <select name="template" id="template" class="form-control">
<?php

    echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for the image text content part
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/imagetext');
if(is_array($tmpllist) && count($tmpllist)) {
    foreach($tmpllist as $val) {
        $selected_val = (isset($content["template"]) && $val == $content["template"]) ? ' selected="selected"' : '';
        $val = html($val);
        echo "  " . '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
    }
}

?>
        </select>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_htmltext'] ?></div>
<?php

$wysiwyg_editor = array(
    'value'     => $content["text"] ?? '',
    'field'     => 'ctext',
    'height'    => '650px',
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
            <input name="cimage_id" type="hidden" value="<?php echo $content["image_id"] ?? ''; ?>">
        </div>
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cimage_pos"><?php echo  $BL['be_cnt_position'] ?></label>
        <select name="cimage_pos" id="cimage_pos" class="form-control">
            <option value="0" <?php
            if(!isset($content["image_pos"])) $content["image_pos"] = 0;

            is_selected(0, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos0'] ?></option>
            <option value="1" <?php is_selected(1, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos1'] ?></option>
            <option value="2" <?php is_selected(2, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos2'] ?></option>
            <option value="3" <?php is_selected(3, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos3'] ?></option>
            <option value="4" <?php is_selected(4, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos4'] ?></option>
            <option value="5" <?php is_selected(5, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos5'] ?></option>
            <option value="6" <?php is_selected(6, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos6'] ?></option>
            <option value="7" <?php is_selected(7, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos7'] ?></option>
            <option value="8" <?php is_selected(8, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos8'] ?></option>
            <option value="9" <?php is_selected(9, $content["image_pos"]) ?>><?php echo  $BL['be_cnt_pos9'] ?></option>
        </select>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cimage_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                <input name="cimage_width" type="text" id="cimage_width" class="form-control" size="4" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($content["image_width"]) ? $template_default['article']['image_default_width'] : $content["image_width"] ?>">
            </div>
            <div>
                <label class="form-label" for="cimage_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                <input name="cimage_height" type="text" id="cimage_height" class="form-control" size="4" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($content["image_height"]) ? $template_default['article']['image_default_height'] : $content["image_height"] ?>">
            </div>
            <div class="form-field">
                <input class="check" type="checkbox" name="cimage_crop" id="cimage_crop" value="1" <?php is_checked(1, $content['cimage']['cimage_crop']); ?>>
                <label for="cimage_crop"><?php echo $BL['be_image_crop'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_behavior'] ?></div>
        <div class="d-flex flex-wrap gap-4">
            <div class="form-field">
                <input class="check" name="cimage_zoom" type="checkbox" id="cimage_zoom" value="1" <?php is_checked(1, empty($content["image_zoom"]) ? 0 : 1); ?>>
                <label for="cimage_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
            </div>
            <div class="form-field">
                <input class="check" name="cimage_lightbox" type="checkbox" id="cimage_lightbox" value="1" <?php is_checked(1, empty($content['cimage']['cimage_lightbox']) ? 0 : 1); ?> onchange="if(this.checked){getObjectById('cimage_zoom').checked=true;}">
                <label for="cimage_lightbox"><?php echo $BL['be_cnt_lightbox'] ?></label>
            </div>
            <div class="form-field">
                <input class="check" name="cimage_nocaption" type="checkbox" id="cimage_nocaption" value="1" <?php is_checked(1, empty($content['cimage']['cimage_nocaption']) ? 0 : 1); ?>>
                <label for="cimage_nocaption"><?php echo $BL['be_cnt_imglist_nocaption'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="cimage_caption"><?php echo $BL['be_cnt_caption'] ?></label>
        <div class="d-flex flex-wrap align-items-start gap-3">
            <div>
                <textarea name="cimage_caption" cols="30" rows="3" class="form-control autosize" id="cimage_caption" style="min-width: 300px"><?php
                        if(isset($content["image_caption"])) {
                            echo html($content["image_caption"]);
                        }
                    ?></textarea>
                <div class="small fg-secondary">
                    <?php echo $BL['be_cnt_caption']; ?>
                    |
                    <?php echo $BL['be_caption_alt']; ?>
                    |
                    <?php echo $BL['be_admin_page_link']; ?> <em><?php echo $BL['be_cnt_target']; ?></em>
                    |
                    <?php echo $BL['be_caption_title']; ?>
                    |
                    <?php echo $BL['be_copyright']; ?>
                </div>
            </div>
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
