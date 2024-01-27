<?php 

if (isset($_GET['p'])) {
	$parent_id = (int) $_GET['p']; 
	$parent = get_files_term($parent_id);
}

if (isset($_POST['title'])) {
	if (strlen2($_POST['title']) < 2) {
		add_error(__('Слишком короткое название')); 
	}
	if (strlen2($_POST['title']) > 64) {
		add_error(__('Слишком длинное название')); 
	}

	if (!isset($parent['term_id'])) {
		add_error(__('Error: Term parent not found')); 
	}

	if (is_files_term_depth_access($parent_id) !== true) {
		add_error(__('Превышена допустимая вложенность')); 
	}

	if (!is_errors()) {
		$result = files_term_create(array(
			'title' => $_POST['title'], 
			'parent' => $parent['term_id'], 
			'user_id' => $author_id, 
		));
		
		$mask_term = array_merge($mask, array(
		    '%term_id%' => isset($result) ? $result : 0, 
		)); 

		$_SESSION['message'] = $strings['create_term_success']; 
		ds_redirect(str_replace(array_keys($mask_term), array_values($mask_term), $ds_files_config['permalinks']['term'])); 
	}
}

$set['title'] = $strings['create_term']; 
get_header(); 

// Хлебные крошки 
ds_files_breadcrumb($parent_id, true, $mask);

$fields = array(
	array(
		'field_name' => 'title', 
		'field_type' => 'text', 
		'field_title' => $strings['edit_term_title'], 
	), 
	array(
		'field_name' => 'description', 
		'field_type' => 'textarea', 
		'field_title' => $strings['edit_term_description'], 
	), 
	array(
		'field_name' => 'save', 
		'field_type' => 'submit', 
		'field_title' => $strings['submit_create_term'], 
		'field_class' => 'button', 
	), 
); 

$fields = use_filters('pf_create_terms_fields', $fields); 

$form = new Forms(text($_SERVER['REQUEST_URI']), 'POST'); 
foreach($fields AS $field) {
	$form->add_field($field); 
}
echo $form->display(); 


get_footer(); 