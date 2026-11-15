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


// OK check article and category information
$sql  = 'SELECT DISTINCT * FROM '.DB_PREPEND.'phpwcms_article ar LEFT JOIN '.DB_PREPEND.'phpwcms_articlecat ac ON ';
$sql .= "ar.article_cid=ac.acat_id WHERE ar.article_id='".$content["aid"]."' LIMIT 1";
$content['article'] = _dbQuery($sql);
$content['article'] = $content['article'][0] ?? array('article_title' => '', 'acat_name' => '', 'acat_template' => 0);
$content['cp_setting_mode'] = false;

if(empty($content['article']['acat_id'])) { // Root structure
    $content['article']['acat_name']        = $indexpage['acat_name'];
    $content['article']['acat_id']          = 0;
    $content['article']['acat_template']    = $indexpage['acat_template'];
}

// Livedate / killdate fallback
if(empty($content["livedate"])) {
    $content["livedate"] = '';
    $set_livedate = 0;
} else {
    $set_livedate = 1;
}
if(empty($content["killdate"])) {
    $content["killdate"] = '';
    $set_killdate = 0;
} else {
    $set_killdate = 1;
}

// the live and kill fields hold the canonical database value; the date picker
// only shows it in the format and week start the user picked in the profile
$backend_date_format = backend_date_format($_SESSION['wcs_user_date_format'] ?? '');
$backend_weekstart   = backend_weekstart($_SESSION['wcs_user_weekstart'] ?? '');

initJsDatePicker();

/*
 *  The type forms are included into the screen: the ones listed here render
 *  their own Bootstrap markup, every other one still writes the rows of the
 *  legacy two column table and keeps that table
 */
$cp_form_bootstrap = array(11, 15, 24);

?>
<script type="text/javascript">
    function validate_before_after(elem, checkElem) {
        if(elem.value.length === 1 && (elem.value === '-' || elem.value === '+')) {
            return true;
        }
        var checkbox_element = document.getElementById(checkElem);
        var elem_int = parseInt(elem.value, 10);
        if(elem_int) {
            elem.value = elem_int;
            checkbox_element.checked = true;
        } else {
            checkbox_element.checked = false;
            elem.value = '';
        }
    }

    // the live and kill date fields: the checkbox keeps the stored value, and a
    // live date after the kill date clears that kill date as the calendar did
    function content_date_set(field, value) {
        var form = document.articlecontent;
        var live = field === 'live';
        window.phpwcmsDatePickerSet(live ? form.clivedate : form.ckilldate, value);
        (live ? form.set_livedate : form.set_killdate).checked = value !== '';
    }

    function content_date_changed(field) {
        var form = document.articlecontent;
        var live = form.clivedate.value;
        var kill = form.ckilldate.value;

        if(field === 'live' && live) {
            form.set_livedate.checked = true;
            if(kill && live.substring(0, 10) > kill.substring(0, 10)) {
                content_date_set('kill', '');
            }
        } else if(field === 'kill' && kill) {
            form.set_killdate.checked = true;
            if(live && kill.substring(0, 10) < live.substring(0, 10)) {
                content_date_set('live', '');
            }
        }
    }
</script>
<form action="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id=<?php echo $content["aid"]."&amp;acid=".$content["id"] ?>" method="post" name="articlecontent" id="articlecontent" <?php

    // Some javascript actions neccessary on submit
    switch($content["type"]) {

        case 2:
        case 29:
        case 16:
        case 50:
        case 89:
            echo 'onsubmit="selectAllOptions(this.cimage_list);return checkCp();"';
            break;

        //case 25:
        case 7:
            echo 'onsubmit="selectAllOptions(this.cfile_list);return checkCp();"';
            break;

        case 8:
            echo 'onsubmit="selectAllOptions(this.calink);return checkCp();"';
            break;

        case 53:
            echo 'onsubmit="selectAllOptions(this.cforum_selection);return checkCp();"';
            break;

        default:
            echo 'onsubmit="var ct=document.getElementById(\'target_ctype\'); if(ct.disabled){ct.disabled=false;} return checkCp();"';

    }

    if(empty($content["id"]) && empty($content['block'])) {

        $sendbutton = $BL['be_article_cnt_button2'];

        $content["block"]           = 'CONTENT';
        $content["before"]          = '';
        $content["after"]           = '';
        $content["title"]           = '';
        $content["subtitle"]        = '';
        $content["top"]             = 0;
        $content["visible"]         = 0;
        $content["anchor"]          = 0;
        $content['comment']         = '';
        $content['paginate_title']  = '';
        $content['paginate_page']   = '';
        $content["granted"]         = 0;

    } else {

        $sendbutton = $BL['be_article_cnt_button1'];

    }

    ?>>
