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

//26-04-2004 Oliver Georgi -> $level seems to be unused
//19-11-2004 Fernando Batista -> Copy article, Copy structures http://fernandobatista.net
//31-03-2005 Fernando Batista -> Copy/Cut Article Content http://fernandobatista.net

function struct_list($id, $copy_article_content, $cut_article_content, $copy_id, $copy_article, $cut_id, $cut_article, $listmode=1, $forbid_cut=0, $forbid_copy=0, $counter=0) {

    $counter++;
    $sql  = "SELECT t1.*, t2.template_default, t2.template_name, t2.template_trash FROM ".DB_PREPEND."phpwcms_articlecat t1 ";
    $sql .= "LEFT JOIN ".DB_PREPEND."phpwcms_template t2 ON t1.acat_template=t2.template_id ";
    $sql .= "WHERE acat_trash=0 AND acat_struct=".intval($id)." ORDER BY acat_sort";

    $struct = _dbQuery($sql);

    if(isset($struct[0]['acat_id'])) {
        $count_row = count($struct);

        foreach($struct as $key => $value) {
            struct_levellist($struct, $key, $counter, $copy_article_content, $cut_article_content, $copy_id, $copy_article, $cut_id, $forbid_cut, $forbid_copy, $listmode, $cut_article, $count_row);
        }
    }
}

/*
 * Indentation class for nested rows, mapped to Bootstrap spacing
 */
function backend_indent($depth) {

    $steps = array('', 'ms-5', 'ms-9', 'ms-12');

    return $steps[intval($depth)] ?? 'ms-12';

}

