<?php 

// Текущая метка времени unix
define('TIME', time());

list($msec, $sec) = explode(chr(32), microtime()); 
define('MICROTIME', $sec + $msec);

define('ARRAY_A', true); 
define('ARRAY_N', false); 

// Корень сайта
if (!defined('H')) {
	define('H', ROOTPATH . '/'); 
}

if (!defined('PATH_CACHE')) {
    define('PATH_CACHE', ROOTPATH . '/sys/tmp/'); 
}

if (!defined('PATH_UPLOADS')) {
    define('PATH_UPLOADS', ROOTPATH . '/sys/uploads/'); 
}

if (!defined('PATH_PLUGINS')) {
    define('PATH_PLUGINS', ROOTPATH . '/sys/plugins/'); 
}

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

/**
* Загрузка классов системы
*/
require(ROOTPATH.'/sys/inc/classes/class.Registry.php');
require(ROOTPATH.'/sys/inc/classes/class.Filter.php');
require(ROOTPATH.'/sys/inc/classes/class.db.php');
require(ROOTPATH.'/sys/inc/classes/sql_parser.php');
require(ROOTPATH.'/sys/inc/classes/Install.php');

/**
* Инициализация Хуков
*/

$filter = new Filter(); 
Registry::set('Filter', $filter); 

// Загрузка функций
require H.'sys/inc/functions.php';

session_name('SESS');
session_start();

do_event('ds_session_init'); 

if (is_file(ROOTPATH . '/config.php')) {
	require(ROOTPATH . '/config.php'); 
	define('DS_INSTALLED', true);
}

if ( defined('DB_USER') && defined('DB_NAME') && defined('DB_PASSWORD') && defined('DB_HOST') ) {
	db::connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME ); 

	if (mysqli_connect_errno()) {
		$this->errors[] = __('Не удалось подключиться к базе данных') . "<br />" . mysqli_connect_error(); 
	} else {
		define('DB_CONNECT', true); 
	}
}


do_event('ds_install_init'); 