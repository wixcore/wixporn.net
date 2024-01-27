<?php 

/**
* Добавляет связь файла вложения с объектом
* @return array
*/ 
function add_object_attachments($files, $args) 
{
	$attachments = array(); 

	if (is_array($files)) {
		foreach($files AS $file) {
			$data = array_merge(array('file_id' => $file, 'time' => time(), 'user_id' => get_user_id()), $args); 
			db::insert('files_attachments', $data); 
			$attachments[] = db::insert_id(); 
		}
	} elseif (is_numeric($files)) {
		$data = array_merge(array('file_id' => $files, 'time' => time(), 'user_id' => get_user_id()), $args); 
		db::insert('files_attachments', $data); 
		$attachments[] = db::insert_id(); 
	}

	do_event('ds_files_attachments', $attachments, $files, $args); 

	return $attachments; 
}

/**
* Удаляет связь файла вложения с объектами
*/ 
function clear_object_attachments($file) 
{
	$q = db::query("SELECT * FROM `files_attachments` WHERE `file_id` = '" . $file['id'] . "'"); 

	while($post = $q->fetch_assoc()) : 
		switch ($post['object']) {
			case 'comment':
				$comment = db::fetch("SELECT * FROM `" . $post['param2'] . "` WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 

				if (isset($comment['id'])) {
					$array = get_text_array($comment['msg']);

					if (is_array($array['data']['attachments'])) {
						$key = array_search($file['id'], $array['data']['attachments']);
						unset($array['data']['attachments'][$key]); 

				        if (empty($array['content'])) {
				        	db::query("DELETE FROM `" . $post['param2'] . "` WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 
				        } else {
					        $content = '<!-- CMS-Social Data {{' . serialize($array['data']) . '}} -->' . "\r";
					        $content .= $array['content']; 
				        	db::query("UPDATE `" . $post['param2'] . "` SET `msg` = '" . $content . "' WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 
				        }
					}
				}
				break; 


			case 'mail':
				$mail = db::fetch("SELECT * FROM `mail` WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 

				if (isset($mail['id'])) {
					$array = get_text_array($mail['msg']);

					if (is_array($array['data']['attachments'])) {
						$key = array_search($file['id'], $array['data']['attachments']);
						unset($array['data']['attachments'][$key]); 

				        if (empty($array['content'])) {
				        	db::query("DELETE FROM `mail` WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 
				        } else {
					        $content = '<!-- CMS-Social Data {{' . serialize($array['data']) . '}} -->' . "\r";
					        $content .= $array['content']; 
				        	db::query("UPDATE `mail` SET `msg` = '" . $content . "' WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 
				        }
					}
				}
				break; 

			case 'feed': 
				$feed = db::fetch("SELECT * FROM `feeds` WHERE `id` = '" . $post['object_id'] . "' LIMIT 1"); 
				$array = unserialize($feed['content']); 

				if (is_array($array)) {
					$key = array_search($post['id'], $array); 

					if ($key) {
						unset($array[$key]); 
						if (count($array) > 0) {
							update_user_feed($feed['id'], $array);
						} else {
							delete_user_feed($feed['id']);
						}
					}					
				}

				break;

			default:
				do_event("clear_" . $post['object'] . "_attachments", $file['id'], $post['object'], $post['object_id'], $post); 
				break; 
		}
	endwhile; 

	db::query("DELETE FROM `files_attachments` WHERE `file_id` = '" . $file['id'] . "'"); 
}

function delete_dir( $dir )
{
    if ( is_dir( $dir ) ) {
        $od = opendir( $dir );
        while ( $rd = readdir( $od ) ) {
            if ( $rd == '.' || $rd == '..' )
                continue;
            if ( is_dir( "$dir/$rd" ) ) {
                @chmod( "$dir/$rd", 0777 );
                delete_dir( "$dir/$rd" );
            } else {
                @chmod( "$dir/$rd", 0777 );
                @unlink( "$dir/$rd" );
            }
        }
        closedir( $od );
        @chmod( $dir, 0777 );
        return @rmdir( $dir );
    } else {
        @chmod( $dir, 0777 ); 
        @unlink( $dir );
    }
}

function size_file($bytes = 0)
{
	if ($bytes <= 0) {
		return '0B'; 
	}
	
    $symbols = array('B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
    $exp = floor(log($bytes) / log(1024));
    return sprintf('%.2f' . $symbols[$exp], ($bytes / pow(1024, floor($exp))));
}

/**
* Получает URL адрес для выбора файла или папки
* @return string
*/ 
function get_files_select_link($uid, $args = array()) 
{
	$select = get_files_select_rule($uid); 

	$default = array(
		'author_id' => get_user_id(), 
		'term_id' => false, 
	); 

	$args = array_merge($default, $args); 

	if (!empty($args['term_id'])) {
		$term = get_files_term($args['term_id']);
	} else {
		$term = get_files_term_root($args['author_id'], $select['files_type']);
	}
	
	if (!$term) return ''; 

	$types = get_media_type($term['term_type']); 
	$author = get_user($term['user_id']); 

	$mask = use_filters('ds_get_files_select_link_mask', array(
	    '%token%' => get_uniquie_token($term['term_id']), 
	    '%files_type%' => $term['term_type'], 
	    '%user_nick%' => $author['nick'], 
	    '%user_id%' => $author['id'], 
	    '%object%' => $select['object'], 
	    '%term_id%' => isset($term['term_id']) ? $term['term_id'] : 0, 
	    '%action%' => $uid, 
	), $term, $author, $types);

	return str_replace(array_keys($mask), array_values($mask), $types['permalinks']['select']); 
}

/**
* Регистрирует хук правило для выбора файлов и папок
* @return bolean
*/ 
function register_files_select($uid, $args = array()) 
{
	$hooks = ds_get('files_selected_hooks'); 
	$default = array(
		'access' => 'private', 
		'callback' => false, 
		'multiple' => false, 
		'object' => 'file', 
		'files_type' => 'files', 
		'title_page' => __('Выбрать файл'), 
		'title_page_multiple' => __('Выбрать файлы'), 
		'mimetype' => array('*/*'), 
	); 

	$args = use_filters('register_' . $uid . '_select', array_merge($default, $args)); 

	if (!isset($hooks[$uid])) {
		$hooks[$uid] = $args; 
		ds_set('files_selected_hooks', $hooks); 

		return true; 
	}

	return false; 
}

function get_files_select_rule($uid) 
{
	$hooks = ds_get('files_selected_hooks'); 

	if (isset($hooks[$uid])) {
		return use_filters('get_files_' . $uid . '_select', $hooks[$uid]); 
	}

	return false; 
}

function DownloadFile($filename, $name, $mimetype = 'application/octet-stream')
{
    $size = filesize($filename);
    $time = date('r', filemtime($filename));
    $fm   = @fopen($filename, 'rb');
    if (!$fm) {
        header("HTTP/1.1 505 Internal server error");
        return;
    }
    $begin = 0;
    $end   = $size - 1;
    if (isset($_SERVER['HTTP_RANGE'])) {
        if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
            $begin = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
        }
    }
    if (isset($_SERVER['HTTP_RANGE'])) {
        header('HTTP/1.1 206 Partial Content');
    } else {
        header('HTTP/1.1 200 OK');
    }
    
    header('Connection: close');
    header('Content-Type: ' . $mimetype);
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Accept-Ranges: bytes');
    header('Content-Length:' . (($end - $begin) + 1));
    if (isset($_SERVER['HTTP_RANGE'])) {
        header("Content-Range: bytes $begin-$end/$size");
    }
    header("Content-Disposition: attachment; filename=$name");
    header("Content-Transfer-Encoding: binary");
    header("Last-Modified: $time");
    $cur = $begin;
    fseek($fm, $begin, 0);
    while (!feof($fm) && $cur <= $end && (connection_status() == 0)) {
        print fread($fm, min(1024 * 16, ($end - $cur) + 1));
        $cur += 1024 * 16;
    }
    exit;
}


function get_count_files_user($user_id, $term_type, $term_id = 0) 
{
	if ($term_id == 0) {
		$term = get_files_term_root($user_id, $term_type); 
	}

	if ($term) {
		return $term['files'];
	} else {
		return 0; 
	}
}

/**
* Максимальный размер файлов при загрузке
* установленный настройками сервера (bytes)
* @return int 
*/ 
function get_upload_max_filesize($term = NULL) 
{
	$media_type_max_size = false; 

	if (is_numeric($term)) {
		$term = get_files_term($term); 
	}

	$upload_max_filesize = ini_get('upload_max_filesize');

	if (preg_match('#([0-9]*)([a-z]*)#i', $upload_max_filesize, $varrs))
	{
		if ($varrs[2] == 'M') $upload_max_filesize = $varrs[1] * 1048576;
		elseif ($varrs[2] == 'K') $upload_max_filesize = $varrs[1] * 1024;
		elseif ($varrs[2] == 'G') $upload_max_filesize = $varrs[1] * 1024 * 1048576;
	}

	if (!empty($term)) {
		$types = get_media_type($term['term_type']); 

		if ($types['max_file_size'] != '-1' && $types['max_file_size'] < $upload_max_filesize) {
			$upload_max_filesize = $types['max_file_size']; 
		}
	}

	return $upload_max_filesize;
}

/**
* Получает и кеширует данные о файле 
* @return array 
*/ 
function get_file($file_id, $live = false) 
{
	$get_files = ds_get('ds_get_files', array()); 

	if (is_array($file_id)) {
		return use_filters('ds_get_file', $file_id); 
	}
	
	if (!isset($get_files[$file_id]) || $live === true) {
		$get_files[$file_id] = db::fetch("SELECT * FROM files WHERE `id` = '" . $file_id . "' LIMIT 1"); 
		ds_set('ds_get_files', $get_files); 
	}

	if (!isset($get_files[$file_id]['id'])) {
		$get_files[$file_id] = false; 
	}

	return use_filters('ds_get_file', $get_files[$file_id]);
}


function ds_file_delete($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	do_event('ds_file_delete', $file); 

	/**
	* Хук фильтр если следует прекратить удаление файла
	* По умолчанию true 
	*/ 
	$hook = use_filters('__ds_file_delete', true); 

	if ($hook !== true) {
		return ; 
	}

	/**
	* Поиск клонов файла, если 1 файл привязан к нескольким
	* то физически ничего не удаляем
	*/ 
	$clone = db::fetch("SELECT * FROM files WHERE `id` != '" . $file['id'] . "' AND `hash` = '" . $file['hash'] . "' AND `path` = '" . $file['path'] . "' LIMIT 1"); 

	/**
	* Удаляем миниатюры файлов
	*/ 

	if (!isset($clone['id'])) {
		$thumbnails = get_file_thumbnails($file); 
		if (is_array($thumbnails)) {
			foreach($thumbnails AS $thumbnail) {
				if (is_file(ROOTPATH . $thumbnail['file'])) {
					unlink(ROOTPATH . $thumbnail['file']); 
				}
			}

			do_event('ds_deleted_thumbnails', $file); 
		}
	}

	// Удаляем все мета поля
	delete_files_meta($file['id']); 

	// Удаляем все привязки файла к папкам
	delete_file_relation_all($file); 

	/**
	* Удаляем файл 
	*/ 

	if (!isset($clone['id'])) {
		if (is_file(ROOTPATH . $file['path'] . $file['name'])) {
			unlink(ROOTPATH . $file['path'] . $file['name']); 
		}		
	}

	do_event('ds_file_deleted', $file); 

	// Удаляем запись из бд 
	db::query("DELETE FROM files WHERE id = '" . $file['id'] . "' LIMIT 1"); 
}

function get_file_classes($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}
	
	if (!isset($file['id'])) return ''; 

	$file_type = preg_replace('/\/.*/', '', $file['mimetype']); 
	$file_extension = preg_replace('/.*\./', '', $file['name']); 

	$classes = array(); 

	$classes[] = 'ds-file-' . $file_type; 
	$classes[] = 'ds-file-' . $file_type . '-' . $file_extension; 

	return use_filters('ds_file_classes', text(join(' ', $classes))); 
}


function get_upload_error($code = 1) {
	if ($code == 1) {
		$error = __('Размер файла превышает %s', size_file(get_upload_max_filesize())); 
	} elseif ($code == 2) {
		$error = __('Файл слишком большой'); 
	} elseif ($code == 3) {
		$error = __('Загружаемый файл был получен только частично'); 
	} elseif ($code == 4) {
		$error = __('Файл не был загружен'); 
	} elseif ($code == 5) {
		$error = __('Отсутствует временная папка'); 
	} elseif ($code == 6) {
		$error = __('Не удалось записать файл на диск'); 
	} elseif ($code == 7) {
		$error = __('PHP-расширение остановило загрузку файла'); 
	} else {
		$error = __('Файл не был загружен'); 
	}

	return $error; 
}


function array_files_multiple(&$files)
{
    $names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

    foreach ($files as $key => $part) {
        $key = (string) $key;
        if (isset($names[$key]) && is_array($part)) {
            foreach ($part as $position => $value) {
                $files[$position][$key] = $value;
            }
            unset($files[$key]);
        }
    }
}

function get_file_thumbnails($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$is_default = use_filters('default_file_thumbnails', true); 

	if ($is_default) {
		$thumbnails = get_files_meta($file['id'], '_thumbnails'); 
		$thumbnails = unserialize($thumbnails);	
	}

	if (!empty($thumbnails)) {
		return use_filters('ds_get_file_thumbnails', $thumbnails); 
	}
}

function get_file_thumbnail($file, $size = 'thumbnail') 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$thumbnails = get_file_thumbnails($file); 

	if (isset($thumbnails[$size])) {
		return use_filters('ds_get_file_thumbnail', $thumbnails[$size], $thumbnails, $file); 
	} else {
		if (is_array($thumbnails)) {
			$thumbnail = array_pop($thumbnails); 
			return use_filters('ds_get_file_thumbnail', $thumbnail, $thumbnails, $file); 			
		}
	}
}

function ds_file_thumbnail($file, $size = 'thumbnail') 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$thumbnail = get_file_thumbnail($file, $size); 

	if (!$thumbnail) {
		return ;
	}

	$thumbnail_template = use_filters('ds_thumbnail_template', '<img class="%class%" src="%src%" width="' . $thumbnail['width'] . '" height="' . $thumbnail['height'] . '" alt="%alt%" />', $thumbnail, $file); 

	$classes = array(
		'size-' . $size ,
		'aligment-' . ($thumbnail['width'] >= $thumbnail['height'] ? 'horizontal' : 'vertical')
	); 

	$mask = array(); 
	$mask['%class%'] = join(' ', $classes); 
	$mask['%alt%'] = text($file['title']); 
	$mask['%src%'] = get_file_thumbnail_url($file, $size); 

	return str_replace(array_keys($mask), array_values($mask), $thumbnail_template); 
}

function get_file_thumbnail_url($file, $size = 'thumbnail') 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$thumbnail = get_file_thumbnail($file, $size); 

	if (isset($thumbnail['file'])) {
		return use_filters('ds_get_file_thumbnail_url', get_site_url($thumbnail['file']), $thumbnail, $file); 
	}
}

function is_file_thumbnail($file_id, $size = 'thumbnail') 
{
	$thumbnails = use_filters('ds_is_file_thumbnail', get_file_thumbnails($file_id), $size, $file_id); 

	if (isset($thumbnails[$size])) {
		return true; 
	}
	return false; 
}

function get_files_meta($object_id, $meta_key = false, $meta_type = 'files') 
{
	$metadata = ds_get('get_files_meta', array()); 

	if ($meta_key) {
		if (isset($metadata[$object_id][$meta_key])) {
			return $metadata[$object_id][$meta_key]; 
		}
	} else {
		if (isset($metadata[$object_id])) {
			return $metadata[$object_id]; 
		}
	}

	$meta = db::select("SELECT * FROM files_meta WHERE object_id = '" . $object_id . "'"); 

	foreach($meta AS $key => $value) {
		$metadata[$object_id][$value['meta_key']] = $value['meta_value']; 
	}

	ds_set('get_files_meta', $metadata); 

	if ($meta_key) {
		if (isset($metadata[$object_id][$meta_key])) {
			return $metadata[$object_id][$meta_key]; 
		}
	} else {
		if (isset($metadata[$object_id])) {
			return $metadata[$object_id]; 
		}
	}
}

function update_files_meta($object_id, $meta_key, $meta_value, $meta_type = 'files') 
{
	$meta = db::fetch("SELECT * FROM files_meta WHERE object_id = '" . $object_id . "' AND `meta_key` = '" . $meta_key . "' LIMIT 1"); 

	if (is_array($meta_value)) {
		$meta_value = serialize($meta_value); 
	}

	if (isset($meta['meta_id'])) {
		db::query("UPDATE files_meta SET meta_value = '" . $meta_value . "' WHERE meta_id = '" . $meta['meta_id'] . "' LIMIT 1"); 
	} else {
		db::insert('files_meta', array(
			'object_id' => $object_id, 
			'meta_key' => $meta_key, 
			'meta_value' => $meta_value, 
			'meta_type' => $meta_type, 
		)); 
	}
}

function delete_files_meta($object_id, $meta_key = NULL, $meta_value = NULL) 
{
	if ($object_id && $meta_key !== NULL && $meta_value !== NULL) {
		db::query("DELETE FROM files_meta WHERE object_id = '" . $object_id . "' AND `meta_key` = '" . $meta_key . "' AND `meta_value` = '" . $meta_value . "'"); 
	} elseif ($object_id && $meta_key !== NULL) {
		db::query("DELETE FROM files_meta WHERE object_id = '" . $object_id . "' AND `meta_key` = '" . $meta_key . "'"); 
	} elseif ($object_id) {
		db::query("DELETE FROM files_meta WHERE object_id = '" . $object_id . "'"); 
	}
}

function upload_image_thumbnails($file, $meta_update = true) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	libload('verot/class.upload.php'); 

	if (!isset($file['id'])) {
		return false; 
	}

	$sizes = get_media_thumbnails_sizes(); 
	$path_directory = ROOTPATH . $file['path']; 
	$path_name = $path_directory . $file['name']; 

	if (!is_file($path_name)) {
		return false; 
	}

	$thumbnails = array(); 

	foreach($sizes AS $key => $size) {
		$x_y = $size['width'] . 'x' . $size['height']; 

		$upload = new Verot\Upload\Upload($path_name, get_language());

		if ($size['width'] < $upload->image_src_x || $size['height'] < $upload->image_src_y) {
			$upload->image_resize = true; 
			$upload->image_ratio = true;
			$upload->image_ratio_x = true;
			$upload->image_ratio_y = true;

			$upload->file_name_body_add = '_' . $x_y; 
			$upload->image_x = $size['width']; 
			$upload->image_y = $size['height']; 
			$upload->file_auto_rename = false;

			$upload->process($path_directory);

			if ($upload->processed) {
				$thumbnails[$key] = array(
					'file' => $file['path'] . $upload->file_dst_name, 
					'size' => filesize(ROOTPATH . $file['path'] . $upload->file_dst_name), 
					'width' => $upload->image_dst_x, 
					'height' => $upload->image_dst_y, 
				); 
			}
		} 
	}

	if ($meta_update === true && $thumbnails) {
		update_files_meta($file['id'], '_thumbnails', $thumbnails); 
	}
	
	return $thumbnails; 
}


function create_audio_meta($file_id) 
{
	$file = get_file($file_id); 
	$filename = ROOTPATH . $file['path'] . $file['name']; 

	libload('getid3/getid3.php'); 
	libload('verot/class.upload.php');
	
	$getID3 = new getID3;
	$mp3info = $getID3->analyze($filename);
	getid3_lib::CopyTagsToComments($mp3info);

	$sizes = get_media_thumbnails_sizes(); 
	$path_directory = ROOTPATH . $file['path'];

	$thumbnails = array(); 
	if (!empty($mp3info['comments']['picture']) && is_array($mp3info['comments']['picture'])) {
		foreach($mp3info['comments']['picture'] AS $image) {
			$tmp_file = dirname($filename) . '/' . get_basename($filename) . '-thumbnail' . '.jpg'; 

			file_put_contents($tmp_file, $image['data']); 

			foreach($sizes AS $key => $size) {
				$x_y = $size['width'] . 'x' . $size['height']; 

				$upload = new Verot\Upload\Upload($tmp_file, get_language());
				$upload->image_resize = true; 
				$upload->image_ratio = true;
				$upload->image_ratio_x = true;
				$upload->image_ratio_y = true;

				$upload->file_name_body_add = '_' . $x_y; 
				$upload->image_x = $size['width']; 
				$upload->image_y = $size['height']; 
				$upload->file_auto_rename = false;

				$upload->process($path_directory);

				if ($upload->processed) {
					$thumbnails[$key] = array(
						'file' => $file['path'] . $upload->file_dst_name, 
						'size' => $upload->file_src_size, 
						'width' => $upload->image_dst_x, 
						'height' => $upload->image_dst_y, 
					); 
				}
			}

			break; 
		}
	}

	if ($thumbnails) {
		update_files_meta($file_id, '_thumbnails', $thumbnails); 
	}
}


/**
* Получение ID3 тегов из медиа файлов
* @params ID, Array, String path
* @return array | false
*/ 
function getId3Tags($file = NULL)
{
	// ID файла
	if (is_numeric($file)) {
		$file = get_file($file); 
	} 

	// Путь до файла
	if (is_string($file) && is_file($file)) {
		$filename = $file; 
	}
    
    if (isset($file['id'])) {
    	$filename = ROOTPATH . $file['path'] . $file['name']; 
    }
    
    if (!isset($filename) || !is_file($filename)) {
        return false; 
    }

    libload('getid3/getid3.php'); 

    $id3 = new getID3();
    $id3->analyze($filename);

    if (!isset($id3->info)) {
        return false;
    }

    $info = $id3->info;

    if (isset($info['error'])) {
        return false;
    }

    $tags = array(
        'title'  => '',
        'artist' => '',
        'album' => '',
        'year' => '',
    );

    foreach($tags AS $key => $value) 
    {
        $ver = 0; 

        if (!empty($info['id3v1'][$key])) {
            $tags[$key] = $info['id3v1'][$key]; 

            if (isset($info['id3v1']['encoding'])) {
                $tags['encoding'] = $info['id3v1']['encoding']; 
            }
        } elseif (!empty($info['id3v2'][$key])) {
            $tags[$key] = $info['id3v2'][$key]; 
        } elseif (!empty($info['tags']['id3v1'][$key])) {
            $tags[$key] = $info['tags']['id3v1'][$key]; 
        } elseif (!empty($info['tags']['id3v2'][$key])) {
            $tags[$key] = $info['tags']['id3v2'][$key]; 
        }

        if (is_array($tags[$key]) && isset($tags[$key][0])) {
            $tags[$key] = $tags[$key][0]; 
        }

        if (empty($tags[$key])) {
            unset($tags[$key]); 
        } 
    }

    if (!empty($tags['encoding']) && $tags['encoding'] != 'UTF-8') {
        $encoding = $tags['encoding']; 

        foreach($tags AS $key => $value) {
            if ($key == 'encoding' || empty($value) || preg_match('/^([0-9]+)$/', $value)) {
                continue; 
            }

            if (function_exists('iconv')) {
                $tags[$key] = iconv($encoding, 'utf-8//TRANSLIT', $value);
            } elseif (function_exists('mb_convert_encoding')) {
                $tags[$key] = mb_convert_encoding($value, 'utf-8', $encoding);
            }
        }

        unset($tags['encoding']);
    }

    if (isset($tags['year'])) {
        $tags['year'] = (int) $tags['year']; 
        if (!preg_match('/^([0-9]{4})$/', $value)) {
            unset($tags['year']); 
        }
    }

    $tags = use_filters('get_id3_tags', $tags); 
    
    return $tags; 
}


function is_file_mimetype_allowed($mimetype, $type = 'files') 
{
	$types = get_media_type($type); 

	if (!$types) {
		return false; 
	}

	$exp = explode('/', $mimetype); 
	foreach($types['accept'] AS $accept) {
		if (strpos($accept, $exp[0]) === 0) {
			return true; 
		}
	}

	return false; 
}

function is_mimetype_allowed($mimetype = 'audio/*', $mimetypes = array('*/*')) {
	$exp = explode('/', $mimetype); 

	if ($mimetype == '*/*') {
		return true; 
	}

	foreach($mimetypes AS $accept) {
		$dexp = explode('/', $accept);  
		if (strpos($accept, $exp[0]) === 0 && ($dexp[1] == '*' || strpos($accept, $exp[1]) !== false)) {
			return true; 
		}
	}

	return false; 
}

function get_file_hash($pathfile) 
{
	if (is_file($pathfile)) {
		$fh = fopen($pathfile, "rb");
		$hash = md5(fread($fh, 128));
		fclose($fh);

		return $hash; 	
	}

	return false; 
}

function get_file_duplicate($hash, $size, $user_id = 0) 
{
	if ($user_id != 0) {
		$result = db::fetch("SELECT * FROM files WHERE hash = '" . $hash . "' AND size = '" . $size . "' AND user_id = '" . $user_id . "' LIMIT 1"); 
	} else {
		$result = db::fetch("SELECT * FROM files WHERE hash = '" . $hash . "' AND size = '" . $size . "' LIMIT 1"); 
	}

	if ($result) {
		return $result; 
	}

	return false; 
}

function file_duplicate_upload($original, $term_id, $data = array()) 
{
	libload('verot/class.upload.php'); 

	$user_id = get_user_id(); 
	$term = get_files_term($term_id, $user_id); 
	$types = get_media_type($term['term_type']); 
	$upload_dir = get_upload_dir($term['term_type']); 

	$data = array_merge(array(
		'name' => $original['name'], 
		'path' => $original['path'], 
		'date_upload' => date('Y-m-d H:i:s'), 
		'time_upload' => time(), 
		'user_id' => $user_id,
	), $data); 

	db::insert('files', $data); 
	$file_id = db::insert_id(); 

	// Связь файла с папкой
	add_file_relation($file_id, $term['term_id'], $user_id); 

	// Копируем мета инфу
	$metadata = get_files_meta($original['id']); 
	foreach($metadata AS $key => $meta) {
		update_files_meta($file_id, $key, $meta); 
	}

	$file = get_file($file_id, true); 

	$file['full'] = array(
		'permalink' => get_file_link($file), 
		'download' => get_file_download_url($file), 
		'thumbnail' => get_file_thumbnail_url($file, 'thumbnail'), 
	); 

	do_event('ds_files_uploaded', $file, $term, false); 
	do_event('ds_files_' . $term['term_type'] . '_uploaded', $file, $term, false); 

	return $file;
}

function file_handle_upload(&$file, $term_id = 0, $user_id = false) 
{
	libload('verot/class.upload.php'); 
	do_event('handle_upload_init'); 

	if ($user_id == false) {
		$user_id = get_user_id();
	}
	
	$term = get_files_term($term_id, $user_id); 
	$types = get_media_type($term['term_type']); 
	$upload_dir = get_upload_dir($term['term_type']); 

	$upload = new Verot\Upload\Upload($file, get_language());
	$upload->allowed = use_filters('ds_files_allowed', $types['accept']);
	$upload->mime_types = use_filters('files_upload_mime_types', $upload->mime_types); 

	$hash = get_file_hash($upload->file_src_pathname); 
	$duplicate = get_file_duplicate($hash, $upload->file_src_size); 

	// Информация о файле
	$data = array(
		'title' => $upload->file_src_name_body, 
		'size' => $upload->file_src_size, 
		'mimetype' => $upload->file_src_mime, 
		'hash' => $hash,
	); 

	// Информация о хранилище файла
	$storage = use_filters('files_upload_storage_info', array(
		'path' => str_replace(ROOTPATH, '', $upload_dir['path']) . '/', 
	)); 

	if (!$upload->uploaded) {
		add_error($upload->error); 
	}
	
	if ($duplicate) {
		$duplicate_user = get_file_duplicate($hash, $upload->file_src_size, $user_id); 

		if ($duplicate_user) {
			add_error(__('Вы уже загружали этот файл. Вот он %s', '<a href="' . get_file_link($duplicate) . '">' . text($duplicate['title']) . '</a>')); 
		}
	}

	// Имя файла на сервере 
	$save_file_name_body = use_filters('save_file_name_body', md5($upload->file_src_size . ':' . $hash)); 
	$upload->file_new_name_body = $save_file_name_body; 

	if (!is_errors()) {
		if ($duplicate) {
			return file_duplicate_upload($duplicate, $term_id, $data);
		}

		$upload->process($upload_dir['path']);

		if ($upload->error) {
			add_error($upload->error); 
		}

		$upload->clean();

		if (is_errors()) {
			return array(
				'error' => get_errors(),
				'title' => $upload->file_src_name_body, 
				'name' => $upload->file_src_name, 
				'size' => $upload->file_src_size, 
				'mimetype' => $upload->file_src_mime, 
			); 
		}

		$data_insert = use_filters('ds_file_insert_data', array_merge(array(
			'path' => $storage['path'], 
			'name' => $upload->file_dst_name, 
			'date_upload' => date('Y-m-d H:i:s'), 
			'time_upload' => time(), 
			'user_id' => $user_id,
			'file_type' => $term['term_type'], 
		), $data)); 

		db::insert('files', $data_insert); 

		$file_id = db::insert_id(); 

		// Миниатюры изображений
		if ($upload->file_is_image === true) {
			upload_image_thumbnails($file_id); 
		}

		// Мета для аудио
		if (use_filters('upload_audio_meta', true)) {
			create_audio_meta($file_id); 

			$info = getId3Tags($file_id); 

			if (is_array($info)) {
				update_files_meta($file_id, '_information', $info); 

				if (!empty($info['artist']) && !empty($info['title'])) {
					$file_title = $info['artist'] . ' - ' . $info['title']; 
				} elseif (!empty($info['title'])) {
					$file_title = $info['title']; 
				}

				if (!empty($file_title)) {
					db::query("UPDATE files SET title = '" . db::esc($file_title) . "' WHERE id = '" . $file_id . "' LIMIT 1"); 
				}
			}
		}
		
		// Связь файла с папкой
		add_file_relation($file_id, $term['term_id'], $user_id); 

		$file = get_file($file_id, true); 

		$file['full'] = array(
			'permalink' => get_file_link($file), 
			'download' => get_file_download_url($file), 
			'thumbnail' => get_file_thumbnail_url($file, 'thumbnail'), 
		); 
		
		do_event('ds_files_uploaded', $file, $term, true); 
		do_event('ds_files_' . $term['term_type'] . '_uploaded', $file, $term, true); 
		
		return $file; 
	} else {
		return array(
			'error' => get_errors(),
			'title' => $upload->file_src_name_body, 
			'name' => $upload->file_src_name, 
			'size' => $upload->file_src_size, 
			'mimetype' => $upload->file_src_mime, 
		); 
	}

	return false; 
}

function dir_uploads_create($path_directory) 
{
	$path_origin = use_filters('upload_dir_origin', PATH_UPLOADS); 
	$path_upload = str_replace($path_origin . '/', '', $path_directory); 

	if (!is_dir($path_directory)) {
		if (strpos($path_upload, '/')) {
			$expdir = explode('/', $path_upload); 

			$new_path_dir = $path_origin; 
			foreach($expdir AS $dir) 
			{
				$new_path_dir .= '/' . $dir; 
				if (!is_dir($new_path_dir)) {
					if (mkdir($new_path_dir)) {
						chmod($new_path_dir, 0777); 
					}
				}
			}		
		} else {
			if (mkdir($path_directory)) {
				chmod($path_directory, 0777); 
			}
		}
	}

	if (is_dir($path_directory)) {
		return true; 
	}

	return false; 
}

function get_upload_dir($dirname = '') 
{
	$is_dir_date = use_filters('upload_dir_with_date', true); 
	$path_origin = use_filters('upload_dir_origin', PATH_UPLOADS); 
	$path_upload = (!empty($dirname) ? $dirname : ''); 

	if ($is_dir_date) {
		$path_upload .= '/' . date('Y') . '/' . date('m'); 
	}
	
	if ($path_upload) {
		$path_directory = $path_origin . '/' . $path_upload; 
	} else {
		$path_directory = $path_origin; 
	}

	if (!is_dir($path_directory)) {
		if (!dir_uploads_create($path_directory)) {
			$error = 'Error create directory {' . $path_directory . '}'; 
		}
	}

	return array(
		'origin_url' => get_site_url($path_directory), 
		'origin' => $path_origin, 
		'path_url' => get_site_url($path_origin), 
		'path' => $path_directory, 
		'dir' => $path_upload, 
		'error' => (isset($error) ? $error : false), 
	); 
}

function get_media_thumbnails_sizes() 
{
	$sizes = use_filters('media_thumbnails_sizes', array(
		'thumbnail' => array(
			'width' => 128, 
			'height' => 128, 
			'crop' => true, 
		), 
		'medium' => array(
			'width' => 480, 
			'height' => 480, 
			'crop' => true, 
		), 
		'large' => array(
			'width' => 720, 
			'height' => 720, 
			'crop' => true, 
		), 
	)); 

	return $sizes; 
}

/**
* Регистрирует раздел для медиа файлов
*/ 
function register_files_type($uid, $args) 
{
	$default = array(
		'labels' => array(
			'title' => __('Файлы'), 
			'title_select_file' => __('Выбрать файл'), 
			'title_select_term' => __('Выбрать папку'), 
			'title_page' => __('Файлы %user_nick%'), 
			'title_upload' => __('Загрузка файла'), 
			'delete_file_success' => __('Файл успешно удалён'), 
			'upload_file_success' => __('Файл успешно загружен'), 
			'upload_files_success' => __('Файлы успешно загружены'), 
			'checked_files_all' => __('Отметить несколько файлов'), 
			'edit_term' => __('Редактировать папку'), 
			'create_term' => __('Создать папку'), 
			'delete_term' => __('Удалить папку'), 
			'edit_file' => __('Редактировать файл'), 
			'upload_file' => __('Добавить файл'), 
			'delete_file' => __('Удалить файл'), 
			'submit_create_term' => __('Создать'), 
			'submit_save_term' => __('Сохранить'), 
			'submit_save_file' => __('Сохранить'), 
			'edit_term_title' => __('Название'), 
			'edit_term_description' => __('Описание'), 
			'edit_file_title' => __('Название'), 
			'edit_file_description' => __('Описание'), 
			'edit_file_comment' => __('Комментарии'), 
			'delete_term_success' => __('Папка успешно удалена'), 
			'create_term_success' => __('Папка успешно создана'), 
			'edit_term_success' => __('Изменения успешно приняты'), 
			'root_term_name' => __('Файлы'), 
			'page_empty' => __('Папка пуста'), 
			'msg_confirm_file_delete' => __('Вы действительно хотите удалить файл %file_name%?'), 
			'msg_confirm_term_delete' => __('Вы действительно хотите удалить папку %term_name%, и все вложеные файлы?'), 
		), 
		'accept' => array(
			'*/*'
		), 
		'rewrite_rule' => array(
			'%files_type%\/' => 'action=index', 
			'%files_type%\/([A-z\_\-]+)\/' => 'action=$1', 
			'%files_type%\/([A-z\_\-]+)\/([0-9]+)\/' => 'action=$1&user_id=$2', 
			'%files_type%\/([A-z\_\-]+)\/([0-9]+)\/' => 'action=$1&file_id=$2', 
			'%files_type%\/([A-z\_\-]+)\/([A-zА-я0-9\-\_\.]+)\/' => 'action=$1&user_nick=$2', 
			'%files_type%\/([A-z\_\-]+)\/([0-9]+)\/([0-9\/]+)\/' => 'action=$1&user_id=$2&term_id=$3', 
		), 
		'rewrite_accept' => array(
			'mimetype' => 'text/plain', 
			'expansion' => 'txt', 
		), 
		'permalinks' => array(
			'file_view' => get_site_url('/' . $uid . '/view/%file_id%/?term_id=%term_id%'), 
			'upload' => get_site_url('/' . $uid . '/upload/?term_id=%term_id%'), 
			'edit_term' => get_site_url('/' . $uid . '/edit_dir/?term_id=%term_id%'), 
			'edit_file' => get_site_url('/' . $uid . '/edit_file/%file_id%/'), 
			'create_term' => get_site_url('/' . $uid . '/create_dir/?p=%term_id%'), 
			'delete_term' => get_site_url('/' . $uid . '/delete_dir/?term_id=%term_id%'), 
			'delete_file' => get_site_url('/' . $uid . '/delete_file/%file_id%/?term_id=%term_id%'), 
			'term' => get_site_url('/' . $uid . '/index/%user_nick%/?term_id=%term_id%'), 
			'select' => get_site_url('/' . $uid . '/select/?action=%action%&term_id=%term_id%'), 
			'shoose' => get_site_url('/' . $uid . '/select/?action=%action%&term_id=%term_id%&shoose=%file_id%'), 
		),
		'icons' => array( 
			'create_term' => 'fa-plus', 
			'edit_term' => 'fa-edit', 
			'upload_file' => 'fa-upload', 
			'edit_file' => 'fa-edit', 
			'file_download' => 'fa-download', 
			'file_delete' => 'fa-trash', 
			'list_term' => 'fa-folder', 
			'attachments' => 'fa-folder', 
		), 
		'depth' => 10, // Глубина вложенности 
		'max_file_size' => (128 * 1024 * 1024), 
		'attachments' => false, 
		'public' => true, 
		'show_dirs' => true, 
	); 

	if (is_array($args)) {
		if (isset($args['rewrite_rule'])) {
			unset($default['rewrite_rule']); 
		}

		if (isset($args['accept'])) {
			unset($default['accept']); 
		}

		$args = array_replace_recursive($default, $args); 
	}

	$files_types = ds_get('ds_files_types', array());

	if (!isset($files_types[$uid])) {
		$files_types[$uid] = use_filters('ds_register_files_type', $args, $uid); 
		ds_set('ds_files_types', $files_types);
		return true; 
	}

	return false; 
}

function get_media_types() 
{
	$files_types = ds_get('ds_files_types'); 
	return $files_types;  
}

function get_media_type($uid) 
{
	$files_types = ds_get('ds_files_types'); 

	if (isset($files_types[$uid])) {
		return $files_types[$uid]; 
	}

	return false; 
}

function ds_files_get_labels($labels, $mask) 
{
	if (is_array($labels)) {
		foreach($labels AS $key => $value) {
			$labels[$key] = str_replace(array_keys($mask), array_values($mask), $value); 
		}
	}

	return $labels; 
}

function ds_files_get_type() 
{
	$ds_request = ds_get('route_request'); 
	$ds_files_config = get_media_type($ds_request['files_type']);

	if ($ds_files_config) {
		return $ds_request['files_type']; 
	}
}

function handle_files_init() 
{
	register_files_type('photos', array(
		'labels' => array(
			'title' => __('Фотографии'), 
			'title_page' => __('Фотографии %user_nick%'), 
			'create_term' => __('Создать альбом'), 
			'upload_file' => __('Добавить фото'), 
			'root_term_name' => __('Фотографии'), 
		), 
		'accept' => array('image/*'), 
		'icons' => array(
			'upload_file' => 'fa-cloud-upload', 
			'attachments' => 'fa-image', 
		), 
		'depth' => 1, 
		'attachments' => true, 
	)); 

	register_files_type('files', array(
		'labels' => array(
			'title' => __('Файлы'), 
		), 
		'accept' => array('*/*'), 
		'depth' => 10, 
		'attachments' => true, 
	)); 

	register_files_type('music', array(
		'labels' => array(
			'title' => 'Музыка', 
			'root_term_name' => __('Музыка'), 
		), 
		'accept' => array('audio/*'), 
		'icons' => array(
			'attachments' => 'fa-music', 
		), 
		'depth' => 5, 
		'attachments' => true, 
	)); 

	register_files_type('attachments', array(
		'labels' => array(
			'title' => __('Вложения'), 
			'root_term_name' => __('Вложения'), 
		), 
		'accept' => array('*/*'), 
		'icons' => array(
			'attachments' => 'fa-archive', 
		), 
		'permalinks' => array(
			'file_view' => get_site_url('/%files_type%/%token%/view/%file_id%/?term_id=%term_id%'), 
			'upload' => get_site_url('/%files_type%/%token%/upload/?term_id=%term_id%'), 
			'edit_term' => get_site_url('/%files_type%/%token%/edit_dir/?term_id=%term_id%'), 
			'edit_file' => get_site_url('/%files_type%/%token%/edit_file/%file_id%/'), 
			'create_term' => get_site_url('/%files_type%/%token%/create_dir/?p=%term_id%'), 
			'delete_term' => get_site_url('/%files_type%/%token%/delete_dir/?term_id=%term_id%'), 
			'delete_file' => get_site_url('/%files_type%/%token%/delete_file/%file_id%/?term_id=%term_id%'), 
			'term' => get_site_url('/%files_type%/%token%/index/%user_nick%/?term_id=%term_id%'), 
			'select' => get_site_url('/%files_type%/%token%/select/?action=%action%&term_id=%term_id%'), 
			'shoose' => get_site_url('/%files_type%/%token%/select/?action=%action%&term_id=%term_id%&shoose=%file_id%'), 
		),
		'rewrite_rule' => array(
			'%files_type%\/([A-z0-9]{6})\/' => 'action=index&token=$1', 
			'%files_type%\/([A-z0-9]{6})\/([A-z\_\-]+)\/' => 'action=$2&token=$1', 
			'%files_type%\/([A-z0-9]{6})\/([A-z\_\-]+)\/([0-9]+)\/' => 'action=$2&user_id=$3&token=$1', 
			'%files_type%\/([A-z0-9]{6})\/([A-z\_\-]+)\/([0-9]+)\/' => 'action=$2&file_id=$3&token=$1', 
			'%files_type%\/([A-z0-9]{6})\/([A-z\_\-]+)\/([A-zА-я0-9\-\_\.]+)\/' => 'action=$2&user_nick=$3&token=$1', 
			'%files_type%\/([A-z0-9]{6})\/([A-z\_\-]+)\/([0-9]+)\/([0-9\/]+)\/' => 'action=$2&user_id=$3&term_id=$4&token=$1', 
		), 
		'depth' => 0, 
		'attachments' => true, 
		'public' => false, 
	)); 

	$files_types = ds_get('ds_files_types'); 

	foreach($files_types AS $uid => $files) {
		foreach($files['rewrite_rule'] AS $regex => $data) {
			$mask = use_filters('ds_mask_gerex_files', array(
			    '%files_type%' => $uid, 
			), $data); 

			$regex = str_replace(array_keys($mask), array_values($mask), $regex); 

			ds_rewrite_rule($regex, ROOTPATH . '/user/files/index.php', 'files_type=' . $uid . '&' . $data);
		}
	}
	
	// Правила для установки аватара
	if (use_filters('ds_avatar_register', true) === true) {
	    register_files_select('setup_avatar', array(
	    	'mimetype' => array(
	    		'image/*', 
	    	), 
	    	'files_type' => 'photos', 
	    	'title_page' => __('Выбрать фотографию'), 
	    	'title_page_multiple' => __('Выбрать фотографии'), 
	    	'redirectUrl' => get_site_url('/avatar.php?file_id=%file_id%'), 
	    )); 

	    add_filter('ds_file_deleted', function($file) {
	    	delete_user_meta($file['user_id'], '__avatar', $file['id']); 
	    }); 		
	}
}

function is_files_term_depth_access($term_id) 
{
	$term = get_files_term($term_id); 
	$types = get_media_type($term['term_type']); 

	$depth = use_filters('files_term_depth_access', $types['depth'], $types); 

	if ($depth !== '-1') {
		$terms_parents = get_files_terms_parents($term_id); 
		if ((count($terms_parents) - 1) >= $depth) {
			return false; 
		}
	}
	
	return true; 
}

function get_files_term($term_id = 0, $user_id = 0, $type = '') 
{
	$ds_files_terms = ds_get('ds_files_terms', array()); 

	if (isset($ds_files_terms[$term_id])) {
		return use_filters('ds_get_files_term', $ds_files_terms[$term_id]); 
	}

	$ds_files_terms[$term_id] = db::fetch("SELECT * FROM files_terms WHERE `term_id` = '" . $term_id . "' LIMIT 1"); 		

	ds_set('ds_files_terms', $ds_files_terms); 

	if (!empty($ds_files_terms[$term_id])) {
		return use_filters('ds_get_files_term', $ds_files_terms[$term_id]); 
	}
}

function get_files_term_root($user_id = 0, $type = 'files') 
{
	$term = db::fetch("SELECT * FROM files_terms WHERE `parent` = '0' AND `user_id` = '" . $user_id . "' AND `term_type` = '" . $type . "' LIMIT 1");

	if ($term) {
		return $term; 
	}
}

function get_files_terms_child($term_id) 
{
	$ds_files_terms_child = ds_get('ds_files_terms_child', array()); 

	if (isset($ds_files_terms_child[$term_id])) {
		return $ds_files_terms_child[$term_id]; 
	}

	$ds_files_terms_child[$term_id] = db::select("SELECT * FROM files_terms WHERE `parent` = '" . $term_id . "'"); 		
	ds_set('ds_files_terms_child', $ds_files_terms_child); 

	if (isset($ds_files_terms_child[$term_id])) {
		return $ds_files_terms_child[$term_id]; 
	}
}

function get_files_terms_parents($term_id = 0, $array = array()) 
{
	if ($term_id) {
		$term = db::fetch("SELECT * FROM files_terms WHERE `term_id` = '" . $term_id . "' LIMIT 1");

		if ($term) {
			array_push($array, array(
				'term_id' => $term['term_id'], 
				'title'   => $term['title'], 
				'files'   => $term['files'], 
			)); 

			if ((int) $term['parent'] > 0) {
				$array = get_files_terms_parents($term['parent'], $array); 
			}
		}
	}

	return $array; 
}

function files_term_create($args = array()) 
{
	if (!isset($args['user_id'])) {
		$args['user_id'] = get_user_id(); 
	}

	if (!isset($args['parent'])) {
		$args['parent'] = 0; 
	}

	if ($args['parent']) {
		$terms = array_reverse(get_files_terms_parents($args['parent'])); 
		$tree = array(); 

		foreach($terms AS $term) {
			$tree[] = $term['term_id']; 
		}
		$args['path'] = implode('/', $tree);  
	}

	if (!isset($args['term_type'])) {
		$args['term_type'] = ds_files_get_type(); 
	}

	db::insert('files_terms', $args); 

	return db::insert_id(); 
}

function files_term_update($term, $args = array()) 
{
	if (is_numeric($term)) {
		$term = get_files_term($term); 
	}

	if (!empty($args['parent'])) {
		$parents = array_reverse(get_files_terms_parents($args['parent'])); 
		$tree = array(); 

		foreach($parents AS $parent) {
			$tree[] = $parent['term_id']; 
		}
		$args['path'] = implode('/', $tree);  
	}

	db::update('files_terms', $args, array('term_id' => $term['term_id'])); 
}


/**
* Получает все дочерние каталоги директории
*/ 
function get_files_terms_childs($term_id) 
{
	$term = get_files_term($term_id);  

	$parent_ids = array($term_id); 

	if ($term['parent'] == 0) {
		$parents = get_files_terms_child($term['term_id']); 

		foreach($parents AS $parent) {
			$parent_ids[] = $parent['term_id']; 
			$join_term[] = "`path` LIKE '" . $parent['term_id'] . "/%'"; 
		}
		$join_term[] = "`path` LIKE '" . $parent['parent'] . "/%'"; 
	}

	$join_term[] = "`parent` IN(" . join(',', $parent_ids) . ")";
	$join_term[] = "`path` LIKE '%/" . $term_id . "/%'";

	$childs = db::select("SELECT * FROM files_terms WHERE " . join(' OR ', $join_term)); 	

	return $childs; 
}


/** 
* Удаляет папку и все вложеные папки и файлы
*/ 
function files_term_delete($term, $args = array()) 
{
	if (is_numeric($term)) {
		$term = get_files_term($term); 
	}

	$ids = array($term['term_id']); 
	$childs = get_files_terms_childs($term['term_id']); 

	if ($childs) {
		foreach($childs AS $child) {
			$ids[] = $child['term_id']; 
		}
	}

	do_event('ds_files_terms_delete', $ids); 

	$relations = db::select("SELECT * FROM files_relation WHERE term_id IN(" . join(',', $ids) . ")"); 

	foreach($relations AS $item) {
		ds_file_delete($item['file_id']); 
	}

	db::query("DELETE FROM files_terms WHERE term_id IN(" . join(',', $ids) . ")"); 
	do_event('ds_files_terms_deleted', $ids); 
}

/**
* Удаляет все привязки файла к каталогам
*/ 
function delete_file_relation_all($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file);
	}

	$relations = db::select("SELECT * FROM files_relation WHERE file_id = '" . $file['id'] . "'"); 

	foreach($relations AS $relation) {
		$terms = get_files_terms_parents($relation['term_id']); 

		$ids = array($relation['term_id']); 
		foreach($terms AS $t) {
			$ids[] = $t['term_id'];
		}

		$ids = array_unique($ids); 

		db::query("UPDATE files_terms SET files = files - '1' WHERE term_id IN(" . implode(',', $ids) . ")") . PHP_EOL;	
	}
	
	db::query("DELETE FROM files_relation WHERE file_id = '" . $file['id'] . "'") . PHP_EOL; 
}

