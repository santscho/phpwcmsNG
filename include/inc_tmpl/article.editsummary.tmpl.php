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

// Be more modern here — we start switch to jQuery and overwrite non-used MooTools with jQuery call
initJsAutocompleter();

unset($_SESSION['filebrowser_image_target']);

$template_default['article']['image_default_width']      = $template_default['article']['image_default_width'] ?? '';
$template_default['article']['image_default_height']     = $template_default['article']['image_default_height'] ?? '';
$template_default['article']['imagelist_default_width']  = $template_default['article']['imagelist_default_width'] ?? '';
$template_default['article']['imagelist_default_height'] = $template_default['article']['imagelist_default_height'] ?? '';

// the begin/end fields hold the canonical database value; the date picker only
// shows it in the format the user picked in the profile
$backend_date_format = backend_date_format($_SESSION['wcs_user_date_format'] ?? '');
$article_begin_time  = empty($article["article_begin"]) ? false : strtotime($article["article_begin"]);
$article_end_time    = empty($article["article_end"])   ? false : strtotime($article["article_end"]);
$article_begin_value = $article_begin_time === false ? '' : date('Y-m-d H:i:s', $article_begin_time);
$article_end_value   = $article_end_time === false ? '' : date('Y-m-d H:i:s', $article_end_time);

// the "last edit" note beside the card title follows the same format
$article_date_display = $article["article_date"] ?? '';
$article_date_time    = strtotime((string) $article_date_display);
if ($article_date_time !== false) {
    $article_date_display = date($backend_date_format['php'], $article_date_time);
}

?>
<form action="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=1&amp;id=<?php echo $article["article_id"] ?>" method="post" name="article" id="article">

<div class="card mb-5">
    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
        <h2 class="h6 mb-0"><?php echo $BL['be_article_estitle'] ?></h2>
<?php if(isset($article["article_date"])) { ?>
        <span class="small fg-secondary"><?php echo $BL['be_article_eslastedit'] ?>: <?php echo (empty($_POST["article_update"]) || !intval($_POST["article_update"])) ? $article_date_display : $BL['be_article_esnoupdate']; ?></span>
<?php } ?>
    </div>
    <div class="card-body">
        <div class="row g-4">

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_cid"><?php echo $BL['be_article_cat'] ?></label>
                <select name="article_cid" id="article_cid" class="form-control">
<?php
                //keine definierte Kategorie = allgemeine Artikelkategorie
                echo '<option value="0"'.((!$article["article_catid"])?' selected="selected"':'').">".$BL['be_admin_struct_index']."</option>\n";
                struct_select_menu(0, 0, $article["article_catid"]);