<input type="hidden" name="ctype_module" value="<?php echo html($content["module"]) ?>" />

<div class="row g-4">

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0 d-flex flex-wrap align-items-center gap-2">
                    <?php echo $BL['be_article_cnt_title'] ?>
                    <span class="badge theme-secondary text-uppercase"><?php

    echo $wcs_content_type[$content["type"]];

    if(!empty($content["module"])) {

        echo ': '.$BL['modules'][$content["module"]]['listing_title'];

        // check if Module is in setting mode
        if(!empty($phpwcms['modules'][$content["module"]]['setting'])) {
            $content['cp_setting_mode'] = true;
        }

    }

    ?></span>
                </h2>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-12 md:col-6">
                        <div class="form-label"><?php echo $BL['be_article_cat'] ?></div>
                        <a href="phpwcms.php?do=admin&amp;p=6&amp;struct=<?php

                        if(empty($content['article']['acat_id'])) {
                            echo 'index';
                        } else {
                            echo $content['article']['acat_struct'], '&amp;cat=', $content['article']['acat_id'];
                        }

                        ?>" class="link d-inline-flex align-items-center gap-2" onclick="return confirm('<?php echo $BL['be_dialog_warn_nosave']; ?>');">
                            <?php echo html($content["article"]['acat_name']).' [ID:'.$content['article']['acat_id'].']' ?>
                            <?php echo backend_icon('arrow-up-right') ?>
                        </a>
                    </div>

                    <div class="col-12 md:col-6">
                        <div class="form-label"><?php echo $BL['be_article_atitle'] ?></div>
                        <a href="#" class="link" id="article-title-link" data-bs-toggle="tooltip" title="<?php echo html($BL['be_change_articleID'].' - '.$BL['be_cnt_default'].': '.$content["aid"]) ?>" onclick="showEditArticleID(this);return false;"><?php echo html($content["article"]['article_title']) ?></a>
                        <script type="text/javascript">

    var istuff_done = false;
    function showEditArticleID(istuff) {
        if(istuff_done) return false;
        istuff.innerHTML += '&nbsp; <'+'span class="small fg-secondary"'+'><?php echo $BL['be_func_struct_articleID'] ?>:&nbsp;<'+'/span><'+'input type="text" name="ctype_change_aid" value="<?php echo $content["aid"] ?>" class="form-control" /'+'>';
        istuff_done = true;
    }

                        </script>
                    </div>

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="target_ctype"><?php echo $BL['be_article_cnt_type'] ?></label>
                        <select name="target_ctype" id="target_ctype" class="form-control" onchange="if(confirm('<?php

    // echo Message for JS dialog
    echo $BL['be_func_switch_contentpart'];

    // build select box options and remember the "old" value for javascript
    $temp_select                = '';
    $temp_count                 = 0;
    $contentpart_temp_selected  = 0;
    $user_selected_cp           = isset($_SESSION["wcs_user_cp"]) && count($_SESSION["wcs_user_cp"]);

    if(is_array($article["article_cntpart"]) && count($article["article_cntpart"])) {

        if(!in_array($content['type'], $article["article_cntpart"])) {
            $article["article_cntpart"][] = $content['type'];
        }

        // list all content parts usable for this article category
        foreach($article["article_cntpart"] as $value) {

            if($user_selected_cp && !isset($_SESSION["wcs_user_cp"][$value]) && $value != $content['type']) {
                continue;
            }

            if(isset($wcs_content_type[$value])) {

                $temp_select .= getContentPartOptionTag($value, $wcs_content_type[$value], $content['type'], $content['module']);
                $temp_count++;
            }
            $value1 = $value * (-1);
            if(isset($BL['be_admin_optgroup_label'][$value1]) && $value) {
                $temp_select .= '<optgroup label="[ '.$BL['be_admin_optgroup_label'][$value1].' ]" class="cntOptGroup"></optgroup>'."\n";
            }
        }

    }
    if(!$temp_count) {
        //list all available content parts
        foreach($wcs_content_type as $key => $value) {

            if($user_selected_cp && !isset($_SESSION["wcs_user_cp"][$key]) && $key != $content['type']) {
                continue;
            }

            $temp_select .= getContentPartOptionTag($key, $value, $content['type'], $content['module']);
            $temp_count++;
        }
    }

