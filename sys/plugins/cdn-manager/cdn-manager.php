<?php 

/** 
* Plugin Name: CDN Менеджер файлов
* Description: Храните файлы на удаленном FTP сервере.
* Version: 1.0.0
* Author: ua.lifesheets
*/ 

require dirname(__FILE__) . '/classes/class.CDN_Manager.php';

add_event('ds_init', 'cdn_manager_site_init'); 
function cdn_manager_site_init() 
{
	$plugin_name = basename(dirname(__FILE__)); 

	add_filter('ds_file_download_url', 'cdn_manager_ds_file_download_url', 1, 2); 

	add_event('ds_files_uploaded', 'cdn_manager_file_handle_upload', 1, 1); 
	add_event('ds_file_delete', 'cdn_manager_file_delete', 1, 1); 
}

add_event('ds_admin_init', 'cdn_manager_admin_init'); 
function cdn_manager_admin_init() 
{
	$plugin_name = basename(dirname(__FILE__)); 

	add_menu_admin(__('CDN Менеджер'), $plugin_name, 'adm_set_sys', 'fa-cloud', 80, 'CDN Менеджер', 'cdn_files_admin_page'); 

	add_filter('ds_plugin_' . $plugin_name . '_action', 'cdn_manager_add_settings_link'); 
	add_event('ds_admin_page_' . $plugin_name . '_init', 'cdn_manager_admin_page_init'); 
}

/**
* Загружаем стили и скрипты плагина
*/ 
add_event('init_admin_head', 'cdn_require_scripts'); 
function cdn_require_scripts() {
	ds_theme_style_add(get_site_url(dirname(__FILE__) . '/assets/css/style.css'), 'cdn-storage-style', '', 'all');
	ds_theme_script_add(get_site_url(dirname(__FILE__) . '/assets/js/cdn-main.js'), 'cdn-main', '1.0.0');  
}

/**
* Изменяем ссылку на файл
*/ 
function cdn_manager_ds_file_download_url($origin, $file) 
{
	$cdn_id = get_files_meta($file['id'], 'cdn_id'); 

	if ($cdn_id) {
		$term = get_files_term($cdn_id); 
		$settings = unserialize($term['description']); 
		$cdn_path = cdn_files_dir_hash($file); 

		if ($settings['type'] == 'ftp') {
			$origin = $settings['ftp_url'] . '/' . $cdn_path . '/' . $file['name']; 
		}
	}

	return $origin; 
}

