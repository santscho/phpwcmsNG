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

// Overwrite some defaults
if(!isset($phpwcms['set_article_active'])) {
    $phpwcms['set_article_active'] = 1;
}
if(!isset($phpwcms['set_category_active'])) {
    $phpwcms['set_category_active'] = 1;
}
if(!isset($phpwcms['set_file_active'])) {
    $phpwcms['set_file_active'] = 1;
}
if(!isset($phpwcms['set_news_active'])) {
    $phpwcms['set_news_active'] = 1;
}
if(isset($phpwcms['set_sociallink']) && is_array($phpwcms['set_sociallink'])) {
    $phpwcms['set_sociallink'] = array_merge(array('article' => false, 'articlecat' => false, 'news' => false, 'shop' => false, 'render' => true), $phpwcms['set_sociallink']);
} else {
    $phpwcms['set_sociallink'] = array('article' => false, 'articlecat' => false, 'news' => false, 'shop' => false, 'render' => true);
}

// general functions used in backend only
function update_cache() {
    // used to update cache setting all current cache entries
    // will be forced to update cache but will not be deleted
    //$sql = "UPDATE ".DB_PREPEND."phpwcms_cache SET cache_timeout='0'";
    //_dbQuery($sql, 'UPDATE');
}

function set_chat_focus($do, $p) { //set_chat_focus("chat", 1)
    if($do === "chat" && $p == 1) {
        echo "<script type=\"text/javascript\"> ";
        echo "document.sendchatmessage.chatmsg.focus(); document.sendchatmessage.chatmsg.value=get_cookie('chatstring');";
        echo "timer = chat_reload(20000); function chat_reload(zeit) {";
        echo "timer=setTimeout(\"write_cookie(1);self.location.href='phpwcms.php'+'?" . CSRF_GET_TOKEN . "&do=chat&p=1&l=" . urlencode($GLOBALS['chatlist'] ?? 0) . "'\", zeit);";
        echo "return timer;\n} function restart_reload(timer) {";
        echo "if(timer != null) { clearTimeout(timer); timer=null; timer = chat_reload(20000); } return timer;} </script>";
    }
}

function forward_to($to, $link, $time=2500) { //Javascript forwarding
    if($to) {
        echo "<script type=\"text/javascript\"> setTimeout(\"document.location.href='".$link."'\", ".(intval($time))."); </script>";
    }
}

/**
 * Inline SVG icon for the backend screen mode switcher
 */
function backend_theme_icon($theme) {

    $icons = [
        'auto' => '<circle cx="8" cy="8" r="6.25" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M8 1.75a6.25 6.25 0 0 1 0 12.5Z"/>',
        'light' => '<circle cx="8" cy="8" r="3.5"/><path d="M8 .75v1.9M8 13.35v1.9M.75 8h1.9M13.35 8h1.9M2.75 2.75l1.35 1.35M11.9 11.9l1.35 1.35M13.25 2.75L11.9 4.1M4.1 11.9l-1.35 1.35" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
        'dark' => '<path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.787.787 0 0 1 .81 1.166A8.2 8.2 0 1 1 5.922.222.77.77 0 0 1 6 .278"/>',
    ];

    if (!isset($icons[$theme])) {
        return '';
    }

    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">' . $icons[$theme] . '</svg>';
}

function subnavtext($text, $link, $is, $should, $getback=1, $js='') {
    //generate subnavigation based on text
    $nav_active = (string) ($is ?: '0') === (string) ($should ?: '0');
    $sn = '<a href="' . $link . '" class="list-group-item list-group-item-action' . ($nav_active ? ' active' : '') . '"';
    $sn .= ($nav_active ? ' aria-current="page"' : '') . ' ' . $js . '>' . $text . '</a>';
    $sn .= "\n";
    if(!$getback) {
        return $sn;
    } else {
        echo $sn;
    }

    return null;
}

function subnavtextext($text, $link, $target='_blank', $getback=1) {
    //generate subnavigation based on text and links to new page
    $sn  = '<a href="' . $link . '" target="' . $target . '" class="list-group-item list-group-item-action">' . $text . '</a>';
    $sn .= "\n";

    if($getback) {
        echo $sn;
    }
    return $sn;
}

function subnavback($text, $link, $h_before=0, $h_after=0) {
    $id = "subbackid".generic_string(5);
    $sn  = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
    if(intval($h_before)) {
        $sn .= "<tr><td colspan=\"2\"><img src=\"img/leer.gif\" width=\"1\" height=\"" . intval($h_before) . "\" alt=\"\" /></td></tr>";
    }
    $sn .= "<tr>";
    $sn .= "<td><img name=\"".$id."\" src=\"img/subnav/subnav_back_0.gif\" width=\"9\" height=\"9\" border=\"0\" alt=\"\" /></td>";
    $sn .= "<td class=\"subnavinactive\">&nbsp;<a href=\"".$link."\" onmouseover=\"".$id.".src='img/subnav/subnav_back_1.gif'\" ";
    $sn .= "onmouseout=\"".$id.".src='img/subnav/subnav_back_0.gif'\"><strong>".$text."</strong></a></td>";
    $sn .= "</tr>";
    if(intval($h_after)) {
        $sn .= "<tr><td colspan=\"2\"><img src=\"img/leer.gif\" width=\"1\" height=\"".intval($h_after)."\" alt=\"\" /></td></tr>";
    }
    $sn .= "</table>";
    echo $sn;
}



/**
 * check_image_extension function.
 *
 * @access public
 * @param mixed $file
 * @param string $filename (default: '')
 * @param mixed &$file_image_size
 * @return string
 */
function check_image_extension($file, $filename, &$file_image_size) {

    $result = false;
    if(empty($file_image_size[2])) {
        $file_image_size = @getimagesize($file);
    }

    if(!empty($file_image_size[2])) {

        if($filename === '') {
            $filename = basename($file);
        }

        switch($file_image_size[2]) {
            case  1: $result = 'gif'; break;
            case  2: $result = 'jpg'; break;
            case  3: $result = 'png'; break;
            case  4: $result = 'swf'; break;
            case  5: $result = 'psd'; break;
            case  6: $result = 'bmp'; break;
            case  7: $result = 'tif'; break; //(intel byte order),
            case  8: $result = 'tif'; break; //(motorola byte order),
            case  9: $result = 'jpc'; break;
            case 10: $result = 'jp2'; break;
            case 11: $result = 'jpx'; break;
            case 12: $result = 'jb2'; break;

            case 13: // there is a problem in some cases swf -> swc ? why ever!
                     // do an additional extension check and compare against swf
                     $result = strtolower(which_ext($filename)) === 'swf' ? 'swf' : 'swc';
                     break;

            case 14: $result = 'iff'; break;

            case 15: // there seems to be a problem with getimagesize and Quicktime VR
                     // mov -> wmbf ? why ever!
                     // do an additional extension check and compare against mov

                     $result = strtolower(which_ext($filename)) === 'mov' ? 'mov' : 'wbmp';
                     break;

            case 16: $result = 'xbm'; break;
            case 18:
            case 32: $result = 'webp'; break;
        }
    }

    return $result;
}

function getParentStructArray($structID) {

    $result = _dbQuery("SELECT * FROM ".DB_PREPEND."phpwcms_articlecat WHERE acat_id=".intval($structID)." LIMIT 1");

    if(isset($result[0]['acat_id'])) {
        return $result[0];
    }

    return $GLOBALS['indexpage'];
}

/*
 * Get sort value for an article based on category
 */
function getArticleSortValue($cat_id=0) {

    // Count all articles within the given structure ID
    $sql  = "SELECT article_id, article_sort FROM ".DB_PREPEND."phpwcms_article ";
    $sql .= "WHERE article_cid=".intval($cat_id)." ORDER BY article_sort DESC";

    $result = _dbQuery($sql);

    if(isset($result[0]['article_id'])) {
        $count = count($result); // number of articles
        $max_sort = $result[0]['article_sort']; // get the highest article sort value
    } else {
        $count = 0;
        $max_sort = 0;
    }

    $count = ($count + 1) * 10;

    return ($max_sort < $count) ? $count : $max_sort + 10;
}

/*
 * Make a re-sort for given structure ID and
 * return new sorted articles as array
 */
function getArticleReSorted($cat_id, $ordered_by) {

    // get all articles including deleted and update sorting
    // in correct sort order by adding sort + 10
    $sort               = 10;
    $sort_multiply_by   = 1;
    $count_article      = 0;
    $ao                 = get_order_sort($ordered_by);

    $sql  = "SELECT article_id, article_cid, article_title, article_aktiv, article_uid, ";
    $sql .= "date_format(article_tstamp, '%Y-%m-%d %H:%i:%s') AS article_date, article_sort, article_deleted, article_tstamp ";
    $sql .= "FROM ".DB_PREPEND."phpwcms_article ";
    $sql .= "WHERE article_cid='".$cat_id."' ORDER BY ".$ao[2];

    $result = _dbQuery($sql);

    if(isset($result[0]['article_id'])) {

        // now check if it's sorted manually and DESC
        // then sort has to be lowerd by -10
        if($ao[0] == 0 && $ao[1] == 1) {
            $sort = (count($result) + 1) * 10;
            $sort_multiply_by = -1;
        }

        // take all entries and build new array with it
        foreach($result as $row) {

            // SQL update query with new sort value
            $update_sql  = "UPDATE ".DB_PREPEND."phpwcms_article SET ";
            $update_sql .= "article_sort=".$sort.", ";
            $update_sql .= "article_tstamp='".$row['article_tstamp']."' ";
            $update_sql .= "WHERE article_id=".$row['article_id']." LIMIT 1";
            _dbQuery($update_sql, 'UPDATE');

            // add entry to the returning array only for article_deleted=0
            // drops all deleted articles or articles having another status
            if(!$row['article_deleted']) {

                $article[$count_article]                    = $row;
                $article[$count_article]['article_sort']    = $sort;

                // count up for article array index
                $count_article++;

            }

            // count sort up by 10
            $sort = $sort + (10 * $sort_multiply_by);

        }
    }

    return $article;
}

