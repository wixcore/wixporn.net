<?php 

class FTP_Upload
{
	private $connect = NULL; 
	private $rootdir = '/';
	private $settings; 
	private $maxsize; 
	private $cdn; 
	public $async = false; 

	public function __construct($cdn) 
	{
		$this->cdn = $cdn; 
		$this->settings = unserialize($cdn['description']); 
		$this->maxsize = (intval($this->settings['size']) * 1024 * 1024); 

		if ($this->settings['ftp_server']) {
			$connect = ftp_connect($this->settings['ftp_server']); 
		}
		
		if ($connect) {
			$result = ftp_login($connect, $this->settings['ftp_login'], $this->settings['ftp_password']);
			if ($result) {
				$this->connect = $connect; 
				$this->chdir($this->settings['ftp_path'], false);
			}
		}
	}

	public function chdir($path_ftp = '/', $root = true) 
	{
		if ($root) {
			@ftp_chdir($this->connect, $this->settings['ftp_path']);
		}

		if (strpos($path_ftp, '/') !== false && $path_ftp != '/') {
			foreach(explode('/', $path_ftp) AS $dir) {
				if (!$dir) continue; 

				@ftp_mkdir($this->connect, $dir);
				@ftp_chdir($this->connect, $dir);
			}
		}
	}

	public function upload($file_local, $file_ftp, $path_ftp = '/') 
	{
		if ($this->async == true) {
			$ftp_size = ftp_size($this->connect, $file_ftp); 
			$ftp_put_async = @ftp_nb_put($this->connect, $file_ftp, $file_local, FTP_BINARY, $ftp_size);

		    $timeout = time(); 
		    while ($ftp_put_async == FTP_MOREDATA) {
		        if (($timeout + 20) < time()) {
		            break; 
		        } else { 
		            $ftp_put_async = ftp_nb_continue($this->connect);
		        } 
		    }

			$result = false; 

			if ($ftp_put_async == FTP_FAILED) {
				$result = -2; 
			}

			if ($ftp_put_async == FTP_MOREDATA) {
				$result = -1; 
			}

			if ($ftp_put_async == FTP_FINISHED) {
				$result = true; 
			}
		} else {
			$result = ftp_put($this->connect, $file_ftp, $file_local, FTP_BINARY); 
		}
		
		if ($result) {
			return true; 
		} else {
			return false; 
		}
	}

	public function move_to_local($filename, $from_path, $to_path) 
	{
		// Переходим в каталог файла на сервере
		$this->chdir($from_path); 

		if (@ftp_get($this->connect, ROOTPATH . $to_path . $filename, $filename, FTP_BINARY)) {

			// Удаляем файл в хранилище
			ftp_delete($this->connect, $filename); 

			// Чистка пустых папок
			$this->clean_directory($from_path); 

			return true; 
		}
		return false; 
	}

	public function move_to_storage($file_path, $dirpath) 
	{
		// Переходим в каталог файла на сервере
		$this->chdir($dirpath); 

		// Отправляем файл на FTP сервер
		if ($this->upload($file_path, basename($file_path))) {
			return true; 
		}

		return false; 
	}

	public function file_delete($filename, $dirpath) 
	{
		// Переходим в каталог файла на сервере
		$this->chdir($dirpath); 

		// Удаляет файл на FTP сервере
		if (@ftp_delete($this->connect, $filename)) {

			// Чистка пустых папок
			$this->clean_directory($dirpath); 

			return true; 
		}

		return false; 
	}

	public function clean_directory($dirpath) 
	{
		$path = array(); 
		$path[] = $this->settings['ftp_path'] . '/' . $dirpath;
		$path[] = dirname($this->settings['ftp_path'] . '/' . $dirpath);

		foreach($path AS $ftpdir) {
			if (!@ftp_rmdir($this->connect, $ftpdir)) {
				break; 
			}
		}
	}
}