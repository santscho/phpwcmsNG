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


// PHP variables

?>
<div class="row g-4">

	<div class="col-12">
		<label class="form-label" for="cvar"><?php echo $BL['be_cnt_vars'] ?></label>
		<textarea name="cvar" rows="20" wrap="VIRTUAL" class="form-control autosize" id="cvar" style="font-family: var(--bs-font-mono)"><?php echo isset($content["var"]) ? html($content["var"]) : '' ?></textarea>
	</div>

</div>
