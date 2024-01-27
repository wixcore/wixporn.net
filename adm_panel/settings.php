<?php

require( '../sys/inc/core.php' );

user_access( 'adm_set_sys', null, 'index.php?' . SID );

do_event('ds_admin_settings_init');

$page_id = (isset($_GET['page']) ? text($_GET['page']) : false); 
$settings_page = get_settings_page($page_id); 

if ($settings_page === false) {
    ds_die(__('Страница настроек не найдена')); 
}

if (!empty($settings_page['function'])) {
    add_event('do_admin_settings_fields', $settings_page['function']); 
}

do_event('ds_admin_settings_' . $page_id . '_init');

if (isset($_POST['save_settings'])) {
    $options = use_filters('save_admin_settings_filter', get_validate_post($_POST)); 
    $sections = get_settings_sections($page_id); 
    
    do_event('save_admin_' . $page_id . '_settings', array($options)); 
    do_event('save_admin_settings', $options); 

    $fields = array(); 
    foreach($sections AS $section_id => $section) {
        $section_fields = get_settings_fields($page_id, $section_id);

        foreach($section_fields AS $field) {
            $field['option_type'] = (isset($section['option_type']) ? $section['option_type'] : ''); 
            $fields[] = $field; 
        }
    }
    
    if (!is_errors()) {
        foreach($fields AS $key => $value) {
            if (isset($options[$value['field_name']])) {
                update_option($value['field_name'], $options[$value['field_name']], $value['option_type']); 
            } elseif ($value['field_type'] == 'checkbox') {
                update_option($value['field_name'], 0); 
            }
        }

        ds_set('ds_options', array());
        $_SESSION['message'] = __('Изменения успешно приняты'); 
        ds_redirect($_SERVER['REQUEST_URI']); 
    }
}

$set['title'] = isset($settings_page['page_title']) ? $settings_page['page_title'] : __('Настройки');
get_header_admin(); 

?>
<div class="page-settings"> 
    <form action="<?php echo get_site_url('/adm_panel/settings.php?page=' . $page_id); ?>" method="POST">
        <?php do_event('ds_before_admin_settings', $page_id, $settings_page); ?>
        <?php do_settings_fields($settings_page['id']); ?>
        <?php do_event('ds_after_admin_settings', $page_id, $settings_page); ?>
    </form>
</div>
<?

get_footer_admin(); 