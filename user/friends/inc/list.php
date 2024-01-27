<?php 

if (!defined('ROOTPATH')) {
	die(__('Access denied')); 
}

if ($uid == get_user_id()) {
	$set['title'] = __('Мои друзья'); 
} else {
	$set['title'] = __('Друзья %s', get_user_nick($uid)); 
}

get_header(); 

$section = (isset($_GET['section']) ? $_GET['section'] : 'friends'); 
$counters = get_friends_counters($uid); 

$sortAction = use_filters('ds_friends_sort_action', array(
	'friends'       => __('Все друзья'), 
	'requests'      => __('Заявки в друзья'), 
	'subscribers'   => __('Подписчики'), 
	'subscriptions' => __('Подписки'), 
	'out_requests'  => __('Мои заявки'), 
));  

foreach($sortAction AS $key => $item) { 
	if ($user['id'] != $uid && ($key == 'out_requests' || $key == 'requests' || $key == 'locked')) {
		continue; 
	}

	$url = get_query_url(array(
		'section'  => $key, 
	));

	$classes = array(); 
	if ($section == $key) {
		$classes[] = 'active'; 
	}

	$action_nav[] = array(
		'%link%' => $url, 
		'%count%' => (isset($counters[$key]) ? $counters[$key] : ''), 
		'%title%' => $item, 
		'%class%' => join(' ', $classes), 
	); 	
}

$default = array(
	'%before%' => '', 
	'%after%' => '', 
	'%class%' => '', 
); 

$action_nav = use_filters('ds_friends_action_nav', $action_nav); 
$template_box = use_filters('ds_template_select_box', '<div class="ds-select">%items%</div>'); 
$template_link = use_filters('ds_template_select_item', '%before%<a class="ds-select-item %class%" href="%link%">%title%<span class="ds-select-counter">%count%</span></a>%after%'); 

$items = array(); 
foreach($action_nav AS $key => $value) {
	$value = array_merge($default, $value); 
	$items[] = str_replace(array_keys($value), array_values($value), $template_link); 
}

if (!empty($items)) {
	echo str_replace(array(
		'%items%', 
	), array(
		join('', $items), 
	), $template_box); 
}

$args = array(
	'p_str' => -1, 
	'user_id' => $uid, 
	'status' => $section, 
); 

$query = new DB_Friends($args); 

do_event('ds_friends_output', $query, $uid, $section); 

$template_list = use_filters('ds_friends_template_list_item', '<div class="%class_list%"><a class="%class_list%-link" href="%link%">%thumbnail% <span class="%class_list%-title">%title%</span></a></div>');
$template_box = use_filters('ds_friends_template_list', '<div class="list list-friends">%content%</div>'); 

$content = ''; 
foreach($query->items AS $friend_id) {
	$ank = get_user($friend_id); 

	$mask_list = use_filters('ds_friends_mask_list_item', array(
	    '%class_list%' => 'list-item', 
	    '%counter%' => 0, 
		'%thumbnail%' => get_avatar($ank['id'], 'thumbnail'), 
	    '%title%' => text($ank['nick']), 
	    '%link%' => get_user_url($ank['id']), 
	)); 

	$content .= str_replace(array_keys($mask_list), array_values($mask_list), $template_list); 
}

echo str_replace('%content%', $content, $template_box); 

if ( $query->pages > 1 ) {
    str('?', $query->pages, $query->paged);
}

get_footer(); 