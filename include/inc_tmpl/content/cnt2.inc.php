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


//images

$caption_box    = '';
$img_thumbs     = '';
$imgx           = 0;

if(empty($template_default['imagegallery_default_column'])) {
    $template_default['imagegallery_default_column'] = 1;
} else {
    $template_default['imagegallery_default_column'] = intval($template_default['imagegallery_default_column']);
    if(empty($template_default['imagegallery_default_column'])) {
        $template_default['imagegallery_default_column'] = 1;
    }
}

$template_default['imagegallery_default_width']  = $template_default['imagegallery_default_width'] ?? '';
$template_default['imagegallery_default_height'] = $template_default['imagegallery_default_height'] ?? '';
$template_default['imagegallery_default_space']  = $template_default['imagegallery_default_space'] ?? '';

if(!isset($content['image_list']) || !is_array($content['image_list'])) {

    $content['image_list'] = array();

}

$content['image_list'] += array(

    'pos'       => 0,
    'width'     => $template_default['imagegallery_default_width'],
    'height'    => $template_default['imagegallery_default_height'],
    'col'       => $template_default['imagegallery_default_column'],
    'space'     => $template_default['imagegallery_default_space'],
    'zoom'      => 0,
    'caption'   => '',
    'lightbox'  => 0,
    'nocaption' => 0,
    'crop'      => 0,
    'random'    => 0,
    'limit'     => 0

);

if(!isset($content['image_list']['limit'])) {
    $content['image_list']['limit'] = 0;
}

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
        <select name="template" id="template" class="form-control">
<?php

    echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for the image table content part
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/imagetable');
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
        <label class="form-label" for="cimage_list"><?php echo $BL['be_cnt_image'] ?></label>
        <div class="d-flex flex-wrap align-items-start gap-3">
            <select name="cimage_list[]" size="<?php echo isset($content["image_list"]) && count($content["image_list"]) ? count($content["image_list"])+5 : 5 ?>" multiple="multiple" class="form-control w-auto" id="cimage_list">