?>')){ this.form.submit(); } else { this.form.target_ctype.selectedIndex = <?php echo $contentpart_temp_selected; ?>; return false; }">
<?php

    // menue with content types
    echo $temp_select

?>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>

<?php

// Content Part Setting Mode - hide all settings related to article and content part rendering
if($content['cp_setting_mode']):

?>
    <div class="col-12">
        <input type="hidden" name="cblock" value="CPSET" />
        <input type="hidden" name="csorting" value="0" />
        <input type="hidden" name="cbefore" value="" />
        <input type="hidden" name="ctab_title" value="" />
        <input type="hidden" name="ctab_number" value="" />
        <input type="hidden" name="ctitle" value="" />
        <input type="hidden" name="csubtitle" value="" />
        <input type="hidden" name="cpaginate_title" value="" />
        <input type="hidden" name="cpaginate_page" value="" />
    </div>
<?php

    // normal contentpart edit mode
    else:

        // Detect Template
        if(!empty($content['article']['acat_template'])) {
            $content['current_template'] = _dbGet('phpwcms_template', '*', 'template_trash=0 AND template_id='._dbEscape($content['article']['acat_template']), '', '', 1);
        }
        if(!isset($content['current_template'][0])) {
            $content['current_template'] = _dbGet('phpwcms_template', '*', 'template_trash=0 AND template_default=1', '', '', 1);
        }
        if(!isset($content['current_template'][0])) {
            $content['current_template'] = _dbGet('phpwcms_template', '*', 'template_trash=0', '', 'template_default DESC', 1);
        }

        $content['blocks'] = array();

        if(isset($content['current_template'][0]['template_var'])) {
            $content['template_name'] = html($content['current_template'][0]['template_name']);
            if($content['current_template'][0]['template_default']) {
                $content['template_name'] .= ' ('.$BL['be_admin_tmpl_default'].')';
            }
            $content['current_template'] = unserialize($content['current_template'][0]['template_var'], ['allowed_classes' => false]);
            if(!empty($content['current_template']['customblock'])) {
                $content['current_template'] = explode(',', $content['current_template']['customblock']);
                if(count($content['current_template'])) {
                    $content['blocks'][] = '<optgroup label="'.$BL['be_admin_page_blocks'].', '.$BL['be_admin_page_customblocks'].'">';
                    foreach($content['current_template'] as $value) {
                        $value = trim($value);
                        if($value !== '') {
                            $valhtml = html($value);
                            $content['blocks'][] = '    <option value="'.$valhtml.'"'.is_selected($value, $content["block"], 0, 0).'>'.$valhtml.'</option>';
                        }
                    }
                    $content['blocks'][] = '</optgroup>';
                }
            }
        } else {
            $content['template_name'] = $BL['be_admin_tmpl_default'];
        }

        $content['blocks'] = implode(LF, $content['blocks']);

    $anchor_title = empty($content["id"]) ? '' : ' data-bs-toggle="tooltip" title="cpid'.$content["id"].'"';

    // handle tab settings
    $content["tab_style"] = ' style="display:none"';

    if(empty($content["tab"])) {

        $content["tab"]         = '';
        $content["tab_number"]  = '';
        $content["tab_title"]   = '';
        $content["tab_type"]    = 0;

    } else {

        $content["tab"]             = explode('_', $content["tab"], 2);
        $content["tab_title"]       = empty($content["tab"][1]) ? '' : $content["tab"][1];
        $content["tab_number"]      = explode('|', $content["tab"][0]);
        $content["tab_type"]        = empty($content["tab_number"][1]) ? 1 : $content["tab_number"][1];
        $content["tab_number"]      = intval($content["tab_number"][0]);

        if($content["tab_number"].$content["tab_title"]) {
            $content["tab"]         = 1;
            $content["tab_style"]   = '';
        }

    }

