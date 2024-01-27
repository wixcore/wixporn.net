<?php

/***
* Если раздел файлов вызывается через 
* основной ./index.php то ядро не загружаем 
* можно использовать ds_rewrite_rule() для изменения 
* адреса к файлам пользователя
*/ 

if (!defined('ROOTPATH')) {
	define('ROOTPATH', dirname(dirname(dirname(__FILE__)))); 
	require ROOTPATH . '/sys/inc/core.php'; 	
}

$ds_request = ds_get('route_request'); 

// Параметры раздела
$ds_files_config = get_media_type($ds_request['files_type']);

if (!$ds_files_config) {
    ds_die(__('Такого типа файлов не существует')); 
}

ds_set('ds_files_config', $ds_files_config); 

$author_id = 0; 
$file_id = (int) (isset($ds_request['file_id']) ? $ds_request['file_id'] : 0); 

if ($file_id) {
    $file = get_file($file_id); 
    $author_id = $file['user_id'];
} elseif (isset($ds_request['user_id'])) {
	$author_id = (int) $ds_request['user_id']; 
} elseif (isset($ds_request['user_nick'])) {
    $author = db::fetch("SELECT id FROM user WHERE nick = '" . db::esc($ds_request['user_nick']) . "' LIMIT 1");
    if (isset($author['id'])) {
        $author_id = $author['id'];
    }
} elseif (is_user()) {
    $author_id = $user['id']; 
}


if ($author_id) {
    $author = get_user($author_id); 
}

if (!isset($author['id'])) {
    p404(); 
}

$file_id = (int) (isset($ds_request['file_id']) ? $ds_request['file_id'] : 0); 
$term_id = (int) get_query_vars('term_id'); 

if ($term_id == 0) {
    $term = get_files_term_root($author['id'], $ds_request['files_type']); 
    if ($term) {
        $term_id = $term['term_id']; 
    }
} else {
    $term = get_files_term($term_id, $author_id);
}

if (empty($term) && $term_id == 0 && $author_id) {
    $term_id = files_term_create(array(
        'parent' => 0, 
        'user_id' => $author_id, 
        'title' => (isset($ds_files_config['labels']['root_term_name']) ? $ds_files_config['labels']['root_term_name'] : __('Файлы')), 
    ));

    $term = get_files_term($term_id);
}

if ($ds_request['files_type'] != $term['term_type']) {
    ds_die(__('Похоже что вы заблудились!')); 
} elseif (isset($ds_request['token'])) {
    $token = get_uniquie_token($file_id ? $file_id : $term_id); 

    if ($token != $ds_request['token']) {
        ds_die(__('Похоже что вы заблудились!')); 
    }
}

$action = isset($ds_request['action']) ? $ds_request['action'] : 'index'; 

if (!isset($term['term_id'])) {
	p404();
}

$mask = array(
    '%token%' => get_uniquie_token($file_id ? $file_id : $term_id),
    '%files_type%' => $ds_request['files_type'], 
    '%user_nick%' => $author['nick'], 
    '%user_id%' => $author_id, 
    '%term_id%' => isset($term['term_id']) ? $term['term_id'] : 0, 
    '%file_id%' => $file_id, 
    '%action%' => $action, 
); 
ds_set('ds_files_mask', $mask); 

$strings = ds_files_get_labels($ds_files_config['labels'], $mask);
ds_set('ds_files_strings', $strings);

$permalinks = array(); 
foreach($ds_files_config['permalinks'] AS $key => $permalink) {
    $permalinks[$key] = str_replace(array_keys($mask), array_values($mask), $permalink); 
}

do_event('ds_personal_files_init'); 

$includes = use_filters('ds_personal_files_includes', array(
    'create_dir' => 'inc/term.create.php', 
    'edit_dir' => 'inc/term.edit.php', 
    'delete_dir' => 'inc/term.delete.php', 
    'edit_file' => 'inc/file.edit.php', 
    'delete_file' => 'inc/file.delete.php', 
    'index' => 'inc/term.php', 
    'select' => 'inc/select.php', 
    'shoose' => 'inc/select.php', 
    'view' => 'inc/file.php', 
    'upload' => 'inc/upload.php', 
)); 

if (isset($includes[$action])) {
    require $includes[$action];
} else {
    p404();
}