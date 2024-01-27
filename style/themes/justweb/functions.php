<?php 

define('THEME_VERSION', '1.0.4'); 
define('LANGUAGE_DOMAIN', 'justweb'); 

require dirname(__FILE__) . '/includes/template-admin.php'; 
require dirname(__FILE__) . '/includes/template-user.php'; 
require dirname(__FILE__) . '/includes/template-functions.php'; 
require dirname(__FILE__) . '/includes/jw-modal-ajax.php'; 


if (!defined('SUPPORT_WIDGETS') || SUPPORT_WIDGETS == true) {
	require dirname(__FILE__) . '/widgets/JW_Widget_Online.php'; 

	add_event('ds_register_widgets', function() {
		register_widget('JW_Widget_Online'); 
	}); 
}

add_event('ds_admin_init', 'justweb_admin_settings'); 
function justweb_admin_settings() {
    add_settings_page('justweb', __t('Настроить тему', LANGUAGE_DOMAIN), 'adm_set_sys', 'justweb_admin_page_settings', __t('Настроить тему', LANGUAGE_DOMAIN), false); 
    add_submenu_admin(__t('Настроить тему', LANGUAGE_DOMAIN), 'settings.php?page=justweb', 'adm_set_sys', 'fa-cog', 10, 'themes.php', __t('Настроить тему', LANGUAGE_DOMAIN));
}

function justweb_admin_page_settings() {
	require dirname(__FILE__) . '/templates/admin-settings.php'; 
}

function jw_user_preset() {
	$presets = get_option('jw_presets', NULL); 

	if ($presets !== NULL) {
		$presets = json_decode($presets, true); 
	} else {
		$presets = jw_theme_presets(); 
	}

	$justweb = jw_theme_settings(); 
	$preset_id = $justweb['preset']; 

	if (is_user()) {
		$options = get_user_options(get_user_id(), 'justweb'); 
		$opt = array_replace($justweb, $options); 
		$preset_id = $opt['preset']; 
	}

	return isset($presets[$preset_id]) ? $presets[$preset_id] : array(); 
}

add_event('ds_theme_styles_init', 'justweb_styles_init'); 
function justweb_styles_init() 
{
	ds_theme_style_add(get_theme_uri() . '/css/fonts.css', 'justweb-fonts', THEME_VERSION, 'all'); 
	ds_theme_style_add(get_theme_uri() . '/css/icons.css', 'justweb-icons', THEME_VERSION, 'all'); 
	ds_theme_style_add(get_theme_uri() . '/icons/flaticon.css', 'justweb-flaticon', THEME_VERSION, 'all'); 
	ds_theme_style_add(get_theme_uri() . '/css/audio-player.css', 'justweb-audio-player', THEME_VERSION, 'all'); 
	ds_theme_style_add(get_theme_uri() . '/css/jw-modal.css', 'justweb-modal', THEME_VERSION, 'all'); 
	ds_theme_style_add(get_theme_uri() . '/css/nprogress.css', 'justweb-nprogress', THEME_VERSION, 'all'); 

	$preset = jw_user_preset(); 

	if (isset($preset['url'])) {
		ds_theme_style_add($preset['url'], 'justweb-preset', THEME_VERSION, 'all'); 
	}
}

add_event('ds_theme_scripts_init', 'justweb_add_scripts'); 
function justweb_add_scripts() { 
	ds_theme_script_add(get_theme_uri() . '/js/nprogress.min.js', 'justweb-nprogress', THEME_VERSION);
	ds_theme_script_add(get_theme_uri() . '/js/jw-modal.js', 'justweb-modal', THEME_VERSION);
	ds_theme_script_add(get_theme_uri() . '/js/jquery.ajaxpage.js', 'justweb-ajaxpage', THEME_VERSION);
	ds_theme_script_add(get_theme_uri() . '/js/main.js', 'justweb-main', THEME_VERSION);
}

