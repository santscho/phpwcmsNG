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


// Be more modern here - we start switch to jQuery and overwrite non-used MooTools with jQuery call
initJsAutocompleter();

// Upload new file
$file_aktiv             = $phpwcms['set_file_active'];
$file_public            = $phpwcms['set_file_active'];
$file_shortinfo         = '';
$file_longinfo          = '';
$file_pid               = empty($_GET["upload"]) ? 0 : intval($_GET["upload"]);
$file_copyright         = '';
$file_tags              = '';
$file_granted           = 0;
$file_gallerydownload   = 0;
$file_sort              = 0;
$file_title     = '';
$file_alt       = '';

//Auswerten des Formulars
if(isset($_POST["file_aktion"]) && intval($_POST["file_aktion"]) == 1) {

    if(!ini_get('safe_mode') && function_exists('set_time_limit')) {
        set_time_limit(0);
    }

    $file_pid               = intval($_POST["file_pid"]);
    $file_aktiv             = empty($_POST["file_aktiv"]) ? 0 : 1;
    $file_public            = empty($_POST["file_public"]) ? 0 : 1;
    $file_shortinfo         = clean_slweg($_POST["file_shortinfo"]);
    $file_longinfo          = slweg($_POST["file_longinfo"]);
    $file_copyright         = clean_slweg($_POST["file_copyright"]);
    $file_tags              = trim(clean_slweg($_POST["file_tags"]), ',');
    $file_granted           = empty($_POST["file_granted"]) ? 0 : 1;
    $file_gallerydownload   = empty($_POST["file_gallerydownload"]) ? 0 : 1;
    $file_keys              = '';
    $file_sort              = intval($_POST["file_sort"]);
    $file_title             = clean_slweg($_POST["file_title"]);
    $file_alt               = clean_slweg($_POST["file_alt"]);
    $file_is_uploaded       = isset($_FILES["file"]["tmp_name"]) && is_uploaded_file($_FILES["file"]["tmp_name"]);
    $file_iptc_info         = null;
    $file_image_size        = null;
    $file_svg               = 0;

    if($file_is_uploaded) {

        // Try to read image data
        $file_image_size = getimagesize($_FILES["file"]["tmp_name"], $file_image_info);

    }

    // Check against IPTC and handle IPTC tags if applicable
    if(!empty($_POST['file_iptc_as_caption']) && isset($file_image_info['APP13'])) {
        $file_image_iptc = IPTC::parse($file_image_info['APP13']);
        $file_iptc_info = render_iptc_fileinfo($file_image_iptc);

        if($file_title === '') {
            $file_title = $file_iptc_info['title'];
        }
        if($file_longinfo === '') {
            $file_longinfo = $file_iptc_info['longinfo'];
        }
        if($file_copyright === '') {
            $file_copyright = $file_iptc_info['copyright'];
        }
        if($file_alt === '') {
            $file_alt = $file_iptc_info['alt'];
        }
    }

    if(count($phpwcms['allowed_lang']) > 1) {

        $file_vars = array();

        foreach($phpwcms['allowed_lang'] as $lang) {
            $lang = strtolower($lang);

            $file_vars[$lang] = array(
                'longinfo' => '',
                'copyright' => '',
                'title' => '',
                'alt' => ''
            );

            if($phpwcms['default_lang'] === $lang) {
                $file_vars[$lang]['longinfo'] = $file_longinfo;
                $file_vars[$lang]['copyright'] = $file_copyright;
                $file_vars[$lang]['title'] = $file_title;
                $file_vars[$lang]['alt'] = $file_alt;
            }

            if(isset($_POST['file_longinfo_'.$lang])) {
                $file_vars[$lang]['longinfo'] = slweg($_POST['file_longinfo_'.$lang]);
            }
            if(isset($_POST['file_copyright_'.$lang])) {
                $file_vars[$lang]['copyright'] = clean_slweg($_POST['file_copyright_'.$lang]);
            }
            if(isset($_POST['file_title_'.$lang])) {
                $file_vars[$lang]['title'] = clean_slweg($_POST['file_title_'.$lang]);
            }
            if(isset($_POST['file_alt_'.$lang])) {
                $file_vars[$lang]['alt'] = clean_slweg($_POST['file_alt_'.$lang]);
            }

            // Set file info based on IPTC for all languages
            if(!empty($phpwcms['iptc_as_caption_all_lang']) && $file_iptc_info !== null) {
                if($file_vars[$lang]['title'] === '') {
                    $file_vars[$lang]['title'] = $file_iptc_info['title'];
                }
                if($file_vars[$lang]['longinfo'] === '') {
                    $file_vars[$lang]['longinfo'] = $file_iptc_info['longinfo'];
                }
                if($file_vars[$lang]['copyright'] === '') {
                    $file_vars[$lang]['copyright'] = $file_iptc_info['copyright'];
                }
                if($file_vars[$lang]['alt'] === '') {
                    $file_vars[$lang]['alt'] = $file_iptc_info['alt'];
                }
            }

        }
    }

    $file_keywords = empty($_POST["file_keywords"]) ? array() : $_POST["file_keywords"];
    if(count($file_keywords)) {
        foreach($file_keywords as $key => $value) {
            unset($file_keywords[$key]);
            $key = intval($key);
            if($value != "0_1") {
                $file_keys .= (($file_keys) ? ":" : "").$key."_".intval($value);
                $file_keywords[$key] = intval($value);
            } else {
                $file_error["keywords"][$key] = 1;
            }
        }
    }

    //starts upload of file
    if(!$file_is_uploaded) {

        $file_error["file"] = $BL['be_fprivup_err1'];

    } elseif($_FILES["file"]["size"] > $phpwcms["file_maxsize"]) {

        $file_error["file"] = $BL['be_fprivup_err2']." ".number_format($phpwcms["file_maxsize"] / 1024, 2, ',', '.')." kB";

    } else {

        $fileName = sanitize_filename($_FILES["file"]["name"]);
        $uploadedExt = strtolower(which_ext($fileName));
        $detected_ext = check_image_extension($_FILES["file"]["tmp_name"], $fileName, $file_image_size);
        if ($detected_ext === false) {
            $fileExt = $uploadedExt;
        } else {
            $fileExt = $detected_ext;
        }
        $fileHash = md5($fileName . microtime());
        $fileType = !empty($file_image_size['mime']) ? $file_image_size['mime'] : (is_mimetype_format($_FILES["file"]["type"]) ? $_FILES["file"]["type"] : get_mimetype_by_extension($fileExt));
        $fileSize = intval($_FILES["file"]["size"]);

        // Check against forbidden file names and spoofed WebP extensions
        $forbiddenUploadName = array(
            '.htaccess', // Apache config
            'web.config', // IIS config
            'lighttpd.conf', // Lighttpd
            'nginx.conf', // Nginx
        );

        if (
            str_starts_with($fileName, '.') ||
            in_array(strtolower($fileName), $forbiddenUploadName) ||
            ($detected_ext === 'webp' && $uploadedExt !== 'webp') ||
            ($uploadedExt === 'webp' && $detected_ext !== 'webp')
        ) {
            $file_error["file"] = sprintf($BL['be_fprivup_err7'], $fileName);
        }

        // Only allowed file extensions
        if(empty($file_error["file"])) {

            if(is_string($phpwcms['allowed_upload_ext'])) {
                $phpwcms['allowed_upload_ext'] = convertStringToArray(strtolower($phpwcms['allowed_upload_ext']));
            }

            $fileExt = strtolower($fileExt);

            if($fileExt === '') {

                $file_error["file"] = sprintf($BL['be_fprivup_err9'], implode(', ', $phpwcms['allowed_upload_ext']));

            } elseif(is_array($phpwcms['allowed_upload_ext']) && count($phpwcms['allowed_upload_ext']) && !in_array($fileExt, $phpwcms['allowed_upload_ext'])) {

                $file_error["file"] = sprintf($BL['be_fprivup_err8'], $fileName, implode(', ', $phpwcms['allowed_upload_ext']));

            } elseif(!$file_image_size && $fileExt === 'svg') {

                require_once PHPWCMS_ROOT.'/include/inc_lib/classes/class.svg-reader.php';

                if($file_svg = @SVGMetadataExtractor::getMetadata($_FILES["file"]["tmp_name"])) {
                    $fileType = 'image/svg+xml';
                    $file_image_size = array(
                        0 => $file_svg['width'],
                        1 => $file_svg['height']
                    );
                    $file_svg = 1;
                }
            }
        }
    }

    if(empty($file_error)) {

        if(isset($file_vars)) {
            $fileVarsField = ',f_vars';
            $fileVarsValue = ','._dbEscape(serialize($file_vars));
        } else {
            $fileVarsField = '';
            $fileVarsValue = '';
        }

        $sql =  "INSERT INTO ".DB_PREPEND."phpwcms_file (".
                "f_pid, f_uid, f_kid, f_aktiv, f_public, f_name, f_created, f_size, f_type, f_ext, f_svg, f_image_width, f_image_height, ".
                "f_shortinfo, f_longinfo, f_keywords, f_hash, f_copyright, f_tags, f_granted, f_gallerystatus, ".
                "f_sort".$fileVarsField.", f_title, f_alt) VALUES (".
                $file_pid.", ".intval($_SESSION["wcs_user_id"]).", 1, ".$file_aktiv.", ".$file_public.", '".
                $fileName."', '".time()."', '".$fileSize."', '".aporeplace($fileType)."', '".$fileExt."', ".
                $file_svg.', '._dbEscape(empty($file_image_size[0]) ? '' : $file_image_size[0]).", ".
                _dbEscape(empty($file_image_size[1]) ? '' : $file_image_size[1]).", '".aporeplace($file_shortinfo)."', '".
                aporeplace($file_longinfo)."', '".aporeplace($file_keys)."', '".aporeplace($fileHash)."', '".
                aporeplace($file_copyright)."', '".aporeplace($file_tags)."', ".$file_granted.", ".
                $file_gallerydownload.", ".$file_sort.$fileVarsValue.","._dbEscape($file_title).", "._dbEscape($file_alt).")";
        $result = _dbQuery($sql, 'INSERT');

        if(!empty($result['INSERT_ID'])) {
            $new_fileId = $result['INSERT_ID']; //Festlegen der aktuellen File-ID
            $wcs_newfilename = ($fileExt) ? $fileHash.'.'.$fileExt : $fileHash;

            // changed for using hashed file names
            $useruploadpath = PHPWCMS_ROOT.$phpwcms["file_path"];
            $usernewfile    = $useruploadpath.$wcs_newfilename;

            if ($dir = @opendir($useruploadpath)) {
                if(!@move_uploaded_file($_FILES["file"]["tmp_name"], $usernewfile)) {

                    $file_error["upload"] = $BL['be_fprivup_err3'].' (1)';
                }
            } else {
                $oldumask = umask(0);
                if(@mkdir($useruploadpath, 0777)) {
                    if(!@move_uploaded_file($_FILES["file"]["tmp_name"], $usernewfile)) {
                        $file_error["upload"] = $BL['be_fprivup_err3'].' (2)';
                    }
                } else {
                    $file_error["upload"] = $BL['be_fprivup_err4'];
                }
                umask($oldumask);
            }
            if(is_file($usernewfile)) {
                @chmod($usernewfile, 0666);
            }
            if(empty($file_error["upload"])) {

                // store tags
                _dbSaveCategories($file_tags, 'file', $new_fileId, ',');

                //after successful upload go back to clear post (form) var
                headerRedirect(PHPWCMS_URL.'phpwcms.php?'.get_token_get_string().'&do=files&f=0&uploaded=1');

            } else {

                echo $file_error["upload"]."<br />";
                $file_error["upload"] = str_replace('{VAL}', $phpwcms["admin_email"], $BL['be_fprivup_err6']);
                _dbQuery("DELETE FROM ".DB_PREPEND."phpwcms_file WHERE f_id=".$new_fileId." AND f_uid=".$_SESSION["wcs_user_id"], 'DELETE');

            }
        }
    }

    if(!ini_get('safe_mode') && function_exists('set_time_limit')) {
        set_time_limit(30);
    }
}

