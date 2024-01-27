<?php 

if ($term['parent'] == 0 && $term['path'] == 0) {
	ds_die(__('У вас нет доступа')); 
} elseif (!is_user_access('loads_file_delete') && get_user_id() != $term['user_id']) {
	ds_die(__('У вас нет доступа')); 
}

if (is_confirmed_valid('confirm', $term['term_id'])) {
	files_term_delete($term); 

	if (!is_errors()) { 
		$_SESSION['message'] = $strings['delete_term_success']; 
		ds_redirect(get_files_term_link($term['parent']), 301);
	}
}

$set['title'] = $strings['delete_term'] . ' - ' . text($term['title']); 

get_header(); 

$mask_term = array_merge($mask, array(
    '%term_name%' => text($term['title']), 
)); 
?>
<div class="box-group-wrap">
	<div class="box-group">
		<div class="box-group-block">
			<?php echo str_replace(array_keys($mask_term), array_values($mask_term), $strings['msg_confirm_term_delete']); ?>
		</div>
	</div>

	<div class="box-group">
		<div class="box-group-links box-group-center">
			<a class="box-link" href="<?php echo get_confirm_url(get_files_term_link($term, 'delete'), 'confirm', $term['term_id']); ?>"><i class="fa fa-check" aria-hidden="true"></i> <?php echo __('Да'); ?></a>
			<a class="box-link" href="<?php echo get_files_term_link($term); ?>"><i class="fa fa-close" aria-hidden="true"></i> <?php echo __('Нет'); ?></a>
		</div>
	</div>
</div>
<?

get_footer(); 