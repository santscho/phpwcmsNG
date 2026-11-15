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

// Main Backend Nav Definition

$wcsnav = array();

$wcsnav["articles"]     = '<a href="phpwcms.php?do=articles" class="nav-link">'.$BL['be_nav_articles'].'</a>';
$wcsnav["files"]        = '<a href="phpwcms.php?do=files" class="nav-link">'.$BL['be_nav_files'].'</a>';
$wcsnav["modules"]      = '<a href="phpwcms.php?do=modules" class="nav-link">'.$BL['be_nav_modules'].'</a>';
$wcsnav["messages"]     = '<a href="phpwcms.php?do=messages&amp;p=4" class="nav-link">'.$BL['be_nav_messages'].'</a>';

if(!empty($phpwcms['enable_chat'])) {
    $wcsnav["chat"]     = '<a href="phpwcms.php?do=chat" class="nav-link">'.$BL['be_nav_chat'].'</a>';
}

$wcsnav["profile"]      = '<a href="phpwcms.php?do=profile" class="nav-link">'.$BL['be_nav_profile'].'</a>';
$wcsnav["admin"]        = '<a href="phpwcms.php?do=admin&amp;p=6" class="nav-link">'.$BL['be_nav_admin'].'</a>';

/**
 * Mark a main navigation entry as the current page
 */
function wcsnav_set_active(&$nav, $key) {
    if(empty($nav[$key])) {
        return;
    }
    $nav[$key] = str_replace(
        'class="nav-link"',
        'class="nav-link active" aria-current="page"',
        $nav[$key]
    );
}