function delete_file_relation($file_id, $term_id = NULL, $user_id = NULL) 
{
	$relation = db::fetch("SELECT * FROM files_relation WHERE term_id = '" . $term_id . "' AND user_id = '" . $user_id . "' AND file_id = '" . $file_id . "' LIMIT 1"); 

	if ($relation) {
		$terms = get_files_terms_parents($term_id); 

		$ids = array($term_id); 
		foreach($terms AS $t) {
			$ids[] = $t['term_id'];
		}

		db::query("UPDATE files_terms SET files = files - '1' WHERE term_id IN(" . implode(',', $ids) . ")");	
		db::query("DELETE FROM files_relation WHERE term_id = '" . $term_id . "' AND user_id = '" . $user_id . "' AND file_id = '" . $file_id . "' LIMIT 1"); 
	}
}

function add_file_relation($file_id, $term_id, $user_id) 
{
	$relation = db::fetch("SELECT * FROM files_relation WHERE term_id = '" . $term_id . "' AND user_id = '" . $user_id . "' AND file_id = '" . $file_id . "' LIMIT 1"); 

	if (!$relation) {
		db::insert('files_relation', array(
			'term_id' => $term_id, 
			'file_id' => $file_id, 
			'user_id' => $user_id, 
		)); 

		// Получаем все родительские каталоги файла
		$terms = get_files_terms_parents($term_id); 

		// Все ID дерева каталогов
		$ids = array($term_id); 
		foreach($terms AS $t) {
			$ids[] = $t['term_id'];
		}

		// Обновление счетчиков 
		db::query("UPDATE files_terms SET files = files + '1' WHERE term_id IN(" . implode(',', $ids) . ")");	
	}
}

