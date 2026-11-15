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

// dates on this screen follow the format picked in the profile
$backend_date_format = backend_date_format($_SESSION['wcs_user_date_format'] ?? '');

$article_edit_link    = 'phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=1&amp;id=' . $article["article_id"];
$article_cp_link      = 'phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id=' . $article["article_id"];
$article_preview_link = rel_url(array('phpwcms-preview' => 1), array(), empty($article["article_alias"]) ? 'aid=' . $article["article_id"] : $article["article_alias"]);
$article_language     = strtolower(empty($article["article_lang"]) ? $phpwcms['default_lang'] : $article["article_lang"]);

// the article information card is a grid of label/value pairs
$info_row = static function ($label, $value, $class = 'col-12 md:col-6') {
    return '<div class="' . $class . '"><div class="small fg-secondary">' . $label . '</div><div>' . $value . '</div></div>';
};

// status marks of the article, the conditions the old list used
$article_flags = array(
    $BL['be_ctype_sitemap']            => $article["article_nositemap"] == 1,
    $BL['be_fsearch_searchlabel']      => $article["article_nosearch"] != 1,
    $BL['be_no_rss']                   => $article["article_norss"] != 1,
    $BL['be_opengraph_support']        => $article["article_opengraph"] == 1,
    $BL['be_show_archived']            => $article["article_archive_status"] == 1,
);

// article images
$thumb_image = false;
if (!empty($article["image"]["hash"])) {
    $thumb_image = get_cached_image(array(
        "target_ext"    =>  $article['image']['ext'],
        "image_name"    =>  $article['image']['hash'] . '.' . $article['image']['ext'],
        "thumb_name"    =>  md5($article['image']['hash'] . $phpwcms["img_list_width"] . $phpwcms["img_list_height"] . $phpwcms["sharpen_level"] . $phpwcms['colorspace'])
    ));
}

$thumb_list_image = false;
if (!empty($article["image"]["list_hash"])) {
    $thumb_list_image = get_cached_image(array(
        "target_ext"    =>  $article['image']['list_ext'],
        "image_name"    =>  $article['image']['list_hash'] . '.' . $article['image']['list_ext'],
        "thumb_name"    =>  md5($article['image']['list_hash'] . $phpwcms["img_list_width"] . $phpwcms["img_list_height"] . $phpwcms["sharpen_level"] . $phpwcms['colorspace'])
    ));
}

