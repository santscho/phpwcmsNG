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


// List

if(empty($content['bulletlist']["list_type"])) $content['bulletlist']["list_type"] = 0;

?>
<div class="row g-4">

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_ullist'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" type="radio" name="clist_type" id="clist_type0" value="0"<?php echo is_checked('0', $content['bulletlist']["list_type"], 0, 0) ?>>
				<label for="clist_type0">&lt;ul&gt;</label>
			</div>
			<div class="form-field">
				<input class="radio" type="radio" name="clist_type" id="clist_type1" value="1"<?php echo is_checked('1', $content['bulletlist']["list_type"], 0, 0) ?>>
				<label for="clist_type1">&lt;ol&gt;</label>
			</div>
			<div class="form-field">
				<input class="radio" type="radio" name="clist_type" id="clist_type2" value="2"<?php echo is_checked('2', $content['bulletlist']["list_type"], 0, 0) ?>>
				<label for="clist_type2">&lt;dl&gt;</label>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="ctext"><?php echo $BL['be_cnt_ullist_desc'] ?></label>
		<textarea name="ctext" rows="30" class="form-control autosize" id="ctext"><?php echo $content["text"] ?? '' ?></textarea>
	</div>

</div>
