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


//ecard

$img_thumbs = '';
$caption_box = '';

if(!isset($content["ecard"])) {

    $content["ecard"] = array(

        "subject"   => '',
        "selector"  => 0,
        "onover"    => '',
        "onclick"   => '',
        "onout"     => '',
        "select"    => array(),
        'images'    => array(),
        "pos"       => 0,
        "width"     => '',
        "height"    => '',
        "form"      => false,
        "send"      => '',
        "mail"      => '',
        'col'       => 1,
        'space'     => '',
        'zoom'  => 0

    );

}

?>
<div class="row g-4">

    <div class="col-12">
        <label class="form-label" for="cecard_subject"><?php echo $BL['be_cnt_subject'] ?></label>
        <input name="cecard_subject" type="text" id="cecard_subject" class="form-control" value="<?php echo html($content["ecard"]["subject"]) ?>" size="40" maxlength="250">
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_ecardform_selector'] ?></div>
        <div class="form-field">
            <input class="radio" name="cecard_selector" id="cecard_selector0" type="radio" value="0"<?php is_checked(0, $content["ecard"]["selector"]); ?>>
            <label for="cecard_selector0"><?php echo $BL['be_cnt_ecardform_radiobutton'] ?></label>
        </div>
        <div class="form-field mt-3">
            <input class="radio" name="cecard_selector" id="cecard_selector1" type="radio" value="1"<?php is_checked(1, $content["ecard"]["selector"]); ?>>
            <label for="cecard_selector1"><?php echo $BL['be_cnt_ecardform_javascript'] ?></label>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cecard_over"><?php echo $BL['be_cnt_ecardform_over'] ?></label>
        <input name="cecard_onover" type="text" id="cecard_over" class="form-control" value="<?php echo html($content["ecard"]["onover"]) ?>" size="40">
        <label class="form-label mt-3" for="cecard_onclick"><?php echo $BL['be_cnt_ecardform_click'] ?></label>
        <input name="cecard_onclick" type="text" id="cecard_onclick" class="form-control" value="<?php echo html($content["ecard"]["onclick"]) ?>" size="40">
        <label class="form-label mt-3" for="cecard_onout"><?php echo $BL['be_cnt_ecardform_out'] ?></label>
        <input name="cecard_onout" type="text" id="cecard_onout" class="form-control" value="<?php echo html($content["ecard"]["onout"]) ?>" size="40">
    </div>

    <div class="col-12 border-top pt-4">
        <div class="row g-3">
            <div class="col-12 md:col-6">
                <label class="form-label" for="cimage_list"><?php echo $BL['be_cnt_image'] ?></label>
                <select name="cimage_list[]" size="<?php echo isset($content["ecard"]["select"]) && count($content["ecard"]["select"]) ? count($content["ecard"]["select"])+5 : 5 ?>" multiple="multiple" class="form-control" id="cimage_list">