add_event('ds_pre_redirect', 'ds_ajax_redirect'); 
function ds_ajax_redirect($url, $status) 
{
	if (is_ajax()) {
		die('<div id="ajax-meta" style="display: none;" data-redirect="' . $url . '" data-title="" data-body=""></div>'); 
	}
}

add_filter('ds_files_file_mask_list_item', 'justweb_files_list_mask_file', 10, 2); 
function justweb_files_list_mask_file($mask, $file) 
{
	if (strpos($file['mimetype'], 'audio') !== false) {
		$mask['%after_item%'] = '<div class="list-item-player">' . get_audio_player($file) . '</div>';
	}

	return $mask; 
}

function justweb_counters() 
{
	$counters = ds_get('justweb_counters', array()); 

	if (is_user()) {
		$counters = get_user_counters(); 
	}

	if (!isset($counters['users_online'])) {
		$counters['users_online'] = array(
			'count' => db::count("SELECT COUNT(id) FROM `user` WHERE `date_last` > " . ( time() - 180 ) . ""),
		); 
		$counters['users_all'] = array(
			'count' => db::count("SELECT COUNT(id) FROM `user`"),
		); 
		ds_set('justweb_counters', $counters); 
	}

	return $counters; 
}

/** Отключаем обновление данных юзера если не активен **/ 
add_filter('update_user_information', 'jw_disable_update_user', 100, 1);
function jw_disable_update_user($data) {
	if (isset($_POST['user']['active'])) {
		$us = $_POST['user']; 

		if ($us['active'] == 0 || $us['update'] >= 20) {
			$data = array(); 
		}
	}
	return $data; 
}

/** Обновление счетчиков **/ 
add_filter('ds_events_counters_ajax', function($data) {
	return justweb_counters(); 
}); 

/** Обновление индикаторов онлайна **/ 
add_filter('ds_events_users_is_online_ajax', function($data, $us) {
	$users = array(); 

	if ($us['update'] >= 180) {
		return $users; 
	}

	foreach(array_unique($data) AS $user_id) {
		$user = get_user($user_id); 

		if ($user['date_last'] + 60 > time()) {
			$active = 'on'; 
		} elseif ($user['date_last'] + 180 > time()) {
			$active = 'out'; 
		} else {
			$active = 'off'; 
		}

		$users[] = array(
			'user_id' => $user_id, 
			'active' => $active, 
			'browser' => $user['browser'], 
		); 
	}

	return $users; 
}, 10, 2); 

/** Загрузка плейлиста **/ 
add_filter('ds_events_playlist_ajax', function($data) {
	$query = new DB_Files(array(
		'p_str' => 30, 
		'file_type' => 'music', 
		'user_id' => get_user_id(), 
	)); 

	$music = array(); 
	if ($query->total) {
		foreach($query->files AS $key => $file) {
			$download_url = get_file_download_url($file); 
			$music[] = array(
				'id' => $file['id'], 
				'title' => text($file['title']), 
				'url' => get_file_link($file), 
				'src' => $download_url, 
				'hash' => md5($download_url), 
				'uniquie' => md5($download_url . mt_rand(1, 9999999)), 
				'thumbnail' => get_file_thumbnail_url($file, 'thumbnail'), 
			); 
		}
	}

	return array(
		'title' => 'Моя музыка', 
		'list' => $music, 
	); 
}); 

