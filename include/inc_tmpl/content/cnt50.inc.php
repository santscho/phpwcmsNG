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


// Reference

if(!isset($content['reference']) || !is_array($content['reference'])) {
    $content['reference'] = array();
}

$content['reference'] += array(
    "text"          => '',
    'tmpl'          => '',
    "select"        => array(),
    "list"          => array(),
    "caption"       => '',
    "zoom"          => 0,
    "width"         => '',
    "height"        => '',
    "border"        => '',
    "pos"           => 0,
    "blockwidth"    => '',
    "blockheight"   => '',
    "space"         => '',
    "listborder"    => '',
    "basis"         => 0
);

$imgx=0;
$img_thumbs = '';

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="creference_tmpl"><?php echo $BL['be_admin_struct_template'] ?></label>
        <select name="creference_tmpl" id="creference_tmpl" class="form-control">
<?php

    // templates for Reference
    echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;
    $tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/reference');
    if(is_array($tmpllist) && count($tmpllist)) {
        foreach($tmpllist as $val) {
            $val = htmlspecialchars($val);
            echo '<option value="' . $val . '"' . ($val == $content["reference"]['tmpl'] ? ' selected="selected"' : '' ).'>' . $val . "</option>\n";
        }
    }

?>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label" for="creference_text"><?php echo $BL['be_cnt_plaintext'] ?></label>
        <textarea name="creference_text" rows="15" wrap="VIRTUAL" class="form-control autosize" id="creference_text"><?php echo  $content['reference']["text"] ?></textarea>
    </div>

    <div class="col-12">
        <label class="form-label" for="cimage_list"><?php echo $BL['be_cnt_image'] ?></label>
        <div class="d-flex flex-wrap align-items-start gap-3">
            <select name="cimage_list[]" size="<?php echo isset($content['reference']["select"]) && count($content['reference']["select"]) ? count($content['reference']["select"]) + 5 : 5; ?>" multiple="multiple" class="form-control w-auto" id="cimage_list">
