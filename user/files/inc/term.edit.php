<?php 

if ($term['parent'] == 0 && $term['path'] == 0) {
	ds_die(__('У вас нет доступа')); 
} elseif (!is_user_access('user_files_edit') && get_user_id() != $term['user_id']) {
	ds_die(__('У вас нет доступа')); 
}

$set['title'] = $strings['edit_term']; 

if (isset($_POST['title'])) {
	if (strlen2($_POST['title']) < 2) {
		add_error(__('Слишком короткое название')); 
	}

	if (strlen2($_POST['title']) > 64) {
		add_error(__('Слишком длинное название')); 
	}

	if (!is_errors()) {
		files_term_update($term, array(
			'title' => $_POST['title'], 
			'description' => $_POST['description'], 
		));

		$mask_term = array_merge($mask, array(
		    '%term_id%' => $term['term_id'], 
		)); 

		$_SESSION['message'] = $strings['edit_term_success']; 
		ds_redirect(get_files_term_link($term['term_id']), 301); 
	}
}

get_header(); 

// Хлебные крошки 
ds_files_breadcrumb($term_id, true, $mask);

$fields = array(
	array(
		'field_name' => 'title', 
		'field_type' => 'text', 
		'field_title' => $strings['edit_term_title'], 
		'field_value' => text($term['title']), 
	), 
	array(
		'field_name' => 'description', 
		'field_type' => 'textarea', 
		'field_title' => $strings['edit_term_description'], 
		'field_value' => text($term['description']), 
	), 
	array(
		'field_name' => 'save', 
		'field_type' => 'submit', 
		'field_title' => $strings['submit_save_term'], 
		'field_class' => 'button', 
	), 
); 

$fields = use_filters('ds_files_edit_term_fields', $fields); 

$form = new Forms(text($_SERVER['REQUEST_URI']), 'POST'); 
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

	<?php if (is_user_access('user_files_delete') || get_user_id() == $term['user_id']) : ?>
	<div class="box-group">
		<div class="box-group-links">
			<a class="box-link" href="<?php echo get_files_term_link($term, 'delete'); ?>"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo $strings['delete_term']; ?></a>
		</div>
	</div>
	<?php endif; ?>
</div>
<?
get_footer(); 