// Init Exif.js library
$GLOBALS['BE']['HEADER']['exif.js'] = getJavaScriptSourceLink('include/inc_js/exif.min.js');
$GLOBALS['BE']['BODY_CLOSE']['exif.js.upload'] = '<script type="text/javascript">
document.getElementById("file").onchange = function(e) {
    var iptcdata = document.getElementById("iptc-info");
    iptcdata.innerHTML = "";
    EXIF.getData(e.target.files[0], function() {
        //alert(EXIF.pretty(this));
        var iptctags = {
            Caption: EXIF.getIptcTag(this, "caption"),
            Copyright: EXIF.getIptcTag(this, "copyright"),
            Credit: EXIF.getIptcTag(this, "credit"),
            Title: EXIF.getIptcTag(this, "headline"),
            Category: EXIF.getIptcTag(this, "category"),
            Writer: EXIF.getIptcTag(this, "captionWriter"),
            Creator: EXIF.getIptcTag(this, "byline"),
            Profession: EXIF.getIptcTag(this, "bylineTitle")
        }, iptctable = "";
        for(var a in iptctags) {
            if(iptctags.hasOwnProperty(a)) {
                if(typeof iptctags[a] === "string") {
                    var val = iptctags[a].trim();
                    if(val !== "") {
                        iptctable += \'<dt class="col-3 fg-secondary">\' + a + \'</dt><dd class="col-9">\' + val + \'</dd>\';
                    }
                }
            }
        }
        if(iptctable !== "") {
            iptctable = \'<dl class="row mb-0">\' + iptctable + \'</dl>\';
        }
        iptcdata.innerHTML = iptctable;
    });
}
</script>';