function phpwcmsversionCheck() {

    global $phpwcms;
    global $BL;

    if(empty($phpwcms['version_check'])) {
        $version = '<div class="card">';
        $version .= '<div class="card-header"><h2 class="h6 mb-0">' . $BL['Version_information'] . '</h2></div>';
        $version .= '<div class="card-body">';
        $version .= '<p class="alert theme-info mb-0">';
        $version .= sprintf($BL['Current_version_info'], PHPWCMS_VERSION . ' ('. PHPWCMS_RELEASE_DATE. ', r' . PHPWCMS_REVISION . ')');
        $version .= '</p>';
        $version .= '</div></div>';

        return $version;
    }

    if(!empty($_SESSION['phpwcms_version_check'])) {
        return $_SESSION['phpwcms_version_check'];
    }

    // Check for new version

    $errno          = 0;
    $errstr         = '';
    $version_info   = '';
    $get_info       = false;
    $has_sockets    = false;

    $identify  = '?version='.rawurlencode(PHPWCMS_VERSION.' '.str_replace('/', '', PHPWCMS_RELEASE_DATE));
    $identify .= '&hash='.md5($_SERVER['REQUEST_URI']);
    $identify .= '&url='.rawurlencode(PHPWCMS_URL);
    $identify .= '&revision='.rawurlencode(PHPWCMS_REVISION);

    if(function_exists('fsockopen')) {
        $has_sockets = true;
        if($fsock = @fsockopen('ssl://www.phpwcms.org', 443, $errno, $errstr, 10)) {

            @fputs($fsock, "GET /versioncheck/".$identify." HTTP/1.1\r\n");
            @fputs($fsock, "Host: www.phpwcms.org\r\n");
            @fputs($fsock, "Connection: close\r\n\r\n");

            $response_header = '';
            while (!@feof($fsock)) {
                if($get_info) {
                    $version_info .= @fread($fsock, 1024);
                } else {
                    $line = @fgets($fsock, 1024);
                    $response_header .= $line;
                    if ($line === "\r\n") {
                        $get_info = true;
                    }
                }
            }
            @fclose($fsock);

            if (preg_match('/^HTTP\/1\.[01]\s+(\d+)/i', $response_header, $status_match)) {
                $status_code = intval($status_match[1]);
                if ($status_code !== 200) {
                    $errstr = 'HTTP Error ' . $status_code;
                    $get_info = false;
                }
            }
        }
    }

    if(!$get_info && function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
        $has_sockets = true;
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 10,
                'header' => 'User-Agent: phpwcms/' . PHPWCMS_VERSION . "\r\n"
            ]
        ]);
        $version_info = @file_get_contents('https://www.phpwcms.org/versioncheck/'.$identify, false, $context);
        if ($version_info !== false) {
            $get_info = true;
            // $http_response_header is deprecated as of PHP 8.5
            $last_response_headers = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? []);
            if (!empty($last_response_headers[0])) {
                if (preg_match('/^HTTP\/1\.[01]\s+(\d+)/i', $last_response_headers[0], $status_match)) {
                    $status_code = (int)$status_match[1];
                    if ($status_code !== 200) {
                        $errstr = 'HTTP Error ' . $status_code;
                        $get_info = false;
                    }
                }
            }
        } else {
            $errstr = 'file_get_contents failed';
        }
    }

    if($get_info && preg_match('/.*BEGIN -->(.+)<!-- END.*/s', $version_info, $match)) {

        $version_info       = explode(LF, $match[1]);
        $latest_version     = trim($version_info[0]);
        $latest_revdate     = trim($version_info[1]);
        $latest_revision    = (int)trim($version_info[2]);
        $latest_time        = strtotime($latest_revdate.' 00:00:00');
        $version_time       = strtotime(PHPWCMS_RELEASE_DATE.' 00:00:00');

        if($latest_revision <= $phpwcms['revision'] || $latest_time <= $version_time)   {
            $version_info  = '<p class="alert theme-success">' . $BL['Version_up_to_date'] . '</p>';
        } else {
            $version_info  = '<p class="alert theme-danger">' . $BL['Version_not_up_to_date'] . '</p>';
        }

        if($latest_time > $version_time) {
            $mark_date_prefix = '<span class="badge theme-danger">';
            $mark_date_suffix = '</span>';
        } elseif($latest_time < $version_time) {
            $mark_date_prefix = '<span class="badge theme-success">';
            $mark_date_suffix = '</span>';
        } else {
            $mark_date_prefix = '';
            $mark_date_suffix = '';
        }

        if($latest_revision > $phpwcms['revision']) {
            $mark_rev_prefix = '<span class="badge theme-danger">';
            $mark_rev_suffix = '</span>';
        } elseif($latest_revision < $phpwcms['revision']) {
            $mark_rev_prefix = '<span class="badge theme-success">';
            $mark_rev_suffix = '</span>';
        } else {
            $mark_rev_prefix = '';
            $mark_rev_suffix = '';
        }

        $version_info .= '<p class="mb-0">' . sprintf($BL['Latest_version_info'], $latest_version.' ('.$latest_revdate.', r'.$latest_revision.')'). '<br />';
        $version_info .= sprintf($BL['Current_version_info'], $mark_rev_prefix.PHPWCMS_VERSION.$mark_rev_suffix.' ('.$mark_date_prefix.PHPWCMS_RELEASE_DATE.$mark_date_suffix.', r'.PHPWCMS_REVISION.')') . '</p>';

    } elseif ($errstr) {
        $version_info = '<p class="alert theme-warning mb-0">' . sprintf($BL['Connect_socket_error'], $errstr) . '</p>';
    } elseif (!$has_sockets) {
        $version_info = '<p class="alert theme-warning mb-0">' . $BL['Socket_functions_disabled'] . '</p>';
    } else {
        $version_info = '<p class="alert theme-warning mb-0">' . sprintf($BL['Connect_socket_error'], 'Invalid response format') . '</p>';
    }

    $_SESSION['phpwcms_version_check'] = '<div class="card"><div class="card-header"><h2 class="h6 mb-0">'.$BL['Version_information'].'</h2></div><div class="card-body">'.$version_info.'</div></div>';

    return $_SESSION['phpwcms_version_check'];
}


function createOptionTransferSelectList($id, $leftData, $rightData, $option = array()) {
    // used to create

    global $BL;

    $id_left                = $id.'_left';
    $id_right               = $id.'_right';
    $id_left_box            = $id_left.'list';
    $id_right_box           = $id_right.'list';
    $option_object          = generic_string(4, 4);

    $table                  = '';

    $option['rows']         = empty($option['rows']) || !intval($option['rows']) ? 5 : $option['rows'];
    $option['delimeter']    = empty($option['delimeter']) ? ',' : $option['delimeter'];
    $option['encode']       = !((isset($option['encode']) && $option['encode'] === false));
    $option['style']        = empty($option['style']) ? '' : ' style="'.$option['style'].'"';
    $option['class']        = empty($option['class']) ? ' class="#SIDE#"' : ' class="#SIDE# '.$option['class'].'"';
    $option['formname']     = empty($option['formname']) ? 'document.forms[0]' : 'document.getElementById(\''.$option['formname'].'\')';


    $GLOBALS['BE']['HEADER']['optionselect.js'] = getJavaScriptSourceLink('include/inc_js/optionselect.js');

    $table .= '<table border="0" cellspacing="0" cellpadding="0">'.LF.'<tr>'.LF;

    // left select list
    $table .= '<td valign><select name="'.$id_left_box.'" id="'.$id_left_box.'" size="'.$option['rows'].'" multiple="multiple"';
    $table .= $option['style'].str_replace('#SIDE#', 'leftSide', $option['class']).' ondblclick="'.$option_object.'.transferRight()">'.LF;
    if(!empty($leftData) && is_array($leftData)) {
        foreach($leftData as $key => $value) {
            $table .= '     <option value="'.$key.'">'.$value.'</option>'.LF;
        }
    }   $table .= '</select></td>'.LF;

    // left <-> right buttons
    $table .= '<td'.$option['style'].$option['class'].'>';
    $table .= '<img src="img/leer.gif" alt="" width="1" height="1" />'.LF;
    $table .= '</td>'.LF;

    // right select list
    $table .= '<td><select name="'.$id_right_box.'" id="'.$id_right_box.'" size="'.$option['rows'].'" multiple="multiple"';
    $table .= $option['style'].str_replace('#SIDE#', 'rightSide', $option['class']).' ondblclick="'.$option_object.'.transferLeft()">'.LF;
    if(!empty($rightData) && is_array($rightData)) {
        foreach($rightData as $key => $value) {
            $table .= '     <option value="'.$key.'">'.$value.'</option>'.LF;
        }
    }
    $table .= '</select></td>'.LF;
    $table .= '</tr>'.LF;

    $table .= '<tr>'.LF.'<td>';
    $table .= '<img src="img/button/list_pos_up.gif" alt="" border="0" onclick="moveOptionUp('.$option['formname'].'.'.$id_left_box.');'.$option_object.'.update();">';
    $table .= '<img src="img/leer.gif" width="2" height="2" alt="" />';
    $table .= '<img src="img/button/list_pos_down.gif" alt="" border="0" onclick="moveOptionDown('.$option['formname'].'.'.$id_left_box.');'.$option_object.'.update();">';
    $table .= '<img src="img/leer.gif" width="4" height="4" alt="" />';
    $table .= '<img src="img/button/put_right_a.gif" alt="Move selected to right" border="0" onclick="'.$option_object.'.transferRight();" />';
    $table .= '<img src="img/leer.gif" width="2" height="2" alt="" />';
    $table .= '<img src="img/button/put_right.gif" alt="Move all to right" border="0" onclick="'.$option_object.'.transferAllRight();"/>';
    $table .= '</td>'.LF;

    $table .= '<td><img src="img/leer.gif" alt="" width="1" height="1" /></td>'.LF;

    $table .= '<td>';
    $table .= '<img src="img/button/put_left_a.gif" alt="Move selected to left" border="0" onclick="'.$option_object.'.transferLeft();" />';
    $table .= '<img src="img/leer.gif" width="2" height="2" alt="" />';
    $table .= '<img src="img/button/put_left.gif" alt="Move all to left" border="0" onclick="'.$option_object.'.transferAllLeft();" />';
    $table .= '</td>'.LF;

    $table .= '</tr>'.LF.'</table>'.LF;

    $table .= '<input type="hidden" name="'.$id_left.'" id="'.$id_left.'" value="" />';
    $table .= '<input type="hidden" name="'.$id_right.'" id="'.$id_right.'" value="" />';

    $table .= '<script>'.LF;
    $table .= SCRIPT_CDATA_START.LF;
    $table .= ' var '.$option_object.' = new OptionTransfer("'.$id_left_box.'","'.$id_right_box.'");'.LF;
    $table .= ' '.$option_object.'.setAutoSort(false);'.LF;
    $table .= ' '.$option_object.'.setDelimiter("'.$option['delimeter'].'");'.LF;
    $table .= ' '.$option_object.'.saveNewLeftOptions("'.$id_left.'");'.LF;
    $table .= ' '.$option_object.'.saveNewRightOptions("'.$id_right.'");'.LF;
    $table .= ' '.$option_object.'.init('.$option['formname'].');'.LF;
    $table .= LF.SCRIPT_CDATA_END.LF;
    $table .= '</script>'.LF;

    return $table;
}