?>
                </select>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_title"><a href="#" id="cat-as-articletitle" class="link"><?php echo $BL['be_article_atitle'] ?></a></label>
                <input name="article_title" type="text" class="form-control" id="article_title" value="<?php echo html($article["article_title"]) ?>" size="40" maxlength="5000" />
                <div class="form-field mt-3">
                    <input class="check" name="article_notitle" id="article_notitle" type="checkbox" value="1" <?php is_checked($article["article_notitle"],1) ?> />
                    <label for="article_notitle"><?php echo $BL['be_admin_struct_hide1'] ?></label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label" for="article_subtitle"><?php echo $BL['be_article_asubtitle'] ?></label>
                <input name="article_subtitle" type="text" class="form-control" id="article_subtitle" value="<?php echo html($article["article_subtitle"]) ?>" size="40" maxlength="5000" />
            </div>

            <div class="col-12 md:col-6">
                <div class="form-field mb-2">
                    <input class="check" name="set_begin" type="checkbox" id="set_begin" value="1"<?php is_checked(1, $set_begin) ?> onclick="document.article.article_begin.value = this.checked ? '<?php echo $article_begin_value ?>' : '';" />
                    <label for="set_begin"><?php echo $BL['be_article_abegin'] ?></label>
                </div>
                <input name="article_begin" type="text" id="article_begin" class="form-control" value="<?php echo $article_begin_value ?>" data-datepicker="1" data-datepicker-alt-format="<?php echo html($backend_date_format['flatpickr']) ?>" data-datepicker-24h="<?php echo $backend_date_format['time_24hr'] ? '1' : '0' ?>" data-datepicker-label="<?php echo html($BL['be_article_abegin']) ?>" onchange="article_date_changed('begin');" />
            </div>

            <div class="col-12 md:col-6">
                <div class="form-field mb-2">
                    <input class="check" name="set_end" type="checkbox" id="set_end" value="1"<?php is_checked(1, $set_end) ?> onclick="document.article.article_end.value = this.checked ? '<?php echo $article_end_value ?>' : '';" />
                    <label for="set_end"><?php echo $BL['be_article_aend'] ?></label>
                </div>
                <input name="article_end" type="text" id="article_end" class="form-control" value="<?php echo $article_end_value ?>" data-datepicker="1" data-datepicker-alt-format="<?php echo html($backend_date_format['flatpickr']) ?>" data-datepicker-24h="<?php echo $backend_date_format['time_24hr'] ? '1' : '0' ?>" data-datepicker-label="<?php echo $BL['be_article_aend'] ?>" onchange="article_date_changed('end');" />
            </div>

            <script type="text/javascript">
                // A picked date has to mark its own boundary active, and a begin after
                // the end has always cleared that end, date only like dynCalendar did
                function article_date_changed(field) {

                    var form = document.article;
                    var begin_date = form.article_begin.value.substring(0, 10);
                    var end_date = form.article_end.value.substring(0, 10);

                    if (field === 'begin') {
                        form.set_begin.checked = form.article_begin.value !== '';
                        if (begin_date && end_date && begin_date > end_date) {
                            form.article_end.value = '';
                            form.set_end.checked = false;
                        }
                    } else {
                        form.set_end.checked = form.article_end.value !== '';
                        if (begin_date && end_date && begin_date > end_date) {
                            form.article_begin.value = '';
                            form.set_begin.checked = false;
                        }
                    }

                }
            </script>

            <div class="col-6 md:col-3">
                <label class="form-label" for="article_sort"><?php echo $BL['be_cnt_sortvalue'] ?></label>
                <input name="article_sort" type="text" id="article_sort" value="<?php echo empty($article["article_sort"]) ? 0 : intval($article["article_sort"]) ?>" class="form-control" maxlength="10" onkeyup="if(!parseInt(this.value,10))this.value='0';" />
            </div>

            <div class="col-6 md:col-3">
                <label class="form-label" for="article_priorize"><?php echo $BL['be_priorize'] ?></label>
                <select name="article_priorize" id="article_priorize" class="form-control">
<?php

    for($x=30; $x>=-30; $x--) {

        echo '  <option value="'.$x.'"';
        is_selected($x, $article["article_priorize"]);
        echo '>'. ( $x==0 ? $BL['be_cnt_default'] : $x ) .'</option>';

    }

?>
                </select>
            </div>

            <div class="col-6 md:col-3">
                <label class="form-label" for="article_aliasid"><?php echo $BL['be_alias_articleID'] ?></label>
                <input name="article_aliasid" type="text" class="form-control" id="article_aliasid" value="<?php echo $article["article_aliasid"] ?: ''; ?>" size="11" maxlength="11" />
            </div>

            <div class="col-6 md:col-3 d-flex align-items-end">
                <div class="form-field">
                    <input class="check" name="article_headerdata" id="article_headerdata" type="checkbox" value="1" <?php is_checked($article["article_headerdata"],1) ?> />
                    <label for="article_headerdata"><?php echo $BL['be_alias_useAll'] ?></label>
                </div>
            </div>

<?php   if(count($phpwcms['allowed_lang']) > 1): ?>

            <div class="col-12">
                <div class="form-label"><?php echo $BL['be_profile_label_lang'] ?></div>
                <div class="d-flex flex-wrap gap-4 lang-select">
                    <div class="form-field">
                        <input class="radio lang-default" type="radio" name="article_lang" id="article_lang_default" value=""<?php is_checked('', $article['article_lang']); ?> />
                        <label for="article_lang_default"><?php echo get_language_name($phpwcms['default_lang']) . ' ('.$BL['be_admin_tmpl_default'].')' ?></label>
                    </div>
<?php       foreach($phpwcms['allowed_lang'] as $key => $lang):

                    $lang = strtolower($lang);

                    if($lang == $phpwcms['default_lang']) {
                        continue;
                    }

?>
                    <div class="form-field">
                        <input class="radio lang-opt" type="radio" name="article_lang" id="article_lang_<?php echo $lang ?>" value="<?php echo $lang ?>"<?php is_checked($lang, $article['article_lang']) ?> />
                        <label for="article_lang_<?php echo $lang ?>"><?php echo get_language_name($lang) ?></label>
                    </div>
