<?php 

function jw_theme_settings() {
	$default = array(
		'preset' => md5('default'), 
		'logotype' => '<strong>CMS-Social</strong> <span>v3</span>', 
		'copyright' => 'CMS-Social v3', 
	); 

	$options = get_option('jw_settings', array()); 

	if ($options) {
		$options = json_decode($options, true); 
	}

	return array_merge($default, $options); 
}

function jw_get_colors($str) {
	preg_match_all('/#([A-z0-9]{3,6})/', $str, $matches); 

	$colors = array(); 
	if (!empty($matches[0])) {
		$colors = array_unique($matches[0]); 
	}

	asort($colors); 

	return $colors; 
}

function jw_theme_presets() 
{
	$path = get_theme_directory(); 
	$list = ds_readdir_files_list($path); 

	$headers = array(
		'name' => 'Preset Name',
		'primary' => 'Primary Color',
	); 

	$info = array(
		md5('default') => array(
			'name' => __t('Стандартный', LANGUAGE_DOMAIN), 
			'colors' => jw_get_colors(file_get_contents(get_theme_directory() . '/style.css')), 
			'primary' => '#43ade7', 
		), 
	); 

	foreach($list AS $file) {
		$ext = strtolower(substr($file, strrpos($file, '.') + 1)); 
		$uri = str_replace(ROOTPATH, '', $file); 
		$hash = md5($uri); 

		if ($ext == 'css') {
			$str = file_get_contents($file); 

            foreach($headers AS $key => $value) {
                if (preg_match('|' . $value . ' ?: ?(.*)$|mi', $str, $matches)) {
                    $info[$hash][$key] = text($matches[1]);
                }
            }

            if (isset($info[$hash]['name'])) {
            	$info[$hash]['url'] = get_site_url($uri); 
            	$info[$hash]['colors'] = jw_get_colors($str); 
            }
		}
	}

	return $info; 
}

//add_event('ds_personal_files_init', 'justweb_register_templates'); 
function justweb_register_templates() 
{
	add_filter('ds_files_template_add_box', 'justweb_files_pre_output');
	add_event('ds_files_output', 'justweb_files_output', 10, 2); 
	add_event('ds_files_after_output', 'justweb_files_output_after', 10, 2); 
}

function justweb_files_pre_output($html) 
{
	$html = '<div class="block__flex"><div class="block__flex-action">' . $html; 
	return $html; 
}

function justweb_files_output($term = null) 
{
	if (empty($term)) return ; 
	if ($term['user_id'] == get_user_id())
		echo '</div><div class="block__flex-content">'; 
}

function justweb_files_output_after($term = null) 
{
	if (empty($term)) return ; 
	if ($term['user_id'] == get_user_id())
	echo '</div></div>'; 
}


/**
* Заменяет стандартный HTML код аватара 
* при его отсутствии
*/ 

add_filter('ds_template_no_avatar', 'jw_get_template_noavatar', 10, 2); 
function jw_get_template_noavatar($html, $user_id, $size) 
{
	$user = get_user($user_id);

	/**
	* Для аватаров в постах
	*/ 
	if ($size == 'thumbnail') {
		// Берем первую букву ника
		$word = mb_substr($user['nick'], 0, 1,'UTF-8');

		// Цвет на основе символа
		$backgroundColor = '#' . substr(md5($user['nick']), 0, 6); 

		// Вместо изображения выводим текст
		$html = '<span class="%class%" style="background-color: ' . $backgroundColor . '">' . $word . '</span>'; 		
	}

	else {

		// Выводим No Avatar по полу пользователя
		$image_url = get_theme_uri() . '/images/avatar_' . ($user['pol'] == 1 ? 'man' : 'woman') . '.png'; 

		$html = '<img class="%class%" src="' . $image_url . '" />';
	}

	return $html; 
}

add_filter('ds_get_avatar', 'jw_get_avatar', 10, 3); 
function jw_get_avatar($html, $user_id, $size) 
{
	$user = get_user($user_id); 

	if ($user['date_last'] + 60 > time()) {
		$active = 'on'; 
	} elseif ($user['date_last'] + 180 > time()) {
		$active = 'out'; 
	} else {
		$active = 'off'; 
	}

	$template = '<span class="wrapper-avatar wrapper-avatar-' . $size . '" data-active="' . $active . '" data-user="' . $user['id'] . '" data-browser="' . text($user['browser']) . '">' . $html . '</span>'; 

	return $template; 
}