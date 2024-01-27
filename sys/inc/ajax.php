<?php 

do_event('pre_init_ajax'); 

$action = ''; 
if (isset($_GET['action'])) {
	$action = $_GET['action']; 
} elseif (isset($_POST['action'])) {
	$action = $_POST['action']; 
}

if (is_user_access('adm_themes')) {
	if ($action == 'widgets_area_save') {
		$area = (isset($_POST['area_id']) ? $_POST['area_id'] : ''); 
		$area_id = '_widgets-' . $area; 
		update_option($area_id, $_POST['widgets'], 'widgets'); 
		die(json_encode(array('save' => 1))); 
	}

	// Обновление настроек виджета
	if ($action == 'widget_edit_save') {
		$widget_name = (isset($_POST['widget_name']) ? $_POST['widget_name'] : ''); 
		$widget_id = (isset($_POST['widget_id']) ? $_POST['widget_id'] : '_widget_no_id'); 
		$widget_area = (isset($_POST['widget_area']) ? $_POST['widget_area'] : '_widget_no_area'); 
		$widgets = get_widgets(); 

		if (isset($widgets[$widget_name])) {
			$className = $widgets[$widget_name]['id']; 
			$widget = new $className();  
			$widget->setup($widget_id); 
			$widget->save($widget_id, array(
				'widget_name' => $widget_name, 
				'widget_area' => $widget_area, 
			)); 

			$ajax = $widget->instance; 

			die(json_encode($ajax)); 
		}

		$ajax['widget_name'] = $widget_name; 
		$ajax['widget_id'] = $widget_id; 

		die(json_encode($ajax)); 
	}
	
	if ($action == 'widget_edit_form') {
		$widget_name = (isset($_POST['widget_name']) ? $_POST['widget_name'] : ''); 
		$widget_id = (isset($_POST['widget_id']) ? $_POST['widget_id'] : 0); 
		$widget_area = (isset($_POST['widget_area']) ? $_POST['widget_area'] : ''); 

		$widgets = get_widgets(); 

		if (isset($widgets[$widget_name])) {
			$className = $widgets[$widget_name]['id']; 
			$widget = new $className();  
			$widget->setup($widget_id); 

			echo '<form class="widget_form" method="POST">'; 

			if (!$widget_id) {
				$widget_id = '_widget_' . $widget_area . '-' . time(); 
			}

			$widget_form = $widget->form($widget->instance); 

			if ($widget_form !== false) {
				echo '<input type="hidden" name="widget_name" value="' . text($widget_name) . '">';
				echo '<input type="hidden" name="widget_id" value="' . text($widget_id) . '">';
				echo '<input type="hidden" name="widget_area" value="' . text($widget_area) . '">';
				echo '<button class="button button-primary" type="submit">Сохранить</button> <span class="widget-alert"></span>'; 
			} else {
				echo __('Этот виджет не имеет настроек'); 
			}
			echo '</form>'; 

			die();
		}
	}
}