<?php       endforeach; ?>
                </div>

                <div class="mt-4 pt-4 border-top" id="lang-id-select"<?php if($article['article_lang'] == ''): ?> style="display:none"<?php endif; ?>>
                    <div class="d-flex flex-wrap align-items-end gap-4">
                        <div class="form-field">
                            <input class="radio" type="radio" name="article_lang_type" id="article_lang_type_category" value="category"<?php is_checked('category', $article['article_lang_type']); ?> />
                            <label for="article_lang_type_category"><?php echo $BL['be_article_cat'] ?> ID</label>
                        </div>
                        <div class="form-field">
                            <input class="radio" type="radio" name="article_lang_type" id="article_lang_type_article" value="article"<?php is_checked('article', $article['article_lang_type']); ?> />
                            <label for="article_lang_type_article"><?php echo $BL['be_cnt_articles'] ?> ID</label>
                        </div>
                        <div>
                            <label class="form-label" for="article_lang_id"><?php echo get_language_name($phpwcms['default_lang']) . ' ('.$BL['be_admin_tmpl_default'].')' ?></label>
                            <input name="article_lang_id" type="text" class="form-control" id="article_lang_id" value="<?php echo $article['article_lang_id'] ?: ''; ?>" size="11" maxlength="11" />
                        </div>
                    </div>
                </div>
            </div>

<?php   endif;

    $struct_alias = get_struct_alias($article["article_catid"]);
    $struct_parental = get_struct_alias($article["article_catid"], true);

?>

            <div class="col-12">
                <div class="d-flex flex-wrap gap-4">
                    <a href="#" class="link small" onclick="return set_article_alias();"><?php echo $BL['be_article_urlalias'] ?></a>
                    <a href="#" id="parent_alias" class="link small" data-bs-toggle="tooltip" title="<?php echo html($struct_parental); ?>" data-alias="<?php echo html($struct_parental); ?>"><?php echo $BL['be_parental_alias'] ?></a>
                    <a href="#" id="struct_alias" class="link small" data-bs-toggle="tooltip" title="<?php echo html($struct_alias); ?>" data-alias="<?php echo html($struct_alias); ?>"><?php echo $BL['be_admin_struct_title'] ?></a>
                </div>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_alias"><?php echo $BL['be_article_urlalias'] ?></label>
                <input name="article_alias" type="text" class="form-control" id="article_alias" value="<?php echo html($article["article_alias"]) ?>" size="40" maxlength="1000"<?php
            if(empty($phpwcms['allow_empty_alias'])): ?> onfocus="set_article_alias(true);"<?php endif; ?> onchange="this.value=create_alias(this.value);" />
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_pagetitle"><?php echo $BL['be_admin_page_pagetitle'] ?></label>
                <input name="article_pagetitle" type="text" id="article_pagetitle" class="form-control" value="<?php echo html($article['article_pagetitle']) ?>" size="40" maxlength="2000" />
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_menutitle"><?php echo $BL['article_menu_title'] ?></label>
                <input name="article_menutitle" type="text" id="article_menutitle" class="form-control" value="<?php echo html($article["article_menutitle"]) ?>" size="40" />
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_redirect"><?php echo $BL['be_article_aredirect'] ?></label>
                <input name="article_redirect" type="text" id="article_redirect" class="form-control" value="<?php echo html($article["article_redirect"]) ?>" size="40" />
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_canonical"><?php echo $BL['be_canonical'] ?></label>
                <input name="article_canonical" type="text" id="article_canonical" class="form-control" value="<?php echo html($article["article_canonical"]) ?>" size="40" maxlength="2000" />
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="article_meta_class"><?php echo $BL['be_cnt_css_class']; ?></label>
                <input name="article_meta_class" type="text" id="article_meta_class" class="form-control" value="<?php echo html($article["article_meta"]['class']) ?>" size="40" maxlength="250" />
            </div>

            <div class="col-12">
                <label class="form-label" for="article_keyword_autosuggest"><?php echo $BL['be_article_akeywords'] ?></label>
                <input type="text" id="article_keyword_autosuggest" class="form-control" /><input type="hidden" name="article_keyword" id="article_keyword" value="<?php echo html($article["article_keyword"]) ?>" />
            </div>

            <div class="col-12">
                <label class="form-label" for="article_description"><?php echo $BL['be_cnt_description'] ?></label>
                <textarea name="article_description" rows="4" class="form-control autosize" id="article_description"><?php echo html($article["article_description"]) ?></textarea>
            </div>

            <div class="col-12">
                <div class="border-top pt-4">
                    <h3 class="h6 mb-4"><?php echo $BL['be_admin_struct_template'] ?></h3>
                    <div class="row g-4">
                        <div class="col-12 md:col-6">
                            <label class="form-label" for="article_tmpllist"><?php echo $BL['be_article_forlist'] ?></label>
                            <select name="article_tmpllist" id="article_tmpllist" class="form-control">
