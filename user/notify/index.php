<?php 

if (!defined('ROOTPATH')) {
	define('ROOTPATH', dirname(dirname(dirname(__FILE__)))); 
	require ROOTPATH . '/sys/inc/core.php'; 	
}

if (!is_user()) {
	ds_redirect('/'); 
}

do_event('ds_notify_init', $user); 

// Все типы уведомлений
$notify_types = get_notify_types(); 
$notify_groups = get_notify_groups(); 

$id = (isset($_GET['id']) ? (int) $_GET['id'] : 0); 
$action = (isset($_GET['action']) ? $_GET['action'] : ''); 

if ($action == 'delete') {
	$notify = db::fetch("SELECT * FROM `notification` WHERE id = '" . $id  . "' AND user_id = '" . $user['id'] . "' LIMIT 1"); 

	if (isset($notify['id'])) {
		db::query("DELETE FROM `notification` WHERE id = '" . $id  . "' AND user_id = '" . $user['id'] . "' LIMIT 1"); 
		$_SESSION['message'] = __('Уведомление успешно удалено'); 
		ds_redirect('/user/notify/'); 
	}
}

db::query("UPDATE `notification` SET `read` = '1' WHERE `user_id` = '" . get_user_id() . "'"); 

$set['title'] = __('Уведомления'); 
get_header(); 

$k_post = db::count("SELECT COUNT(*) FROM `notification` WHERE `user_id` = '" . $user['id'] . "' AND `type` IN('" . join("', '", array_keys($notify_types)) . "')");
$k_page = k_page( $k_post, $set['p_str'] );
$page   = page( $k_page );
$start  = $set['p_str'] * $page - $set['p_str'];

$q      = db::query("SELECT * FROM `notification` WHERE `user_id` = '" . $user['id'] . "' AND `type` IN('" . join("', '", array_keys($notify_types)) . "') ORDER BY `time` DESC LIMIT $start, $set[p_str]");

if ( $k_post == 0 ) {
    echo '<div class="empty">';
    echo __('Список уведомлений пуст');
    echo '</div>';
} else {
	echo '<div class="ds-notify">'; 
	while ($post =  $q->fetch_assoc()) {
		$type = $post['type']; 
		$data = array(); 
		if (!empty($post['data'])) {
			$data = json_decode($post['data'], true); 
		}

		if (isset($notify_types[$type])) {
			if (is_callable($notify_types[$type]['callback'])) {
				echo call_user_func($notify_types[$type]['callback'], $type, $data, $post); 
			} else {
				echo '<pre>' . __('Не задана callback функция для {%s} типа уведомлений', $type) . '</pre>'; 
			}
		}
	}
	echo '</div>'; 

	if ( $k_page > 1 ) {
	    str( "?", $k_page, $page );
	}	
}

get_footer(); 