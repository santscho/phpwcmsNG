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

//WYSIWYG

$cnt_fieldgroup_templates = array();
if(isset($template_default['settings']['wysiwyg_custom_fields']) && is_array($template_default['settings']['wysiwyg_custom_fields']) && count($template_default['settings']['wysiwyg_custom_fields'])) {
    $cnt_fieldgroups = $template_default['settings']['wysiwyg_custom_fields'];
    foreach($template_default['settings']['wysiwyg_custom_fields'] as $key => $cnt_fieldgroup) {
        if(empty($cnt_fieldgroup['template'])) {
            continue;
        }
        $cnt_fieldgroup_templates[ $cnt_fieldgroup['template'] ] = $key;
    }
} else {
    $cnt_fieldgroups = array();
}

$cnt_fieldgroups_active = $cnt_fieldgroup_templates['default'] ?? '';

?>
<div class="row g-4">

    <div class="col-12 md:col-6">
        <label class="form-label" for="template"><?php echo $BL['be_admin_struct_template']; ?></label>
        <select name="template" id="template" class="form-control"<?php if(count($cnt_fieldgroups)): ?> onchange="return toggleTabsTemplate(this);"<?php endif; ?>>
<?php

    echo '<option value=""'.(empty($content["template"]) ? ' selected="selected"' : '').'>'.$BL['be_admin_tmpl_default'].'</option>'.LF;

    $tmpllist = get_tmpl_files(PHPWCMS_TEMPLATE.'inc_cntpart/wysiwyg');
    if(is_array($tmpllist) && count($tmpllist)) {
        foreach($tmpllist as $val) {
            if(isset($content["template"]) && $val === $content["template"]) {
                $selected_val = ' selected="selected"';
                $cnt_fieldgroups_active = $cnt_fieldgroup_templates[$val] ?? '';
            } else {
                $selected_val = '';
            }

            $val = html($val);
            echo "  " . '<option value="' . $val . '"' . $selected_val . '>' . $val . '</option>' . LF;
        }
    }

?>
        </select>
    </div>

    <div class="col-12">
<?php

$wysiwyg_editor = array(
    'value'     => $content["html"] ?? '',
    'field'     => 'chtml',
    'height'    => '400px',
    'width'     => '100%',
    'rows'      => '15',
    'editor'    => $_SESSION["WYSIWYG_EDITOR"],
    'lang'      => 'en'
);
include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';

?>
    </div>

<?php

if($cnt_fieldgroups_active && isset($template_default['settings']['wysiwyg_custom_fields'][$cnt_fieldgroups_active])) {
    $cnt_fieldgroup =& $template_default['settings']['wysiwyg_custom_fields'][$cnt_fieldgroups_active];
} else {
    $cnt_fieldgroup = null;
}
if($cnt_fieldgroup !== null && isset($cnt_fieldgroup['fields']) && is_array($cnt_fieldgroup['fields']) && count($cnt_fieldgroup['fields'])) {
    $custom_cnt_fields = array_keys($cnt_fieldgroup['fields']);
} else {
    $custom_cnt_fields = array();
    $cnt_fieldgroup = null;
}

$content['custom_field_items'] = $custom_cnt_fields;
$custom_cnt_fields_hidden = array();
$custom_cnt_field_types = array('str', 'textarea', 'option', 'select', 'int', 'float', 'bool');

if(isset($content['custom_fields']) && is_array($content['custom_fields']) && count($content['custom_fields'])) {

    if(count($custom_cnt_fields)) {
        $content['custom_field_items'] = array_unique( array_merge($custom_cnt_fields, array_keys($content['custom_fields'])) );
    } else {
        $content['custom_field_items'] = array_keys($content['custom_fields']);
    }

} else {

    $content['custom_field_items'] = $custom_cnt_fields;

}

