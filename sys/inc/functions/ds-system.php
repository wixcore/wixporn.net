<?php 

/**
* Получает UID языка сайта 
* @return string
*/ 
function get_language() 
{ 
    $language = ds_get('site_language', 'en_US');  
    return $language; 
} 

function get_version() 
{
    require ROOTPATH . '/sys/inc/version.php';
    return $cs_core_version; 
}

/**
* Алиасы класса Registry
* @return object | string | array
*/

function ds_get($key, $default = NULL) 
{
    if (isset($key)) { 
        $get = Registry::get($key); 

        if ($get === NULL && $default !== NULL) {
            return $default;
        }
        return $get; 
    }
}

/**
* Регистрирует глобальную переменную
* @return bolean true
*/
function ds_set($key, $var) 
{
    return Registry::set($key, $var); 
}

/**
* Возвращает все глобальные данные
* @return array
*/
function ds_getAll() 
{
    return Registry::getAll($key); 
}


function libload($filename) 
{
    $path = ROOTPATH . '/sys/inc/libs/' . $filename; 
    $hash = 'LIB_' . md5($path); 

    if (defined($hash)) {
        return ; 
    }

    if (is_file($path)) {
        define($hash, true); 
        require($path); 
    }
}

function ds_die($msg = '') 
{
    $ob_end_content = ob_get_contents(); 

    if ($ob_end_content) {
        ob_end_clean(); 
        $msg = $ob_end_content . "<br >" . $msg; 
    }
    
    die('
    <html>
        <head>
            <title>' . __('Ошибка %s', 'CMS-Social') . '</title>
            <style>
            body {
                background-color: white;
                margin: 0; 
                padding: 0;
            }
            #ds-error {
                padding: 20px;
                width: 700px;
                max-width: 90%;
                margin: 30px auto;
                border: 1px solid #eaeaea;
                border-radius: 10px;
                box-sizing: border-box;
                background-color: #f9f9f9;
            }
            </style>
        </head>
        <body><div id="ds-error">
            ' . $msg . '
        </div></body>
    </html>');

}

/**
* Получает настройки сайта с учетом настроек пользователя
* Может возвращать как значение так и все настройки
* @return array | string
*/ 

function get_settings($key = false, $default = '') 
{
    $set = get_system(); 
    $set_user = array(); 
    
    if (is_user()) {
        $set_user = get_user_options(get_user_id(), 'general'); 
    }
    
    $set = use_filters('ds_settings_filter', array_replace($set, $set_user));

    if ($key === false) {
        return $set; 
    } elseif (isset($set[$key])) {
		return $set[$key]; 
	}

	return $default; 
}

/**
* Получает настройки сайта без учета настроек пользователя
* @return array
*/ 

function get_system() 
{
    $set = array();
    $set_default = array();
    $set_dinamic = array();

    $ini = parse_ini_file( ROOTPATH . '/sys/upgrade/settings.ini', true );

    if (is_array($ini['DEFAULT'])) {
        $set_default = $ini['DEFAULT'];
    }
    
    $set_dinamic = get_options();
    $set = use_filters('ds_system_filter', array_replace($set_default, $set_dinamic));

    return $set; 
}

function sort_position($a, $b) 
{ 
    return strnatcmp($a["position"], $b["position"]); 
} 

function file_add_cache($cacheFile, $array) 
{
    $cacheFile = PATH_CACHE . '/' . $cacheFile . '.cache';

    if (is_file($cacheFile)) {
        unlink($cacheFile);
    } 

    file_put_contents($cacheFile, json_encode($array, JSON_UNESCAPED_UNICODE));
}

function file_get_cache($cacheFile) 
{
    $cacheFile = PATH_CACHE . '/' . $cacheFile . '.cache';

    if (is_file($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), 1); 
        return $cache;
    }

    return false; 
}

function file_delete_cache($cacheFile) 
{
    $cacheFile = PATH_CACHE . '/' . $cacheFile . '.cache';
    if (is_file($cacheFile)) {
        unlink($cacheFile);
    } 
}


