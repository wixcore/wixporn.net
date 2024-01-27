<?php

// Ретранслит русских букв на латинницу
function retranslit($string)
{
    $from = array("Ё","Ж","Ч","Ш","Щ","Э","Ю","Я","ё","ж","ч","ш","щ","э","ю","я","А","Б","В","Г","Д","Е","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ь","Ы","а","б","в","г","д","е","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ь","ы");
    
    $to = array("JO","ZH","CH","SH","SCH","Je","Jy","Ja","jo","zh","ch","sh","sch","je","jy","ja","A","B","V","G","D","E","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","H","C","","Y","a","b","v","g","d","e","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","c","","y");
    
    $string = preg_replace('/[^\-\_0-9A-z]+/', '-', strtolower(str_replace($from, $to, $string)));
    $string = preg_replace('/[\-]+/', '-', $string);
    $string = preg_replace('/^([\-]{1})|([\-]{1})$/', '', $string);

    return $string;
}

// Ретранслит латинских букв на русские
function translit($in)
{
	$trans1= array("JO","ZH","CH","SH","SCH","JE","JY","JA","jo","zh","ch","sh","sch","je","jy","ja","A","B","V","G","D","E","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","H","C","'","Y","a","b","v","g","d","e","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","c","'","y");
	$trans2= array("Ё","Ж","Ч","Ш","Щ","Э","Ю","Я","ё","ж","ч","ш","щ","э","ю","я","А","Б","В","Г","Д","Е","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ь","Ы","а","б","в","г","д","е","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ь","ы");
	return str_replace($trans1,$trans2,$in);
}

//Фильтрация символов
function text($str)
{
	return stripcslashes(htmlspecialchars($str));
}

// Строка в hex 
function str_to_hex($string) { 
	$hex = ''; 
	for ($i = 0; $i < strlen($string); $i++) { 
		$hex .= dechex(ord($string[$i])); 
	} 
	return $hex; 
} 

// Hex в строку
function hex_to_str($hex) { 
	$string = ''; 
	for ($i = 0; $i < strlen($hex) - 1; $i += 2) { 
		$string .= chr(hexdec($hex[$i].$hex[$i+1])); 
	} 
	return $string; 
}

function des2num($int, $array, $lang = true) 
{
    $cases = array (2, 0, 1, 1, 1, 2);
    $text = $array[($int % 100 > 4 && $int % 100 < 20) ? 2 : $cases[min($int % 10, 5)]];

    if ($lang === true) {
    	$text = __($text);
    }

    $result = use_filters('__des2num', $int . ' ' . $text); 
    return $result;
}

/**
 * Резервная функция мультиязычности
 * @return string
 * [$var, $var, ..]
 */

if (!function_exists('__')) {
    function __($string) 
    { 
        $args = func_get_args();
        $args4eval = array();
        for ($i = 1; $i < count($args); $i++) {
            $args4eval[] = '$args[' . $i . ']';
        }
        
        if ($args4eval) {
            eval('$string = sprintf($string, ' . implode(', ', $args4eval) . ');');
        }
        
        return $string; 
    }    
}


/**
* Функция для вывода перевода текста плагина
* $string - Текст (обязательно)
* $plugin - Название папки плагина (обязательно)
* [$var, $var, ..]
*/ 
if (!function_exists('__p')) { 
    function __p($string, $plugin) 
    {
        $args = func_get_args();
        $args4eval = array();
        for ($i = 2; $i < count($args); $i++) {
            $args4eval[] = '$args[' . $i . ']';
        }
        
        if ($args4eval) {
            eval('$string = sprintf($string, ' . implode(', ', $args4eval) . ');');
        }
        
        return $string; 
    }
}

/**
* Функция для вывода перевода текста плагина
* $string - Текст (обязательно)
* $plugin - Название папки шаблона (обязательно)
* [$var, $var, ..]
*/ 
if (!function_exists('__t')) {
    function __t($string, $theme) 
    {
        $args = func_get_args();
        $args4eval = array();
        for ($i = 2; $i < count($args); $i++) {
            $args4eval[] = '$args[' . $i . ']';
        }
        
        if ($args4eval) {
            eval('$string = sprintf($string, ' . implode(', ', $args4eval) . ');');
        }
        
        return $string; 
    }
}