if($content['custom_field_items']):

    foreach($content['custom_field_items'] as $custom_field_key => $custom_field):

        // send fields not defined as hidden values, should ensure not loosing values
        if(!isset($cnt_fieldgroup['fields'][$custom_field]) && isset($content['custom_fields'][$custom_field])) {
            // do not store if the value is an empty string
            if($content['custom_fields'][$custom_field] !== '') {
                $custom_cnt_fields_hidden[] = '<input type="hidden" name="customfield['.$custom_field.']" value="'.html($content['custom_fields'][$custom_field]).'" />';
            }
            continue;
        }

        // support only type "str" or "textarea" at the moment
        if(empty($cnt_fieldgroup['fields'][$custom_field]['type']) || !in_array($cnt_fieldgroup['fields'][$custom_field]['type'], $custom_cnt_field_types)) {
            $cnt_fieldgroup['fields'][$custom_field]['type'] = 'str';
        }

        $custom_field_id = 'customfield_' . $custom_field;
        $custom_field_placeholder = isset($cnt_fieldgroup['fields'][$custom_field]['placeholder']) && $cnt_fieldgroup['fields'][$custom_field]['placeholder'] !== '' ? ' placeholder="'.html($cnt_fieldgroup['fields'][$custom_field]['placeholder']).'"' : '';
        $is_wysiwyg = $cnt_fieldgroup['fields'][$custom_field]['type'] === 'textarea' && !empty($cnt_fieldgroup['fields'][$custom_field]['render']) && $cnt_fieldgroup['fields'][$custom_field]['render'] === 'wysiwyg';
        $custom_field_legend = isset($cnt_fieldgroup['fields'][$custom_field]['legend']) ? html($cnt_fieldgroup['fields'][$custom_field]['legend']) : $BL['be_custom_textfield'].' #'.($custom_field_key+1);

        if($cnt_fieldgroup['fields'][$custom_field]['type'] === 'bool'):
?>
    <div class="col-12 md:col-6 tab-collapsable-row">
        <div class="form-field">
            <input class="check" type="checkbox" name="customfield[<?php echo $custom_field; ?>]" id="<?php echo $custom_field_id; ?>" value="1"<?php
                if((!empty($content['custom_fields'][$custom_field])) || (!isset($content['custom_fields'][$custom_field]) && !empty($cnt_fieldgroup['fields'][$custom_field]['default']))):
            ?> checked="checked"<?php endif; ?>>
            <label for="<?php echo $custom_field_id; ?>"><?php echo isset($cnt_fieldgroup['fields'][$custom_field]['legend']) ? html($cnt_fieldgroup['fields'][$custom_field]['legend']) : ''; ?></label>
        </div>
    </div>
<?php
        elseif($is_wysiwyg):
?>
    <div class="col-12 tab-collapsable-row">
        <div class="form-label"><?php echo $custom_field_legend; ?></div>
<?php
                $wysiwyg_editor = array(
                    'value'     => $content['custom_fields'][$custom_field] ?? '',
                    'field'     => 'customfield['.$custom_field.']',
                    'height'    => empty($cnt_fieldgroup['fields'][$custom_field]['height']) ? '150px' : $cnt_fieldgroup['fields'][$custom_field]['height'],
                    'width'     => '100%',
                    'rows'      => empty($cnt_fieldgroup['fields'][$custom_field]['rows']) ? '5' : $cnt_fieldgroup['fields'][$custom_field]['rows'],
                    'editor'    => $_SESSION["WYSIWYG_EDITOR"],
                    'lang'      => 'en'
                );
                include PHPWCMS_ROOT.'/include/inc_lib/wysiwyg.editor.inc.php';
?>
    </div>
<?php
        else:
?>
    <div class="col-12 md:col-6 tab-collapsable-row">
        <label class="form-label" for="<?php echo $custom_field_id; ?>"><?php echo $custom_field_legend; ?></label>
<?php
        if($cnt_fieldgroup['fields'][$custom_field]['type'] === 'str'): ?>
        <input type="text" name="customfield[<?php echo $custom_field; ?>]" id="<?php echo $custom_field_id; ?>" value="<?php
                if(isset($content['custom_fields'][$custom_field])) { echo html($content['custom_fields'][$custom_field]); }
                ?>"<?php if(!empty($cnt_fieldgroup['fields'][$custom_field]['maxlength'])): ?> maxlength="<?php echo $cnt_fieldgroup['fields'][$custom_field]['maxlength']; ?>"<?php endif; ?>
                class="form-control"<?php echo $custom_field_placeholder; ?>>