function countNewsletterRecipients($target) {
    // try to count all recipients for special newsletter
    $recipients = _dbQuery('SELECT * FROM '.DB_PREPEND.'phpwcms_address WHERE address_verified=1');
    $counter    = 0;
    $check      = !((empty($target) || !is_array($target) || !count($target)));
    foreach($recipients as $value) {
        if(empty($value['address_subscription'])) {
            $counter++;
        } elseif($check) {
            $value['address_subscription'] = @unserialize($value['address_subscription'], ['allowed_classes' => false]);
            if(is_array($value['address_subscription']) && count($value['address_subscription'])) {
                foreach($value['address_subscription'] as $subscr) {
                    if(in_array(intval($subscr), $target)) {
                        $counter++;
                        break;
                    }
                }
            } else {
                $counter++;
            }
        }
    }
    return $counter;
}

function getContentPartOptionTag($value='', $text='', $selected='', $module='') {

    $result = '';


    // neccessary plugin check
    if($value == 30) {

        if(!isset($GLOBALS['temp_count'])) {
            $GLOBALS['temp_count'] = 0;
        }

        foreach($GLOBALS['phpwcms']['modules'] as $module_value) {

            if($module_value['cntp'] && file_exists($module_value['path'].'inc/cnt.list.php')) {
                $result .= '<option value="'.$value.':'.$module_value['name'].'"';
                if($value == $selected && $module_value['name'] == $module) {
                    $result .= ' selected="selected"';
                    $GLOBALS['contentpart_temp_selected'] = $GLOBALS['temp_count'];
                }
                $result .= '>'.$text;
                $result .= ': '.$GLOBALS['BL']['modules'][ $module_value['name'] ]['listing_title'];
                $result .= '</option>'.LF;
                $GLOBALS['temp_count']++;
            }

        }

    } else {

        $result .= '<option value="'.$value.'"';
        if($value == $selected) {
            $result .= ' selected="selected"';
            $GLOBALS['contentpart_temp_selected'] = $GLOBALS['temp_count'];
        }
        $result .= '>'.$text.'</option>'.LF;

    }

    return $result;

}

function isContentPartSet($value='') {

    $value = explode(':', $value);
    $value[0] = intval($value[0]);

    // check for set module
    if(empty($value[1])) {

        $value[1] = 1;

    } elseif(       isset($GLOBALS['phpwcms']['modules'][$value[1]])
                &&  $GLOBALS['phpwcms']['modules'][$value[1]]['cntp']
                &&  file_exists($GLOBALS['phpwcms']['modules'][$value[1]]['path'].'inc/cnt.list.php')) {

        $value[1] = 1;

    } else {

        $value[1] = 0;

    }

    // check if content part ID exists
    if(isset($GLOBALS['wcs_content_type'][ $value[0] ]) && $value[1]) {

        return true;

    } else {

        return false;

    }

}

/*
 *  Show System Status Message
 */
/*
 *  Inline SVG icons for backend list actions
 */
/*
 * Backend icons: every icon is an inline 16x16 SVG from Bootstrap Icons
 * (https://icons.getbootstrap.com, MIT licence, v1.13.2) drawn in currentColor.
 */
function backend_icon($name) {

    $icons = array(
        // type markers
        'grid-1x2-fill'         => '<path d="M0 1a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm9 0a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1zm0 9a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1z"/>',
        'folder-fill'           => '<path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3m-8.322.12q.322-.119.684-.12h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981z"/>',
        'journal-bookmark-fill' => '<path fill-rule="evenodd" d="M6 1h6v7a.5.5 0 0 1-.757.429L9 7.083 6.757 8.43A.5.5 0 0 1 6 8z"/><path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/><path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>',
        'postcard-fill'         => '<path d="M11 8h2V6h-2z"/><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm8.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0zM2 5.5a.5.5 0 0 0 .5.5H6a.5.5 0 0 0 0-1H2.5a.5.5 0 0 0-.5.5M2.5 7a.5.5 0 0 0 0 1H6a.5.5 0 0 0 0-1zM2 9.5a.5.5 0 0 0 .5.5H6a.5.5 0 0 0 0-1H2.5a.5.5 0 0 0-.5.5m8-4v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5"/>',
        // row actions
        'pen'                   => '<path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"/>',
        'scissors'              => '<path d="M3.5 3.5c-.614-.884-.074-1.962.858-2.5L8 7.226 11.642 1c.932.538 1.472 1.616.858 2.5L8.81 8.61l1.556 2.661a2.5 2.5 0 1 1-.794.637L8 9.73l-1.572 2.177a2.5 2.5 0 1 1-.794-.637L7.19 8.61zm2.5 10a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0m7 0a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>',
        'copy'                  => '<path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>',
        'clipboard-plus'        => '<path fill-rule="evenodd" d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7"/><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>',
        'trash'                 => '<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>',
        'restore'               => '<path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>',
        // visibility switches
        'eye'                   => '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>',
        'eye-slash'             => '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>',
        // user marker
        'person-fill'           => '<path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>',
        // tree toggles and add
        'chevron-right'         => '<path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>',
        'arrow-up-right'        => '<path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0z"/>',
        'chevron-up'            => '<path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>',
        'chevron-down'          => '<path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>',
        'plus'                  => '<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>',
        // file manager
        'upload'                => '<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/> <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>',
        'download'              => '<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/> <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>',
        'folder-plus'           => '<path d="m.5 3 .04.87a2 2 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2m5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19q-.362.002-.683.12L1.5 2.98a1 1 0 0 1 1-.98z"/> <path d="M13.5 9a.5.5 0 0 1 .5.5V11h1.5a.5.5 0 1 1 0 1H14v1.5a.5.5 0 1 1-1 0V12h-1.5a.5.5 0 0 1 0-1H13V9.5a.5.5 0 0 1 .5-.5"/>',
        'toggle-on'             => '<path d="M5 3a5 5 0 0 0 0 10h6a5 5 0 0 0 0-10zm6 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8"/>',
        'toggle-off'            => '<path d="M11 4a4 4 0 0 1 0 8H8a5 5 0 0 0 2-4 5 5 0 0 0-2-4zm-6 8a4 4 0 1 1 0-8 4 4 0 0 1 0 8M0 8a5 5 0 0 0 5 5h6a5 5 0 0 0 0-10H5a5 5 0 0 0-5 5"/>',
        'globe'                 => '<path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>',
        'lock'                  => '<path fill-rule="evenodd" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4M4.5 7A1.5 1.5 0 0 0 3 8.5v5A1.5 1.5 0 0 0 4.5 15h7a1.5 1.5 0 0 0 1.5-1.5v-5A1.5 1.5 0 0 0 11.5 7zM8 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>',
        'question-circle'       => '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94"/>',
        'chevron-bar-expand'    => '<path fill-rule="evenodd" d="M3.646 10.146a.5.5 0 0 1 .708 0L8 13.793l3.646-3.647a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 0-.708m0-4.292a.5.5 0 0 0 .708 0L8 2.207l3.646 3.647a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 0 0 0 .708M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8"/>',
        'chevron-bar-contract'  => '<path fill-rule="evenodd" d="M3.646 14.854a.5.5 0 0 0 .708 0L8 11.207l3.646 3.647a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 0 0 0 .708m0-13.708a.5.5 0 0 1 .708 0L8 4.793l3.646-3.647a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 0-.708M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8"/>',
        'image'                 => '<path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/> <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/>',
        'image-fill'            => '<path d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2zm1 9v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062zm5-6.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>',
        'images'                => '<path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/> <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>',
        // file types
        'file-earmark'          => '<path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>',
        'file-earmark-image'    => '<path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/> <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>',
        'file-earmark-pdf'      => '<path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/> <path d="M4.603 14.087a.8.8 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.7 7.7 0 0 1 1.482-.645 20 20 0 0 0 1.062-2.227 7.3 7.3 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a11 11 0 0 0 .98 1.686 5.8 5.8 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.86.86 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.7 5.7 0 0 1-.911-.95 11.7 11.7 0 0 0-1.997.406 11.3 11.3 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.8.8 0 0 1-.58.029m1.379-1.901q-.25.115-.459.238c-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361q.016.032.026.044l.035-.012c.137-.056.355-.235.635-.572a8 8 0 0 0 .45-.606m1.64-1.33a13 13 0 0 1 1.01-.193 12 12 0 0 1-.51-.858 21 21 0 0 1-.5 1.05zm2.446.45q.226.245.435.41c.24.19.407.253.498.256a.1.1 0 0 0 .07-.015.3.3 0 0 0 .094-.125.44.44 0 0 0 .059-.2.1.1 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a4 4 0 0 0-.612-.053zM8.078 7.8a7 7 0 0 0 .2-.828q.046-.282.038-.465a.6.6 0 0 0-.032-.198.5.5 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822q.036.167.09.346z"/>',
        'file-earmark-word'     => '<path d="M5.485 6.879a.5.5 0 1 0-.97.242l1.5 6a.5.5 0 0 0 .967.01L8 9.402l1.018 3.73a.5.5 0 0 0 .967-.01l1.5-6a.5.5 0 0 0-.97-.242l-1.036 4.144-.997-3.655a.5.5 0 0 0-.964 0l-.997 3.655L5.485 6.88z"/> <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>',
        'file-earmark-excel'    => '<path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/> <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>',
        'file-earmark-zip'      => '<path d="M5 7.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v.938l.4 1.599a1 1 0 0 1-.416 1.074l-.93.62a1 1 0 0 1-1.11 0l-.929-.62a1 1 0 0 1-.415-1.074L5 8.438zm2 0H6v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.929-.62-.4-1.598A1 1 0 0 1 7 8.438z"/> <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1h-2v1h-1v1h1v1h-1v1h1v1H6V5H5V4h1V3H5V2h1V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>',
        'file-earmark-text'     => '<path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/> <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>',
        'file-earmark-music'    => '<path d="M11 6.64a1 1 0 0 0-1.243-.97l-1 .25A1 1 0 0 0 8 6.89v4.306A2.6 2.6 0 0 0 7 11c-.5 0-.974.134-1.338.377-.36.24-.662.628-.662 1.123s.301.883.662 1.123c.364.243.839.377 1.338.377s.974-.134 1.338-.377c.36-.24.662-.628.662-1.123V8.89l2-.5z"/> <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>',
        'file-earmark-play'     => '<path d="M6 6.883v4.234a.5.5 0 0 0 .757.429l3.528-2.117a.5.5 0 0 0 0-.858L6.757 6.454a.5.5 0 0 0-.757.43z"/> <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>',
    );

    $path = $icons[$name] ?? '';

    if ($path === '') {
        return '';
    }

    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">' . $path . '</svg>';

}

