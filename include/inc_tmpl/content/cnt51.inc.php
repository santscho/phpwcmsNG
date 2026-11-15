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

//map

if(!isset($content["map"]) || !is_array($content["map"])) {
    $content["map"] = array();
}

$content["map"] += array(
    'template'  => '',
    'image'     => '',
    'text'      => ''
);

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="cmap_template"><?php echo $BL['be_admin_struct_template'] ?></label>
        <select name="cmap_template" id="cmap_template" class="form-control">
<?php
// templates for article listing
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/map');
if(is_array($tmpllist) && count($tmpllist)) {
    foreach($tmpllist as $val) {
        $vals = '';
        if($val == $content["map"]['template']) $vals= ' selected="selected"';
        $val = htmlspecialchars($val);
        echo '<option value="'.$val.'"'.$vals.'>'.$val."</option>\n";
    }
}

?>
        </select>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_ctype_map'] ?></div>
<?php

// select the map image
$g = '';
$map_selected = 0;
$map_name = '';
$imglist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/map/map_img', 'jpg,gif,png,jpeg');
if(is_array($imglist) && count($imglist)) {
    $g = '<div class="d-flex flex-wrap gap-4">';
    foreach($imglist as $val) {
        $vals = '';
        if($val == $content["map"]['image']) {
            $vals= ' checked="checked"';
            $map_selected = 1;
            $map_name = $content["map"]['image'];
            $map_info = getimagesize(PHPWCMS_TEMPLATE.'inc_cntpart/map/map_img/'.$map_name);
        }
        $map_img_id = 'cmap_image_' . substr(md5($val), 0, 8);
        $gp = trim($phpwcms["templates"], '/').'/inc_cntpart/map/map_img/'.$val;
        $g .= '<div class="form-field">';
        $g .= '<input class="radio" type="radio" name="cmap_image" id="' . $map_img_id . '" value="' . html($val) . '"' . $vals . '>';
        $g .= '<label for="' . $map_img_id . '" class="d-flex flex-column gap-1">';
        $g .= '<img src="' . html($gp) . '" class="img-fluid rounded border" style="max-height:48px" alt="">';
        $g .= '<span class="small">' . html($val) . '</span>';
        $g .= '</label>';
        $g .= '</div>';
    }
    $g .= '</div>';
}
if($g) {
    echo $g;
} else {
    echo '<span class="fg-danger small">no map available. upload one first.</span>';
}

?>
    </div>
