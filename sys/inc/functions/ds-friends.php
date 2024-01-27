<?php 

function get_friends_counters($user_id) 
{
	$counters = ds_get('ds_friends_counters', array()); 

	if (isset($counters[$user_id])) {
		return $counters[$user_id]; 
	}

	$sql = "SELECT 
			 SUM(IF(status = 1 AND friend_id = '$user_id', 1, 0)) AS 'friends', 
			 SUM(IF((status = 2 OR status = 0) AND friend_id = '$user_id', 1, 0)) AS 'subscribers', 
			 SUM(IF((status = 2 OR status = 0) AND user_id = '$user_id', 1, 0)) AS 'subscriptions', 
			 SUM(IF(status = -1 AND user_id = '$user_id', 1, 0)) AS 'locked', 
			 SUM(IF(status = 0 AND friend_id = '$user_id', 1, 0)) AS 'requests', 
			 SUM(IF(status = 0 AND user_id = '$user_id', 1, 0)) AS 'out_requests'
			 FROM `friends` 
			WHERE user_id = '$user_id' OR friend_id = '$user_id'";

	$counters[$user_id] = db::fetch($sql); 

	return $counters[$user_id]; 
}

function set_friend_status($user_id, $friend_id, $status = 1) 
{
	$friend = db::fetch("SELECT * FROM friends WHERE `user_id` = '$user_id' AND `friend_id` = '$friend_id' LIMIT 1"); 

	if (!empty($friend)) {
		db::update('friends', array(
			'status' => $status, 
		), array(
			'user_id' => $user_id, 
			'friend_id' => $friend_id, 
		)); 
	} else {
		db::insert('friends', array(
			'user_id' => $user_id, 
			'friend_id' => $friend_id, 
			'time' => date('Y-m-d H:i:s'), 
			'status' => $status, 
		));	
	}
}

/**
* Статус пользователя по отношению к другому пользователю
*
* -100 - Неизвестное значение
* -1   - Вы заблокированы
*  0   - Заявка отправлена
*  1   - Вы друзья
*  2   - Вы подписаны
*  @return int
*/ 

function get_friend_status($user_id, $friend_id) 
{
	$cache = ds_get('ds_friend_status', array()); 

	if (!isset($cache[$user_id][$friend_id])) {
		$cache[$user_id][$friend_id] = db::fetch("SELECT * FROM friends WHERE `user_id` = '$user_id' AND `friend_id` = '$friend_id' LIMIT 1"); 
		$cache_update = true; 
	}

	if (!isset($cache[$friend_id][$user_id])) {
		$cache[$friend_id][$user_id] = db::fetch("SELECT * FROM friends WHERE `user_id` = '$friend_id' AND `friend_id` = '$user_id' LIMIT 1"); 
		$cache_update = true; 
	}

	if (isset($cache_update)) {
		ds_set('ds_friend_status', $cache);
	}
	
	$main = $cache[$user_id][$friend_id]; 
	$friend = $cache[$friend_id][$user_id]; 

	$status = 'add'; 

	if (!empty($main) && empty($friend)) {
		if ($main['status'] == '0') {
			$status = 'sent'; 
		} elseif ($main['status'] == '2') {
			$status = 'subscribed'; 
		}
	}

	if (empty($main) && !empty($friend)) {
		if ($friend['status'] == '0') {
			$status = 'confirm'; 
		}
	}

	if (!empty($friend) && !empty($main)) {
		if ($friend['status'] == '1' && $main['status'] == '1') {
			$status = 'friends'; 
		} elseif ($main['status'] == '0' && $friend['status'] == '2') {
			$status = 'read'; 
		} elseif ($main['status'] == '2' && !$friend) {
			$status = 'subscribed'; 
		} elseif ($main['status'] == '0' && $friend['status'] == '-1') {
			$status = 'locked'; 
		} elseif ($main['status'] == '-1' && $friend['status'] == '0') {
			$status = 'unlock'; 
		}
	}
	
	return $status; 
}

