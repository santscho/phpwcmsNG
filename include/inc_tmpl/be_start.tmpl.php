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


// set backend listing values
$_phpwcms_home['homeMaxArticles'] = empty($_COOKIE['homeMaxArticles']) ? 10 : intval($_COOKIE['homeMaxArticles']);
$_phpwcms_home['homeMaxCntParts'] = empty($_COOKIE['homeMaxCntParts']) ? 10 : intval($_COOKIE['homeMaxCntParts']);
$_phpwcms_home['homeCntType'] = empty($_COOKIE['homeCntType']) ? '' : $_COOKIE['homeCntType'];

if(isset($_POST['homeMaxArticles'])) {
	if($_phpwcms_home['homeMaxArticles'] = intval($_POST['homeMaxArticles'])) {
		@setcookie('homeMaxArticles', strval($_phpwcms_home['homeMaxArticles']) , time()+31536000, '/', getCookieDomain(), PHPWCMS_SSL, true); // store cookie for 1 year
	}
}
if(isset($_POST['homeMaxCntParts'])) {
	if($_phpwcms_home['homeMaxCntParts'] = intval($_POST['homeMaxCntParts'])) {
		@setcookie('homeMaxCntParts', strval($_phpwcms_home['homeMaxCntParts']) , time()+31536000, '/', getCookieDomain(), PHPWCMS_SSL, true); // store cookie for 1 year
	}
	$_phpwcms_home['homeCntType'] = clean_slweg($_POST['homeCntType']);
	@setcookie('homeCntType', $_phpwcms_home['homeCntType'], time()+31536000, '/', getCookieDomain(), PHPWCMS_SSL, true); // store cookie for 1 year
	$_SESSION['phpwcms_backend_search'] = '';
}

// set if user has admin rights
$_usql = $_SESSION["wcs_user_admin"] ? '' : 'AND article_uid='.intval($_SESSION["wcs_user_id"]).' ';

// first list last edited articles
$_asql_1  = "SELECT *, DATE_FORMAT(acontent_tstamp, '%d/%m/%Y %H:%i') AS acontent_changed FROM ".DB_PREPEND."phpwcms_articlecontent t1 ";
$_asql_1 .= "LEFT JOIN ".DB_PREPEND."phpwcms_article t2 ON ";
$_asql_1 .= "t1.acontent_aid = t2.article_id ";
$_asql_1 .= 'WHERE t1.acontent_trash=0 AND t2.article_deleted=0 ';
$_asql_1 .= $_usql;
if(is_intval($_phpwcms_home['homeCntType'])) {
	$_asql_1 .= ' AND t1.acontent_type=' . _dbEscape($_phpwcms_home['homeCntType']);
}
if(!empty($_SESSION['phpwcms_backend_search'])) {
	$_asql_1 .= " AND (";
	$_asql_1 .= "	CONCAT(t1.acontent_title,t1.acontent_subtitle,t1.acontent_text,t1.acontent_html) LIKE '%"._dbEscape($_SESSION['phpwcms_backend_search'], FALSE)."%'";
	$_asql_1 .= " OR ";
	$_asql_1 .= "	CONCAT(t2.article_title,t2.article_subtitle,t2.article_summary) LIKE '%"._dbEscape($_SESSION['phpwcms_backend_search'], FALSE)."%'";
	$_asql_1 .= " ) ";

	$_be_search = $BL['be_ctype_search'].': ' . html($_SESSION['phpwcms_backend_search']) ;

} else {
	$_be_search = $BL['be_last_edited'];
}
$_asql_1 .= ' ORDER BY acontent_tstamp DESC LIMIT '.$_phpwcms_home['homeMaxCntParts'];
$_last10_articlecontent = _dbQuery($_asql_1);

$_asql_1  = "SELECT article_id, article_cid, article_title, article_subtitle, article_aktiv, article_uid, ";
$_asql_1 .= "date_format(article_tstamp, '%d/%m/%Y %H:%i') AS article_date ";
$_asql_1 .= 'FROM '.DB_PREPEND.'phpwcms_article WHERE article_deleted=0 ';
$_asql_1 .= $_usql;
if(!empty($_SESSION['phpwcms_backend_search'])) {
	$_asql_1 .= " AND CONCAT(article_title,article_subtitle,article_summary) LIKE '%"._dbEscape($_SESSION['phpwcms_backend_search'], FALSE)."%' ";
}
$_asql_1 .= 'ORDER BY article_tstamp DESC LIMIT '.$_phpwcms_home['homeMaxArticles'];
$_last10_article = _dbQuery($_asql_1);

