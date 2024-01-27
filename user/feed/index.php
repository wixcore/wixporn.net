<?php 

if (!defined('ROOTPATH')) {
	die('Доступ запрещен'); 
}

only_reg();

$set['title'] = __('Новости друзей'); 
get_header(); 

$args = array(
	'user_id' => get_user_id(), 
); 

$query = new DB_Feeds($args); 
foreach($query->items AS $feed) {
	ds_output_feed($feed); 
}

get_footer(); 