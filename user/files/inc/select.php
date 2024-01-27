<?php 

$action = (isset($_GET['action']) ? text($_GET['action']) : false); 
$shoose = (isset($_GET['shoose']) ? (int) $_GET['shoose'] : false); 

$select = get_files_select_rule($action); 

if ($shoose !== false) {
	if ($select['object'] == 'file') {
		$object = get_file($shoose); 
		$object_types = get_media_type($select['files_type']); 

		if (!is_confirmed_valid('confirm', $object['id'])) {
			add_error(__('Попробуйте ещё раз')); 
		} elseif (!$object) {
			add_error(__('Файл не найден'));  
		} elseif ($object['file_type'] != $select['files_type']) {
			add_error(__('Вы не можете выбрать файл из этого раздела')); 
		} elseif ($select['access'] == 'private' && $object['user_id'] != $user['id']) {
			add_error(__('Можно использовать только свои файлы')); 
		} elseif (!is_mimetype_allowed($object['mimetype'], $select['mimetype'])) {
			add_error(__('Нельзя выбрать файл этого типа')); 
		} else { 
			if (!empty($select['redirectUrl'])) {
				$mask_link = array(
					'%file_id%' => $object['id'],
				); 

				$select['redirectUrl'] = str_replace(array_keys($mask_link), array_values($mask_link), $select['redirectUrl']); 
				ds_redirect($select['redirectUrl'], 301); 
			}

			die('ok'); 
		}
	}

	if ($select['object'] == 'term') {
		$object = get_files_term($shoose); 
	}
}


if (!$action || !$select) {
	ds_die(__('Проверьте правильность URL адреса')); 
}

$set['title'] = $strings['title_page'];
get_header(); 

do_event('ds_files_pre_output'); 

$query = new DB_Files(array(
	'files_type' => $ds_request['files_type'], 
	'user_id' => $author['id'], 
	'term_id' => $term['term_id'], 
)); 

if ($query->paged == 1) {
	$terms = get_files_terms_child($term_id); 
	$template_list = use_filters('ds_files_term_template_list_item', '<div class="%class_list%"><a class="%class_list%-link" href="%link%">%icon% <span class="%class_list%-title">%title%</span> <span class="%class_list%-counter">%counter%</span></a></div>');

	if (count($terms) > 0) {
		echo '<div class="list list-terms list-type-' . $ds_request['files_type'] . '">'; 

		foreach($terms AS $dir) {
			$mask_link = array_merge($mask, array(
			    '%term_id%' => isset($dir['term_id']) ? $dir['term_id'] : 0, 
			    '%action%' => $action,  
			));

			$mask_term = use_filters('ds_files_term_mask_list_item', array_merge($mask, array(
			    '%term_id%' => isset($dir['term_id']) ? $dir['term_id'] : 0, 
			    '%action%' => $action, 
			    '%class_list%' => 'list-item', 
			    '%counter%' => $dir['files'], 
			    '%icon%' => get_icon_html($ds_files_config['icons']['list_term']), 
			    '%shoose%' => $shoose, 
			    '%title%' => text($dir['title']), 
			    '%link%' => str_replace(array_keys($mask_link), array_values($mask_link), $ds_files_config['permalinks']['select']),  
			))); 

			echo str_replace(array_keys($mask_term), array_values($mask_term), $template_list); 
		}	

		echo '</div>'; 		
	}
}

if ($query->total) { 
	$template_list = use_filters('ds_files_file_template_list_item', '%before%<div class="%class_list%">%before_item%<input type="checkbox" name="select[]" value="%file_id%" /><a class="list-item-link" href="%link%">%thumbnail% <span class="list-item-title">%icon% %title%</span></a>%after_item%</div>%after%');

	echo '<form action="" method="POST" class="list list-files list-select list-type-' . $ds_request['files_type'] . '">';
	foreach($query->files AS $key => $file) 
	{
		$thumbnail = ''; 
		if (is_file_thumbnail($file['id'], 'thumbnail')) {
			$thumbnail = '<img class="ds-thumbnail" src="' . get_file_thumbnail_url($file['id'], 'thumbnail') . '" />'; 
		}

		$mask_link = array_merge($mask, array(
		    '%term_id%' => isset($term['term_id']) ? $term['term_id'] : 0, 
		    '%action%' => $action, 
		    '%file_id%' => $file['id'], 
		)); 

		$classes = use_filters('ds_files_list_file_classes', 'list-item ' . get_file_classes($file), $file); 

		$mask_file = use_filters('ds_files_file_mask_list_item', array_merge($mask, array(
		    '%term_id%' => isset($dir['term_id']) ? $dir['term_id'] : 0, 
		    '%file_id%' => $file['id'], 
		    '%action%' => $action, 
		    '%class_list%' => $classes,  
		    '%thumbnail%' => $thumbnail, 
		    '%icon%' => get_file_icon($file), 
		    '%title%' => text($file['title']) . '.' . get_file_ext($file), 
		    '%link%' => get_confirm_url(str_replace(array_keys($mask_link), array_values($mask_link), $ds_files_config['permalinks']['shoose']), 'confirm', $file['id']), 
		    '%before%' => '', 
		    '%after%' => '', 
		    '%before_item%' => '', 
		    '%after_item%' => '', 
		)), $file); 

		echo str_replace(array_keys($mask_file), array_values($mask_file), $template_list); 
	}
	echo '</form>'; 

	if ( $query->pages > 1 ) {
	    str('?', $query->pages, $query->paged);
	}	
}

if ($query->total == 0 && count($terms) == 0) {
	echo '<div class="list-empty">' . $strings['page_empty'] . '</div>'; 
}

get_footer(); 