<?php
// templates for article listing
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/articlesummary/list');
if($article['image']['tmpllist'] == 'default') {
    $vals= ' selected="selected"';
} else {
    $vals = '';
}
echo '<option value="default"'.$vals.'>'.$BL['be_cnt_default']."</option>\n";
if(count($tmpllist)) {
    foreach($tmpllist as $val) {
        $vals = '';
        if($val == $article['image']['tmpllist']) $vals= ' selected="selected"';
        $val = htmlspecialchars($val);
        echo '<option value="'.$val.'"'.$vals.'>'.$val."</option>\n";
    }
}

?>
                            </select>
                        </div>
                        <div class="col-12 md:col-6">
                            <label class="form-label" for="article_tmplfull"><?php echo $BL['be_article_forfull'] ?></label>
                            <select name="article_tmplfull" id="article_tmplfull" class="form-control">
<?php
// templates for full article
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/articlesummary/article');
if($article['image']['tmplfull'] == 'default') $vals= ' selected="selected"';
echo '<option value="default"'.$vals.'>'.$BL['be_cnt_default']."</option>\n";
if(count($tmpllist)) {
    foreach($tmpllist as $val) {
        $vals = '';
        if($val == $article['image']['tmplfull']) $vals= ' selected="selected"';
        $val = htmlspecialchars($val);
        echo '<option value="'.$val.'"'.$vals.'>'.$val."</option>\n";
    }
}

?>
                            </select>
                        </div>

                        <div class="col-12 md:col-6">
                            <label class="form-label" for="article_listmaxwords"><?php echo $BL['be_cnt_results_wordlimit'] ?></label>
                            <input name="article_listmaxwords" type="text" id="article_listmaxwords" class="form-control" value="<?php echo empty($article['image']['list_maxwords']) ? '' : intval($article['image']['list_maxwords']) ?>" size="10" maxlength="6" />
                        </div>

                        <div class="col-12 md:col-6">
                            <div class="d-flex flex-column gap-2 md:pt-5">
                                <div class="form-field">
                                    <input class="check" name="article_hidesummary" type="checkbox" id="article_hidesummary" value="1"<?php is_checked(1, $article["article_hidesummary"]); ?> />
                                    <label for="article_hidesummary"><?php echo $BL['be_article_nosummary'] ?></label>
                                </div>
                                <div class="form-field">
                                    <input class="check" name="article_morelink" type="checkbox" id="article_morelink" value="1"<?php is_checked(1, $article['article_morelink']); ?> />
                                    <label for="article_morelink"><?php echo $BL['be_article_morelink'] ?></label>
                                </div>
                                <div class="form-field">
                                    <input class="check" name="article_noteaser" type="checkbox" id="article_noteaser" value="1"<?php is_checked(1, $article['article_noteaser']); ?> />
                                    <label for="article_noteaser"><?php echo $BL['be_article_noteaser'] ?></label>
                                </div>
                                <div class="form-field">
                                    <input class="check" name="article_paginate" type="checkbox" id="article_paginate" value="1"<?php is_checked(1, $article["article_paginate"]); ?> />
                                    <label for="article_paginate"><?php echo $BL['be_cnt_pagination'] ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <h2 class="h6 mb-0"><?php echo $BL['be_article_asummary'] ?></h2>
    </div>
    <div class="card-body">
<?php

$wysiwyg_editor = array(
    'value' => $article["article_summary"],
    'field' => 'article_summary',
    'height' => '450px',
    'width' => '100%',
    'rows' => '15',
    'editor' => $_SESSION["WYSIWYG_EDITOR"],
    'lang' => 'en'
);
include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';

