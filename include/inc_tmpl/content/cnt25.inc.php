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


// Flash Media Player

initJQuery(); // We use jQuery here

if(!$content["id"]) {
    include PHPWCMS_ROOT.'/include/inc_lib/content/cnt25.takeval.inc.php';
}

if(!is_array($fmp_data)) {
    $fmp_data = array();
}

if(!isset($fmp_data['fmp_set_skin_html5'])) {
    $fmp_data['fmp_set_skin_html5'] = '';
}
if(!isset($fmp_data['fmp_int_ext_h264'])) {
    // H.264
    $fmp_data['fmp_int_ext_h264']       = 0;
    $fmp_data['fmp_internal_id_h264']   = 0;
    $fmp_data['fmp_internal_name_h264'] = '';
    $fmp_data['fmp_external_file_h264'] = '';

    // WebM
    $fmp_data['fmp_int_ext_webm']       = 0;
    $fmp_data['fmp_internal_id_webm']   = 0;
    $fmp_data['fmp_internal_name_webm'] = '';
    $fmp_data['fmp_external_file_webm'] = '';

    // Ogg
    $fmp_data['fmp_int_ext_ogg']        = 0;
    $fmp_data['fmp_internal_id_ogg']    = 0;
    $fmp_data['fmp_internal_name_ogg']  = '';
    $fmp_data['fmp_external_file_ogg']  = '';
}

if(!isset($fmp_data['fmp_set_volume'])) {
    $fmp_data['fmp_set_volume'] = 80;
}
if(!isset($fmp_data['fmp_set_preload'])) {
    $fmp_data['fmp_set_preload'] = 'auto';
}
if(!isset($fmp_data['fmp_set_html5only'])) {
    $fmp_data['fmp_set_html5only'] = 0;
}
if(!isset($fmp_data['fmp_set_audio'])) {
    $fmp_data['fmp_set_audio'] = 0;
}
if(!isset($fmp_data['fmp_set_loop'])) {
    $fmp_data['fmp_set_loop'] = 0;
}
if(!isset($fmp_data['fmp_set_downloadbutton'])) {
    $fmp_data['fmp_set_downloadbutton'] = 0;
}

$fmp_html5_media = array(
    'h264' => array(
        'label'     => $BL['be_html5_h264'],
        'ext'       => 'mp4, m4v, mov, m4p, m4a, mp3, aac, mpeg',
        'opt'       => 12
    ),
    'webm' => array(
        'label'     => $BL['be_html5_webm'],
        'ext'       => 'webm',
        'opt'       => 13
    ),
    'ogg'  => array(
        'label'     => $BL['be_html5_ogg'],
        'ext'       => '.ogg, .ogv, .oga, .ogx',
        'opt'       => 14
    )
);

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="fmp_template"><?php echo $BL['be_admin_struct_template']; ?></label>
        <select name="fmp_template" id="fmp_template" class="form-control">
<?php

    echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

    // templates for Flash Media Player
    $tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/flashplayer');
    if(is_array($tmpllist) && count($tmpllist)) {
        foreach($tmpllist as $val) {
            $selected_val = (isset($fmp_data['fmp_template']) && $val == $fmp_data['fmp_template']) ? ' selected="selected"' : '';
            $val = html($val);
            echo '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>';
        }
    }

?>
        </select>
    </div>

    <div class="col-12 md:col-6">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div class="flex-grow-1">
                <label class="form-label" for="fmp_width_height"><?php echo $BL['be_cnt_maxw'] ?></label>
                <select name="fmp_width_height" id="fmp_width_height" class="form-control" onchange="setPlayerSize(this);">

                    <option><?php echo $BL['be_flashplayer_selectsize'] ?></option>
<?php

    if(empty($template_default['settings']['html5_player']['sizes'])) {

        $template_default['settings']['html5_player']['sizes'] = array(
            '426x240' => '240p',
            '640x360' => '360p',
            '854x480' => '480p',
            '1280x720' => '720p',
            '1920x1080' => '1080p',
            '2560x1440' => '1440p (2k)',
            '3840x2160' => '2160p (4k)',
            '640x267' => '640 x 267 (21:9)',
            '854x356' => '854 x 356 (21:9)',
            '1280x533' => '1280 x 533 (21:9)',
            '1920x800' => '1920 x 800 (21:9)',
            '200x178' => '200 x 178 px',
            '320x240' => '320 x 240 px',
            '380x313' => '380 x 313 px',
            '425x350' => '425 x 350 px',
            '450x338' => '450 x 338 px',
            '500x403' => '500 x 403 px',
            '640x264' => '640 x 264 px',
            '640x480' => '640 x 480 px'
        );

    }

        foreach($template_default['settings']['html5_player']['sizes'] as $val => $option):
