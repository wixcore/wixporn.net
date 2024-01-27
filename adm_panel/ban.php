<?php

// Основной файл системы
require('../sys/inc/core.php'); 

if (!user_access('user_ban_set') && !user_access('user_ban_set_h') && !user_access('user_ban_unset')) {
    ds_redirect("/index.php?".SID);
}

$action = (isset($_GET['action']) ? text($_GET['action']) : 'list'); 
$profile_id = (isset($_GET['id']) ? $_GET['id'] : 0); 
$ban_id = (isset($_GET['ban_id']) ? $_GET['ban_id'] : 0); 
$ank = get_user($profile_id);

if ($user['level'] <= $ank['level']) {
    ds_redirect("/index.php?".SID);
}

if ($ban_id) {
    $ban = db::fetch("SELECT * FROM ban WHERE id = " . $ban_id); 
}

if (!empty($_POST['save_ban'])) {

    $calc = array(
        'min'    => 60, 
        'hour'   => 60 * 60, 
        'day'    => 60 * 60 * 24,
        'month'  => 60 * 60 * 24 * 30, 
        'year'   => 60 * 60 * 24 * 365, 
    ); 

    $comment = $_POST['comment'];
    $vremja = (!empty($_POST['vremja']) ? $_POST['vremja'] : 'day'); 
    $time = (int) $_POST['time_until']; 

    $ban['banned_id'] = get_user_id();
    $ban['user_id'] = $ank['id'];

    $ban['time_until'] = (time() + (!empty($_POST['forever']) ? $calc['year'] * 100 : $time * $calc[$vremja])); 
    $ban['reason'] = (!empty($_POST['reason']) ? $_POST['reason'] : '');
    $ban['comment'] = (!empty($_POST['comment']) ? $_POST['comment'] : '');
    $ban['forever'] = (!empty($_POST['forever']) ? 1 : 0);
    $ban['time_create'] = time(); 
    $ban['hide'] = 0; 

    if ($ban['time_until'] < $time) {
        add_error(__('Ошибка времени бана')); 
    }

    if (strlen2($ban['comment']) > 1024) { 
        $err[] = __('Сообщение слишком длинное'); 
    }

    if (!is_errors()) {
        db::insert('ban', $ban); 
        $ban_id = db::insert_id(); 
        $_SESSION['message'] = __('Пользователь успешно заблокирован'); 
        ds_redirect('?id=' . $ank['id']); 
    }
}

if (isset($ban['id'])) {
    if ($action == 'remove') {
        db::delete('ban', ['id' => $ban_id]); 
        $_SESSION['message'] = __('Блокировка успешно удалена'); 
    }    

    if ($action == 'unblock') {
        db::update('ban', ['time_until' => time() - 1, 'forever' => 0], ['id' => $ban_id]); 
        $_SESSION['message'] = __('Пользователь успешно разблокирован'); 
    }    
    
    if ($action != 'list') {
        ds_redirect('?id=' . $ank['id']); 
    }
}



if ($action == 'list') {
    add_event('ds_admin_title_action', function($links = array()) {
        $profile_id = (isset($_GET['id']) ? $_GET['id'] : 0); 

        $links['add'] = array(
            'title' => __('Новая блокировка'), 
            'url'   => get_admin_url('ban.php', 'action=add&id=' . $profile_id), 
        ); 
        
        return $links; 
    });     
}


$set['title'] = __('Бан: %s', $ank['nick']);
get_header_admin(); 

$reasons = get_ban_reasons(); 

if ($action == 'add' || $action == 'edit') {
    $valuesReasons = []; 
    foreach($reasons AS $key => $value) {
        $valuesReasons[] = array(
            'title' => $value,
            'value' => $key,
        ); 
    }

    $valuesFormat = array(
        array(
            'title' => __('Минуты'), 
            'value' => 'min', 
        ), 
        array(
            'title' => __('Часы'), 
            'value' => 'hour', 
        ), 
        array(
            'title' => __('Дни'), 
            'value' => 'day', 
        ), 
        array(
            'title' => __('Месяцы'), 
            'value' => 'month', 
        ), 
        array(
            'title' => __('Годы'), 
            'value' => 'year', 
        ), 
    ); 

    $fields = use_filters('ds_user_ban_fields', array(
        array(
            'field_title' => __('Время бана'), 
            'field_name' => 'time_until', 
            'field_type' => 'text', 
        ),
        array(
            'field_title' => __('Формат времени'), 
            'field_name' => 'vremja', 
            'field_type' => 'radio', 
            'field_value' => 'day',
            'field_values' => $valuesFormat, 
        ),
        array(
            'field_title' => __('Причина'), 
            'field_name' => 'reason', 
            'field_value' => '1', 
            'field_type' => 'select', 
            'field_value' => key($reasons),
            'field_values' => $valuesReasons, 
        ),
        array(
            'field_title' => __('Комментарий'), 
            'field_name' => 'comment', 
            'field_type' => 'textarea', 
        ),
        array(
            'field_title' => __('Заблокировать навсегда'), 
            'field_name' => 'forever', 
            'field_type' => 'checkbox', 
            'field_value' => 1, 
            'field_checked' => 0, 
        ),
    )); 


    $forms = new Forms('?id=' . $ank['id'] . '&action=' . $action, 'POST'); 
    $forms->add_field(array(
        'field_name' => 'save_ban', 
        'field_value' => '1', 
        'field_type' => 'hidden', 
    )); 

    foreach($fields AS $field) {
        $forms->add_field($field); 
    }

    $forms->button(isset($ban['id']) ? __('Сохранить') : __('Заблокировать'));
    echo $forms->display();    
} else {
    $items = db::select("SELECT * FROM ban WHERE user_id = '" . $profile_id . "' ORDER BY time_until DESC, forever ASC"); 

    if (!empty($items)) {
        ?><div class="list"><?
        foreach($items AS $item) 
        {
            $active = ($item['time_until'] > time() || $item['forever'] == 1); 

            $ban_action = array(); 
            if ($active == true) {
                $ban_action[] = '<a class="ds-link-edit" href="?id=' . $ank['id'] . '&ban_id=' . $item['id'] . '&action=unblock">' . __('Снять бан') . '</a>'; 
            }
            
            $ban_action[] = '<a class="ds-link-delete" href="?id=' . $ank['id'] . '&ban_id=' . $item['id'] . '&action=remove">' . __('Удалить') . '</a>'; 
            $info = array(__('Кто заблокировал: %s', '<a href="' . get_user_url($item['banned_id']) . '">' . get_user_nick($item['banned_id']) . '</a>')); 

            if ($item['comment']) {
                $info[] = __('Комментарий: %s', output_text($item['comment'])); 
            }
            ?>
            <div class="list-item <?php echo ($active == true ? 'active' : ''); ?>">
                <div class="list-item-title"><?php echo __('Действует до: %s', $item['forever'] == 0 ? vremja($item['time_until']) : __('Навсегда')); ?></div>
                <div class="list-item-description"><?php echo join('<br />', $info); ?></div>
                <div class="list-item-action">
                    <?php echo join(' | ', $ban_action); ?>
                </div> 
            </div>
            <?
        }
        ?></div><?    
    } else {
        ?>
        <div class="empty empty-ban">
            <?php echo __('Список блокировок пуст'); ?>
        </div>
        <?
    }
}


get_footer_admin(); 