function struct_levellist($struct, $key, $counter, $copy_article_content, $cut_article_content, $copy_id, $copy_article, $cut_id, $forbid_cut, $forbid_copy, $listmode, $cut_article, $count_row) {

    global $BL;

    $page_val       = $listmode ? "do=admin&amp;p=6" : "do=articles";
    $child_count    = get_root_childcount($struct[$key]["acat_id"]);
    $child_sort     = (($child_count+1)*10);

    $forbid_cut     = $struct[$key]["acat_struct"] == $cut_id || $forbid_cut ? 1 : 0;
    $forbid_copy    = $struct[$key]["acat_struct"] == $copy_id || $forbid_copy ? 1 : 0;

    $acat_id        = intval($struct[$key]["acat_id"]);
    $acat_parent    = intval($struct[$key]["acat_struct"]);
    $an             = html($struct[$key]["acat_name"]);
    $is_open        = !empty($_SESSION["structure"][$acat_id]);
    $sortable       = !empty($_SESSION['wcs_user_admin']) && $count_row > 1;
    $indent         = backend_indent($counter);

    $a  = '<div class="list-group-item d-flex align-items-center gap-2' . ($indent ? ' ' . $indent : '') . '"';
    if($sortable) {
        $a .= ' data-sort-group="struct" data-sort-id="' . $acat_id . '" data-sort-parent="' . $acat_parent . '"';
    }
    $a .= '>' . LF;

    if($sortable) {
        $a .= backend_sort_handle();
    }

    $a .= '<span class="fg-secondary flex-shrink-0" title="' . $BL['be_admin_struct_title'] . '">' . backend_icon('folder') . '</span>';

    if($child_count) {
        $a .= '<a href="phpwcms.php?' . $page_val . '&amp;open=' . rawurlencode($acat_id . ':' . ($is_open ? 0 : 1)) . '"';
        $a .= ' class="link-secondary text-decoration-none flex-shrink-0" aria-label="' . $an . '" title="' . $an . '">';
        $a .= backend_icon($is_open ? 'chevron-down' : 'chevron-right') . '</a>';
    } else {
        $a .= '<span class="flex-shrink-0" style="width:16px"></span>';
    }

    $a .= '<div class="flex-grow-1 text-truncate"><a href="';
    $a .= rel_url(array('phpwcms-preview'=>1), array(), empty($struct[$key]["acat_alias"]) ? 'id=' . $acat_id : $struct[$key]["acat_alias"]);
    $a .= '" target="_blank" title="' . $BL['be_func_struct_preview'] . ': ' . $an . '">' . $an . '</a>';
    if(empty($struct[$key]["acat_aktiv"])) {
        $a .= ' <span class="badge theme-secondary">' . $BL['be_inactive'] . '</span>';
    }
    $a .= '</div>' . LF;

    $a .= '<div class="d-flex align-items-center gap-2 flex-shrink-0">';

    if($listmode) {

        $a .= backend_action('phpwcms.php?do=admin&amp;p=6&amp;struct=' . $acat_id . '&amp;sort=' . $child_sort, 'plus', $BL['be_func_struct_insert_level'] . ' [' . $an . ']');

        if($cut_id) {
            if($cut_id != $acat_id && !$forbid_cut) {
                $a .= backend_action('include/inc_act/act_structure.php?do=1%7C' . $cut_id . '%7C' . $acat_id . '%7C' . $child_sort, 'cut', $BL['be_func_struct_paste_level'] . ' [' . $an . ']', 'active');
            } else {
                $a .= backend_action('phpwcms.php?do=admin&amp;p=6', 'cut', $BL['be_func_struct_paste_cancel'] . ' [' . $an . ']', 'disabled');
            }
        } elseif($acat_id) {
            $a .= backend_action('phpwcms.php?do=admin&amp;p=6&amp;cut=' . $acat_id, 'cut', $BL['be_func_struct_cut_level'] . ' [' . $an . ']');
        } else {
            $a .= backend_action('phpwcms.php?do=admin&amp;p=6', 'cut', $BL['be_func_struct_no_cut'], 'disabled');
        }

        if($copy_id) {
            if($copy_id != $acat_id && !$forbid_copy) {
                $a .= backend_action('include/inc_act/act_structure.php?do=6%7C' . $copy_id . '%7C' . $acat_id . '%7C' . $child_sort, 'copy', $BL['be_func_struct_paste_level'] . ' [' . $an . ']', 'active');
            } else {
                $a .= backend_action('phpwcms.php?do=admin&amp;p=6', 'copy', $BL['be_func_struct_paste_cancel'] . ' [' . $an . ']', 'disabled');
            }
        } elseif($acat_id) {
            $a .= backend_action('phpwcms.php?do=admin&amp;p=6&amp;cop=' . $acat_id, 'copy', $BL['be_func_struct_copy_level'] . ' [' . $an . ']');
        } else {
            $a .= backend_action('phpwcms.php?do=admin&amp;p=6', 'copy', $BL['be_func_struct_no_copy'], 'disabled');
        }

        $a .= backend_action('phpwcms.php?do=admin&amp;p=6&amp;struct=' . ($acat_id ? $acat_parent . '&amp;cat=' . $acat_id : 'index'), 'pencil', $BL['be_func_struct_sedit'] . ' [' . $an . ']');

        if($acat_id) {
            $onclick = ' onclick="return confirm(\'' . $BL['be_func_struct_del_sjsmsg'] . ' \\n\\n[' . js_singlequote($an) . '] \');"';
            $a .= backend_action('include/inc_act/act_structure.php?do=9%7C' . $acat_id, 'trash', $BL['be_func_struct_del_struct'] . ' [' . $an . ']', 'normal', $onclick);
        }

    } else {

        $a .= backend_action('phpwcms.php?do=articles&amp;p=1&amp;struct=' . $acat_id, 'plus', $BL['be_func_struct_new_article'] . ' [' . $an . ']');

        if($cut_article) {
            $a .= backend_action('include/inc_act/act_structure.php?do=3%7C' . intval($cut_article) . '%7C' . $acat_id, 'cut', $BL['be_func_struct_paste_article'] . ' [' . $an . ']', 'active');
        } else {
            $a .= backend_action('phpwcms.php?do=articles', 'cut', $BL['be_func_struct_no_cut'], 'disabled');
        }

        if($copy_article) {
            $a .= backend_action('include/inc_act/act_structure.php?do=5%7C' . intval($copy_article) . '%7C' . $acat_id, 'copy', $BL['be_func_struct_paste_article'] . ' [' . $an . ']', 'active');
        } else {
            $a .= backend_action('phpwcms.php?do=articles', 'copy', $BL['be_func_struct_no_copy'], 'disabled');
        }

    }

    $a .= '</div>' . LF;
    $a .= '</div>' . LF;
    echo $a;

    if($is_open) {

        if(!$listmode) {
            struct_articlelist($acat_id, $counter, $copy_article_content, $cut_article_content, $copy_article, $cut_article, $struct[$key]["acat_order"]);
        }

        struct_list($acat_id, $copy_article_content, $cut_article_content, $copy_id, $copy_article, $cut_id, $cut_article, $listmode, $forbid_cut, $forbid_copy, $counter);

    }

}