<?php
if(is_array($content['ecard']['images']) && count($content['ecard']['images'])) {

    // browse images and list available
    // will be visible only when aceessible
    foreach($content['ecard']['images'] as $key => $value) {

        $caption_box .= html($content['ecard']['images'][$key][6])."\n";

        $thumb_image = get_cached_image(array(
            "target_ext"    =>  $content['ecard']['images'][$key][3],
            "image_name"    =>  $content['ecard']['images'][$key][2] . '.' . $content['ecard']['images'][$key][3],
            "thumb_name"    =>  md5($content['ecard']['images'][$key][2].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
        ));

        if($thumb_image != false) {

            // image found
            echo '<option value="' . $content['ecard']['images'][$key][0] . '">';
            $img_name = html($content['ecard']['images'][$key][1]);
            echo $img_name . "</option>\n";

            $img_thumbs .= '<img src="' . $thumb_image['src'] .'" '.$thumb_image[3].' alt="'.$img_name.'" title="'.$img_name.'">';

        }

    }

}

?>
                </select>
            </div>
            <div class="col-12 md:col-6">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=3&amp;target=nolist')"><?php echo $BL['be_cnt_openimagebrowser'] ?></a>
<?php echo backend_action('#', 'chevron-up', $BL['be_cnt_sortup'], 'normal', ' onclick="moveOptionUp(document.articlecontent.cimage_list);return false;"') ?>
<?php echo backend_action('#', 'chevron-down', $BL['be_cnt_sortdown'], 'normal', ' onclick="moveOptionDown(document.articlecontent.cimage_list);return false;"') ?>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delimage'], 'normal', ' onclick="removeSelectedOptions(document.articlecontent.cimage_list);return false;"', 'danger') ?>
                </div>
<?php

if($img_thumbs) {
    echo '<div class="d-flex flex-wrap align-items-start gap-2">' . $img_thumbs . '</div>';
}

?>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cecard_pos"><?php echo $BL['be_cnt_alignment'] ?></label>
        <select name="cecard_pos" id="cecard_pos" class="form-control">
            <option value="0" <?php is_selected(0, $content["ecard"]["pos"]) ?>><?php echo $BL['be_cnt_left'].' ('.$BL['be_cnt_default'].')' ?></option>
            <option value="1" <?php is_selected(1, $content["ecard"]["pos"]) ?>><?php echo $BL['be_cnt_center'] ?></option>
            <option value="2" <?php is_selected(2, $content["ecard"]["pos"]) ?>><?php echo $BL['be_cnt_right'] ?></option>
        </select>
    </div>

    <div class="col-12 md:col-6">
        <div class="row g-3">
            <div class="col-6">
                <label class="form-label" for="cecard_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                <div class="input-group">
                    <input name="cecard_width" type="text" id="cecard_width" class="form-control" size="3" maxlength="4" onKeyUp="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content["ecard"]["width"] ?>">
                    <span class="input-group-text">px</span>
                </div>
            </div>
            <div class="col-6">
                <label class="form-label" for="cecard_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                <div class="input-group">
                    <input name="cecard_height" type="text" id="cecard_height" class="form-control" size="3" maxlength="4" onKeyUp="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content["ecard"]["height"] ?>">
                    <span class="input-group-text">px</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="row g-3">
            <div class="col-6">
                <label class="form-label" for="cecard_col"><?php echo $BL['be_cnt_column'] ?></label>
                <select name="cecard_col" id="cecard_col" class="form-control">
<?php
// list select menu for max image columns
for($max_image_col = 1; $max_image_col <= 25; $max_image_col++) {

    echo '<option value="'.$max_image_col.'" ';
    is_selected($max_image_col, $content['ecard']['col']);
    echo '>'.$max_image_col."</option>\n";

}

?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label" for="cecard_space"><?php echo $BL['be_cnt_imagespace'] ?></label>
                <div class="input-group">
                    <input name="cecard_space" type="text" id="cecard_space" class="form-control" size="2" maxlength="2" onKeyUp="if(!parseInt(this.value,10)) this.value='';" value="<?php echo $content["ecard"]["space"] ?>">
                    <span class="input-group-text">px</span>
                </div>
            </div>
        </div>
        <div class="form-field mt-3">
            <input class="check" name="cecard_zoom" type="checkbox" id="cecard_zoom" value="1"<?php is_checked(1, $content["ecard"]["zoom"]); ?>>
            <label for="cecard_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="cecard_caption"><?php echo $BL['be_cnt_ecardtext'] ?></label>
        <textarea name="cecard_caption" cols="40" rows="3" wrap="off" class="form-control" id="cecard_caption"><?php echo $caption_box ?? '' ?></textarea>
    </div>

    <div class="col-12 border-top pt-4">
        <div class="small fg-secondary mb-2">
            HTML: ###ECARD_SUBJECT###, ###SENDER_NAME###, ###SENDER_EMAIL###,
            ###RECIPIENT_NAME###, ###RECIPIENT_EMAIL###, ###SENDER_MESSAGE###,
            ###ECARD_CHOOSER###,
            &lt;!--FORM_ERROR_START--&gt; &lt;!--FORM_ERROR_END--&gt;
        </div>
<?php

// fill in the default template if form value is empty

if(!$content["ecard"]["form"]) {
    $content["ecard"]["form"]  = '<div align="center" style="margin:0 0 0 0;padding:10px 0 10px 0;">###ECARD_CHOOSER###</div>'."\n";
    $content["ecard"]["form"] .= '<table border="0" align="center" cellpadding="4" cellspacing="0" style="font-size:11px;font-family:Verdana,Arial,sans-serif;">'."\n";
    $content["ecard"]["form"] .= "<!--FORM_ERROR_START-->\n";
    $content["ecard"]["form"] .= '<tr><td colspan="5" style="margin:0 0 0 0;padding:0 0 0 0;"><table border="0" cellspacing="0" cellpadding="0">'."\n";
    $content["ecard"]["form"] .= '<tr><td valign="top"><img src="img/symbole/achtung.gif" width="13" height="11" />&nbsp;</td>'."\n";
    $content["ecard"]["form"] .= '<td valign="top"><strong style="color:#CC3300;">'.$BL['be_cnt_ecardform_err'].'</strong></td>'."\n";
    $content["ecard"]["form"] .= "</tr></table></td></tr>\n";
    $content["ecard"]["form"] .= '<tr><td colspan="5"><img src="img/leer.gif" width="1" height="2" /></td></tr>'."\n";
    $content["ecard"]["form"] .= "<!--FORM_ERROR_END-->\n";
    $content["ecard"]["form"] .= "<tr>\n";
    $content["ecard"]["form"] .= '<td colspan="2" bgcolor="#99CC00"><strong style="color:#FFFFFF;">&nbsp;'.$BL['be_cnt_ecardform_sender'].'</strong></td>'."\n";
    $content["ecard"]["form"] .= '<td rowspan="5"><img src="img/leer.gif" width="3" height="1" /></td>'."\n";
    $content["ecard"]["form"] .= '<td colspan="2" bgcolor="#99CC00"><strong style="color:#FFFFFF;">&nbsp;'.$BL['be_cnt_ecardform_recipient'].'</strong></td>'."\n";
    $content["ecard"]["form"] .= "</tr><tr>\n";
    $content["ecard"]["form"] .= '<td colspan="2" bgcolor="#F8FFDF" style="margin:0 0 0 0;padding:2px 0 0 0;"><img src="img/leer.gif" width="1" height="1" /></td>'."\n";
    $content["ecard"]["form"] .= '<td colspan="2" bgcolor="#F8FFDF" style="margin:0 0 0 0;padding:2px 0 0 0;"><img src="img/leer.gif" width="1" height="1" /></td>'."\n";
    $content["ecard"]["form"] .= "</tr>\n";
    $content["ecard"]["form"] .= "<tr>\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF">&nbsp;'.$BL['be_cnt_ecardform_name'].':</td>'."\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF"><input name="###SENDER_NAME###" type="text" style="font-size:12px; width:150px;margin-right:3px;" value="###SENDER_NAME###" size="25" /></td>'."\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF">&nbsp;'.$BL['be_cnt_ecardform_name'].':</td>'."\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF"><input name="###RECIPIENT_NAME###" type="text" style="font-size:12px; width:150px;margin-right:3px" value="###RECIPIENT_NAME###" size="25" /></td>'."\n";
    $content["ecard"]["form"] .= "</tr>\n";
    $content["ecard"]["form"] .= "<tr>\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF">&nbsp;'.$BL['be_profile_label_email'].'<span style="color:#CC3300;">*</span>:</td>'."\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF"><input name="###SENDER_EMAIL###" type="text" style="font-size:12px; width:150px;" value="###SENDER_EMAIL###" size="25" /></td>'."\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF">&nbsp;'.$BL['be_profile_label_email'].'<span style="color:#CC3300;">*</span>:</td>'."\n";
    $content["ecard"]["form"] .= '<td bgcolor="#F8FFDF"><input name="###RECIPIENT_EMAIL###" type="text" style="font-size:12px; width:150px;" value="###RECIPIENT_EMAIL###" size="25" /></td>'."\n";
    $content["ecard"]["form"] .= "</tr>\n";
    $content["ecard"]["form"] .= "<tr>\n";
    $content["ecard"]["form"] .= '<td colspan="2" bgcolor="#F8FFDF" style="margin:0 0 0 0;padding:2px 0 0 0;"><img src="img/leer.gif" width="1" height="1" /></td>'."\n";
    $content["ecard"]["form"] .= '<td colspan="2" bgcolor="#F8FFDF" style="margin:0 0 0 0;padding:2px 0 0 0;"><img src="img/leer.gif" width="1" height="1" /></td>'."\n";
    $content["ecard"]["form"] .= "</tr>\n";
    $content["ecard"]["form"] .= '<tr><td colspan="5"><img src="img/leer.gif" width="1" height="3" /></td></tr>'."\n";
    $content["ecard"]["form"] .= '<tr bgcolor="#99CC00"><td colspan="5"><strong style="color:#FFFFFF;">&nbsp;'.$BL['be_cnt_ecardform_msgtext'].'</strong></td></tr>'."\n";
    $content["ecard"]["form"] .= '<tr bgcolor="#F8FFDF"><td colspan="5" style="margin:0 0 0 0;padding:2px 0 0 0;"><img src="img/leer.gif" width="1" height="1" /></td></tr>'."\n";
    $content["ecard"]["form"] .= '<tr align="center" bgcolor="#F8FFDF">'."\n";
    $content["ecard"]["form"] .= '<td colspan="5"><textarea name="###SENDER_MESSAGE###" cols="50" rows="6" id="ecard_sender_msg" class="width440 autosize">###SENDER_MESSAGE###</textarea></td></tr>'."\n";
    $content["ecard"]["form"] .= '<tr bgcolor="#F8FFDF"><td colspan="5" style="margin:0 0 0 0;padding:4px 0 0 0;"><img src="img/leer.gif" width="1" height="1" /></td></tr>'."\n";
    $content["ecard"]["form"] .= '<tr align="center" bgcolor="#F8FFDF"><td colspan="5"><input name="###BUTTON###" type="submit" value="'.$BL['be_cnt_ecardform_button'].'" style="font-size:12px;" /></td></tr>'."\n";
    $content["ecard"]["form"] .= '<tr bgcolor="#F8FFDF"><td colspan="5"><img src="img/leer.gif" width="1" height="1" /></td></tr>'."\n";
    $content["ecard"]["form"] .= '</table>';

}

?>
        <label class="form-label" for="cecard_form"><?php echo $BL['be_cnt_ecardform'] ?></label>
        <textarea name="cecard_form" rows="15" wrap="VIRTUAL" class="form-control autosize" id="cecard_form" style="font-family: var(--bs-font-mono)"><?php echo html($content["ecard"]["form"]); ?></textarea>
    </div>

    <div class="col-12">
        <div class="small fg-secondary mb-2">
            HTML: ###ECARD_SUBJECT###, ###RECIPIENT_NAME###, ###RECIPIENT_EMAIL###,
            ###SENDER_MESSAGE###, ###ECARD_TITLE###, ###ECARD_IMAGE###
        </div>
        <label class="form-label" for="cecard_send"><?php echo $BL['be_cnt_ecardsend'] ?></label>
        <textarea name="cecard_send" rows="5" wrap="VIRTUAL" class="form-control autosize" id="cecard_send" style="font-family: var(--bs-font-mono)"><?php echo html($content["ecard"]["send"]); ?></textarea>
    </div>

    <div class="col-12">
        <div class="small fg-secondary mb-2">
            HTML: ###ECARD_SUBJECT###, ###SENDER_NAME###, ###SENDER_EMAIL###,
            ###RECIPIENT_NAME###, ###RECIPIENT_EMAIL###, ###SENDER_MESSAGE###,
            ###ECARD_IMAGE###, ###ECARD_TITLE###
        </div>
        <label class="form-label" for="cecard_mail"><?php echo $BL['be_cnt_ecardtmpl'] ?></label>
        <textarea name="cecard_mail" rows="15" wrap="VIRTUAL" class="form-control autosize" id="cecard_mail" style="font-family: var(--bs-font-mono)"><?php echo  html($content["ecard"]["mail"]) ?></textarea>
    </div>

</div>