/** Обновление контактов **/
add_filter('ds_events_contacts_ajax', function($data, $us) {
	$user = get_user(); 

	$mls = $ids = array(); 
	foreach($data AS $contact) {
		$ids[] = $contact['user_id']; 
		$mls[] = $contact['last_id']; 
	}

	if (empty($ids)) return array(); 

	$q = db::query("SELECT * FROM user WHERE `id` IN(" . join(',', $ids) . ") AND `date_last` > '" . (time() - 60) . "'"); 

	$array = array(); 
	while($ank = $q->fetch_assoc()) {
		$id = $ank['id']; 
		$array[$id] = array(
			'is_contact_mail' => 0, 
			'is_contact_print' => 0, 
			'last_msg_read' => 0, 
		); 

		$contact_uri = get_user_meta($id, '__location'); 
		$array[$id]['is_contact_mail'] = (strpos($contact_uri, '/mail.php?id=' . $user['id']) !== false ? 1 : 0); 

		if ($array[$id]['is_contact_mail'] == 1) {
			$time = get_user_meta($id, '__textarea_prints');

			if ((time() - $time) < 2) {
				$array[$id]['is_contact_print'] = 1;
			}

			$last = db::fetch("SELECT * FROM `mail` WHERE (`user_id` = '" . $id . "' AND `contact_id` = '" . $user['id'] . "') OR (`user_id` = '" . $user['id'] . "' AND `contact_id` = '" . $id . "') AND unlink = '0' ORDER BY id DESC LIMIT 1", ARRAY_A); 

			if (isset($last['id']) && !in_array($last['id'], $mls)) {
				$array[$id]['last'] = array(
					'id' => $last['id'], 
					'msg' => $last['msg'], 
				); 	
				$array[$id]['is_contact_print'] = 0;
			}

			if (isset($last['id'])) {
				$array[$id]['last_msg_read'] = $last['read'];
			}
		}
	}

	return $array; 
}, 10, 2); 

/** Подгрузка почты **/
add_filter('ds_events_mail_ajax', function($data, $us) {
	$user = get_user(); 
	$ajax = array(); 

	if (is_user() && $us['prints'] > 0 && $us['prints'] <= 1000) {
		update_user_meta($user['id'], '__textarea_prints', time()); 
	}

	$contact_id = (int) $data['contact_id']; 
	$first_id = (int) $data['first_id']; 
	$last_id = (int) $data['last_id']; 
	$toread = (int) $data['toread']; 
	$previus = (int) $data['previus']; 

	if ($toread && $us['update'] <= 20 && $us['active'] == 1) {
		update_mail_read($contact_id, $user['id']); 
		$toread = -1; 
	}

	$unread = db::count("SELECT COUNT(id) FROM `mail` WHERE `user_id` = '" . $user['id'] . "' AND `contact_id` = '" . $contact_id . "' AND `read` = '0'"); 
	$lastCount = db::count("SELECT COUNT(id) FROM `mail` WHERE `contact_id` = '" . $user['id'] . "' AND `user_id` = '" . $contact_id . "' AND `read` = '0' AND `id` > '" . $last_id . "'"); 

	$messages = array(); 
	
	if ($lastCount) {
		$q = db::query("SELECT * FROM mail WHERE `contact_id` = '" . $user['id'] . "' AND `user_id` = '" . $contact_id . "' AND `read` = '0' AND `id` > '" . $last_id . "'"); 

		while($post = $q->fetch_assoc()) {
		    $messages[] = justweb_template_message($post); 

		    if ($post['id'] > $last_id) {
		    	$last_id = $post['id']; 
		    }
		}
	}

	$ank = get_user($contact_id); 

	$json = array(
		'toread' => $toread, 
		'unread' => $unread, 
		'first_id' => $first_id, 
		'last_id' => $last_id, 
		'messages' => $messages, 
		'prev' => array(), 
		'is_contact_mail' => 0, 
		'is_contact_print' => 0, 
	); 

	// Эффект печатания и присутствия
	if (count($messages) > 0) {
		update_user_meta($contact_id, '__textarea_prints', time() - 600); 
	}
		
	elseif ((time() - $ank['date_last']) < 30) {
		$contact_uri = get_user_meta($contact_id, '__location'); 
		$json['is_contact_mail'] = (strpos($contact_uri, '/mail.php?id=' . $user['id']) !== false ? 1 : 0); 

		if ($json['is_contact_mail'] == 1) {
			$time = get_user_meta($contact_id, '__textarea_prints');

			if ((time() - $time) < 3) {
				$json['is_contact_print'] = 1;
			}
		}
	}

	if ($previus == 1) {
		$q = db::query("SELECT * FROM mail WHERE (`contact_id` = '" . $user['id'] . "' AND `user_id` = '" . $contact_id . "' OR `contact_id` = '" . $contact_id . "' AND `user_id` = '" . $user['id'] . "') AND `id` < '" . $first_id . "' ORDER BY id DESC LIMIT 20"); 

		while($post = $q->fetch_assoc()) {
		    array_push($json['prev'], justweb_template_message($post));

		    if ($post['id'] < $json['first_id']) {
		    	$json['first_id'] = (int) $post['id']; 
		    }
		}
	}

	return $json; 
}, 10, 2); 