function br( $msg, $br = '<br />' )
{
    $msg = str_replace("\t", "    ", $msg); 
    //$msg = str_replace(array("\r", ' '), "&nbsp;", $msg); 
    $msg = preg_replace("#((<br( ?/?)>)|\n|\r)+#i", $br, $msg);

    return $msg; 
}

function esc( $text, $br = NULL )
{
    if ( $br != NULL )
        for ( $i = 0; $i <= 31; $i++ )
            $text = str_replace( chr( $i ), NULL, $text );
    else {
        for ( $i = 0; $i < 10; $i++ )
            $text = str_replace( chr( $i ), NULL, $text );
        for ( $i = 11; $i < 20; $i++ )
            $text = str_replace( chr( $i ), NULL, $text );
        for ( $i = 21; $i <= 31; $i++ )
            $text = str_replace( chr( $i ), NULL, $text );
    }
    return $text;
}


// вывод времени
function vremja( $time = NULL )
{
    global $user;
    if ( $time == NULL )
        $time = time();
    if ( isset( $user ) )
        $time = $time + $user['set_timesdvig'] * 60 * 60;
    $timep     = date( "j M Y в H:i", $time );
    $time_p[0] = date( "j n Y", $time );
    $time_p[1] = date( "H:i", $time );
    if ( $time_p[0] == date( "j n Y" ) )
        $timep = date( "H:i:s", $time );
    if ( isset( $user ) ) {
        if ( $time_p[0] == date( "j n Y", time() + $user['set_timesdvig'] * 60 * 60 ) )
            $timep = date( "H:i:s", $time );
        if ( $time_p[0] == date( "j n Y", time() - 60 * 60 * ( 24 - $user['set_timesdvig'] ) ) )
            $timep = "Вчера в $time_p[1]";
    } else {
        if ( $time_p[0] == date( "j n Y" ) )
            $timep = date( "H:i:s", $time );
        if ( $time_p[0] == date( "j n Y", time() - 60 * 60 * 24 ) )
            $timep = "Вчера в $time_p[1]";
    }
    $timep = str_replace( "Jan", "Янв", $timep );
    $timep = str_replace( "Feb", "Фев", $timep );
    $timep = str_replace( "Mar", "Марта", $timep );
    $timep = str_replace( "May", "Мая", $timep );
    $timep = str_replace( "Apr", "Апр", $timep );
    $timep = str_replace( "Jun", "Июня", $timep );
    $timep = str_replace( "Jul", "Июля", $timep );
    $timep = str_replace( "Aug", "Авг", $timep );
    $timep = str_replace( "Sep", "Сент", $timep );
    $timep = str_replace( "Oct", "Окт", $timep );
    $timep = str_replace( "Nov", "Ноября", $timep );
    $timep = str_replace( "Dec", "Дек", $timep );
    return $timep;
}

function ds_date($format = '', $timestump = null) 
{
    if ($timestump !== null) {
        $date = date($format, $timestump); 
    } else {
        $date = date($format); 
    }

    $strings = array(
        'January'   => __( 'Января'),
        'February'  => __( 'Февраля' ),
        'March'     => __( 'Марта' ),
        'April'     => __( 'Апреля' ),
        'May'       => __( 'Мая' ),
        'June'      => __( 'Июня' ),
        'July'      => __( 'Июля' ),
        'August'    => __( 'Августа' ),
        'September' => __( 'Сентября' ),
        'October'   => __( 'Октября' ),
        'November'  => __( 'Ноября' ),
        'December'  => __( 'Декабря' ),

        'Jan' => __('Янв'), 
        'Feb' => __('Фев'), 
        'Mar' => __('Марта'), 
        'May' => __('Мая'), 
        'Apr' => __('Апр'), 
        'Jun' => __('Июня'), 
        'Aug' => __('Авг'), 
        'Sep' => __('Сент'), 
        'Oct' => __('Окт'), 
        'Nov' => __('Ноября'), 
        'Dec' => __('Дек'), 
    );

    $date = str_replace(array_keys($strings), array_values($strings), $date);  
    
    return $date; 
}

