<?php 

$user_id = (isset($_GET['id']) ? (int) $_GET['id'] : (is_user() ? get_user_id() : 0)); 
$ank = get_user($user_id);

if (empty($ank['id'])) {
    header("Location: /404.php");
    exit;
}

// Загрузка конструктора профиля
add_event('ds_profile', 'ds_profile_load', 1); 

// Событие перед началом вывода 
do_event('ds_profile_pre_load', $ank['id']); 

$set['title'] = __('Страничка %s', use_filters('ds_user_nick', $ank['nick'])); 
get_header(); 

// Основное событие профиля
do_event('ds_profile', $ank['id']); 

/*
$args = array(
	'author_id' => $ank['id'], 
); 

$query = new DB_Feeds($args); 

foreach($query->items AS $feed) {
	ds_output_feed($feed); 
}
*/

get_footer(); 