?>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="cblock"><?php echo $BL['be_show_content'] ?></label>
                        <select name="cblock" id="cblock" class="form-control" onchange="checkCntBlockPaginate(this);">
                            <optgroup label="<?php echo $BL['be_admin_struct_template'].': '.$content['template_name'] ?>">
                                <option value="CONTENT"<?php echo is_selected('CONTENT', $content["block"]) ?>><?php echo $BL['be_main_content'] ?> (CONTENT)</option>
                                <option value="LEFT"<?php echo is_selected('LEFT', $content["block"]) ?>><?php echo $BL['be_cnt_left'] ?> (LEFT)</option>
                                <option value="RIGHT"<?php echo is_selected('RIGHT', $content["block"]) ?>><?php echo $BL['be_cnt_right'] ?> (RIGHT)</option>
                                <option value="HEADER"<?php echo is_selected('HEADER', $content["block"]) ?>><?php echo $BL['be_admin_page_header'] ?> (HEADER)</option>
                                <option value="FOOTER"<?php echo is_selected('FOOTER', $content["block"]) ?>><?php echo $BL['be_admin_page_footer'] ?> (FOOTER)</option>
                                <option value="SYSTEM"<?php echo is_selected('SYSTEM', $content["block"]) ?>><?php echo $BL['be_system_container'] ?> (SYSTEM)</option>
                            </optgroup>
                            <?php echo $content['blocks'] ?>
                        </select>
                    </div>

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="ctab"><?php echo $BL['be_cnt_paginate_subsection'] ?></label>
                        <select name="ctab" id="ctab" class="form-control" onchange="checkTabStatus(this);"<?php if($content["block"] == 'SYSTEM'): ?> disabled="disabled"<?php endif; ?>>
                            <option value="0"<?php is_selected(0, $content["tab_type"]); ?>><?php echo $BL['be_off'] ?></option>
                            <option value="1"<?php is_selected(1, $content["tab_type"]); ?>><?php echo $BL['be_ctype_tabs'] ?></option>
                            <option value="2"<?php is_selected(2, $content["tab_type"]); ?>><?php echo $BL['be_ctype_accordion'] ?></option>
<?php
    if(isset($template_default['attributes']['cpgroup_custom']) && is_array($template_default['attributes']['cpgroup_custom']) && count($template_default['attributes']['cpgroup_custom'])):
        foreach($template_default['attributes']['cpgroup_custom'] as $tab_type_value => $value):
?>
                            <option value="<?php echo $tab_type_value ?>"<?php is_selected($tab_type_value, $content["tab_type"]); ?>><?php echo html($value['title']) ?></option>
<?php
        endforeach;
    endif;
