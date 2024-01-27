<?php 

if (!defined('ROOTPATH')) {
	define('ROOTPATH', dirname(dirname(dirname(__FILE__)))); 
	require ROOTPATH . '/sys/inc/core.php'; 	
}

$sort = (isset($_GET['s']) ? (int) $_GET['s'] : 1); 
$setup = ds_get('ds_setup_friends', array()); 
$request = ds_get('route_request'); 

if (!empty($request['user_nick'])) {
	$profile = get_user_by_nick($request['user_nick']); 
} elseif (!empty($request['user_id'])) {
	$profile = get_user($request['user_id']); 
} else {
	$profile = $user;
}

$uid = $profile['id'];

do_event('ds_friends_init', $profile, $setup); 

$action = (isset($request['require']) ? $request['require'] : 'list'); 

$includes = use_filters('ds_friends_includes', array(
    'action' => 'inc/action.php', 
    'list'   => 'inc/list.php', 
)); 

if (isset($includes[$action])) {
    require $includes[$action]; 
} else {
    p404(); 
}