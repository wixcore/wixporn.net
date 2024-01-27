<?php 

/**
* Возвращает путь до папки с плагином
* @return string
*/ 
function ds_plugin_directory_path($plug) 
{
    $plugins = ds_plugins();

    if (is_file($plugin_file = PATH_PLUGINS . $plugins[$plug]['script'])) {
        return dirname($plugin_file); 
    }
}

/**
* Возвращает массив с плагинами
* @return array
*/ 
function ds_plugins() 
{
    if ($plugins = ds_get('ds_plugins')) {
      return $plugins;
    }

    $json = get_option('ds_plugins'); 

    if ($json) {
        $plugins = json_decode($json, 1);
    }

    return is_array($plugins) ? $plugins : array(); 
}


/**
* Функция активирует плагин
* @return bolean
*/ 
function ds_plugin_activate($plug, $update = false) 
{
    $plugins = ds_plugins();

    if (!is_file($plugin_path = PATH_PLUGINS . $plugins[$plug]['script'])) {
        return false; 
    }

    if (!defined('plugin_' . $plug . '_loaded') && is_file($plugin_path)) {
        require $plugin_path; 
    }

    do_event('ds_plugin_pre_activation', $plug);
    do_event('ds_plugin_' . $plug . '_pre_activation', $plug);

    if (isset($plugins[$plug])) {
        if ($plugins[$plug]['active'] != '1') {
            do_event('ds_plugin_activation', $plug);
            do_event('ds_plugin_' . $plug . '_activation', $plug);            
        }
        
        $plugins[$plug]['active'] = '1'; 
        update_option('ds_plugins', $plugins, 'plugins'); 

        return true;
    }

    return false; 
}


/**
* Функция деактивирует плагин
* @return bolean
*/ 
function ds_plugin_deactivate($plug) 
{
    do_event('ds_plugin_pre_deactivation', $plug);
    do_event('ds_plugin_' . $plug . '_pre_deactivation', $plug);

    $plugins = ds_plugins();

    if (isset($plugins[$plug])) {
		$plugins[$plug]['active'] = '0'; 
		update_option('ds_plugins', $plugins, 'plugins'); 

        do_event('ds_plugin_deactivation', $plug);
        do_event('ds_plugin_' . $plug . '_deactivation', $plug);
    
        return true;
    }

    return false; 
}

/**
* Функция добавляет плагин в список плагинов
* @return bolean
*/ 
function ds_plugin_add($plug, $info) 
{
    $plugins = ds_plugins();

    if (!isset($plugins[$plug])) {
		$plugins[$plug] = $info; 
		$plugins[$plug]['active'] = '0'; 
		update_option('ds_plugins', $plugins, 'plugins'); 
		return true;
    }

    return false; 
}

/**
* Функция удаляет плагин из системы
* @return bolean
*/ 
function ds_plugin_remove($plug) 
{
    $plugins = ds_plugins();

    do_event('ds_plugin_remove', $plug);
    
    if (isset($plugins[$plug])) {
        if (is_file($uninstall = ds_plugin_directory_path($plug) . '/uninstall.php')) {
            require $uninstall; 
        }

        do_event('ds_plugin_' . $plug . '_remove', $plug); 

        if (ds_plugin_directory_path($plug) == PATH_PLUGINS) {
            unlink(PATH_PLUGINS . $plugins[$plug]['script']); 
        } elseif (is_dir($dirpath = dirname(PATH_PLUGINS . $plugins[$plug]['script']))) {
            delete_dir($dirpath); 
        }

        unset($plugins[$plug]); 
        update_option('ds_plugins', $plugins, 'plugins'); 
        return true;
    }

    return false; 
}

/**
* Проверка активен ли плагин
* @return bolean
*/ 
function is_plugin_active($plug) 
{
    $plugins = ds_plugins();

    if (!empty($plugins[$plug]) && $plugins[$plug]['active'] == '1') {
        return true;
    }
    
    return false; 
}

/**
* Проверка на наличие плагина
* @return bolean
*/ 
function plugin_exists($plug) 
{
    $plugins = ds_plugins();

    if (isset($plugins[$plug]['script']) && is_file(PATH_PLUGINS . $plugins[$plug]['script'])) {
        return true;
    }
    
    return false; 
}

