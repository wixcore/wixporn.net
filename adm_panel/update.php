<?php 

define('ROOTPATH', dirname(dirname( __FILE__ ))); 
require (ROOTPATH . '/sys/inc/core.php');
user_access( 'update_core', null, 'index.php');

$do = (isset($_GET['do']) ? $_GET['do'] : ''); 
$slug = (isset($_GET['slug']) ? $_GET['slug'] : ''); 
$update = new Update(); 
$update_info = $update->get_latest();  


if (is_confirmed_valid('confirm', 'update')) { 
	if (!class_exists('ZipArchive')) {
		add_error(__('У вас не установлена библиотека \'zip\' для работы с архивами')); 
	}

	elseif ($do == 'core') {
		$arrContextOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		    ),
		); 
		
		$archive = file_get_contents($update_info['latest']['download'], false, stream_context_create($arrContextOptions)); 
		$archive_path = ROOTPATH . '/sys/upgrade/' . basename($update_info['latest']['download']); 

		file_put_contents($archive_path, $archive);

		$version_current = get_version(); 

		$tmpDir = ROOTPATH . '/sys/upgrade/update'; 
		$backupDir = ROOTPATH . '/sys/upgrade/backups'; 
		$backupTmpDir = $tmpDir . '/backup-' . $version_current; 
		$latestTmpDir = $tmpDir . '/latest-' . $update_info['latest']['version']; 

		if (!is_file($archive_path)) {
			add_error(__('Не удалось скачать архив \'%s\'', '<a href="' . $update_info['latest']['download'] . '">' . $update_info['latest']['download'] . '</a>')); 
		}

		elseif (!is_dir($backupDir) && !@mkdir($backupDir, 0777, true)) {
			add_error(__('Не удалось создать папку для резервных копий')); 
		}

		elseif (!is_dir($backupTmpDir) && !@mkdir($backupTmpDir)) {
			add_error(__('Не удалось создать папку для резервной копии')); 
		}

		elseif (!is_dir($latestTmpDir) && !@mkdir($latestTmpDir)) {
			add_error(__('Не удалось создать папку для обновления')); 
		}

		if (!is_errors()) { 
			$zip = new ZipArchive();
		    $zip->open($archive_path, ZipArchive::CREATE);
		    $zip->extractTo($latestTmpDir);

		    $latest_files = ds_readdir_files_list($latestTmpDir); 	

		    foreach($latest_files AS $file) {
		    	$rootfile = str_replace($latestTmpDir, '', $file); 

		    	if (is_file(ROOTPATH . $rootfile)) {
			    	$dirpath = dirname($backupTmpDir . $rootfile); 
			    	if (!is_dir($dirpath)) {
			    		mkdir($dirpath, 0777, true); 
			    	}
			    	
					rename(ROOTPATH . $rootfile, $backupTmpDir . $rootfile); 
		    	}
				
				copy($file, ROOTPATH . $rootfile); 
		    }
		    
		    $zip->close();

		    /**
		    * Создание резервной копии сайта
		    * В архив добавляются только заменяемые файлы 
		    */ 
		    $backup = new ZipArchive();
		    $backup->open($backupDir . '/backup-' . $version_current . '.zip', ZipArchive::CREATE);
		    $latest_dirs = ds_readdir_dir_list($latestTmpDir); 

		    foreach($latest_dirs AS $rootdir) {
		    	$dirName = str_replace($latestTmpDir . '/', '', $rootdir);  
		    	$backup->addEmptyDir($dirName); 
		    }

		    $backup_files = ds_readdir_files_list($backupTmpDir); 	

		    foreach($backup_files AS $file) {
		    	$rootfile = str_replace($backupTmpDir . '/', '', $file); 
		    	$backup->addFile($file, $rootfile); 
		    }

		    $backup->close();

		    @unlink($archive_path); 
		    delete_dir($latestTmpDir); 
		    delete_dir($backupTmpDir); 

		    if (is_file(PATH_CACHE . '/ds_update_core.json')) {
		    	unlink(PATH_CACHE . '/ds_update_core.json'); 
		    }
		}

		if (!is_errors()) {
			$_SESSION['message'] = __('Система успешно обновлена до версии %s', $update_info['latest']['version']); 
			ds_redirect(get_site_url('/adm_panel/info.php?from=' . $version_current . '&to=' . $update_info['latest']['version'])); 			
		}
	}
}

