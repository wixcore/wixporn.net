<?php 

/**
* Добавить хук фильтр к строке 
* @return bolean
*/

function add_filter($key, $callback, $priority = 10, $accepted = 1) 
{
    $filter = Registry::get('Filter'); 
    return $filter->add($key, $callback, $priority, $accepted);
}

/**
* Удалить хук фильтр к строке 
* @return bolean
*/

function remove_filter($key, $callback, $priority = 10, $accepted = 1) 
{
    $filter = Registry::get('Filter'); 
    return $filter->remove($key, $callback, $priority, $accepted);
}

/**
* Проверка на наличие хука фильтра
* @return bolean
*/

function is_filter($key, $function_to_check = false) 
{
  	$filter = Registry::get('Filter'); 
  	return $filter->has($key, $function_to_check);
}

/**
* Выполняет объявленные хуки фильтры
* @return callback
*/

function use_filters($key, $value) 
{
  	$filter = Registry::get('Filter'); 

    if ($filter->has($key) === false) {
        return $value; 
    }
    
    $args = func_get_args();
    array_shift($args);
    
    if (count($args) == 1) {
        return $filter->runFilter($key, $value); 
    }
    
  	return $filter->runFilter($key, $args); 
}

/**
* Добавить хук событие
* @return bolean
*/

function add_event($key, $callback, $priority = 10, $accepted = 1) 
{
    return add_filter($key, $callback, $priority, $accepted);
}

/**
* Удалить хук событие
* @return bolean
*/

function remove_event($key, $callback, $priority = 10, $accepted = 1) 
{
  	return remove_filter($key, $callback, $priority, $accepted);
}

/**
* Выполнить события хука
* @return callback 
*/

function do_event($key, $value = NULL) 
{
  	$filter = Registry::get('Filter'); 
    
    if ($filter->has($key) === false) {
        return $value; 
    }
    
    $args = func_get_args();
    array_shift($args);

  	return $filter->runEvent($key, $args); 
}

/**
* Служебная функция, регистрирует устаревшие инклуды
*/
function include_deprecated($file, $version) 
{
	global $messages_deprecated; 

	$messages_deprecated[] = 'Файл <span style="color: blue;">' . str_replace(H, '', $file) . 
	' считается устаревшим c <a href="https://cms-social.ru/version/' . $version . '/">версии ' . $version . '</a>';
}

/**
* Функция кеширует инклуды в файл, с интервалом обновления заданным в настройках системы
*/ 
function add_includes_cache($includes, $dir_files = '', $cache_name) 
{
    $path_tmp_includes = ROOTPATH . '/sys/tmp/' . $cache_name . '.cache.php'; 
    $includes = use_filters('ds_includes_cache_array', $includes); 

    if (!is_file($path_tmp_includes)) {
        $cache = "<?php";
        $cache .= "\n\n/**\n* Created this file by CMS-Social \n* Caching all php functions in one file \n* If you change the function files or add new ones, delete the file\n* Direcory PHP files this cache: ./".$dir_files."\n* Support tehnical WEB site: https://cms-social.ru\n*/\n";

        foreach($includes AS $include) 
        {
            $str = trim(file_get_contents(ROOTPATH.'/'.$include));  
            $str = preg_replace('/(\<\?php|\<\?)/', '', $str, 1); 
            $str = preg_replace('/(\?\>)([\s]+)?$/', '', $str, 1); 
            $str = preg_replace('/\/\*(.*?)\*\//s', '', $str); 

            $cache .= "\n\n/**\n* Cache a Functions file: ".$include." \n*/\n" . trim($str);
        }
        
        file_put_contents($path_tmp_includes, $cache);
    }
}

/**
* Хук срабатывает перед загрузкой основных функций системы 
* add_event('functions_pre_loading', 'callback_function'); 
*/ 
do_event('ds_functions_pre_loading'); 

// Файл с функциями если работает кеширование
$functions_cache_file = use_filters('ds_functions_cache_file', ROOTPATH.'/sys/tmp/functions.cache.php'); 
$is_functions_cache = use_filters('ds_functions_cache', (defined('CACHE_FUNCTIONS') ? CACHE_FUNCTIONS : false)); 

// Загрузка всех функций из кеша
if ($is_functions_cache === true && is_file($functions_cache_file)) {
    require($functions_cache_file); 
    do_event('ds_functions_loaded_cache'); 
}

else {
    $path_functions_dir = use_filters('ds_functions_path', H.'sys/inc/functions');
    $open_functions_dir = opendir($path_functions_dir); 

    if ($is_functions_cache === true) {
        $cache_includes_fnc = array(); 
    }
    
    // Живая загрузка функций из папки ./sys/inc/functions
    while ($filebase = readdir($open_functions_dir)) {
        $include_function_file = use_filters('ds_function_include_file', $path_functions_dir . '/' . $filebase); 

        if (preg_match('#\.php$#i', $include_function_file)) {
            require($include_function_file);

            if ($is_functions_cache === true) {
                $cache_includes_fnc[] = str_replace(H, '', $include_function_file);
            }
        }
    }

    do_event('ds_functions_loaded_live'); 

    // Кеширование функций
    if ($is_functions_cache === true) {
        add_includes_cache($cache_includes_fnc, str_replace(H, '', $path_functions_dir), 'functions');
        unset($cache_includes_fnc);         
    }
}

/**
* Хук срабатывает после загрузки основных функций системы 
* add_event('functions_loaded', 'callback_function'); 
*/ 
do_event('ds_functions_loaded'); 