<?php 

$options = get_user_options($user['id'], 'general'); 

$forms = new Forms('?do=' . $page_id, 'POST'); 
$forms->add_field(array(
    'field_name' => 'save_settings', 
    'field_value' => '1', 
    'field_type' => 'hidden', 
)); 

$languages = get_core_languages(); 

$values = array(); 
foreach($languages AS $lang) {
    if (!is_translations('core', $lang['code']) && $lang['code'] != 'ru_RU') {
        continue; 
    }

    $values[] = array(
        'title' => $lang['native_name'],
        'value' => $lang['code'],
    ); 
}

$fields = use_filters('ds_user_settings_general', array(
    array(
        'field_title' => __('Пунктов на страницу'), 
        'field_name' => 'p_str', 
        'field_value' => $options['p_str'], 
        'field_type' => 'text', 
    ),
    array(
        'field_title' => __('Язык сайта'), 
        'field_name' => 'site_language', 
        'field_value' => '1', 
        'field_type' => 'select', 
        'field_value' => $options['site_language'],
        'field_values' => $values, 
    )
)); 

foreach($fields AS $field) {
    $forms->add_field($field); 
}

$forms->button(__('Сохранить'));
echo $forms->display();

