<?php

only_reg();

/**
* Хук работы стандартных аватаров
* Для создания собственных используйте 
* событие ds_pre_avatar_setup 
*/ 
$hook_default_avatar = use_filters('ds_avatar_default', true); 

if ($hook_default_avatar === true) {
	$file_id = (isset($_GET['file_id']) ? (int) $_GET['file_id'] : false); 

	if ($file_id) {
		$file = get_file($file_id); 

		if (!$file) {
			add_error(__('Файл не найден')); 
		} elseif ($file['user_id'] != $user['id']) {
			add_error(__('Можно использовать только свои файлы')); 
		} elseif (!is_mimetype_allowed($file['mimetype'], array('image/jpeg', 'image/png', 'image/png'))) {
			add_error(__('Недопустимый формат изображения'));  
		} else {
			update_user_meta($user['id'], '__avatar', $file['id']); 
			$_SESSION['message'] = __('Аватар успешно установлен'); 
			ds_redirect('?');  
		}
	}
}

do_event('ds_pre_avatar_setup'); 

$set['title'] = __('Параметры аватара');
get_header(); 

do_event('ds_avatar_setup'); 

if ($hook_default_avatar === true) {
	$link_setup_avatar = use_filters('link_setup_avatar', get_files_select_link('setup_avatar')); 
	?>
	<form action="?" method="POST">
		<div class="box-group-wrap box-setup_avatar">
			<div class="box-group">
				<div class="box-group-block"><?php echo avatar( $user['id'], true, 128, false ); ?></div>
				<div class="box-group-links">
					<a class="box-link" id="link_setup_avatar" href="<?php echo $link_setup_avatar; ?>"><i class="fa fa-picture-o" aria-hidden="true"></i> <?php echo __('Выбрать фото'); ?></a>
				</div>
			</div>
		</div>
	</form>
	<?php	
}

do_event('ds_after_avatar_setup'); 

get_footer(); 