?>
                        </select>
                    </div>

                    <div class="col-12" id="system1"<?php if($content["block"] !== 'SYSTEM'): ?> style="display:none;"<?php endif; ?>>
                        <label class="form-label" for="ctid"><?php echo $BL['be_article_rendering'] ?></label>
                        <select name="ctid" id="ctid" class="form-control">
                            <option value="0"<?php echo is_selected(0, $content['tid']) ?>><?php echo $BL['be_custom_scriptlogic'] ?></option>
                            <option value="1"<?php echo is_selected(1, $content['tid']) ?>><?php echo $BL['be_article_forlist'] ?></option>
                            <option value="2"<?php echo is_selected(2, $content['tid']) ?>><?php echo $BL['be_article_forfull'] ?></option>
                            <option value="3"<?php echo is_selected(3, $content['tid']) ?>><?php echo $BL['be_article_forlist'].' + '.$BL['be_article_forfull'] ?></option>
                        </select>
                    </div>

                    <!-- ctab section -->
                    <div class="col-12" id="ctab2"<?php echo $content["tab_style"] ?>>
                        <label class="form-label" for="ctab_title"><?php echo $BL['be_cnt_paginate_subsection'] ?></label>
                        <div class="row g-3">
                            <div class="col">
                                <input name="ctab_title" type="text" id="ctab_title" class="form-control" value="<?php echo html($content["tab_title"]) ?>" size="40" maxlength="1500" />
                            </div>
                            <div class="col-6 md:col-3">
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo $BL['be_ctype_number'] ?></span>
                                    <input name="ctab_number" type="text" id="ctab_number" class="form-control" value="<?php echo $content["tab_number"] ?>" size="3" maxlength="4" onkeyup="if(!parseInt(this.value,10))this.value='';" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ctab section end -->

                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">

<?php
          if(isset($content["error"])) {
?>
                <div class="alert theme-danger mb-4"><?php
            //Fehlerdarstellung
            $content["error_result"]="";
            foreach($content["error"] as $value) {
                $content["error_result"] .= "> ".$value."\n";
            }
            echo nl2br(html(chop($content["error_result"])));
            unset($content["error_result"]);

                ?></div>
<?php
          }
?>

                <div class="row g-4">

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="ctitle"><?php echo $BL['be_article_cnt_ctitle'] ?></label>
                        <input name="ctitle" type="text" id="ctitle" class="form-control" value="<?php echo html($content["title"]) ?>" size="40" maxlength="2000" />
                    </div>

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="csubtitle"><?php echo $BL['be_article_asubtitle'] ?></label>
                        <input name="csubtitle" type="text" id="csubtitle" class="form-control" value="<?php echo html($content["subtitle"]) ?>" size="40" maxlength="2000" />
                    </div>

<?php

    // check if it is necessary to display paginate stuff
    // in case no content part pagination isset for current article

    $content["paginate_page"] = empty($content["paginate_page"]) ? 0 : intval($content["paginate_page"]);

    if(empty($content['article']['article_paginate']) || $content["block"] === 'SYSTEM') {

        echo '<input name="cpaginate_title" type="hidden" id="cpaginate_title" value="'.html($content["paginate_title"]).'" />';
        echo '<input name="cpaginate_page" type="hidden" id="cpaginate_page" value="'.$content["paginate_page"].'" />';

    } else {

?>

                    <div class="col-6 md:col-3">
                        <label class="form-label" for="cpaginate_page"><?php echo $BL['be_cnt_paginate_subsection'] ?></label>
                        <input name="cpaginate_page" type="text" id="cpaginate_page" class="form-control" value="<?php echo $content["paginate_page"] ?>" size="3" maxlength="3" onkeyup="if(!parseInt(this.value,10))this.value='0';" />
                    </div>

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="cpaginate_title"><?php echo $BL['be_cnt_subsection_tite'].' ('.$BL['be_pagination'].')' ?></label>
                        <input name="cpaginate_title" type="text" id="cpaginate_title" class="form-control" value="<?php echo html($content["paginate_title"]) ?>" size="40" maxlength="2000" />
                    </div>

<?php

    }
    // end paginate check

?>

                </div>
            </div>
        </div>
    </div>

<?php

    // end non content part setting mode
    endif;

?>

<?php

