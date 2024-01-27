<?php 

class YandexDisk
{
	private $cdn; 
	private $url = 'https://cloud-api.yandex.net:443/v1'; 
	public $request; 
	public $info; 

	public function __construct($cdn) 
	{
		$this->cdn = $cdn; 
		$this->settings = unserialize($cdn['description']); 
	}

	/**
	* Выполняем cURL запрос к API яндекс диска
	* @return array 
	*/ 
	public function curl($url = '/disk/', $params = array(), $method = 'get') 
	{
		if (isset($params['curl_url'])) {
			$curl_url = $params['curl_url']; 
		} else {
			$curl_url = $this->url . $url . '?' . http_build_query($params, '', '&'); 
		}

		//echo $curl_url . '<br>'; 

		$ch = curl_init($curl_url);

		if ($method == 'post') {
			curl_setopt($ch, CURLOPT_POST, true);
			//curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		}
		if ($method == 'put') curl_setopt($ch, CURLOPT_PUT, true);
		if ($method == 'delete') curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		/**
		* Отправка файла на яндекс диск
		*/ 
		if ($method == 'put' && isset($params['file'])) {
			if (!is_file($params['file'])) {
				return array('error' => 'File not found'); 
			}

			$fp = fopen($params['file'], 'r');
			curl_setopt($ch, CURLOPT_UPLOAD, true);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($params['file']));
			curl_setopt($ch, CURLOPT_INFILE, $fp);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
		   'Content-Type: application/json',
		   'Authorization: OAuth ' . $this->settings['ya_token'], 
		));

		$data = curl_exec($ch);
		$this->request = curl_getinfo($ch);
		curl_close($ch);

		print_r($this->request);
		//print_r($data);

		if (isset($fp)) fclose($fp);

		if ($data) {
			return json_decode($data, 1); 
		}
	}

	public function getDisk() 
	{
		return $this->curl('/disk/', array()); 
	}

	public function createDir($dirpath) 
	{
		$directory = $this->curl('/disk/resources/', array(
			'path' => $dirpath, 
		), 'get'); 

		if (isset($directory['error']) && $directory['error'] == 'DiskNotFoundError') {
			$pathExplode = explode('/', $dirpath); 

			$dirs = array(); 
			foreach($pathExplode AS $key => $path) {
				if (isset($dirs[$key-1])) {
					$dirs[] = $dirs[$key-1] . '/' . $path;
				} else {
					$dirs[] = '/' . $path; 
				}
			}

			foreach($dirs AS $dir) {
				$res = $this->curl('/disk/resources/', array(
					'path' => $dir, 
				), 'put'); 
			}

			$directory = $this->curl('/disk/resources/', array(
				'path' => $dirpath, 
			), 'get'); 
		}

		if (!isset($directory['error'])) {
			return true; 
		}

		return false; 
	}

	public function get_download_link($name, $path) {
		$replace = array(
			'/([\/]{2,})/' => '/', 
			'/^\/(.*)$/' => '$1', 
			'/^(.*)\/$/' => '$1', 
		); 

		$dir_upload = preg_replace(array_keys($replace), array_values($replace), $this->settings['ya_dir_upload']); 

		return $this->curl('/disk/resources/download', array(
			'path' => '/' . $dir_upload . '/' . $path . '/' . $name, 
		), 'get'); 
	}

	public function move_to_storage($filepath, $dirpath) 
	{
		$replace = array(
			'/([\/]{2,})/' => '/', 
			'/^\/(.*)$/' => '$1', 
			'/^(.*)\/$/' => '$1', 
		); 

		$dir_upload = preg_replace(array_keys($replace), array_values($replace), $this->settings['ya_dir_upload']); 

		if (!$this->createDir($dir_upload . '/' . $dirpath)) {
			return false;  
		}

		$disk_upload = $this->curl('/disk/resources/upload', array(
			'path' => '/' . $dir_upload . '/' . $dirpath . '/' . basename($filepath), 
			'url' => get_site_url($filepath), 
		), 'post'); 

		print_r($disk_upload);

		if ($this->request['http_code'] == 202) {
			return true; 
		}

		return false; 
	}

	public function move_to_local($filename, $from_path, $to_path) 
	{
		$replace = array(
			'/([\/]{2,})/' => '/', 
			'/^\/(.*)$/' => '$1', 
			'/^(.*)\/$/' => '$1', 
		); 

		$dir_upload = preg_replace(array_keys($replace), array_values($replace), $this->settings['ya_dir_upload']); 

		$file_info = $this->curl('/disk/resources/download', array(
			'path' => '/' . $dir_upload . '/' . $from_path . '/' . $filename, 
		), 'get'); 

		if (isset($file_info['href'])) {
			$file = file_get_contents($file_info['href']); 

			if ($file) {
				$download = file_put_contents(ROOTPATH . $to_path . $filename, $file); 

				if ($download) {

					// Удаляем файл с диска 
					$this->file_delete($filename, $from_path); 
					
					return true; 
				}
			}
		}

		return false; 
	}

	public function file_delete($filename, $dirpath) 
	{
		$replace = array(
			'/([\/]{2,})/' => '/', 
			'/^\/(.*)$/' => '$1', 
			'/^(.*)\/$/' => '$1', 
		); 

		$dir_upload = preg_replace(array_keys($replace), array_values($replace), $this->settings['ya_dir_upload']); 

		$this->curl('/disk/resources', array(
			'path' => '/' . $dir_upload . '/' . $dirpath . '/' . $filename, 
			'force_async' => 'true', 
			'permanently' => 'true', 
		), 'delete'); 

		if (in_array($this->request['http_code'], array(202, 204))) {
			return true; 
		} 

		return false; 
	}
}