function get_time_ago($time = 0, $join = ' ', $slice = false) 
{
    $output = array(); 

    if ($time > 0) {
        $years = floor($time / 31536000); 
        if ($years) {
            $time -= ($years * 31536000); 
            $output[] = des2num($years, array(__('год'), __('года'), __('лет')), false); 
        }

        $months = floor($time / 2592000); 
        if ($months) {
            $time -= ($months * 2592000); 
            $output[] = des2num($months, array(__('месяц'), __('месяца'), __('месяцев')), false); 
        }

        $weeks = floor($time / 604800); 
        if ($weeks) {
            $time -= ($weeks * 604800); 
            $output[] = des2num($weeks, array(__('неделя'), __('недели'), __('недель')), false); 
        }

        $days = floor($time / 86400); 
        if ($days) {
            $time -= ($days * 86400); 
            $output[] = des2num($days, array(__('день'), __('дня'), __('дней')), false); 
        }

        $hours = floor($time / 3600); 
        if ($hours) {
            $time -= ($hours * 3600); 
            $output[] = des2num($hours, array(__('час'), __('часа'), __('часов')), false); 
        }

        $min = floor($time / 60); 
        if ($min) {
            $time -= ($min * 60); 
            $output[] = des2num($min, array(__('минута'), __('минуты'), __('минут')), false); 
        }

        if ($time) {
            $output[] = des2num($time, array(__('секунда'), __('секунды'), __('секунд')), false); 
        }
    }

    if ($slice) {
        $output = array_slice($output, 0, $slice);
    }

    return join($join, $output); 
}

function cmp2($a, $b) 
{
    if ($a['2'] == $b['2']) return 0;
    return ($a['2'] > $b['2']) ? -1 : 1;
}

function bbcodehightlight($arr)
{
	$arr[0] = html_entity_decode($arr[0], ENT_QUOTES, 'UTF-8');
	return '<div class="cit" style="overflow:scroll;clip:auto;max-width:480px;">'.preg_replace('#<code>(.*?)</code>#si', '\\1', highlight_string($arr[0], 1)).'</div>'."\n";
}

function BBcode($msg)
{
	global $set;

	$bbcode = array();
	$bbcode['/\[br\]/isU'] = '<br />';

	if ($set['bb_i'])$bbcode['/\[i\](.+)\[\/i\]/isU'] = '<em>$1</em>';
	if ($set['bb_b'])$bbcode['/\[b\](.+)\[\/b\]/isU'] = '<strong>$1</strong>';
	if ($set['bb_u'])$bbcode['/\[u\](.+)\[\/u\]/isU'] = '<span style="text-decoration:underline;">$1</span>';
	if ($set['bb_big'])$bbcode['/\[big\](.+)\[\/big\]/isU'] = '<span style="font-size:large;">$1</span>';
	if ($set['bb_small'])$bbcode['/\[small\](.+)\[\/small\]/isU'] = '<span style="font-size:small;">$1</span>';
	if ($set['bb_red'])$bbcode['/\[red\](.+)\[\/red\]/isU'] = '<span style="color:#ff0000;">$1</span>';
	if ($set['bb_yellow'])$bbcode['/\[yellow\](.+)\[\/yellow\]/isU'] = '<span style="color:#ffff22;">$1</span>';
	if ($set['bb_green'])$bbcode['/\[green\](.+)\[\/green\]/isU'] = '<span style="color:#00bb00;">$1</span>';
	if ($set['bb_blue'])$bbcode['/\[blue\](.+)\[\/blue\]/isU'] = '<span style="color:#0000bb;">$1</span>';
	if ($set['bb_white'])$bbcode['/\[white\](.+)\[\/white\]/isU'] = '<span style="color:#ffffff;">$1</span>';
	if ($set['bb_size'])$bbcode['/\[size=([0-9]{1,2})\](.+)\[\/size\]/isU'] = '<span style="font-size:$1px;">$2</span>';
	
	$bbcode['/\[color=\#([0-9A-z]{3,6})\]/isU'] = '<span style="color: #$1;">';
	$bbcode['/\[\/color\]/isU'] = '</span>';
	
	$bbcode['/\[fon=\#([0-9A-z]{3,6})\]/isU'] = '<span style="background-color: #$1;">';
	$bbcode['/\[\/fon\]/isU'] = '</span>';
	
	if (count($bbcode))$msg = preg_replace(array_keys($bbcode), array_values($bbcode), $msg);

	return $msg;
}