// render buttons only once and save the buffer
if(!empty($content["id"])) {
    $buttonActionLink = rel_url(array('phpwcms-preview'=>1), array(), empty($content['article']["article_alias"]) ? (empty($content["aid"]) ? 'id='.$content["id"] : 'aid='.$content["aid"]) : $content['article']["article_alias"]);
    $buttonAction  = '<button type="button" value="'.$BL['be_func_struct_preview'].'" class="btn btn-outline theme-secondary btn-sm" data-bs-toggle="tooltip" title="'.$BL['be_func_struct_preview'].'" ';
    $buttonAction .= 'onclick="window.open(\''.$buttonActionLink."', 'articlePreviewWindows');return false;\">";
    $buttonAction .= $BL['be_func_struct_preview']."</button>" . LF;
} else {
    $buttonAction  = '';
}

ob_start();

?>
    <input name="Submit" type="submit" class="btn theme-primary btn-solid btn-styled btn-sm" value="<?php echo  $sendbutton ?>" />
    <input name="SubmitClose" type="submit" class="btn btn-outline theme-secondary btn-sm" value="<?php echo $BL['be_article_cnt_button3'] ?>" />
    <input name="donotsubmit" type="button" class="btn btn-outline theme-secondary btn-sm" value="<?php echo  $BL['be_newsletter_button_cancel'] ?>" onclick="location.href='phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;id=<?php echo $content["aid"] ?>'" />
    <?php echo $buttonAction; ?>
<?php

$_save_close_buttons = ob_get_clean();

?>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0"><?php echo $wcs_content_type[$content["type"]] ?></h2>
            </div>
<?php

    // show content part specific form elements
    if($content['type'] != 30 && file_exists(PHPWCMS_ROOT.'/include/inc_tmpl/content/cnt'.$content['type'].'.inc.php')) {

        $cp_form_file = PHPWCMS_ROOT.'/include/inc_tmpl/content/cnt'.$content['type'].'.inc.php';

    } elseif($content['type'] == 30 && file_exists($phpwcms['modules'][$content["module"]]['path'].'inc/cnt.form.php')) {

        $cp_form_file = $phpwcms['modules'][$content["module"]]['path'].'inc/cnt.form.php';

    } else {

        $cp_form_file = PHPWCMS_ROOT.'/include/inc_tmpl/content/cnt0.inc.php';

    }

    if(in_array((int) $content['type'], $cp_form_bootstrap, true)) {

        echo '<div class="card-body">';
        include_once $cp_form_file;
        echo '</div>';

    } else {

        // the type forms of the screens not revamped yet write the rows of a two
        // column table
        echo '<div class="card-body"><table class="w-100" summary="">';
        include_once $cp_form_file;
        echo '</table></div>';

    }

