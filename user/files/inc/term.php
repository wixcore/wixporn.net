<?php 

$set['title'] = $strings['title_page'];

get_header(); 

do_event('ds_files_pre_output'); 

$action_add = array(); 

$default = array(
	'%class%' => '', 
	'%link%' => '', 
	'%title%' => '', 
	'%before%' => '', 
	'%after%' => '', 
); 

$default = use_filters('ds_files_action_add_default', $default); 

// Хлебные крошки 
ds_files_breadcrumb($term_id, false, $mask);

if ($author['id'] == $user['id'] && is_files_term_depth_access($term_id) === true) {
	$action_add['create'] = array(
		'%token%' => get_uniquie_token($term_id),
		'%link%' => $permalinks['create_term'], 
		'%title%' => $strings['create_term'], 
		'%icon%' => get_icon_html($ds_files_config['icons']['create_term']), 
	); 	
}

if ($author['id'] == $user['id']) {
	$action_add['upload'] = array(
		'%token%' => get_uniquie_token($term_id),
		'%link%' => $permalinks['upload'], 
		'%title%' => $strings['upload_file'], 
		'%icon%' => get_icon_html($ds_files_config['icons']['upload_file']), 
	);	
}

$action_add = use_filters('ds_files_action_add', $action_add); 

if (count($action_add) > 0) {
	$template_link = use_filters('ds_files_template_add_item', '%before%<a class="%class%" href="%link%">%icon%%title%</a>%after%'); 
	$template_box = use_filters('ds_files_template_add_box', '<div class="ds-actions ds-actions-%count%">%items%</div>'); 

	$action_items = array(); 
	foreach($action_add AS $key => $value) {
		$value = array_merge($default, $value); 
		$action_items[] = str_replace(array_keys($value), array_values($value), $template_link); 
	}

	if (!empty($action_items)) {
		echo str_replace(array(
			'%count%', 
			'%items%', 
		), array(
			count($action_items), 
			join('', $action_items), 
		), $template_box); 
	}
}

$query = new DB_Files(array(
	'files_type' => $ds_request['files_type'], 
	'user_id' => $author['id'], 
	'term_id' => $term['term_id'], 
)); 

do_event('ds_files_output', $term); 

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
			    '%title%' => text($dir['title']), 
			    '%link%' => str_replace(array_keys($mask_link), array_values($mask_link), $ds_files_config['permalinks']['term']), 
			))); 

			echo str_replace(array_keys($mask_term), array_values($mask_term), $template_list); 
		}	

		echo '</div>'; 		
	}
}

if ($query->total == 0 && count($terms) == 0) {
	echo '<div class="list-empty">' . $strings['page_empty'] . '</div>'; 
}

if ($query->total) {
	$template_list = use_filters('ds_files_file_template_list_item', '%before%<div class="%class_list%">%before_item%<a class="list-item-link" href="%link%">%thumbnail% <span class="list-item-title">%icon% %title%</span></a>%after_item%</div>%after%');

	echo '<div class="list list-files list-type-' . $ds_request['files_type'] . '">';
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
		    '%link%' => get_file_link($file), 
		    '%before%' => '', 
		    '%after%' => '', 
		    '%before_item%' => '', 
		    '%after_item%' => '', 
		)), $file); 

		echo str_replace(array_keys($mask_file), array_values($mask_file), $template_list); 
	}
	echo '</div>'; 

	if ( $query->pages > 1 ) {
	    str('?', $query->pages, $query->paged);
	}
}

do_event('ds_files_after_output', $term); 

$actions_term = array(); 

if ($author['id'] == $user['id'] && ($term['parent'] != 0 && $term['path'] != 0)) {
	$actions_term['edit'] = array(
		'%link%' => $permalinks['edit_term'], 
		'%title%' => $strings['edit_term'], 
		'%icon%' => get_icon_html($ds_files_config['icons']['edit_term']), 
	); 	
}

if (count($actions_term) > 0) {
	$template_link = use_filters('ds_files_template_edit_item', '%before%<a class="%class%" href="%link%">%icon%%title%</a>%after%'); 
	$template_box = use_filters('ds_files_template_edit_box', '<div class="ds-actions ds-actions-%count%">%items%</div>'); 

	$action_items = array(); 
	foreach($actions_term AS $key => $value) {
		$value = array_merge($default, $value); 
		$action_items[] = str_replace(array_keys($value), array_values($value), $template_link); 
	}

	if (!empty($action_items)) {
		echo str_replace(array(
			'%count%', 
			'%items%', 
		), array(
			count($action_items), 
			join('', $action_items), 
		), $template_box); 
	}
}

get_footer(); 