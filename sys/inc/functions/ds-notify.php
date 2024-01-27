<?php 

/**
* Инициализация типов уведомлений 
*/
function ds_notify_init() 
{
    do_event('ds_notify_init'); 

    $groups = array(
        'comments'  => __('Комментарии'), 
        'friends'   => __('Друзья'), 
    ); 

    foreach($groups AS $uid => $name) {
        register_notify_group($uid, array(
            'title' => $name, 
        )); 
    }

    $default = array(
        'comment_reply' => array(
            'group_id' => 'comments', 
            'title' => __('Ответы'), 
            'callback' => 'ds_notify_comment_reply', 
        ), 
    ); 

    foreach(array('friends_add', 'friends_added') AS $friend_type) {
        $default[$friend_type] = array(
            'group_id' => 'friends', 
            'title' => __('Друзья'), 
            'callback' => 'ds_notify_friends', 
        ); 
    }

    foreach($default AS $type => $args) {
        register_notify($type, $args, $args['group_id']);
    }

    do_event('ds_notify_inited'); 
}

function del_notify($user_id, $from_id = false, $type = false, $group_id = false) 
{
    $args = array(
        'user_id' => $user_id, 
    ); 

    if ($from_id !== false) {
        $args['from_id'] = $from_id; 
    }
    if ($type !== false) {
        $args['type'] = $type; 
    }
    if ($group_id !== false) {
        $args['group_id'] = $group_id; 
    }

    db::delete('notification', $args); 
}

/**
* Добавляет новое уведомление в базу
*/ 
function add_notify($from_id, $user_id, $type, $group_id = 'other', $content = '', $object = '', $object_id = 0) 
{
	if (is_array($content)) {
		$content = json_encode($content, JSON_UNESCAPED_UNICODE); 
	}

	$data = array(
		'from_id'   => $from_id,
        'user_id'   => $user_id,
        'group_id'  => $group_id,  
        'data'      => $content, 
		'type'      => $type,  
		'time'      => time(), 
        'object'    => $object, 
        'object_id' => $object_id, 
	); 

	db::insert('notification', $data); 
}

/**
* Регистрирует новый тип уведомлений
*/ 
function register_notify($type, $args, $group_id = 'other') 
{
	$setup = ds_get('ds_registered_notify', array()); 
    $groups = ds_get('ds_groups_notify', array()); 

	if (!isset($setup[$type])) {
		$setup[$type] = $args; 

        if (isset($groups[$group_id])) {
            $groups[$group_id]['list'][$type] = $type; 
        }
 	}

	ds_set('ds_registered_notify', $setup); 
    ds_set('ds_groups_notify', $groups); 

	do_event('ds_register_notify', $group_id, $type, $args); 
}

/**
* Регистрирует новую группу уведомлений
*/ 
function register_notify_group($uid, $args) 
{
	$groups = ds_get('ds_groups_notify', array()); 

	if (!isset($groups[$uid])) {
		$groups[$uid] = $args; 
 	}

	ds_set('ds_groups_notify', $groups); 
	do_event('ds_register_notify', $uid, $args); 
}

function get_notify_groups() 
{
    $data = ds_get('ds_groups_notify', array()); 
    return $data; 
}

function get_notify_types() 
{
    $data = ds_get('ds_registered_notify', array()); 
    return $data; 
}


/**
* Вывод ответов на комментарии
*/ 
function ds_notify_comment_reply($type, $data, $post) 
{
    $classes = array('ds-messages-item'); 
    $classes[] = ($post['read'] == 0 ? 'no-read' : 'read');

    $args = array(
        'classes' => join(' ', $classes), 
        'image' => get_avatar($post['from_id']), 
        'time' => vremja($post['time']), 
        'title' => __('%s ответил вам на странице %s', '<a href="' . get_user_url($post['from_id']) . '">' . get_user_nick($post['from_id']) . '</a>', '<a href="' . $data['params']['redirect'] . '">' . $data['params']['comments_title'] . '</a>'), 
        'content' => '', 
        'actions' => array(
            'delete' => array(
                'title' => __('Удалить уведомление'), 
                'url' => '?action=delete&id=' . $post['id'], 
            ), 
        ), 
    ); 

    return get_notify_template($args); 
}


/**
* Вывод уведомлений друзей
*/ 
function ds_notify_friends($type, $data, $post) 
{
    $classes = array('ds-messages-item'); 
    $classes[] = ($post['read'] == 0 ? 'no-read' : 'read');

    $actions = array(); 

    if ($type == 'friends_add') {
        $title = __('%s хочет добавить вас в друзья', '<a href="' . get_user_url($data['friend_id']) . '">' . get_user_nick($data['friend_id']) . '</a>'); 
        $actions = get_friends_action_links($post['user_id'], $post['from_id']); 
    } elseif ($type == 'friends_added') {
        $title = __('%s стал вашим другом', '<a href="' . get_user_url($data['friend_id']) . '">' . get_user_nick($data['friend_id']) . '</a>'); 
    } else {
        $title = 'Undefined type of {' . $type . '}';
    }

    $args = array(
        'classes' => join(' ', $classes), 
        'image' => get_avatar($data['friend_id']), 
        'time' => vremja($post['time']), 
        'title' => $title, 
        'content' => '', 
        'actions' => array_merge($actions, array(
            'delete' => array(
                'title' => __('Удалить уведомление'), 
                'url' => '?action=delete&id=' . $post['id'], 
            ))
        ),
    ); 

    return get_notify_template($args); 
}

function get_notify_template($args) 
{
    $template = use_filters('ds_notify_template', '<div class="%post_class%"><div class="ds-messages-post"><div class="ds-message-photo">%post_image%</div><div class="ds-message-content">%post_title%<br />%post_content%<div class="ds-message-time">%post_time%</div></div></div>%post_actions%</div>'); 

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
    	'%post_content%' => ($args['content'] ? '<div class="ds-message-text">' . $args['content'] . '</div>' : ''), 
    	'%post_actions%' => $html_actions, 
    ); 

    return str_replace(array_keys($mask), array_values($mask), $template); 
}