function get_basename($path) 
{
	$ext = strtolower(substr($path, strrpos($path, '.') + 1)); 
	return basename($path, '.'.$ext);
}

function get_files_accesses() 
{
	return use_filters('ds_files_accesses', array(
		'public' => array(
			'title' => __('Все'), 
			'value' => 'public', 
		), 
		'friends' => array(
			'title' => __('Только друзья'), 
			'value' => 'friends', 
		), 
		'private' => array(
			'title' => __('Только я'), 
			'value' => 'private', 
		), 
	));  
}

function get_files_meta_name($key = '') 
{
	$names = use_filters('ds_files_meta_names', array(
		'title' => __('Название'), 
		'artist' => __('Исполнитель'), 
		'album' => __('Альбом'), 
		'duration' => __('Продолжительность'), 
		'bitrate' => __('Битрейт'), 
	));  

	if (isset($names[$key])) {
		return $names[$key]; 
	}

	return ucwords($key); 
}

function get_file_ext($file = NULL) 
{
	if (is_numeric($file)) {
		$file = get_file($file);
	}

	if (isset($file['name'])) {
		return substr(strrchr($file['name'], '.'), 1);
	}
}

function get_file_prev($args = array()) 
{
	$args = array_merge(array(
		'p_str' => 1, 
		'order' => 'ASC', 
		'where' => array(
			array(
				'operator' => '>', 
				'field' => 'id',  
				'value' => $args['file_id'], 
			), 
		), 
	), $args); 

	$query = new DB_Files($args); 

	if ($query->have_files()) {
		foreach($query->files AS $file) {
			return $file; 
		}
	}
}

