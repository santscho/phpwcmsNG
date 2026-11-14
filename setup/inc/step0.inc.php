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

$_SESSION['admin_set'] = false;
$setup_recommend = true;

?>
                    <h1 class="h3 d-flex align-items-center gap-2">
                        <span class="badge text-bg-primary">1</span>
                        Thanks! You have agreed to the GPL.
                    </h1>
                    <p>Now that you know the <a href="http://www.gnu.org/licenses/licenses.html#GPL" target="_blank"><strong>licence</strong></a> under which <strong>phpwcms</strong> is released you can continue to install or upgrade <strong>phpwcms</strong>.</p>

                    <h2 class="h3 d-flex align-items-center gap-2 mt-4">
                        <span class="badge text-bg-primary">2</span>
                        Now lets check your server settings
                    </h2>
                    <p>Please proof all information about your system (recommend PHP 8.2+, MySQL 5.6+).</p>

                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <span class="fw-semibold">WWW server:</span>
                            <strong><?php echo empty($_SERVER['SERVER_SOFTWARE']) ? 'unavailable' : html_specialchars($_SERVER['SERVER_SOFTWARE']) ?></strong>
                        </li>
                        <li class="list-group-item">
                            <span class="fw-semibold">PHP version:</span>
                            <?php

                            echo '<strong>' . html_specialchars(phpversion()) . '</strong>';

                            switch (version_compare('8.1', phpversion())) {

                                case -1:    // current used PHP is > OK
                                    echo ' <span class="badge text-bg-success">OK</span>';
                                    break;

                                case  0:    // the same version - HM not recommend
                                    echo ' <span class="badge text-bg-warning">OK</span>';
                                    echo ' <span class="text-body-secondary">(your version of PHP is OK but update to 8.2+ is recommend)</span>';
                                    break;

                                case  1:    // false it's older
                                    echo ' <span class="badge text-bg-danger">Stop</span>';
                                    echo ' <span class="text-body-secondary">(your version of PHP is too old - it is not recommend to continue)</span>';
                                    $setup_recommend = false;
                                    break;
                            }

                            ?>
                        </li>
                        <li class="list-group-item">
                            <span class="fw-semibold">MySQLi extension:</span>
                            <?php
                            if (function_exists('mysqli_connect')) {
                                echo '<strong>installed</strong> <span class="badge text-bg-success">OK</span>';
                            } else {
                                echo '<strong class="text-danger">not installed</strong> <span class="badge text-bg-danger">Stop</span>';
                                $setup_recommend = false;
                            }
                            ?>
                        </li>
                        <li class="list-group-item">
                            <span class="fw-semibold">MySQLi client information:</span>
                            <?php

                            $mysqlnd = false;
                            $mysql_version = mysqli_get_client_info();

                            if (str_contains($mysql_version, 'mysqlnd')) {
                                $mysql_version = preg_replace('/^.*?\s(\d.+?)\s.*?$/', '$1', $mysql_version) . ' (client lib)';
                                $mysqlnd = true;
                            }

                            echo '<strong>' . $mysql_version . '</strong>';

                            $mysql_version = explode('.', preg_replace('/[^0-9.]/', '', str_replace(array('mysqlnd ', ' (client lib)'), '', strtolower($mysql_version))));
                            $mysql_version[0] = (int)$mysql_version[0];
                            $mysql_version[1] = empty($mysql_version[1]) ? 0 : (int)$mysql_version[1];

                            if ($mysql_version[0] >= 5 && ($mysql_version[1] >= 6 || $mysqlnd)) {

                                // current MySQL isOK
                                echo ' <span class="badge text-bg-success">OK</span>';
                            } else {

                                // the same version or older
                                echo ' <span class="badge text-bg-danger">Stop</span>';
                                echo ' <span class="text-body-secondary">(not supported any longer)</span>';

                                $setup_recommend = false;
                            }

                            ?>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-semibold">PHP settings</span>
                                <a class="small" href="http://www.php.net/manual/en/security.php" target="_blank" title="PHP Security">info</a>
                            </div>
                            <ul class="list-group list-group-flush mt-2">
                                <li class="list-group-item px-0">
                                    <strong>register_globals</strong>
                                    <?php

                                    if (ini_get('register_globals')) {
                                        echo '<strong>On</strong> <span class="badge text-bg-warning">Warning</span>';
                                        echo ' <span class="text-body-secondary">(should always be set Off</span> ';
                                        echo '<a href="http://phpsec.org/projects/guide/1.html#1.3" target="_blank" class="small">info</a>';
                                        echo '<span class="text-body-secondary">)</span>';
                                    } else {
                                        echo '<strong>Off</strong> <span class="badge text-bg-success">OK</span>';
                                    }

                                    ?>
                                </li>
                                <?php if (version_compare(phpversion(), '8.2.0', '<')): ?>
                                <li class="list-group-item px-0">
                                    <strong>safe_mode</strong>
                                    <?php

                                    if (ini_get('safe_mode')) {
                                        echo '<strong>On</strong> <span class="badge text-bg-success">OK</span>';
                                        echo ' <span class="text-body-secondary">(limited permissions, but recommend)</span>';
                                    } else {
                                        echo '<strong>Off</strong> <span class="badge text-bg-warning">Warning</span>';
                                        echo ' <span class="text-body-secondary">(check information about security risks</span> ';
                                        echo '<a href="http://www.php.net/features.safe-mode" target="_blank" class="small">info</a>';
                                        echo '<span class="text-body-secondary">)</span>';
                                    }

                                    ?>
                                </li>
                                <?php endif; ?>
                                <li class="list-group-item px-0">
                                    <?php

                                    $_phpinfo = parsePHPModules();
                                    if (isset($_phpinfo['gd']['GD Support']) && $_phpinfo['gd']['GD Support'] == 'enabled' && isset($_phpinfo['gd']['GD Version'])) {
                                        $_phpinfo['gd_version'] = html_specialchars($_phpinfo['gd']['GD Version']);
                                    } else {
                                        $_phpinfo['gd_version'] = 'n.a.';
                                    }

                                    echo '<strong>GD';

                                    if (function_exists('imagegd2')) {
                                        echo '2</strong> ' . $_phpinfo['gd_version'];
                                        echo ' <span class="badge text-bg-success">OK</span>';
                                        $is_gd = true;
                                    } elseif (function_exists('imagegd')) {
                                        echo '1</strong> ' . $_phpinfo['gd_version'];
                                        echo ' <span class="badge text-bg-warning">GD1</span>';
                                        echo ' <span class="text-body-secondary">(GD2 is recommend)</span>';
                                        $is_gd = true;
                                    } else {
                                        echo ' not available</strong>';
                                        echo ' <span class="badge text-bg-danger">not present</span>';
                                        $is_gd = false;
                                    }

                                    if ($is_gd) {

                                        $is_gd = array();

                                        if (imagetypes() & IMG_GIF) {
                                            $is_gd[] = 'GIF <span class="badge text-bg-success">OK</span>';
                                        } else {
                                            $is_gd[] = 'GIF <span class="badge text-bg-danger">Stop</span>';
                                        }
                                        if (imagetypes() & IMG_PNG) {
                                            $is_gd[] = 'PNG <span class="badge text-bg-success">OK</span>';
                                        } else {
                                            $is_gd[] = 'PNG <span class="badge text-bg-danger">Stop</span>';
                                        }
                                        if (imagetypes() & IMG_JPG) {
                                            $is_gd[] = 'JPG <span class="badge text-bg-success">OK</span>';
                                        } else {
                                            $is_gd[] = 'JPG <span class="badge text-bg-danger">Stop</span>';
                                        }

                                        echo '<div class="mt-2">Image types supported: ' . implode(' / ', $is_gd) . '</div>';
                                    }

                                    ?>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <p><strong>phpwcms</strong> has
                        automatic image resizing capabilities. This works very well for standard graphics
                        file formats like JPEG, GIF and PNG as long as your PHP installation has built-in
                        support for <a href="http://en.wikipedia.org/wiki/GD_Graphics_Library" target="_blank">GD</a> &#8212; <strong>GD2</strong>
                        with freetype support is always recommend. <strong>Note:</strong> It
                        is not necessary but recommend that you have installed <a href="http://www.imagemagick.org" target="_blank">ImageMagick</a>
                        and <a href="http://www.ghostscript.com/" target="_blank">GhostScript</a> on
                        your server system which enables image resizing for nearly every
                        graphics file format.</p>
<?php

// is the setup config file writable
if (!is_writable($DOCROOT . '/setup/setup.conf.inc.php')) {

    if (!@chmod($DOCROOT . '/setup/setup.conf.inc.php', 0666)) {

        echo '<div class="alert alert-danger" role="alert"><strong>The file <i>setup.conf.inc.php</i> in which all values are stored is NOT writable.</strong></div>';

        ?><p>Please correct this problem before you can continue (connect to your account by FTP and permissions to
            chmod 777).</p>
        <?php

    }
} else {

    if (!$setup_recommend) {

        echo '<div class="alert alert-warning" role="alert">
		    <strong>It is not recommend to continue with setup. See the warnings!</strong>
		</div>';
    }

    ?>
                    <form action="setup.php?step=1" method="post" class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" name="Submit" value="Start setup of phpwcms" class="btn btn-primary btn-lg">Start setup of phpwcms</button>
                        <button type="submit" name="Submit" value="Upgrade existing installation" class="btn btn-outline-secondary btn-lg"
                                onclick="window.location.href='upgrade.php';return false;">Upgrade existing installation</button>
                    </form>
    <?php
}