/*
 *  File type icon for a file extension, used by the file manager
 */
function backend_file_icon($ext) {

    $types = array(
        'image' => array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'psd', 'ai', 'ps', 'eps', 'svg', 'ico', 'avif', 'heic', 'pix'),
        'pdf'   => array('pdf'),
        'word'  => array('doc', 'dot', 'docx', 'odt', 'rtf'),
        'excel' => array('xls', 'xlsx', 'ods', 'csv', 'cvs'),
        'zip'   => array('zip', 'tar', 'gz', 'tgz', 'gzip', 'rar', '7z', 'sit', 'sea'),
        'text'  => array('txt', 'htm', 'html', 'xml', 'json', 'php', 'css', 'js', 'md', 'ini', 'yml', 'yaml'),
        'music' => array('mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'aif', 'aiff'),
        'play'  => array('mp4', 'm4v', 'mov', 'avi', 'wmv', 'flv', 'webm', 'mpg', 'mpeg', 'swf'),
    );

    $ext = strtolower(trim((string) $ext));

    foreach($types as $icon => $extensions) {
        if(in_array($ext, $extensions, true)) {
            return 'file-earmark-' . $icon;
        }
    }

    return 'file-earmark';

}

/*
 *  Icon button: $theme is the Bootstrap colour token, $state one of
 *  normal, active (pressed) or disabled
 */
function backend_action($href, $icon, $label, $state = 'normal', $onclick = '', $theme = 'secondary') {

    $label_attr = html($label);
    $class      = 'btn btn-icon btn-outline theme-' . $theme . ' btn-sm';
    $inner      = backend_icon($icon);

    switch($state) {
        case 'active':
            $class = 'btn btn-icon theme-' . $theme . ' btn-solid btn-sm';
            break;
        case 'disabled':
            // a disabled button ignores the pointer, so the tooltip sits on a wrapper
            return '<span class="d-inline-flex" data-bs-toggle="tooltip" title="' . $label_attr . '"><span class="' . $class . ' disabled" aria-disabled="true">' . $inner . '</span></span>';
    }

    return '<a class="' . $class . '" href="' . $href . '" data-bs-toggle="tooltip" title="' . $label_attr . '" aria-label="' . $label_attr . '"' . $onclick . '>' . $inner . '</a>';

}

/*
 *  Drag handle for sortable backend rows
 */
function backend_sort_handle($label='') {

    global $BL;

    $label = $label ?: ($BL['be_sort_drag'] ?? 'drag to reorder');

    $handle  = '<span class="backend-sort-handle d-inline-flex align-items-center justify-content-center flex-shrink-0 rounded" data-sort-handle="1" role="button"';
    $handle .= ' aria-label="' . html($label) . '" data-bs-toggle="tooltip" title="' . html($label) . '"';
    $handle .= ' style="cursor:grab;width:26px;height:26px">';
    $handle .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="24" fill="currentColor" viewBox="0 0 16 24" aria-hidden="true">';
    foreach (array(4, 9, 14, 19) as $handle_y) {
        foreach (array(4.5, 11.5) as $handle_x) {
            $handle .= '<circle cx="' . $handle_x . '" cy="' . $handle_y . '" r="1.8"/>';
        }
    }
    $handle .= '</svg></span>';

    return $handle;

}

function show_status_message($return_status=false) {
    if(empty($_SESSION['system_status']['msg'])) {
        $status = '';
    } else {
        $status_types = [
            'success' => 'success',
            'info'    => 'info',
            'help'    => 'info',
            'warning' => 'warning',
            'error'   => 'danger',
        ];
        $status_type = $status_types[ $_SESSION['system_status']['type'] ?? 'info' ] ?? 'info';
        $status  = '<div class="alert theme-' . $status_type . ' mb-5" role="alert"><div>';
        $status .= nl2br( trim( html($_SESSION['system_status']['msg']) ) ) . '</div></div>';
        $_SESSION['system_status']['msg'] = '';
    }
    if($return_status) {
        return $status;
    } else {
        echo $status;
        return NULL;
    }
}
/*
 *  Set System Status Message
 */
function set_status_message($msg='', $type='info', $replace=array()) {
    if(is_array($replace) && count($replace)) {
        foreach($replace as $key => $item) {
            $msg = str_replace('{'.strtoupper($key).'}', $item, $msg);
        }
    }
    $_SESSION['system_status']['msg']  = $msg;
    switch($type) {
        case 'success':
        case 'info':
        case 'help':
        case 'error':
        case 'warning': break;
        default: $type = 'info';
    }
    $_SESSION['system_status']['type'] = $type;
    return NULL;
}

function set_language_cookie($lang='en') {
    setcookie('phpwcmsBELang', $lang, time()+(3600*24*365), '/', getCookieDomain(), PHPWCMS_SSL, true);
}

