<?php 

if (!isset($cdn)) {
	$token = (isset($_GET['ya_token']) ? text($_GET['ya_token']) : ''); 

	$cdn = array(
	    'ya_token' => $token, 
	    'ya_dir_upload' => '/cdn', 
        'size' => '', 
	); 	
} 

else {
	$token = $cdn['ya_token']; 
}

if ($token) {
	if (!class_exists('YandexDisk')) {
		require dirname(dirname(__FILE__)) . '/classes/class.YandexDisk.php'; 
	}

	$disk = new YandexDisk($term); 
	$info = $disk->getDisk();  

	if (isset($info['error'])) {
		echo '<div class="alert alert-error">' . $info['message'] . '</div>'; 
	} else {
		$cdn['size'] = ceil($info['total_space'] - $info['used_space']); 

		if ($cdn['size'] <= 0) {
			$cdn['size'] = 0; 
			echo '<div class="alert alert-error">' . __('Ваш диск переполнен') . '</div>'; 
		} else {
			echo '<div class="alert alert-success">' . __('Соединение с Яндекс.Диском установлено, свободное место под файлы %s', size_file($cdn['size'])) . '</div>'; 
		}		
	}
}

$forms = new Forms(get_admin_url($plugin_name, 'action=' . $action . '&type=' . $type));  
$forms->add_field(array(
    'field_name' => 'save_settings_cdn', 
    'field_value' => $action,  
    'field_type' => 'hidden', 
)); 
$forms->add_field(array(
    'field_name' => 'type', 
    'field_value' => 'yadisk',  
    'field_type' => 'hidden',  
)); 
$forms->add_field(array(
    'field_name' => 'id', 
    'field_value' => $cdn_id,  
    'field_type' => 'hidden', 
)); 


$fields = array(
    array(
        'field_title' => __('Токен'), 
        'field_name' => 'ya_token', 
        'field_after' => '<a href="https://dcms-social.com/oauth/yandex.disk.php?redirect_uri=' . urlencode(get_current_url()) . '">' . __('Получить токен') . '</a>', 
    ), 
    array(
        'field_title' => __('Директория'), 
        'field_name' => 'ya_dir_upload', 
        'field_after' => __('Папка на яндекс диске, в которую будут загружаться файлы.'), 
    ), 
    array(
        'field_name' => 'size', 
        'field_type' => 'text', 
    ), 
); 

foreach($fields AS $field) {
    $key = $field['field_name'];

    if ($key == 'size') {
        $cdn[$key] = intval($cdn[$key]) / 1024 / 1024; 
    }

    $forms->add_field(array_merge(array(
        'field_type' => 'text', 
        'field_value' => $cdn[$key], 
    ), $field));
}

$forms->button(__($cdn_id ? 'Сохранить' : 'Добавить'));
echo $forms->display();