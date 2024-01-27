<?php

only_reg();

if (!isset($_GET['id'])) {
    ds_redirect('/konts.php');
}

$ank = get_user($_GET['id']);

if (!$ank) {
    ds_redirect('/konts.php');
}

$contact = get_mail_contact($user['id'], $ank['id']); 

do_event('ds_mail_init', $ank, $contact); 

if (isset($_POST['comments_hash'])) {
    $params = json_decode(base64_decode($_POST['comments_params']), 1); 
    $params_hash = md5($_POST['comments_params'] . ':' . $user['id'] . ':' . SALT_FORMS_FIELDS); 

    if ($params_hash !== $_POST['comments_params_hash']) {
        add_error(__('Данные сообщения не валидны')); 
    }

    if (empty($_POST['attachments']) && empty($_POST['msg'])) {
        add_error(__('Сообщение слишком короткое')); 
    }

    if (!is_errors()) {
        $attachments = (isset($_POST['attachments']) ? $_POST['attachments'] : array()); 

        $content = '<!-- CMS-Social Data {{' . serialize(use_filters('ds_mail_serialize_data', array(
            'user_id' => $user['id'], 
            'contact_id' => $ank['id'], 
            'attachments' => $attachments, 
        ))) . '}} -->' . "\r";
        $content .= $_POST['msg']; 

        $posted = db::insert('mail', array(
            'msg' => use_filters('mail_msg_insert', $content), 
            'time' => time(), 
            'user_id' => get_user_id(), 
            'contact_id' => $ank['id'], 
        )); 

        $post_id = db::insert_id(); 

        if ($posted) {
            add_object_attachments($attachments, array(
                'object' => 'mail', 
                'object_id' => $post_id, 
                'param1_id' => $user['id'], 
                'param2_id' => $ank['id'], 
            )); 

            if (empty($contact)) {
                add_mail_contact($user['id'], $ank['id']);
                add_mail_contact($ank['id'], $user['id']);
            }

            db::query("UPDATE `mail_contacts` SET `time_update` = '" . time() . "' WHERE (`user_id` = '" . get_user_id() . "' AND `contact_id` = '" . $ank['id'] . "') OR (`user_id` = '" . $ank['id'] . "' AND `contact_id` = '" . get_user_id() . "')"); 

            do_event('ds_mail_posted', $post_id); 

            $_SESSION['message'] = __('Сообщение успешно отправлено'); 
            ds_redirect('?id=' . $ank['id'] . '&last_id=' . $post_id); 
        }
    }

    do_event('ds_mail_error'); 
}

update_mail_read($ank['id'], $user['id']); 

$set['title'] = 'Почта: ' . $ank['nick'];

add_body_class('ds-page-mail'); 
get_header(); 

$query = new DB_Mail(array(
    'user_id' => $user['id'], 
    'contact_id' => $ank['id'], 
)); 

do_event('ds_messages_pre_output', $ank, $query); 

echo '<div class="wrap-page-mail">'; 

if ($ank['ban'] == 0) {
    ds_message('mail', array( 
        'title' => __('Сообщения'), 
        'type' => 'mail', 
    ));     
} else {
    echo '<div class="not-form-text">' . __('Пользователь заблокирован') . '</div>'; 
}


echo '<div class="wrap-messages">'; 
do_event('ds_messages_helper_before', $ank, $query); 

$last = array_key_first($query->items);
$first = array_key_last($query->items);
$last_id = (!empty($query->items[$last]) ? $query->items[$last]['id'] : -1); 
$first_id = (!empty($query->items[$first]) ? $query->items[$first]['id'] : -1); 

echo '<div class="ds-messages ds-messages-mail" id="ds-messages-mail" data-contact="' . $ank['id'] . '" data-last="' . $last_id . '" data-first="' . $first_id . '">'; 
if ($query->is_posts()) {
    $query->items = use_filters('ds_mail_messages', $query->items); 
    foreach($query->items AS $post) {
        $classes = array(
            'ds-messages-item', 
            $post['user_id'] == $user['id'] ? 'ds-msg-user' : 'ds-msg-ank', 
            $post['read'] == 0 ? 'no-read' : 'read',  
            'post-' . $post['id'], 
        ); 
        echo '<div class="' . join(' ', $classes) . '">'; 
        echo '<div class="ds-messages-post">'; 
        echo '<div class="ds-message-photo">' . get_avatar($post['user_id']) . '</div>'; 
        echo '<div class="ds-message-content">'; 
        echo '<a class="ds-message-user" href="' . get_user_url($post['user_id']) . '">' . get_user_nick($post['user_id']) . '</a><span class="ds-message-time">' . vremja($post['time']) . '</span>'; 
        echo '<div class="ds-message-text">' . output_text($post['msg']) . '</div>'; 
        echo '</div>'; 
        echo '</div>'; 
        echo '</div>'; 
    }
}
echo '</div>'; 
do_event('ds_messages_helper_after', $ank, $query); 
echo '</div>'; 
echo '</div>'; 

get_footer(); 