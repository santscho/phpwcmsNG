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


// Sitemap

if(!isset($content['sitemap'])) {

	$content['sitemap']["before"]			= '';
	$content['sitemap']["after"]			= '';
	$content['sitemap']["catimg"]			= '';
	$content['sitemap']["articleimg"]		= '';
	$content['sitemap']["startid"]			= 0;
	$content['sitemap']["display"]			= 0;
	$content['sitemap']["catclass"]			= '';
	$content['sitemap']["articleclass"]		= '';
	$content['sitemap']["classcount"]		= 0;
	$content['sitemap']["without_parent"]	= 0;

}

?>
<div class="row g-4">

	<div class="col-12">
		<label class="form-label" for="csitemap_before"><?php echo $BL['be_cnt_guestbook_before'] ?></label>
		<textarea name="csitemap_before" cols="40" rows="3" class="form-control autosize" id="csitemap_before" style="font-family: var(--bs-font-mono)"><?php echo html($content["sitemap"]["before"]) ?></textarea>
	</div>

	<div class="col-12">
		<label class="form-label" for="csitemap_after"><?php echo $BL['be_cnt_guestbook_after'] ?></label>
		<textarea name="csitemap_after" cols="40" rows="3" class="form-control autosize" id="csitemap_after" style="font-family: var(--bs-font-mono)"><?php echo html($content["sitemap"]["after"]) ?></textarea>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csitemap_catimg"><?php echo $BL['be_cnt_sitemap_catimage'] ?></label>
		<div class="d-flex flex-wrap align-items-center gap-3">
			<input name="csitemap_catimg" type="text" id="csitemap_catimg" class="form-control w-auto" value="<?php echo html($content["sitemap"]["catimg"]) ?>" size="40">
<?php
	if($content["sitemap"]["catimg"]) {
		echo '<img src="'.$content["sitemap"]["catimg"].'" alt="" class="img-fluid">';
	}
?>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csitemap_articleimg"><?php echo $BL['be_cnt_sitemap_articleimage'] ?></label>
		<div class="d-flex flex-wrap align-items-center gap-3">
			<input name="csitemap_articleimg" type="text" id="csitemap_articleimg" class="form-control w-auto" value="<?php echo html($content["sitemap"]["articleimg"]) ?>" size="40">
<?php
	if($content["sitemap"]["articleimg"]) {
		echo '<img src="'.$content["sitemap"]["articleimg"].'" alt="" class="img-fluid">';
	}
?>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csitemap_startid"><?php echo $BL['be_cnt_sitemap_startid'] ?></label>
		<select name="csitemap_startid" id="csitemap_startid" class="form-control">
<?php
	echo "<option value='0'".((!$content["sitemap"]["startid"])?" selected":"").">".$BL['be_admin_struct_index']."</option>\n";
	struct_select_menu(0, 0, $content["sitemap"]["startid"]);
?>
		</select>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_cnt_sitemap_display'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" name="csitemap_display" id="csitemap_display0" type="radio" value="0"<?php is_checked(0, $content["sitemap"]["display"]) ?>>
				<label for="csitemap_display0"><?php echo $BL['be_cnt_sitemap_structuronly'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="csitemap_display" id="csitemap_display1" type="radio" value="1"<?php is_checked(1, $content["sitemap"]["display"]) ?>>
				<label for="csitemap_display1"><?php echo $BL['be_cnt_sitemap_structurarticle'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csitemap_without_parent" id="csitemap_without_parent" type="checkbox" value="1"<?php is_checked(1, $content["sitemap"]["without_parent"]) ?>>
				<label for="csitemap_without_parent"><?php echo $BL['be_cnt_sitemap_without_parent'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csitemap_catclass"><?php echo $BL['be_cnt_sitemap_catclass'] ?></label>
		<input name="csitemap_catclass" type="text" id="csitemap_catclass" class="form-control" value="<?php echo html($content["sitemap"]["catclass"]) ?>" size="40">
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csitemap_articleclass"><?php echo $BL['be_cnt_sitemap_articleclass'] ?></label>
		<input name="csitemap_articleclass" type="text" id="csitemap_articleclass" class="form-control" value="<?php echo html($content["sitemap"]["articleclass"]) ?>" size="40">
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_cnt_sitemap_count'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" name="csitemap_classcount" id="csitemap_classcount0" type="radio" value="0"<?php is_checked(0, $content["sitemap"]["classcount"]) ?>>
				<label for="csitemap_classcount0"><?php echo $BL['be_cnt_sitemap_noclasscount'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="csitemap_classcount" id="csitemap_classcount1" type="radio" value="1"<?php is_checked(1, $content["sitemap"]["classcount"]) ?>>
				<label for="csitemap_classcount1"><?php echo $BL['be_cnt_sitemap_classcount'] ?></label>
			</div>
		</div>
	</div>

</div>