?>
<form action="phpwcms.php?do=files&amp;f=0" method="post" enctype="multipart/form-data" name="uploadfile" id="uploadfile">
<div class="card mb-4">
    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">
        <h2 class="h6 mb-0"><?php echo $BL['be_fprivup_title'] ?></h2>
        <a href="phpwcms.php?do=files&amp;f=0" class="btn btn-outline theme-secondary btn-sm"><?php echo $BL['be_func_struct_close'] ?></a>
    </div>
    <div class="card-body">
<?php if(isset($file_error["upload"])) { ?>
        <div class="alert theme-danger mb-4"><?php echo $file_error["upload"] ?></div>
<?php }
if(isset($file_error["file"])) { ?>
        <div class="alert theme-danger mb-4"><?php echo $file_error["file"] ?></div>
<?php } ?>
        <div class="row g-4">
            <div class="col-12 md:col-6">
                <label class="form-label" for="file_pid"><?php echo $BL['be_ftptakeover_directory'] ?></label>
                <select name="file_pid" id="file_pid" class="form-control">
                    <option value="0"><?php echo $BL['be_ftptakeover_rootdir'] ?></option>
                    <?php dir_menu(0, $file_pid, "+", $_SESSION["wcs_user_id"], "+"); ?>
                </select>
            </div>
            <div class="col-12 md:col-6">
                <label class="form-label" for="file"><?php echo $BL['be_fprivup_upload'] ?></label>
                <input name="file" type="file" id="file" class="form-control">
            </div>
        </div>

        <div class="border-top mt-4 pt-4">
            <div class="form-label mb-2"><?php echo $BL['be_iptc_data'] ?></div>
            <div class="form-field">
                <input class="check" name="file_iptc_as_caption" type="checkbox" id="file_iptc_as_caption" value="1"<?php if(!empty($phpwcms['iptc_as_caption'])): ?> checked="checked"<?php endif; ?>>
                <label for="file_iptc_as_caption"><?php echo $BL['be_iptc_as_caption'] ?></label>
            </div>
            <div id="iptc-info"></div>
        </div>

        <div class="border-top mt-4 pt-4">
