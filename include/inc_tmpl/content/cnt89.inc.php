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


// content 89: poll         jens

$caption_box = '';
$img_thumbs = '';

if(!isset($content['poll_list']) || !is_array($content['poll_list'])) {
    $content['poll_list'] = array();
}
if(!isset($content['poll_list']['width'])) {
    $content['poll_list']['width'] = '';
}
if(!isset($content['poll_list']['height'])) {
    $content['poll_list']['height'] = '';
}
if(!isset($content['poll_list']['zoom'])) {
    $content['poll_list']['zoom'] = 0;
}
if(empty($content["poll_form"])) {
    $content["poll_form"] = array();
}
if(!isset($content['poll_text']['poll_buttonstyle'])) {
    $content['poll_text']['poll_buttonstyle'] = '';
}
if(!isset($content['poll_text']['poll_buttontext'])) {
    $content['poll_text']['poll_buttontext'] = '';
}
if(!empty($content["poll_form"]["choice"]) && is_array($content["poll_form"]["choice"]) && count($content["poll_form"]["choice"])) {
    foreach($content["poll_form"]["choice"] as $key => $value) {
        $caption_box .= html($content["poll_form"]["choice"][$key])."\n";
    }
} else {
    $content["poll_form"]["choice"] = array();
}

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="cpoll_buttonstyle">Class</label>
        <input name="cpoll_buttonstyle" type="text" class="form-control" id="cpoll_buttonstyle" value="<?php echo html($content['poll_text']['poll_buttonstyle']) ?>">
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cpoll_buttontext">Button</label>
        <input name="cpoll_buttontext" type="text" class="form-control" id="cpoll_buttontext" value="<?php echo html($content['poll_text']['poll_buttontext']) ?>">
    </div>

    <div class="col-12">
        <label class="form-label" for="cpoll_caption">choices</label>
        <textarea name="cpoll_caption" cols="40" rows="8" wrap="off" class="form-control autosize" id="cpoll_caption"><?php echo $caption_box; ?></textarea>
    </div>

    <div class="col-12">
        <label class="form-label" for="cimage_list"><?php echo $BL['be_cnt_image'] ?></label>
        <div class="d-flex flex-wrap align-items-start gap-3">
            <select name="cimage_list[]" size="<?php echo !empty($content["poll_select"]) && is_array($content["poll_select"]) ? count($content["poll_select"])+5 : 5 ?>" multiple="multiple" class="form-control w-auto" id="cimage_list">
<?php

if(isset($content['poll_list']['images']) && is_array($content['poll_list']['images']) && count($content['poll_list']['images'])) {
    foreach($content['poll_list']['images'] as $key => $value) {

        // 0   :1       :2   :3        :4    :5     :6      :7       :8
        // dbid:filename:hash:extension:width:height:caption:position:zoom
        $thumb_image = get_cached_image(array(
            "target_ext" => $content['poll_list']['images'][$key][3],
            "poll_name"  => $content['poll_list']['images'][$key][2] . '.' . $content['poll_list']['images'][$key][3],
            "thumb_name" => md5($content['poll_list']['images'][$key][2].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
        ));

        if($thumb_image != false) {
            echo '<option value="' . $content['poll_list']['images'][$key][0] . '">';
            $img_name = html($content['poll_list']['images'][$key][1]);
            echo $img_name . "</option>\n";

            $img_thumbs .= '<img src="'.$thumb_image['src'].'" '.$thumb_image[3].' alt="'.$img_name.'" title="'.$img_name.'" class="img-fluid">';
        }
    }
}

?>
            </select>
            <div class="d-flex flex-column gap-2">
                <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=1&amp;target=nolist')"><?php echo $BL['be_cnt_openimagebrowser'] ?></a>
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

    <div class="col-12">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cpoll_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                <input name="cpoll_width" type="text" class="form-control" id="cpoll_width" size="3" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content['poll_list']['width'] ?>">
            </div>
            <div>
                <label class="form-label" for="cpoll_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                <input name="cpoll_height" type="text" class="form-control" id="cpoll_height" size="3" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content['poll_list']['height'] ?>">
            </div>
            <div class="form-field">
                <input class="check" name="cpoll_zoom" type="checkbox" id="cpoll_zoom" value="1" <?php is_checked(1, $content['poll_list']['zoom']); ?>>
                <label for="cpoll_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
            </div>
        </div>
    </div>

</div>