function get_file_next($args = array()) 
{
	$args = array_merge(array(
		'p_str' => 1, 
		'order' => 'DESC', 
		'where' => array(
			array(
				'operator' => '<', 
				'field' => 'id',  
				'value' => $args['file_id'], 
			), 
		), 
	), $args); 

	$query = new DB_Files($args); 

	if ($query->have_files()) {
		foreach($query->files AS $file) {
			return $file; 
		}
	}
}

function get_uniquie_token($uid = 0) {
	$token = substr(md5($uid . ':' . SALT_FORMS_FIELDS), 0, 6); 

	return $token; 
}

/**
* Получает URL адрес страницы файла 
* @return string
*/ 
function get_file_link($file = NULL) 
{
	if (is_numeric($file)) {
		$file = get_file($file);
	}

	$author = get_user($file['user_id']); 
	$types = get_media_type($file['file_type']); 
	$terms = get_file_terms($file); 

	if (is_array($terms)) {
		foreach($terms AS $term) {
			$term_id = $term['term_id']; 
			break; 
		}		
	}

	$mask = use_filters('ds_get_file_link_mask', array(
	    '%token%' => get_uniquie_token($file['id']), 
	    '%user_nick%' => $author['nick'], 
	    '%user_id%' => $author['id'], 
	    '%term_id%' => isset($term['term_id']) ? $term['term_id'] : 0, 
	    '%file_id%' => $file['id'], 
	    '%files_type%' => $file['file_type'], 
	), $file, $terms, $author, $types);

	return str_replace(array_keys($mask), array_values($mask), $types['permalinks']['file_view']); 
}