if (is_user_access('plugins')) {

	/**
	* API: Список плагинов
	*/ 

	if ($action == 'plugins_search_api') {
		$search = (isset($_GET['s']) ? urlencode($_GET['s']) : ''); 
		$page = (isset($_GET['page']) ? urlencode($_GET['page']) : 1); 
		$sort = (isset($_GET['sort']) ? urlencode($_GET['sort']) : 'popular'); 

		$list = get_http_content('https://cms-social.ru/api/v1/plugins/list', array(
			's' => $search, 
			'sort' => $sort, 
			'page' => $page, 
		)); 

		$array = json_decode($list, 1); 

		foreach($array['list'] AS $plugin) {
			echo '<div class="cards-item">'; 
			echo '<div class="card">'; 
			echo '<img class="card-image" src="' . $plugin['thumbnail'] . '" />'; 
			echo '<div class="card-title">' . $plugin['title'] . '</div>'; 
			echo '<div class="card-description">' . $plugin['description'] . '</div>'; 

			$author = ($plugin['author_uri'] ? '<a target="_blank" href="' . $plugin['author_uri'] . '">' . $plugin['author'] . '</a>' : $plugin['author']); 
			echo '<div class="card-description">' . __('Автор: %s', $author) . '</div>'; 

			echo '<div class="card-action">'; 
			if (!plugin_exists($plugin['slug'])) {
				echo '<a class="button plugin-install" data-slug="' . $plugin['slug'] . '" href="' . get_admin_url('plugins.php', 'action=install&slug=' . $plugin['slug']) . '">' . __('Установить') . '</a>'; 
			} elseif (!is_plugin_active($plugin['slug'])) {
				echo '<a class="button button-primary" href="' . get_admin_url('plugins.php', 'action=activate&slug=' . $plugin['slug']) . '">' . __('Активировать') . '</a>'; 
			} else {
				echo '<span class="button button-disabled">' . __('Активен') . '</span>'; 
			}
			echo '</div>'; 
			echo '</div>'; 
			echo '</div>'; 
		}
		die(); 
	}	

	/**
	* API: Установка плагина из репозитория
	*/ 

	if ($action == 'plugins_install_api') {
		$plug = (isset($_GET['slug']) ? $_GET['slug'] : ''); 

		ds_plugin_install($plug); 
		
		if (!is_errors()) {
			$ajax = array(
				'status' => 'success', 
				'message' => __('Установлено'), 
				'href' => 'plugins.php?action=activate&slug=' . $plug, 
				'title' => __('Активировать'), 
			); 

		} else {
			$ajax = array(
				'status' => 'error', 
				'message' => __('Установка не удалась'), 
				'errors' => get_errors(), 
			); 
		}


		die(json_encode($ajax)); 
	}

	/**
	* API: Обновление плагина из репозитория
	*/ 

	if ($action == 'plugins_update_api') {
		$plug = (isset($_GET['slug']) ? $_GET['slug'] : ''); 

		do_event('ds_plugin_update', $plug); 

		$active = is_plugin_active($plug); 

		ds_plugin_deactivate($plug); 
		ds_plugin_remove($plug); 
		ds_plugin_install($plug); 
		
		if (!is_errors()) {
			if ($active === true) {
				ds_plugin_activate($plug); 
			}

			$update_file_required = ds_plugin_directory_path($plug) . '/ds-plugin-update.php'; 

			if (is_file($update_file_required)) {
				require $update_file_required; 
				unlink($update_file_required); 
			}

			do_event('ds_plugin_updated', $plug); 

			$ajax = array(
				'status' => 'success', 
				'message' => __('Обновлено'), 
			); 
			
			if (is_file(PATH_CACHE . '/ds_update_plugins.json')) {
				unlink(PATH_CACHE . '/ds_update_plugins.json');
			}
		} else {
			do_event('ds_plugin_updated_error', $plug, get_errors()); 

			$ajax = array(
				'status' => 'error', 
				'message' => __('Установка не удалась'), 
				'errors' => get_errors(), 
			); 
		}

		die(json_encode($ajax)); 
	}
}

if ($action == 'ds_media_manager') {
	$json = array(); 

	if (!is_user()) {
		die(json_encode(array('error' => __('Вы не авторизованы')))); 
	} 

	// Тип файлов
	$mediaType = (isset($_GET['type']) ? db::esc($_GET['type']) : 'files'); 
	$mediaConfig = get_media_type($mediaType);


	$term_id = (isset($_GET['term']) ? (int) $_GET['term'] : 0); 

	if (!$mediaConfig) {
		die(json_encode(array('error' => __('Такого типа файлов не существует')))); 
	} 

	if (!$term_id) {
		$term = get_files_term_root($user['id'], $mediaType); 

		if (empty($term) && $term_id == 0) {
		    $term_id = files_term_create(array(
		        'term_type' => $mediaType, 
		        'privacy' => ($mediaType == 'attachments' ? 'personal' : 'public'), 
		        'parent' => 0, 
		        'user_id' => $user['id'], 
		        'title' => (isset($mediaConfig['labels']['root_term_name']) ? $mediaConfig['labels']['root_term_name'] : __('Файлы')), 
		    ));

		    $term = get_files_term($term_id);
		}
	} else {
		$term = get_files_term($term_id, $user['id']);
	}

	$json['title'] = $term['title']; 
	$json['files_type'] = $mediaType;
	$json['term'] = $term; 
	$json['labels'] = $mediaConfig['labels']; 
	$json['config'] = $mediaConfig; 
	$json['accept'] = join(',', $mediaConfig['accept']); 

	$terms = get_files_terms_child($term['term_id']); 

	$mask = array(
		'%user_nick%' => $user['nick']
	);

	$list = array(); 
	foreach($terms AS $dir) {
		$mask_link = array_merge($mask, array(
		    '%term_id%' => isset($dir['term_id']) ? $dir['term_id'] : 0, 
		));

		$list[] = array(
			'term_id' => $dir['term_id'], 
			'title' => trim($dir['title']), 
			'link' => str_replace(array_keys($mask_link), array_values($mask_link), $mediaConfig['permalinks']['term']), 
			'count' => $dir['files'], 
		); 
	}

	$json['folders'] = $list; 

	$query = new DB_Files(array(
		'files_type' => $mediaType, 
		'user_id' => $user['id'], 
		'term_id' => $term['term_id'], 
	)); 

	$list = array(); 

	if ($query->total) {
		foreach($query->files AS $key => $file) 
		{
			$thumbnail = ''; 
			if (is_file_thumbnail($file['id'], 'thumbnail')) {
				$thumbnail = get_file_thumbnail_url($file['id'], 'thumbnail'); 
			}

			$list[] = array(
				'file_id' => $file['id'], 
				'title' => text($file['title']), 
				'link' => get_file_link($file), 
				'icon' => get_file_icon($file, true), 
				'thumbnail' => $thumbnail, 
				'size' => size_file($file['size']), 
				'type' => $mediaType, 
			); 
		}		
	}

	$json['files'] = $list; 
	$json['filesTotal'] = $query->total; 
	$json['filesPages'] = $query->pages; 
	$json['filesPage'] = $query->paged; 

	die(json_encode($json)); 
}

