<?php

/**
* Основной файл системы CMS-Social
* Крайне не рекомендуем вносить изменения в ./index.php
*/ 

define('ROOTPATH', dirname(__FILE__)); 
require(ROOTPATH.'/sys/inc/core.php'); 

/**
* Хук всех запросов из REQUEST_URI 
* Используйте ds_rewrite_rule($regexp, $include_file, $str_params)
*/
do_event('ds_request_init'); 