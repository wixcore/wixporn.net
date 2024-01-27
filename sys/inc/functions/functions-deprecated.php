<?php 

function online($user = NULL) {}
function title() { }
function img_copyright( $img ) { }
function group($user = NULL) { }
function antimat( $str ) { }
function medal($user = 0) { } 
function otkuda($ref) { }
function ras_to_mime($ras = null) { } 
function rekl($sel) { }
function resize($file_input, $file_output, $w_o, $h_o, $percent = false) { }
function crop($file_input, $file_output, $crop = 'square',$percent = false) { }
function aut() { }
function status($ID) { }
function url( $url ) { }
function url2( $url ) { }
function err( ) { }
function msg( $msg ) { }
function save_settings( $set, $type = '' ) { }


/**
* Устаревшая функция аватара, с использованием старых параметров
* возвращает HTML код изображения, используйте get_avatar()
* @return string
*/
function avatar($user_id, $link = false, $dir = '50', $w = '50') 
{
    $sizes = array(
        50 => 'thumbnail', 
        128 => 'medium', 
        640 => 'large', 
    ); 

    $size = $sizes[$dir]; 
    $avatar = get_avatar($user_id, $size, $link); 
    return $avatar; 
}


/**
* PHP < 7.3.0
* Получает первый ключ массива
*/ 

if (!function_exists('array_key_first')) {
    function array_key_first(array $array)
    {
        if (count($array)) {
            reset($array);
            return key($array);
        }
        return null;
    }
}

/**
* PHP < 7.3.0
* Получает ключ последнего элемента массива
*/ 

if (!function_exists('array_key_last') ) {
    function array_key_last(array $array) 
    {
        if (!empty($array)) {
            return key(array_slice($array, -1, 1, true));
        }
        return null; 
    }
}