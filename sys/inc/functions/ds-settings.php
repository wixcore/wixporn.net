<?php 

function default_user_settings() 
{
    $default = use_filters('ds_user_settings_default', array(
        'general' => array(
            'page_title' => __('Основные настройки'), 
            'menu_title' => __('Основные'), 
            'callback' => 'inc/settings-general.php',
            'callback_save' => 'save_user_settings_general',
            'default' => array(
                'p_str' => 20, 
                'site_language' => 'ru_RU', 
            ),
        ), 
    )); 

    foreach($default AS $page_id => $args) {
        add_user_settings_page($page_id, $args); 
    }
}

function add_user_settings_page($page_id, $args) 
{
    $set_pages = ds_get('ds_user_settings_pages', array()); 

    if (!isset($set_pages[$page_id])) {
        $set_pages[$page_id] = $args; 
    }

    ds_set('ds_user_settings_pages', $set_pages);
}

function get_user_settings_page($page_id) 
{
    $set_pages = ds_get('ds_user_settings_pages', array()); 

    if (isset($set_pages[$page_id])) {
        return $set_pages[$page_id]; 
    }

    return false;
}

function get_user_options($user_id, $setting_id) 
{
    $cache = ds_get('ds_users_settings', array());

    if (isset($cache[$user_id][$setting_id])) {
        return $cache[$user_id][$setting_id]; 
    }
    
    $result = db::fetch("SELECT * FROM `user_options` WHERE `user_id` = '" . $user_id . "' AND `setting_id` = '" . $setting_id . "' LIMIT 1");  

    $opt = array(); 
    if ($result) {
        $opt = unserialize($result['options']); 
    }

    $set_page = get_user_settings_page($setting_id); 

    if ($set_page && is_array($set_page['default'])) {
        $opt = array_replace($set_page['default'], $opt); 
    }

    $cache[$user_id][$setting_id] = $opt; 
    ds_set('ds_users_settings', $cache); 

    return $opt; 
}

function update_user_options($user_id, $setting_id, $options = array()) 
{
    $result = db::fetch("SELECT * FROM `user_options` WHERE `user_id` = '" . $user_id . "' AND `setting_id` = '" . $setting_id . "' LIMIT 1");  

    if (isset($result['id'])) {
        db::update('user_options', array(
            'options' => serialize($options), 
        ), array(
            'user_id' => $user_id, 
            'setting_id' => $setting_id, 
        ));
    } else {
        db::insert('user_options', array(
            'user_id' => $user_id, 
            'setting_id' => $setting_id, 
            'options' => serialize($options), 
        ));
    }
}

function save_user_settings_general($post, $settings)  
{
    $page_id = 'general'; 
    $opt = get_user_options(get_user_id(), $page_id); 

    if ($post['p_str'] >= 1 && $post['p_str'] <= 100) {
        $opt['p_str'] = $post['p_str']; 
    }

    $languages = get_core_languages(); 
    $values = array(); 
    foreach($languages AS $lang) {
        $values[] = $lang['code']; 
    }

    if (in_array($post['site_language'], $values)) {
        $opt['site_language'] = $post['site_language'];  
    }

    $opt = use_filters('ds_user_settings_' . $page_id . '_save', $opt); 

    if ($opt) {
        update_user_options(get_user_id(), $page_id, $opt); 
    }

    if (!is_errors()) {
        $_SESSION['message'] = __('Настройки успешно сохранены'); 
        ds_redirect('?do=' . $page_id); 
    }
}