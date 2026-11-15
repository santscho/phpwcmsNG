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

/**
 * Render @@Text@@ based on browser language and store in related language file
 * which allows easy translation at later time and when needed. The Text between
 * @@Default@@ will be taken as default text if no translation exists.
 *
 * Based on work and ideas of
 *   Dr.-Ing. Tobias Schittkowski (http://www.schittkowski.de/index.php?q=node/20)
 *   André Rabold (http://smarty.incutio.com/?page=SmartyMultilanguageSupport)
 **/

// language which is selected by user (defined in the browser settings)
function i18n_get_language($complex=false) {
	global $phpwcms;
	if(!empty($phpwcms['i18_lang'])) {
		return $phpwcms['i18_lang'];
	} elseif(empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$phpwcms['i18_lang'] = $phpwcms['default_lang'];
		return $phpwcms['i18_lang'];
	}
	$complex = $phpwcms['i18n_complex'] ?? $complex;
	if($complex) {
		$lang = explode(';', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']), 2);
		$lang = explode(',', $lang[0], 2);
		$phpwcms['i18_lang'] = trim($lang[0]);
	} else {
		$phpwcms['i18_lang'] = substr(trim($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
	}
	if($phpwcms['i18_lang'] === '*') { // Any language
    	$phpwcms['i18_lang'] = $phpwcms['default_lang'];
    	return $phpwcms['i18_lang'];
	}
	$phpwcms['i18_lang'] = preg_replace('/[^a-z\-_]/', '', strtolower($phpwcms['i18_lang']));
	if(empty($phpwcms['i18_lang'])) {
		$phpwcms['i18_lang'] = $phpwcms['default_lang'];
	}
	return $phpwcms['i18_lang'];
}
// get the template file name
function i18n_get_filename() {
	return PHPWCMS_TEMPLATE . 'template_lang/' . i18n_get_language(true) . '.php';
}
function i18n_get_file_open_text() {
	$text  = '<?php' . LF;
	$text .= '// phpwcms template language file "' . i18n_get_language(true) . '" (' . now('Y-m-d H:i:s') . ')' . LF;
	$text .= '// ATTENTION! Never add the closing PHP tag "? >" at the end of this file!' . LF . LF;
	return $text;
}
// A generated language file is one PHP header followed by plain token entries.
function i18n_generated_file_is_valid($content) {
	return strpos($content, '<?php') === 0 && preg_match_all('/^<\?php/m', $content) === 1;
}
// Create the language file with its header. Mode "x" creates exclusively, so of
// several requests reaching a brand new language only one writes the header and
// the others cannot append a second PHP open tag.
function i18n_create_file() {
	$f = i18n_get_filename();
	if($handle = @fopen($f, 'x')) {
		fwrite($handle, i18n_get_file_open_text());
		fflush($handle);
		fclose($handle);
		return true;
	}
	return is_readable($f);
}
// Load the token list of the active language, creating or repairing the file.
function i18n_load_file() {
	global $i18n_tokens;
	$f = i18n_get_filename();
	if(!is_readable($f) && !i18n_create_file()) {
		return false;
	}
	$content = @file_get_contents($f);
	if($content === false) {
		return false;
	}
	if(!i18n_generated_file_is_valid($content)) {
		// A file with more than one PHP header cannot be included at all. It came
		// from an append without a lock, its entries were unusable, so start over.
		@file_put_contents($f, i18n_get_file_open_text(), LOCK_EX);
		return true;
	}
	include $f;
	return true;
}
// substitutes a single token
function i18n_substitute_text_token($token) {
	global $i18n_tokens, $phpwcms;
	$a = trim($token[1] ?? $token);
    if($a === '') {
        return '';
    }
	// Translation tokens are plain text labels, never code or binary data.
	// Reject control characters and oversized tokens so they cannot end up in
	// the generated language file.
	$max_length = isset($phpwcms['i18n_token_max_length']) ? (int)$phpwcms['i18n_token_max_length'] : 1024;
	if(($max_length > 0 && mb_strlen($a, 'UTF-8') > $max_length) || preg_match('/[\x00-\x1F\x7F]/', $a)) {
		return $a;
	}
	if(isset($i18n_tokens[$a])) {
		return $i18n_tokens[$a];
	} else {
		$f = i18n_get_filename();
		if(!i18n_load_file()) {
			return $a;
		}
		if(isset($i18n_tokens[$a])) {
			return $i18n_tokens[$a];
		}
		// Stop appending once the language file exceeds the configured limit.
		// Defaults to 1 MiB, 0 disables the limit.
		$max_size = isset($phpwcms['i18n_lang_file_max_size']) ? (int)$phpwcms['i18n_lang_file_max_size'] : 1048576;
		if($max_size > 0 && is_file($f) && filesize($f) > $max_size) {
			return $a;
		}

		$i18n_tokens[$a] = $a;
		// var_export() generates a valid PHP literal for both the array key and the
		// value. Hand-made escaping broke on backslashes and allowed PHP code
		// injection into the generated language file (CWE-94/-95/-96).
		$export = var_export($a, true);
		$s = '$i18n_tokens[' . $export . '] = ' . $export . '; // NEW ' . now('Y-m-d H:i:s') . LF;
		@file_put_contents($f, $s, FILE_APPEND | LOCK_EX);
	}
	return $a;
}
// all contents starting and ending with @@ are replaced
function i18n_substitute_text($tpl_output) {
	global $i18n_tokens;
	if(!isset($i18n_tokens)) {
		i18n_load_file();
	}
	return preg_replace_callback('/@@(.+?)@@/', 'i18n_substitute_text_token', $tpl_output);
}