?>
            <div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
                <?php echo $_save_close_buttons ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0"><?php echo $BL['be_article_cnt_space'] ?></h2>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-12 md:col-6">
                        <div class="form-field mb-2">
                            <input class="check" name="ccb" type="checkbox" id="ccb" value="1" <?php if($content["before"] !== '') echo "checked"; ?> onclick="if(!this.checked){this.form.cbefore.value='';}else if(this.form.cbefore.value===''){this.checked=false;}" />
                            <label for="ccb"><?php echo $BL['be_article_cnt_before'] ?></label>
                        </div>
                        <div class="input-group">
                            <input name="cbefore" type="text" id="cbefore" class="form-control" value="<?php echo $content["before"] ?>" size="5" maxlength="5" onkeyup="validate_before_after(this, 'ccb');" />
                            <span class="input-group-text"><?php echo empty($template_default['article']['div_spacer_unit']) ? 'px' : $template_default['article']['div_spacer_unit']; ?></span>
                        </div>
                    </div>

                    <div class="col-12 md:col-6">
                        <div class="form-field mb-2">
                            <input class="check" name="cca" type="checkbox" id="cca" value="1" <?php if($content["after"] !== '') echo "checked"; ?> onclick="if(!this.checked){this.form.cafter.value='';}else if(this.form.cafter.value===''){this.checked=false;}" />
                            <label for="cca"><?php echo $BL['be_article_cnt_after'] ?></label>
                        </div>
                        <div class="input-group">
                            <input name="cafter" type="text" id="cafter" class="form-control" value="<?php echo $content["after"] ?>" size="5" maxlength="5" onkeyup="validate_before_after(this, 'cca');" />
                            <span class="input-group-text"><?php echo empty($template_default['article']['div_spacer_unit']) ? 'px' : $template_default['article']['div_spacer_unit']; ?></span>
                        </div>
                    </div>

                    <div class="col-12 md:col-6">
                        <div class="form-field">
                            <input class="check" name="ctop" type="checkbox" id="ctop" value="1"<?php is_checked(1, $content["top"]); ?> />
                            <label for="ctop"><?php echo $BL['be_article_cnt_toplink'] ?></label>
                        </div>
                        <div class="form-field mt-3">
                            <input class="check" name="canchor" type="checkbox" id="canchor" value="1"<?php is_checked(1, $content["anchor"]); echo $anchor_title ?> />
                            <label for="canchor"><?php echo $BL['be_article_cnt_anchor'] ?></label>
                        </div>
                    </div>

                    <div class="col-12 md:col-6">
                        <label class="form-label" for="cattr_class"><?php echo $BL['be_cnt_attribute_class'] ?></label>
                        <input name="cattr_class" type="text" id="cattr_class" value="<?php echo html($content["attr_class"]); ?>" class="form-control" maxlength="255" />
                        <label class="form-label mt-3" for="cattr_id"><?php echo $BL['be_cnt_attribute_id'] ?></label>
                        <input name="cattr_id" type="text" id="cattr_id" value="<?php echo html($content["attr_id"]); ?>" class="form-control" maxlength="255" />
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0"><?php echo $BL['be_ftptakeover_status'] ?></h2>
            </div>
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-12 md:col-6">
                        <div class="form-field mb-2">
                            <input class="check" name="set_livedate" type="checkbox" id="set_livedate" value="1"<?php is_checked(1, $set_livedate) ?> onclick="content_date_set('live', this.checked ? '<?php echo $content["livedate"]; ?>' : '');" />
                            <label for="set_livedate"><?php echo $BL['be_article_abegin'] ?></label>
                        </div>
                        <input name="clivedate" type="text" id="clivedate" class="form-control" value="<?php echo $content["livedate"]; ?>" data-datepicker="1" data-datepicker-alt-format="<?php echo html($backend_date_format['flatpickr']) ?>" data-datepicker-24h="<?php echo $backend_date_format['time_24hr'] ? '1' : '0' ?>" data-datepicker-weekstart="<?php echo $backend_weekstart ?>" data-datepicker-defaulthour="0" data-datepicker-defaultminute="0" data-datepicker-defaultseconds="0" data-datepicker-label="<?php echo html($BL['be_article_abegin']) ?>" onchange="content_date_changed('live');" />
                    </div>

                    <div class="col-12 md:col-6">
                        <div class="form-field mb-2">
                            <input class="check" name="set_killdate" type="checkbox" id="set_killdate" value="1"<?php is_checked(1, $set_killdate) ?> onclick="content_date_set('kill', this.checked ? '<?php echo $content["killdate"] ?>' : '');" />
                            <label for="set_killdate"><?php echo $BL['be_article_aend'] ?></label>
                        </div>
                        <input name="ckilldate" type="text" id="ckilldate" class="form-control" value="<?php echo $content["killdate"]; ?>" data-datepicker="1" data-datepicker-alt-format="<?php echo html($backend_date_format['flatpickr']) ?>" data-datepicker-24h="<?php echo $backend_date_format['time_24hr'] ? '1' : '0' ?>" data-datepicker-weekstart="<?php echo $backend_weekstart ?>" data-datepicker-defaulthour="23" data-datepicker-defaultminute="59" data-datepicker-defaultseconds="59" data-datepicker-label="<?php echo html($BL['be_article_aend']) ?>" onchange="content_date_changed('kill');" />
                    </div>

                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-end gap-4">
                            <div class="form-field">
                                <input class="check" name="cvisible" type="checkbox" id="cvisible" value="1"<?php is_checked(1, $content["visible"]); ?> />
                                <label for="cvisible"><?php echo $BL['be_admin_struct_visible'] ?></label>
                            </div>
                            <div>
                                <label class="form-label visually-hidden" for="cgranted"><?php echo $BL['be_ftptakeover_status'] ?></label>
                                <select name="cgranted" id="cgranted" class="form-control">
                                    <option value="0"<?php is_selected(0, $content["granted"]); ?>><?php echo $BL['be_visible_for_everybody'] ?></option>
                                    <option value="1"<?php is_selected(1, $content["granted"]); ?>><?php echo $BL['be_granted_feuser'] ?></option>
                                    <option value="2"<?php is_selected(2, $content["granted"]); ?>><?php echo $BL['be_hidden_for_feuser'] ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="csorting"><?php echo $BL['be_cnt_sortvalue'] ?></label>
                                <input name="csorting" type="text" id="csorting" value="<?php echo $content["sorting"] ?>" class="form-control" maxlength="10" onkeyup="if(!parseInt(this.value,10))this.value='0';" />
                            </div>
                        </div>
                    </div>

                </div>

                <input name="caktion" type="hidden" id="caktion" value="1" />
                <input name="caid" type="hidden" id="caid" value="<?php echo $article["article_id"] ?>" />
                <input name="cid" type="hidden" id="cid" value="<?php echo  $content["id"] ?>" />
                <input name="ctype" type="hidden" id="ctype" value="<?php echo  $content["type"] ?>" />

            </div>
            <div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
                <?php echo $_save_close_buttons ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <label class="form-label" for="ccomment"><?php echo $BL['be_profile_label_notes'] ?></label>
                <textarea name="ccomment" id="ccomment" class="form-control autosize" rows="5"><?php echo html($content["comment"]) ?></textarea>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">

    var cTabStatus = <?php echo $content["tab_type"] ? 'true' : 'false' ?>,
        loadblock = true;

    function checkTabStatus(tabVal) {

        cTabStatus = tabVal.options[tabVal.selectedIndex].value !== '0';

        document.getElementById('ctab2').style.display = cTabStatus ? '' : 'none';

        tabVal.blur();
    }

    function setTabStatus(enabled) {
        document.getElementById('ctab').disabled = !enabled;
    }

    function checkCntBlockPaginate(obj) {
        var paginate = document.getElementById("cpaginate_page"),
            block = obj.options[obj.selectedIndex].value,
            system1 = document.getElementById('system1'),
            ctab = document.getElementById('ctab');

        if(block === 'SYSTEM') {
            system1.style.display = '';
            ctab.value = '0';
            ctab.disabled = true;
            cTabStatus = false;

            document.getElementById('ctab2').style.display = 'none';

        } else {

            system1.style.display = 'none';
            ctab.disabled = false;

        }

        if(block !== 'CONTENT') {
            if(paginate.value !== "0" && !loadblock) {
                if(!confirm("<?php echo $BL['be_cnt_subsection_warning'] ?>")) {
                    obj.selectedIndex = 0;
                    return false;
                }
            }
            paginate.disabled = true;
        } else {
            paginate.disabled = false;
        }
    }

    function checkCp() {

        var ctab = document.getElementById('ctab'),
            ctab_title = document.getElementById('ctab_title'),
            ctab_number = document.getElementById('ctab_number');

        if(ctab.selectedIndex > 0 && ctab_title.value === '' && ctab_number.value === '') {
            return confirm('<?php echo PHPWCMS_CHARSET === 'utf-8' ? $BL['confirm_cp_tab_warning'] : mb_convert_encoding($BL['confirm_cp_tab_warning'], PHPWCMS_CHARSET); ?>');
        }

        return true;
    }

    checkCntBlockPaginate(document.getElementById("cblock"));
    loadblock = false;

</script>

</form>
