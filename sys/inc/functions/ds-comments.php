<?php 

/**
* Инициализация комментариев
*/ 
function ds_comments_init() 
{
	$user = get_user(); 

	if (!isset($user['id'])) return ; 

	if (isset($_GET['cma']) && isset($_GET['cmt']) && isset($_GET['cmu'])) {

		$comment_id = (int) $_GET['cmu']; 
		$comment_type = my_esc($_GET['cmt']); 
		$comment_table = use_filters('ds_comment_table', 'comments', $comment_type, 0); 

		$comment = db::fetch("SELECT * FROM `" . $comment_table . "` WHERE `id` = '" . $comment_id . "' AND `object` = '" . $comment_type . "' LIMIT 1"); 
		$access = use_filters('is_access_comment_delete', false, $comment_type, $comment_id); 

		if (isset($comment['id'])) {
			if ($user['id'] == $comment['user_id'] || is_user_access('comments_delete') || $access === true) {
				db::query("DELETE FROM `" . $comment_table . "` WHERE `id` = '" . $comment_id . "' LIMIT 1"); 
				$_SESSION['message'] = __('Комментарий успешно удален'); 

				$url = get_query_url(array(
					'cma' => 'unset', 
					'cmu' => 'unset', 
					'cmt' => 'unset', 
				)); 
				ds_redirect($url); 
			}
		}
	}

	if (isset($_POST['comments_hash']) && isset($_POST['comments_type']) && $_POST['comments_type'] == 'comment') {
		$params = json_decode(base64_decode($_POST['comments_params']), 1); 
		$params_hash = md5($_POST['comments_params'] . ':' . $user['id'] . ':' . SALT_FORMS_FIELDS);  

		if ($params_hash !== $_POST['comments_params_hash']) { 
			add_error(__('Данные комментария не валидны')); 
		}

		if (strlen2(trim($_POST['msg'])) == 0 && empty($_POST['attachments'])) {
			return ; 
		}

		if (!is_errors()) {
			$attachments = (isset($_POST['attachments']) ? $_POST['attachments'] : array()); 

	        $content = '<!-- CMS-Social Data {{' . serialize(use_filters('ds_mail_serialize_data', array(
	            'user_id' => $user['id'], 
	            'attachments' => $attachments, 
	        ))) . '}} -->' . "\r";

	        $content .= $_POST['msg']; 

	        $comment_table = use_filters('ds_comment_table', 'comments', $params['object'], $params['object_id']); 

			db::insert($comment_table, array(
				'msg' => use_filters('comment_msg_insert', $content), 
				'time' => time(), 
				'user_id' => get_user_id(), 
				'object' => $params['object'], 
				'object_id' => $params['object_id'], 
			)); 

			$post_id = db::insert_id(); 

            add_object_attachments($attachments, array(
                'object' => 'comment', 
                'object_id' => $post_id, 
                'param1' => $params['object'], 
                'param1_id' => $params['object_id'], 
                'param2' => $comment_table, 
            )); 

			// Уведомление об ответах
			if (!empty($_POST['comments_reply'])) {
				$comment = db::fetch("SELECT * FROM `" . $comment_table . "` WHERE `id` = '" . (int) $_POST['comments_reply'] . "' LIMIT 1"); 

				if (isset($comment['id'])) {
					add_notify($user['id'], $comment['user_id'], 'comment_reply', 'comments', array(
						'params' => $params, 
						'comment_id' => $post_id, 
					), $params['object'], $params['object_id']);
				}
			}
		
			do_event('ds_comment_send', $post_id, $params['object'], $params['object_id']); 

			$_SESSION['message'] = $params['strings']['comment_send'];
			ds_redirect($params['action']); 
		} else {
			do_event('ds_comment_error'); 
		}
	}
}