function get_root_childcount($id) {

    // get amount of active child levels
    $id = intval($id);

    $p1_count = _dbQuery("SELECT COUNT(*) FROM ".DB_PREPEND."phpwcms_articlecat WHERE acat_trash=0 AND acat_struct=".$id, 'COUNT');
    $p2_count = _dbQuery("SELECT COUNT(*) FROM ".DB_PREPEND."phpwcms_article WHERE article_deleted=0 AND article_cid=".$id, 'COUNT');

    return $p1_count + $p2_count;

}

function get_article_content_count($id) {

    return _dbQuery("SELECT COUNT(*) FROM ".DB_PREPEND."phpwcms_articlecontent WHERE acontent_trash=0 AND acontent_aid=".intval($id), 'COUNT');

}

function struct_articlelist($struct_id, $counter, $copy_article_content, $cut_article_content, $copy_article, $cut_article, $article_order=0) {

    global $BL;

    $article            = [];
    $sort_array         = [];
    $article_order      = intval($article_order);
    $max_article_count  = 0;
    $show_sort          = !$article_order || $article_order == 1 ? 1 : 0;
    $ao                 = get_order_sort($article_order);
    $count_article      = 0;

    $sql  = "SELECT *, ";
    $sql .= "DATE_FORMAT(article_tstamp, '%Y-%m-%d %H:%i:%s') AS article_date ";
    $sql .= "FROM ".DB_PREPEND."phpwcms_article ";
    $sql .= "WHERE article_cid='".intval($struct_id)."' AND article_deleted=0 ORDER BY ".$ao[2];

    $result = _dbQuery($sql);

    if(isset($result[0]['article_id'])) {

        $max_article_count = count($result);

        foreach($result as $row) {

            $article[$count_article] = $row;
            if($row['article_sort'] > 0) {
                $sort_array[$count_article] = $row['article_sort'];
            }

            $count_article++;

        }

    }

    // now check if all sort values are unique, if not do a re-sort for all articles
    if($max_article_count > count(array_unique($sort_array)) ) {
        $article = getArticleReSorted($struct_id, $article_order);
    }

    foreach($article as $akey => $avalue) {

        $article_id     = intval($article[$akey]["article_id"]);
        $at             = html($article[$akey]["article_title"]);
        $may_edit       = $article[$akey]["article_uid"] == $_SESSION["wcs_user_id"] || $_SESSION["wcs_user_admin"];
        $acontent_count = get_article_content_count($article_id);
        $is_open        = !empty($_SESSION["structure"]["article"][$article_id]);
        $sortable       = $show_sort && $max_article_count > 1 && $may_edit;
        $indent         = backend_indent($counter + 1);
        $marked         = $cut_article == $article_id || $copy_article == $article_id;

        $a  = '<div class="list-group-item d-flex align-items-center gap-2' . ($indent ? ' ' . $indent : '') . ($marked ? ' bg-2' : '') . '"';
        if($sortable) {
            $a .= ' data-sort-group="article" data-sort-id="' . $article_id . '" data-sort-parent="' . intval($struct_id) . '"';
        }
        $a .= '>' . LF;

        if($sortable) {
            $a .= backend_sort_handle();
        }

        $a .= '<span class="fg-secondary flex-shrink-0" title="' . $BL['be_article'] . '">' . backend_icon('page') . '</span>';

        if($acontent_count && $may_edit) {
            $a .= '<a href="phpwcms.php?do=articles&amp;opena=' . rawurlencode($article_id . ':' . ($is_open ? 0 : 1)) . '"';
            $a .= ' class="link-secondary text-decoration-none flex-shrink-0" aria-label="' . $at . '" title="' . $at . '">';
            $a .= backend_icon($is_open ? 'chevron-down' : 'chevron-right') . '</a>';
        } else {
            $a .= '<span class="flex-shrink-0" style="width:16px"></span>';
        }

        $a .= '<div class="flex-grow-1 text-truncate"><a href="';
        $a .= rel_url(array('phpwcms-preview'=>1), array(), empty($article[$akey]["article_alias"]) ? 'aid=' . $article_id : $article[$akey]["article_alias"]);
        $a .= '" target="_blank" title="' . $BL['be_func_struct_preview'] . ': ' . $at . '">' . $at . '</a>';
        if(empty($article[$akey]["article_aktiv"])) {
            $a .= ' <span class="badge theme-secondary">' . $BL['be_inactive'] . '</span>';
        }
        $a .= '</div>' . LF;

        $a .= '<div class="d-flex align-items-center gap-2 flex-shrink-0">';

        if($may_edit) {
            $a .= backend_action('phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;id=' . $article_id, 'pencil', $BL['be_func_struct_edit'] . ' [' . $at . ']');
        } else {
            $a .= backend_action('', 'pencil', $BL['be_func_struct_edit'] . ' [' . $at . ']', 'disabled');
        }

        if($cut_article != $article_id && !$cut_article_content) {
            $a .= backend_action('phpwcms.php?do=articles&amp;acut=' . $article_id, 'cut', $BL['be_func_struct_cut'] . ' [' . $at . ']');
        } elseif($cut_article_content) {
            $a .= backend_action('include/inc_act/act_structure.php?do=7%7C' . intval($cut_article_content) . '%7C' . $article_id . '%7C-10', 'cut', $BL['be_func_content_paste0'] . ' [' . $at . ']', 'active');
        } else {
            $a .= backend_action('phpwcms.php?do=articles', 'cut', $BL['be_func_struct_nocut'], 'disabled');
        }

        if($copy_article != $article_id && !$copy_article_content) {
            $a .= backend_action('phpwcms.php?do=articles&amp;acopy=' . $article_id, 'copy', $BL['be_func_struct_copy'] . ' [' . $at . ']');
        } elseif($copy_article_content) {
            $a .= backend_action('include/inc_act/act_structure.php?do=8%7C' . intval($copy_article_content) . '%7C' . $article_id . '%7C-10', 'copy', $BL['be_func_content_paste0'] . ' [' . $at . ']', 'active');
        } else {
            $a .= backend_action('phpwcms.php?do=articles', 'copy', $BL['be_func_struct_nocopy'], 'disabled');
        }

        $a .= backend_action('include/inc_act/act_articlecontent.php?do=3,' . $article_id . ',,' . ($article[$akey]["article_aktiv"] ? 0 : 1), $article[$akey]["article_aktiv"] ? 'eye' : 'eye-off', $BL['be_func_struct_svisible'] . ' [' . $at . ']');

        if($may_edit) {
            $onclick = ' onclick="return confirm(\'' . $BL['be_func_struct_del_jsmsg'] . ' \\n[' . js_singlequote($at) . '] \');"';
            $a .= backend_action('include/inc_act/act_articlecontent.php?do=1,' . $article_id, 'trash', $BL['be_func_struct_del_article'] . ' [' . $at . ']', 'normal', $onclick);
        } else {
            $a .= backend_action('', 'trash', $BL['be_func_struct_del_article'] . ' [' . $at . ']', 'disabled');
        }

        $a .= '</div>' . LF;
        $a .= '</div>' . LF;
        echo $a;

        if($may_edit && $is_open) {
            struct_articlecontentlist($article, $akey, $copy_article_content, $cut_article_content, $counter);
        }

    }
}