?>

<?php
// inline pencil icon for the row action buttons
$_be_edit_icon  = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">';
$_be_edit_icon .= '<path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>';
$_be_edit_icon .= '</svg>';
?>

<div class="card mb-5">

    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">

        <h2 class="h6 mb-0"><?php echo $BL['be_cnt_articles'] ?> <span class="fg-secondary">(<?php echo $_be_search ?>)</span></h2>

        <form action="phpwcms.php?do=home" id="setHomeMaxArticles" name="setHomeMaxArticles" method="post">
            <select name="homeMaxArticles" id="homeMaxArticles" class="form-control form-control-sm" onchange="this.form.submit();" aria-label="<?php echo $BL['be_cnt_articles'] ?>">
<?php foreach(array(5,10,15,25,50,75,100,150) as $x): ?>
                <option value="<?php echo $x ?>"<?php is_selected($_phpwcms_home['homeMaxArticles'], $x) ?>><?php echo $x ?></option>
<?php endforeach; ?>
                <option value="99999"<?php is_selected(99999, $_phpwcms_home['homeMaxArticles']) ?>><?php echo $BL['be_ftptakeover_all'] ?></option>
            </select>
        </form>

    </div>

    <table class="table table-hover align-middle mb-0">

        <thead>
            <tr>
                <th scope="col"><?php echo $BL['be_article_atitle'] ?></th>
                <th scope="col" class="text-center text-nowrap"><?php echo $BL['be_cnt_last_edited'] ?></th>
                <th scope="col" class="text-end">&nbsp;</th>
            </tr>
        </thead>

        <tbody>
<?php

    if(count($_last10_article)) {

        foreach($_last10_article as $value) {

            echo '<tr style="cursor:pointer" title="'.$BL['be_func_struct_edit'].'"';
            echo ' onclick="document.location.href=\'phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;id='.$value['article_id'].'\'">'.LF;

            echo "\t".'<td>'.html($value['article_title']);
            if($value['article_subtitle']) {
                echo ' <span class="fg-secondary">/ '.html($value['article_subtitle']).'</span>';
            }
            echo '</td>'.LF;

            echo "\t".'<td class="text-center text-nowrap">'.$value['article_date'].'</td>'.LF;

            echo "\t".'<td class="text-end text-nowrap">';
            echo '<span class="badge '.($value['article_aktiv'] ? 'theme-success' : 'theme-secondary').'">';
            echo $value['article_aktiv'] ? $BL['be_admin_struct_visible'] : $BL['be_inactive'];
            echo '</span> ';
            echo '<a class="btn btn-outline theme-secondary btn-sm" href="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;id='.$value['article_id'].'"';
            echo ' title="'.$BL['be_func_struct_edit'].'" aria-label="'.$BL['be_func_struct_edit'].'">'.$_be_edit_icon.'</a>';
            echo '</td>'.LF;

            echo '</tr>'.LF;

        }
    }

?>
        </tbody>

    </table>

    <div class="card-footer d-flex flex-column md:flex-row gap-3">
        <a class="btn theme-primary btn-solid btn-styled btn-sm" href="phpwcms.php?do=articles"><?php echo $BL['be_subnav_article_center'] ?></a>
        <a class="btn btn-outline theme-secondary btn-sm" href="phpwcms.php?do=articles&amp;p=1&amp;struct=0"><?php echo $BL['be_subnav_article_new'] ?></a>
    </div>

</div>

<div class="card mb-5">

    <div class="card-header d-flex flex-column md:flex-row md:justify-content-between md:align-items-center gap-3">

        <h2 class="h6 mb-0"><?php echo $BL['be_ctype'] ?> <span class="fg-secondary">(<?php echo $_be_search ?>)</span></h2>

        <form action="phpwcms.php?do=home" id="setHomeMaxCntParts" name="setHomeMaxCntParts" method="post" class="d-flex flex-column md:flex-row gap-3">
            <select name="homeCntType" class="form-control form-control-sm" onchange="this.form.submit();" aria-label="<?php echo $BL['be_cnt_type'] ?>">
                <option value="">&#8211;</option>
