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

// general wrapper for ajax based queries

$phpwcms = array('SESSION_START' => true);
$base_dir = dirname(__DIR__, 2);
require_once $base_dir . '/include/config/conf.inc.php';
require_once $base_dir . '/include/inc_lib/default.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/helper.session.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/dbcon.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/general.inc.php';
require_once PHPWCMS_ROOT.'/include/inc_lib/backend.functions.inc.php';

if(empty($_SESSION['wcs_user']) || empty($_SESSION['PHPWCMS_BROWSER_HASH']) || $_SESSION['PHPWCMS_BROWSER_HASH'] !== $GLOBALS['phpwcms']['USER_AGENT']['hash']) {
    headerRedirect('', 401);
    die();
}

if(isset($_POST['action'])) {
    $action		= $_POST['action'];
    $method		= $_POST['method'] ?? 'json';
    $value		= isset($_POST['value']) ? clean_slweg($_POST['value'], 0, false) : '';
    $jquery		= false;
} elseif($_GET['action']) {
    $action		= $_GET['action'];
    $method		= $_GET['method'] ?? 'json';
    $value		= isset($_GET['value']) ? clean_slweg($_GET['value'], 0, false) : '';
    $jquery		= true;
} else {
    $method     = 'json';
    $action     = 'empty';
    $jquery     = false;
    $value      = '';
}

if(empty($value) && $action !== 'backend_search_clear') {
    $action = 'empty';
}

// do charset conversions for value
if(PHPWCMS_CHARSET !== 'utf-8') {
    $value = mb_convert_encoding($value, PHPWCMS_CHARSET, 'utf-8');
}

$data = array();

