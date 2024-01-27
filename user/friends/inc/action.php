<?php 

if (!defined('ROOTPATH')) {
	die(__('Access denied')); 
}

only_reg(); 

if ($user['id'] == $profile['id']) {
	add_error(__('Вы не можете добавлять/удалять себя как друга')); 
}

$result = ''; 
do_event('preset_friends_' . $request['do'] . '_status', $user, $profile, $request); 

$messages = array(
	'canceled' => __('Вы отменили заявку'), 
	'deleted' => __('Пользователь %s удален из списка друзей', $profile['nick']), 
	'added' => __('Заявка успешно отправлена'), 
	'subscribed' => __('Подписка отменена'), 
	'confirmed' => __('Пользователь %s стал вашим другом', $profile['nick']), 
); 

$status = get_friend_status($user['id'], $profile['id']); 
$doStatus = array('add', 'confirm'); 

if (in_array($request['do'], $doStatus) && in_array($status, $doStatus)) {
	$result = action_friend($user['id'], $profile['id'], 1); 
}

$doStatus = array('unlock', 'delete', 'cancel', 'sent', 'friends', 'subscribed', 'confirm'); 

if (in_array($request['do'], $doStatus) && in_array($status, $doStatus)) {
	$result = action_friend($user['id'], $profile['id'], 0); 
}

if (!empty($messages[$result])) {
	$_SESSION['message'] = $messages[$result]; 
}

do_event('set_friends_' . $request['do'] . '_status', $user, $profile, $request); 

/**
* Редирект на страницу пользователя по умолчанию
* Используйте фильтр get_user_url совместно 
* с событием ds_friends_init для изменения url
*/ 
if (empty($setup['redirect'])) {
	$setup['redirect'] = use_filters('get_user_url', '/info.php?id=' . $profile['id'], $profile); 
}

ds_redirect($setup['redirect']); 
