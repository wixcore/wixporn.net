<?php 

require dirname(dirname(dirname(__FILE__))) . '/sys/inc/core.php';

if (!is_user()) {
	ds_die(__('Вы не авторизованы')); 
}

do_event('ds_user_settings', $user); 

$page_id = (isset($_GET['do']) ? $_GET['do'] : 'general'); 
$settings = get_user_settings_page($page_id); 

if (empty($settings)) {
	ds_die(__('Страница настроек не найдена')); 
}

$opt = get_user_options($user['id'], $page_id); 

if (isset($_POST['save_settings'])) {
	if (is_string($settings['callback_save'])) {
		if (is_file($settings['callback_save'])) {
			require $settings['callback_save']; 
		} elseif (is_callable($settings['callback_save'])) {
			call_user_func($settings['callback_save'], $_POST, $settings);
		}
	}
}

$set['title'] = isset($settings['page_title']) ? text($settings['page_title']) : __('Настройки');  

get_header(); 

do_event('before_user_settings_output');

if (is_string($settings['callback'])) { 
	if (is_file($settings['callback'])) {
		require $settings['callback']; 
	} elseif (is_callable($settings['callback'])) {
		call_user_func($settings['callback'], array($settings));
	}
}

do_event('after_user_settings_output');

get_footer(); 