?>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
        <h2 class="h6 mb-0"><?php echo $BL['be_cnt_image'] ?></h2>
        <span class="badge theme-secondary"><?php echo $BL['be_article_forfull'] ?></span>
    </div>
    <div class="card-body">
        <div class="row g-4">

            <div class="col-12">
                <label class="form-label" for="cimage_name"><?php echo $BL['be_cnt_image'] ?></label>
                <div class="d-flex flex-wrap gap-2">
                    <input name="cimage_name" type="text" id="cimage_name" class="form-control flex-grow-1" value="<?php echo html($article['image']['name']) ?>" size="40" maxlength="250" onfocus="this.blur()" />
                    <a href="#" class="btn btn-outline theme-secondary btn-sm" data-bs-toggle="tooltip" title="<?php echo html($BL['be_cnt_openimagebrowser']) ?>" onclick="openFileBrowser('filebrowser.php?opt=0&amp;target=summary');return false;"><?php echo $BL['be_cnt_openimagebrowser'] ?></a>
                    <?php echo backend_action('#', 'trash', $BL['be_cnt_delimage'], 'normal', ' onclick="document.article.cimage_name.value=\'\';document.article.cimage_id.value=\'0\';this.blur();return false;"', 'danger'); ?>
                    <input name="cimage_id" type="hidden" value="<?php echo $article['image']['id'] ?>" />
                </div>
            </div>

            <div class="col-12 md:col-6">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" for="cimage_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                        <input name="cimage_width" type="text" class="form-control" id="cimage_width" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($article['image']['width']) ? $template_default['article']['image_default_width'] : $article['image']['width']; ?>" />
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="cimage_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                        <input name="cimage_height" type="text" class="form-control" id="cimage_height" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($article['image']['height']) ? $template_default['article']['image_default_height'] : $article['image']['height']; ?>" />
                    </div>
                    <div class="col-12">
                        <div class="form-field">
                            <input class="check" name="cimage_zoom" type="checkbox" id="cimage_zoom" value="1" <?php is_checked(1, $article['image']['zoom']); ?> />
                            <label for="cimage_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="cimage_lightbox" type="checkbox" id="cimage_lightbox" value="1" <?php is_checked(1, empty($article['image']['lightbox']) ? 0 : 1); ?> onchange="if(this.checked){getObjectById('cimage_zoom').checked=true;}" />
                            <label for="cimage_lightbox"><?php echo $BL['be_cnt_lightbox'] ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="cimage_caption"><?php echo $BL['be_cnt_description'] ?></label>
                <textarea name="cimage_caption" cols="30" rows="3" class="form-control autosize" id="cimage_caption"><?php echo html($article['image']['caption']) ?></textarea>
                <div class="form-text">
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
                <div class="form-field mt-3">
                    <input class="check" type="checkbox" name="cimage_caption_suppress" id="cimage_caption_suppress" value="1" <?php is_checked(1, empty($article['image']['caption_suppress']) ? 0 : 1); ?> />
                    <label for="cimage_caption_suppress"><?php echo $BL['be_suppress_render_caption']; ?></label>
                </div>
            </div>

            <div class="col-12">
