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

if (!defined('PHP8')) {
    die("You Cannot Access This Script Directly, Have a Nice Day.");
}

$_SERVER['DOCUMENT_ROOT'] = $phpwcms['DOC_ROOT'];
$phpwcms["root"] = !empty($phpwcms["root"]) ? "/".$phpwcms["root"] : "";

// Renders one path/status row of the final check list
$status_row = function ($label, $path, $status, $text_status = null) {
    $text_status = $text_status === null ? $status : $text_status;
    $text        = gib_status_text($text_status);
    $is_problem  = str_contains($text, 'FALSE');
    echo '<li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-3">';
    echo '<div><div class="fw-semibold">' . html_specialchars($label) . '</div><code>' . html_specialchars($path) . '</code></div>';
    echo '<div class="text-end"><span class="badge theme-' . ($is_problem ? 'danger' : 'success') . '">' . ($is_problem ? 'PROBLEM' : 'OK') . '</span>';
    echo '<div class="small fg-secondary">' . $text . '</div></div>';
    echo '</li>';
};

?>
                    <h1 class="h3">Ready to start phpwcms?</h1>
                    <p>Some &quot;problems&quot; maybe OK &#8212; you can check by testing phpwcms installation.</p>

                    <ul class="list-group mb-7">
<?php

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["file_path"]);
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["file_path"], 0777, $status);
  }
  $status_row('filestorage', $phpwcms["file_path"], $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["file_path"].'/can_be_deleted');
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["file_path"].'/can_be_deleted', 0777, $status);
  }
  $status_row('deleted files', $phpwcms["file_path"].'/can_be_deleted', $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["templates"]);
  $status_row('templates', $phpwcms["templates"], $status, $status == 1 ? 3 : $status);

  $template_lang_path = trim($phpwcms["templates"], '/').'/template_lang';
  $status = check_path_status($phpwcms["root"]."/".$template_lang_path);
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$template_lang_path, 0777, $status);
  }
  $status_row('template languages', $template_lang_path, $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["ftp_path"]);
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["ftp_path"], 0777, $status);
  }
  $status_row('ftp takeover', $phpwcms["ftp_path"], $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["content_path"]);
  $status_row('frontend content', $phpwcms["content_path"], $status, $status == 1 ? 3 : $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["content_path"]."/images");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["content_path"]."/images", 0777, $status);
  }
  $status_row('frontend images', $phpwcms["content_path"]."/images", $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["content_path"]."/form");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["content_path"]."/form", 0777, $status);
  }
  $status_row('frontend form', $phpwcms["content_path"]."/form", $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["content_path"]."/tmp");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["content_path"]."/tmp", 0777, $status);
  }
  $status_row('frontend tmp', $phpwcms["content_path"]."/tmp", $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["content_path"]."/rss");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["content_path"]."/rss", 0777, $status);
  }
  $status_row('frontend rss', $phpwcms["content_path"]."/rss", $status);

  $status = check_path_status($phpwcms["root"]."/".$phpwcms["content_path"]."/pages");
  $status_row('frontend pages', $phpwcms["content_path"]."/pages", $status, $status == 1 ? 3 : $status);

  $status = check_file_status($phpwcms["root"]."/".$phpwcms["templates"]."/inc_default/startup.php");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["templates"]."/inc_default/startup.php", 0666, $status, 1);
  }
  $status_row('startup text', $phpwcms["templates"]."/inc_default/startup.php", $status);

  $status = check_file_status($phpwcms["root"]."/".$phpwcms["templates"]."/inc_css/frontend.css");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["templates"]."/inc_css/frontend.css", 0666, $status, 1);
  }
  $status_row('main CSS file', $phpwcms["templates"]."/inc_css/frontend.css", $status);

  $status = check_file_status($phpwcms["root"]."/include/config/conf.indexpage.inc.php");
  if($status != 2) {
  	$status = set_chmod($phpwcms["root"]."/".$phpwcms["templates"]."/include/config/conf.indexpage.inc.php", 0666, $status, 1);
  }
  $status_row('index level settings', 'include/config/conf.indexpage.inc.php', $status);

?>
                    </ul>

                    <div class="d-flex flex-wrap gap-3 mb-7 fg-secondary">
                        <span><span class="badge theme-success">OK</span> OK</span>
                        <span><span class="badge theme-danger">PROBLEM</span> PROBLEM</span>
                    </div>
<?php