// checks for alias and sets unique value
function proof_alias($current_id, $alias='', $mode='CATEGORY') {

    $alias      = strtolower( uri_sanitize( clean_slweg($alias) ) );
    $reserved   = array(
        'print',
        'newsdetail',
        'newspage',
        'id',
        'aid',
        'subgallery',
        'listpage',
        'page',
        'subscribe',
        'unsubscribe',
        'email',
        'u',
        's',
        'q',
        'feedimport',
        'r404',
        'phpwcms-preview',
        'dl',
        'fmp'
    );

    if($alias === '') {

        if(!empty($GLOBALS['phpwcms']['allow_empty_alias'])) {
            return '';
        } elseif($mode == 'CATEGORY' && isset($_POST["acat_name"])) {
            $alias = $_POST["acat_name"];
        } elseif($mode == 'ARTICLE' && isset($_POST["article_title"])) {
            $alias = $_POST["article_title"];
        } elseif($mode == 'CONTENT' && ( isset($_POST["cnt_title"]) || isset($_POST["cnt_name"]) )) {
            $alias = trim($_POST["cnt_title"]) == '' ? $_POST["cnt_name"] : $_POST["cnt_title"];
        }

        $alias = strtolower( uri_sanitize( clean_slweg($alias) ) );

        if($alias === '') {
            return '';
        }
    }

    // Test against existing folders to avoid problems with rewrite
    if(PHPWCMS_ALIAS_WSLASH && str_contains($alias, '/')) {

        $root_folders = returnSubdirListAsArray(PHPWCMS_ROOT);

        if($root_folders !== false) {

            // Only check first "path" section
            $alias_sections = explode('/', $alias);
            $alias_proof = strtolower($alias_sections[0]);
            $alias_proof_suffix = '';
            $alias_proof_suffix_count = 0;

            foreach($root_folders as $key => $folder) {
                $root_folders[$key] = strtolower($folder);
            }

            while(in_array($alias_proof.$alias_proof_suffix, $root_folders)) {
                $alias_proof_suffix_count++;
                $alias_proof_suffix = '-'.$alias_proof_suffix_count;
            }

            if($alias_proof_suffix_count) {
                $alias_sections[0] .= $alias_proof_suffix;
                $alias = implode('/', $alias_sections);
            }
        }
    }

    $alias = substr($alias, 0, 230);

    if(PHPWCMS_ALIAS_WSLASH) {
        $alias = trim($alias, '/');
    }

    $alias_proof_suffix = '';
    $alias_proof_suffix_count = 0;

    // Now test against existing files to avoid problems with rewrite
    while(is_file(PHPWCMS_ROOT.'/'.$alias.$alias_proof_suffix.PHPWCMS_REWRITE_EXT)) {
        $alias_proof_suffix_count++;
        $alias_proof_suffix = '-'.$alias_proof_suffix_count;
    }

    if($alias_proof_suffix_count) {
        $alias .= $alias_proof_suffix;
    }

    // new reserved alias can be defined in $phpwcms['reserved_alias']
    if(isset($GLOBALS['phpwcms']['reserved_alias']) && is_array($GLOBALS['phpwcms']['reserved_alias']) && count($GLOBALS['phpwcms']['reserved_alias'])) {
        $reserved = array_merge($reserved, $GLOBALS['phpwcms']['reserved_alias']);
    }

    if($alias === '' || in_array($alias, $reserved) || ($alias === 'index' && $current_id !== 'index') ) {
        $alias .= $mode === 'CONTENT' ? date('_Ymd') : '-view';
    }

    $alias = trim($alias, '-');

    $where_acat     = '';
    $where_article  = '';
    $where_content  = '';
    $current_sql_id = $current_id === 'index' ? 0 : (int) $current_id;

    switch($mode) {
        case 'CATEGORY':    $where_acat     = 'acat_id != '.$current_sql_id.' AND ';    break;
        case 'ARTICLE':     $where_article  = 'article_id != '.$current_sql_id.' AND '; break;
        case 'CONTENT':     $where_content  = 'cnt_id != '.$current_sql_id.' AND ';     break;
    }

    // check alias against all structure alias
    $sql  = "SELECT COUNT(acat_id) FROM ".DB_PREPEND."phpwcms_articlecat WHERE ";
    $sql .= $where_acat;
    $sql .= "acat_alias="._dbEscape($alias);
    $acat_count = _dbQuery($sql, 'COUNT');

    // check alias against all articles
    $sql  = "SELECT COUNT(article_id) FROM ".DB_PREPEND."phpwcms_article WHERE ";
    $sql .= $where_article;
    $sql .= "article_alias="._dbEscape($alias);
    $article_count = _dbQuery($sql, 'COUNT');

    // check alias against all "sub" contents like news
    $sql  = "SELECT COUNT(cnt_id) FROM ".DB_PREPEND."phpwcms_content WHERE ";
    $sql .= $where_content;
    $sql .= "cnt_alias="._dbEscape($alias);
    $content_count = _dbQuery($sql, 'COUNT');

    if( $acat_count > 0 || $article_count > 0 || $content_count > 0 ) {

        $sql  = "SELECT acat_alias FROM ".DB_PREPEND."phpwcms_articlecat WHERE ";
        $sql .= $where_acat;
        $sql .= "acat_alias LIKE "._dbEscape($alias, true, '', '%');
        $all_acat_alias = _dbQuery($sql);

        $sql  = "SELECT article_alias FROM ".DB_PREPEND."phpwcms_article WHERE ";
        $sql .= $where_article;
        $sql .= "article_alias LIKE "._dbEscape($alias, true, '', '%');
        $all_article_alias = _dbQuery($sql);

        $sql  = "SELECT cnt_alias FROM ".DB_PREPEND."phpwcms_content WHERE ";
        $sql .= $where_content;
        $sql .= "cnt_alias LIKE "._dbEscape($alias, true, '', '%');
        $all_content_alias = _dbQuery($sql);

        $all_alias = array();
        foreach($all_acat_alias as $item) {
            $item = $item['acat_alias'];
            $all_alias[$item] = $item;
        }
        foreach($all_article_alias as $item) {
            $item = $item['article_alias'];
            $all_alias[$item] = $item;
        }
        foreach($all_content_alias as $item) {
            $item = $item['cnt_alias'];
            $all_alias[$item] = $item;
        }
        $all_alias_count = count($all_alias);
        while( isset( $all_alias[ $alias.'-'.$all_alias_count ] ) ) {
            $all_alias_count++;
        }

        if(preg_match('/\-(\d+)$/', $alias)) {
            $alias .= $all_alias_count;
        } else {
            $alias .= '-'.$all_alias_count;
        }
    }

    return $alias;
}

function _getTime($time='', $delimeter=':', $default_time='H:i:s') {

    $timeformat     = explode($delimeter, trim($default_time));
    $time           = explode($delimeter, trim($time));

    $hour           = 0;
    $minute         = 0;
    $second         = 0;

    for($x=0; $x<=2; $x++) {
        if(isset($timeformat[$x])) {
            switch(substr(trim($timeformat[$x]), 0, 1)) {

                case 'H':   if(isset($time[$x])) {
                                $hour = intval($time[$x]);
                                if($hour < 0 || $hour > 23) {
                                    $hour = 0;
                                }
                            }
                            break;

                case 'i':   if(isset($time[$x])) {
                                $minute = intval($time[$x]);
                                if($minute < 0 || $minute > 59) {
                                    $minute = 0;
                                }
                            }
                            break;

                case 's':   if(isset($time[$x])) {
                                $second = intval($time[$x]);
                                if($second < 0 || $second > 59) {
                                    $second = 0;
                                }
                            }
                            break;
            }
        }
    }

    $time = str_replace($delimeter, ':', $default_time);
    $time = str_replace('H', substr('0' . $hour, -2), $time);
    $time = str_replace('i', substr('0' . $minute, -2), $time);
    $time = str_replace('s', substr('0' . $second, -2), $time);

    return $time;
}

function _getDate($date='', $delimeter='', $default_date='') {

    global $BL;

    $delimeter      = $delimeter == '' ? $BL['default_date_delimiter'] : $delimeter;
    $default_date   = $default_date == '' ? $BL['default_date'] : $default_date;

    $dateformat     = explode($delimeter, trim($default_date));
    $date           = explode($delimeter, trim($date));

    $day            = '';
    $month          = '';
    $year           = '';

    for($x=0; $x<=2; $x++) {
        if(isset($dateformat[$x])) {
            switch(substr(strtolower(trim($dateformat[$x])), 0, 1)) {

                case 'y':   if(isset($date[$x])) {
                                $year = intval($date[$x]);
                                if($year < 0) {
                                    $year = '';
                                }
                            }
                            break;

                case 'd':   if(isset($date[$x])) {
                                $day = intval($date[$x]);
                                if($day < 1 || $day > 31) {
                                    $day = '';
                                }
                            }
                            break;

                case 'm':   if(isset($date[$x])) {
                                $month = intval($date[$x]);
                                if($month < 1 || $month > 12) {
                                    $month = '';
                                }
                            }
                            break;

            }
        }
    }

    if($year && $month && $day) {

        return substr('000' . $year, -4) . '-' . substr('0' . $month, -2) . '-' . substr('0' . $day, -2);

    } else {

        return '0000-00-00';

    }

}

function _dbSaveCategories($categories=array(), $type='', $pid=0, $seperator=',') {

    $pid    = intval($pid);
    $type   = trim($type);

    if(is_string($categories)) {
        $categories = convertStringToArray($categories, $seperator);
    }

    // delete all related categories first
    if($type && $pid) {

        $sql = 'DELETE FROM '.DB_PREPEND.'phpwcms_categories WHERE cat_pid='.$pid." AND cat_type="._dbEscape( $type );
        _dbQuery($sql, 'DELETE');

    }

    if(is_array($categories) && count($categories) && $type && $pid) {

        $data = array(
            'cat_type'          => $type,
            'cat_pid'           => $pid,
            'cat_status'        => 1,
            'cat_createdate'    => date('Y-m-d H:i:s'),
            'cat_changedate'    => date('Y-m-d H:i:s'),
            'cat_name'          => '',
            'cat_info'          => ''
        );

        foreach($categories as $value) {
            $value = trim($value);
            if($value != '') {

                $data['cat_name'] = $value;
                _dbInsert('phpwcms_categories', $data);

            }
        }
    }
}