<?php
// if a map is selected show possible add/edit new point
if($map_selected) {

    if(isset($_GET['dellocid']) && intval($_GET['dellocid'])) {

        _dbQuery("UPDATE ".DB_PREPEND."phpwcms_map SET map_deleted=9 WHERE map_cid=".intval($content["id"])." AND map_id=".intval($_GET['dellocid']), 'UPDATE');

    }

    $map_current = (isset($_GET['locid'])) ? intval($_GET['locid']) : 0;
    $map_sql  = "SELECT * FROM ".DB_PREPEND."phpwcms_map WHERE map_deleted=0 ";
    $map_sql .= "AND map_cid=".intval($content["id"])." ORDER BY map_zip ASC, map_city ASC";
    $map_result = _dbQuery($map_sql);

    $map_list = '';
    $map_xy = array();
    $ck = 0;
    $map_link_base = 'phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id='.$content["aid"].'&amp;acid='.$content["id"];
    $map_delete_label = trim(preg_replace('/\s+/', ' ', str_replace('\\n', ' ', $BL['be_btn_delete'])));

    if(isset($map_result[0]['map_id'])) {
        foreach($map_result as $map_row) {
            if($map_row['map_id'] == $map_current) {

                $content["location"]['id']      = $map_row['map_id'];
                $content["location"]['x']       = $map_row['map_x'];
                $content["location"]['y']       = $map_row['map_y'];
                $content["location"]['title']   = $map_row['map_title'];
                $content["location"]['zip']     = $map_row['map_zip'];
                $content["location"]['city']    = $map_row['map_city'];
                $content["location"]['entry']   = $map_row['map_entry'];

            }
            $map_list .= '<li class="list-group-item d-flex align-items-center gap-3">'.LF;
            $map_list .= '<span class="small fg-secondary">'.$map_row['map_x'].'x'.$map_row['map_y'].'</span>'.LF;
            $map_list .= '<span class="flex-grow-1">'.$map_row['map_title'].'</span>'.LF;
            $map_list .= '<span class="d-flex gap-2">';
            $map_list .= backend_action($map_link_base.'&amp;locid='.$map_row['map_id'], 'pen', $BL['be_cnt_map_edit']);
            $map_confirm_text = str_replace('\\n', "\n", $map_delete_label.' '.$map_row['map_title']);
            $map_list .= backend_action($map_link_base.'&amp;dellocid='.$map_row['map_id'], 'trash', $map_delete_label, 'normal', ' onclick="return confirm('.html(json_encode($map_confirm_text, JSON_UNESCAPED_UNICODE)).');"', 'danger');
            $map_list .= '</span>'.LF;
            $map_list .= '</li>'.LF;
            $map_xy[] = $map_row['map_x'].':::'.$map_row['map_y'].':::'.$map_row['map_title'];

            $ck++;
        }
    }

?>

    <div class="col-12">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cmap_location_x">X</label>
                <input name="cmap_location_x" type="text" class="form-control" id="cmap_location_x" size="4" maxlength="4" value="<?php echo  empty($content['location']["x"]) ? '' : intval($content['location']["x"]) ?>" onchange="doMapChange();">
            </div>
            <div>
                <label class="form-label" for="cmap_location_y">Y</label>
                <input name="cmap_location_y" type="text" class="form-control" id="cmap_location_y" size="4" maxlength="4" value="<?php echo  empty($content['location']["y"]) ? '' : intval($content['location']["y"]) ?>" onchange="doMapChange();">
            </div>
            <div>
                <button type="button" class="btn btn-outline theme-secondary btn-sm" onclick="flevPopupLink('include/inc_tmpl/content/cnt51.open.php?cid=<?php
                echo $content["id"] ?>&amp;map=<?php
                echo rawurlencode($map_name);
                echo '&amp;points='.rawurlencode(implode(':|:', $map_xy));
                ?>','map','<?php
                echo 'scrollbars=yes,resizable=yes,width='.($map_info[0]+12).',height='.($map_info[1]+15)
                ?>',1);return document.MM_returnValue;">open map</button>
            </div>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <label class="form-label" for="cmap_location_title"><?php echo $BL['be_cnt_map_title'] ?></label>
        <input name="cmap_location_title" type="text" class="form-control" id="cmap_location_title" value="<?php echo  empty($content['location']["title"]) ? '' : html($content['location']["title"]) ?>" size="40" onchange="doMapChange();">
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label" for="cmap_location_zip"><?php echo $BL['be_profile_label_zip'] ?></label>
                <input name="cmap_location_zip" type="text" class="form-control" id="cmap_location_zip" value="<?php echo  empty($content['location']["zip"]) ? '' : html($content['location']["zip"]) ?>" size="8" onchange="doMapChange();">
            </div>
            <div class="flex-grow-1">
                <label class="form-label" for="cmap_location_city"><?php echo $BL['be_profile_label_city'] ?></label>
                <input name="cmap_location_city" type="text" class="form-control" id="cmap_location_city" value="<?php echo  empty($content['location']["city"]) ? '' : html($content['location']["city"]) ?>" size="30" onchange="doMapChange();">
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_map_info'] ?></div>
<?php

$wysiwyg_editor = array(
    'value'     => empty($content['location']["entry"]) ? '' : $content['location']["entry"],
    'field'     => 'cmap_location_entry',
    'height'    => '250px',
    'width'     => '100%',
    'rows'      => '7',
    'editor'    => $_SESSION["WYSIWYG_EDITOR"],
    'lang'      => 'en'
);
include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';

?>
    </div>

    <div class="col-12">
        <div class="d-flex align-items-center gap-2">
            <input type="hidden" name="cmap_location_id" value="<?php echo  empty($content['location']["id"]) ? 0 : intval($content['location']["id"]) ?>">
            <input type="hidden" id="cmap_location_edited" name="cmap_location_edited" value="1">
            <input name="Submit" type="submit" class="btn theme-primary btn-solid btn-styled btn-sm" value="<?php echo $BL['be_save_btn'] ?>">
        </div>
    </div>
<?php

    if($map_list) {
?>
    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_cnt_map_list'] ?></div>
        <ul class="list-group overflow-auto"<?php if($ck > 10) echo ' style="max-height:200px"'; ?>>
<?php echo $map_list; ?>
        </ul>
    </div>
<?php
    }

}
?>

    <div class="col-12">
        <label class="form-label" for="cmap_text"><?php echo $BL['be_admin_page_text'] ?></label>
        <textarea name="cmap_text" id="cmap_text" cols="40" rows="8" class="form-control autosize"><?php echo empty($content["map"]["text"]) ? '' : html($content["map"]["text"]) ?></textarea>
    </div>

</div>
