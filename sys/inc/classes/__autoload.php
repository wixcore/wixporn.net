<?php 

/**
* PHP_VERSION >= PHP 5
* Автозагрузка классов
*/

function __autoload($class) 
{
    ds_autoload($class); 
}