?>
<form action="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id=<?php echo $article["article_id"] ?>" method="post" name="addcontent" id="addcontent">
<div class="row g-4">

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">

                <h2 class="h6 mb-0 d-flex flex-wrap align-items-center gap-2">
                    <span class="fg-secondary d-inline-flex"><?php echo backend_icon('journal-bookmark-fill') ?></span>
                    <a href="<?php echo $article_edit_link ?>"><?php echo html($article["article_title"]) ?></a>
                    <span class="badge theme-secondary">AID:<?php echo $article["article_id"] ?></span>
                    <span class="badge <?php echo $article["article_aktiv"] ? 'theme-success' : 'theme-secondary' ?>"><?php echo $article["article_aktiv"] ? $BL['be_admin_struct_visible'] : $BL['be_inactive'] ?></span>
                    <span class="badge theme-secondary" data-bs-toggle="tooltip" title="<?php echo html(get_language_name($article_language)) ?>"><?php echo strtoupper($article_language) ?></span>
                </h2>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php
                    echo backend_action($article_edit_link, 'pen', $BL['be_article_cnt_ledit']);
                    echo backend_action(
                        'include/inc_act/act_articlecontent.php?do=3,' . $article["article_id"] . ',0,' . switch_on_off($article["article_aktiv"]),
                        $article["article_aktiv"] ? 'eye' : 'eye-slash',
                        $BL['be_article_cnt_lvisible'],
                        'normal',
                        '',
                        $article["article_aktiv"] ? 'success' : 'danger'
                    );
                    echo backend_action(
                        'include/inc_act/act_articlecontent.php?do=1,' . $article["article_id"],
                        'trash',
                        $BL['be_article_cnt_ldel'],
                        'normal',
                        ' onclick="return confirm(\'' . js_singlequote($BL['be_article_cnt_ldeljs'] . "\n" . $article["article_title"]) . '\');"',
                        'danger'
                    );
                    ?>
                </div>

            </div>
            <div class="card-body">
                <div class="row g-4">

                    <?php
                    echo $info_row($BL['be_article_urlalias'], '<strong>' . html($article["article_alias"]) . '</strong>');

                    if ($article["article_subtitle"]) {
                        echo $info_row($BL['be_article_asubtitle'], '<strong>' . html($article["article_subtitle"]) . '</strong>');
                    }

                    if (!empty($article["article_summary"])) {
                        echo $info_row($BL['be_article_asummary'], html(getCleanSubString(strip_tags($article["article_summary"]), 250, '&#8230;'), false), 'col-12');
                    }

                    echo $info_row($BL['be_article_cat'], html($article["article_cat"]));
                    echo $info_row($BL['be_article_akeywords'], $article["article_keyword"] ? html($article["article_keyword"]) : '<span class="fg-secondary">' . $BL['be_not_set'] . '</span>');

                    if ($article["article_canonical"]) {
                        echo $info_row($BL['be_canonical'], html($article["article_canonical"]));
                    }

                    if ($article["article_redirect"]) {
                        echo $info_row($BL['be_article_cnt_redirect'], html($article["article_redirect"]));
                    }

                    if ($thumb_image || $thumb_list_image) {
                        $image_value = '<div class="d-flex flex-wrap align-items-start gap-2">';
                        if ($thumb_image) {
                            $image_value .= '<img src="' . $thumb_image['src'] . '" ' . $thumb_image[3] . ' alt="" class="img-fluid" data-bs-toggle="tooltip" title="' . html($BL['be_cnt_image']) . '">';
                        }
                        if ($thumb_list_image) {
                            $image_value .= '<img src="' . $thumb_list_image['src'] . '" ' . $thumb_list_image[3] . ' alt="" class="img-fluid' . (empty($article['image']['list_usesummary']) ? '' : ' opacity-50') . '">';
                        }
                        $image_value .= '</div>';
                        echo $info_row($BL['be_cnt_image'], $image_value);
                    }

                    echo $info_row($BL['be_article_username'], html($article["article_username"]));
                    echo $info_row($BL['be_article_eslastedit'], phpwcms_strtotime($article["article_date"], $backend_date_format['php'], '<span class="fg-secondary">' . $BL['be_not_set'] . '</span>'));
                    echo $info_row($BL['be_fprivedit_created'], date($backend_date_format['php'], $article["article_created"]));
                    echo $info_row($BL['be_article_cnt_start'], $set_begin ? phpwcms_strtotime($article["article_begin"], $backend_date_format['php'], '') : '<span class="fg-secondary">' . $BL['be_not_set'] . '</span>');
                    echo $info_row($BL['be_article_cnt_end'], $set_end ? phpwcms_strtotime($article["article_end"], $backend_date_format['php'], '') : '<span class="fg-secondary">' . $BL['be_not_set'] . '</span>');
                    echo $info_row($BL['be_cnt_sortvalue'], $article["article_sort"]);
                    echo $info_row($BL['be_priorize'], $article["article_priorize"]);

                    $status_value = '<div class="d-flex flex-wrap gap-2">';
                    foreach ($article_flags as $flag_label => $flag_active) {
                        $status_value .= '<span class="badge ' . ($flag_active ? 'theme-success' : 'theme-secondary') . '">' . $flag_label . '</span>';
                    }
                    $status_value .= '</div>';
                    echo $info_row($BL['be_ftptakeover_status'], $status_value, 'col-12');

                    if ($article["article_meta"]['noindex'] || $article["article_meta"]['nofollow']) {
                        $robots_value = '<div class="d-flex flex-wrap gap-2">';
                        if ($article["article_meta"]['noindex']) {
                            $robots_value .= '<span class="badge theme-danger">' . $BL['be_robots_noindex'] . '</span>';
                        }
                        if ($article["article_meta"]['nofollow']) {
                            $robots_value .= '<span class="badge theme-danger">' . $BL['be_robots_nofollow'] . '</span>';
                        }
                        $robots_value .= '</div>';
                        echo $info_row($BL['be_robots'], $robots_value, 'col-12');
                    }
                    ?>

                </div>
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <a class="btn theme-primary btn-solid btn-styled btn-sm" href="phpwcms.php?do=articles"><?php echo $BL['be_article_cnt_center'] ?></a>
                <a class="btn btn-outline theme-secondary btn-sm" href="<?php echo $article_preview_link ?>" data-bs-toggle="tooltip" title="<?php echo html($BL['be_func_struct_preview']) ?>" onclick="window.open('<?php echo $article_preview_link ?>', 'articlePreviewWindows');return false;"><?php echo $BL['be_func_struct_preview'] ?></a>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">

            <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">

                <h2 class="h6 mb-0"><?php echo $BL['be_ctype'] ?></h2>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select name="ctype" class="form-control form-control-sm w-auto" id="ctype" onchange="this.form.submit();" aria-label="<?php echo html($BL['be_article_cnt_addtitle']) ?>">