function get_options($type = 'autoload') 
{
    $options = ds_get('ds_options_' . $type);

    if (!empty($options[$type])) { 
        return $options[$type]; 
    }

    $res = db::select("SELECT * FROM `options` WHERE `type` = '" . $type . "'"); 

    $options[$type] = array(); 
    foreach($res AS $key => $option) {
        $options[$type][$option['name']] = $option['value']; 
    }

    ds_set('ds_options_' . $type, $options);

    return (is_array($options[$type]) ? $options[$type] : array()); 
}

function get_option($key, $default = NULL) 
{
    $options = ds_get('ds_options');

    if (!empty($options)) {
        if (isset($options[$key])) {
            return $options[$key];
        }
    }

    $cache = file_get_cache('options.cache'); 
    if (!empty($cache)) {
        if (isset($cache[$key])) {
            return $cache[$key];   
        }
    }
    
    $res = db::fetch("SELECT * FROM `options` WHERE `name` = '" . my_esc($key) . "' LIMIT 1"); 

    if ($res) {
        return $res['value'];  
    }
    
    return $default; 
}

function update_option($key, $value, $type = '') 
{ 
    // Удаляем кеш опций
    file_delete_cache('options'); 

    $option = db::fetch("SELECT * FROM `options` WHERE `name` = '" . $key . "' LIMIT 1"); 

    if (is_array($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE); 
    }

    if (isset($option['id'])) {
        $update = array(
            'value' => $value, 
        ); 
        db::update('options', $update, array('name' => $key)); 
    }
    
    else {
       db::query("INSERT INTO `options` (`name`, `value`, `type`) VALUES ('$key', '$value', '$type')");
    }
}

function delete_option($key) 
{ 
    // Удаляем кеш опций
    file_delete_cache('options'); 
    $option = db::fetch("SELECT * FROM `options` WHERE `name` = '" . $key . "' LIMIT 1"); 
    if (isset($option['id'])) {
        db::delete('options', array('name' => $key));
        return true;  
    }
    return false; 
}

function ds_options_load() 
{
    $cache = file_get_cache('options'); 

    if (empty($cache)) {
        $options = db::select("SELECT * FROM `options` WHERE type = 'autoload'", ARRAY_A);

        $cache = array(); 
        foreach($options AS $option) {
            $cache[$option['name']] = $option['value']; 
        }
        file_add_cache('options', $cache);
        ds_get('ds_options', $cache); 
 
        // Язык сайта 
        if (isset($options['site_language'])) {
            ds_set('site_language', $options['site_language']); 
        }
    }

    ds_set('ds_options', $cache);
}

function ds_get_file_extensions($path) 
{
    return strtolower(substr($path, strrpos($path, '.') + 1)); 
}

function ds_readdir_files_list($dir, $args = array()) 
{
    $ds_files_recursive = array(); 

    if (!is_dir($dir)) {
        return $ds_files_recursive; 
    }

    $opdirbase = opendir($dir);
    while ($filebase = readdir($opdirbase)) {
        if ($filebase == '..' || $filebase == '.') continue; 

        if (is_file($dir . '/' . $filebase)) {
            if (!empty($args['skip_extensions']) && in_array(ds_get_file_extensions($filebase), $args['skip_extensions'])) {
                continue; 
            }

            if (!empty($args['allowed_extensions']) && !in_array(ds_get_file_extensions($filebase), $args['allowed_extensions'])) {
                continue; 
            }

            $ds_files_recursive[] = $dir . '/' . $filebase;
        }

        elseif (is_dir($dir . '/' . $filebase)) {
            if (!empty($args['skip_folders']) && in_array($dir . '/' . $filebase, $args['skip_folders'])) {
                continue;  
            }
            
            $ds_files_recursive = array_merge_recursive($ds_files_recursive, ds_readdir_files_list($dir . '/' . $filebase, $args)); 
        }
    }

    return $ds_files_recursive; 
}

function ds_readdir_dir_list($dir, $ds_directory_recursive = array()) 
{
    $opdirbase = opendir($dir);
    while ($filebase = readdir($opdirbase)) {
        if ($filebase == '..' || $filebase == '.') continue; 

        if (is_dir($dir . '/' . $filebase)) {
            $ds_directory_recursive[] = $dir . '/' . $filebase;
            $ds_directory_recursive = array_merge_recursive($ds_directory_recursive, ds_readdir_dir_list($dir . '/' . $filebase)); 
        }
    }

    return $ds_directory_recursive; 
} 

