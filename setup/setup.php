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

session_start();

$phpwcms = array();

require_once __DIR__ .'/inc/setup.func.inc.php';
require_once $DOCROOT.'/setup/setup.conf.inc.php';

if (is_file($DOCROOT . '/include/config/conf.inc.php')) {
    header('HTTP/1.1 401 Authorization Required');
    header('Location: ../login.php');
    exit();
}

$step		= isset($_GET["step"]) ? intval($_GET["step"]) : 0;
$do			= isset($_POST["do"]) ? intval($_POST["do"]) : 0;
$err		= 0;
$prepend	= $phpwcms["db_prepend"];

if($do) require_once($DOCROOT.'/setup/inc/setup.check.inc.php');


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php echo $phpwcms['charset'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>phpwcms Setup</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="../include/inc_ext/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-1">

<nav class="navbar md:navbar-expand bg-primary" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3" href="http://www.phpwcms.org" target="_blank" rel="noopener">
            <img src="../img/backend/phpwcms-logo.svg" alt="phpwcms" width="140" height="39">
            <span class="badge theme-inverse">Setup</span>
        </a>
        <span class="navbar-text md:ms-5 d-none md:d-inline">v<?php echo htmlspecialchars($phpwcms_version) ?>, release <?php echo htmlspecialchars($phpwcms_release_date) ?></span>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="../index.php" target="_top">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php" target="_top">Licence</a></li>
            <li class="nav-item"><a class="nav-link" href="../<?php echo $phpwcms['login.php'] ?>" target="_top">Login</a></li>
        </ul>
    </div>
</nav>

<main class="container py-7 md:py-12">
    <div class="row justify-content-center">
        <div class="col-12 lg:col-10 xl:col-9">
            <div class="card shadow-sm">
                <div class="card-body p-7 md:p-12">
<?php

		if(empty($NO_ACCESS)) {

			switch($step) {
				case  1:	include $DOCROOT.'/setup/inc/step1.inc.php'; break;
				case  2:	include $DOCROOT.'/setup/inc/step2.inc.php'; break;
				case  3:	include $DOCROOT.'/setup/inc/step3.inc.php'; break;
				case  4:	include $DOCROOT.'/setup/inc/step4.inc.php'; break;
				case  5:	include $DOCROOT.'/setup/inc/step5.inc.php'; break;
				default:	include $DOCROOT.'/setup/inc/step0.inc.php';
			}

		} else {
			?>
                    <div class="alert theme-danger" role="alert">
                        <div>
                            <h1 class="h4 alert-heading">Setup stopped</h1>
                            <p>Access on setup process is restricted if the setup config file is downloaded once. To re-enable setup remove the line</p>
                            <p><code>$NO_ACCESS = true;</code></p>
                            <p class="mb-0">in <code>setup.conf.inc.php</code>.</p>
                        </div>
                    </div>
			<?php
		}
		?>
                </div>
            </div>

            <p class="text-center fg-secondary mt-7">
                <a href="http://www.phpwcms.org" target="_blank" rel="noopener">phpwcms</a>
                &copy; 2003&#8212;<?php echo date('Y') ?>
                <a title="oliver at phpwcms dot de" onclick="location.href='mailto:oliver'+'@'+'phpwcms'+'.'+'de';return false;" href="#">Oliver Georgi</a>.
                Licensed under <a href="http://www.gnu.org/licenses/gpl.html" target="_blank" rel="noopener">GPL</a>.
                Extensions are copyright of their respective owners.
            </p>
        </div>
    </div>
</main>

<script type="module" src="../include/inc_ext/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