<?php

                    $_SESSION['image_browser_article'] = 1;

                    $thumb_image = false;
                    if(!empty($article["image"]["hash"])) {
                        $thumb_image = get_cached_image(array(
                            "target_ext" => $article['image']['ext'],
                            "image_name" => $article['image']['hash'] . '.' . $article['image']['ext'],
                            "thumb_name" => md5($article['image']['hash'].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
                        ));
                    }
                    echo $thumb_image ? '<img src="'. $thumb_image['src'] .'" '.$thumb_image[3].' alt="" class="img-fluid" />' : '';

?>
            </div>

        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
        <h2 class="h6 mb-0"><?php echo $BL['be_cnt_image'] ?></h2>
        <span class="badge theme-secondary"><?php echo $BL['be_article_forlist'] ?></span>
    </div>
    <div class="card-body">
<?php

// set default list values
if(!isset($article['image']['list_usesummary'])) {
    $article['image']['list_usesummary'] = 0;
    $article['image']['list_name'] = '';
    $article['image']['list_id'] = 0;
    $article['image']['list_width'] = '';
    $article['image']['list_height'] = '';
    $article['image']['list_zoom'] = 0;
    $article['image']['list_caption'] = '';
}

?>
        <div class="form-field mb-4">
            <input class="check" name="cimage_usesummary" type="checkbox" id="cimage_usesummary" value="1" <?php is_checked(1, $article['image']['list_usesummary']); ?> />
            <label for="cimage_usesummary"><?php echo $BL['be_cnt_same_as_summary'] ?></label>
        </div>

        <div class="row g-4">

            <div class="col-12">
                <label class="form-label" for="cimage_list_name"><?php echo $BL['be_cnt_image'] ?></label>
                <div class="d-flex flex-wrap gap-2">
                    <input name="cimage_list_name" type="text" id="cimage_list_name" class="form-control flex-grow-1" value="<?php echo html($article['image']['list_name']) ?>" size="40" maxlength="250" onfocus="this.blur()" />
                    <a href="#" class="btn btn-outline theme-secondary btn-sm" data-bs-toggle="tooltip" title="<?php echo html($BL['be_cnt_openimagebrowser']) ?>" onclick="openFileBrowser('filebrowser.php?opt=0&amp;target=list');return false;"><?php echo $BL['be_cnt_openimagebrowser'] ?></a>
                    <?php echo backend_action('#', 'trash', $BL['be_cnt_delimage'], 'normal', ' onclick="document.article.cimage_list_name.value=\'\';document.article.cimage_list_id.value=\'0\';this.blur();return false;"', 'danger'); ?>
                    <input name="cimage_list_id" type="hidden" value="<?php echo $article['image']['list_id'] ?>" />
                </div>
            </div>

            <div class="col-12 md:col-6">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" for="cimage_list_width"><?php echo $BL['be_cnt_maxw'] ?></label>
                        <input name="cimage_list_width" type="text" class="form-control" id="cimage_list_width" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($article['image']['list_width']) ? $template_default['article']['imagelist_default_width'] : $article['image']['list_width']; ?>" />
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="cimage_list_height"><?php echo $BL['be_cnt_maxh'] ?></label>
                        <input name="cimage_list_height" type="text" class="form-control" id="cimage_list_height" maxlength="4" onkeyup="if(!parseInt(this.value,10)) this.value='';" value="<?php echo empty($article['image']['list_height']) ? $template_default['article']['imagelist_default_height'] : $article['image']['list_height']; ?>" />
                    </div>
                    <div class="col-12">
                        <div class="form-field">
                            <input class="check" name="cimage_list_zoom" type="checkbox" id="cimage_list_zoom" value="1" <?php is_checked(1, $article['image']['list_zoom']); ?> />
                            <label for="cimage_list_zoom"><?php echo $BL['be_cnt_enlarge'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="cimage_list_lightbox" type="checkbox" id="cimage_list_lightbox" value="1" <?php is_checked(1, empty($article['image']['list_lightbox']) ? 0 : 1); ?> onchange="if(this.checked){getObjectById('cimage_list_zoom').checked=true;}" />
                            <label for="cimage_list_lightbox"><?php echo $BL['be_cnt_lightbox'] ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 md:col-6">
                <label class="form-label" for="cimage_list_caption"><?php echo $BL['be_cnt_description'] ?></label>
                <textarea name="cimage_list_caption" cols="30" rows="3" class="form-control autosize" id="cimage_list_caption"><?php echo html($article['image']['list_caption']) ?></textarea>
                <div class="form-text">
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
                <div class="form-field mt-3">
                    <input class="check" type="checkbox" name="cimage_list_caption_suppress" id="cimage_list_caption_suppress" value="1" <?php is_checked(1, empty($article['image']['list_caption_suppress']) ? 0 : 1); ?> />
                    <label for="cimage_list_caption_suppress"><?php echo $BL['be_suppress_render_caption']; ?></label>
                </div>
            </div>

            <div class="col-12">
<?php

    $_SESSION['image_browser_article'] = 1;

    $thumb_image = false;
    if(!empty($article["image"]["list_hash"])) {
        $thumb_image = get_cached_image(array(
            "target_ext" => $article['image']['list_ext'],
            "image_name" => $article['image']['list_hash'] . '.' . $article['image']['list_ext'],
            "thumb_name" => md5($article['image']['list_hash'].$phpwcms["img_list_width"].$phpwcms["img_list_height"].$phpwcms["sharpen_level"].$phpwcms['colorspace'])
        ));
    }
    echo $thumb_image ? '<img src="'. $thumb_image['src'] .'" '.$thumb_image[3].' alt="" class="img-fluid" />' : '';

?>
            </div>

        </div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <h2 class="h6 mb-0"><?php echo $BL['be_ftptakeover_status'] ?></h2>
    </div>
    <div class="card-body">
        <div class="row g-4">

<?php
            if($_SESSION["wcs_user_admin"]) {
?>
            <div class="col-12 md:col-6">
                <label class="form-label" for="article_uid"><?php echo $BL['be_article_articleowner'] ?></label>
                <select name="article_uid" id="article_uid" class="form-control">
<?php
                $u_sql = "SELECT usr_id, usr_name, usr_login, usr_admin FROM ".DB_PREPEND."phpwcms_user WHERE usr_aktiv=1 ORDER BY usr_admin DESC, usr_name";
                $u_result = _dbQuery($u_sql);
                if(isset($u_result[0]['usr_id'])) {
                    foreach($u_result as $u_row) {
                        $u_label = html($u_row['usr_name'] ?: $u_row['usr_login']);
                        if(intval($u_row['usr_admin'])) {
                            $u_label .= ' (' . $BL['be_article_adminuser'] . ')';
                        }
                        echo '<option value="'.$u_row['usr_id'].'"';
                        if($u_row['usr_id'] == $article["article_uid"]) {
                            echo ' selected="selected"';
                        }
                        echo '>'.$u_label.'</option>';
                    }

                }
?>
                </select>
            </div>
<?php
            }
?>
            <div class="col-12 md:col-6">
                <label class="form-label" for="article_username"><?php echo $BL['be_article_username'] ?></label>
                <input name="article_username" type="text" id="article_username" class="form-control" value="<?php echo html($article["article_username"]) ?>" size="40" maxlength="200" />
            </div>

            <div class="col-12">
                <div class="border-top pt-4">
                    <h3 class="h6 mb-4"><?php echo $BL['be_cache'] ?></h3>
                    <div class="row g-4">
                        <div class="col-12 md:col-6">
                            <div class="form-field">
                                <input class="check" name="article_cacheoff" type="checkbox" id="article_cacheoff" value="1" <?php if($article["article_timeout"] === '0') echo "checked"; ?> />
                                <label for="article_cacheoff"><?php echo $BL['be_off'] ?></label>
                            </div>
                        </div>
                        <div class="col-12 md:col-6">
                            <label class="form-label" for="article_timeout"><?php echo $BL['be_cache_timeout'] ?></label>
                            <select name="article_timeout" id="article_timeout" class="form-control" onchange="document.article.article_cacheoff.checked=false;">
<?php
                                echo '<option value=" ">'.$BL['be_admin_tmpl_default']."</option>\n";
                                echo '<option value="60"'.is_selected($article["article_timeout"], '60', 0, 0).'>&nbsp;&nbsp;1 '.$BL['be_date_minute']."</option>\n";
                                echo '<option value="300"'.is_selected($article["article_timeout"], '300', 0, 0).'>&nbsp;&nbsp;5 '.$BL['be_date_minutes']."</option>\n";
                                echo '<option value="900"'.is_selected($article["article_timeout"], '900', 0, 0).'>15 '.$BL['be_date_minutes']."</option>\n";
                                echo '<option value="1800"'.is_selected($article["article_timeout"], '1800', 0, 0).'>30 '.$BL['be_date_minutes']."</option>\n";
                                echo '<option value="3600"'.is_selected($article["article_timeout"], '3600', 0, 0).'>&nbsp;&nbsp;1 '.$BL['be_date_hour']."</option>\n";
                                echo '<option value="14400"'.is_selected($article["article_timeout"], '14400', 0, 0).'>&nbsp;&nbsp;4 '.$BL['be_date_hours']."</option>\n";
                                echo '<option value="43200"'.is_selected($article["article_timeout"], '43200', 0, 0).'>12 '.$BL['be_date_hours']."</option>\n";
                                echo '<option value="86400"'.is_selected($article["article_timeout"], '86400', 0, 0).'>&nbsp;&nbsp;1 '.$BL['be_date_day']."</option>\n";
                                echo '<option value="172800"'.is_selected($article["article_timeout"], '172800', 0, 0).'>&nbsp;&nbsp;2 '.$BL['be_date_days']."</option>\n";
                                echo '<option value="604800"'.is_selected($article["article_timeout"], '604800', 0, 0).'>&nbsp;&nbsp;1 '.$BL['be_date_week']."</option>\n";
                                echo '<option value="1209600"'.is_selected($article["article_timeout"], '1209600', 0, 0).'>&nbsp;&nbsp;2 '.$BL['be_date_weeks']."</option>\n";
                                echo '<option value="2592000"'.is_selected($article["article_timeout"], '2592000', 0, 0).'>&nbsp;&nbsp;1 '.$BL['be_date_month']."</option>\n";
?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="border-top pt-4">
                    <h3 class="h6 mb-4"><?php echo $BL['be_robots'] ?></h3>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-field">
                            <input class="check" name="article_meta_noindex" type="checkbox" id="article_meta_noindex" value="1"<?php is_checked(1, $article["article_meta"]['noindex']); ?> />
                            <label for="article_meta_noindex"><?php echo $BL['be_robots_noindex'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="article_meta_nofollow" type="checkbox" id="article_meta_nofollow" value="1" <?php is_checked(1, $article["article_meta"]['nofollow']); ?> />
                            <label for="article_meta_nofollow"><?php echo $BL['be_robots_nofollow'] ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="border-top pt-4">
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-field">
                            <input class="check" name="article_nositemap" type="checkbox" id="article_nositemap" value="1"<?php is_checked(1, $article["article_nositemap"]); ?> />
                            <label for="article_nositemap"><?php echo $BL['be_ctype_sitemap'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="article_nosearch" type="checkbox" id="article_nosearch" value="1" <?php is_checked(1, $article['article_nosearch']); ?> />
                            <label for="article_nosearch"><?php echo $BL['be_no_search'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="article_norss" type="checkbox" id="article_norss" value="1" <?php is_checked(1, $article['article_norss']); ?> />
                            <label for="article_norss"><?php echo $BL['be_no_rss'] ?></label>
                        </div>
<?php
                            // Opengraph fallback when creating a new article
                            if(!isset($_POST['article_title']) && empty($article["article_id"]) && defined('ACAT_OPENGRAPH_STATUS') && ACAT_OPENGRAPH_STATUS === false) {
                                $article['article_opengraph'] = 0;
                            }
?>
                        <div class="form-field">
                            <input class="check" name="article_opengraph" type="checkbox" id="article_opengraph" value="1" <?php is_checked(1, $article['article_opengraph']); ?> />
                            <label for="article_opengraph"><?php echo $BL['be_opengraph_support'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="article_aktiv" type="checkbox" id="article_aktiv" value="1"<?php is_checked(1, $article["article_aktiv"]); ?> />
                            <label for="article_aktiv"><?php echo $BL['be_admin_struct_visible'] ?></label>
                        </div>
                        <div class="form-field">
                            <input class="check" name="article_archive" type="checkbox" id="article_archive" value="1" <?php is_checked(1, $article['article_archive_status']); ?> />
                            <label for="article_archive"><?php echo $BL['be_show_archived'] ?></label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
        <input name="updatesubmit" type="submit" class="btn theme-primary btn-solid btn-styled btn-sm" value="<?php echo $article["article_id"] ? $BL['be_article_cnt_button1'] : $BL['be_article_cnt_button2'] ?>" />
        <input name="Submit" type="submit" class="btn btn-outline theme-secondary btn-sm" value="<?php echo $BL['be_article_cnt_button3'] ?>" />
        <input name="donotsubmit" type="submit" class="btn btn-outline theme-secondary btn-sm" value="<?php echo $BL['be_newsletter_button_cancel'] ?>" onclick="return cancelEdit();" />
        <input name="article_update" type="hidden" id="article_update" value="1" />
    </div>
</div>

</form>
<script type="text/javascript">
$(function(){

    $("#article_keyword_autosuggest").autoSuggest('<?php echo PHPWCMS_URL ?>include/inc_act/ajax_connector.php', {
        selectedItemProp: "cat_name",
        selectedValuesProp: 'cat_name',
        searchObjProps: "cat_name",
        queryParam: 'value',
        extraParams: '&method=json&action=category',
        startText: '',
        preFill: $("#article_keyword").val(),
        neverSubmit: true,
        asHtmlID: 'keyword-autosuggest'
    });

    $('#article').submit(function(){
        $("#article_keyword").val($('#as-values-keyword-autosuggest').val());
    });

    // Handle language switch click
    var langIdSelect = $('#lang-id-select');

    $('input.lang-opt').change(function(){
        langIdSelect.show();
    });

    $('input.lang-default').change(function(){
        langIdSelect.hide();
    });

    $('#struct_alias,#parent_alias').click(function() {

        var struct = $(this).data('alias') || $('#article_cid option:selected').text();
        var title = $.trim($('#article_title').val());

        if(struct.length) {
            struct = struct.replace(/^-+/gi, '').trim();

            if(title) {
                struct += '<?php if($phpwcms['alias_allow_slash']): ?>/<?php else: ?>-<?php endif; ?>'+title;
            }
        } else {
            struct = title;
        }

        $('#article_alias').val( create_alias(struct) );
    });

    $('#cat-as-articletitle').click(function(evnt){
        evnt.preventDefault();
        var currentCat = $('#article_cid option:selected').text();
        if(currentCat) {
            $('#article_title').val(currentCat.replace(/^-+ /, ''));
        }
    });
});

function cancelEdit() {
    document.location.href='phpwcms.php'+'?<?php echo CSRF_GET_TOKEN; ?>&do=articles<?php echo $article["article_id"] ? '&p=2&s=1&id='.$article["article_id"] : '' ?>';
    return false;
}

</script>