<?php   elseif($cnt_fieldgroup['fields'][$custom_field]['type'] === 'int' || $cnt_fieldgroup['fields'][$custom_field]['type'] === 'float'): ?>
        <input type="number" name="customfield[<?php echo $custom_field; ?>]" id="<?php echo $custom_field_id; ?>" value="<?php
                echo $content['custom_fields'][$custom_field] ?? 0;
                ?>" class="form-control" <?php echo $custom_field_placeholder; ?>
                <?php if(!empty($cnt_fieldgroup['fields'][$custom_field]['min'])): ?> min="<?php echo $cnt_fieldgroup['fields'][$custom_field]['min']; ?>" <?php endif; ?>
                <?php if(!empty($cnt_fieldgroup['fields'][$custom_field]['max'])): ?> max="<?php echo $cnt_fieldgroup['fields'][$custom_field]['max']; ?>" <?php endif; ?>
                <?php if(!empty($cnt_fieldgroup['fields'][$custom_field]['step'])): ?> step="<?php
                    if($cnt_fieldgroup['fields'][$custom_field]['type'] === 'int') {
                        $cnt_fieldgroup['fields'][$custom_field]['step'] = ceil($cnt_fieldgroup['fields'][$custom_field]['step']);
                    } else {
                        $cnt_fieldgroup['fields'][$custom_field]['step'] = floatval($cnt_fieldgroup['fields'][$custom_field]['step']);
                        $cnt_fieldgroup['fields'][$custom_field]['step'] = rtrim(number_format($cnt_fieldgroup['fields'][$custom_field]['step'], 14 - log10($cnt_fieldgroup['fields'][$custom_field]['step'])), '0');
                    }
                    echo $cnt_fieldgroup['fields'][$custom_field]['step'];
                ?>"
                <?php endif; ?>
                >
<?php   elseif($cnt_fieldgroup['fields'][$custom_field]['type'] === 'textarea'): ?>
        <textarea name="customfield[<?php echo $custom_field; ?>]" id="<?php echo $custom_field_id; ?>" class="form-control autosize"<?php echo $custom_field_placeholder; ?> rows="<?php
                    echo empty($cnt_fieldgroup['fields'][$custom_field]['rows']) ? '3' : $cnt_fieldgroup['fields'][$custom_field]['rows'];
                ?>"><?php if(isset($content['custom_fields'][$custom_field])) { echo html($content['custom_fields'][$custom_field]); } ?></textarea>
<?php
        elseif($cnt_fieldgroup['fields'][$custom_field]['type'] === 'option' && !empty($cnt_fieldgroup['fields'][$custom_field]['values'])): ?>
        <div class="d-flex flex-wrap gap-4">
<?php       foreach($cnt_fieldgroup['fields'][$custom_field]['values'] as $option_key => $option_label):
                $custom_option_id = $custom_field_id . '_' . substr(md5((string) $option_key), 0, 8); ?>
            <div class="form-field">
                <input class="radio" type="radio" name="customfield[<?php echo $custom_field; ?>]" id="<?php echo $custom_option_id; ?>" value="<?php echo ($option_key === 'empty' ? '' : html($option_key)); ?>"<?php
                        if(isset($content['custom_fields'][$custom_field]) && $content['custom_fields'][$custom_field] === $option_key):
                    ?> checked="checked"<?php
                        elseif(empty($content['custom_fields'][$custom_field]) && !empty($cnt_fieldgroup['fields'][$custom_field]['default']) && $cnt_fieldgroup['fields'][$custom_field]['default'] === $option_key):
                    ?> checked="checked"<?php endif; ?>>
                <label for="<?php echo $custom_option_id; ?>"><?php echo html($option_label); ?></label>
            </div>
<?php       endforeach; ?>
        </div>
<?php   elseif($cnt_fieldgroup['fields'][$custom_field]['type'] === 'select' && !empty($cnt_fieldgroup['fields'][$custom_field]['values'])): ?>
        <select name="customfield[<?php echo $custom_field; ?>]" id="<?php echo $custom_field_id; ?>" class="form-control">
<?php       foreach($cnt_fieldgroup['fields'][$custom_field]['values'] as $option_key => $option_label): ?>
            <option value="<?php echo ($option_key === 'empty' ? '' : html($option_key)); ?>"<?php
                        if(isset($content['custom_fields'][$custom_field]) && $content['custom_fields'][$custom_field] === $option_key):
                    ?> selected="selected"<?php
                        elseif(empty($content['custom_fields'][$custom_field]) && !empty($cnt_fieldgroup['fields'][$custom_field]['default']) && $cnt_fieldgroup['fields'][$custom_field]['default'] === $option_key):
                    ?> selected="selected"<?php endif; ?>><?php echo html($option_label); ?></option>
<?php       endforeach; ?>
        </select>
<?php   endif; ?>
    </div>
<?php
        endif;
    endforeach;
endif;
?>

    <div class="col-12">
        <input type="hidden" name="cnt_fieldgroup" value="<?php echo $cnt_fieldgroups_active; ?>">
<?php
        if(count($custom_cnt_fields_hidden)) {
            echo implode('', $custom_cnt_fields_hidden);
        }
        if(count($cnt_fieldgroups)): ?>
    <script>
    function toggleTabsTemplate(e) {
		if(confirm('<?php echo correct_charset($BL['be_tab_template_toggle_warning'], true); ?>')) {
			e.form.submit();
			return true;
		}
		return false;
	}
	</script><?php endif; ?>
    </div>

</div>
