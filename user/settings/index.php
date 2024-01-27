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
			call_user_func($settings['callback_save'], $_POST, $settings, $page_id);
		}
	}

	if (!is_errors()) {
		$_SESSION['message'] = __('Настройки успешно сохранены'); 
		ds_redirect('?do=' . $page_id); 
	}
}

$set['title'] = isset($settings['page_title']) ? text($settings['page_title']) : __('Настройки');  

get_header(); 


$action_nav = array(); 
$register = ds_get('ds_user_settings_pages', array()); 
foreach($register AS $key => $regitem) {
	$classes = array(); 
	if ($page_id == $key) {
		$classes[] = 'active'; 
	}

	$action_nav[$key] = array(
		'%link%' => '?do=' . $key, 
		'%title%' => $regitem['menu_title'], 
		'%class%' => join(' ', $classes), 
	); 
}

$default = array(
	'%before%' => '', 
	'%after%' => '', 
	'%class%' => '', 
); 

$action_nav = use_filters('ds_settings_action_nav', $action_nav); 
$template_box = use_filters('ds_template_select_box', '<div class="ds-select">%items%</div>'); 
$template_link = use_filters('ds_template_select_item', '%before%<a class="ds-select-item %class%" href="%link%">%title%</a>%after%'); 

$items = array(); 
foreach($action_nav AS $key => $value) {
	$value = array_merge($default, $value); 
	$items[] = str_replace(array_keys($value), array_values($value), $template_link); 
}

do_event('before_user_settings_action', $items);

if (!empty($items)) {
	echo str_replace(array(
		'%items%', 
	), array(
		join('', $items), 
	), $template_box); 
}

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