<?php
if(isset($content['image_list']['images']) && is_array($content['image_list']['images']) && count($content['image_list']['images'])) {

    // browse images and list available
    // will be visible only when aceessible
    foreach($content['image_list']['images'] as $key => $value) {

        $caption_box .= html($content['image_list']['images'][$key][6])."\n";

        // 0   :1       :2   :3        :4    :5     :6      :7       :8
        // dbid:filename:hash:extension:width:height:caption:position:zoom
        $thumb_image = get_cached_image(array(
            "target_ext"    =>  $content['image_list']['images'][$key][3],
            "image_name"    =>  $content['image_list']['images'][$key][2] . '.' . $content['image_list']['images'][$key][3],
            "thumb_name"    =>  md5($content['image_list']['images'][$key][2].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
        ));

        if($thumb_image != false) {

            // image found
            echo '<option value="' . $content['image_list']['images'][$key][0] . '">';
            $img_name = html($content['image_list']['images'][$key][1]);
            echo $img_name . "</option>\n";

            $img_thumbs .= '<img src="' . $thumb_image['src'] .'" '.$thumb_image[3].' alt="'.$img_name.'" class="img-fluid">';

            $imgx++;
        }

    }

}

?>
            </select>
            <div class="d-flex flex-column gap-2">
                <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=1&amp;target=nolist');"><?php echo $BL['be_cnt_openimagebrowser'] ?></a>
                <div class="d-flex gap-2">
<?php echo backend_action('#', 'chevron-up', $BL['be_cnt_sortup'], 'normal', ' onclick="moveOptionUp(document.articlecontent.cimage_list);return false;"') ?>
<?php echo backend_action('#', 'chevron-down', $BL['be_cnt_sortdown'], 'normal', ' onclick="moveOptionDown(document.articlecontent.cimage_list);return false;"') ?>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delimage'], 'normal', ' onclick="removeSelectedOptions(document.articlecontent.cimage_list);return false;"', 'danger') ?>
                </div>
            </div>
<?php

if($img_thumbs) {
    echo '<div class="d-flex flex-wrap align-items-start gap-2">' . $img_thumbs . '</div>';
}

?>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cimage_pos"><?php echo $BL['be_cnt_position'] ?></label>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <select name="cimage_pos" id="cimage_pos" class="form-control w-auto">
                <option value="0" <?php is_selected(0, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos0'] ?></option>
                <option value="1" <?php is_selected(1, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos1'] ?></option>
                <option value="2" <?php is_selected(2, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos2'] ?></option>
                <option value="3" <?php is_selected(3, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos3'] ?></option>
                <option value="4" <?php is_selected(4, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos4'] ?></option>
                <option value="5" <?php is_selected(5, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos5'] ?></option>
                <option value="6" <?php is_selected(6, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos6'] ?></option>
                <option value="7" <?php is_selected(7, $content['image_list']['pos']) ?>><?php echo $BL['be_cnt_pos7'] ?></option>
            </select>
            <div class="form-field">
                <input class="check" type="checkbox" name="cimage_usetable" id="cimage_usetable" value="1" <?php is_checked(1, empty($content['image_list']['usetable']) ? 0 : 1); ?>>
                <label for="cimage_usetable"><?php echo $BL['be_admin_page_table'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cimage_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                <input name="cimage_width" type="text" id="cimage_width" class="form-control" size="4" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($content['image_list']['width']) ? $template_default['imagegallery_default_width'] : $content['image_list']['width']; ?>">
            </div>
            <div>
                <label class="form-label" for="cimage_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                <input name="cimage_height" type="text" id="cimage_height" class="form-control" size="4" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($content['image_list']['height']) ? $template_default['imagegallery_default_height'] : $content['image_list']['height']; ?>">
            </div>
            <div class="form-field">
                <input class="check" type="checkbox" name="cimage_crop" id="cimage_crop" value="1" <?php is_checked(1, $content['image_list']['crop']); ?>>
                <label for="cimage_crop"><?php echo $BL['be_image_crop'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cimage_limit"><?php echo $BL['limit_image_from_list'] ?></label>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <select name="cimage_limit" id="cimage_limit" class="form-control w-auto">
                <option value="0"<?php is_selected(0, $content['image_list']['limit']); ?>><?php echo $BL['be_ftptakeover_all'] ?></option>
<?php
// take max images from list
if($imgx) {
    $max_limit = $imgx + 10;
} else {
    $max_limit = 10;
}
for($max_image_limit = 1; $max_image_limit <= $max_limit; $max_image_limit++) {

    echo '<option value="'.$max_image_limit.'" ';
    is_selected($max_image_limit, $content['image_list']['limit']);
    echo '>'.$max_image_limit."</option>\n";
}
?>
            </select>
            <div class="form-field">
                <input class="check" name="cimage_random" type="checkbox" id="cimage_random" value="1" <?php is_checked(1, $content['image_list']['random']); ?>>
                <label for="cimage_random"><?php echo $BL['random_image'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cimage_col"><?php echo $BL['be_cnt_column'] ?></label>
                <select name="cimage_col" id="cimage_col" class="form-control w-auto">
<?php

// list select menu for max image columns
for($max_image_col = 1; $max_image_col <= 25; $max_image_col++) {

    echo '<option value="'.$max_image_col.'" ';
    is_selected($max_image_col, $content['image_list']['col']);
    echo '>'.$max_image_col."</option>\n";

}

?>
                </select>
            </div>
            <div>
                <label class="form-label" for="cimage_space"><?php echo $BL['be_cnt_imagespace'] ?></label>
                <input name="cimage_space" type="text" id="cimage_space" class="form-control" size="2" maxlength="3" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($content['image_list']['space']) ? $template_default['imagegallery_default_space'] : $content['image_list']['space']; ?>">
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_behavior'] ?></div>
        <div class="d-flex flex-wrap gap-4">
            <div class="form-field">
                <input class="check" name="cimage_zoom" type="checkbox" id="cimage_zoom" value="1" <?php is_checked(1, $content['image_list']['zoom']); ?>>
                <label for="cimage_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
            </div>
            <div class="form-field">
                <input class="check" name="cimage_lightbox" type="checkbox" id="cimage_lightbox" value="1" <?php is_checked(1, $content['image_list']['lightbox']); ?> onchange="if(this.checked){getObjectById('cimage_zoom').checked=true;}">
                <label for="cimage_lightbox"><?php echo $BL['be_cnt_lightbox'] ?></label>
            </div>
            <div class="form-field">
                <input class="check" name="cimage_nocaption" type="checkbox" id="cimage_nocaption" value="1" <?php is_checked(1, $content['image_list']['nocaption']); ?>>
                <label for="cimage_nocaption"><?php echo $BL['be_cnt_imglist_nocaption'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="cimage_caption"><?php echo $BL['be_cnt_caption'] ?></label>
        <textarea name="cimage_caption" cols="40" rows="3" wrap="off" class="form-control autosize" id="cimage_caption"><?php echo $caption_box; ?></textarea>
        <div class="small fg-secondary mt-3">
            <?php echo $BL['be_cnt_caption']; ?>
            |
            <?php echo $BL['be_caption_alt']; ?>
            |
            <?php echo $BL['be_admin_page_link']; ?> <em><?php echo $BL['be_cnt_target']; ?></em>
            |
            <?php echo $BL['be_caption_title']; ?>
            |
            <?php echo $BL['be_copyright']; ?>&nbsp;&crarr;&nbsp;&hellip;
        </div>
    </div>

</div>
