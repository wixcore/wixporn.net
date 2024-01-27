<?php 

user_access('adm_set_sys', null, 'index.php?' . SID);

$plugin_name = basename(dirname(__FILE__)); 

$cdn_settings = get_options('cdn_manager_storages');
$action = (isset($_GET['action']) ? urlencode($_GET['action']) : 'list'); 
$type = (isset($_GET['type']) ? urlencode($_GET['type']) : ''); 
$cdn_id = (isset($_GET['id']) ? intval($_GET['id']) : 0); 

if ($cdn_id) {
    $term = get_files_term($cdn_id); 

    if ($term) {
        $cdn = unserialize($term['description']); 
        $type = $cdn['type']; 
    }
}

$cdn_root = get_files_term_root('-1', 'cdn'); 

$server_types = array(
    'ftp' => array(
        'title' => __p('FTP сервер', 'cdn-manager')
    ), 
); 

$breadcrumb = array(); 

if ($action != 'list') {
    $breadcrumb[] = '<a class="breadcrumb-link" href="' . get_admin_url($plugin_name) . '"><i class="fa fa-cloud" aria-hidden="true"></i> ' . __('Главная') . '</a>'; 
    if (!$type && $action == 'add') {
        $breadcrumb[] = '<span class="breadcrumb-text">' . __('Тип хранилища') . '</span>'; 
    }
} else {
    $breadcrumb[] = '<span class="breadcrumb-text"><i class="fa fa-cloud" aria-hidden="true"></i> ' . __('Главная') . '</span>'; 
}

if ($type && $action == 'add') {
    $breadcrumb[] = '<a class="breadcrumb-link" href="' . get_admin_url($plugin_name, 'action=add') . '">' . __('Тип хранилища') . '</a>'; 
    $breadcrumb[] = '<span class="breadcrumb-text">' . __('Добавить') . '</span>'; 
}

if ($action == 'edit') {
    $breadcrumb[] = '<span class="breadcrumb-text">' . __('Редактировать') . '</span>'; 
}

if ($cdn_id && $action == 'delete') {
    $breadcrumb[] = '<a class="breadcrumb-link" href="' . get_admin_url($plugin_name, 'action=edit&id=' . $cdn_id) . '">№' . $term['term_id'] . ' ' . $server_types[$cdn['type']]['title'] . '</a>'; 
    $breadcrumb[] = '<span class="breadcrumb-text">' . __('Удаление') . '</span>'; 
}

if ($breadcrumb) {
    echo '<div class="breadcrumb">' . implode(' <span class="delimiter">&#8250;</span> ', $breadcrumb) . '</div>'; 
}

if (empty($cdn_root)) {
    $term_id = files_term_create(array(
        'parent' => '0', 
        'user_id' => '-1', 
        'title' => 'CDN Хранилище', 
        'term_type' => 'cdn', 
        'privacy' => 'private', 
    ));     

    $cdn_root = get_files_term($term_id); 
}

if ($action != 'list') {
    if ($action == 'add' && empty($type)) {
        echo '<div class="list">'; 
        foreach($server_types AS $key => $value) {
            echo '<div class="list-item">'; 
            echo '<a href="' . get_admin_url($plugin_name, 'action=add&type=' . $key) . '"><img src="' . get_site_url(dirname(__FILE__)) . '/assets/img/' . $key . '.png" /> ' . $value['title'] . '</a>'; 
            echo '</div>'; 
        }
        echo '</div>'; 
    } elseif ($action == 'view') {
        require dirname(__FILE__) . '/includes/storage-view.php'; 
    } elseif ($action == 'delete') {
        require dirname(__FILE__) . '/includes/storage-delete.php'; 
    } else {
        if (isset($type) && is_file(dirname(__FILE__) . '/includes/settings-' . $type . '.php')) {
            require dirname(__FILE__) . '/includes/settings-' . $type . '.php'; 
        }
    }
}

else {
    $cdn_root = get_files_term_root('-1', 'cdn'); 
    $cdn_list = get_files_terms_child($cdn_root['term_id']); 

    if ($cdn_list) {
        echo '<div class="list">'; 
        foreach($cdn_list AS $term) {
            $cdn = unserialize($term['description']); 

            echo '<div class="list-item">'; 
            echo '<div class="list-item-title"><img src="' . get_site_url(dirname(__FILE__)) . '/assets/img/' . $cdn['type'] . '.png" /> <b>' . $server_types[$cdn['type']]['title'] . '</b> (' . des2num($term['files'], array('файл', 'файла', 'файлов')) . ')</div>'; 
            echo '<div class="list-item-description">№' . $term['term_id'] . ' ' . __('Занято %s из %s', size_file($term['size']), size_file($cdn['size'])) . '</div>';

            $percent = 0; 
            if ($term['size']) {
                $percent = ceil($term['size'] * 100 / $cdn['size']);  
            }
            
            echo '<div class="progress"><div style="width: ' . $percent . '%" class="progress-bar"></div></div>';
            
            echo '<div class="list-item-action">'; 
            $action = array(); 
            $action[] = '<a href="' . get_admin_url($plugin_name, 'action=view&id=' . $term['term_id']) . '">' . __('Посмотреть') . '</a>'; 
            $action[] = '<a href="' . get_admin_url($plugin_name, 'action=edit&id=' . $term['term_id']) . '">' . __('Редактировать') . '</a>'; 
            $action[] = '<a class="ds-link-delete" href="' . get_admin_url($plugin_name, 'action=delete&id=' . $term['term_id']) . '">' . __('Удалить') . '</a>'; 

            echo join(' | ', $action); 
            echo '</div>'; 
            echo '</div>'; 
        }
        echo '</div>';         
    }
}