function setItemsPerPage($default=25) {
    if( isset($_GET['showipp']) ) {
        $ipp = intval(is_numeric($_GET['showipp']) ? $_GET['showipp'] : $default);
        setcookie('phpwcmsBEItemsPerPage', $ipp, time()+157680000, '/', getCookieDomain(), PHPWCMS_SSL, true);
    } elseif(isset($_SESSION['PAGE_FILTER'])) {
        $ipp = $_SESSION['PAGE_FILTER']['IPP'];
    } elseif( isset($_COOKIE['phpwcmsBEItemsPerPage']) ) {
        $ipp = intval( $_COOKIE['phpwcmsBEItemsPerPage'] );
    } else {
        $ipp = $default;
    }

    if(!isset($_SESSION['PAGE_FILTER'])) {
        $_SESSION['PAGE_FILTER'] = array();
    }

    $_SESSION['PAGE_FILTER']['IPP'] = $ipp;

    return $ipp;
}

function getItemsPerPageMenu($base_url='', $steps=array(10,25,50,100,250,0), $separator=' ') {

    $ipp = $_SESSION['PAGE_FILTER']['IPP'] ?? setItemsPerPage();

    if(!in_array($ipp, $steps)) {
        array_unshift($steps, $ipp);
    }

    $menu = array();
    $x = 0;
    foreach($steps as $item) {

        $menu[$x]  = '<a href="'.$base_url.'&amp;showipp='.$item.'"';
        if($ipp == $item) {
            $menu[$x] .= ' class="active"';
        }
        $menu[$x] .= '>';
        $menu[$x] .= $item == 0 ? $GLOBALS['BL']['be_ftptakeover_all'] : $item;
        $menu[$x] .= '</a>';

        $x++;
    }

    return implode($separator, $menu);
}

// The date and time formats a backend user can pick, mapped to the PHP pattern
// used to render a date and to the flatpickr tokens used to edit it
function backend_date_formats() {
    return array(
        'iso'   => array('php' => 'Y-m-d H:i:s',  'flatpickr' => 'Y-m-d H:i:S',  'time_24hr' => true,  'sample' => '2026-11-15 19:07:02'),
        'swiss' => array('php' => 'd.m.Y H:i:s',  'flatpickr' => 'd.m.Y H:i:S',  'time_24hr' => true,  'sample' => '15.11.2026 19:07:02'),
        'uk'    => array('php' => 'd/m/Y H:i:s',  'flatpickr' => 'd/m/Y H:i:S',  'time_24hr' => true,  'sample' => '15/11/2026 19:07:02'),
        'us'    => array('php' => 'm/d/Y g:i:s A', 'flatpickr' => 'm/d/Y h:i:S K', 'time_24hr' => false, 'sample' => '11/15/2026 7:07:02 PM'),
    );
}

function backend_date_format($format = '') {
    $formats = backend_date_formats();
    $format  = is_string($format) ? $format : '';

    return isset($formats[$format]) ? $formats[$format] : $formats['iso'];
}

// The first day of a backend calendar week, using the value flatpickr expects:
// 1 is Monday (ISO 8601, the default), 0 is Sunday
function backend_weekstarts() {
    return array(
        1 => 'monday',
        0 => 'sunday',
    );
}

function backend_weekstart($weekstart = '') {
    $weekstarts = backend_weekstarts();
    $weekstart  = is_numeric($weekstart) ? (int) $weekstart : 1;

    return isset($weekstarts[$weekstart]) ? $weekstart : 1;
}

function initJsDatePicker() {
    $GLOBALS['BE']['HEADER']['flatpickr.css'] = '<link href="include/inc_ext/flatpickr/flatpickr.min.css" rel="stylesheet">';
    $GLOBALS['BE']['HEADER']['flatpickr.js'] = getJavaScriptSourceLink('include/inc_ext/flatpickr/flatpickr.min.js');
    $GLOBALS['BE']['HEADER']['backend.datepicker.js'] = '<script type="module" src="include/inc_js/backend.datepicker.js"></script>';
}

function initJsCalendar() {
    $GLOBALS['BE']['HEADER']['date.js'] = getJavaScriptSourceLink('include/inc_js/date.js');
    $GLOBALS['BE']['HEADER']['dynCalendar.js'] = getJavaScriptSourceLink('include/inc_js/dynCalendar.js');
}
function initMootools($mode='1.1', $more=array()) {
    switch($mode) {
        // MooTools 1.1
        case '1.1':
            $GLOBALS['BE']['HEADER']['mootools.js'] = getJavaScriptSourceLink('include/inc_js/mootools/mootools-1.1-yc.js');
            break;

        // MooTools 1.2 + More
        default:
            unset($GLOBALS['BE']['HEADER']['mootools.js']);
            $GLOBALS['BE']['HEADER']['mootools-1.2-core.js'] = getJavaScriptSourceLink('include/inc_js/mootools/mootools-1.2-core-yc.js');

            if(is_array($more) && count($more)) {
                array_unshift($more, 'Core/More');
                foreach($more as $item) {
                    $name = 'mootools-more-'.$item;
                    if(empty($GLOBALS['BE']['HEADER'][$name]) && is_file(PHPWCMS_TEMPLATE.'lib/mootools/more/'.$item.'.js')) {
                        $GLOBALS['BE']['HEADER'][$name] = getJavaScriptSourceLink(TEMPLATE_PATH.'lib/mootools/more/'.$item.'.js');
                    }
                }
            }
    }
    $GLOBALS['phpwcms']['mootools_mode'] = $mode;
}
function initMootoolsAutocompleter($mode='1.1') {
    initMootools($mode);
    $GLOBALS['BE']['HEADER']['Autocompleter.js'] = getJavaScriptSourceLink('include/inc_js/mootools/cnet/Autocompleter.js');
    $GLOBALS['BE']['HEADER']['Autocompleter.Remote.js'] = getJavaScriptSourceLink('include/inc_js/mootools/cnet/Autocompleter.Remote.js');
    $GLOBALS['BE']['HEADER']['Observer.js'] = getJavaScriptSourceLink('include/inc_js/mootools/cnet/Observer.js');
}
function initJsOptionSelect() {
    $GLOBALS['BE']['HEADER']['optionselect.js'] = getJavaScriptSourceLink('include/inc_js/optionselect.js');
}
function initJsAutocompleter() {
    initJQuery();
    $GLOBALS['BE']['HEADER']['autosuggest.js'] = getJavaScriptSourceLink('include/inc_js/jquery/jquery.autoSuggest.min.js');
    $GLOBALS['BE']['HEADER']['backend.tags.js'] = '<script type="module" src="include/inc_js/backend.tags.js"></script>';
}
function initJQuery() {
    unset($GLOBALS['BE']['HEADER']['mootools.js']);
    // add jQuery at first position and keep the key
    $GLOBALS['BE']['HEADER'] = array('jquery.js' => getJavaScriptSourceLink('include/inc_js/jquery/jquery.min.js')) + $GLOBALS['BE']['HEADER'];
}

// make phpwcms compatibility and upgrade check
function phpwcms_get_available_revisions() {
    $files = glob(PHPWCMS_ROOT.'/include/inc_lib/revision/r*.php');
    $revisions = array();
    if (is_array($files)) {
        foreach ($files as $file) {
            if (preg_match('/r(\d+)\.php$/i', $file, $matches)) {
                $revisions[] = (int)$matches[1];
            }
        }
        sort($revisions, SORT_NUMERIC);
    }
    return $revisions;
}

function phpwcms_mark_revision_checked($revision, $return_message = '') {
    $revision_str = (string)$revision;
    $GLOBALS['phpwcms']['check_r'.$revision_str] = true;
    $extra = empty($return_message) ? '' : "\n\nReturn:\n-------\n" . $return_message;
    $tmp_file = PHPWCMS_TEMP.'r'.$revision_str.'.checked.tmp';
    $result = @write_textfile($tmp_file, date('Y-m-d H:i:s').$extra);

    if (!empty($GLOBALS['db']) && function_exists('_setConfig')) {
        @_setConfig('revision_r'.$revision_str, 1, 'sys_revision');
    }

    return $result;
}

function phpwcms_revision_check($revision) {

    if (empty($revision)) {
        return false;
    }

    $target_revision = (int)$revision;
    $GLOBALS['phpwcms']['revision_error'] = '';

    // Drop the obsolete sysvalue_tstamp column before any revision marker insert.
    // The column (NOT NULL DEFAULT '0000-00-00 00:00:00') only exists on installs
    // upgraded from 1.2.x-1.4.x and makes the sysvalue INSERTs of
    // phpwcms_mark_revision_checked() fail under strict sql_mode NO_ZERO_DATE
    // (MySQL default since 5.7). It is not read anywhere in phpwcms.
    if (function_exists('_dbQuery')) {
        $result = _dbQuery("SHOW COLUMNS FROM `" . DB_PREPEND . "phpwcms_sysvalue` WHERE Field='sysvalue_tstamp'");
        if (isset($result[0]['Field'])) {
            if (!@_dbQuery("ALTER TABLE `" . DB_PREPEND . "phpwcms_sysvalue` DROP `sysvalue_tstamp`", 'ALTER')) {
                // Fail the run with a clear message: every revision marker INSERT
                // would keep failing under strict NO_ZERO_DATE until the column is gone
                $GLOBALS['phpwcms']['revision_error'] = 'Could not drop obsolete sysvalue_tstamp column';
                return false;
            }
        }
    }

    // Run every revision up to the target one. Revisions do not have to
    // call their predecessor explicitly any more, so a revision added
    // later cannot silently skip an earlier one.
    foreach (phpwcms_get_available_revisions() as $rev) {

        if ($rev > $target_revision) {
            break;
        }

        if (phpwcms_revision_check_temp($rev) === true) {
            continue;
        }

        $revision_file = PHPWCMS_ROOT.'/include/inc_lib/revision/r'.$rev.'.php';
        if (!is_file($revision_file)) {
            continue;
        }
        include_once $revision_file;

        $revision_function = 'phpwcms_revision_r'.$rev;
        if (!function_exists($revision_function) || !empty($GLOBALS['phpwcms']['check_r'.$rev])) {
            continue;
        }

        $GLOBALS['phpwcms']['revision_return'] = '';
        $exec_result = false;
        try {
            $exec_result = call_user_func($revision_function);
        } catch (Throwable $e) {
            $exec_result = false;
            $GLOBALS['phpwcms']['revision_return'] = $e->getMessage();
        }

        if ($exec_result !== false) {
            phpwcms_mark_revision_checked($rev, $GLOBALS['phpwcms']['revision_return']);
            $GLOBALS['phpwcms']['revision_success'][] = 'r'.$rev;
        } else {
            $db_err = function_exists('_dbError') ? _dbError() : '';
            $err_msg = 'Database revision update r'.$rev.' failed!';
            if (!empty($GLOBALS['phpwcms']['revision_return'])) {
                $err_msg .= ' Error: ' . $GLOBALS['phpwcms']['revision_return'];
            }
            if (!empty($db_err)) {
                $err_msg .= ' MySQL Error: ' . $db_err;
            }
            $GLOBALS['phpwcms']['revision_error'] = $err_msg;
            return false;
        }

    }

    return true;
}