<?php

$temp_count = 0;
$user_selected_cp = isset($_SESSION["wcs_user_cp"]) && count($_SESSION["wcs_user_cp"]);

if (is_array($article["article_cntpart"]) && count($article["article_cntpart"])) {

    // list all content parts usable for this article category
    foreach ($article["article_cntpart"] as $value) {

        if ($user_selected_cp && !isset($_SESSION["wcs_user_cp"][$value])) {
            continue;
        }

        if (isset($wcs_content_type[$value])) {

            echo getContentPartOptionTag($value, $wcs_content_type[$value], $article['article_cpdefault']);
            $temp_count++;

        }
        $value1 = $value * (-1);
        if (isset($BL['be_admin_optgroup_label'][$value1]) && $value) {
            echo '<optgroup label="[ ' . $BL['be_admin_optgroup_label'][$value1] . ' ]" class="cntOptGroup"></optgroup>' . "\n";
        }
    }

}
if (!$temp_count) {
    //list all available content parts
    foreach ($wcs_content_type as $key => $value) {

        if ($user_selected_cp && !isset($_SESSION["wcs_user_cp"][$key])) {
            continue;
        }

        echo getContentPartOptionTag($key, $value, $article['article_cpdefault']);
    }
}
?>
                    </select>
                    <input type="submit" name="image" value="<?php echo $BL['be_article_cnt_add'] ?>" class="btn theme-primary btn-solid btn-styled btn-sm" data-bs-toggle="tooltip" title="<?php echo html($BL['be_article_cnt_addtitle']) ?>" />
                </div>

            </div>

            <div class="list-group list-group-flush" data-backend-sortable="1" data-backend-sort-token="<?php echo get_token_get_value() ?>" data-backend-sort-error="<?php echo html($BL['be_sort_drag_error'] ?? 'Reordering failed.') ?>">
<?php

// Sorting: the rows are dragged by their handle and saved through the backend_sort
// action, a content part can only be moved inside its own block
$scc = 0;
$contentpart_block_count = array();

$result = _dbQuery(
    "SELECT COUNT(*) AS contentpart_count FROM " . DB_PREPEND . "phpwcms_articlecontent " .
    "WHERE acontent_aid=" . $article["article_id"]
);
if (isset($result[0]['contentpart_count'])) {
    $scc = intval($result[0]['contentpart_count']);
}

$result = _dbQuery(
    "SELECT acontent_block, COUNT(*) AS contentpart_count FROM " . DB_PREPEND . "phpwcms_articlecontent " .
    "WHERE acontent_aid=" . $article["article_id"] . " AND acontent_trash=0 " .
    "GROUP BY acontent_block"
);
if (is_array($result)) {
    foreach ($result as $block_count) {
        $contentpart_block_count[(string) $block_count['acontent_block']] = intval($block_count['contentpart_count']);
    }
}

//Listing zugehöriger Artikel Content Teile
$sortierwert            = 1;
$contentpart_block      = ' ';
$contentpart_block_name = '';
$contentpart_tab        = '';