function cdn_manager_admin_page_init() 
{
	$plugin_name = basename(dirname(__FILE__)); 
	add_filter('ds_admin_title_action', 'cdn_manager_admin_title_action', 1, 1); 

	if (isset($_POST['save_settings_cdn'])) {
		$term = get_files_term_root('-1', 'cdn'); 

		if (empty($term)) {
			$term_id = files_term_create(array(
		        'parent' => '0', 
		        'user_id' => '-1', 
		        'title' => 'CDN Хранилище', 
		        'term_type' => 'cdn', 
		        'privacy' => 'private', 
		    ));		

		    $term = get_files_term($term_id); 
		}

		$data = $_POST; 

		if (!empty($data['id'])) {
			$cdn_id = $data['id']; 
		}

		$data['size'] = (intval($data['size']) * 1024 * 1024); 

		unset($data['save_settings_cdn']); 
		unset($data['id']); 
		
		$data = serialize($data); 

		if ($_POST['save_settings_cdn'] == 'add') {
			$cdn_id = files_term_create(array(
		        'parent' => $term['term_id'], 
		        'user_id' => '-1', 
		        'title' => 'CDN Хранилище', 
		        'term_type' => 'cdn', 
		        'privacy' => 'private', 
		        'slug' => '0', 
		        'description' => $data, 
		    ));

		    $_SESSION['message'] = __('Новое хранилище добавлено'); 			
		}

		if ($cdn_id) {
			files_term_update($cdn_id, array(
				'description' => $data, 
		    ));	
		    $_SESSION['message'] = __('Изменения успешно приняты');
		}

		ds_redirect(get_admin_url($plugin_name)); 
	}


	/**
	* Перенос файлов на сайт
	*/ 
	if (isset($_POST['move_files_storage']) && isset($_POST['cdn_id'])) {
		$term = get_files_term($_POST['cdn_id']); 
		$cdn = unserialize($term['description']); 
		$maxsize = 0; 

		$json = array(
			'total_storage' => $term['files'],  
		); 

		if ($term) {
            $cdnmgr = new CDN_Manager($term);

            $query = new DB_Files(array(
                'term_id' => $term['term_id'], 
                'p_str' => 1, 
            )); 

            $json['total_local'] = $query->total; 

            $files = array(); 

            if ($query->total) {
                foreach($query->files AS $key => $file) 
                {
                    if ($cdnmgr->move_to_local($file)) {
                        $maxsize += $file['size']; 
                        $files[] = array(
                            'title' => $file['title'], 
                            'size' => $file['size'], 
                        );
                        $term['files'] -= 1;         
	                    $term['size'] -= $file['size'];       
                    }

                    if ($maxsize >= (10 * 1024 * 1024)) {
                        break; 
                    }
                } 
            }

            // Использовано 
        	$json['total_uses'] = size_file($term['size']); 

        	// Свободно 
        	$json['total_avail'] = size_file($cdn['size'] - $term['size']); 

        	// Колличество файлов в хранилище
            $json['total_storage'] = $term['files']; 

        	// Процент использования 
        	$json['total_percent'] = ceil($term['size'] * 100 / $cdn['size']); 

			$json['files'] = $files; 
		}	

		die(json_encode($json)); 
	}


	/**
	* Перенос файлов в хранилище
	*/ 
	if (isset($_POST['move_files_local']) && isset($_POST['cdn_id'])) {
		$json = array(); 
		$term = get_files_term($_POST['cdn_id']); 
		$cdn = unserialize($term['description']); 

	    $cdn_root = get_files_term_root('-1', 'cdn'); 
	    $cdn_list = get_files_terms_child($cdn_root['term_id']);  

	    $cdn_ids = array(); 
	    foreach($cdn_list AS $t) {
	        $cdn_ids[] = $t['term_id'];
	    }

	    $cdnmgr = new CDN_Manager($term);
	    $cdnmgr->async = true; 

	    if (count($cdn_ids) > 0) {
	        $query = new DB_Files(array(
	            'term_not_in' => $cdn_ids, 
                'p_str' => 1, 
	        ));   

	        $json['total_local'] = $query->total; 

	        $maxsize = 0; 
	        $files = array(); 

	        if ($query->total) {
	            foreach($query->files AS $key => $file) 
	            {
	                if ($cdnmgr->move_to_storage($file)) {
	                    $maxsize += $file['size']; 
	                    $files[] = array(
	                        'title' => $file['title'],  
	                        'size' => $file['size'], 
	                    );   
	                    $term['files'] += 1;
	                    $term['size'] += $file['size']; 
 	                }
	                
	                if ($maxsize >= (10 * 1024 * 1024)) {
	                    break; 
	                }
	            } 
	        }

	        // Свободно 
        	$json['total_avail'] = size_file($cdn['size'] - $term['size']); 

        	// Использовано 
        	$json['total_uses'] = size_file($term['size']); 

        	// Процент использования 
        	$json['total_percent'] = ceil($term['size'] * 100 / $cdn['size']); 
	    }


        $json['total_storage'] = $term['files']; 

		die(json_encode($json)); 
	}


}

function cdn_manager_admin_title_action($links = array()) 
{
	$plugin_name = basename(dirname(__FILE__)); 
	$links['add'] = array(
		'title' => __('Добавить'), 
		'url'   => get_admin_url($plugin_name, 'action=add'), 
	); 

	return $links; 
}

function cdn_files_admin_page() {
	require dirname(__FILE__) . '/cdn-manager-admin.php'; 
}

function cdn_manager_add_settings_link($action) 
{
	$plugin_name = basename(dirname(__FILE__)); 

	$action['settings'] = '<a href="' . get_admin_url($plugin_name) . '">' . __('Настройки') . '</a>';
	return $action; 
}

function cdn_get_free_storage($size) 
{
	$cdn_root = get_files_term_root('-1', 'cdn'); 
	$cdn_list = get_files_terms_child($cdn_root['term_id']); 

	if ($cdn_list) {
		foreach($cdn_list AS $cdn) {
			$settings = unserialize($cdn['description']); 
			$maxsize = intval($settings['size']); 

			if (($cdn['size'] + $size) >= $maxsize) {
				continue;
			}

			return $cdn; 
		}		
	}

	return false; 
}

function cdn_manager_file_handle_upload($file_id) 
{
	$file = get_file($file_id); 
	$cdn_id = get_files_meta($file['id'], 'cdn_id'); 

	if (!$file || $cdn_id) return ;

	if (!$cdn_id) {
		$term = cdn_get_free_storage($file['size']); 

		if ($term) {
			$cdnmgr = new CDN_Manager($term);
			$cdnmgr->move_to_storage($file);
		}	
	}
}

function cdn_manager_file_delete($file) 
{
	$cdn_id = get_files_meta($file['id'], 'cdn_id'); 
	if (!$cdn_id) return ; 

	$cdn = get_files_term($cdn_id); 
	if (!$cdn) return ; 

	$cdnmgr = new CDN_Manager($cdn); 
	$cdnmgr->file_delete($file);
}

function cdn_files_dir_hash($file) 
{
	return $file['mimetype']; 

	$dir = array(); 
	$dir[] = substr($file['hash'], 0, 2);
	$dir[] = substr($file['hash'], 3, 2);

	return join('/', $dir); 
}