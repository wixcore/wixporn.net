<?php 

function justweb_user_settings_save($data, $set, $page_id) 
{
	$opt = array(); 
	foreach($set['default'] AS $key => $value) {
		$opt[$key] = (isset($data[$key]) ? $data[$key] : $value); 
	}

	update_user_options(get_user_id(), $page_id, $opt); 
	return true; 
}


add_event('ds_umenu_settings', 'justweb_umenu_settings', 10); 
function justweb_umenu_settings() {
	?>
    <div class="box-group-links">
        <a class="box-link" href="<?php echo get_site_url('/user/settings/?do=justweb'); ?>"><i class="fa fa-paint-brush"></i> <?php echo __t('Тема оформления', LANGUAGE_DOMAIN); ?></a>
    </div>
    <?
}

add_event('ds_init', 'justweb_user_settings', 10); 
function justweb_user_settings() 
{
	if (!is_user()) {
		return ;
	}

	$args = array(
	    'page_title' => __t('Настройки темы', LANGUAGE_DOMAIN), 
        'menu_title' => __t('Тема', LANGUAGE_DOMAIN), 
        'callback' => get_theme_directory() . '/templates/user-theme-settings.php',
        'callback_save' => 'justweb_user_settings_save',
        'default' => array(
        	'preset' => md5('default'), 
        ),
	); 
	add_user_settings_page('justweb', $args);
}
