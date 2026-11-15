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

//search form

// necessary JavaScript libraries
initJsAutocompleter();


if(!isset($content['search']) || !is_array($content['search'])) {
	$content['search'] = array();
}

if(empty($content['search']["text_html"])) {
	$content['search']["text_html"] = 0;
}

$content['search']["search_news"]	= empty($content['search']["search_news"]) ? 0 : 1;

if(!isset($content['search']["news_lang"])) {
	$content['search']["news_lang"] = array();
}
if(!isset($content['search']["news_category"])) {
	$content['search']["news_category"] = array();
}
if(!isset($content['search']["news_andor"])) {
	$content['search']["news_andor"] = 'OR';
}
if(empty($content['search']["news_url"])) {
	$content['search']["news_url"] = '';
}
if(empty($content["search"]["hide_summary"])) {
	$content["search"]["hide_summary"] = 0;
}
if(empty($content["search"]["highlight_result"])) {
	$content["search"]["highlight_result"] = 0;
}
if(empty($content["search"]["newwin"])) {
	$content["search"]["newwin"] = 0;
}
if(empty($content["search"]["no_filenames"])) {
	$content["search"]["no_filenames"] = 0;
}
if(empty($content["search"]["no_username"])) {
	$content["search"]["no_username"] = 0;
}
if(empty($content["search"]["no_caption"])) {
	$content["search"]["no_caption"] = 0;
}
if(empty($content["search"]["no_keyword"])) {
	$content["search"]["no_keyword"] = 0;
}
if(empty($content['search']['type'])) {
	$content['search']['type'] = 'OR';
}

?>
<div class="row g-4">

	<div class="col-12 md:col-6">
		<label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
		<select name="template" id="template" class="form-control">
<?php

	echo '<option value="">'.$BL['be_admin_tmpl_default'].'</option>'.LF;

// templates for search listing
$tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/search');
if(is_array($tmpllist) && count($tmpllist)) {
	foreach($tmpllist as $val) {
		$selected_val = (isset($content["template"]) && $val == $content["template"]) ? ' selected="selected"' : '';
		$val = html($val);
		echo '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
	}
}

