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


//article menu

if(!isset($content["alist"]["cat"])) {
    $content["alist"]["cat"] = 0;
}
if(!isset($content["alist"]["catid"])) {
    $content["alist"]["catid"] = 0;
}
if(!isset($content["alist"]["headertext"])) {
    $content["alist"]["headertext"] = 0;
}
if(!isset($content["alist"]["div"])) {
    $content["alist"]["div"] = 0;
}
if(!isset($content["alist"]["ul"])) {
    $content["alist"]["ul"] = 0;
}
if(!isset($content["alist"]["class"])) {
    $content["alist"]["class"] = '';
}
if(empty($content["alist"]["maxchar"])) {
    $content["alist"]["maxchar"] = '';
}
if(empty($content["alist"]["morelink"])) {
    $content["alist"]["morelink"] = '';
}
if(empty($content["alist"]["titlewrap"])) {
    $content["alist"]["titlewrap"] = '';
}
if(!isset($content["alist"]["hideactive"])) {
    $content["alist"]["hideactive"] = 0;
}
if(!isset($content["alist"]["titleasnumber"])) {
    $content["alist"]["titleasnumber"] = 0;
}
if(empty($content["alist"]["break"])) {
    $content["alist"]["break"] = '';
}
if(empty($content["alist"]["label"])) {
    $content["alist"]["label"] = '';
}


?>
<tr>
    <td colspan="2" class="pt-7">
        <div class="row g-4">

            <div class="col-12 md:col-6">
                <div class="form-label"><?php echo $BL['be_cnt_sitelevel'] ?></div>
                <div class="form-field mb-3">
                    <input class="radio" name="calist_cat" id="calist_cat0" type="radio" value="0"<?php is_checked(0, intval($content["alist"]["cat"])) ?> />
                    <label for="calist_cat0"><?php echo $BL['be_cnt_sitecurrent'] ?></label>
                </div>
                <div class="form-field mb-3">
                    <input class="radio" name="calist_cat" id="calist_cat1" type="radio" value="1"<?php is_checked(1, intval($content["alist"]["cat"])) ?> />
                    <label for="calist_cat1"><?php echo $BL['be_cnt_sitelevel'] ?></label>
                </div>
                <select name="calist_catid" id="calist_catid" class="form-control">
<?php
    echo '<option value="0"'.((!$content["alist"]["catid"]) ? ' selected="selected"' : '').'>'.$BL['be_admin_struct_index'].'</option>' . LF;
    struct_select_menu(0, 0, $content["alist"]["catid"]);
?>
                </select>
            </div>

            <div class="col-12 md:col-6">
                <div class="form-label"><?php echo $BL['be_show_content'] ?></div>
                <div class="d-flex flex-wrap gap-5">
                    <div class="form-field">
                        <input class="radio" name="calist_ul" id="calist_ul1" type="radio" value="1"<?php is_checked(1, intval($content["alist"]["ul"])) ?> />
                        <label for="calist_ul1">&lt;ul&gt;</label>
                    </div>
                    <div class="form-field">
                        <input class="radio" name="calist_ul" id="calist_ul2" type="radio" value="2"<?php is_checked(2, intval($content["alist"]["ul"])) ?> />
                        <label for="calist_ul2">&lt;div&gt;</label>
                    </div>
                    <div class="form-field">
                        <input class="radio" name="calist_ul" id="calist_ul3" type="radio" value="3"<?php is_checked(3, intval($content["alist"]["ul"])) ?> />
                        <label for="calist_ul3">&lt;dl&gt;</label>
                    </div>
                    <div class="form-field">
                        <input class="radio" name="calist_ul" id="calist_ul4" type="radio" value="4"<?php is_checked(4, intval($content["alist"]["ul"])) ?> />
                        <label for="calist_ul4">&lt;span&gt;</label>
                    </div>
                    <div class="form-field">
                        <input class="radio" name="calist_ul" id="calist_ul0" type="radio" value="0"<?php is_checked(0, intval($content["alist"]["ul"])) ?> />
                        <label for="calist_ul0"><?php echo $BL['be_admin_page_table'] ?></label>
                    </div>
                </div>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="calist_class"><?php echo $BL['be_cnt_css_class'] ?></label>
                <input type="text" name="calist_class" id="calist_class" class="form-control" value="<?php echo html($content["alist"]["class"]) ?>" />
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="calist_titlewrap"><?php echo $BL['be_title_wrap'] ?></label>
                <select name="calist_titlewrap" id="calist_titlewrap" class="form-control">
<?php

    echo '  <option value=""';
    is_selected(0, $content["alist"]["titlewrap"]);
    echo '>'.$BL['be_cnt_default'].' ('.$BL['be_func_struct_empty'].')</option>' . LF;

    foreach(array('p','div','span','h1','h2','h3','h4','h5','h6','pre','blockquote','em') as $value) {

        echo '  <option value="'.$value.'"';
        is_selected($value, $content["alist"]["titlewrap"]);
        echo '>'.strtoupper($value).'</option>' . LF;

    }

?>
                </select>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="calist_label"><?php echo $BL['be_cnt_label'] ?></label>
                <input type="text" name="calist_label" id="calist_label" class="form-control" value="<?php echo html($content["alist"]["label"]) ?>" />
            </div>

            <div class="col-6 md:col-3">
                <label class="form-label" for="calist_break"><?php echo $BL['be_cnt_field']['break'] ?></label>
                <input type="text" name="calist_break" id="calist_break" class="form-control" value="<?php echo html($content["alist"]["break"]) ?>" />
            </div>

            <div class="col-12">
                <div class="form-field">
                    <input class="check" name="calist_titleasnumber" id="calist_titleasnumber" type="checkbox" value="1"<?php is_checked(1, intval($content["alist"]["titleasnumber"])) ?> />
                    <label for="calist_titleasnumber"><?php echo $BL['numerize_title'] ?></label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-field">
                    <input class="check" name="calist_headertext" id="calist_headertext" type="checkbox" value="1"<?php is_checked(1, intval($content["alist"]["headertext"])) ?> />
                    <label for="calist_headertext"><?php echo $BL['be_article_asummary'] ?></label>
                </div>
            </div>

            <div class="col-6 md:col-3">
                <label class="form-label" for="calist_maxchar"><?php echo $BL['be_cnt_articlemenu_maxchar'] ?></label>
                <input type="text" name="calist_maxchar" id="calist_maxchar" class="form-control" value="<?php echo $content["alist"]["maxchar"] ?>" />
            </div>

            <div class="col-6 md:col-3">
                <label class="form-label" for="calist_morelink"><?php echo $BL['be_article_morelink'] ?></label>
                <input type="text" name="calist_morelink" id="calist_morelink" class="form-control" value="<?php echo html($content["alist"]["morelink"]) ?>" />
            </div>

            <div class="col-12">
                <div class="form-field">
                    <input class="check" name="calist_hideactive" id="calist_hideactive" type="checkbox" value="1"<?php is_checked(1, intval($content["alist"]["hideactive"])) ?> />
                    <label for="calist_hideactive"><?php echo $BL['be_hide_active_articlelink'] ?></label>
                </div>
            </div>

        </div>
    </td>
</tr>