$this_root = dirname(__FILE__, 3);
$config_setup = read_textfile($this_root.'/setup/setup.conf.inc.php');
$config_setup = str_replace('?>', "\$NO_ACCESS = true;\n\n", $config_setup);
$result = false;
if(!is_file($this_root.'/include/config/conf.inc.php')) {

	// try to chmod
	//set_chmod($phpwcms["root"]."/include/config',

	// Try to write config file to the correct position
	if(!write_textfile($this_root.'/include/config/conf.inc.php', $config_setup)) {

		// Try to copy
		if(!@copy($this_root.'/setup/setup.conf.inc.php', $this_root.'/include/config/conf.inc.php')) {

			// Try to move
			if(@rename($this_root.'/setup/setup.conf.inc.php', $this_root.'/include/config/conf.inc.php')) {
				// moved successfully
				$result = true;
			}

		} else {
			// copied successfully
			$result = true;
		}

	} else {
		// written successfully
		$result = true;
	}
}

// Try to secure setup folder
@write_textfile($this_root.'/setup/.htaccess', 'Deny from all');

if($result): ?>
                    <div class="alert theme-success" role="alert">
                        The conf.inc.php was created successfully and placed at the right position by the setup script.
                    </div>
<?php else: ?>
                    <div class="alert theme-danger" role="alert">
                        The conf.inc.php could not placed at the right position by the setup script.
                    </div>
<?php endif; ?>
<?php
// Create default .htaccess
if(is_file($this_root.'/.htaccess')):
?>
                    <div class="alert theme-warning" role="alert">
                        A <strong>.htaccess</strong> file exists. Compare against the <a href="../_.htaccess" target="_blank" rel="noopener">default</a>.
                        If you want to use segmented URLs it is necessary to configure the Rewrite process.
                    </div>
<?php else:
    $result = false;
    // Try to copy
    if(!@copy($this_root.'/_.htaccess', $this_root.'/.htaccess')) {
        // Try to move
        if(@rename($this_root.'/_.htaccess', $this_root.'/.htaccess')) {
            // moved successfully
            $result = true;
        }
    } else {
        $result = true;
    }
    if($result): ?>
                    <div class="alert theme-success" role="alert">
                        A default <strong>.htaccess</strong> file was placed in the document root of your installation.
                        This file usually is hidden because of the leading <strong>.</strong> in the file name.
                        <?php
                        if($phpwcms["root"] && $htaccess = @read_textfile($this_root.'/.htaccess')) {
                            $htaccess = str_replace('RewriteBase /', '#RewriteBase /', $htaccess);
                            $htaccess = str_replace('#RewriteBase /subfolder/', 'RewriteBase /' . trim($phpwcms["root"], '/') . '/', $htaccess);
                            write_textfile($this_root.'/.htaccess', $htaccess);
                        }
                        ?>
                    </div>
    <?php else: ?>
                    <div class="alert theme-danger" role="alert">
                        Writing the <strong>.htaccess</strong> file to the document root of your installation failed.
                        If you want to use Rewrite please configure it manually. See the file
                        <a href="../_.htaccess" target="_blank" rel="noopener">_.htaccess</a>.
                    </div>
    <?php endif; ?>
<?php endif; ?>

                    <h2 class="h4 mt-7">The manual way to finish setup</h2>
                    <p>
                        Download the config file <a href="get_conf_file.php"><strong>here</strong></a> and copy it to ./include/config/<strong>conf.inc.php</strong>
                        &#8212; you can also edit values manually. Maybe you have to rename the file if you download
                        using Internet Explorer (name is: <strong>conf.inc.php</strong>). Another possible way is to
                        connect to your account by ftp. Then place the file ./setup/<strong>setup.conf.inc.php</strong> in ./include/config. There you have to delete or rename default conf.inc.php &#8212; then
                        rename setup.conf.inc.php to conf.inc.php. It's not possible to start the setup
                        process again as long the setup folder still exists and if you have downloaded
                        the created config file once.
                    </p>

                    <div class="alert theme-danger" role="alert">
                        <strong>ATTENTION!!!<br>Delete the &quot;setup&quot; folder</strong>
                        otherwise everybody might see your username, passwords and settings.
                    </div>

                    <p>
                        To makes changes again or proof your values:<br>
                        &#8212; <a href="setup.php?step=1">MySQL database infos</a><br>
                        &#8212; <a href="setup.php?step=2">site infos and admin account</a><br>
                        &#8212; <a href="setup.php?step=3">path values</a><br>
                        &#8212; <a href="setup.php?step=4">content values</a>
                    </p>
                    <p>
                        Please check infos about system and PHP version with the requirements. <strong>Very
                        important when you use ImageMagick:</strong> Please check that the system
                        can find the application (paths must be registered to the system - try this
                        by using &quot;convert -version&quot; inside your terminal or command line).
                        Check <a href="http://www.imagemagick.org/" target="_blank" rel="noopener">http://www.imagemagick.org</a>
                        for additional information.
                    </p>