function ds_check_installed() 
{
    $install = true; 
    if (!is_file(ROOTPATH . '/config.php')) {
        header('Location: ' . get_site_url('/adm_panel/install.php')); 
        exit;
    }
}

function is_serialized( $data ) 
{
    if (!is_string($data)) {
        return false;
    }

    $data = trim($data);

    if (strlen($data) < 4) {
        return false;
    } elseif (':' !== $data[1]) {
        return false;
    } elseif (';' !== substr($data, -1)) {
        return false;
    } elseif ($data[0] !== 's') {
        return false;
    } elseif ('"' !== substr($data, -2, 1)) {
        return false;
    } else {
        return true;
    }
}

function ds_mail($to, $subject, $message, $headers = '', $attachments = array()) 
{
    $atts = use_filters('ds_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments'));

    if ($ds_mail_retrun = use_filters('ds_pre_ds_mail', null, $to, $subject, $message, $headers, $attachments) !== null) {
        return $ds_mail_retrun; 
    }

    if (isset($atts['to'])) {
        $to = $atts['to'];
    }

    if (!is_array($to)) {
        $to = explode(',', $to);
    }

    if (isset($atts['subject'])) {
        $subject = $atts['subject'];
    }

    if (isset($atts['message'])) {
        $message = $atts['message'];
    }

    if (isset($atts['headers'])) {
        $headers = $atts['headers'];
    }

    if (isset($atts['attachments'])) {
        $attachments = $atts['attachments'];
    }

    if (! is_array($attachments)) {
        $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
    }

    global $phpmailer;

    if (!($phpmailer instanceof PHPMailer)) {
        libload('PHPMailer.php');  
        libload('SMTP.php');  

        $phpmailer = new PHPMailer(true);
    }

    $cc       = array();
    $bcc      = array();
    $reply_to = array();

    if ( empty( $headers ) ) {
        $headers = array();
    } else {
        if ( ! is_array( $headers ) ) {
            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } else {
            $tempheaders = $headers;
        }
        $headers = array();

        if ( ! empty( $tempheaders ) ) {
            foreach ( (array) $tempheaders as $header ) {
                if ( strpos( $header, ':' ) === false ) {
                    if ( false !== stripos( $header, 'boundary=' ) ) {
                        $parts    = preg_split( '/boundary=/i', trim( $header ) );
                        $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                    }
                    continue;
                }

                list( $name, $content ) = explode( ':', trim( $header ), 2 );

                $name    = trim( $name );
                $content = trim( $content );

                switch ( strtolower( $name ) ) {
                    case 'from':
                        $bracket_pos = strpos( $content, '<' );
                        if ( false !== $bracket_pos ) {
                            if ( $bracket_pos > 0 ) {
                                $from_name = substr( $content, 0, $bracket_pos - 1 );
                                $from_name = str_replace( '"', '', $from_name );
                                $from_name = trim( $from_name );
                            }

                            $from_email = substr( $content, $bracket_pos + 1 );
                            $from_email = str_replace( '>', '', $from_email );
                            $from_email = trim( $from_email );

                        } elseif ( '' !== trim( $content ) ) {
                            $from_email = trim( $content );
                        }
                        break;
                    case 'content-type':
                        if ( strpos( $content, ';' ) !== false ) {
                            list( $type, $charset_content ) = explode( ';', $content );
                            $content_type                   = trim( $type );
                            if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                            } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                $charset  = '';
                            }

                        } elseif ( '' !== trim( $content ) ) {
                            $content_type = trim( $content );
                        }
                        break;
                    case 'cc':
                        $cc = array_merge( (array) $cc, explode( ',', $content ) );
                        break;
                    case 'bcc':
                        $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                        break;
                    case 'reply-to':
                        $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                        break;
                    default:
                        $headers[ trim( $name ) ] = trim( $content );
                        break;
                }
            }
        }
    }

    $phpmailer->clearAllRecipients();
    $phpmailer->clearAttachments();
    $phpmailer->clearCustomHeaders();
    $phpmailer->clearReplyTos();

    if ( ! isset( $from_name ) ) {
        $from_name = 'CMS-Social';
    }

    if ( ! isset( $from_email ) ) {
        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        $from_email = 'cms-social@' . $sitename;
    }

    $from_email = use_filters( 'ds_mail_from', $from_email );
    $from_name = use_filters( 'ds_mail_from_name', $from_name );

    try {
        $phpmailer->setFrom( $from_email, $from_name, false );
    } catch ( phpmailerException $e ) {
        $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
        $mail_error_data['phpmailer_exception_code'] = $e->getCode();

        do_event( 'ds_mail_failed', $mail_error_data);

        return false;
    }

    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;

    $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

    foreach ( $address_headers as $address_header => $addresses ) {
        if ( empty( $addresses ) ) {
            continue;
        }

        foreach ( (array) $addresses as $address ) {
            try {
                $recipient_name = '';

                if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $address        = $matches[2];
                    }
                }

                switch ( $address_header ) {
                    case 'to':
                        $phpmailer->addAddress( $address, $recipient_name );
                        break;
                    case 'cc':
                        $phpmailer->addCc( $address, $recipient_name );
                        break;
                    case 'bcc':
                        $phpmailer->addBcc( $address, $recipient_name );
                        break;
                    case 'reply_to':
                        $phpmailer->addReplyTo( $address, $recipient_name );
                        break;
                }
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    $phpmailer->isMail();

    if (!isset($content_type)) {
        $content_type = 'text/plain';
    }

    $content_type = use_filters( 'ds_mail_content_type', $content_type );
    $phpmailer->ContentType = $content_type;

    if ( 'text/html' == $content_type ) {
        $phpmailer->isHTML( true );
    }

    if ( ! isset( $charset ) ) {
        $charset = 'utf-8';
    }

    $phpmailer->CharSet = use_filters( 'ds_mail_charset', $charset );

    if ( ! empty( $headers ) ) {
        foreach ( (array) $headers as $name => $content ) {
            if ( ! in_array( $name, array( 'MIME-Version', 'X-Mailer' ) ) ) {
                $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
            }
        }

        if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
            $phpmailer->addCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
        }
    }

    if ( ! empty( $attachments ) ) {
        foreach ( $attachments as $attachment ) {
            try {
                $phpmailer->addAttachment( $attachment );
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    try {
        return $phpmailer->send();
    } catch ( phpmailerException $e ) {

        $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
        $mail_error_data['phpmailer_exception_code'] = $e->getCode();

        do_event( 'ds_mail_failed', $mail_error_data);

        return false;
    }
}


/**
* Регистрирует новый модуль поиска
*/ 
function ds_register_search($uid, $args = array()) 
{
    $register = ds_get('ds_register_search', array()); 

    if (!isset($register[$uid])) {
        $register[$uid] = $args; 
    }

    ds_set('ds_register_search', $register);
}

/**
* Возвращает зарегистрированные поиски
* Можно так-же получить конкретный по ID
* @return array
*/ 
function get_register_search($uid = NULL) 
{
    $register = ds_get('ds_register_search', array()); 

    if ($uid !== NULL && isset($register[$uid])) {
        return $register[$uid]; 
    }

    return $register;
}

/**
* Регистрируем модули поиска по умолчанию
*/ 
function ds_seacrh_init() 
{
    $default = array(
        'users' => array(
            'name' => __('Люди'), 
            'url' => '/users.php?q=%query%', 
            'callback' => 'ds_callback_search_users', 
        ), 
    ); 

    foreach($default AS $uid => $args) {
        ds_register_search($uid, $args); 
    }
}


/**
* Добавляет строку в лог файл
* */

function add_log($str, $type = 'message') 
{
    $log = ROOTPATH . '/sys/tmp/system-log.txt'; 

    if (!is_file($log)) {
        file_put_contents($log, ''); 
    }

    if (is_writable($log)) {
        if (!$handle = fopen($log, 'a')) {
            return false; 
        }

        if (fwrite($handle, '['.$type.'] ' . date('Y-m-d H:i:s') . ' "' . $str . "\"\n") === FALSE) {
            return false; 
        }

        fclose($handle);
        return true; 
    }

    return false; 
}