/**
* Получает URL адрес для скачивания файла
* @return string
*/ 
function get_file_download_url($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$url = use_filters('ds_file_download_url', get_site_url($file['path'] . $file['name']), $file); 

	return $url; 
}

/**
* Получает URL адрес к каталогу по ID
* @return string
*/ 
function get_files_term_link($term = NULL, $action = NULL) 
{
	if (is_numeric($term)) {
		$term = get_files_term($term);
	}
	
	if (!$term) return ''; 

	$types = get_media_type($term['term_type']); 
	$author = get_user($term['user_id']); 

	$mask = use_filters('ds_get_file_term_link_mask', array(
	    '%token%' => get_uniquie_token($term['term_id']), 
	    '%files_type%' => $term['term_type'], 
	    '%user_nick%' => $author['nick'], 
	    '%user_id%' => $author['id'], 
	    '%term_id%' => isset($term['term_id']) ? $term['term_id'] : 0, 
	), $term, $author, $types);

	if ($action == 'delete') {
		$url = str_replace(array_keys($mask), array_values($mask), $types['permalinks']['delete_term']); 
	} else {
		$url = str_replace(array_keys($mask), array_values($mask), $types['permalinks']['term']); 
	}

	return $url; 
}

/**
* Получает ссылку на удаление файла
* @return string
*/ 
function get_file_link_delete($file = NULL) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$types = get_media_type($file['file_type']); 
	$author = get_user($file['user_id']); 
	$terms = get_file_terms($file); 

	$term_id = 0; 
	if (is_array($terms)) {
		foreach($terms AS $term) {
			$term_id = $term['term_id']; 
			break; 
		}		
	}

	$mask = use_filters('ds_get_file_link_delete_mask', array(
	    '%token%' => get_uniquie_token($file['id']), 
	    '%files_type%' => $file['file_type'], 
	    '%user_nick%' => $author['nick'], 
	    '%file_id%' => isset($file['id']) ? $file['id'] : 0, 
	    '%term_id%' => $term_id, 
	), $file, $author, $types);

	$link = str_replace(array_keys($mask), array_values($mask), $types['permalinks']['delete_file']); 

	return use_filters('ds_get_file_link_delete', $link); 
}

