<?php 

$root_path_dir = dirname(dirname(dirname( __FILE__ ))); 

/**
* Загрузка конфигурационного файла системы
*/ 
if (is_file($root_path_dir . '/config.php')) {
    require($root_path_dir . '/config.php'); 
}

/**
* Константы
*/

// Время полуночь
define('FTIME', mktime(0, 0, 0));

// Текущая метка времени unix
define('TIME', time());


list($msec, $sec) = explode(chr(32), microtime()); 
define('MICROTIME', $sec + $msec);

define('ARRAY_A', true); 
define('ARRAY_N', false); 

/**
* Возвращает время генерации страницы 
* в момент вызова функции get_page_gen()
*/ 
function get_page_gen($per = 3) 
{
    $timestart = MICROTIME; 

    list($msec, $sec) = explode(chr(32), microtime()); 
    $timeend = $sec + $msec; 

    return number_format($timeend - $timestart, $per); 
}

// Корень сайта
if (!defined('H')) {
	define('H', $root_path_dir . '/'); 
}

if (!defined('ROOTPATH')) {
    define('ROOTPATH', $root_path_dir); 
}

if (!defined('PATH_CACHE')) {
    define('PATH_CACHE', $root_path_dir . '/sys/tmp'); 
}

if (!defined('PATH_UPLOADS')) {
    define('PATH_UPLOADS', $root_path_dir . '/sys/uploads'); 
}

if (!defined('PATH_PLUGINS')) {
    define('PATH_PLUGINS', $root_path_dir . '/sys/plugins'); 
}

// отключаем показ ошибок
if (function_exists('error_reporting'))@error_reporting(0); 

// Ставим ограничение для выполнения скрипта на 60 сек
if (function_exists('set_time_limit'))@set_time_limit(60);

if (function_exists('ini_set')) {
    ini_set('display_errors', false); // отключаем показ ошибок
    ini_set('register_globals', false); // вырубаем глобальные переменные
    ini_set('session.use_cookies', true); // используем куки для сессий
    ini_set('session.use_trans_sid', true); // используем url для передачи сессий
    ini_set('arg_separator.output', "&amp;"); // разделитель переменных в url (для соответствия с xml)

    // Deprecated 
    if (ini_get('mbstring.internal_encoding')) {
        ini_set('mbstring.internal_encoding', 'UTF-8');
    }
    ini_set('mbstring.func_overload', 7);
}

// принудительно вырубаем глобальные переменные
if (ini_get('register_globals')) {
    $allowed = array('_ENV' => 1, '_COOKIE' => 1, '_FILES' => 1, '_GET' => 1, '_POST' => 1, '_SERVER' => 1, '_REQUEST' => 1, 'GLOBALS' => 1);
    foreach ($GLOBALS as $key => $value) {
        if (!isset($allowed[$key])) {
            unset($GLOBALS[$key]);
        }
    }
}

// время запуска скрипта
list($msec, $sec) = explode(chr(32), microtime()); 
$conf['headtime'] = $sec + $msec;
$time = time();

/**
* Загрузка классов системы
*/
require(ROOTPATH.'/sys/inc/classes/autoload.php');

/**
* Инициализация Хуков
*/

$filter = new Filter(); 
Registry::set('Filter', $filter); 

$tools = new Tools();
Registry::set('Tools', $tools); 

$CS_Menu = new Menu(); 
Registry::set('Menu', $CS_Menu); 

/**
* Поддержка мультиязычности сайта
* Для отключения используйте define('DISABLE_LANGUAGES', true); в ./config.php
*/ 
if (!defined('DISABLE_LANGUAGES') || DISABLE_LANGUAGES === false) {
    require H.'sys/inc/languages.php';
} 

// Загрузка функций
require H.'sys/inc/functions.php';

// Установка событий по умолчанию 
require H.'sys/inc/events.php';

if (isset($_POST['msg'])) {
    $_POST['msg'] = emoji_to_code($_POST['msg']);
}

$phpvervion = explode('.', phpversion());
$conf['phpversion'] = $phpvervion[0];

$upload_max_filesize = ini_get('upload_max_filesize');

if (preg_match('#([0-9]*)([a-z]*)#i', $upload_max_filesize, $varrs)) {
    if ($varrs[2] == 'M')$upload_max_filesize = $varrs[1]*1048576;
    elseif ($varrs[2] == 'K')$upload_max_filesize = $varrs[1]*1024;
    elseif ($varrs[2] == 'G')$upload_max_filesize = $varrs[1]*1024*1048576;
}

session_name('SESS');
session_start();

$sess = addslashes(session_id());
if (!preg_match('#[A-z0-9]{32}#i', $sess)) {
	$sess = md5(mt_rand(1111, 999999));
}

do_event('ds_session_init'); 

$passgen = passgen();

/**
* База данных подключение
*/
db::connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

// Событие при успешном подключении к базе данных
do_event('ds_mysql_init'); 

/**
* Настройки системы основные 
*/ 

$set = get_system();

// Событие после инициализации настроек системы
do_event('ds_settings_init', $set); 

if ($set['show_err_php']) {
    error_reporting(E_ALL); 
    ini_set('display_errors', true); 
}

/**
* Загрузка плагинов 
*/ 
$plugins = ds_plugins();  
Registry::set('ds_plugins', $plugins); 

