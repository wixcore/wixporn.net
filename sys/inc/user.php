<?php

if (!isset($_SERVER['HTTP_REFERER'])) {
   $_SERVER['HTTP_REFERER'] = '/index.php'; 
}

// если аккаунт не активирован
if (isset($user['activation']) && $user['activation'] != NULL) {
	$err[] = __('Вам необходимо активировать Ваш аккаунт на <a href="%s">этой странице</a>, высланный на ваш Email, указанный при регистрации', get_site_url('/reg.php?action=activation'));
	unset($user);
}

if (isset($user)) {
	if (!is_page_admin()) {
		$timeactiv  =  time() - $user['date_last'];
		
		if ($timeactiv < 120) {
			add_user_update('time', $user['time'] + $timeactiv); 
		}
	}

   // бан пользователя
	if (strpos($_SERVER['REQUEST_URI'], '/ban.php') === false) {
		if (db::count("SELECT COUNT(*) FROM `ban` WHERE `user_id` = '$user[id]' AND `time_until` > '$time'") != 0 ) {
			ds_redirect(get_site_url('/ban.php'));
		}
	}

	// Записываем url 
	add_user_update('url', $_SERVER['SCRIPT_NAME']); 
	
	// Тип браузера
	add_user_update('browser', ($webbrowser == true ? "web" : "wap")); 

	// Пишем ip пользователя
	if (isset($ip2['add']))add_user_update('ip', ip2long($ip2['add'])); 
	else add_user_update('ip', NULL); 
	if (isset($ip2['cl']))add_user_update('ip_cl', ip2long($ip2['cl'])); 
	else add_user_update('ip_cl', NULL); 
	if (isset($ip2['xff']))add_user_update('ip_xff', ip2long($ip2['xff'])); 
	else add_user_update('ip_xff', NULL); 
	if ($ua)add_user_update('ua', $ua);  
	
} else {
	unset($access);
}

if (!isset($user) || $user['level'] == 0) {
	error_reporting(0);
	ini_set('display_errors', false); 
}

if (!isset($user) && $set['guest_select'] == '1' && strpos($_SERVER['REQUEST_URI'], '/aut.php') === false) {
	ds_redirect(get_site_url('/aut.php')); 
} 