?>
                    <option value="<?php echo $val ?>"<?php is_selected($val, $fmp_data['fmp_width'].'x'.$fmp_data['fmp_height']); ?>><?php echo html($option); ?></option>

<?php   endforeach; ?>

                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input name="fmp_width" type="text" class="form-control w-auto text-center" id="fmp_width" size="4" maxlength="4" value="<?php echo $fmp_data['fmp_width']; ?>">
                <span class="small fg-secondary">x</span>
                <input name="fmp_height" type="text" class="form-control w-auto text-center" id="fmp_height" size="4" maxlength="4" value="<?php echo $fmp_data['fmp_height']; ?>">
                <span class="small fg-secondary">px</span>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_html5_media'] ?></div>
        <div class="d-flex flex-column gap-4">
<?php foreach($fmp_html5_media as $fmp_key => $fmp_format): ?>
            <div>
                <div class="form-label mb-1"><?php echo $fmp_format['label'] ?> <span class="small fg-secondary">&mdash; <i><?php echo $fmp_format['ext'] ?></i></span></div>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-field">
                            <input class="radio" name="fmp_int_ext_<?php echo $fmp_key ?>" id="fmp_int_ext0_<?php echo $fmp_key ?>" type="radio" value="0" <?php is_checked(0, $fmp_data['fmp_int_ext_'.$fmp_key]); ?>>
                            <label for="fmp_int_ext0_<?php echo $fmp_key ?>"><?php echo $BL['be_cnt_internal'] ?></label>
                        </div>
                        <input name="fmp_internal_id_<?php echo $fmp_key ?>" type="hidden" id="fmp_internal_id_<?php echo $fmp_key ?>" value="<?php echo $fmp_data['fmp_internal_id_'.$fmp_key] ?>">
                        <input name="fmp_internal_name_<?php echo $fmp_key ?>" type="text" id="fmp_internal_name_<?php echo $fmp_key ?>" class="form-control w-auto" value="<?php echo html($fmp_data['fmp_internal_name_'.$fmp_key]) ?>" size="40" onfocus="this.blur()" onclick="openFileBrowser('filebrowser.php?opt=<?php echo $fmp_format['opt'] ?>');">
                        <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=<?php echo $fmp_format['opt'] ?>');"><?php echo $BL['be_cnt_openmediabrowser'] ?></a>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delmedia'], 'normal', ' onclick="getObjectById(\'fmp_internal_name_'.$fmp_key.'\').value=\'\';getObjectById(\'fmp_internal_id_'.$fmp_key.'\').value=\'0\';this.blur();return false;"', 'danger') ?>

                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-field">
                            <input class="radio" name="fmp_int_ext_<?php echo $fmp_key ?>" id="fmp_int_ext1_<?php echo $fmp_key ?>" type="radio" value="1" <?php is_checked(1, $fmp_data['fmp_int_ext_'.$fmp_key]); ?>>
                            <label for="fmp_int_ext1_<?php echo $fmp_key ?>"><?php echo $BL['be_cnt_external'] ?></label>
                        </div>
                        <input name="fmp_external_file_<?php echo $fmp_key ?>" type="text" id="fmp_external_file_<?php echo $fmp_key ?>" class="form-control" value="<?php echo html($fmp_data['fmp_external_file_'.$fmp_key]) ?>" size="40">
                    </div>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_flash_media'] ?> <span class="small fg-secondary">&mdash; <i>mp4, mp3, mov, m4v, jpg, png</i> / Flash (<?php echo $BL['be_deprecated'] ?>) <i>flv, swf, f4v</i></span></div>
        <div class="d-flex flex-column gap-2">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="form-field">
                    <input class="radio" name="fmp_int_ext" id="fmp_int_ext0" type="radio" value="0" <?php is_checked(0, $fmp_data['fmp_int_ext']); ?>>
                    <label for="fmp_int_ext0"><?php echo $BL['be_cnt_internal'] ?></label>
                </div>
                <input name="fmp_internal_id" type="hidden" id="fmp_internal_id" value="<?php echo $fmp_data['fmp_internal_id'] ?>">
                <input name="fmp_internal_name" type="text" id="fmp_internal_name" class="form-control w-auto" value="<?php echo html($fmp_data['fmp_internal_name']) ?>" size="40" onfocus="this.blur()" onclick="openFileBrowser('filebrowser.php?opt=6');">
                <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=6');"><?php echo $BL['be_cnt_openmediabrowser'] ?></a>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delmedia'], 'normal', ' onclick="getObjectById(\'fmp_internal_name\').value=\'\';getObjectById(\'fmp_internal_id\').value=\'0\';this.blur();return false;"', 'danger') ?>

            </div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="form-field">
                    <input class="radio" name="fmp_int_ext" id="fmp_int_ext1" type="radio" value="1" <?php is_checked(1, $fmp_data['fmp_int_ext']); ?>>
                    <label for="fmp_int_ext1"><?php echo $BL['be_cnt_external'] ?></label>
                </div>
                <input name="fmp_external_file" type="text" id="fmp_external_file" class="form-control" value="<?php echo html($fmp_data['fmp_external_file']) ?>" size="40">
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="fmp_caption"><?php echo $BL['be_flashplayer_caption'] ?></label>
        <textarea name="fmp_caption" cols="40" rows="2" class="form-control autosize" id="fmp_caption"><?php echo html($fmp_data['fmp_caption']) ?></textarea>
    </div>

    <div class="col-12">
        <label class="form-label" for="fmp_link"><?php echo $BL['be_admin_page_link'] ?></label>
        <input name="fmp_link" type="text" id="fmp_link" class="form-control" value="<?php echo html($fmp_data['fmp_link']) ?>" size="40">
    </div>

    <div class="col-12">
        <label class="form-label" for="fmp_img_name"><?php echo $BL['be_flashplayer_thumbnail'] ?></label>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <input name="fmp_img_name" type="text" id="fmp_img_name" class="form-control w-auto" value="<?php echo html($fmp_data['fmp_img_name']) ?>" size="40" onfocus="this.blur()" onclick="openFileBrowser('filebrowser.php?opt=7');">
            <a href="javascript:" class="btn btn-outline theme-secondary btn-sm" onclick="openFileBrowser('filebrowser.php?opt=7');"><?php echo $BL['be_cnt_openmediabrowser'] ?></a>