<?php if(count($phpwcms['allowed_lang']) > 1): ?>
            <div class="nav nav-pills flex-wrap mb-4 file-lang-tabs">
                <a href="#" rel="<?php echo $phpwcms['default_lang'] ?>" class="nav-link active" aria-current="page" title="<?php echo get_language_name($phpwcms['default_lang']) . ' (' . $BL['be_admin_tmpl_default'] . ')' ?>">
                    <?php echo strtoupper($phpwcms['default_lang']) ?> <span class="badge theme-secondary"><?php echo $BL['be_admin_tmpl_default'] ?></span>
                </a>
<?php   foreach($phpwcms['allowed_lang'] as $lang):

            $lang = strtolower($lang);

            if($lang == $phpwcms['default_lang']) {
                continue;
            }
?>
                <a href="#" rel="<?php echo $lang ?>" class="nav-link" title="<?php echo get_language_name($lang) ?>"><?php echo strtoupper($lang) ?></a>
<?php   endforeach; ?>
            </div>
<?php endif; ?>

            <div class="finfo finfo<?php echo $phpwcms['default_lang'] ?>">
                <div class="row g-4">
                    <div class="col-12 md:col-6">
                        <label class="form-label" for="file_title"><?php echo $BL['be_attr_title'] ?></label>
                        <input name="file_title" type="text" id="file_title" class="form-control" maxlength="1000" value="<?php echo html($file_title) ?>">
                    </div>
                    <div class="col-12 md:col-6">
                        <label class="form-label" for="file_copyright"><?php echo $BL['be_copyright'] ?></label>
                        <input name="file_copyright" type="text" id="file_copyright" class="form-control" maxlength="1000" value="<?php echo html($file_copyright) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="file_longinfo"><?php echo $BL['be_cnt_description'] ?></label>
                        <textarea name="file_longinfo" rows="4" class="form-control autosize" id="file_longinfo"><?php echo html($file_longinfo) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="file_alt"><?php echo $BL['be_attr_alt'] ?></label>
                        <input name="file_alt" type="text" id="file_alt" class="form-control" maxlength="1000" value="<?php echo html($file_alt) ?>">
                    </div>
                </div>
            </div>

