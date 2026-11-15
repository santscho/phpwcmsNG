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

// frontend login

if(!isset($content['felogin']['felogin_cookie_expire'])) {
	$content['felogin']['felogin_cookie_expire'] = 2592000;
}
if(empty($content['felogin']['felogin_date_format'])) {
	$content['felogin']['felogin_date_format'] = '%m/%d/%y';
}
if(empty($content['felogin']['felogin_locale'])) {
	$content['felogin']['felogin_locale'] = '';
}
if(!isset($content['felogin']['felogin_validate_backenduser'])) {
	$content['felogin']['felogin_validate_backenduser']	= 1;
}
if(!isset($content['felogin']['felogin_validate_userdetail'])) {
	$content['felogin']['felogin_validate_userdetail']	= 1;
}
if(!isset($content['felogin']['felogin_profile_registration'])) {
	$content['felogin']['felogin_profile_registration']	= 1;
}
if(!isset($content['felogin']['felogin_profile_manage'])) {
	$content['felogin']['felogin_profile_manage'] = 1;
}
if(!isset($content['felogin']['felogin_reminder_subject'])) {
	$content['felogin']['felogin_reminder_subject']	= '';
}
if(!isset($content['felogin']['felogin_reminder_body'])) {
	$content['felogin']['felogin_reminder_body'] = '';
}
if(!isset($content['felogin']['felogin_profile_manage_redirect'])) {
	$content['felogin']['felogin_profile_manage_redirect']= '';
}
if(!isset($content['felogin']['felogin_accept_email_login'])) {
    $content['felogin']['felogin_accept_email_login'] = 0;
}

?>
<div class="row g-4">

	<div class="col-12 md:col-6">
		<label class="form-label" for="template"><?php echo $BL['be_admin_struct_template'] ?></label>
		<select name="template" id="template" class="form-control">
<?php

// templates for frontend login
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/felogin');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		$selected_val = (isset($content['felogin_template']) && $val == $content['felogin_template']) ? ' selected="selected"' : '';
		$val = html($val);
		echo "\t" . '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
	}
}

?>
		</select>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_settings'] ?></div>
		<div class="row g-3">
			<div class="col-12">
				<label class="form-label" for="cookie_expire"><?php echo $BL['be_cookie_runtime'] ?></label>
				<div class="d-flex align-items-center gap-2">
					<input name="cookie_expire" type="text" class="form-control w-auto" id="cookie_expire" size="10" maxlength="10" onkeyup="if(!parseInt(this.value,10))this.value='0';" value="<?php echo $content['felogin']['felogin_cookie_expire']; ?>">
					<span class="small fg-secondary"><?php echo $BL['be_cnt_guestbook_seconds'] ?></span>
				</div>
			</div>
			<div class="col-12">
				<label class="form-label" for="date_format"><?php echo $BL['be_date_format'] ?></label>
				<div class="d-flex align-items-center gap-2">
					<input name="date_format" type="text" class="form-control w-auto" id="date_format" value="<?php echo $content['felogin']['felogin_date_format']; ?>">
					<a href="http://www.php.net/strftime" target="_blank" class="link small" data-bs-toggle="tooltip" title="PHP strftime">(?)</a>
				</div>
			</div>
			<div class="col-12">
				<label class="form-label" for="locale"><?php echo $BL['be_locale'] ?></label>
				<div class="d-flex align-items-center gap-2">
					<input name="locale" type="text" class="form-control w-auto" id="locale" value="<?php echo $content['felogin']['felogin_locale']; ?>">
					<a href="http://www.php.net/setlocale" target="_blank" class="link small" data-bs-toggle="tooltip" title="PHP setlocale">(?)</a>
					<span class="small fg-secondary">(en, de_DE)</span>
				</div>
			</div>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_check_login_against'] ?></div>
		<div class="d-flex flex-column gap-2">
			<div class="form-field">
				<input class="check" type="checkbox" name="validate_userdetail" id="validate_userdetail" value="1"<?php echo is_checked(1, $content['felogin']['felogin_validate_userdetail']); ?>>
				<label for="validate_userdetail"><?php echo $BL['be_userprofile_db'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" type="checkbox" name="validate_backenduser" id="validate_backenduser" value="1"<?php echo is_checked(1, $content['felogin']['felogin_validate_backenduser']); ?>>
				<label for="validate_backenduser"><?php echo $BL['be_backenduser_db']?></label>
			</div>
			<div class="form-field">
				<input class="check" type="checkbox" name="accept_email_login" id="accept_email_login" value="1"<?php echo is_checked(1, $content['felogin']['felogin_accept_email_login']); ?>>
				<label for="accept_email_login"><?php echo $BL['be_check_login_allow_email']; ?></label>
			</div>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_check_feuser_profile'] ?></div>
		<div class="d-flex flex-column gap-2">
			<div class="form-field">
				<input class="check" type="checkbox" name="profile_registration" id="profile_registration" value="1"<?php echo is_checked(1, $content['felogin']['felogin_profile_registration']); ?>>
				<label for="profile_registration"><?php echo $BL['be_check_feuser_registration'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" type="checkbox" name="profile_manage" id="profile_manage" value="1"<?php echo is_checked(1, $content['felogin']['felogin_profile_manage']); ?>>
				<label for="profile_manage"><?php echo $BL['be_check_feuser_manage'] ?></label>
			</div>
			<div>
				<div class="d-flex align-items-center gap-2">
					<input name="profile_manage_redirect" type="text" class="form-control w-auto" id="profile_manage_redirect" size="10" value="<?php echo html($content['felogin']['felogin_profile_manage_redirect']); ?>">
					<span class="small fg-secondary"><?php echo $BL['be_alias'], ', aid=ID', ', id=ID' ?></span>
				</div>
			</div>
		</div>
	</div>

</div>