// check upgrade temp file for current revision
function phpwcms_revision_check_temp($revision) {
    if(empty($revision) || !preg_match('/^\d+$/', $revision)) {
        return NULL;
    }
    $revision_str = strval($revision);
    $tmp_file = PHPWCMS_TEMP.'r'.$revision_str.'.checked.tmp';
    if (is_file($tmp_file)) {
        return true;
    }
    // Fall back to the database marker, the temp folder may have been cleaned
    if (!empty($GLOBALS['db']) && function_exists('_getConfig')) {
        $db_checked = _getConfig('revision_r'.$revision_str, false);
        if (!empty($db_checked)) {
            $GLOBALS['phpwcms']['check_r'.$revision_str] = true;
            @write_textfile($tmp_file, date('Y-m-d H:i:s')."\n\nSynced from DB sys_revision");
            return true;
        }
    }
    return false;
}

/*
 * Parse backend for language related BBCode [EN][/EN] or {EN}{/EN} unsing JavaScript
 *
 * To enable backend language parser set config in conf.inc.php
 * $phpwcms['be_lang_parse'] = 'BBCode'; // to enable parsing for [EN][/EN]
 * $phpwcms['be_lang_parse'] = 'BraceCode'; // to enable parsing for {EN}{/EN}
 * $phpwcms['be_lang_parse'] = 'i18n'; // ToDo: to enable parsing for @@Default@@
 * $phpwcms['be_lang_parse'] = false; // to disable backend language parsing
 */
function backend_language_parser() {

    global $phpwcms, $BE, $BL;


    if(!$phpwcms['be_parse_lang_process'] || empty($phpwcms['be_lang_parse'])) {

        return backend_language_replace('');

    } elseif(empty($phpwcms['allowed_lang']) || !is_array($phpwcms['allowed_lang']) || count($phpwcms['allowed_lang']) < 2) {

        return backend_language_replace('');

    } else {

        $parse_mode = strtoupper($phpwcms['be_lang_parse']);

        if(!in_array($parse_mode, array('BBCODE', 'BRACECODE'))) { // i18n later

            return backend_language_replace('');

        }

    }

    // cut main backend content innerHTML
    $html_pos1  = strpos($BE['HTML'], '<!--BE_MAIN_CONTENT_START//-->');
    $html_pos2  = strpos($BE['HTML'], '<!--BE_MAIN_CONTENT_END//-->');

    if($html_pos1 !== false && $html_pos2 !== false) {

        $html_pos1 += strlen('<!--BE_MAIN_CONTENT_START//-->');
        $html_pos2 -= 1;

    }

    $html       = trim( preg_replace('/\s+/', ' ', substr($BE['HTML'], $html_pos1, $html_pos2-$html_pos1) ) );
    $BE['HTML'] = substr($BE['HTML'], 0, $html_pos1) . substr($BE['HTML'], $html_pos2);

    // load MooTools too
    if(empty($phpwcms['mootools_mode'])) {
        initMootools();
    }

    // init language replacements
    $regexp     = array( 'search' => array(), 'replace' => array() );
    $bracket    = array('BBCODE_OPEN' => '[', 'BRACECODE_OPEN' => '{', 'BBCODE_CLOSE' => ']', 'BRACECODE_CLOSE' => '}');
    $cookie     = (empty($_COOKIE['phpwcms_be_parse_lang']) || !in_array($_COOKIE['phpwcms_be_parse_lang'], $phpwcms['allowed_lang'])) ? false : $_COOKIE['phpwcms_be_parse_lang'];

    // init menu
    $menu       = array(
        '<ul id="be_lang">',
        '<li class="be-lang-label chatlist">'.$BL['be_profile_label_lang'].':</li>',
        '<li><a href="#" class="be-disabled'.
            ($cookie === false ? ' be-active' : '').
            '" rel="disabled" title="'.
            $BL['be_profile_label_lang'].': '.$BL['be_off'].'">'.$BL['be_off'].'</a></li>'
    );

    // Header CSS section
    $BE['HEADER']['be_parse_lang']  = ' <style type="text/css">' . LF;

    // JavaScript section
    $BE['BODY_CLOSE']['hidden_main_content']  = '   <script type="text/javascript">' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= "       var be_lang_html = [];" . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= "       var cur_be_lang = 'disabled';" . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= "       be_lang_html['disabled'] = '" . trim(str_replace(array("\\", "'"), array("\\\\", "\\'"), $html)) . "';" . LF . LF;

    // build regular expression at first
    foreach($phpwcms['allowed_lang'] as $lang) {
        $regexp['search'][$lang]    = '/\\'.$bracket[$parse_mode.'_OPEN'].$lang.'\\'.$bracket[$parse_mode.'_CLOSE'].'(.*?)\\'.$bracket[$parse_mode.'_OPEN'].'\/'.$lang.'\\'.$bracket[$parse_mode.'_CLOSE'].'/is';
        $regexp['replace'][$lang]   = '';
    }
    // parse each language at second
    foreach($phpwcms['allowed_lang'] as $lang) {
        $replace        = $regexp['replace'];
        $replace[$lang] = '$1';
        $lang_html      = preg_replace($regexp['search'], $replace, $html);

        $BE['HEADER']['be_parse_lang'] .= ' #be_lang a.be-lang-'.$lang.' {background-image:url(img/famfamfam/lang/'.$lang.'.png);}'.LF;


        $menu_item      = '<li><a href="#" class="be-lang be-lang-'.$lang;

        // check which is current default
        if($lang == $cookie) {
            $new_html = $lang_html; // phpwcms should use the curent lang html
            $BE['BODY_CLOSE']['hidden_main_content'] .= "       cur_be_lang = '" . $lang . "';" . LF;
            $menu_item .= ' be-active';
        }

        $menu[] = $menu_item . '" rel="'.$lang.'" title="'.$BL['be_profile_label_lang'].': '.strtoupper($lang).'">'.$lang.'</a></li>';

        $BE['BODY_CLOSE']['hidden_main_content'] .= "       be_lang_html['".$lang."'] = '" . trim(str_replace(array("\\", "'"), array("\\\\", "\\'"), $lang_html)) . "';" . LF . LF;
    }

    $BE['HEADER']['be_parse_lang']           .= '   </style>';

    $BE['BODY_CLOSE']['hidden_main_content'] .= '   window.addEvent("domready", function() {' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '       var be_lang = $("be_lang");' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '       var be_lang_cnt = $("be_lang_cnt");' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '       if(be_lang && be_lang_cnt) {' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '           var be_lang_items = be_lang.getElements("a");' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '           be_lang_items.each(function(l) {' . LF;

    $BE['BODY_CLOSE']['hidden_main_content'] .= '               l.addEvent("click", function(){' . LF;

    $BE['BODY_CLOSE']['hidden_main_content'] .= "                   if(cur_be_lang == l.rel) {return;}" . LF;

    $BE['BODY_CLOSE']['hidden_main_content'] .= "                   cur_be_lang = l.rel;" . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '                   be_lang_items.each(function(el){el.removeClass("be-active");});' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '                   l.addClass("be-active");' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '                   be_lang_cnt.setHTML(be_lang_html[l.rel]);' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '                   Cookie.set("phpwcms_be_parse_lang", cur_be_lang);' . LF;

    $BE['BODY_CLOSE']['hidden_main_content'] .= '               });' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '           });' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '       }' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '   });' . LF;
    $BE['BODY_CLOSE']['hidden_main_content'] .= '   </script>';

    $menu[] = '</ul>';

    // wrap current lang/html with <div>
    $BE['HTML'] = replace_tmpl_section('BE_MAIN_CONTENT', $BE['HTML'], '<div id="be_lang_cnt">' . (empty($new_html) ? $html : $new_html) . '</div>');

    backend_language_replace( implode(LF, $menu) );

    return null;
}

function backend_language_replace($result) {

    $GLOBALS['BE']['HTML'] = str_replace('{BE_PARSE_LANG}', $result, $GLOBALS['BE']['HTML']);

    return null;
}

function get_language_name($lang='', $default=true) {

    if(empty($lang)) {
        if($default == false) {
            return '';
        }
        $lang = $GLOBALS['phpwcms']['default_lang'];
    }

    $lang = strtoupper($lang);

    return $GLOBALS['BL'][$lang] ?? $lang;

}