$set['title'] = __('Центр обновлений'); 
get_header_admin(); 

$updateList = get_list_updates(); 

if ($update_info['latest']['version'] <= get_version()) {
	echo __('У вас последняя версия CMS-Social %s', '<a target="_blank" href="' . $update_info['latest']['url'] . '">' . $update_info['latest']['version'] . '</a>'); 
} else {
	?>
	<h4><?php echo __('Система'); ?></h4>

	<div class="list">
		<div class="list-item">
		    <div class="list-item-title"><?php echo __('Доступна новая версия CMS-Social %s', $update_info['latest']['version']); ?></div>
		    <div class="list-item-description"><?php echo __('У вас установлена версия %s, вам доступно обновление до версии %s', get_version(), $update_info['latest']['version']); ?></div>
		    <div class="list-item-action">
		        <a class="button" href="<?php echo get_confirm_url(get_query_url(array('do' => 'core')), 'confirm', 'update'); ?>"><?php echo __('Обновить'); ?></a>
		    </div>
		</div>
	</div>
	<?
}

if (count($updateList['plugins']) > 0) {
?>
	<h4><?php echo __('Плагины'); ?></h4>

	<div class="list">
	<?php 
	foreach($updateList['plugins'] AS $key => $plugin) : 
		$plug_action = array(); 
		$plug_action[] = '<a class="ds-link plugin-update" data-slug="' . $plugin['slug'] . '" href="' . get_confirm_url(get_query_url(array(
			'do' => 'plugins', 
			'slug' => $plugin['slug'])
		), 'confirm', 'update') . '">' . __('Обновить') . '</a>';
		$plug_action[] = '<a target="_blank" href="' . $plugin['url'] . '">' . __('Детали') . '</a>';
	?>
	<div class="list-item">
	    <div class="list-item-title"><?php echo $plugin['title']; ?></div>
	    <div class="list-item-description"><?php echo $plugin['description']; ?></div>
	    <div class="list-item-description">
	    	<?php echo __('Версия: %s', $plugin['version']); ?>	| 
	    	<?php echo __('Автор: %s', '<a href="' . (isset($plugin['authoruri']) ? $plugin['authoruri'] : $plugin['url']) . '">' . $plugin['author'] . '</a>'); ?>	
	    </div>
	    <div class="list-item-action">
	        <?php echo join(' | ', $plug_action); ?>
	    </div>
	</div>
	<?php endforeach; ?>
	</div>
<?	
}

?>
    <script>
    jQuery(function($) {
        $(document).on('click', '.plugin-update', function() {
            var b = $(this); 
	        b.replaceWith(b = $('<span/>', {
	            	class: 'text-process', 
	            	text: '<?php echo __('Обновление'); ?>...', 
	            })); 

            $.ajax(ajax_url, {
                data: 'action=plugins_update_api&slug=' + $(this).data('slug'), 
                dataType: 'json', 
                success: function(resp) {
                    if (resp.status == 'success') {
                        b.replaceWith($('<span/>', {
                        	class: 'text-success', 
                        	text: resp.message, 
                        })); 
                    } else {
                        if (resp.errors) {
                            var wrapErrors = $('<span/>', {
                                class: 'plugin-update-errors', 
                            }); 

                            $(b).replaceWith(wrapErrors); 

                            for(var key in resp.errors) {
                                $(wrapErrors).append($('<span/>', {
                                    class: 'text-error', 
                                    text: resp.errors[key], 
                                })); 
                            }
                        }
                    }
                }
            }); 

            return false; 
        }); 
    }); 
    </script>
<?



get_footer_admin(); 