<?php echo backend_action('#', 'trash', $BL['be_cnt_delmedia'], 'normal', ' onclick="getObjectById(\'fmp_img_name\').value=\'\';getObjectById(\'fmp_img_id\').value=\'0\';this.blur();return false;"', 'danger') ?>

            <input name="fmp_img_id" type="hidden" id="fmp_img_id" value="<?php echo $fmp_data['fmp_img_id'] ?>">
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="fmp_marker"><?php echo $BL['be_flashplayer_marker'] ?></label>
        <textarea name="fmp_marker" cols="40" rows="2" class="form-control autosize" id="fmp_marker"><?php echo html($fmp_data['fmp_marker']) ?></textarea>
        <div class="small fg-secondary mt-3">
            <?php echo $BL['be_marker_time']; ?>
            |
            <?php echo $BL['be_marker_text']; ?>
            |
            <?php echo $BL['be_marker_overlaytext']; ?>
            |
            <?php echo $BL['be_cnt_css_class']; ?>&nbsp;&crarr;&nbsp;&hellip;
        </div>
        <div id="fmp_marker_links" style="display:none" class="mt-3"></div>
    </div>

    <div class="col-12">
        <div class="form-label"><?php echo $BL['be_settings'] ?></div>
        <div class="row g-3">
            <div class="col-12 md:col-6">
                <div class="d-flex flex-column gap-2">
                    <div class="form-field">
                        <input class="check" type="checkbox" name="fmp_set_html5only" id="fmp_set_html5only" value="1"<?php is_checked(1, $fmp_data['fmp_set_html5only']) ?>>
                        <label for="fmp_set_html5only"><?php echo $BL['be_display_html5_only'] ?></label>
                    </div>
                    <div class="form-field">
                        <input class="check" type="checkbox" name="fmp_set_audio" id="fmp_set_audio" value="1"<?php is_checked(1, $fmp_data['fmp_set_audio']) ?>>
                        <label for="fmp_set_audio"><?php echo $BL['be_audio_only'] ?></label>
                    </div>
                    <div class="form-field">
                        <input class="check" type="checkbox" name="fmp_set_autostart" id="fmp_set_autostart" value="1"<?php is_checked(1, $fmp_data['fmp_set_autostart']) ?>>
                        <label for="fmp_set_autostart">Autostart</label>
                    </div>
                    <div class="form-field">
                        <input class="check" type="checkbox" name="fmp_set_loop" id="fmp_set_loop" value="1"<?php is_checked(1, $fmp_data['fmp_set_loop']) ?>>
                        <label for="fmp_set_loop">Loop</label>
                    </div>
                    <div class="form-field">
                        <input class="check" type="checkbox" name="fmp_set_downloadbutton" id="fmp_set_downloadbutton" value="1"<?php is_checked(1, $fmp_data['fmp_set_downloadbutton']) ?>>
                        <label for="fmp_set_downloadbutton"><?php echo $BL['be_hide_downloadbutton'] ?></label>
                    </div>
                </div>
            </div>
            <div class="col-12 md:col-6">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label" for="fmp_set_preload">Preload</label>
                        <select name="fmp_set_preload" id="fmp_set_preload" class="form-control">
                            <option value="auto"<?php is_selected('auto', $fmp_data['fmp_set_preload']) ?>><?php echo $BL['automatic']; ?></option>
                            <option value="metadata"<?php is_selected('metadata', $fmp_data['fmp_set_preload']) ?>>Metadata</option>
                            <option value="none"<?php is_selected('none', $fmp_data['fmp_set_preload']) ?>><?php echo $BL['be_off'] ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="fmp_set_skin_html5"><?php echo $BL['be_skin'].' '.$BL['be_html5_media'] ?></label>
                        <select name="fmp_set_skin_html5" id="fmp_set_skin_html5" class="form-control">
                            <option value="default"<?php is_selected('', $fmp_data['fmp_set_skin_html5']) ?>><?php echo $BL['be_admin_tmpl_default'] ?></option>