foreach($plugins AS $plugin) {
    if (is_plugin_active($plugin['slug']) === true) {
        $plugin_path = PATH_PLUGINS . DIRECTORY_SEPARATOR; 
        if (is_file($plugin_path . $plugin['script'])) {
            require $plugin_path . $plugin['script']; 
            define('plugin_' . $plugin['slug'] . '_loaded', 1); 
        }
    }
}

// Событие после загрузки всех плагинов
do_event('ds_plugins_loaded'); 

// Авторизация пользователя
if (isset($_SESSION['id_user'])) {
    $user_id = use_filters('ds_session_user', $_SESSION['id_user']); 
    $user = get_user($user_id);

    $user['type_input'] = 'session';

    if (!isset($user['id'])) {
        $_SESSION['message'] = __('Не удалось восстановить сессию'); 
        setcookie('id_user');
        setcookie('pass');
        session_destroy();
        unset($user); 
    }

    // Язык сайта
    ds_set('site_language', $set['site_language']); 
} 

elseif (!empty($_COOKIE['id_user']) && !empty($_COOKIE['pass'])) {
    $user_auth = use_filters('ds_auth_data', array(
        'key' => 'id', 
        'id' => $_COOKIE['id_user'], 
        'pass' => cookie_decrypt($_COOKIE['pass']), 
    )); 

    if (is_auth_user($user_auth['id'], $user_auth['pass'], $user_auth['key'])) {
        $user = get_user($user_auth['id']);
        $_SESSION['id_user'] = $user['id']; 
        $user['type_input'] = 'cookie';
    }
}

// Событие что пользователь авторизован
if (isset($user['id'])) {
    do_event('ds_user_init', $user['id']); 

    // Обновляем настройки
    $set = get_settings();
}

/**
* Загрузка файла с функциями темы
*/ 
if (!is_dir(get_theme_directory())) {
    $ds_theme_first = array_shift(ds_themes()); 
    if ($ds_theme_first) {
        update_option('set_them', $ds_theme_first, 'autoload'); 
        ds_redirect(get_current_url()); 
    }
    ds_die(__('Шаблон {%s} не найден', basename(get_theme_directory()))); 
}

if (is_file(get_theme_directory() . '/functions.php')) {
    require get_theme_directory() . '/functions.php'; 
    do_event('ds_theme_functions_loaded'); 
}

/**
* Регистрация стандартных виджетов
*/ 
if (!defined('SUPPORT_WIDGETS') || SUPPORT_WIDGETS == true) {
    register_widget('Widget_Html'); 
    register_widget('Widget_Text'); 
    
    do_event('ds_register_widgets'); 
}

if (isset($_SERVER["HTTP_USER_AGENT"]) && preg_match('#up-browser|blackberry|windows ce|symbian|palm|nokia|obile#i', $_SERVER["HTTP_USER_AGENT"]))
$webbrowser = false;

elseif (isset($_SERVER["HTTP_USER_AGENT"]) && (preg_match('#windows#i', $_SERVER["HTTP_USER_AGENT"]) || 
        preg_match('#linux#i', $_SERVER["HTTP_USER_AGENT"]) || preg_match('#bsd#i', $_SERVER["HTTP_USER_AGENT"]) || 
        preg_match('#x11#i', $_SERVER["HTTP_USER_AGENT"]) || preg_match('#unix#i', $_SERVER["HTTP_USER_AGENT"]) || 
        preg_match('#macos#i', $_SERVER["HTTP_USER_AGENT"]) ||preg_match('#macintosh#i', $_SERVER["HTTP_USER_AGENT"])))
$webbrowser = true;
else 
$webbrowser = false;


$ipa = false;
if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']!='127.0.0.1' && preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#",$_SERVER['HTTP_X_FORWARDED_FOR']))
{
	$ip2['xff'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$ipa[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
if(isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']!='127.0.0.1' && preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#",$_SERVER['HTTP_CLIENT_IP']))
{
	$ip2['cl'] = $_SERVER['HTTP_CLIENT_IP'];
	$ipa[] = $_SERVER['HTTP_CLIENT_IP'];
}
if(isset($_SERVER['REMOTE_ADDR']) && preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#",$_SERVER['REMOTE_ADDR']))
{
	$ip2['add'] = $_SERVER['REMOTE_ADDR'];
	$ipa[] = $_SERVER['REMOTE_ADDR'];
}

$ip = $ipa[0];
$iplong = ip2long($ip);

if (isset($_SERVER['HTTP_USER_AGENT']))
{
	$ua = $_SERVER['HTTP_USER_AGENT'];
	$ua = strtok($ua, '/');
	$ua = strtok($ua, '('); // оставляем только то, что до скобки
	$ua = preg_replace('#[^a-z_\./ 0-9\-]#iu', null, $ua); // вырезаем все "левые" символы

	// Опера мини тоже посылает данные о телефоне :)
	if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) && preg_match('#Opera#i',$ua))
	{
		$ua_om = $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'];
		$ua_om = strtok($ua_om, '/');
		$ua_om = strtok($ua_om, '(');
		$ua_om = preg_replace('#[^a-z_\. 0-9\-]#iu', null, $ua_om);
		$ua = 'Opera Mini ('.$ua_om.')';
	}
}
else $ua = 'Нет данных';

require (ROOTPATH.'/sys/inc/user.php');

do_event('ds_init'); 

if (is_page_admin()) { 
    do_event('ds_admin_init'); 
}