/**
* Получает список каталогов в которых закреплен файл
* @return array 
*/ 
function get_file_terms($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	if (!isset($file['id'])) {
		return false; 
	}

	$file_id = $file['id']; 
	$file_terms = ds_get('ds_file_terms', array()); 

	if (isset($file_terms[$file_id])) {
		return $file_terms[$file_id]; 
	}

	$sql = "SELECT files_terms.* FROM files_relation 
			LEFT JOIN files_terms ON files_relation.term_id = files_terms.term_id
			WHERE files_relation.file_id = '" . $file['id'] . "' AND files_terms.term_type = '" . $file['file_type'] . "'";

	$file_terms[$file_id] = db::select($sql); 
	ds_set('ds_file_terms', $file_terms);

	return use_filters('ds_get_file_terms', $file_terms[$file_id], $file_id); 
}

function get_file_icon($file, $class = false) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$icon = 'fa-file-o'; 
	if (!empty($file)) {
		if (strpos($file['mimetype'], 'video') !== false) {
			$icon = 'fa-file-video-o'; 
		} elseif (strpos($file['mimetype'], 'audio') !== false) {
			$icon = 'fa-music'; 
		} elseif (strpos($file['mimetype'], 'image') !== false) {
			$icon = 'fa-file-image-o'; 
		} elseif (strpos($file['mimetype'], 'zip') !== false) {
			$icon = 'fa-file-archive-o'; 
		} elseif (strpos($file['mimetype'], 'text') !== false) {
			$icon = 'fa-file-text-o'; 
		}
	}

	$classIcon = use_filters('ds_get_file_icon', 'fa ' . $icon, $file['mimetype'], $file); 

	if ($class) {
		return $classIcon; 
	}

	return '<i class="' . $classIcon . '"></i>';
}

