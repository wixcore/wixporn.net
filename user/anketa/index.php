<?php 

if (!defined('ROOTPATH')) {
	define('ROOTPATH', dirname(dirname(dirname(__FILE__)))); 
	require ROOTPATH . '/sys/inc/core.php'; 	
}

if (isset($_GET['id'])) {
	$ank = get_user($_GET['id']); 
} elseif (is_user()) { 
	$ank = $user; 
}

$profile = get_user_profile($ank['id']); 

do_event('ds_profile_init', $profile, $ank); 

$set['title'] = __('Анкета %s', $ank['nick']); 

get_header(); 

$general_profile = use_filters('ds_profile_general_fields', array(
    'firstname' => array(
        'title' => __('Имя'), 
        'value' => text($ank['first_name']),
    ), 
    'lastname' => array(
        'title' => __('Фамилия'), 
        'value' => text($ank['last_name']), 
    ), 
    'birthdate' => array(
        'title' => __('Дата рождения'), 
        'value' => ($ank['birthdate'] != '0000-00-00' ? ds_date('d F Y', strtotime($ank['birthdate'])) : ''), 
    ), 
    'city' => array(
        'title' => __('Город'), 
        'value' => text($profile['city']), 
    ), 
), $profile, $ank); 
?>
<div class="breadcrumb">
    <i class="fa fa-user"></i> <?php echo '<a href="' . get_user_url($ank) . '">' . use_filters('ds_user_nick', $ank['nick']) . '</a>'; ?> / <?php echo __('Анкета'); ?>
</div>

<div class="box-group-wrap ds-profile-box">
    <?php do_event('ds_profile_before_output', $profile, $ank); ?>
    <div class="box-group">
        <div class="box-group-title"><?php echo __('Общая информация'); ?></div>
        <div class="box-group-block">
        <?php 
        foreach($general_profile AS $key => $field) {
            if (empty($field['value'])) {
                $field['value'] = __('Не заполнено'); 
            }
            ?>
            <div class="box-meta ds-profile-<?php echo $key; ?>">
                <span class="box-meta-key"><?php echo $field['title']; ?></span> 
                <span class="box-meta-value"><?php echo $field['value']; ?></span>
            </div>
            <?
        }

        do_event('ds_profile_output_end', $profile, $ank); 
        ?>
        </div>
    </div>
    <?php do_event('ds_profile_after_output', $profile, $ank); ?>

    <?php 
    $user_actions = array(); 

    if ((is_user() && $user['id'] == $ank['id']) || is_user_access('user_prof_edit')) {
        $user_actions['profile_edit'] = array(
            'url' => get_site_url('/user/anketa/edit.php?id=' . $ank['id']), 
            'title' => __('Редактировать'), 
            'icon' => '<i class="fa fa-edit"></i>', 
        ); 
    }

    $user_actions = use_filters('ds_profile_action', $user_actions); 

    if ($user_actions) { 
        ?>
        <div class="box-group">
            <div class="box-group-links">
            <?php foreach($user_actions AS $key => $action) : ?>
                <a class="box-link" href="<?php echo $action['url']; ?>"><?php echo $action['icon']; ?> <?php echo $action['title']; ?></a>
            <?php endforeach; ?>
            </div>
        </div>
        <?php         
    }
?>
</div>
<?

get_footer(); 