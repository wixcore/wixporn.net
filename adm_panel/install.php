<?php 

define('DS_INSTALL', 1); 
define('ROOTPATH', dirname(dirname( __FILE__ ))); 

require (ROOTPATH . '/sys/inc/core-install.php');

$install = new Install(); 

do_event('ds_install_pre_setup'); 
$install->get_header(); 
do_event('ds_install_setup'); 
$install->get_footer(); 