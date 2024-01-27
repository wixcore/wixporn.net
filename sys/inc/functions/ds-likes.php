<?php 

function handle_likes_init() 
{
	add_event('ajax_ds-like_callback', 'ajax_toggle_likes'); 
}

function ajax_toggle_likes() 
{
	$uid = (int) $_POST['uid']; 
	$tbl = $_POST['type']; 

	if (!is_user()) {
		return ;
	}

	$result = toggle_likes($tbl, get_user_id(), $uid);
	$item = db::fetch("SELECT `likes` FROM `" . $tbl . "` WHERE `id` = '" . $uid . "' LIMIT 1"); 

	die(json_encode(array(
		'result' => $result, 
		'count' => (int) $item['likes'], 
	))); 
}

function toggle_likes($tbl, $user_id, $object_id) 
{
	$like = db::fetch("SELECT user_id FROM `" . $tbl . "_likes` WHERE user_id = '$user_id' AND `object_id` = '$object_id' LIMIT 1");
	$sql_args = array(
		'user_id' => $user_id, 
		'object_id' => $object_id,
	); 

	if (!empty($like)) {
		db::delete($tbl . '_likes', $sql_args); 
		db::query("UPDATE `" . $tbl . "` SET likes = likes - '1' WHERE id = '" . $object_id . "' LIMIT 1"); 
		return 'delete'; 
	} else {
		db::insert($tbl . '_likes', $sql_args); 
		db::query("UPDATE `" . $tbl . "` SET likes = likes + '1' WHERE id = '" . $object_id . "' LIMIT 1"); 
		return 'add'; 
	}
}

function get_panel_likes($tbl, $item) 
{
	$classes = array(); 
	$classes[] = 'ds-like';  
	$classes[] = (is_user() ? 'ds-like-user' : 'ds-like-guest'); 

	if (!empty($item['is_liked'])) {
		$classes[] = 'ds-i-liked'; 
	}
	
	return '<a class="' . join(' ', $classes) . '" data-uid="' . $item['id'] . '" data-type="' . $tbl . '"><i class="icon-like"></i><span class="counter">' . $item['likes'] . '</span></a>'; 
}