function strlen2($str)
{
	$rus = array('й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю','Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','Я','Ч','С','М','И','Т','Ь','Б','Ю');
	return strlen(str_replace($rus, '0', $str));
}

function smiles($msg) {
	return $msg; 
}

function my_esc( $str )
{
    return db::esc( $str);
}

function img_preg($arr)
{
  	if (preg_match('/\.(?:jp(?:e?g|e|2)|gif|png|tiff?|bmp|ico)$/i', $arr[1])) {
        return '<img class="bb_img" src="' . text($arr[1]) . '" alt="img" />';
    } else {
        return '<img class="bb_img" src="/style/no_image.png" alt="No Image" />';	
    }
}

function links_preg1($arr)
{
  	global $set;

  	if (preg_match('#^http://' . preg_quote($_SERVER['HTTP_HOST']) . '#',$arr[1]) || !preg_match('#://#',$arr[1]))
  	    return '<a href="' . $arr[1] . '">' . $arr[2] . '</a>';
  	else
  	    return '<a target="_blank" rel="nofollow" href="' . $arr[1] . '">' . $arr[2] . '</a>';
}

function links_preg2($arr)
{
  	global $set;

  	if (preg_match('#^http://' . preg_quote($_SERVER['HTTP_HOST']) . '#',$arr[2]))
  	return $arr[1] . '<a href="' . $arr[2] . '">' . $arr[2] . '</a>' . $arr[4];
  	else
  	return $arr[1] . '<a target="_blank" rel="nofollow" href="' . $arr[2] . '">' . $arr[2] . '</a>' . $arr[4];
}

function links($msg)
{
  	global $set;
  	if ($set['bb_img'])$msg = preg_replace_callback('/\[img\]((?!javascript:|data:|document.cookie).+)\[\/img\]/isU', 'img_preg', $msg);
  	if ($set['bb_url'])$msg = preg_replace_callback('/\[url=((?!javascript:|data:|document.cookie).+)\](.+)\[\/url\]/isU', 'links_preg1', $msg); 
  	if ($set['bb_http'])$msg = preg_replace_callback('~(^|\s)([a-z]+://([^ \r\n\t`\'"]+))(\s|$)~iu', 'links_preg2', $msg);
    
  	return $msg;
}

/**
* Разбивает сообщение с серилизоваными данными в массив 
* @return array
*/
function get_text_array($str) 
{
    $data = array(); 

    preg_match('/<!-- CMS-Social Data {{(.*)}} -->/m', $str, $matches); 
    if (!empty($matches[1])) {
        $data = unserialize($matches[1]); 
        $str = trim(preg_replace('/<!-- CMS-Social Data {{(.*)}} -->/m', '', $str)); 
    }
    
    return array(
        'data' => $data, 
        'content' => $str, 
    ); 
}

/**
* Обрабатывает строку вырезая теги и мета информацию
* оставляя только подпись о вложении, смайлы и текст
* @return string
*/ 

function output_short($str) 
{
    $array = get_text_array($str); 
    
    if ($array['content']) {
    	$text = htmlentities($array['content'], ENT_QUOTES, 'UTF-8'); 
    	$text = strip_tags(bbcode($text)); 
    	$text = ds_filter_emoji($text); 
    }

    elseif (!empty($array['data']['attachments'])) {
		$text = __('файл'); 
    }

    $text = use_filters('ds_output_short', $text, $array['content'], $array['data']); 

    return $text; 
}

/**
* Функция обрабатывает текстовые строки перед выводом в браузер
* настоятельно не рекомендуется тут что-либо менять
* @return string
*/