function ds_message($comments_id, $args = array()) 
{
	$set = get_settings(); 
	$args = ds_message_args($args); 
	$reply_id = (isset($_GET['reply']) ? (int) $_GET['reply'] : 0); 
	$reply_user = (isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0); 
	$string = ''; 

	// Хеш сгенерированной формы
	$hash = get_comments_hash($args['object'], $args['object_id']); 

	do_event('pre_comments', $hash, $args); 

	$encode_params = base64_encode(json_encode($args)); 

	echo '<form id="' . $args['type'] . '-form-' . $hash . '" data-hash="' . $hash . '" class="comments-form" action="' . $args['action'] . '" method="POST" enctype="multipart/form-data">';
	echo '<input type="hidden" name="comments_hash" value="' . $hash . '" />';
	echo '<input type="hidden" name="comments_title" value="' . (isset($args['comments_title']) ? $args['comments_title'] : text($set['title'])) . '" />';
	echo '<input type="hidden" name="comments_type" value="' . $args['type'] . '" />';
	echo '<input type="hidden" name="comments_reply" value="' . $reply_id . '" />';
	echo '<input type="hidden" name="comments_params" value="' . $encode_params . '" />';
	echo '<input type="hidden" name="comments_params_hash" value="' . md5($encode_params . ':' . get_user_id() . ':' . SALT_FORMS_FIELDS) . '" />';

	if (isset($args['object'])) {
		echo '<input type="hidden" name="comments_object" value="' . text($args['object']) . '" />';
	}

	if (isset($args['object_id'])) {
		echo '<input type="hidden" name="comments_object_id" value="' . text($args['object_id']) . '" />';
	}
	
	if ($reply_user) {
		$string = get_user_nick($reply_user) . ', '; 
	}

	echo '<!-- .ds-comment-form -->'; 
	echo '<div class="ds-comment-form">'; 

	echo '<div class="ds-comment-form-attach">'; 
	echo '<div class="choose">'; 
	echo '<span class="choose-attachment" data-hash="' . $hash . '"></span>';
	echo '<div class="choose-types" data-hash="' . $hash . '">';
	$all_media = get_media_types(); 

	foreach($all_media AS $type => $media) {
		if ($media['attachments'] === true) {
			echo '<a data-hash="' . $hash . '" data-type="' . $type . '" data-term="0" data-accept="' . join(',', $media['accept']) . '" class="choose-type choose-type-' . $type . ' load-files"><i class="fa ' . $media['icons']['attachments'] . '"></i> ' . $media['labels']['title'] . '</a>'; 
		}
	}
	echo '</div>';
	echo '</div>';
	echo '</div>';

	echo '<div class="ds-comment-form-textarea">';
	do_event('ds_comment_textarea_before', $hash, $args); 
	ds_editor('msg', $string, array(
		'placeholder' => __('Сообщение'), 
		'hash' => $hash, 
	)); 
	do_event('ds_comment_textarea_after', $hash, $args); 
	echo '</div>'; 

	echo '<div class="ds-comment-form-send">';
	echo '<button name="' . $hash . '" class="button button-comments-send" type="submit">' . $args['strings']['send_title'] . '</button>';
	echo '</div>'; 

	echo '<!-- / .ds-comment-form -->'; 
	echo '</div>';

	echo '<div data-hash="' . $hash . '" id="attachments-' . $hash . '" class="attachments"></div>';
	echo '<div data-hash="' . $hash . '" class="wrap-choose-manager"></div>'; 
	echo '</form>';
	
	do_event('ds_comment_form_after', $hash, $args); 
}

function ds_message_args($args) 
{
	$default = array(
		'action' => get_site_url($_SERVER['REQUEST_URI']), 
		'redirect' => get_site_url($_SERVER['REQUEST_URI']), 
		'type' => 'comment', 
		'strings' => array(
			'comment_send' => __('Комментарий успешно отправлен'), 
			'send_title' => __('Отправить'), 
		),
		'placeholder' => '', 
		'object' => 'none', 
		'object_id' => 0, 
	); 

	return use_filters('filter_message_form_args', array_replace_recursive($default, $args)); 
}

function get_comments_hash($object, $object_id) 
{
	return base64_encode($object . ':' . $object_id); 
}

function get_comments_count($object, $object_id) 
{
	$hash = get_comments_hash($object, $object_id); 

	$counters = ds_get('ds_comments_counters', array()); 
	if (isset($counters[$hash])) {
		return $counters[$hash]; 
	}

	$comment_table = use_filters('ds_comment_table', 'comments', $object, $object_id); 

	$counters[$hash] = db::count("SELECT COUNT(*) FROM `" . $comment_table . "` WHERE `object` = '" . $object . "' AND `object_id` = '" . $object_id . "'"); 
	ds_set('ds_comments_counters', $counters);

	return $counters[$hash]; 
}

function get_panel_comment($type, $args) 
{
	$count = get_comments_count($type, $args['object_id']); 

	$classes = array(); 
	$classes[] = 'link-feed-comment';  

	return '<a class="' . join(' ', $classes) . '" href="' . $args['url'] . '"><i class="fa fa-comment-o" aria-hidden="true"></i><span class="counter">' . $count . '</span></a>'; 
}


function get_comment_template($args) 
{
    $template = use_filters('ds_comment_template', '<div class="%post_class%"><div class="ds-messages-post"><div class="ds-message-photo">%post_image%</div><div class="ds-message-content">%post_title% (%post_time%)<br /><div class="ds-message-text">%post_content%</div></div></div>%post_actions%</div>'); 

    $html_actions = ''; 
    if (!empty($args['actions'])) {
    	$actions = array(); 
    	foreach($args['actions'] AS $action) {
    		if (is_array($action)) {
    			$actions[] = '<a href="' . $action['url'] . '">' . $action['title'] . '</a>'; 
    		} elseif (is_string($action)) {
    			$actions[] = $action; 
    		}
    	}

    	$html_actions = '<div class="post-header-action"><span class="post-action-toggle"><i class="fa fa-ellipsis-h"></i></span><span class="post-action-nav">' . join('', $actions) . '</span></div>'; 
    }

    $mask = array(
    	'%post_class%'   => $args['classes'],
    	'%post_image%'   => $args['image'],
    	'%post_title%'   => $args['title'],
    	'%post_time%'    => $args['time'],
    	'%post_content%' => $args['content'], 
    	'%post_actions%' => $html_actions, 
    ); 

    return str_replace(array_keys($mask), array_values($mask), $template); 
}