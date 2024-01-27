<?php 

if (!defined('ROOTPATH')) {
	define('ROOTPATH', dirname(dirname(dirname(__FILE__)))); 
	require ROOTPATH . '/sys/inc/core.php'; 	
}

only_reg('/'); 

if (isset($_GET['id'])) {
	$ank = get_user($_GET['id']); 

	if ($user['id'] != $ank['id'] && !is_user_access('user_edit')) {
		ds_die(__('У вас нет доступа')); 
	}
} else { 
	$ank = $user; 
}

$profile = get_user_profile($ank['id']); 

if (isset($_POST['save_profile'])) {
    foreach($_POST AS $key => $value) {
        if (is_string($_POST[$key])) {
            $_POST[$key] = trim(strip_tags($value)); 
        }
    }
    do_event('ds_save_profile', $_POST, $profile, $ank); 

    if (strlen2($_POST['firstname']) > 64) 
        add_error(__('Имя слишком длинное')); 

    if (strlen2($_POST['lastname']) > 64) 
        add_error(__('Фамилия слишком длинная')); 

    $userEdit = array(); 
    $userEdit['pol'] = ($_POST['pol'] == '1' ? 1 : 0); 

    if (!is_errors()) {
        if ($_POST['birthdate']) {
            $userEdit['birthdate'] = date('Y-m-d', strtotime($_POST['birthdate'])); 
        } else {
            $userEdit['birthdate'] = '0000-00-00'; 
        }
        
        $userEdit['first_name'] = db::esc($_POST['firstname']); 
        $userEdit['last_name'] = db::esc($_POST['lastname']); 

        if (empty($userEdit['first_name'])) {
            $userEdit['first_name'] = '';
        }

        if (empty($userEdit['last_name'])) {
            $userEdit['last_name'] = '';
        }

        $save = use_filters('ds_save_profile_fields', array(
            'city' => (!empty($_POST['city']) ? $_POST['city'] : $profile['city']), 
        ), $_POST); 

        foreach($save AS $key => $value) {
            if ($value == 'unset') {
                db::query("DELETE FROM user_profile WHERE user_id = '" . $ank['id'] . "' AND profile_key = '" . $key . "'"); 
            } elseif (isset($profile[$key]) && $profile[$key] !== null) {
                db::update('user_profile', array( 
                    'profile_value' => $value, 
                ), array(
                    'user_id' => $ank['id'], 
                    'profile_key' => $key, 
                )); 
            } else { 
                db::insert('user_profile', array(
                    'user_id' => $ank['id'], 
                    'profile_key' => $key, 
                    'profile_value' => $value, 
                )); 
            }
        }

        db::update('user', $userEdit, array(
            'id' => $ank['id'], 
        )); 

        $_SESSION['message'] = __('Изменения успешно приняты'); 
        ds_redirect('/user/anketa/?id=' . $ank['id']); 
    }
}

$set['title'] = __('Редактировать анкету %s', $ank['nick']); 

get_header(); 

?>
<div class="breadcrumb">
    <i class="fa fa-user"></i> <?php echo '<a href="' . get_user_url($ank) . '">' . use_filters('ds_user_nick', $ank['nick']) . '</a>'; ?> / <a href="<?php echo get_site_url('/user/anketa/?id=' . $ank['id']); ?>"><?php echo __('Анкета'); ?></a> / <?php echo __('Редактирование'); ?>
</div>
<?
$forms = new Forms('?id=' . $ank['id'], 'POST'); 
$forms->add_field(array(
    'field_name' => 'save_profile', 
    'field_value' => '1', 
    'field_type' => 'hidden', 
)); 

$fields = use_filters('ds_user_profile_general', array(
    array(
        'field_title' => __('Имя'), 
        'field_name' => 'firstname', 
        'field_value' => text($ank['first_name']), 
        'field_type' => 'text', 
    ),
    array(
        'field_title' => __('Фамилия'), 
        'field_name' => 'lastname', 
        'field_value' => text($ank['last_name']), 
        'field_type' => 'text', 
    ),
    array(
        'field_title' => __('Город'), 
        'field_name' => 'city', 
        'field_value' => text($profile['city']), 
        'field_type' => 'text', 
    ),
    array(
        'field_title' => __('Дата рождения'), 
        'field_name' => 'birthdate', 
        'field_value' => text($ank['birthdate']), 
        'field_type' => 'date', 
    ),
    array(
        'field_title' => __('Пол'), 
        'field_name' => 'pol', 
        'field_value' => $ank['pol'], 
        'field_type' => 'select', 
        'field_values' => array(
            array(
                'value' => '1',
                'title' => __('Мужской'),  
            ), 
            array(
                'value' => '0',
                'title' => __('Женский'),  
            ), 
        ), 
    ),
)); 

foreach($fields AS $field) {
    $forms->add_field($field); 
}

$forms->button(__('Сохранить'));
echo $forms->display();

get_footer(); 