function get_pix_or_percent($val) {
    //is used to return configuration width/height values
    //whether based on pixel or percent
    //that's why the default empty return value is ""
    //returns a string
    $val = trim($val);
    $intval = intval($val);
    if(strlen($val) > 1 && strlen($val)-1 == strrpos($val, "%") && $intval) {
        $val = ($intval > 100 ? "100" : $intval) . "%";
    } else {
        $val = $intval ?: "";
    }
    return $val;
}

// List private files
function dir_menu($pid, $zid, $vor, $userID, $vorzeichen = ":") {
    $pid  = intval($pid);
    $sql  = "SELECT f_id, f_name, f_uid, usr_login FROM ".DB_PREPEND."phpwcms_file f ";
    $sql .= "LEFT JOIN ".DB_PREPEND."phpwcms_user u ON u.usr_id=f.f_uid ";
    $sql .= "WHERE f.f_pid=".$pid." AND ";
    if(empty($_SESSION["wcs_user_admin"])) {
        $sql .= "f.f_uid=".intval($userID)." AND ";
    }
    $sql .= "f.f_kid=0 AND f.f_trash=0 ORDER BY f_name";
    $result = _dbQuery($sql);
    if(isset($result[0]['f_id'])) {
        foreach($result as $row) {
            $dirname = html($row['f_name']);
            if($_SESSION["wcs_user_id"] != $row['f_uid']) {
                $dirname .= ' (' . html($row['usr_login']) . ')';
            }
            echo "<option value='".$row['f_id']."'";
            if(intval($zid) == $row['f_id']) {
                echo ' selected="selected"';
            }
            echo ">".$vor.' '.$dirname."</option>\n";
            dir_menu($row['f_id'], $zid, $vor.$vorzeichen, $userID, $vorzeichen);
        }
    }
    return $vor;
}

function get_struct_alias($start_id=0, $parent_alias=false) {

    if($start_id == 0) {
        global $indexpage;

        if($parent_alias && !empty($indexpage['acat_alias'])) {
            return $indexpage['acat_alias'];
        } elseif(!empty($indexpage['acat_pagetitle']) && strlen($indexpage['acat_name']) > strlen($indexpage['acat_pagetitle'])) {
            return strtolower(uri_sanitize($indexpage['acat_pagetitle']));
        } elseif(!empty($struct_array[$start_id]['acat_name'])) {
            return strtolower(uri_sanitize($indexpage['acat_name']));
        }

        return '';
    }
    $start_id = intval($start_id);

    $sql  = 'SELECT acat_id, acat_struct, acat_name, acat_pagetitle, acat_alias ';
    $sql .= 'FROM '.DB_PREPEND.'phpwcms_articlecat WHERE acat_trash=0 ';
    if($parent_alias) {
        $sql .= 'AND acat_id='.$start_id;
    } else {
        $sql .= 'ORDER BY acat_struct, acat_sort';
    }

    $result = _dbQuery($sql);
    $struct_array = array();

    if(isset($result[0]['acat_id'])) {
        if($parent_alias && !empty($result[0]['acat_alias'])) {
            return $result[0]['acat_alias'];
        }

        foreach($result as $value) {
            $value['acat_id'] = intval($value['acat_id']);
            $value['acat_struct'] = intval($value['acat_struct']);
            $struct_array[$value['acat_id']] = $value;
        }
    } else {
        return '';
    }

    $data = array();
    while($start_id && isset($struct_array[$start_id])) {
        if(!empty($struct_array[$start_id]['acat_pagetitle']) && strlen($struct_array[$start_id]['acat_name']) > strlen($struct_array[$start_id]['acat_pagetitle'])) {
            $data[$start_id] = strtolower(uri_sanitize($struct_array[$start_id]['acat_pagetitle']));
        } else {
            $data[$start_id] = strtolower(uri_sanitize($struct_array[$start_id]['acat_name']));
        }
        $start_id = $struct_array[$start_id]["acat_struct"];
    }
    if(!empty($struct_array[$start_id]['acat_pagetitle']) && strlen($struct_array[$start_id]['acat_name']) > strlen($struct_array[$start_id]['acat_pagetitle'])) {
        $data[$start_id] = strtolower(uri_sanitize($struct_array[$start_id]['acat_pagetitle']));
    } elseif(!empty($struct_array[$start_id]['acat_name'])) {
        $data[$start_id] = strtolower(uri_sanitize($struct_array[$start_id]['acat_name']));
    }

    return implode($GLOBALS['phpwcms']['alias_allow_slash'] ? '/' : '-', array_reverse($data));
}


/**
 * Correct the text in case phpwcms charset is different from UTF-8
 *
 * @access public
 * @param string $text
 * @param bool $js (default: false)
 * @return string
 */
function correct_charset($text='', $js=false) {

    if(PHPWCMS_CHARSET !== 'utf-8' && phpwcms_seems_utf8($text)) {
        $text = mb_convert_encoding($text, PHPWCMS_CHARSET);
    }
    if($js) {
        $text = str_replace("'", "\'", $text);
    }
    return $text;
}


/**
 * Render IPTC fields (for use in image info fields)
 *
 * @access public
 * @param mixed $iptc_data
 * @return array
 */
function render_iptc_fileinfo($iptc_data) {

    $iptc_rules = $GLOBALS['phpwcms']['iptc_rules'];
    $iptc_keys = $GLOBALS['phpwcms']['iptc_keys'];
    $fileinfo = array(
        'title' => '',
        'longinfo' => '',
        'copyright' => '',
        'alt' => ''
    );

    if(is_array($iptc_data) && count($iptc_data)) {

        $fileinfo['title'] = $iptc_rules['title'];
        $fileinfo['longinfo'] = $iptc_rules['longinfo'];
        $fileinfo['copyright'] = $iptc_rules['copyright'];
        $fileinfo['alt'] = $iptc_rules['alt'];

        foreach($iptc_data as $iptc_key => $iptc_value) {

            if(empty($iptc_value)) {

                $iptc_value = '';

            } elseif(is_array($iptc_value)) {

                if(empty($GLOBALS['phpwcms']['iptc_rules_multiple'])) {
                    $iptc_value = $iptc_value[0];
                } else {
                    $iptc_value = implode($GLOBALS['phpwcms']['iptc_separator'], $iptc_value);
                }

            }

            $fileinfo['title'] = render_custom_tag($fileinfo['title'], $iptc_key, $iptc_value);
            $fileinfo['longinfo'] = render_custom_tag($fileinfo['longinfo'], $iptc_key, $iptc_value);
            $fileinfo['copyright'] = render_custom_tag($fileinfo['copyright'], $iptc_key, $iptc_value);
            $fileinfo['alt'] = render_custom_tag($fileinfo['alt'], $iptc_key, $iptc_value);

            unset($iptc_keys[$iptc_key]);
        }
    }

    if(count($iptc_keys)) {

        foreach($fileinfo as $field => $value) {

            if((str_contains($value, '{') && str_contains($value, '}')) || str_contains($value, '[/')) {

                foreach($iptc_keys as $iptc_key => $iptc_value) {

                    if($fileinfo[$field] === '') {
                        break;
                    }

                    $fileinfo[$field] = trim( render_custom_tag($fileinfo[$field], $iptc_key, $iptc_value) );
                }
            }
        }
    }

    return $fileinfo;
}

function render_custom_tag($text='', $tag='', $value='', $value_else='', $case_sensitive=true) {
    $value = strval($value);
    $case_sensitive = $case_sensitive ? '' : 'i';
    if($value !== '') {
        $text = preg_replace('/\['.$tag.'\](.*?)\[\/'.$tag.'\]/'.$case_sensitive.'s', '$1', $text);
        $text = preg_replace('/\['.$tag.'_ELSE\].*?\[\/'.$tag.'_ELSE\]/'.$case_sensitive.'s', '', $text);
    } else {
        $text = preg_replace('/\['.$tag.'_ELSE\](.*?)\[\/'.$tag.'_ELSE\]/'.$case_sensitive.'s', '$1', $text);
        $text = preg_replace('/\['.$tag.'\].*?\[\/'.$tag.'\]/'.$case_sensitive.'s', '', $text);
        $text = str_replace('{'.$tag.'_ELSE}', $value_else, $text);
    }
    return str_replace('{'.$tag.'}', $value, $text);
}

function get_template_file_select($block='', $name='', $selected='', $path='') {
    if($block) {
        if($name === '') {
            $name = 'template_' . $block . '_file';
        }
        if($path === '') {
            $path = PHPWCMS_TEMPLATE . 'inc_cntpart/template-sections/' . $block;
        }
        if(is_dir($path)) {
            $files = get_tmpl_files($path, 'tmpl,html,tpl');
            if(count($files)) {
                $select = '<select name="' . $name .'" class="mb-3">';
                $select .= '<option value=""';
                if($selected === '') {
                    $select .= ' selected="selected"';
                }
                $select .= '>' . $GLOBALS['BL']['be_admin_template_choose_file'] . '</option>';
                foreach($files as $file) {
                    $select .= '<option value="' . html($file) . '"';
                    if($selected === $file) {
                        $select .= ' selected="selected"';
                    }
                    $select .= '>inc_cntpart/template-sections/' . html($block.'/'.$file) . '</option>';
                }
                $select .= '</select><br />';
                return $select;
            }
        }
        return '<input type="hidden" name="' . $name . '" value="" />';
    }
    return '';
}
