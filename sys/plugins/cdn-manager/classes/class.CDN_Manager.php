<?php 

class CDN_Manager
{
	private $cdn; 
	private $settings; 
	private $maxsize; 
	public $storage; 
	public $async = false; 

	public function __construct($cdn) 
	{
		$this->cdn = $cdn; 
		$this->settings = unserialize($cdn['description']); 
		$this->maxsize = (intval($this->settings['size']) * 1024 * 1024); 

		// Инициализируем FTP хранилище
		if ($this->settings['type'] == 'ftp') {
			if (!class_exists('FTP_Upload')) {
				require dirname(__FILE__) . '/class.FTP_Upload.php'; 
			}
			$this->storage = new FTP_Upload($cdn); 
		}
	}

	/**
	* Обновляет размер каталога 
	*/ 
	public function update_term_size($term_id, $size) 
	{
		db::query("UPDATE files_terms SET size = size + '" . intval($size) . "' WHERE term_id = " . $term_id); 
	}

	/**
	* Загрузка локального файла на сервер хранилища
	* @return bolean
	*/ 
	public function move_to_storage($file) 
	{
		if (!is_object($this->storage)) {
			return false; 
		}

		if (is_numeric($file)) {
			$file = get_file($file); 
		}

		// Асинхронный режим передачи файла
		$this->storage->async = $this->async; 

		// Создаем путь на основе хеша файла
		$storage_path = cdn_files_dir_hash($file);  

		// Локальный файл 
		$file_path = ROOTPATH . $file['path'] . $file['name']; 

		// Отправляем файл на сервер хранилища
		if (is_file($file_path) && $result = $this->storage->move_to_storage($file_path, $storage_path)) { 

			// Обновляем размер хранилища
			$this->update_term_size($this->cdn['term_id'], $file['size']); 

			// Мета поле с ID хранилища
			update_files_meta($file['id'], 'cdn_id', $this->cdn['term_id']); 
			add_file_relation($file['id'], $this->cdn['term_id'], '-1'); 	
			unlink($file_path); 
		}

		return false; 
	}

	/**
	* Загрузка удаленного файла на локальный сервер
	* @return bolean
	*/ 
	public function move_to_local($file) 
	{
		if (!is_object($this->storage)) {
			return false; 
		}

		if (is_numeric($file)) {
			$file = get_file($file); 
		}
		
		// Получаем путь на основе хеша файла
		$storage_path = cdn_files_dir_hash($file); 

		// Локальный файл 
		$file_path = ROOTPATH . $file['path'] . $file['name']; 

		if ($result = $this->storage->move_to_local($file['name'], $storage_path, $file['path'])) {

			// Удаляем метку хранилища
			delete_files_meta($file['id'], 'cdn_id'); 

			// Обновляем размер хранилища
			$this->update_term_size($this->cdn['term_id'], -$file['size']); 

			// Удаляем связь с хранилищем
			delete_file_relation($file['id'], $this->cdn['term_id'], '-1'); 

			return true; 
		}

		return false; 
	}

	/** 
	* Удаление файла из хранилища
	* @return bolen 
	*/ 
	public function file_delete($file) 
	{
		if (!is_object($this->storage)) {
			return false; 
		}

		if (is_numeric($file)) {
			$file = get_file($file); 
		}

		// Получаем путь на основе хеша файла
		$storage_path = cdn_files_dir_hash($file); 

		// Удаляем файл физически в хранилище
		if ($this->storage->file_delete($file['name'], $storage_path)) {
			
			// Обновляем размер хранилища
			files_term_update($this->cdn, array(
				'size' => ($this->cdn['size'] - $file['size']), 
			)); 

			return true; 
		}

		return false; 
	}
}