function struct_articlecontentlist($article, $akey, $copy_article_content, $cut_article_content, $counter){

    global $BL;

    $article_id = intval($article[$akey]["article_id"]);
    $may_edit   = $article[$akey]["article_uid"] == $_SESSION["wcs_user_id"] || $_SESSION["wcs_user_admin"];

    $sql  = "SELECT * FROM ".DB_PREPEND."phpwcms_articlecontent ";
    $sql .= "WHERE acontent_aid=".$article_id." AND acontent_trash=0 ";
    $sql .= "ORDER BY acontent_block, acontent_sorting, acontent_id";

    $result = _dbQuery($sql);

    if(empty($result[0]['acontent_id'])) {
        return;
    }

    // content parts are sorted inside their block only
    $block_count = array();
    foreach($result as $row) {
        if(!isset($GLOBALS['wcs_content_type'][ $row["acontent_type"] ])) {
            continue;
        }
        $block = (string) $row['acontent_block'];
        $block_count[$block] = ($block_count[$block] ?? 0) + 1;
    }

    $rows   = '';
    $indent = backend_indent($counter + 2);

    foreach($result as $article_content) {

        // if type of content part not enabled available
        if(!isset($GLOBALS['wcs_content_type'][ $article_content["acontent_type"] ]) || ($article_content['acontent_type'] == 30 && !isset($GLOBALS['phpwcms']['modules'][$article_content["acontent_module"]]))) {
            continue;
        }

        $acontent_id    = intval($article_content["acontent_id"]);
        $acontent_aid   = intval($article_content["acontent_aid"]);
        $block          = (string) $article_content['acontent_block'];
        $label          = '[ID:' . $acontent_id . '] ' . $GLOBALS["wcs_content_type"][$article_content["acontent_type"]];
        if($article_content["acontent_type"] == 30) {
            $label .= ': ' . $BL['modules'][$article_content["acontent_module"]]['listing_title'];
        }

        $sortable = $may_edit && ($block_count[$block] ?? 0) > 1;
        $marked   = $cut_article_content == $acontent_id || $copy_article_content == $acontent_id;

        $rows .= '<div class="list-group-item d-flex align-items-center gap-2' . ($indent ? ' ' . $indent : '') . ($marked ? ' bg-2' : '') . '"';
        if($sortable) {
            $rows .= ' data-sort-group="content" data-sort-id="' . $acontent_id . '" data-sort-parent="' . $acontent_aid . '"';
            $rows .= ' data-sort-block="' . html($block) . '"';
        }
        $rows .= '>' . LF;

        if($sortable) {
            $rows .= backend_sort_handle();
        }

        $rows .= '<span class="fg-secondary flex-shrink-0">' . backend_icon('block') . '</span>';

        $rows .= '<div class="flex-grow-1 text-truncate"><span class="fg-secondary">' . html($label) . '</span>';
        $rows .= ' <span class="badge theme-secondary">' . html($block) . '</span>';
        if($article_content["acontent_title"]) {
            $rows .= ' ' . html($article_content["acontent_title"]);
        }
        $rows .= '</div>' . LF;

        $rows .= '<div class="d-flex align-items-center gap-2 flex-shrink-0">';

        if($may_edit) {
            $rows .= backend_action('phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id=' . $article_id . '&amp;acid=' . $acontent_id, 'pencil', $BL['be_func_content_edit']);
        } else {
            $rows .= backend_action('', 'pencil', $BL['be_func_content_edit'], 'disabled');
        }

        if($cut_article_content) {
            if($cut_article_content != $acontent_id) {
                $rows .= backend_action('include/inc_act/act_structure.php?do=7%7C' . intval($cut_article_content) . '%7C' . $acontent_aid . '%7C' . intval($article_content["acontent_sorting"]), 'cut', $BL['be_func_content_paste'], 'active');
            } else {
                $rows .= backend_action('phpwcms.php?do=articles', 'cut', $BL['be_func_content_paste_cancel'], 'active');
            }
        } elseif($acontent_id) {
            $rows .= backend_action('phpwcms.php?do=articles&amp;accut=' . $acontent_id, 'cut', $BL['be_func_content_cut']);
        } else {
            $rows .= backend_action('phpwcms.php?do=articles', 'cut', $BL['be_func_content_no_cut'], 'disabled');
        }

        if($copy_article_content) {
            if($copy_article_content != $acontent_id) {
                $rows .= backend_action('include/inc_act/act_structure.php?do=8%7C' . intval($copy_article_content) . '%7C' . $acontent_aid . '%7C' . intval($article_content["acontent_sorting"]), 'copy', $BL['be_func_content_paste'], 'active');
            } else {
                $rows .= backend_action('phpwcms.php?do=articles', 'copy', $BL['be_func_content_paste_cancel'], 'active');
            }
        } elseif($acontent_id) {
            $rows .= backend_action('phpwcms.php?do=articles&amp;accopy=' . $acontent_id, 'copy', $BL['be_func_content_copy']);
        } else {
            $rows .= backend_action('phpwcms.php?do=articles', 'copy', $BL['be_func_content_no_copy'], 'disabled');
        }

        $rows .= backend_action('include/inc_act/act_articlecontent.php?do=2,' . $acontent_aid . ',' . $acontent_id . ',' . ($article_content["acontent_visible"] ? 0 : 1), $article_content["acontent_visible"] ? 'eye' : 'eye-off', $BL['be_func_struct_svisible']);

        if($may_edit) {
            $onclick = ' onclick="return confirm(\'' . $BL['be_article_cnt_delpart'] . ' \\n[' . js_singlequote($label) . '] \');"';
            $rows .= backend_action('include/inc_act/act_articlecontent.php?do=9,' . $acontent_aid . ',' . $acontent_id, 'trash', $BL['be_article_cnt_delpart'], 'normal', $onclick);
        } else {
            $rows .= backend_action('', 'trash', $BL['be_article_cnt_delpart'], 'disabled');
        }

        $rows .= '</div>' . LF . '</div>' . LF;
    }

    if($rows) {
        echo '<div class="list-group-item p-0">' . LF;
        echo '<div class="list-group list-group-flush">' . LF;
        echo $rows;
        echo '</div></div>' . LF;
    }
}