/**
* Устанавливает плагин из репозитория CMS-Social
* @return bolean 
*/ 
function ds_plugin_install($plug) 
{
    if (plugin_exists($plug)) {
        add_error(__('Плагин уже существует')); 
    }

    $plugin = json_decode(get_http_content('https://cms-social.ru/api/v1/plugins/info', array(
        'slug' => $plug, 
    )), true); 

    if (!$plugin) {
        add_error(__('Не удалось получить информацию о плагине')); 
    }

    if (is_errors()) return false; 
    
    $sess_id = md5(time()); 
    $tmpFile = PATH_CACHE.'/'.$sess_id.'.zip'; 

    $pluginData = get_http_content($plugin['download']); 

    if (!$pluginData || !file_put_contents($tmpFile, $pluginData)) {
        add_error(__('Не удалось загрузить плагин'));
    }

    if (is_file($tmpFile) && ds_plugin_zip_install($tmpFile)) {
        return true; 
    }

    return false; 
}

/**
* Устанавливает плагин из Zip архива
* @return bolean 
*/ 
function ds_plugin_upload($file_path) 
{
    $sess_id = md5(time()); 

    if (!class_exists('ZipArchive')) {
        add_error(__('У вас не установлена библиотека \'zip\' для работы с архивами')); 
    }

    if (!is_dir(PATH_CACHE)) {    
        @mkdir(PATH_CACHE, 0777); 
        @chmod(PATH_CACHE, 0777); 
    }

    if (!is_file(PATH_PLUGINS . '/index.php')) {
        file_put_contents(PATH_PLUGINS . '/index.php', "<?php \n\n// Index Of");
    }

    $tmpFile = PATH_CACHE.'/'.$sess_id.'.zip'; 

    if (!copy($file_path, $tmpFile)) {
        add_error(__('Не удалось загрузить плагин'));
    }

    if (is_file($tmpFile)) {
        return ds_plugin_zip_install($tmpFile); 
    }
}


function ds_plugin_zip_install($zip_path) 
{
    $sess_id = md5($zip_path);
    $plg = new Plugins(); 
    $installed = array(); 

    if (is_file($zip_path)) {
        if (!is_errors()) {
            $zip = new ZipArchive();
            $zip->open($zip_path, ZipArchive::CREATE);
            $zip->extractTo(PATH_CACHE.'/' . $sess_id);
            $zip->close();

            unlink($zip_path); 

            $files_list = ds_readdir_files_list(PATH_CACHE.'/' . $sess_id); 
            $dires_list = ds_readdir_dir_list(PATH_CACHE.'/' . $sess_id); 
            $plugins = array(); 

            foreach($files_list AS $file) {
                if (preg_match('/\.php$/m', $file)) {
                    $read = file_get_contents($file); 

                    if (strpos($read, 'Plugin Name') !== false) {
                        $plugin = $plg->getPluginInfo($file, false); 

                        if (isset($registered[$plugin['slug']])) {
                            $plugins[$file] = $registered[$plugin['slug']]; 
                        } else {
                            $plugins[$file] = $plugin; 
                        }
                    }
                }
            }

            if (!is_errors()) {
                foreach($plugins AS $plugin) {
                    if (basename(dirname($plugin['script'])) == $sess_id) {
                        if (!rename($plugin['script'], PATH_PLUGINS . '/' . basename($plugin['script']))) {
                            add_error(__('Не удалось установить плагин')); 
                        } else $installed[] = $plugin['slug']; 
                    } else {
                        $plugin_dirs = ds_readdir_dir_list(dirname($plugin['script'])); 
                        if (!is_dir($plugin_dir = PATH_PLUGINS . '/' . basename(dirname($plugin['script'])))) {
                            mkdir($plugin_dir, 0755); 
                        }

                        foreach($plugin_dirs AS $dir) {
                            if (!is_dir($dircopy = str_replace(PATH_CACHE.'/' . $sess_id, PATH_PLUGINS, $dir))) {
                                mkdir($dircopy, 0755); 
                            }
                        }

                        $plugin_files = ds_readdir_files_list(dirname($plugin['script'])); 
                        foreach($plugin_files AS $file) {
                            if (file_exists($filecopy = str_replace(PATH_CACHE.'/' . $sess_id, PATH_PLUGINS, $file))) {
                                unlink($filecopy); 
                            }

                            rename($file, $filecopy); 
                        }

                        if (!file_exists(str_replace(PATH_CACHE.'/' . $sess_id, PATH_PLUGINS, $plugin['script']))) {
                            add_error(__('Не удалось установить плагин')); 
                        } else $installed[] = $plugin['slug']; 
                    }
                }
            }

            $plg->listPlugins(); 
        }
    }

    if (is_dir(PATH_CACHE.'/' . $sess_id)) {
        delete_dir(PATH_CACHE.'/' . $sess_id); 
    }
    
    if (is_file($zip_path)) { 
        unlink($zip_path); 
    }

    if (!is_errors()) {
        do_event('ds_plugins_installed', $installed); 
        return true; 
    }

    return false; 
}