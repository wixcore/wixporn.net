<?php 

add_event('pre_init_ajax', 'jw_modal_init'); 
function jw_modal_init() 
{
	add_event('ajax_jw_modal_file_callback', function() {
		$file = get_file($_POST['file_id']); 

		if (strpos($file['mimetype'], 'image/') !== false) {
			$type = 'image'; 
		} elseif (strpos($file['mimetype'], 'video/') !== false) {
			$type = 'video'; 
		}

		$json = array(
			'media_type' => $type, 
			'url' => get_file_link($file), 
			'title' => text($file['title']), 
			'ext' => get_file_ext($file), 
		); 

		if ($type == 'image') {
			$json['content'] = '<div class="jw-modal-image">' . ds_file_thumbnail($file, 'large') . '</div>'; 
		} elseif ($type == 'video') {
			$download = get_file_download_url($file); 
			$json['content'] = '<div class="jw-modal-video"><video src="' . $download . '" controls="" preload="none" autoplay="autoplay"></video></div>'; 
		}

		die(json_encode($json)); 
	}); 
}