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


//email form

if(!isset($content["mailhtml"])) {
	$content["mailhtml"] = 0;
}

?>
<div class="row g-4">

	<div class="col-12">
		<div class="alert theme-warning" role="alert"><?php echo $BL['be_deprecated_info']; ?></div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="cmailsubject"><?php echo $BL['be_cnt_subject'] ?></label>
		<input name="cmailsubject" type="text" id="cmailsubject" class="form-control" value="<?php echo  isset($content["mailsubject"]) ?  html($content["mailsubject"]) : '' ?>" size="40" maxlength="250">
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="cmailrecipient"><?php echo $BL['be_cnt_recipient'] ?></label>
		<input name="cmailrecipient" type="text" id="cmailrecipient" class="form-control" value="<?php echo  isset($content["mailrecipient"]) ? html($content["mailrecipient"]) : '' ?>" size="40" maxlength="250">
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="cmailbutton"><?php echo $BL['be_cnt_buttontext']  ?></label>
		<input name="cmailbutton" type="text" id="cmailbutton" class="form-control" value="<?php echo  isset($content["mailbutton"]) ? html($content["mailbutton"]) : '' ?>" size="20" maxlength="35">
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_cnt_sendas'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" name="cmailhtml" type="radio" id="cmailhtml0" value="0"<?php is_checked(0, $content["mailhtml"]); ?>>
				<label for="cmailhtml0"><?php echo $BL['be_cnt_text'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="cmailhtml" type="radio" id="cmailhtml1" value="1"<?php is_checked(1, $content["mailhtml"]); ?>>
				<label for="cmailhtml1"><?php echo $BL['be_cnt_html'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="cmailform"><?php echo $BL['be_cnt_formfields'] ?></label>
		<textarea name="cmailform" rows="15" wrap="off" class="form-control autosize" id="cmailform"><?php

	if(isset($content["mailform"])) {
		if(is_array($content["mailform"])) {
			foreach($content["mailform"] as $formkey => $valform) {
				echo html($content["mailform"][$formkey]['field'])."\n";
			}
		} else {
			echo html($content["mailform"]);
		}
	} else {
		echo '';
	}

	 ?></textarea>
	</div>

</div>
