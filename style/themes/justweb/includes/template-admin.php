<?php 

add_event('ds_admin_settings_justweb_init', 'jw_admin_options_save'); 
function jw_admin_options_save() {
	if (!isset($_POST['justweb_save'])) {
		return ;
	}

	$styles = jw_theme_presets();
	$options = jw_theme_settings(); 

	foreach($options AS $key => $value) {
		if (isset($_POST[$key])) {
			$options[$key] = $_POST[$key]; 
		}
	}

	update_option('jw_presets', $styles, 'justweb'); 
	update_option('jw_settings', $options, 'justweb'); 

	$_SESSION['message'] = __t('Настройки сохранены', LANGUAGE_DOMAIN); 
}