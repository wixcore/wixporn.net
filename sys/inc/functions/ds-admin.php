<?php 

function admin_log($mod, $act, $opis)
{
    global $user;
    $q = db::query("SELECT * FROM `admin_log_mod` WHERE `name` = '" . my_esc( $mod ) . "' LIMIT 1");
    if (  $q->fetch_row() == 0 ) {
        db::query("INSERT INTO `admin_log_mod` (`name`) VALUES ('" . my_esc( $mod ) . "')");
        $id_mod = db::insert_id();
    } else
        $id_mod = $q->fetch_row();
    $q2 = db::query("SELECT * FROM `admin_log_act` WHERE `name` = '" . my_esc( $act ) . "' AND `id_mod` = '$id_mod' LIMIT 1");
    if (  $q2->fetch_row() == 0 ) {
        db::query("INSERT INTO `admin_log_act` (`name`, `id_mod`) VALUES ('" . my_esc( $act ) . "', '$id_mod')");
        $id_act = db::insert_id();
    } else
        $id_act = $q2->fetch_row();
    db::query("INSERT INTO `admin_log` (`time`, `id_user`, `mod`, `act`, `opis`) 
                  VALUES ('" . time() . "','$user[id]', '$id_mod', '$id_act', '" . my_esc( $opis ) . "')");
}

function is_page_admin() {
    if (strpos($_SERVER['REQUEST_URI'], '/adm_panel/') !== false) {
        return true; 
    }
    return false; 
}

function get_admin_url($url, $query = '') {
    if (preg_match('/^([A-z\-\_]+)$/m', $url)) {
        $url = 'index.php?admin=' . $url;
    }    

    if ($query) {
        $url = get_query_url($query, $url); 
    }

    return $url; 
}

/**
* Функция добавляет пункт меню в админку
*/ 

function add_menu_admin($menu_title, $url, $access = 'adm_info', $icon = '', $position = 10, $page_title = '', $callback = '')
{
    $admin_menu = ds_get('ds_admin_menu', array()); 
    $admin_menu[] = array(
        'title'      => $menu_title,  
        'page_title' => $page_title,  
        'position'   => $position, 
        'access'     => $access, 
        'icon'       => $icon,
        'url'        => get_admin_url($url), 
        'function'   => $callback, 
    ); 
    ds_set('ds_admin_menu', $admin_menu);
    
    $classesMenu = ds_get('ds_admin_menu_classes', array()); 
    $is_current_menu = strpos(get_current_url(), get_admin_url($url)); 

    if ($is_current_menu !== false && $is_current_menu > 0) {
        $classesMenu[get_admin_url($url)] = true; 
    }

    ds_set('ds_admin_menu_classes', $classesMenu);

    if (!empty($callback)) {
        $admin_pages = ds_get('ds_admin_pages', array()); 
        $admin_pages[get_admin_url($url)] = array(
            'title' => $page_title, 
            'access' => $access, 
            'function' => $callback,  
        ); 
        ds_set('ds_admin_pages', $admin_pages);
    }
}

/**
* Функция добавляет подпункт меню в админку
*/ 

function add_submenu_admin($menu_title, $url, $access = 'adm_info', $icon = '', $position = 10, $menu_parent = '', $page_title = '', $callback = '')
{
    $menu_parent = get_admin_url($menu_parent); 

    $admin_submenu = ds_get('ds_admin_submenu', array()); 
    $admin_submenu[$menu_parent][] = array(
        'title'      => $menu_title, 
        'page_title' => $page_title, 
        'position'   => $position,
        'access'     => $access, 
        'icon'       => $icon,
        'url'        => get_admin_url($url), 
        'function'   => $callback, 
        'parent'     => $menu_parent,
    ); 
    ds_set('ds_admin_submenu', $admin_submenu);

    $classesMenu = ds_get('ds_admin_menu_classes', array()); 

    $is_current_menu = strpos(get_current_url(), get_admin_url($url)); 
    if ($is_current_menu !== false) {
        $classesMenu[get_admin_url($url)] = true; 
        $classesMenu[$menu_parent] = true; 
    }

    ds_set('ds_admin_menu_classes', $classesMenu);


    if (!empty($callback)) {
        $admin_pages = ds_get('ds_admin_pages', array()); 
        $admin_pages[get_admin_url($url)] = array(
            'title' => $page_title, 
            'access' => $access, 
            'function' => $callback, 
        ); 
        ds_set('ds_admin_pages', $admin_pages);
    }
}

/**
* Возвращает меню админки 
*/ 
function get_admin_menu() 
{
    $menu = ds_get('ds_admin_menu', array()); 
    $submenu = ds_get('ds_admin_submenu', array()); 
    $classes = ds_get('ds_admin_menu_classes', array()); 

    foreach($menu AS $key => $item) {
        if (isset($classes[$item['url']])) {
            $menu[$key]['class'] = 'active'; 
        }
        if (!empty($submenu[$item['url']])) {
            $menu[$key]['submenu'] = $submenu[$item['url']]; 

            foreach($menu[$key]['submenu'] AS $s_key => $s_value) {
                if (isset($classes[$s_value['url']])) {
                    $menu[$key]['submenu'][$s_key]['class'] = 'active'; 
                }
            }
        }
    }

    return $menu; 
}

/**
* Функция возвращает зарегистрированную страницу админки
*/ 
function get_admin_page($page_name) 
{
    $admin_pages = ds_get('ds_admin_pages', array()); 

    if (isset($admin_pages[$page_name])) {
        return $admin_pages[$page_name]; 
    }

    return false; 
}

function ds_admin_menu_load() 
{
    add_menu_admin(__('Перейти на сайт'), get_site_url(), 'adm_panel_show', 'fa-home', -1); 
    add_menu_admin(__('Пользователи'), 'users.php', 'adm_users_list', 'fa-users', 50); 
    add_submenu_admin(__('Группы пользователей'), 'accesses.php', 'adm_accesses', 'fa-sliders', 80, 'users.php');

    add_menu_admin(__('Темы оформления'), 'themes.php', 'adm_themes', 'fa-paint-brush', 60); 
    add_submenu_admin(__('Виджеты'), 'widgets.php', 'adm_themes', 'fa-th-list', 20, 'themes.php');

    add_menu_admin(__('Настройки'), 'settings.php?page=general', 'adm_set_sys', 'fa-cogs', 80); 
    add_menu_admin(__('Плагины'), 'plugins.php', 'plugins', 'fa-plug', 80); 
    add_menu_admin(__('О системе'), 'info.php', 'adm_info', 'fa-life-ring', 99); 

    do_event('ds_admin_menu_load'); 
}

function ds_admin_menu() 
{
    $admin_menu = use_filters('ds_admin_menu', get_admin_menu()); 
    $Menu = new Menu(); 
    echo '<div class="admin-menu">';
    echo $Menu->get_template_menu($admin_menu); 
    echo '</div>';
}

/**
* Вывод сообщений в админке
*/ 
function ds_admin_messages() 
{
    do_event('pre_admin_output_messages');

    if (isset($_SESSION['message'])) {
        do_event('ds_admin_messages_output');
        echo use_filters('ds_admin_message_filter', '<div class="alert alert-message">' . $_SESSION['message'] . '</div>'); 
        $_SESSION['message'] = NULL; 
    }
}

/**
* Вывод сообщений ошибок в админке
*/ 
function ds_admin_errors() 
{
    $template = use_filters('ds_admin_template_error', '<div class="alert alert-error">%error</div>'); 

    do_event('pre_admin_output_errors');
    $errors = get_errors(); 
    
    foreach($errors AS $error) {
        echo str_replace('%error', $error, $template); 
    }
    do_event('ds_admin_errors_output');
}


function add_settings_section($page_id, $section_id, $args = array()) 
{
    $sections = ds_get('ds_admin_options_section', array()); 

    if (!isset($sections[$page_id][$section_id])) {
        $sections[$page_id][$section_id] = $args; 
    }

    ds_set('ds_admin_options_section', $sections);
}

function get_settings_sections($page_id) 
{
    $sections = ds_get('ds_admin_options_section', array()); 

    if (isset($sections[$page_id])) {
        return $sections[$page_id]; 
    }
}

function get_settings_fields($page_id, $section_id) 
{
    $fields = ds_get('ds_settings_fields', array()); 

    if (isset($fields[$page_id][$section_id])) {
        return $fields[$page_id][$section_id]; 
    }

    return array(); 
}

function add_settings_page($page_id, $page_title, $access = 'adm_set_sys', $callback = '', $menu_title = '', $parent_settings = true) 
{
    $set_pages = ds_get('ds_admin_settings_pages', array()); 

    if (!isset($set_pages[$page_id])) {
        $set_pages[$page_id] = array(
            'id' => $page_id, 
            'page_title' => $page_title, 
            'access' => $access, 
            'function' => $callback, 
            'menu_title' => $menu_title, 
        ); 

        if ($parent_settings === true) {
            add_submenu_admin($menu_title, 'settings.php?page=' . $page_id, $access, 'fa-cog', 10, 'settings.php?page=general'); 
        }
    }

    ds_set('ds_admin_settings_pages', $set_pages);
}

function get_settings_page($page_id) 
{
    $set_pages = ds_get('ds_admin_settings_pages', array()); 

    if (isset($set_pages[$page_id])) {
        return $set_pages[$page_id]; 
    }

    return false;
}

function do_settings_section($section_id) 
{
    global $page_id; 
}

function do_settings_fields($page_id) 
{
    $page = get_settings_page($page_id); 

    if (is_callable($page['function'])) {
        add_event('do_settings_fields', $page['function']); 
    }

    do_event('do_settings_fields', $page_id); 

    $sections = get_settings_sections($page_id); 
    
    if (!is_array($sections)) {
        return ;
    }

    $forms = new Forms(); 
    $forms->add_field(array(
        'field_name' => 'save_settings', 
        'field_value' => '1', 
        'field_type' => 'hidden', 
    )); 

    foreach($sections AS $section_id => $section) {
        $fields = get_settings_fields($page_id, $section_id);
        
        foreach($fields AS $field) {
            if ($field) {
                $value = get_option($field['field_name']); 
                if (empty($value)) {
                    $value = $field['field_default']; 
                }

                $forms->add_field(array_merge($field, array(
                    'field_value' => str_replace('%value', $value, $field['field_value']), 
                    'field_checked' => get_option($field['field_name'], '0') == '0' ? 0 : 1, 
                ))); 
                $forms->add_field(array_merge($field, array(
                    'field_name' => '_' . $field['field_name'], 
                    'field_value' => hash('sha256', $field['field_name'] . ':' . SALT_FORMS_FIELDS), 
                    'field_type' => 'hidden',
                ))); 
            }
        }
    }
    
    $forms->button(__('Сохранить'));
    echo $forms->display();
}

function add_settings_field($page_id, $section_id, $args = array()) 
{
    $default = array(
        'title'         => '', 
        'type'          => 'text', 
        'name'          => '', 
        'description'   => '', 
        'default'       => '', 
        'value'         => '%value', 
        'checked'       => '%value', 
        'after'         => '', 
        'before'        => '', 
        'placeholder'   => '', 
        'attr'          => array(), 
        'values'        => array(), 
    );

    $args = array_merge($default, $args); 

    $settings_fields = ds_get('ds_settings_fields', array());
    $settings_fields[$page_id][$section_id][] = array(
        'field_placeholder' => $args['placeholder'], 
        'field_before'      => $args['before'], 
        'field_title'       => $args['title'], 
        'field_value'       => $args['value'], 
        'field_default'     => $args['default'], 
        'field_name'        => $args['name'], 
        'field_type'        => $args['type'], 
        'field_desc'        => $args['description'], 
        'field_after'       => $args['after'], 
        'field_attr'        => $args['attr'], 
        'field_values'      => $args['values'], 
        'field_checked'     => $args['checked'], 
    );  

    ds_set('ds_settings_fields', $settings_fields);
}

function ds_admin_settings_load() 
{
    $set = get_system(); 

    add_settings_page('general', __('Основные настройки сайта'), 'adm_set_sys', '', '', false); 

    add_settings_section('general', 'settings_general', array(
        'option_type' => 'autoload', 
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Название сайта'), 
        'name' => 'title', 
        'value' => text($set['title']), 
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Описание (meta: description)'), 
        'name' => 'meta_description', 
        'type' => 'textarea', 
        'value' => text($set['meta_description']), 
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Пунктов на страницу'), 
        'name' => 'p_str', 
        'value' => abs(intval($set['p_str'])), 
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('E-mail для BackUp'), 
        'name' => 'mail_backup', 
        'value' => text($set['mail_backup']), 
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Перенаправлять HTTP-запросы в HTTPS'), 
        'name' => 'https', 
        'type' => 'checkbox', 
        'value' => 1, 
        'checked' => '%value', 
        'description' => '* Автоматически перенаправлять HTTP-запросы к WWW-домену в безопасное HTTPS-соединение', 
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Ошибки интерпретатора'), 
        'name' => 'show_err_php', 
        'type' => 'select', 
        'values' => array(
            array(
                'title' => __('Показывать администрации'), 
                'value' => 1, 
            ), 
            array(
                'title' => __('Скрывать'), 
                'value' => 0, 
            ), 
        )
    )); 

    $languages = get_core_languages(); 
    $values = array(); 
    foreach($languages AS $lang) {
        if (is_translations('core', $lang['code']) || $lang['code'] == 'ru_RU') {
            $values[] = array(
                'title' => $lang['native_name'],
                'value' => $lang['code'],
            );             
        }
    }

    add_settings_field('general', 'settings_general', array(
        'title' => __('Язык сайта'), 
        'name' => 'site_language', 
        'type' => 'select', 
        'values' => $values, 
        'value' => get_option('site_language', 'ru_RU'),
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Регистрация пользователей'), 
        'name' => 'reg_select', 
        'type' => 'select', 
        'values' => array(
            array(
                'title' => __('Открыта'), 
                'value' => 'open', 
            ), 
            array(
                'title' => __('Открыта + E-Mail'), 
                'value' => 'open_mail', 
            ), 
            array(
                'title' => __('Закрыта'), 
                'value' => 'close', 
            ), 
        )
    )); 

    add_settings_field('general', 'settings_general', array(
        'title' => __('Доступ для гостей'), 
        'name' => 'guest_select', 
        'type' => 'select', 
        'description' => __('[!] В закрытом режиме, гостям доступна авторизация и регистрация'), 
        'values' => array(
            array(
                'title' => __('Открыто все'), 
                'value' => 0, 
            ), 
            array(
                'title' => __('Закрыто все'), 
                'value' => 1, 
            ), 
        )
    )); 

    add_settings_page('bbcode', __('Настройки тегов BBcode'), 'adm_set_sys', '', 'Настройки BBcode', true); 
    add_settings_section('bbcode', 'settings_bbcode', array(
        'option_type' => 'autoload', 
    )); 

    $fields = array(
        array(
            'title' => __('Курсив [i]'), 
            'name' => 'bb_i', 
        ), 
        array(
            'title' => __('Подчеркнутый [u]'), 
            'name' => 'bb_u',  
        ), 
        array(
            'title' => __('Жирный [b]'), 
            'name' => 'bb_b',  
        ), 
        array(
            'title' => __('Большой [big]'), 
            'name' => 'bb_big',  
        ), 
        array(
            'title' => __('Маленький [small]'), 
            'name' => 'bb_small',  
        ), 
        array(
            'title' => __('Маленький [small]'), 
            'name' => 'bb_small',  
        ), 
        array(
            'title' => __('Подсветка PHP-кода [code]'), 
            'name' => 'bb_code',  
        ), 
        array(
            'title' => __('Размер шрифта [size]'), 
            'name' => 'bb_size',  
        ), 
        array(
            'title' => __('Обработка ссылок'), 
            'name' => 'bb_http',  
        ), 
        array(
            'title' => __('Вставка ссылок'), 
            'name' => 'bb_url',  
        ), 
        array(
            'title' => __('Вставка изображений'), 
            'name' => 'bb_img',  
        ), 
    ); 

    foreach($fields AS $field) {
        add_settings_field('bbcode', 'settings_bbcode', array_merge(array(
            'type' => 'checkbox', 
            'value' => 1, 
            'checked' => '%value', 
        ), $field)); 
    }

}

function get_validate_post($array) 
{
    $validate = array(); 
    if (is_array($array)) {
        foreach($array AS $key => $value) {
            if (isset($array['_' . $key]) && hash('sha256', $key . ':' . SALT_FORMS_FIELDS) === $array['_' . $key]) {
                $validate[$key] = $value; 
            }
        }
    }

    return $validate; 
}

function get_list_updates() 
{
    $json = ds_get('ds_list_updates', array()); 

    if (!empty($json)) {
        return $json; 
    }

    $json['count_any'] = 0; 

    $checkFile = PATH_CACHE . '/ds_update_core.json';

    if (is_file($checkFile)) {
        $json['core'] = json_decode(file_get_contents($checkFile), true); 
        $json['count_any'] += count($json['core']); 
    }

    $checkFile = PATH_CACHE . '/ds_update_plugins.json';

    if (is_file($checkFile)) {
        $json['plugins'] = json_decode(file_get_contents($checkFile), true); 
        $json['count_any'] += count($json['plugins']); 
    }

    ds_set('ds_list_updates', $json); 

    return use_filters('ds_list_updates', $json); 
}

/** 
* Проверяем наличие обновлений для плагинов
*/ 
function check_plugins_update() 
{
    $checkFile = PATH_CACHE . '/ds_update_plugins.json'; 

    if (is_file($checkFile)) {
        $time = (time() - filemtime($checkFile)); 

        if ($time < 60 * 60 * 12) {
            return ;
        }
    }

    $update = new Update(); 
    $plugins = ds_plugins(); 
    $updatePlugins = $update->get_plugins(join(',', array_keys($plugins))); 

    $array_update = array(); 

    if (is_array($updatePlugins)) {
        foreach($updatePlugins AS $key => $resource) 
        {
            if ($resource['version'] > $plugins[$key]['version']) {
                $array_update[$key] = $resource; 
            }
        }
    }

    @file_put_contents($checkFile, json_encode($array_update, JSON_UNESCAPED_UNICODE));

    do_event('check_plugins_update', $array_update); 
}

/** 
* Проверяем наличие обновлений ядра CMS-Social
*/ 
function check_core_update() 
{
    $checkFile = PATH_CACHE . '/ds_update_core.json'; 

    if (is_file($checkFile)) {
        $time = (time() - filemtime($checkFile)); 

        if ($time < 60 * 60 * 12) {
            return ;
        }
    }

    $update = new Update(); 
    $update_info = $update->get_latest();  

    $array_update = array(); 

    if ($update_info['latest']['version'] > get_version()) {
        $array_update[] = $update_info['latest']; 
    }

    @file_put_contents($checkFile, json_encode($array_update, JSON_UNESCAPED_UNICODE));

    do_event('check_core_update', $array_update); 
}