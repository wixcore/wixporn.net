<?php 

$file = get_file($file_id); 

if ($file_id && !$file) {
    p404();
}

if (!is_user_access('user_files_edit') && get_user_id() != $file['user_id']) {
	ds_die(__('У вас нет доступа')); 
}

if (isset($_POST['save'])) 
{ 
	$fields = use_filters('save_edit_file_fields', array(
		'title' => isset($_POST['title']) ? db::esc($_POST['title']) : '', 
		'description' => isset($_POST['description']) ? db::esc($_POST['description']) : '', 
		'comment' => isset($_POST['comment']) ? $_POST['comment'] : '', 
	)); 

	$access_list = get_files_accesses(); 

	if (!isset($access_list[$fields['comment']])) {
		ds_die(__('Хмм.. Что-то пошло не так..')); 
	}

	if (strlen2($_POST['title']) < 1) {
		add_error(__('Слишком короткое название')); 
	}
	if (strlen2($_POST['title']) > 64) {
		add_error(__('Слишком длинное название')); 
	}

	if (!is_errors()) { 
		do_event('save_edit_file', $file); 
		db::update('files', $fields, array('id' => $file['id']));

		$_SESSION['message'] = __('Изменения успешно приняты'); 
		ds_redirect(get_current_url()); 
	}
}

$set['title'] = $strings['edit_file'] . ' - ' . text($file['title']); 

get_header(); 

$fields = array(
	array(
		'field_name' => 'title', 
		'field_type' => 'text', 
		'field_title' => $strings['edit_file_title'], 
		'field_value' => text($file['title']), 
	), 
	array(
		'field_name' => 'description', 
		'field_type' => 'textarea', 
		'field_title' => $strings['edit_file_description'], 
		'field_value' => text($file['description']), 
	),
	array(
		'field_name' => 'comment', 
		'field_type' => 'radio', 
		'field_title' => $strings['edit_file_comment'], 
		'field_value' => text($file['comment']), 
		'field_values' => get_files_accesses(), 
	),
	array(
		'field_name' => 'save', 
		'field_type' => 'submit', 
		'field_title' => $strings['submit_save_file'], 
	), 
); 

$fields = use_filters('pf_edit_file_fields', $fields); 

$form = new Forms(get_current_url(), 'POST'); 
foreach($fields AS $field) {
	$form->add_field($field); 
}
?>
<div class="box-group-wrap">
	<div class="box-group">
		<div class="box-group-form">
			<?php echo $form->display(); ?>
		</div>
	</div>

	<div class="box-group">
		<div class="box-group-links">
			<a class="box-link" href="<?php echo get_file_link_delete($file); ?>"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo $strings['delete_file']; ?></a>
		</div>
	</div>
</div>
<?

get_footer(); 