<?php foreach($wcs_content_type as $key => $value): ?>
                <option value="<?php echo $key ?>"<?php is_selected($_phpwcms_home['homeCntType'], $key) ?>><?php echo $value ?></option>
<?php endforeach; ?>
            </select>
            <select name="homeMaxCntParts" class="form-control form-control-sm" onchange="this.form.submit();" aria-label="<?php echo $BL['be_ctype'] ?>">
<?php foreach(array(5,10,15,25,50,75,100,150,200,250) as $x): ?>
                <option value="<?php echo $x ?>"<?php is_selected($_phpwcms_home['homeMaxCntParts'], $x) ?>><?php echo $x ?></option>
<?php endforeach; ?>
                <option value="99999"<?php is_selected(99999, $_phpwcms_home['homeMaxCntParts']) ?>><?php echo $BL['be_ftptakeover_all'] ?></option>
            </select>
        </form>

    </div>

    <table class="table table-hover align-middle mb-0">

        <thead>
            <tr>
                <th scope="col" class="text-nowrap"><?php echo $BL['be_cnt_type'] ?></th>
                <th scope="col"><?php echo $BL['be_article_atitle'].' / '.$BL['be_profile_label_notes'] ?></th>
                <th scope="col" class="text-center text-nowrap"><?php echo $BL['be_cnt_last_edited'] ?></th>
                <th scope="col" class="text-end">&nbsp;</th>
            </tr>
        </thead>

        <tbody>
<?php

    if(count($_last10_articlecontent)) {

        foreach($_last10_articlecontent as $value) {

            if(($value["acontent_type"] == 30 && !isset($phpwcms['modules'][$value["acontent_module"] ])) || !isset($wcs_content_type[$value["acontent_type"]])) {
                continue;
            }

            $value['notice'] = str_replace('###', ', ', trim($value['acontent_title'].'###'.$value['acontent_subtitle'].'###'.$value['acontent_comment'], '#'));
            if($value['notice']) {
                $value['notice_long'] = $value['article_title'] . ' > ' . $value['notice'];
                $value['notice'] = getCleanSubString($value['article_title'], 15, '.') . ' > ' . $value['notice'];
            } else {
                $value['notice_long'] = $value['notice'] = $value['article_title'];
            }

            $value['notice'] = html(preg_replace('/\s+/', ' ', $value['notice'], false));

            echo '<tr style="cursor:pointer" title="'.html($value['notice_long'], false).'"';
            echo ' onclick="document.location.href=\'phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id='.$value['acontent_aid'].'&amp;acid='.$value['acontent_id'].'\'">'.LF;

            echo "\t".'<td class="text-nowrap">'.$wcs_content_type[$value["acontent_type"]];
            if($value["acontent_type"] == 30) {
                echo ' <span class="fg-secondary">'.$BL['modules'][$value["acontent_module"]]['listing_title'].'</span>';
            }
            echo '</td>'.LF;

            echo "\t".'<td>'.$value['notice'].'</td>'.LF;

            echo "\t".'<td class="text-center text-nowrap">'.$value['acontent_changed'].'</td>'.LF;

            echo "\t".'<td class="text-end text-nowrap">';
            echo '<span class="badge '.($value['acontent_visible'] ? 'theme-success' : 'theme-secondary').'">';
            echo $value['acontent_visible'] ? $BL['be_admin_struct_visible'] : $BL['be_inactive'];
            echo '</span> ';
            echo '<a class="btn btn-outline theme-secondary btn-sm" href="phpwcms.php?do=articles&amp;p=2&amp;s=1&amp;aktion=2&amp;id='.$value['acontent_aid'].'&amp;acid='.$value['acontent_id'].'"';
            echo ' title="'.$BL['be_func_content_edit'].'" aria-label="'.$BL['be_func_content_edit'].'">'.$_be_edit_icon.'</a>';
            echo '</td>'.LF;

            echo '</tr>'.LF;

        }
    }

?>
        </tbody>

    </table>

</div>