<?php
                            // skins for HTML5 Media Player
                            $skins = returnFileListAsArray(PHPWCMS_TEMPLATE.'lib/video-js/skins', 'css');
                            if(is_array($skins) && count($skins)):
                                foreach($skins as $skin):
                                    $skin = cut_ext($skin['filename']);
?>
                            <option value="<?php
                                echo html($skin)
                            ?>"<?php is_selected($skin, $fmp_data['fmp_set_skin_html5']) ?>><?php
                                echo html(ucwords(str_replace('_', ' ', $skin)))
                            ?></option>
<?php
                                endforeach;
                            endif;
?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="fmp_set_showcontrols">Controlbar</label>
                        <select name="fmp_set_showcontrols" id="fmp_set_showcontrols" class="form-control">
                            <option value="bottom"<?php is_selected('bottom', $fmp_data['fmp_set_showcontrols']) ?>><?php echo $BL['be_admin_tmpl_default'] ?></option>
                            <option value="none"<?php is_selected('none', $fmp_data['fmp_set_showcontrols']) ?>><?php echo $BL['be_admin_struct_hide1'] ?></option>
                            <option value="over"<?php is_selected('over', $fmp_data['fmp_set_showcontrols']) ?>><?php echo $BL['over'] ?></option>
                        </select>
                        <input type="hidden" name="fmp_set_largecontrols" id="fmp_set_largecontrols" value="0">
                        <input type="hidden" name="fmp_set_showdigits" id="fmp_set_showdigits" value="0">
                        <input type="hidden" name="fmp_set_showeq" id="fmp_set_showeq" value="0">
                        <input type="hidden" name="fmp_set_showvolume" id="fmp_set_showvolume" value="0">
                        <input type="hidden" name="fmp_set_showdownload" id="fmp_set_showdownload" value="0">
                    </div>
                    <div>
                        <label class="form-label" for="fmp_set_volume"><?php echo $BL['be_player_volume'] ?></label>
                        <select name="fmp_set_volume" id="fmp_set_volume" class="form-control">
<?php       for($x=0; $x<=100; $x+=5) : ?>
                            <option value="<?php echo $x ?>"<?php is_selected($x, $fmp_data['fmp_set_volume']) ?>><?php echo ($x ? $x.' %' : $BL['be_player_volume_muted']) ?></option>