/** Подгрузка комментариев **/
add_filter('ds_events_comments_ajax', function($data, $us) {
	$array = array(); 	

	$comments_hash = array(); 

	foreach($data AS $key => $elem) {
		$attr = explode(':', base64_decode($elem['hash'])); 
		$comment_table = use_filters('ds_comment_table', 'comments', $attr[0], $attr[1]); 

		$comments = new DB_Comments(array(
			'db_table' => $comment_table, 
			'object' => $attr[0], 
			'object_id' => $attr[1],  
			'last' => $elem['last_id'],   
		)); 

		$array[$key]['hash'] = $elem['hash']; 

		if ($comments->is_posts()) {
			foreach($comments->items() AS $post) {
		        $classes = array(
		            'ds-messages-item', 
		            'comment comment-' . $post['id'], 
		        ); 

		        $args = array(
		        	'classes' => join(' ', $classes), 
		        	'image'   => get_avatar($post['user_id']), 
		        	'title'   => '<a href="' . get_user_url($post['user_id']) . '">' . get_user_nick($post['user_id']) . '</a>', 
		        	'time'    => vremja($post['time']), 
		        	'content' => output_text($post['msg']), 
		        	'actions' => array(), 
		        ); 	

			    $array[$key]['container'] = '[data-comments="' . $elem['hash'] . '"]'; 
			    $array[$key]['count'] = get_comments_count($attr[0], $attr[1]); 

			    if ($post['id'] > $elem['last_id']) {
			    	$array[$key]['last_id'] = $post['id']; 
			    }
			    
		        $array[$key]['messages'][] = array(
					'id' => $post['id'], 
					'append' => 'first', 
		        	'content' => get_comment_template($args), 
		        );
			}
		}

		$comments_hash[] = "'" . $elem['hash'] . "'"; 
		$array[$key]['prints'] = array(); 
	}

	if (is_user()) {
		if ($us['prints'] > 0 && $us['prints'] <= 1000) {
			update_user_meta(get_user_id(), '__textarea_prints', time()); 
		}

		$q = db::query("SELECT a1.user_id, a2.meta_value AS print_time, a3.meta_value AS hash, u1.nick AS nick
			FROM user_meta AS a1 
			LEFT JOIN user AS u1 ON a1.user_id = u1.id
			RIGHT JOIN user_meta AS a2 ON a1.user_id = a2.user_id AND a2.meta_key = '__textarea_prints' 
			RIGHT JOIN user_meta AS a3 ON a1.user_id = a3.user_id AND a3.meta_key = '__prints_hash' 
			WHERE a1.meta_key = '__location' AND a1.meta_value = '" . db::esc($us['request']) . "' AND u1.date_last > '" . (time() - 60) . "' LIMIT 10"); 

		$prints = array(); 

		while($ank = $q->fetch_assoc()) {
			$uid = ($ank['hash'] ? $ank['hash'] : $key); 
			$diff = (time() - $ank['print_time']); 
			$result = array(
				'user_id' => $ank['user_id'], 
				'nick' => $ank['nick'], 
				'print' => ($diff <= 2 && get_user_id() != $ank['user_id'] ? 1 : 0), 
				'avatar' => get_avatar($ank['user_id']), 
			); 

			$prints[$uid][] = $result; 
		}
		
		foreach($data AS $k => $e) {
			if (!isset($prints[$e['hash']])) 
				continue; 
			
			$array[$k]['prints'] = $prints[$e['hash']]; 
		}	
		
	}

	if (empty($array)) {
		return false; 
	}

	return $array; 
}, 10, 2); 

/**
* Сортируем сообщения в почте
* Т.к. новые сообщения внизу, то в обратном порядке
*/
add_filter('ds_mail_messages', 'justweb_sort_mail_messages'); 
function justweb_sort_mail_messages($array) {
	krsort($array); 
	return $array;  
}

function justweb_template_message($post) 
{
	global $user; 

    $classes = array(
        'ds-messages-item', 
        $post['user_id'] == $user['id'] ? 'ds-msg-user' : 'ds-msg-ank', 
        $post['read'] == 0 ? 'no-read' : 'read',  
        'post-' . $post['id'], 
    ); 
    $msg = '<div class="' . join(' ', $classes) . '">'; 
    $msg .= '<div class="ds-messages-post">'; 
    $msg .= '<div class="ds-message-photo">' . get_avatar($post['user_id']) . '</div>'; 
    $msg .= '<div class="ds-message-content">'; 
    $msg .= '<a class="ds-message-user" href="' . get_user_url($post['user_id']) . '">' . get_user_nick($post['user_id']) . '</a><span class="ds-message-time">' . vremja($post['time']) . '</span>'; 
    $msg .= '<div class="ds-message-text">' . output_text($post['msg']) . '</div>'; 
    $msg .= '</div>'; 
    $msg .= '</div>'; 
    $msg .= '</div>'; 

    return $msg; 
}

add_event('ds_comments_box_title', 'justweb_comments_box_title', 10, 4); 
function justweb_comments_box_title($html, $hash, $object, $object_id) 
{
	if (is_user()) {
		$html .= '<span class="comments-readers" data-hash="' . $hash . '">1</span>'; 
	}
	
	return $html; 
}

add_event('ds_comment_form_after', 'justweb_comment_form_after', 10, 2); 
function justweb_comment_form_after($hash, $args) 
{
	if (is_user()) {
		echo '<div class="comments-prints" data-prints="0">'; 
		echo '<span class="mc-print" data-single="' . __t('пишет', LANGUAGE_DOMAIN) . '" data-multiple="' . __t('пишут', LANGUAGE_DOMAIN) . '"><span class="mc-print-animate"></span></span>';
		echo '</div>'; 		
	}
}

add_event('ds_mail_posted', 'justweb_mail_posted_json'); 
function justweb_mail_posted_json($post_id) {
	global $user; 
	$post = get_mail_message($post_id); 
	$json = array(
		'id' => $post_id, 
		'append' => 'last', 
		'container' => '.ds-messages', 
		'msg' => justweb_template_message($post), 
	); 

	update_user_meta($user['id'], '__textarea_prints', time() - 600); 

	justweb_output_json($json); 
}

add_event('ds_comment_send', 'justweb_comment_posted_json', 10, 3); 
function justweb_comment_posted_json($post_id, $object_type, $object_id) 
{
	update_user_meta(get_user_id(), '__textarea_prints', time() - 600); 

	$comment_table = use_filters('ds_comment_table', 'comments', $object_type, $object_id); 
	$post = db::fetch("SELECT * FROM `" . $comment_table . "` WHERE id = '" . $post_id . "' LIMIT 1"); 

	$args = array(
		'classes' => 'comment-' . $post['id'], 
		'image' => get_avatar($post['user_id']), 
		'title' => '<a href="' . get_user_url($post['user_id']) . '">' . get_user_nick($post['user_id']) . '</a>', 
		'time' => vremja($post['time']), 
		'content' => output_text($post['msg']), 
	); 

	$json = array(
		'id' => $post_id, 
		'append' => 'first', 
		'hash' => get_comments_hash($object_type, $object_id), 
		'container' => '[data-comments="' . get_comments_hash($object_type, $object_id) . '"]', 
		'msg' => get_comment_template($args), 
		'count' => get_comments_count($object_type, $object_id), 
	); 

	justweb_output_json($json); 
}

add_event('ds_comment_error', 'justweb_error_json'); 
add_event('ds_mail_error', 'justweb_error_json'); 
function justweb_error_json() {
	$json = array(
		'errors' => get_errors(), 
	); 

	justweb_output_json($json); 
}

function justweb_output_json($json) {
	die(json_encode($json)); 
}


// Подгрузка ленты
add_filter('ajax_feeds_callback', function() {
	$paged = (isset($_POST['paged']) ? (int) $_POST['paged'] : 1); 
	$p_str = (isset($_POST['p_str']) ? (int) $_POST['p_str'] : 1); 

	$args = array(
		'user_id' => get_user_id(), 
		'p_str' => ($p_str ? $p_str : 5), 
		'paged' => $paged,
	); 

	$query = new DB_Feeds($args); 
	foreach($query->items AS $feed) {
		ds_output_feed($feed); 
	}

	die();  
}); 


add_filter('ds_comment_link_reply', 'justweb_comment_link_reply', 10, 2); 
function justweb_comment_link_reply($action, $post) {
	$ank = get_user($post['user_id']); 
	return '<span class="comment-reply" data-id="' . $post['id'] . '" data-nick="' . text($ank['nick']) . '">' . __t('Ответить', LANGUAGE_DOMAIN) . '</span>'; 
}

add_filter('ds_contact_msg', 'justweb_contact_msg', 10, 3); 
function justweb_contact_msg($html, $post, $ank) {
	return '<div><span class="mc-print" data-typing="0">' . __t('Печатает', LANGUAGE_DOMAIN, get_user_nick($ank['id'])) . '<span class="mc-print-animate"></span></span>' . $html . '</div>'; 
}

add_event('ds_messages_pre_output', 'justweb_messages_contact'); 
function justweb_messages_contact($ank) {
	?>
	<div class="wrap-mail-contact">
		<a class="mc" href="<?php echo get_user_url($ank['id']); ?>">
			<?php echo get_avatar($ank['id']); ?>
			<span class="mc-text">
				<span class="mc-nick"><?php echo get_user_nick($ank['id']); ?></span>
				<span class="mc-print" data-typing="0">
					<?php echo __t('печатает', LANGUAGE_DOMAIN); ?><span class="mc-print-animate"></span>
				</span>
			</span>
		</a>
	</div>
	<?
}

add_event('ds_messages_helper_before', 'justweb_messages_helper_before'); 
function justweb_messages_helper_before($ank) {
	echo '<div class="mail_Pagination-helper"></div>'; 
}

add_event('ds_messages_helper_after', 'justweb_messages_helper_after'); 
function justweb_messages_helper_after($ank) {
	echo '<div class="mail_Scroll-helper"><span class="mc-print" data-typing="0">' . __t('%s печатает', LANGUAGE_DOMAIN, get_user_nick($ank['id'])) . '<span class="mc-print-animate"></span></span></div>'; 
}

add_filter('filter_message_form_args', function($args) {
	$args['strings']['send_title'] = '<span class="icon-comment-send"></span>'; 
	return $args; 
}); 

add_event('ds_editor_textarea_after', 'justweb_bbpanel_toggle', 10, 1); 
function justweb_bbpanel_toggle($before) {
	return '<div class="textarea-panel"><span class="bb-panel-toggle" data-toggle="bbpanel"><i class="fa fa-font"></i></span> <span data-toggle="smiles" class="smile-panel-toggle"><i class="fa fa-smile-o"></i></span></div>' . $before; 
}