function ds_files_breadcrumb($term_id, $last = false, $mask = array(), $delim = ' / ') 
{
	$term = get_files_term($term_id); 

	if (!$term) return ''; 

	$bread = array(); 
	$types = get_media_type($term['term_type']); 

	$public = true; 
	if ($types['public'] === false) {
		if ($term['user_id'] != get_user_id()) {
			$public = false; 
		}
	}

	if ($public == true) {
		$parents = array_reverse(get_files_terms_parents($term_id)); 

		if (count($parents) > 0) {
			foreach($parents AS $k => $t) 
			{
				$mask_term = array_merge($mask, array(
		    		'%token%' => get_uniquie_token($t['term_id']), 
				    '%term_id%' => $t['term_id'], 
				)); 
				
				$bread[] = '<a href="' . str_replace(array_keys($mask_term), array_values($mask_term), $types['permalinks']['term']) . '">' . text($t['title']) . '</a>';
			}
		}		
	} else {
		$bread[] = text($types['labels']['title']);
	}

	if ($bread && $last === false) {
		$end = array_pop($bread); 
		$bread[] = strip_tags($end); 
	}

	if (!empty($bread)) {
		echo '<div class="breadcrumb"><i class="fa fa-folder-open"></i> ' . join($delim, $bread) . '</div>';
	}
}

