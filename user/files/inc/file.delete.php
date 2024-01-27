<?php 

$file = get_file($file_id); 

if ($file_id && !$file) {
    p404();
}

if (!is_user_access('loads_file_delete') && get_user_id() != $file['user_id']) {
	ds_die(__('У вас нет доступа')); 
}

if (is_confirmed_valid('confirm', $file['id'])) {
	ds_file_delete($file); 
	
	if (!is_errors()) { 
		$_SESSION['message'] = $strings['delete_file_success']; 
	 	ds_redirect(get_files_term_link($term_id), 301);
	}
}

$set['title'] = $strings['edit_file'] . ' - ' . text($file['title']); 

get_header(); 

$mask_file = array_merge($mask, array(
    '%file_name%' => text($file['title']), 
)); 
?>
<div class="box-group-wrap">
	<div class="box-group">
		<div class="box-group-block">
			<?php echo str_replace(array_keys($mask_file), array_values($mask_file), $strings['msg_confirm_file_delete']); ?>
		</div>
	</div>

	<div class="box-group">
		<div class="box-group-links box-group-center">
			<a class="box-link" href="<?php echo get_confirm_url(get_file_link_delete($file), 'confirm', $file['id']); ?>"><i class="fa fa-check" aria-hidden="true"></i> <?php echo __('Да'); ?></a>
			<a class="box-link" href="<?php echo get_file_link($file); ?>"><i class="fa fa-close" aria-hidden="true"></i> <?php echo __('Нет'); ?></a>
		</div>
	</div>
</div>
<?

get_footer(); 