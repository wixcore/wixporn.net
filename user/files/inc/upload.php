<?php 

if (!is_user()) {
	die(__('У вас нет доступа')); 
}

do_event('ds_upload_init', $author); 

// Типы файлов доступные для выгрузки
$accept_files = use_filters('ds_files_accept_files', $ds_files_config['accept']);

/**
* Загрузка нескольких файлов
*/
if (!empty($_FILES['files'])) {
	array_files_multiple($_FILES['files']); 

	$_FILES['files'] = use_filters('ds_pre_upload_files', $_FILES['files']); 

	$uploadFiles = array(); 
	foreach($_FILES['files'] AS $file) {
		$uploadFiles[] = file_handle_upload($file, $term_id);
	}

	$jsonResponse['files'] = use_filters('json_files_uploaded', $uploadFiles); 

	if (is_ajax()) {
		die(json_encode($jsonResponse));
	}

	if (!is_errors()) {
		$_SESSION['message'] = $strings['upload_files_success']; 	
		ds_redirect(get_files_term_link($term_id), 301); 		
	}
}

/**
* Загрузка одиночного файла
*/
if (!empty($_FILES['file'])) {
	$_FILES['file'] = use_filters('ds_pre_upload_file', $_FILES['file']); 
	$uploadFile = file_handle_upload($_FILES['file'], $term_id);
	$jsonResponse['file'] = use_filters('json_file_uploaded', $uploadFile); 

	if (is_ajax()) {
		die(json_encode($jsonResponse));
	}

	if (!is_errors()) {
		$_SESSION['message'] = $strings['upload_files_success']; 	
		ds_redirect(get_files_term_link($term_id), 301);
	}
}

// Заголовок страницы загрузки файлов
$set['title'] = use_filters('ds_upload_title', $strings['title_upload']);

get_header(); 

// Хлебные крошки 
ds_files_breadcrumb($term_id, true, $mask);
?>
<form class="ds-uploader" action="<?php echo get_current_url(); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" id="upload_max_size" value="<?php echo get_upload_max_filesize($term); ?>" />

	<label class="ds-uploader-file">
		<span class="ds-uploader-select">Выбрать файлы</span>
		<span class="ds-uploader-info"><?php echo __('Файл не должен превышать %s', size_file(get_upload_max_filesize($term))); ?></span>
		<input class="upload-ajax" name="files[]" type="file" accept="<?php echo join(',', $accept_files); ?>" multiple="multiple" />
	</label>

	<div class="ds-files-list"></div>

	<button class="button" type="submit"><?php echo __('Загрузить'); ?></button>  
</form>
<?
get_footer(); 