?>
		</select>
	</div>

	<div class="col-12 md:col-6">
		<div class="form-label"><?php echo $BL['be_cnt_results'] ?></div>
		<div class="d-flex flex-wrap align-items-end gap-3">
			<div>
				<label class="form-label" for="csearch_result_per_page"><?php echo $BL['be_cnt_results_per_page'] ?></label>
				<input name="csearch_result_per_page" type="text" id="csearch_result_per_page" class="form-control" size="3" maxlength="5" placeholder="25" value="<?php echo $content["search"]["result_per_page"] ?? '' ?>">
			</div>
			<div>
				<label class="form-label" for="csearch_wordlimit"><?php echo $BL['be_cnt_results_wordlimit'] ?></label>
				<input name="csearch_wordlimit" type="text" id="csearch_wordlimit" class="form-control" size="3" maxlength="5" placeholder="35" value="<?php echo $content["search"]["wordlimit"] ?? '' ?>">
			</div>
			<div>
				<label class="form-label" for="csearch_minchar"><?php echo $BL['be_cnt_results_minchar'] ?></label>
				<input name="csearch_minchar" type="text" id="csearch_minchar" class="form-control" size="3" maxlength="5" placeholder="3" value="<?php echo $content["search"]["minchar"] ?? '3' ?>">
			</div>
		</div>
		<div class="d-flex flex-column gap-2 mt-3">
			<div class="form-field">
				<input class="check" name="csearch_newwin" type="checkbox" id="csearch_newwin" value="1" <?php is_checked(1, $content["search"]["newwin"]) ?>>
				<label for="csearch_newwin"><?php echo $BL['be_cnt_opennewwin'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_highlight" type="checkbox" id="csearch_highlight" value="1" <?php is_checked(1, $content["search"]["highlight_result"]) ?>>
				<label for="csearch_highlight"><?php echo $BL['be_cnt_search_highlight'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_hidesummary" type="checkbox" id="csearch_hidesummary" value="1" <?php is_checked(1, $content["search"]["hide_summary"]) ?>>
				<label for="csearch_hidesummary"><?php echo $BL['be_cnt_search_hidesummary'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csearch_start_at"><?php echo $BL['be_cnt_search_startlevel'] ?></label>
		<select name="csearch_start_at[]" size="10" multiple="multiple" class="form-control" id="csearch_start_at">
<?php
				if(!isset($content["search"]["start_at"]) || !is_array($content["search"]["start_at"])) {
					$content["search"]["start_at"] = array();
				}

				echo '<option value="0"';
				if(in_array(0, $content["search"]["start_at"])) {
					echo ' selected="selected"';
				}
				echo '>'.$BL['be_admin_struct_index'].'</option>'.LF;
				struct_select_list(0, 0, $content["search"]["start_at"]);
?>
		</select>
	</div>

	<div class="col-12 md:col-6">
		<label class="form-label" for="csearch_type"><?php echo $BL['be_cnt_search_default_type'] ?></label>
		<select name="csearch_type" id="csearch_type" class="form-control">
			<option value="OR"<?php is_selected('OR', $content['search']['type']) ?>><?php echo $BL['be_fsearch_or'] ?></option>
			<option value="AND"<?php is_selected('AND', $content['search']['type']) ?>><?php echo $BL['be_fsearch_and'] ?></option>
		</select>
	</div>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_search_searchnot'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="check" name="csearch_nofilenames" type="checkbox" id="csearch_nofilenames" value="1" <?php is_checked(1, $content["search"]["no_filenames"]) ?>>
				<label for="csearch_nofilenames"><?php echo $BL['be_fprivedit_filename'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_nousername" type="checkbox" id="csearch_nousername" value="1" <?php is_checked(1, $content["search"]["no_username"]) ?>>
				<label for="csearch_nousername"><?php echo $BL['be_article_username'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_nocaption" type="checkbox" id="csearch_nocaption" value="1" <?php is_checked(1, $content["search"]["no_caption"]) ?>>
				<label for="csearch_nocaption"><?php echo $BL['be_cnt_caption'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_nokeyword" type="checkbox" id="csearch_nokeyword" value="1" <?php is_checked(1, $content["search"]["no_keyword"]) ?>>
				<label for="csearch_nokeyword"><?php echo $BL['be_article_akeywords'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_module_search'] ?></div>
		<div class="d-flex flex-column gap-3">
			<div class="d-flex flex-wrap align-items-end gap-3">
				<div class="form-field">
					<input class="check" name="csearch_news" type="checkbox" id="csearch_news" value="1"<?php is_checked(1, $content['search']["search_news"]) ?>>
					<label for="csearch_news"><strong><?php echo $BL['be_news'] ?></strong></label>
				</div>
				<div>
					<label class="form-label" for="news_lang"><?php echo $BL['be_profile_label_lang'] ?></label>
					<input type="text" name="csearch_news_lang" id="news_lang" class="form-control w-auto" value="<?php echo html(implode(', ', $content['search']["news_lang"])) ?>">
				</div>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-3">
				<label class="form-label mb-0" for="news_category_autosuggest"><?php echo $BL['be_tags'] ?></label>
				<input type="text" id="news_category_autosuggest" class="form-control">
				<input type="hidden" name="csearch_news_category" id="csearch_news_category" value="<?php echo html(implode(', ', $content['search']["news_category"])) ?>">
				<select name="csearch_news_andor" id="news_andor" class="form-control w-auto">
					<option value="OR"<?php is_selected('OR', $content['search']['news_andor']) ?>><?php echo $BL['be_fsearch_or'] ?></option>
					<option value="AND"<?php is_selected('AND', $content['search']['news_andor']) ?>><?php echo $BL['be_fsearch_and'] ?></option>
					<option value="NOT"<?php is_selected('NOT', $content['search']['news_andor']) ?>><?php echo $BL['be_fsearch_not'] ?></option>
				</select>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-3">
				<label class="form-label mb-0" for="news_url"><?php echo $BL['be_cnt_target'].' ('.$BL['be_alias'] ?>/aid=1/id=3)</label>
				<input type="text" name="csearch_news_url" id="news_url" value="<?php echo html($content['search']["news_url"]) ?>" class="form-control">
			</div>
		</div>
	</div>

<?php

$content['search']['module_search'] = array();

// check modules for frontend search
foreach($phpwcms['modules'] as $value) {

	// check if module is fe searchable
	if($value['search'] === true && is_file($value['path'].'frontend.search.php')) {

		$value['tr']  = '<div class="form-field">';
		$value['tr'] .= '<input class="check" name="csearch_module['.$value['name'].']" type="checkbox" ';
		$value['tr'] .= 'id="csearch_module_'.$value['name'].'" value="1"';
		if( !empty( $content['search']['module'][ $value['name'] ] ) ) {
			$value['tr'] .= ' checked="checked"';
		}
		$value['tr'] .= ' />';
        $value['tr'] .= '<label for="csearch_module_'.$value['name'].'">';
		$value['tr'] .= $BL['be_ctype_module'].': '.$BL['modules'][ $value['name'] ]['backend_menu'] . '</label>';
		$value['tr'] .= '</div>';

		$content['search']['module_search'][] = $value['tr'];

	}

}

if(count($content['search']['module_search'])) {

	echo '<div class="col-12">';
	echo '<div class="form-label">' . $BL['be_ctype_module'] . '</div>';
	echo '<div class="d-flex flex-wrap gap-4">';
	echo implode(LF, $content['search']['module_search']);
	echo '</div>';
	echo '</div>';
}
?>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_searchlabeltext'] ?></div>
		<div class="d-flex flex-column gap-3">
			<div class="d-flex flex-wrap align-items-end gap-3">
				<div class="flex-grow-1">
					<label class="form-label" for="csearch_label_input"><?php echo $BL['be_cnt_input'] ?></label>
					<input name="csearch_label_input" type="text" class="form-control" id="csearch_label_input" value="<?php echo $content["search"]["label_input"] ?? '' ?>" size="10" maxlength="250">
				</div>
				<div class="flex-grow-1">
					<label class="form-label" for="csearch_style_input"><?php echo $BL['be_cnt_css_class'] ?></label>
					<input name="csearch_style_input" type="text" id="csearch_style_input" class="form-control" value="<?php echo $content["search"]["style_input"] ?? '' ?>" size="20">
				</div>
			</div>
			<div class="d-flex flex-wrap align-items-end gap-3">
				<div class="flex-grow-1">
					<label class="form-label" for="csearch_label_button"><?php echo $BL['be_cnt_buttontext'] ?></label>
					<input name="csearch_label_button" type="text" id="csearch_label_button" class="form-control" value="<?php echo $content["search"]["label_button"] ?? '' ?>" size="10" maxlength="75">
				</div>
				<div class="flex-grow-1">
					<label class="form-label" for="csearch_style_button"><?php echo $BL['be_cnt_css_class'] ?></label>
					<input name="csearch_style_button" type="text" id="csearch_style_button" class="form-control" value="<?php echo $content["search"]["style_button"] ?? '' ?>" size="20">
				</div>
			</div>
			<div class="d-flex flex-wrap align-items-end gap-3">
				<div class="flex-grow-1">
					<label class="form-label" for="csearch_label_result"><?php echo $BL['be_cnt_result'] ?></label>
					<input name="csearch_label_result" type="text" id="csearch_label_result" class="form-control" value="<?php echo  isset($content["search"]["label_result"]) ? html($content["search"]["label_result"]) : '' ?>" size="10" maxlength="250">
				</div>
				<div class="flex-grow-1">
					<label class="form-label" for="csearch_style_result"><?php echo $BL['be_cnt_css_class'] ?></label>
					<input name="csearch_style_result" type="text" id="csearch_style_result" class="form-control" value="<?php echo $content["search"]["style_result"] ?? '' ?>" size="20">
				</div>
			</div>
		</div>
	</div>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_page_of_pages'] ?></div>
		<div class="small fg-secondary"><?php echo $BL['be_cnt_page_of_pages_descr'] ?></div>
		<div class="d-flex flex-wrap gap-4 mt-3">
<?php

	  if(!isset($content["search"]["show_always"]))		$content["search"]["show_always"] 	= 1;
	  if(!isset($content["search"]["show_top"]))		$content["search"]["show_top"] 		= 1;
	  if(!isset($content["search"]["show_bottom"]))		$content["search"]["show_bottom"] 	= 1;
	  if(!isset($content["search"]["show_next"]))		$content["search"]["show_next"] 		= 1;
	  if(!isset($content["search"]["show_prev"]))		$content["search"]["show_prev"] 		= 1;

?>
			<div class="form-field">
				<input class="check" name="csearch_show_always" id="csearch_show_always" type="checkbox" value="1" <?php is_checked(1, $content["search"]["show_always"]) ?>>
				<label for="csearch_show_always"><?php echo $BL['be_cnt_search_show_forall'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_show_top" id="csearch_show_top" type="checkbox" value="1" <?php is_checked(1, $content["search"]["show_top"]) ?>>
				<label for="csearch_show_top"><?php echo $BL['be_cnt_search_show_top'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_show_bottom" id="csearch_show_bottom" type="checkbox" value="1" <?php is_checked(1, $content["search"]["show_bottom"]) ?>>
				<label for="csearch_show_bottom"><?php echo $BL['be_cnt_search_show_bottom'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_show_prev" id="csearch_show_prev" type="checkbox" value="1" <?php is_checked(1, $content["search"]["show_prev"]) ?>>
				<label for="csearch_show_prev"><?php echo $BL['be_cnt_search_show_prev'] ?></label>
			</div>
			<div class="form-field">
				<input class="check" name="csearch_show_next" id="csearch_show_next" type="checkbox" value="1" <?php is_checked(1, $content["search"]["show_next"]) ?>>
				<label for="csearch_show_next"><?php echo $BL['be_cnt_search_show_next'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12">
		<label class="form-label" for="csearch_label_pages"><?php echo $BL['be_cnt_page_of_pages'] ?></label>
		<textarea name="csearch_label_pages" rows="4" class="form-control autosize" id="csearch_label_pages"><?php echo  isset($content["search"]["label_pages"]) ? html($content["search"]["label_pages"]) : '' ?></textarea>
	</div>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_align'] ?></div>
		<div class="d-flex flex-wrap gap-4">
<?php

	  if(!isset($content["search"]["align"])) {
	  	$content["search"]["align"] = 0;
	  }

?>
			<div class="form-field">
				<input class="radio" name="csearch_align" id="csearch_align0" type="radio" value="0" <?php is_checked(0, $content["search"]["align"]) ?>>
				<label for="csearch_align0"><?php echo $BL['be_cnt_mediapos0'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="csearch_align" id="csearch_align1" type="radio" value="1" <?php is_checked(1, $content["search"]["align"]) ?>>
				<label for="csearch_align1"><?php echo $BL['be_cnt_right'] ?></label>
			</div>
			<div class="form-field">
				<input class="radio" name="csearch_align" id="csearch_align2" type="radio" value="2" <?php is_checked(2, $content["search"]["align"]) ?>>
				<label for="csearch_align2"><?php echo $BL['be_cnt_center'] ?></label>
			</div>
		</div>
	</div>

	<div class="col-12">
		<div class="form-label"><?php echo $BL['be_cnt_searchformtext'] ?></div>
		<div class="d-flex flex-wrap gap-4">
			<div class="form-field">
				<input class="radio" type="radio" name="csearch_text_html" id="csearch_text_html0" value="0"<?php echo is_checked('0', $content['search']["text_html"], 0, 0) ?>>
				<label for="csearch_text_html0">Text</label>
			</div>
			<div class="form-field">
				<input class="radio" type="radio" name="csearch_text_html" id="csearch_text_html1" value="1"<?php echo is_checked('1', $content['search']["text_html"], 0, 0) ?>>
				<label for="csearch_text_html1">HTML</label>
			</div>
		</div>
		<div class="d-flex flex-column gap-3 mt-3">
			<div>
				<label class="form-label" for="csearch_text_intro"><?php echo $BL['be_cnt_intro'] ?></label>
				<textarea name="csearch_text_intro" rows="6" class="form-control autosize" id="csearch_text_intro"><?php echo $content["search"]["text_intro"] ?? '' ?></textarea>
			</div>
			<div>
				<label class="form-label" for="csearch_text_result"><?php echo $BL['be_cnt_result'] ?></label>
				<textarea name="csearch_text_result" rows="6" class="form-control autosize" id="csearch_text_result"><?php echo $content["search"]["text_result"] ?? '' ?></textarea>
			</div>
			<div>
				<label class="form-label" for="csearch_text_noresult"><?php echo $BL['be_cnt_noresult'] ?></label>
				<textarea name="csearch_text_noresult" rows="6" class="form-control autosize" id="csearch_text_noresult"><?php echo $content["search"]["text_noresult"] ?? '' ?></textarea>
			</div>
		</div>
	</div>

</div>
<script type="text/javascript">

	$(function(){

		$("#news_category_autosuggest").autoSuggest('<?php echo PHPWCMS_URL ?>include/inc_act/ajax_connector.php', {
			selectedItemProp: "cat_name",
			selectedValuesProp: 'cat_name',
			searchObjProps: "cat_name",
			queryParam: 'value',
			extraParams: '&method=json&action=newstags',
			startText: '',
			preFill: $("#csearch_news_category").val(),
			neverSubmit: true,
			asHtmlID: 'search-news-category-autosuggest'
		});

		$('#articlecontent').submit(function(){
			$("#csearch_news_category").val($('#as-values-search-news-category-autosuggest').val());
		});

		$('#news_lang').on('keyup', function(){
			this.value = this.value.replace(/[^a-z\-, ]/g, '');
		});

	});

</script>