if ($action == 'ds_files_upload') {
	if (is_user()) {
		if (!empty($_POST['term_id'])) {
			$term_id = (int) $_POST['term_id']; 
		} else {
			$term_id = 0; 
		}

		if (!empty($_POST['term_type'])) {
			$mediaType = db::esc($_POST['term_type']); 
		} else {
			$mediaType = 'attachments'; 
		}

		$ds_files_config = get_media_type($mediaType);

		if (!$term_id) {
			$term = get_files_term_root($user['id'], $mediaType); 

			if (empty($term) && $term_id == 0) {
			    $term_id = files_term_create(array(
			        'term_type' => $mediaType, 
			        'privacy' => ($mediaType == 'attachments' ? 'personal' : 'public'), 
			        'parent' => 0, 
			        'user_id' => $user['id'], 
			        'title' => (isset($ds_files_config['labels']['root_term_name']) ? $ds_files_config['labels']['root_term_name'] : __('Файлы')), 
			    ));

			    $term = get_files_term($term_id);
			}
		} else {
			$term = get_files_term($term_id, $user['id']);
		}
		
		$_FILES['file'] = use_filters('ds_pre_upload_file', $_FILES['file']); 

		$hash = get_file_hash($_FILES['file']['tmp_name']); 
		$duplicate = get_file_duplicate($hash, $_FILES['file']['size']); 

		if (!isset($duplicate['id'])) {
			$uploadFile = file_handle_upload($_FILES['file'], $term['term_id']);
		}
		
		if (!is_errors()) {
			if (!isset($duplicate['id'])) {
				$file = use_filters('json_file_uploaded', $uploadFile); 
			} else {
				$file = $duplicate; 
			}
			
			$thumbnail = ''; 
			if (is_file_thumbnail($file['id'], 'thumbnail')) {
				$thumbnail = get_file_thumbnail_url($file['id'], 'thumbnail'); 
			}

			$json['file'] = array(
				'file_id' => $file['id'], 
				'title' => text($file['title']), 
				'link' => get_file_link($file), 
				'icon' => get_file_icon($file, true), 
				'thumbnail' => $thumbnail, 
				'size' => size_file($file['size']), 
				'type' => $mediaType, 
			);
		} else {
			$json['errors'] = get_errors(); 
		}

		die(json_encode($json));
	}
}

if ($action == 'ds_events') {
	$ajax = array(); 

	if (is_user()) {
		if (isset($_POST['user']['request'])) {
			update_user_meta(get_user_id(), '__location', db::esc($_POST['user']['request'])); 
		}

		if (!empty($_POST['user']['hash'])) {
			update_user_meta(get_user_id(), '__prints_hash', db::esc($_POST['user']['hash'])); 
		}
	}

	foreach(json_decode($_POST['json'], 1) AS $type => $data) 
	{
		$ajax[$type] = use_filters('ds_events_' . $type . '_ajax', $data, $_POST['user']); 
		if ($ajax[$type] === false) {
			unset($ajax[$type]); 
		}
	}

	die(json_encode($ajax)); 
}

if ($action == 'mod_rewrite_test') {
	die('OK'); 
}

if ($action) { 
	do_event('ajax_' . $action . '_callback'); 
}

die(json_encode(array('error' => '0'))); 