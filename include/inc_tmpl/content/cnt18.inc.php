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


//guestbook/comments

if(!isset($content['guestbook']) || !is_array($content['guestbook'])) {
    $content['guestbook'] = array();
}

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="cguestbook_template"><?php echo $BL['be_admin_struct_template'] ?></label>
        <select name="cguestbook_template" id="cguestbook_template" class="form-control">
<?php
// templates for article listing
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/guestbook');
if(is_array($tmpllist) && count($tmpllist)) {
    foreach($tmpllist as $val) {
        $vals = '';
        if(isset($content["guestbook"]['template']) && $val == $content["guestbook"]['template']) $vals= ' selected="selected"';
        $val = htmlspecialchars($val);
        echo '<option value="'.$val.'"'.$vals.'>'.$val."</option>\n";
    }
}

?>
        </select>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cguestbook_aliasID"><?php echo $BL['be_alias_ID'] ?></label>
                <input name="cguestbook_aliasID" type="text" class="form-control" id="cguestbook_aliasID" size="10"
                    maxlength="10" onkeyup="if(!parseInt(this.value,10))this.value='';"
                    value="<?php echo $content["guestbook"]["aliasID"] ?? '' ?>">
            </div>
<?php

    $_aliasID_Query  = 'SELECT acontent_id,  acontent_visible, article_title, acontent_form FROM '.DB_PREPEND.'phpwcms_articlecontent';
    $_aliasID_Query .= ' LEFT JOIN '.DB_PREPEND.'phpwcms_article ON ';
    $_aliasID_Query .= ' ('.DB_PREPEND.'phpwcms_articlecontent.acontent_aid = '.DB_PREPEND.'phpwcms_article.article_id)';
    $_aliasID_Query .= ' WHERE '.DB_PREPEND.'phpwcms_articlecontent.acontent_id != '.$content["id"];
    $_aliasID_Query .= ' AND '.DB_PREPEND.'phpwcms_articlecontent.acontent_type=18';
    $_aliasID_Query .= ' AND '.DB_PREPEND.'phpwcms_articlecontent.acontent_trash=0';

    $_available_aliasID = _dbQuery($_aliasID_Query);

    if(count($_available_aliasID)) {
        echo '<div><select name="cguestbook_aliasID_select" id="cguestbook_aliasID_select" class="form-control w-auto" aria-label="' . html($BL['be_alias_ID']) . '">'.LF;

        foreach($_available_aliasID as $_aliasValue) {

            $_temp_gb_data = unserialize($_aliasValue['acontent_form'], ['allowed_classes' => false]);

            if(empty($_temp_gb_data['aliasID'])) {
                echo '<option value="'.$_aliasValue['acontent_id'].'">['.$_aliasValue['acontent_id'].'] ';
                echo html(getCleanSubString($_aliasValue['article_title'], 6, '&#8230;', 'word'));
                echo '</option>'.LF;
            }

        }
        echo '</select></div>';
    }
?>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_guestbook_listing'] ?></div>
        <div class="d-flex flex-wrap align-items-center gap-4">
<?php

if(!isset($content["guestbook"]["listing"])) {
    $content["guestbook"]["listing"] = 0;
}

?>
            <div class="form-field">
                <input class="radio" name="cguestbook_listing" id="cguestbook_listing0" type="radio" value="0" <?php is_checked(0, $content["guestbook"]["listing"]); ?>>
                <label for="cguestbook_listing0"><?php echo $BL['be_cnt_guestbook_listing_all'] ?></label>
            </div>
            <div class="form-field">
                <input class="radio" name="cguestbook_listing" id="cguestbook_listing1" type="radio" value="1" <?php is_checked(1, $content["guestbook"]["listing"]); ?>>
                <label for="cguestbook_listing1"><?php echo $BL['be_cnt_guestbook_list'] ?></label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input name="cguestbook_listcount" type="text" class="form-control w-auto" id="cguestbook_listcount" size="10" maxlength="10" onkeyup="if(!parseInt(this.value,10))this.value='';" value="<?php echo $content["guestbook"]["listcount"] ?? '' ?>">
                <span class="small fg-secondary"><?php echo $BL['be_cnt_guestbook_perpage'] ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-column gap-2">