switch($action) {

    case 'category':
        $where  = "cat_status=1 AND cat_type NOT IN('module_shop') AND ";
        $where .= "cat_name LIKE '%" . _dbEscape(preg_replace('/[^\w\-\/]/', '', $value), false) . "%'";
        $result = _dbGet('phpwcms_categories', 'cat_name', $where, 'cat_name', 'cat_name', 20);

        if(isset($result[0])) {
            foreach($result as $value) {
                $value = mb_convert_encoding($value['cat_name'], 'UTF-8');
                $data[] = $jquery ? array('cat_name' => $value) : $value;
            }
        }
        break;

    case 'newstags':
        $where  = "cat_status=1 AND cat_type='news' AND ";
        $where .= "SUBSTRING(cat_name, 1, 5) != '*CSS-' AND ";
        $where .= "cat_name LIKE '%" . _dbEscape(preg_replace('/[^\w\-\/]/', '', $value), false) . "%'";
        $result = _dbGet('phpwcms_categories', 'cat_name', $where, 'cat_name', 'cat_name', 20);

        if(isset($result[0])) {
            foreach($result as $value) {
                $value = mb_convert_encoding($value['cat_name'], 'UTF-8');
                $data[] = $jquery ? array('cat_name' => $value) : $value;
            }
        }
        break;

    case 'lang':
        $data = is_array($phpwcms['allowed_lang']) && count($phpwcms['allowed_lang']) ? $phpwcms['allowed_lang'] : array($phpwcms['default_lang']);
        sort($data);
        break;

    case 'backend_theme':
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || !in_array($value, array('auto', 'light', 'dark'), true)
            || empty($_POST['csrftoken'])
            || empty($_SESSION['wcs_user_id'])
            || $_POST['csrftoken'] !== get_token_get_value()
        ) {
            headerRedirect('', 403);
            die();
        }
        $updated = _dbQuery(
            'UPDATE ' . DB_PREPEND . 'phpwcms_user SET usr_theme=' . _dbEscape($value) . ' WHERE usr_id=' . (int) $_SESSION['wcs_user_id'],
            'UPDATE'
        );
        $_SESSION['wcs_user_theme'] = $value;
        $data = array('theme' => $value, 'status' => $updated === false ? 'failed' : 'ok');
        break;

    case 'backend_search_clear':
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || empty($_POST['csrftoken'])
            || empty($_SESSION['wcs_user_id'])
            || $_POST['csrftoken'] !== get_token_get_value()
        ) {
            headerRedirect('', 403);
            die();
        }
        $_SESSION['phpwcms_backend_search'] = '';
        // the browser reloads the HOME listing right after this response, so the
        // session has to be written before the response ends
        session_write_close();
        $data = array('status' => 'ok');
        break;

    case 'backend_sort':
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
            || empty($_POST['csrftoken'])
            || $_POST['csrftoken'] !== get_token_get_value()
            || empty($_SESSION['wcs_user_id'])
        ) {
            headerRedirect('', 403);
            die();
        }

        $sort_group     = isset($_POST['group']) ? (string) $_POST['group'] : '';
        $sort_parent    = isset($_POST['parent']) ? intval($_POST['parent']) : 0;
        $sort_block     = isset($_POST['block']) ? (string) $_POST['block'] : '';
        $sort_ids       = array();

        if (isset($_POST['ids'])) {
            foreach (explode(',', (string) $_POST['ids']) as $sort_id) {
                $sort_id = intval(trim($sort_id));
                if ($sort_id > 0 && !in_array($sort_id, $sort_ids, true)) {
                    $sort_ids[] = $sort_id;
                }
            }
        }

        if (!$sort_ids || !in_array($sort_group, array('struct', 'article', 'content'), true)) {
            http_response_code(400);
            die();
        }

        $sort_allowed       = array();
        $sort_allowed_uid   = true;
        $sort_owner         = 0;

        if ($sort_group === 'struct' && !empty($_SESSION['wcs_user_admin'])) {
            $sort_result = _dbQuery(
                'SELECT acat_id FROM ' . DB_PREPEND . 'phpwcms_articlecat WHERE acat_trash=0 AND acat_struct=' . $sort_parent
            );
        } elseif ($sort_group === 'article') {
            $sort_result = _dbQuery(
                'SELECT article_id, article_uid FROM ' . DB_PREPEND . 'phpwcms_article WHERE article_deleted=0 AND article_cid=' . $sort_parent
            );
        } elseif ($sort_group === 'content' && $sort_block !== '') {
            // content parts are ordered inside one block of one article
            $sort_article = _dbQuery(
                'SELECT article_uid FROM ' . DB_PREPEND . 'phpwcms_article WHERE article_id=' . $sort_parent
            );
            $sort_owner = isset($sort_article[0]['article_uid']) ? (int) $sort_article[0]['article_uid'] : 0;
            $sort_result = $sort_owner ? _dbQuery(
                'SELECT acontent_id FROM ' . DB_PREPEND . 'phpwcms_articlecontent WHERE acontent_trash=0 AND acontent_aid=' . $sort_parent . ' AND acontent_block=' . _dbEscape($sort_block)
            ) : false;
        } else {
            $sort_result = false;
        }

        if (is_array($sort_result)) {
            foreach ($sort_result as $sort_row) {
                if ($sort_group === 'struct') {
                    $sort_allowed[] = (int) $sort_row['acat_id'];
                } elseif ($sort_group === 'article') {
                    $sort_allowed[] = (int) $sort_row['article_id'];
                    if ((int) $sort_row['article_uid'] !== (int) $_SESSION['wcs_user_id']) {
                        $sort_allowed_uid = false;
                    }
                } else {
                    $sort_allowed[] = (int) $sort_row['acontent_id'];
                }
            }
        }

        if ($sort_group === 'article' && !$sort_allowed_uid && empty($_SESSION['wcs_user_admin'])) {
            headerRedirect('', 403);
            die();
        }

        if ($sort_group === 'content' && $sort_owner !== (int) $_SESSION['wcs_user_id'] && empty($_SESSION['wcs_user_admin'])) {
            headerRedirect('', 403);
            die();
        }

        $sort_allowed_check = $sort_allowed;
        $sort_ids_check     = $sort_ids;
        sort($sort_allowed_check);
        sort($sort_ids_check);

        if (!$sort_allowed || $sort_allowed_check !== $sort_ids_check) {
            http_response_code(409);
            die();
        }

        $data = array('status' => 'ok', 'count' => count($sort_ids));

        foreach ($sort_ids as $sort_index => $sort_id) {
            $sort_value = ($sort_index + 1) * 10;
            if ($sort_group === 'struct') {
                _dbQuery(
                    'UPDATE ' . DB_PREPEND . 'phpwcms_articlecat SET acat_sort=' . $sort_value . ' WHERE acat_id=' . $sort_id,
                    'UPDATE'
                );
            } elseif ($sort_group === 'article') {
                _dbQuery(
                    'UPDATE ' . DB_PREPEND . 'phpwcms_article SET article_sort=' . $sort_value . ' WHERE article_id=' . $sort_id,
                    'UPDATE'
                );
            } else {
                _dbQuery(
                    'UPDATE ' . DB_PREPEND . 'phpwcms_articlecontent SET acontent_sorting=' . $sort_value . ' WHERE acontent_id=' . $sort_id,
                    'UPDATE'
                );
            }
        }
        break;

    case 'flush_image_cache':
        if (empty($_SESSION['wcs_user_admin'])) {
            headerRedirect('', 401);
            die();
        }
        $files = returnFileListAsArray(PHPWCMS_ROOT.'/'.PHPWCMS_IMAGES, array('jpg', 'png', 'gif', 'svg', 'webp'));
        $data = array('file_count' => 0, 'status' => 'ok');
        if(is_array($files)) {
            $data['file_count'] = count($files);
            foreach($files as $file) {
                @unlink(PHPWCMS_ROOT.'/'.PHPWCMS_IMAGES.$file['filename']);
            }
        } else {
            $data['status'] = '';
        }
        break;
}

if($method === 'json') {

    header('Content-type: application/json');
    echo json_encode($data);

}