function update_404redirect() {

    $data = array(
        'error' => array(),
        'data'  => array(
            'rid'       => intval($_POST['rid']),
            'alias'     => clean_slweg($_POST['alias']),
            'id'        => trim($_POST['id']) === '' ? '' : intval($_POST['id']),
            'aid'       => trim($_POST['aid']) === '' || !intval($_POST['aid']) ? '' : intval($_POST['aid']),
            'type'      => empty($_POST['type']) || !in_array($_POST['type'], array('alias', 'id', 'aid', 'link')) ? '' : clean_slweg($_POST['type']),
            'active'    => empty($_POST['active']) ? 0 : 1,
            'shortcut'  => empty($_POST['shortcut']) ? 0 : 1,
            'code'      => empty($_POST['code']) || !in_array($_POST['code'], array('301', '307', '404', '401', '503')) ? '' : clean_slweg($_POST['code']),
            'target'    => clean_slweg($_POST['target']),
            'changed'   => date('Y-m-d H:i:s')
        )
    );

    if(!$data['data']['aid'] && !$data['data']['alias'] && $data['data']['id'] == '' && !isset($_POST['delete_'.md5($data['data']['rid'])])) {
        $data['error'][] = $GLOBALS['BL']['be_redirect_error1'];
    }
    if($data['data']['type'] && $data['data']['target'] === '') {
        $data['error'][] = $GLOBALS['BL']['be_redirect_error2'];
    } elseif(($data['data']['type'] == 'id' || $data['data']['type'] == 'aid') && !is_intval($data['data']['target'])) {
        $data['error'][] = $GLOBALS['BL']['be_redirect_error3'];
    }

    if(count($data['error'])) {
        $data['data']['active'] = 0;
        set_status_message(implode('<br />', $data['error']), 'error');
    } else {
        $data['error'] = NULL;
        $rid = $data['data']['rid'];
        unset($data['data']['rid']);
        if($rid) {
            // Mark for deletion
            if(isset($_POST['delete_'.md5($rid)])) {
                $data['data']['active'] = 9;
                $result = _dbQuery('DELETE FROM '.DB_PREPEND.'phpwcms_redirect WHERE rid='.$rid, 'DELETE');
            } else {
                $result = _dbUpdate('phpwcms_redirect', $data['data'], 'rid='.$rid);
            }
        } else {
            $result = _dbInsert('phpwcms_redirect', $data['data']);
            if(isset($result['INSERT_ID'])) {
                $rid = $result['INSERT_ID'];
            }
        }
        $data['data']['rid'] = $rid;

        if($result) {
            if($data['data']['active'] == 9) {
                set_status_message(str_replace('{ID}', $data['data']['rid'], $GLOBALS['BL']['be_action_deleted']), 'success');
                headerRedirect('phpwcms.php?'.get_token_get_string().'&do=admin&p=14');
            } else {
                set_status_message($GLOBALS['BL']['be_successfully_saved'], 'success');
            }
        } else {
            set_status_message($GLOBALS['BL']['be_error_while_save'], 'error');
        }
    }

    return $data;
}
