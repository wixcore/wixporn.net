<?php 


// Основной файл системы
require('../sys/inc/core.php'); 

if (!user_access('adm_accesses')) {
    ds_redirect("/?sid=" . time());
}

$action = (isset($_GET['action']) ? text($_GET['action']) : 'list'); 
$profile_id = (isset($_GET['id']) ? $_GET['id'] : 0); 
$group_id = (isset($_GET['group_id']) ? $_GET['group_id'] : ''); 

if ($group_id && get_user_roles($group_id) == null) {
    ds_die(__('Произошла ошибка, роли c ID %s не существует', $group_id)); 
}

if (!empty($_POST['save_accesses'])) {
    db::query("DELETE FROM `user_group_access` WHERE `id_group` = '" . $group_id . "'");

    $accesses = get_user_accesses(); 
    foreach($accesses AS $key => $value) {
        if (isset($_POST[$key]) && $_POST[$key] == 1) {
            db::insert('user_group_access', [
                'id_group' => $group_id, 
                'id_access' => $key, 
            ]);
        }
    }
    
    $_SESSION['message'] = __('Привилегии успешно изменены');
    ds_redirect('?action=edit&group_id=' . $group_id);         
}

if ($group_id) {
    $group = get_user_roles($group_id); 
    $set['title'] = __('Группа: %s', $group['title']);
} else {
    $set['title'] = __('Группы пользователей');
}

get_header_admin(); 

$accesses = get_user_accesses(); 

if ($action == 'edit') {
    $access_list = db::get_var("SELECT id_access FROM `user_group_access` WHERE `id_group` = '" . $group_id . "'", true); 

    $accessFields = []; 
    foreach($accesses AS $key => $value) {
        $accessFields[] = array(
            'field_title' => $value . ' {'.$key.'}', 
            'field_name' => $key, 
            'field_type' => 'checkbox', 
            'field_value' => 1, 
            'field_checked' => (in_array($key, $access_list) ? 1 : 0), 
        ); 
    }

    $fields = use_filters('ds_user_access_fields', $accessFields); 

    $forms = new Forms('?group_id=' . $group_id . '&action=edit', 'POST'); 
    $forms->add_field(array(
        'field_name' => 'save_accesses', 
        'field_value' => '1', 
        'field_type' => 'hidden', 
    )); 

    foreach($fields AS $field) {
        $forms->add_field($field); 
    }

    $forms->button(__('Сохранить'));
    echo $forms->display();    
} else {
    $roles = get_user_roles(); 

    if (!empty($roles)) {
        ?><div class="list"><?
        foreach($roles AS $role_id => $role) 
        {
            $count_access = db::count("SELECT COUNT(*) FROM `user_group_access` WHERE `id_group` = '" . $role_id . "' LIMIT 1"); 

            $action_links = array(); 
            $action_links[] = '<a class="ds-link-edit" href="?group_id=' . $role_id . '&action=edit">' . __('Редактировать') . '</a>'; 
            ?>
            <div class="list-item">
                <div class="list-item-title">
                    <?php echo $role['title']; ?> <?php echo '(L' . $role['level'] . ', ' . $count_access . ')'; ?>
                </div>
                <div class="list-item-action">
                    <?php echo join(' | ', $action_links); ?>
                </div> 
            </div>
            <?
        }
        ?></div><?    
    } else {
        ?>
        <div class="empty empty-ban">
            <?php echo __('Список групп пользователей пуст'); ?>
        </div>
        <?
    }
}

get_footer_admin(); 