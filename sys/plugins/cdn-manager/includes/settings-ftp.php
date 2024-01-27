<?php 

if (!isset($cdn)) {
	$cdn = array(
	    'ftp_server' => '', 
	    'ftp_login' => '', 
	    'ftp_password' => '', 
	    'ftp_path' => '', 
	    'ftp_url' => '', 
	    'size' => '', 
	); 	
}


$forms = new Forms(get_admin_url($plugin_name, 'action=' . $action . '&type=' . $type));  
$forms->add_field(array(
    'field_name' => 'save_settings_cdn', 
    'field_value' => $action,  
    'field_type' => 'hidden', 
)); 
$forms->add_field(array(
    'field_name' => 'type', 
    'field_value' => 'ftp',  
    'field_type' => 'hidden', 
)); 
$forms->add_field(array(
    'field_name' => 'id', 
    'field_value' => $cdn_id,  
    'field_type' => 'hidden', 
)); 


$fields = array(
    array(
        'field_title' => __('FTP адрес сервера'), 
        'field_name' => 'ftp_server', 
    ), 
    array(
        'field_title' => __('FTP логин'), 
        'field_name' => 'ftp_login', 
    ), 
    array(
        'field_title' => __('FTP пароль'), 
        'field_name' => 'ftp_password', 
    ), 
    array(
        'field_title' => __('Путь к каталогу'), 
        'field_name' => 'ftp_path', 
    ), 
    array(
        'field_title' => __('URL адрес'), 
        'field_name' => 'ftp_url', 
    ), 
    array(
        'field_title' => __('Размер хранилища (Mb)'), 
        'field_name' => 'size', 
        'field_desc' => 'Укажите размер этого хранилища в мегабайтах', 
    ), 
); 

foreach($fields AS $field) {
    $key = $field['field_name'];

    if ($key == 'size') {
        $cdn[$key] = (intval($cdn[$key]) / 1024 / 1024); 
    }

    $forms->add_field(array_merge(array(
        'field_type' => 'text', 
        'field_value' => $cdn[$key], 
    ), $field));
}

$forms->button(__($cdn_id ? 'Сохранить' : 'Добавить'));
echo $forms->display();