function get_audio_player($file) 
{
	if (is_numeric($file)) {
		$file = get_file($file); 
	}

	$src = get_file_download_url($file); 
	$meta = get_files_meta($file['id']); 

	$volume = 100; 
	if (isset($_COOKIE['playerData'])) {
		$data = json_decode($_COOKIE['playerData'], true); 

		if ($data['volume'] >= 0 && $data['volume'] <= 1) {
			$volume = ($data['volume'] * 100); 
		}
	}

	$player = '<div class="dpl" data-title="' . text($file['title']) . '" data-id="' . $file['id'] . '" data-src="' . $src . '" data-hash="' . md5($src) . '" data-uniquie="' . md5($src . mt_rand(1, 9999999)) . '">'; 
	$player .= '<div class="dpl-toggle"></div>';
	$player .= '<div class="dpl-title">' . text($file['title']) . '</div>';
	$player .= '<div class="dpl-progress" onclick="setPlayerSeeked(this);"><div class="dpl-progress-loaded"></div><div class="dpl-progress-bar"></div></div>';
	$player .= '<div class="dpl-time">0:00</div>';
	$player .= '<div class="dpl-volume"><div class="dpl-volume-bar" style="width: ' . $volume . '%;"></div></div>';
	$player .= '</div>';

	return use_filters('get_audio_player', $player, $file); 
}