<?php   if(count($phpwcms['allowed_lang']) > 1):

            foreach($phpwcms['allowed_lang'] as $lang):

                $lang = strtolower($lang);

                if($lang == $phpwcms['default_lang']) {
                    continue;
                }

                $file_vars[$lang] = array(
                    'longinfo' => '',
                    'copyright' => '',
                    'title' => '',
                    'alt' => ''
                );
?>
            <div class="finfo finfo<?php echo $lang ?> d-none">
                <div class="row g-4">
                    <div class="col-12 md:col-6">
                        <label class="form-label" for="file_title_<?php echo $lang ?>"><?php echo $BL['be_attr_title'] ?></label>
                        <input name="file_title_<?php echo $lang ?>" type="text" id="file_title_<?php echo $lang ?>" class="form-control" maxlength="1000" value="<?php echo html($file_vars[$lang]['title']) ?>">
                    </div>
                    <div class="col-12 md:col-6">
                        <label class="form-label" for="file_copyright_<?php echo $lang ?>"><?php echo $BL['be_copyright'] ?></label>
                        <input name="file_copyright_<?php echo $lang ?>" type="text" id="file_copyright_<?php echo $lang ?>" class="form-control" maxlength="1000" value="<?php echo html($file_vars[$lang]['copyright']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="file_longinfo_<?php echo $lang ?>"><?php echo $BL['be_cnt_description'] ?></label>
                        <textarea name="file_longinfo_<?php echo $lang ?>" rows="4" class="form-control autosize" id="file_longinfo_<?php echo $lang ?>"><?php echo html($file_vars[$lang]['longinfo']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="file_alt_<?php echo $lang ?>"><?php echo $BL['be_attr_alt'] ?></label>
                        <input name="file_alt_<?php echo $lang ?>" type="text" id="file_alt_<?php echo $lang ?>" class="form-control" maxlength="1000" value="<?php echo html($file_vars[$lang]['alt']) ?>">
                    </div>
                </div>
            </div>
<?php       endforeach;
        endif;
?>

        </div>

        <div class="border-top mt-4 pt-4">
            <h3 class="h6 mb-3"><?php echo $BL['be_ftptakeover_keywords'] ?></h3>
            <div class="row g-4">
<?php

    //Auswahlliste vordefinierte Keywörter
    $sql = "SELECT * FROM ".DB_PREPEND."phpwcms_filecat WHERE fcat_deleted=0 ORDER BY fcat_sort, fcat_name";
    $result = _dbQuery($sql);

    if(isset($result[0]['fcat_id'])) {
        foreach($result as $row) {

            if(!get_filecat_childcount($row["fcat_id"])) {
                continue;
            }
?>
                <div class="col-12 md:col-6">
                    <label class="form-label" for="file_keywords_<?php echo $row["fcat_id"] ?>">
                        <?php echo html($row["fcat_name"]) ?><?php if(isset($file_error["keywords"][$row["fcat_id"]])): ?> <span class="badge theme-danger">!</span><?php endif; ?>
                    </label>
                    <select name="file_keywords[<?php echo $row["fcat_id"] ?>]" id="file_keywords_<?php echo $row["fcat_id"] ?>" class="form-control">
                        <option value="<?php echo (($row["fcat_needed"]) ? "0_".$row["fcat_needed"] : "0") ?>"><?php echo (($row["fcat_needed"]) ? $BL['be_ftptakeover_needed'] : $BL['be_ftptakeover_optional']) ?></option>
<?php
            $ksql = "SELECT * FROM ".DB_PREPEND."phpwcms_filekey WHERE fkey_deleted=0 AND fkey_cid=".$row["fcat_id"]." ORDER BY fkey_name";
            $kresult = _dbQuery($ksql);
            if(isset($kresult[0]['fkey_id'])) {
                foreach($kresult as $krow) {
?>
                        <option value="<?php echo $krow["fkey_id"] ?>"<?php if(isset($file_keywords[$row["fcat_id"]]) && $file_keywords[$row["fcat_id"]] == $krow["fkey_id"]): ?> selected="selected"<?php endif; ?>><?php echo html($krow["fkey_name"]) ?></option>
<?php
                }
            }
?>
                    </select>
                </div>
<?php
        }
    }
?>
                <div class="col-12 md:col-6">
                    <label class="form-label" for="file_shortinfo"><?php echo $BL['be_ftptakeover_additional'] ?></label>
                    <input name="file_shortinfo" type="text" class="form-control" id="file_shortinfo" value="<?php echo html($file_shortinfo) ?>" maxlength="750">
                </div>
            </div>
        </div>

        <div class="border-top mt-4 pt-4">
            <div class="row g-4">
                <div class="col-12 md:col-6">
                    <label class="form-label" for="file_tags_autosuggest"><?php echo $BL['be_tags'] ?></label>
                    <input type="text" class="form-control" id="file_tags_autosuggest">
                    <input name="file_tags" type="hidden" id="file_tags" value="<?php echo html($file_tags) ?>">
                </div>
                <div class="col-12 md:col-6">
                    <label class="form-label" for="file_sort"><?php echo $BL['be_cnt_sorting'] ?></label>
                    <input name="file_sort" type="text" id="file_sort" class="form-control" maxlength="10" value="<?php echo intval($file_sort) ?>">
                </div>
                <div class="col-12 md:col-6">
                    <div class="form-field">
                        <input class="check" name="file_aktiv" type="checkbox" id="file_aktiv" value="1"<?php is_checked("1", $file_aktiv) ?>>
                        <label for="file_aktiv"><?php echo $BL['be_ftptakeover_active'] ?></label>
                    </div>
                    <div class="form-field">
                        <input class="check" name="file_public" type="checkbox" id="file_public" value="1"<?php is_checked("1", $file_public) ?>>
                        <label for="file_public"><?php echo $BL['be_ftptakeover_public'] ?></label>
                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="form-field">
                        <input class="check" name="file_granted" type="checkbox" id="file_granted" value="1"<?php is_checked("1", $file_granted) ?>>
                        <label for="file_granted"><?php echo $BL['be_granted_download'] ?></label>
                    </div>
                    <div class="form-field">
                        <input class="check" name="file_gallerydownload" type="checkbox" id="file_gallerydownload" value="1"<?php is_checked(1, $file_gallerydownload) ?>>
                        <label for="file_gallerydownload"><?php echo $BL['be_gallerydownload'] ?></label>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer d-flex flex-wrap gap-2 justify-content-end">
        <input name="file_aktion" type="hidden" id="file_aktion" value="1">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php

            if(ini_get('post_max_size')) {
                $post_max_size = return_bytes(ini_get('post_max_size'));
                if($post_max_size < $phpwcms['file_maxsize']) {
                    $phpwcms['file_maxsize'] = $post_max_size;
                }
            } else {
                $post_max_size = $phpwcms['file_maxsize'];
            }
            if(ini_get('upload_max_filesize')) {
                $upload_max_filesize = return_bytes(ini_get('upload_max_filesize'));
                if($upload_max_filesize < $phpwcms['file_maxsize']) {
                    $phpwcms['file_maxsize'] = $upload_max_filesize;
                }
            } else {
                $upload_max_filesize = $phpwcms['file_maxsize'];
            }

            echo min($post_max_size, $upload_max_filesize, $phpwcms['file_maxsize']);

        ?>">
        <input type="button" class="btn btn-outline theme-secondary btn-sm" value="<?php echo $BL['be_func_struct_close'] ?>" onclick="document.location.href='phpwcms.php?do=files&amp;f=0'">
        <input name="Submit" type="submit" class="btn theme-primary btn-solid btn-styled btn-sm" value="<?php echo $BL['be_fprivup_button'] ?>">
    </div>
</div>
</form>
<script type="text/javascript">

$(function(){

    $("#file_tags_autosuggest").autoSuggest('<?php echo PHPWCMS_URL ?>include/inc_act/ajax_connector.php', {
        selectedItemProp: "cat_name",
        selectedValuesProp: 'cat_name',
        searchObjProps: "cat_name",
        queryParam: 'value',
        extraParams: '&method=json&action=category',
        startText: '',
        preFill: $("#file_tags").val(),
        neverSubmit: true,
        asHtmlID: 'keyword-autosuggest'
    });

    $('#uploadfile').submit(function(){
        $("#file_tags").val($('#as-values-keyword-autosuggest').val());
    });

<?php   if(count($phpwcms['allowed_lang']) > 1): ?>

    var tab_content = $('div.finfo');
    var tabs        = $('.file-lang-tabs .nav-link');

    tabs.click(function(event) {
        event.preventDefault();

        var $_this = $(this);

        if($_this.hasClass('active')) {
            return false;
        }

        tab_content.addClass('d-none');
        tabs.removeClass('active').removeAttr('aria-current');

        $_this.addClass('active').attr('aria-current', 'page');
        $('div.finfo' + $_this.attr('rel')).removeClass('d-none');

    });

<?php   endif; ?>

});

</script>