function output_text($str, $br = 1, $html = 1, $smiles = 1, $links = 1, $bbcode = 1)
{
    global $theme_ini;
    
    $array = get_text_array($str); 
    $data = $array['data']; 
    $str = $array['content']; 
    
    if ($br && isset($theme_ini['text_width']))
        $str = wordwrap($str, $theme_ini['text_width'], ' ', 1);
    
    // преобразуем все к нормальному перевариванию браузером
    if ($html) $str = htmlentities($str, ENT_QUOTES, 'UTF-8'); 
    
    $str = use_filters('ds_pre_output_text', $str); 

    preg_match_all('/\[code\](.+)\[\/code\]/isU', $str, $replace_code);
    if (!empty($replace_code[0])) {
        $str = preg_replace('/\[code\](.+)\[\/code\]/isU', '#!%code%;', $str);
    }
    
    // обработка ссылок
    if ($links)
        $str = links($str); 
    
    // вставка смайлов
    if ($smiles)
        $str = smiles($str); 
    
    // обработка bbcode
    if ($bbcode) {
        $str = bbcode($str); 
    }

    // переносы строк
    if ($br) {
        $str = br($str); 
    }

    if (!empty($replace_code[1])) {
        foreach($replace_code[1] AS $key => $value) {
            $str = str_replace_once('#!%code%;', '<pre><code>' . trim($replace_code[1][$key]) . '</code></pre>', $str);
        }
    }

    $str = use_filters('ds_output_text', $str); 

    if (!empty($array['data']['attachments'])) {
        $attachments = get_output_media($array['data']['attachments']); 

        if ($attachments) {
            $str = $str . '<div class="ds-messages-attachments">' . $attachments . '</div>'; 
        }
    }
    
    return stripslashes($str);
}

function get_grid_images($array) 
{
    $count = count($array); 
    $tpl = array(); 

    if (count($array) >= 5) {
        array_push($tpl, array_splice($array, 0, 2)); 
    } else {
        array_push($tpl, array_splice($array, 0, 1)); 
    }

    if (count($array) >= 7) {
        array_push($tpl, array_splice($array, 0, 3)); 
    }

    if (count($array)) {
        array_push($tpl, $array); 
    }

    $template = '<div class="glr glr-rows-' . count($tpl) . ' glr-cnt-' . $count . '">';
    foreach($tpl AS $key => $items) {
        $template .= '<div class="glr-row glr-col-' . count($items) . '">' . join('', $items) . '</div>';
    }
    $template .= '</div>'; 

    return $template;
}

function get_output_media($files) 
{
    $array = array(
        'photos_videos' => array(), 
        'audios' => array(), 
        'files' => array(), 
    ); 

    $pre_output_media = use_filters('pre_get_output_media', null, $files); 

    if (null !== $pre_output_media) {
        return $pre_output_media; 
    }

    foreach($files AS $file) 
    {
        $file = get_file($file); 

        if (strpos($file['mimetype'], 'audio/') !== false) { 
            $array['audios'][] = get_audio_player($file); 
        } 

        elseif (strpos($file['mimetype'], 'image/') !== false) {
            $thumbnail = get_file_thumbnail($file, 'medium'); 
            $array['photos_videos'][] = '<a data-media="image" data-file="' . $file['id'] . '" href="' . get_file_link($file) . '">' . ds_file_thumbnail($file, 'medium') . '</a>'; 
        } 

        elseif (strpos($file['mimetype'], 'video/') !== false) {
            $thumbnail = ds_file_thumbnail($file, 'medium'); 
            if (!$thumbnail) {
                $thumbnail = '<span class="ds-thumbnail-empty" data-type="video"><img src="' . get_site_url('/sys/static/images/null-480.png') . '" alt="" /></span>'; 
            }

            $array['photos_videos'][] = '<a data-media="video" data-file="' . $file['id'] . '" href="' . get_file_link($file) . '">' . 
                                            $thumbnail . '<span class="ds-media-title">' . $file['title'] . '</span>' .
                                        '</a>'; 
        } 

        else {
            $array['files'][] = '<div class="output-item-file"><a data-file="' . $file['id'] . '" href="' . get_file_link($file) . '">' . get_file_icon($file) . ' ' . $file['title'] . '</a></div>'; 
        }
    } 

    $output = ''; 
    if ($array['photos_videos'])
        $output .= get_grid_images($array['photos_videos']); 

    if ($array['audios'])
        $output .= '<div class="ds-output-audios ds-col-' . count($array['audios']) . '" data-count="' . count($array['audios']) . '">' . join('', $array['audios']) . '</div>'; 

    if ($array['files'])
        $output .= '<div class="ds-output-files ds-col-' . count($array['files']) . '" data-count="' . count($array['files']) . '">' . join('', $array['files']) . '</div>'; 

    return use_filters('ds_get_output_media', $output, $array, $files); 
}

function str_replace_once($search, $replace, $text)
{
    $pos = strpos($text, $search);
    return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

// для форм
function input_value_text($str)
{
    return output_text($str, 0, 1, 0, 0, 0);
}
