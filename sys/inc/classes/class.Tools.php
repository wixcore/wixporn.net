<?php 

class Tools 
{
    public function sclon($number, $titles) {
        $cases = array (2, 0, 1, 1, 1, 2);
        return $number . ' ' . $titles[ ($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ];
    }
    
    public function keyGlobals($key, $type = 0, $null = false) {
        if ($type === 0) {
            if (isset($_SESSION[$key])) {
                return $_SESSION[$key];
            } elseif (isset($_GET[$key])) {
                return $_GET[$key];
            } elseif (isset($_POST[$key])) {
                return $_POST[$key];
            }
        } 
        
        elseif ($type == 'get') {
            if (isset($_GET[$key])) {
                return $_GET[$key];
            }
        }
        
        elseif ($type == 'post') {
            if (isset($_POST[$key])) {
                return $_POST[$key];
            }
        }
        
        elseif ($type == 'sess') {
            if (isset($_SESSION[$key])) {
                return $_SESSION[$key];
            }
        }
        
        return $null;
    }
    
    public function regSession($key) {
        if (is_array($key)) {
            foreach($key AS $key_id => $key_value) {
                if (isset($_POST[$key_value])) {
                    $_SESSION[$key_value] = $_POST[$key_value];
                }
                
                elseif (isset($_GET[$key_value])) {
                    $_SESSION[$key_value] = $_GET[$key_value];
                } 
            }
        }
        
        else {
            if (isset($_POST[$key])) {
                $_SESSION[$key] = $_POST[$key];
            }
            
            elseif (isset($_GET[$key])) {
                $_SESSION[$key] = $_GET[$key];
            }             
        }
    }
    
    public function get_amp_request($url = '/', $get = '?') {
        if (preg_match('/\?/', $url)) {
            $amp = '&';
        } else {
            $amp = '?';
        }
        
        return $url.$amp.$get;
    }
}