<?php       endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-end gap-3">
                    <div>
                        <label class="form-label" for="fmp_set_bgcolor"><?php echo $BL['be_background_color'].' '.$BL['be_flash_media'] ?> (HEX)</label>
                        <input name="fmp_set_bgcolor" type="text" class="form-control" id="fmp_set_bgcolor" value="<?php echo html($fmp_data['fmp_set_bgcolor']) ?>" size="40" maxlength="7">
                    </div>
                    <div>
                        <label class="form-label" for="fmp_set_color"><?php echo $BL['be_foreground_color'].' '.$BL['be_flash_media'] ?> (HEX)</label>
                        <input name="fmp_set_color" type="text" class="form-control" id="fmp_set_color" value="<?php echo html($fmp_data['fmp_set_color']) ?>" size="40" maxlength="7">
                    </div>
                    <div class="flex-grow-1">
                        <label class="form-label" for="fmp_set_logo"><?php echo $BL['be_media_watermark'].' '.$BL['be_flash_media'].' ('.$BL['be_cnt_pages_cust'].')' ?></label>
                        <input name="fmp_set_logo" type="text" class="form-control" id="fmp_set_logo" value="<?php echo html($fmp_data['fmp_set_logo']) ?>" size="40">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">

    function setIdName(file_id, file_name, file_type) {
        if(!file_id) {
            file_id = 0;
        }
        if(!file_name) {
            file_name = '';
        }
        if(file_type == 6 || file_type == null) {
            $('#fmp_internal_id').val(file_id);
            $('#fmp_internal_name').val(file_name);
        } else if(file_type == 12) { // H.264
            $('#fmp_internal_id_h264').val(file_id);
            $('#fmp_internal_name_h264').val(file_name);
        } else if(file_type == 13) { // WebM
            $('#fmp_internal_id_webm').val(file_id);
            $('#fmp_internal_name_webm').val(file_name);
        } else if(file_type == 14) { // Ogg
            $('#fmp_internal_id_ogg').val(file_id);
            $('#fmp_internal_name_ogg').val(file_name);
        }
    }
    function setImgIdName(file_id, file_name) {
        if(!file_id) {
            file_id = 0;
        }
        if(!file_name) {
            file_name = '';
        }
        $('#fmp_img_id').val(file_id);
        $('#fmp_img_name').val(file_name);
    }
    function setPlayerSize(sval) {
        var indx = sval.selectedIndex;
        if(indx > 0) {
            var val = sval.options[indx].value.split('x');
            $('#fmp_width').val(parseInt(val[0], 10) || 426);
            $('#fmp_height').val(parseInt(val[1], 10) || 240);
        }
        sval.options[0].selected = true;
        sval.blur();
    }

    $(function () {
        let cid = $('#cid');

        if (cid.length) {
            let fmpMarker = $('#fmp_marker'),
                fmpMarkerLinks = $('#fmp_marker_links'),
                fmpId = cid.val(),
                eventLastTime = 0,
                eventDelay = 750,
                setMarkerLinks = function (str) {
                    if (str) {
                        let marker = $.trim(str).split('\n'),
                            markerLinks = [];
                        if (marker.length) {
                            for (let i = 0; i < marker.length; i++) {
                                let item = $.trim(marker[i]);
                                if (item) {
                                    item = item.split('|');
                                    if (item.length) {
                                        if (typeof item[0] !== 'undefined') {
                                            let timer = parseFloat($.trim(item[0]));
                                            if (timer) {
                                                let anchor = '#fmp' + fmpId + '-',
                                                    get = 'fmp=' + fmpId + '-',
                                                    markerNum = markerLinks.length + 1;
                                                    link = '<strong><?php echo $BL['be_flashplayer_marker']; ?> ' + markerNum + ':</strong><br>';

                                                link += '<?php echo $BL['be_article_cnt_anchor']; ?> ';
                                                link += '<a href="#" onclick="copyToClipboard(\'' + anchor + timer + '\');return false;" title="<?php echo $BL['copy_to_clipboard']; ?>">' + anchor + timer + '</a>, ';
                                                link += '<a href="#" onclick="copyToClipboard(\'' + anchor + 'm' + markerNum + '\');return false;" title="<?php echo $BL['copy_to_clipboard']; ?>">' + anchor + 'm' + markerNum + '</a><br>';
                                                link += '<?php echo $BL['url_parameter']; ?> ';
                                                link += '<a href="#" onclick="copyToClipboard(\'' + get + timer + '\');return false;" title="<?php echo $BL['copy_to_clipboard']; ?>">' + get + timer + '</a>, ';
                                                link += '<a href="#" onclick="copyToClipboard(\'' + get + 'm' + markerNum + '\');return false;" title="<?php echo $BL['copy_to_clipboard']; ?>">' + get + 'm' + markerNum + '</a>';

                                                markerLinks.push(link);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        fmpMarkerLinks.html(markerLinks.join('<br>'));
                        fmpMarkerLinks.show();
                    } else {
                        fmpMarkerLinks.html('');
                        fmpMarkerLinks.hide();
                    }
                };

            fmpMarker.on('change keyup', function (event) {
                if (event.type === 'keyup') {
                    let dateObject = new Date();
                    if ((dateObject.getTime() - eventLastTime) > eventDelay) {
                        eventLastTime = dateObject.getTime();
                        setMarkerLinks($(this).val());
                    }
                } else {
                    setMarkerLinks($(this).val());
                }
            });

            setMarkerLinks(fmpMarker.val());
        }
    });

</script>
