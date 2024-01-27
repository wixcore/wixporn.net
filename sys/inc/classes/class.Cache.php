<?php 

class Cache
{
    private $cache_time; 
    
    public function __construct($time = 30) {
        $this->cache_time = $time;
    }
    
    public function read($cacheId, $json = false, $cache_pach = false) 
    {
        $cache = $this->_cache($cacheId, $json, $cache_pach);
        
        if ($cache) {
            return $cache; 
        } 
        
        return false;
    }
    
    public function write($cacheId, $data, $json = false) 
    {
        $cache = $this->_cache_create($cacheId, $data, $json); 
        
        if ($cache) {
            return $cache; 
        } 
        
        return false;
    }
    
    public function delete($cacheId) 
    {
        $cache = $this->_cache_delete($cacheId); 
    }
    
    private function _cache($cacheId, $json = false, $cache_pach = false) 
    {
        $url = H.'sys/tmp/cache.' . $cacheId . '.ser'; 
        
        if (!$cacheId) {
            return false;
        }
        
        if ($cache_pach && is_file($url)) {
            return $url; 
        } 
        
        elseif (is_file($url) && (TIME - $this->cache_time) < fileatime($url)) {
            if ($json) {
                return json_decode(file_get_contents($url), 1); 
            } else {
                return file_get_contents($url); 
            }
        } else {
            $this->_cache_delete($cacheId); 
        }
        
        return false;
    }
    
    private function _cache_create($cacheId, $data = false, $json = false) 
    {
        if (!$cacheId) {
            return false;
        }
        
        $this->_cache_delete($cacheId);
        
        if (file_put_contents(H.'sys/tmp/cache.' . $cacheId . '.ser', ($json ? json_encode($data) : $data))) {
            return $this->_cache($cacheId, $json);
        }
        
        return false;
    }
    
    private function _cache_delete($cacheId) 
    {
        if (is_file(H.'sys/tmp/cache.' . $cacheId . '.ser')) {
            unlink(H.'sys/tmp/cache.' . $cacheId . '.ser');
        }
    }
}