<?php 

/**
* Класс для работы с обновлением системы CMS Social
* @since 3.0.0
*/

class Update 
{
    private $config;
    private $current = array();
    private $url_api = 'https://cms-social.ru/api/v1'; 
    
    /**
    * Получение информации о последней версии движка
    * @return array
    */
    public function get_latest()  
    { 
        $array = $this->http_get(get_query_url(array(
            'version' => get_version(), 
        ), '/core/update')); 
        
        return $array; 
    }

    public function get_plugins($list) 
    {
        $array = $this->http_get(get_query_url(array(
            'list' => $list, 
        ), '/plugins/update')); 

        return $array; 
    }
    
    private function http_get($url) 
    {
        $release = ( defined('CORE_DEV_RELEASE') && CORE_DEV_RELEASE ? 'development' : 'release' ); 

        $build = array(
            'HOST' => $_SERVER['HTTP_HOST'],
            'PROTOCOL' => $_SERVER['SERVER_PROTOCOL'],
            'PORT' => $_SERVER['REMOTE_PORT'],
            'VERSION' => get_version(),
            'RELEASE' => $release,
        ); 

        $data = false;

        if (function_exists('curl_init')) {
            $ch = curl_init(); 

            curl_setopt($ch, CURLOPT_URL, $this->url_api . $url); 
            curl_setopt($ch, CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); 
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_HOST']); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($build, false, '&'));
            $data = curl_exec($ch); 

            curl_close($ch);
        }

        if ($data) {
            return json_decode($data, 1); 
        }
        
        return false;
    }
}