<?php

if(empty($content["guestbook"]["gb_login_post"])) {
    $content["guestbook"]["gb_login_post"] = 0;
}

if(empty($content["guestbook"]["gb_login_show"])) {
    $content["guestbook"]["gb_login_show"] = 0;
}

?>
            <div class="form-field">
                <input class="check" name="cguestbook_login_show" id="cguestbook_login_show" type="checkbox" value="1" <?php is_checked(1, $content["guestbook"]["gb_login_show"]); ?>>
                <label for="cguestbook_login_show"><?php echo $BL['be_gb_show_login'] ?></label>
            </div>
            <div class="form-field">
                <input class="check" name="cguestbook_login_post" id="cguestbook_login_post" type="checkbox" value="1" <?php is_checked(1, $content["guestbook"]["gb_login_post"]); ?>>
                <label for="cguestbook_login_post"><?php echo $BL['be_gb_post_login'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_imgupload'] ?></div>
        <div class="d-flex flex-wrap align-items-center gap-4">
<?php

if(!isset($content["guestbook"]["image_upload"])) {
    $content["guestbook"]["image_upload"] = 0;
}

if(empty($content["guestbook"]["max_image_filesize"])) {
    $content["guestbook"]["max_image_filesize"] = $phpwcms['file_maxsize'];
}

$content["guestbook"]["max_image_filesize"] = return_bytes_shorten($content["guestbook"]["max_image_filesize"]);

if(return_bytes($content["guestbook"]["max_image_filesize"]) > $phpwcms['file_maxsize']) {
    $content["guestbook"]["max_image_filesize"] = return_bytes_shorten($phpwcms['file_maxsize']);
}

?>
            <div class="form-field">
                <input class="check" name="cguestbook_imgupload" id="cguestbook_imgupload" type="checkbox" value="1" <?php is_checked(1, $content["guestbook"]["image_upload"]); ?>>
                <label for="cguestbook_imgupload"><?php echo $BL['be_on'] ?></label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="cguestbook_maximgsize"><?php echo $BL['be_cnt_filesize'] ?></label>
                <input name="cguestbook_maximgsize" type="text" class="form-control w-auto" id="cguestbook_maximgsize" size="20" maxlength="20" value="<?php echo $content["guestbook"]["max_image_filesize"] ?>">
                <span class="small fg-secondary">(<?php echo return_bytes($content["guestbook"]["max_image_filesize"]) ?> Byte)</span>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_sorting'] ?></div>
        <div class="d-flex flex-wrap gap-4">
<?php

if(!isset($content["guestbook"]["sorting"])) {
    $content["guestbook"]["sorting"] = 0;
}

?>
            <div class="form-field">
                <input class="radio" name="cguestbook_sorting" id="cguestbook_sorting0" type="radio" value="0" <?php is_checked(0, $content["guestbook"]["sorting"]); ?>>
                <label for="cguestbook_sorting0"><?php echo $BL['be_msg_date'].' '.$BL['be_admin_struct_orderdesc'] ?></label>
            </div>
            <div class="form-field">
                <input class="radio" name="cguestbook_sorting" id="cguestbook_sorting1" type="radio" value="1" <?php is_checked(1, $content["guestbook"]["sorting"]); ?>>
                <label for="cguestbook_sorting1"><?php echo $BL['be_msg_date'].' '.$BL['be_admin_struct_orderasc'] ?></label>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label">Captcha</div>
        <div class="d-flex flex-wrap align-items-center gap-4">
<?php

if(!isset($content["guestbook"]["captcha"])) {
    $content["guestbook"]["captcha"] = 1;
}

if(empty($content["guestbook"]["captcha_maxchar"])) {
    $content["guestbook"]["captcha_maxchar"] = 5;
}

?>
            <div class="form-field">
                <input class="check" name="cguestbook_captcha" id="cguestbook_captcha" type="checkbox" value="1" <?php is_checked(1, $content["guestbook"]["captcha"]); ?>>
                <label for="cguestbook_captcha"><?php echo $BL['be_admin_usr_verify'] ?></label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="cguestbook_captchamaxchar"><?php echo $BL['be_cnt_captchalength'] ?></label>
                <input name="cguestbook_captchamaxchar" type="text" class="form-control w-auto" id="cguestbook_captchamaxchar" size="3" maxlength="2" value="<?php echo $content["guestbook"]["captcha_maxchar"] ?>">
                <span class="small fg-secondary"><?php echo $BL['be_cnt_chars'] ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_profile_label_website'] ?></div>
<?php

if(empty($content["guestbook"]["gb_urlcheck"])) {
    $content["guestbook"]["gb_urlcheck"] = 0;
}

?>
        <div class="form-field">
            <input class="check" name="cguestbook_urlcheck" id="cguestbook_urlcheck" type="checkbox" value="1" <?php is_checked(1, $content["guestbook"]["gb_urlcheck"]); ?>>
            <label for="cguestbook_urlcheck"><?php echo $BL['be_gb_urlcheck'] ?></label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="cguestbook_banned"><?php echo $BL['be_cnt_guestbook_banned'] ?></label>
        <textarea name="cguestbook_banned" cols="40" rows="3" class="form-control autosize" id="cguestbook_banned"><?php echo  isset($content["guestbook"]["banned"]) ? html($content["guestbook"]["banned"]) : '' ?></textarea>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_guestbook_flooding'] ?></div>
        <div class="d-flex flex-wrap align-items-center gap-4">
<?php

if(!isset($content["guestbook"]["cookie"])) $content["guestbook"]["cookie"] = 1;

?>
            <div class="form-field">
                <input class="check" name="cguestbook_cookie" id="cguestbook_cookie" type="checkbox" value="1"<?php is_checked(1, intval($content["guestbook"]["cookie"])) ?>>
                <label for="cguestbook_cookie"><?php echo $BL['be_cnt_guestbook_setcookie'] ?></label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="cguestbook_time"><?php echo $BL['be_cnt_guestbook_allowed'] ?></label>
                <input name="cguestbook_time" type="text" class="form-control w-auto" id="cguestbook_time" size="10" maxlength="10" value="<?php

if(!isset($content["guestbook"]["time"])) {
    $content["guestbook"]["time"] = 86400;
}
echo intval($content["guestbook"]["time"]);

?>">
                <span class="small fg-secondary"><?php echo $BL['be_cnt_guestbook_seconds'] ?></span>
            </div>
        </div>
    </div>

<?php

if(!isset($content["guestbook"]["notify"])) {
    $content["guestbook"]["notify"] = 0;
}
if(!isset($content["guestbook"]["notify_email"])) {
    $content["guestbook"]["notify_email"] = '';
}

?>
    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_email_notify'] ?></div>
        <div class="d-flex flex-wrap align-items-center gap-4">
            <div class="form-field">
                <input class="check" name="cguestbook_notify" id="cguestbook_notify" type="checkbox" value="1" <?php is_checked(1, $content["guestbook"]["notify"]); ?>>
                <label for="cguestbook_notify"><?php echo $BL['be_cnt_notify_by_email'] ?></label>
            </div>
            <input name="cguestbook_notify_email" type="text" class="form-control w-auto" id="cguestbook_notify_email" size="10" value="<?php echo html($content["guestbook"]["notify_email"]) ?>">
        </div>
    </div>

<?php
// show possibility to edit guestbook entries
// if content part is created
if($content["id"]) {
?>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_guestbook_edit'] ?></div>
        <iframe width="100%" height="350" scrolling="auto" class="rounded border" src="include/inc_act/act_guestbook.php?<?php echo CSRF_GET_TOKEN; ?>&amp;cid=<?php echo empty($content["guestbook"]["aliasID"]) ? $content["id"] : $content["guestbook"]["aliasID"]; ?>"></iframe>
    </div>

<?php
}
?>

</div>