$sql =  "SELECT *, UNIX_TIMESTAMP(acontent_tstamp) as acontent_date FROM " . DB_PREPEND . "phpwcms_articlecontent " .
        "WHERE acontent_aid=" . $article["article_id"] . " AND acontent_trash=0 " .
        "ORDER BY acontent_block, acontent_sorting, acontent_tab, acontent_id";
$result = _dbQuery($sql);
if (isset($result[0]['acontent_id'])) {
    foreach ($result as $row) {

        // if type of content part not enabled available
        if (!isset($wcs_content_type[ $row["acontent_type"] ]) || ($row["acontent_type"] == 30 && !isset($phpwcms['modules'][$row["acontent_module"]]))) {
            continue;
        }

        // now show current block name
        if ($contentpart_block != $row['acontent_block']) {
            $contentpart_block = $row['acontent_block'];
            $contentpart_block_name = html(' {' . $row['acontent_block'] . '}');

            switch ($contentpart_block) {
                case '':
                case 'CONTENT':
                    $contentpart_block_name = $BL['be_main_content'] . $contentpart_block_name;
                    if ($article['article_paginate']) {
                        $contentpart_block_name .= ' <span class="badge theme-secondary">' . $BL['be_cnt_pagination'] . '</span>';
                    }
                    break;

                case 'LEFT':
                    $contentpart_block_name = $BL['be_cnt_left'] . $contentpart_block_name;
                    break;

                case 'RIGHT':
                    $contentpart_block_name = $BL['be_cnt_right'] . $contentpart_block_name;
                    break;

                case 'HEADER':
                    $contentpart_block_name = $BL['be_admin_page_header'] . $contentpart_block_name;
                    break;

                case 'FOOTER':
                    $contentpart_block_name = $BL['be_admin_page_footer'] . $contentpart_block_name;
                    break;

                case 'CPSET':
                    $contentpart_block_name = $BL['be_settings'] . ' <span class="fg-secondary">(' . $BL['be_system_container_norender'] . ')</span>';
                    break;

                case 'SYSTEM':
                    $contentpart_block_name = $BL['be_system_container'] . ' <span class="fg-secondary">(' . $BL['be_system_container_norender'] . ')</span>';
                    break;
            }

            echo '<div class="list-group-item bg-2"><div class="small fw-bold text-uppercase">' . $contentpart_block_name . '</div></div>' . LF;
        }

        // now check if content part is tabbed
        if ($row['acontent_tab'] && $contentpart_tab != $row['acontent_tab']) {
            $contentpart_tab        = $row['acontent_tab'];
            $contentpart_tabbed     = explode('_', $contentpart_tab, 2);
            $contentpart_tab_title  = isset($contentpart_tabbed[1]) ? trim($contentpart_tabbed[1]) : '';
            $contentpart_tab_number = explode('|', $contentpart_tabbed[0]);
            $contentpart_tab_type   = empty($contentpart_tab_number[1]) ? 1 : $contentpart_tab_number[1];
            $contentpart_tab_number = intval($contentpart_tab_number[0]);

            $contentpart_tab_name = '';

            if ($contentpart_tab_type == 2) {
                $contentpart_tab_name = $BL['be_ctype_accordion'];
            } elseif (isset($template_default['attributes']['cpgroup_custom'][$contentpart_tab_type])) {
                $contentpart_tab_name = html($template_default['attributes']['cpgroup_custom'][$contentpart_tab_type]['title']);
            } else {
                $contentpart_tab_name = $BL['be_ctype_tabs'];
            }
            $contentpart_tab_name .= ' / ' . $BL['be_cnt_paginate_subsection'] . ': ';
            if ($contentpart_tab_title !== '') {
                $contentpart_tab_name .= html($contentpart_tab_title) . ' ';
            }
            $contentpart_tab_name .= '[' . $contentpart_tab_number . ']';

            echo '<div class="list-group-item bg-2"><div class="small fg-secondary">' . $contentpart_tab_name . '</div></div>' . LF;

        } elseif ($contentpart_tab && empty($row['acontent_tab'])) {

            // not the same tab but following cp is not tabbed
            $contentpart_tab = '';
        }

        $cntpart_title = $wcs_content_type[$row["acontent_type"]];
        if (!empty($row["acontent_module"])) {
            $cntpart_title .= ': ' . $BL['modules'][$row["acontent_module"]]['listing_title'];
        }

        $contentpart_meta = '';
        if ($contentpart_block != 'CPSET') {

            //Display cp paginate page number
            if ($article["article_paginate"]) {
                $contentpart_meta .= '<span class="badge theme-secondary" data-bs-toggle="tooltip" title="' . html($BL['be_cnt_paginate_subsection']) . '">' . ($row["acontent_paginate_page"] == 0 ? 1 : $row["acontent_paginate_page"]) . '</span>';
            }

            //Anzeigen der Space Before/After Info
            if (intval($row["acontent_before"])) {
                $contentpart_meta .= '<span class="badge theme-secondary" data-bs-toggle="tooltip" title="' . html($BL['be_article_cnt_before']) . '">' . $row["acontent_before"] . '</span>';
            }
            if (intval($row["acontent_after"])) {
                $contentpart_meta .= '<span class="badge theme-secondary" data-bs-toggle="tooltip" title="' . html($BL['be_article_cnt_after']) . '">' . $row["acontent_after"] . '</span>';
            }
            if ($row["acontent_top"]) {
                $contentpart_meta .= '<span class="badge theme-secondary">' . $BL['be_article_cnt_toplink'] . '</span>';
            }
            if ($row["acontent_anchor"]) {
                $contentpart_meta .= '<span class="badge theme-secondary" data-bs-toggle="tooltip" title="' . html($BL['be_article_cnt_anchor']) . '">#</span>';
            }

        }

        // only a content part that shares its block with another one is sortable
        $contentpart_sortable = ($contentpart_block_count[(string) $row['acontent_block']] ?? 0) > 1;

        ?>
                <div class="list-group-item"<?php if ($contentpart_sortable): ?> data-sort-group="content" data-sort-id="<?php echo intval($row['acontent_id']) ?>" data-sort-parent="<?php echo intval($article['article_id']) ?>" data-sort-block="<?php echo html($row['acontent_block']) ?>"<?php endif; ?>>

                    <div class="d-flex align-items-start gap-3">

                        <div class="d-flex align-items-center gap-2 flex-shrink-0 fg-secondary">
                            <?php
                            echo backend_icon('postcard-fill');
                            if ($row["acontent_granted"]) {
                                echo $row["acontent_granted"] == 2
                                    ? '<span data-bs-toggle="tooltip" title="' . html($BL['be_hidden_for_feuser']) . '">' . backend_icon('lock') . '</span>'
                                    : '<span data-bs-toggle="tooltip" title="' . html($BL['be_granted_feuser']) . '">' . backend_icon('person-fill') . '</span>';
                            }
                            ?>
                        </div>

                        <div class="flex-grow-1 min-w-0">

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-bold small text-uppercase"><?php echo $cntpart_title ?></span>
                                <span class="badge theme-secondary">ID:<?php echo $row["acontent_id"] ?></span>
                                <span class="small fg-secondary"><?php echo date($backend_date_format['php'], $row["acontent_date"]) ?></span>
                                <?php echo $contentpart_meta ?>
                                <span class="badge <?php echo $row["acontent_visible"] ? 'theme-success' : 'theme-secondary' ?>"><?php echo $row["acontent_visible"] ? $BL['be_admin_struct_visible'] : $BL['be_inactive'] ?></span>
                            </div>

                            <?php if ($row["acontent_block"] === 'SYSTEM'): ?>
                            <div class="small fg-secondary">
                                <?php
                                echo $BL['be_article_rendering'] . ': ';
                                if (empty($row["acontent_tid"])) {
                                    echo $BL['be_custom_scriptlogic'];
                                } elseif ($row["acontent_tid"] == 3) {
                                    echo $BL['be_article_forlist'] . ' + ' . $BL['be_article_forfull'];
                                } elseif ($row["acontent_tid"] == 2) {
                                    echo $BL['be_article_forfull'];
                                } else { // == 1
                                    echo $BL['be_article_forlist'];
                                }
                                ?>
                            </div>
                            <?php endif; ?>

                            <?php
                            $acontent_livedate = is_null($row['acontent_livedate']) ? false : phpwcms_strtotime($row['acontent_livedate'], $backend_date_format['php'], '');
                            $acontent_killdate = is_null($row['acontent_killdate']) ? false : phpwcms_strtotime($row['acontent_killdate'], $backend_date_format['php'], '');

                            if ($acontent_livedate || $acontent_killdate):
                            ?>
                            <div class="small">
                                <span class="fg-secondary"><?php echo $BL['be_article_cnt_start'] ?>:</span> <?php echo $acontent_livedate ?: $BL['be_not_set']; ?>
                                &nbsp;&nbsp;
                                <span class="fg-secondary"><?php echo $BL['be_article_cnt_end'] ?>:</span> <?php echo $acontent_killdate ?: $BL['be_not_set']; ?>
                            </div>
                            <?php endif; ?>

<?php

        // list content type overview
        $cinfo = NULL;

        // check default content parts (system internals
        if ($row['acontent_type'] != 30 && file_exists('include/inc_tmpl/content/cnt' . $row['acontent_type'] . '.list.inc.php')) {

            include PHPWCMS_ROOT . '/include/inc_tmpl/content/cnt' . $row['acontent_type'] . '.list.inc.php';

        } elseif ($row['acontent_type'] == 30 && file_exists($phpwcms['modules'][$row['acontent_module']]['path'] . 'inc/cnt.list.php')) {

            // custom module
            include $phpwcms['modules'][$row['acontent_module']]['path'] . 'inc/cnt.list.php';

        } else {

            // default fallback
            include PHPWCMS_ROOT . '/include/inc_tmpl/content/cnt0.list.inc.php';

        }
        // end list

        ?>

                        </div>

                        <div class="d-flex flex-wrap align-items-start justify-content-end gap-2 flex-shrink-0">

                            <?php
                            echo backend_action(
                                $article_cp_link . '&amp;acid=' . $row["acontent_id"],
                                'pen',
                                $BL['be_article_cnt_edit']
                            );

                            echo backend_action(
                                'include/inc_act/act_structure.php?do=8%7C' . $row["acontent_id"] . '%7C' . $article["article_id"] . '%7C' . ($row["acontent_sorting"] + 5),
                                'copy',
                                $BL['be_func_content_copy'] . ' [ID:' . $row["acontent_id"] . ']',
                                'normal',
                                ' onclick="return confirm(\'' . js_singlequote($BL['be_func_content_copy'] . ': ' . $cntpart_title . ' [ID:' . $row["acontent_id"] . ']') . '\');"'
                            );

                            echo backend_action(
                                'include/inc_act/act_articlecontent.php?do=2,' . $article["article_id"] . ',' . $row["acontent_id"] . ',' . switch_on_off($row["acontent_visible"]),
                                $row["acontent_visible"] ? 'eye' : 'eye-slash',
                                $BL['be_article_cnt_lvisible'],
                                'normal',
                                '',
                                $row["acontent_visible"] ? 'success' : 'danger'
                            );

                            echo backend_action(
                                'include/inc_act/act_articlecontent.php?do=9,' . $article["article_id"] . ',' . $row["acontent_id"],
                                'trash',
                                $BL['be_article_cnt_delpart'],
                                'normal',
                                ' onclick="return confirm(\'' . js_singlequote($BL['be_article_cnt_delpartjs'] . ' [ID: ' . $row["acontent_id"] . ']') . '\');"',
                                'danger'
                            );
                            ?>

                        </div>

                        <?php if ($contentpart_sortable): ?>
                        <?php echo backend_sort_handle() ?>
                        <?php endif; ?>

                    </div>

                </div>
<?php
    }
} //Ende Listing Artikel Content Teile
?>
            </div>

        </div>
    </div>

</div>
<input name="csorting" type="hidden" id="csorting" value="<?php echo ($scc * 10); ?>" />
</form>
