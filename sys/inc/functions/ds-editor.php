<?php 

function get_editor($name = 'msg', $value = '', $attr = array()) {
	$hash = md5(get_salt() . $name); 

	if (!isset($attr['hash'])) {
		$attr['hash'] = $hash; 
	}

	if (!isset($attr['placeholder'])) {
		$attr['placeholder'] = ''; 
	}

	do_event('ds_pre_editor_init', $hash);

	$ds_editor = '<div class="ds-editor">';
	$ds_button = array(); 
	$ds_button['b'] = '<a href="javascript:tag(\'[b]\', \'[/b]\', \'' . $hash . '\')"><i class="icon icon-bold"></i></a>'; 
	$ds_button['i'] = '<a href="javascript:tag(\'[i]\', \'[/i]\', \'' . $hash . '\')"><i class="icon icon-italic"></i></a>'; 
	$ds_button['u'] = '<a href="javascript:tag(\'[u]\', \'[/u]\', \'' . $hash . '\')"><i class="icon icon-underline"></i></a>'; 
	$ds_button['url'] = '<a href="javascript:tag(\'[url=]\', \'[/url]\', \'' . $hash . '\')"><i class="icon icon-link"></i></a>'; 
	$ds_button['color'] = '<a href="javascript:colorpicker(\'' . $hash . '\', \'color\')"><i class="icon icon-text-color"></i></a>'; 
	$ds_button['fon'] = '<a href="javascript:colorpicker(\'' . $hash . '\', \'fon\')"><i class="icon icon-color"></i></a>'; 
	$ds_button['code'] = '<a href="javascript:tag(\'[code]\', \'[/code]\', \'' . $hash . '\')"><i class="icon icon-code"></i></a>'; 

	$ds_button = use_filters('ds_editor_panel_buttons', $ds_button, $hash, $name); 
	$ds_smiles = use_filters('ds_editor_panel_smiles', get_list_emoji_html($hash)); 

	$ds_editor .= '<div class="ds-editor-panel">' . join(' ', $ds_button) . '</div>';
	$ds_editor .= '<div class="ds-editor-smiles">' . $ds_smiles . '</div>';
	$ds_editor .= '<div class="ds-editor-modal" id="ds_editor_modal" style="display: none;"></div>';
	$ds_editor .= use_filters('ds_editor_textarea_before', ''); 
	$ds_editor .= '<textarea class="ds-editor-textarea" name="' . $name . '" id="' . $hash . '" placeholder="' . $attr['placeholder'] . '" data-hash="' . $attr['hash'] . '">' . $value . '</textarea>';
	$ds_editor .= use_filters('ds_editor_textarea_after', ''); 
	$ds_editor .= '<div class="ds-editor-helper" data-hash="' . $hash . '"></div>';
	$ds_editor .= '<input type="hidden" name="ds_editor_name" value="' . $name . '" />';
	$ds_editor .= '<input type="hidden" name="ds_editor_hash" value="' . $hash . '" />';
	$ds_editor .= '</div>';

	do_event('ds_editor_init', $hash); 
	
	return use_filters('ds_editor_textarea', $ds_editor, $name, $value, $hash);
}

function ds_editor($name = 'msg', $value = '', $attr = array()) 
{
	echo get_editor($name, $value, $attr); 
}

/**
* Устаревшее
*/
function ds_editor_post() 
{
	if (isset($_POST['ds_editor_name']) && isset($_POST['ds_editor_hash'])) {
		$key = $_POST['ds_editor_name']; 
		$hash = $_POST['ds_editor_hash']; 

		$verify = $hash = md5(get_salt() . $key); 

		if ($verify == $hash) {
			$string = $_POST[$key]; 

			$tags = array(
				'bold' => use_filters('ds_editor_replace_bold', array('/<(\/?strong)>/sU' => '[$1]')), 
				'italic' => use_filters('ds_editor_replace_italic', array('/<(\/?i)>/sU' => '[$1]')), 
				'paragraph' => use_filters('ds_editor_replace_paragraph', array('/<(\/?p)>/sU' => '[$1]')), 
				'blockquote' => use_filters('ds_editor_replace_blockquote', array('/<(\/?blockquote)>/sU' => '[$1]')), 
				'link' => use_filters('ds_editor_replace_link', array('/<a.*href="(.*)".*>(.*)<\/a>/sU' => '[url="$1"]$2[/url]')), 
			); 

			foreach($tags AS $key => $replace) {
				$string = preg_replace(key($replace), $replace[key($replace)], $string); 
			}

			$_POST[$key] = $string; 
		}
		
	}
}