function action_friend($user_id, $friend_id, $status = 1) 
{
	$i = get_friend_status($user_id, $friend_id); 
	$f = get_friend_status($friend_id, $user_id); 

	$result = -1; 

	// Подтвердить или отправить запрос дружбы
	if ($status == 1) {
		if ($i == 'confirm' || $f == 'subscribed') {
			set_friend_status($user_id, $friend_id, 1);		
			set_friend_status($friend_id, $user_id, 1);	

			add_subscription('friends', $user_id, $friend_id); 
			add_subscription('friends', $friend_id, $user_id); 
			
			if ($i == 'confirm') $result = 'confirmed'; 
			if ($f == 'subscribed') $result = 'confirmed';  

			del_notify($user_id, $friend_id, false, 'friends'); 
			add_notify($user_id, $friend_id, 'friends_added', 'friends', array(
				'friend_id' => $user_id, 
			));

		} elseif ($i == 'add') {
			add_notify($user_id, $friend_id, 'friends_add', 'friends', array(
				'friend_id' => $user_id, 
			));

			add_subscription('friends', $user_id, $friend_id); 
			set_friend_status($user_id, $friend_id, 0);
			$result = 'added'; 
		}
	}

	// Удалить из друзей или отменить заявку
	if ($status == 0) {
		if ($f == 'confirm' || $i == 'subscribed') {

			if ($i == 'confirm') $result = 'confirmed'; 
			if ($i == 'subscribed') $result = 'unsubscribed'; 
			if ($i == 'sent') $result = 'canceled'; 
			if ($i == 'unlock') $result = 'unlocked'; 

			delete_subscription('friends', $user_id, $friend_id); 
			db::delete('friends', array(
				'user_id' => $user_id, 
				'friend_id' => $friend_id, 
			)); 
		}

		if ($i == 'friends' || $f == 'sent') {
			set_friend_status($friend_id, $user_id, 2); 
			db::delete('friends', array(
				'user_id' => $user_id, 
				'friend_id' => $friend_id, 
			)); 

			if ($f == 'friends') $result = 'deleted'; 
			if ($f == 'sent') $result = 'canceled'; 
		}

		if ($i == 'unlock') {
			db::delete('friends', array(
				'user_id' => $user_id, 
				'friend_id' => $friend_id, 
			));
			db::delete('friends', array(
				'user_id' => $friend_id, 
				'friend_id' => $user_id, 
			));
			$result = 'deleted'; 
		}
	}

	return $result; 
}

function is_friend($user_id, $friend_id) 
{
	$count = db::count("SELECT COUNT(id) FROM friends WHERE ((`user_id` = '$user_id' AND `friend_id` = '$friend_id') 
							OR (`user_id` = '$friend_id' AND `friend_id` = '$user_id')) AND status = '1'"); 
	
	if ($count == 2) {
		return true; 
	}

	return false; 
}


function handle_friends_init() 
{
	$setup = use_filters('ds_setup_friends', array(
		'rewrite_rule' => array(
			'friends\/([A-zА-я0-9\-\_]+)\/cancel\/' => 'user_nick=$1&require=action&do=cancel', 
			'friends\/([A-zА-я0-9\-\_]+)\/delete\/' => 'user_nick=$1&require=action&do=delete', 
			'friends\/([A-zА-я0-9\-\_]+)\/confirm\/' => 'user_nick=$1&require=action&do=confirm', 
			'friends\/([A-zА-я0-9\-\_]+)\/add\/' => 'user_nick=$1&require=action&do=add', 
			'friends\/([A-zА-я0-9\-\_]+)\/' => 'user_nick=$1&require=list', 
		), 
		'permalinks' => array(
			'cancel' => get_site_url('/friends/%user_nick%/cancel/'), 
			'unlock' => get_site_url('/friends/%user_nick%/delete/'), 
			'delete' => get_site_url('/friends/%user_nick%/delete/'), 
			'list' => get_site_url('/friends/%user_nick%/'), 
			'add' => get_site_url('/friends/%user_nick%/add/'), 
			'confirm' => get_site_url('/friends/%user_nick%/confirm/'), 
		),
		'redirect' => '', 
	)); 

	ds_set('ds_setup_friends', $setup); 

	foreach($setup['rewrite_rule'] AS $regex => $data) {
		ds_rewrite_rule($regex, ROOTPATH . '/user/friends/index.php', $data);
	}
}

function get_friends_link($user_id, $action = 'list') 
{
	$profile = get_user($user_id); 
	$setup = ds_get('ds_setup_friends'); 

	$mask = array(
		'%user_id%' => $profile['id'], 
		'%user_nick%' => $profile['nick'], 
		'%action%' => $action,  
	); 

	return str_replace(array_keys($mask), array_values($mask), $setup['permalinks'][$action]); 
}

/**
* Получает список доступных ссылок
* для действия пользователя над другом
* @return array | null
*/ 
function get_friends_action_links($user_id, $friend_id) 
{
	$i = get_friend_status($user_id, $friend_id);
	$f = get_friend_status($friend_id, $user_id);

	$links = array(); 

	if ($i == 'subscribed') {
		$links[] = array(
			'title' => __('Отменить подписку'), 
			'url' => get_friends_link($friend_id, 'cancel'), 
		); 
	}

	if ($i == 'sent') {
		$links[] = array(
			'title' => __('Отменить заявку'), 
			'url' => get_friends_link($friend_id, 'cancel'), 
		); 
	}

	if ($i == 'confirm' || $i == 'add') {
		$links[] = array(
			'title' => __('Добавить в друзья'), 
			'url' => get_friends_link($friend_id, 'add'), 
		); 
	}

	if ($i == 'confirm') {
		$links[] = array(
			'title' => __('Оставить в подписчиках'), 
			'url' => get_friends_link($friend_id, 'cancel'), 
		); 
	}

	if ($i == 'friends') {
		$links[] = array(
			'title' => __('Удалить из друзей'), 
			'url' => get_friends_link($friend_id, 'delete'), 
		); 
	}

	if ($i == 'unlock') {
		$links[] = array(
			'title' => __('Разблокировать'), 
			'url' => get_friends_link($friend_id, 'unlock'), 
		); 
	}

	return use_filters('ds_friends_action_links', $links, $i, $f); 
}