<?php
                    if(is_array($content['reference']["list"]) && count($content['reference']["list"])) {

                        foreach($content['reference']["list"] as $key => $value) {

                            $thumb_image = get_cached_image(array(
                                "target_ext"    =>  $content['reference']["list"][$key][3],
                                "image_name"    =>  $content['reference']["list"][$key][2] . '.' . $content['reference']["list"][$key][3],
                                "thumb_name"    =>  md5($content['reference']["list"][$key][2].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
                            ));

                            if($thumb_image != false) {

                                echo "<option value=\"".$content['reference']["list"][$key][0]."\">";
                                $img_name = html($content['reference']["list"][$key][1]);
                                echo $img_name."</option>\n";

                                $img_thumbs .= '<img src="' . $thumb_image['src'] .'" '.$thumb_image[3].' alt="'.$img_name.'" class="img-fluid">';

                                $imgx++;

                            }

                        }

                    }
                    ?>
            </select>
            <div class="d-flex flex-column gap-2">
                <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=3&amp;target=nolist')"><?php echo $BL['be_cnt_openimagebrowser'] ?></a>
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
        <label class="form-label" for="creference_caption"><?php echo $BL['be_cnt_caption'] ?></label>
        <textarea name="creference_caption" cols="40" rows="5" wrap="off" class="form-control autosize" id="creference_caption"><?php echo html($content['reference']["caption"]) ?></textarea>
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

    <div class="col-12">
        <div class="form-field">
            <input class="check" name="creference_zoom" type="checkbox" id="creference_zoom" value="1" <?php is_checked(1, $content['reference']["zoom"]); ?>>
            <label for="creference_zoom"><?php echo $BL['be_cnt_reference_zoom'].': '.$BL['be_cnt_enlarge'] ?></label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_reference_largetext']; ?></div>
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="creference_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                <input name="creference_width" type="text" class="form-control" id="creference_width" size="5" maxlength="5" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content['reference']["width"] ?>">
            </div>
            <div>
                <label class="form-label" for="creference_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                <input name="creference_height" type="text" class="form-control" id="creference_height" size="5" maxlength="5" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content['reference']["height"] ?>">
            </div>
            <div>
                <label class="form-label" for="creference_border"><?php echo $BL['be_cnt_reference_border'] ?></label>
                <input name="creference_border" type="text" class="form-control" id="creference_border" size="3" maxlength="3" onkeyup="if(!parseInt(this.value,10)) this.value='0';" value="<?php echo $content['reference']["border"] ?>">
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_reference_aligntext'] ?> / <?php echo $BL['be_cnt_reference_basis'] ?></div>
        <div class="d-flex flex-wrap align-items-center gap-4">
            <div class="form-field">
                <input class="radio" name="creference_basis" id="creference_basis0" type="radio" value="0" <?php is_checked(0, $content["reference"]["basis"]); ?>>
                <label for="creference_basis0"><?php echo $BL['be_cnt_reference_horizontal'] ?></label>
            </div>
            <div class="form-field">
                <input class="radio" name="creference_basis" id="creference_basis1" type="radio" value="1" <?php is_checked(1, $content["reference"]["basis"]); ?>>
                <label for="creference_basis1"><?php echo $BL['be_cnt_reference_vertical'] ?></label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="creference_pos"><?php echo $BL['be_cnt_position'] ?></label>
                <select name="creference_pos" id="creference_pos" class="form-control w-auto">
                    <option value="0" <?php is_selected(0, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_default'] ?></option>
                    <option value="1" <?php is_selected(1, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_left'].', '.$BL['be_admin_page_top'] ?></option>
                    <option value="2" <?php is_selected(2, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_left'].', '.$BL['be_cnt_reference_middle'] ?></option>
                    <option value="3" <?php is_selected(3, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_left'].', '.$BL['be_admin_page_bottom'] ?></option>
                    <option value="4" <?php is_selected(4, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_center'].', '.$BL['be_admin_page_top'] ?></option>
                    <option value="5" <?php is_selected(5, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_center'].',. '.$BL['be_cnt_reference_middle'] ?></option>
                    <option value="6" <?php is_selected(6, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_center'].', '.$BL['be_admin_page_bottom'] ?></option>
                    <option value="7" <?php is_selected(7, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_right'].', '.$BL['be_admin_page_top'] ?></option>
                    <option value="8" <?php is_selected(8, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_right'].', '.$BL['be_cnt_reference_middle'] ?></option>
                    <option value="9" <?php is_selected(9, $content['reference']["pos"]) ?>><?php echo $BL['be_cnt_right'].', '.$BL['be_admin_page_bottom'] ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_reference_block'] ?></div>
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div class="d-flex align-items-center gap-2">
                <input name="creference_blockwidth" type="text" class="form-control w-auto" id="creference_blockwidth" size="5" maxlength="5" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content['reference']["blockwidth"] ?>">
                <span class="small fg-secondary">x</span>
                <input name="creference_blockheight" type="text" class="form-control w-auto" id="creference_blockheight" size="5" maxlength="5" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content['reference']["blockheight"] ?>">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="creference_space"><?php echo $BL['be_cnt_imagespace'] ?></label>
                <input name="creference_space" type="text" class="form-control w-auto" id="creference_space" size="2" maxlength="2" onkeyup="if(!parseInt(this.value,10)) this.value='0';" value="<?php echo $content['reference']["space"] ?>">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="creference_listborder"><?php echo $BL['be_cnt_reference_border'] ?></label>
                <input name="creference_listborder" type="text" class="form-control w-auto" id="creference_listborder" size="3" maxlength="3" onkeyup="if(!parseInt(this.value,10)) this.value='0';" value="<?php echo $content['reference']["listborder"] ?>">
            </div>
        </div>
    </div>

</div>
