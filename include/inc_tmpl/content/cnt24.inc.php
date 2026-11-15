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


// Alias Content
$content['alias_link'] = '';

if(empty($content["alias"]['alias_ID'])) {
    $content["alias"]['alias_ID'] = '';
} else {
    $content["alias"]['alias_ID'] = intval($content["alias"]['alias_ID']);
    $sql_cnt  = "SELECT * FROM ".DB_PREPEND."phpwcms_articlecontent WHERE acontent_id=".$content["alias"]['alias_ID']." AND acontent_trash=0";
    $cntresult = _dbQuery($sql_cnt);
    if(isset($cntresult[0]['acontent_id'])) {
        if($cntresult[0]['acontent_type'] == 30 && (!$cntresult[0]['acontent_module'] || !isset($phpwcms['modules'][$cntresult[0]['acontent_module']]))) {
            $content['alias_link'] = '<span class="badge theme-danger">'.$BL['be_cnt_plugin_n.a.'].'</span>';
        } else {
            $content['alias_link']  = '<a href="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id=';
            $content['alias_link'] .= $cntresult[0]['acontent_aid'].'&amp;acid='.$content["alias"]['alias_ID'];
            $content['alias_link'] .= '" target="_blank" class="btn btn-outline theme-secondary btn-sm">';
            $content['alias_link'] .= $BL['be_article_cnt_edit'].': '.$wcs_content_type[$cntresult[0]['acontent_type']].'</a>';
        }
    }
}

$content["alias"]['alias_block']    = empty($content["alias"]['alias_block']) ? 0 : 1;
$content["alias"]['alias_spaces']   = empty($content["alias"]['alias_spaces']) ? 0 : 1;
$content["alias"]['alias_title']    = empty($content["alias"]['alias_title']) ? 0 : 1;
$content["alias"]['alias_toplink']  = empty($content["alias"]['alias_toplink']) ? 0 : 1;
$content["alias"]['alias_status']   = empty($content["alias"]['alias_status']) ? 0 : 1;

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="calias"><?php echo $BL['be_alias_ID'] ?></label>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <input name="calias" type="text" class="form-control w-auto" id="calias" value="<?php echo $content["alias"]['alias_ID'] ?>">
            <?php echo $content['alias_link']; ?>
        </div>
    </div>

    <div class="col-12 md:col-6">
        <div class="form-label"><?php echo $BL['be_cnt_setting'] ?></div>
        <div class="form-field">
            <input class="check" type="checkbox" name="cablock" id="cablock" value="1"<?php is_checked(1, $content["alias"]['alias_block']); ?>>
            <label for="cablock"><?php echo $BL['be_cnt_block'] ?></label>
        </div>
        <div class="form-field mt-3">
            <input class="check" type="checkbox" name="caspaces" id="caspaces" value="1"<?php is_checked(1, $content["alias"]['alias_spaces']); ?>>
            <label for="caspaces"><?php echo $BL['be_cnt_spaces'] ?></label>
        </div>
        <div class="form-field mt-3">
            <input class="check" type="checkbox" name="catitle" id="catitle" value="1"<?php is_checked(1, $content["alias"]['alias_title']); ?>>
            <label for="catitle"><?php echo $BL['be_cnt_title'] ?></label>
        </div>
        <div class="form-field mt-3">
            <input class="check" type="checkbox" name="catop" id="catop" value="1"<?php is_checked(1, $content["alias"]['alias_toplink']); ?>>
            <label for="catop"><?php echo $BL['be_cnt_toplink'] ?></label>
        </div>
        <div class="form-field mt-3">
            <input class="check" type="checkbox" name="castatus" id="castatus" value="1"<?php is_checked(1, $content["alias"]['alias_status']); ?>>
            <label for="castatus"><?php echo $BL['be_cnt_status'] ?></label>
        </div>
    </div>

</div>
