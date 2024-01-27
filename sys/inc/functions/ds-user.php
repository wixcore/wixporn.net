<?php 

/** 
* Склонение строк по полу
* $m       - Строка Муж. 
* $w       - Строка Жен. 
* $user_id - ID пользователя
* @return string
*/ 

function strsex($m, $w, $user = null) 
{
    if ($user === null) 
        $user = get_user_id(); 

    if (is_numeric($user)) {
        $user = get_user($user); 
    }
    
    return ($user['pol'] == 1 ? $m : $w); 
}

function opsos( $ips = NULL )
{
    global $ip;
    if ($ips == NULL)
        $ips = $ip;
    $ipl = ip2long( $ips );
    if ( db::count("SELECT COUNT(*) FROM `opsos` WHERE `min` <= '$ipl' AND `max` >= '$ipl'") != 0 ) {
        $opsos = db::fetch("SELECT opsos FROM `opsos` WHERE `min` <= '$ipl' AND `max` >= '$ipl' LIMIT 1", ARRAY_A);
        return stripcslashes( htmlspecialchars( $opsos['opsos'] ) );
    } else
        return false;
}

/**
* Проверяет на существование пользователя в системе
* @return int | false
*/ 
function user_exists($value, $field = 'nick') 
{
    $ank = db::fetch("SELECT id FROM user WHERE `" . $field . "` = '" . db::esc($value) . "' LIMIT 1"); 

    if (isset($ank['id'])) {
        return $ank['id']; 
    }
    
    return false; 
}

/**
* Получает URL адрес изображения аватара 
* Для замены функции следует использовать хук ds_pre_get_avatar_url
* @return string 
*/ 
function get_avatar_url($user_id, $size = 'thumbnail')
{
    $avatar_url = use_filters('ds_pre_get_avatar_url', null, $user_id, $size); 

    if ($avatar_url !== null) {
        return use_filters('ds_get_avatar_url', $avatar_url, $user_id, $size); 
    }

    $file_id = get_user_meta($user_id, '__avatar'); 

    if (!$file_id) {
        return null; 
    }

    $file = get_file($file_id); 

    if ($file) {
        $avatar_url = get_file_thumbnail_url($file, $size); 
    }

    return use_filters('ds_get_avatar_url', $avatar_url); 
}

/**
* Получает HTML код аватара 
* Для замены функции следует использовать хук ds_pre_get_avatar
* @return string 
*/ 
function get_avatar($user_id, $size = 'thumbnail', $link = false) 
{
    $avatar = use_filters('ds_pre_get_avatar', null, $user_id, $size); 

    if ($avatar !== null) {
        return use_filters('ds_get_avatar', $avatar, $user_id, $size); 
    }

    $classes = array('avatar', 'avatar-' . $size); 
    $avatar_url = get_avatar_url($user_id, $size); 

    $file_id = get_user_meta($user_id, '__avatar'); 
    $file = get_file($file_id); 

    /**
    * Используйте фильтр ds_template_avatar чтобы изменить 
    * HTML тег вашего аватара
    * %class% - Классы тега изображения 
    * %src% - Путь к картинке аватара
    */ 

    $mask = array(
        '%class%' => join(' ', $classes), 
        '%src%' => $avatar_url,  
    ); 

    if ($file) {
        $mask['%href%'] = get_file_link($file);
    }

    if ($avatar_url) {
        if ($link === false || empty($mask['%href%'])) {
            $template = use_filters('ds_template_avatar', '<img class="%class%" src="%src%" />', $user_id, $size); 
        } else {
            $template = use_filters('ds_template_avatar_link', '<a class="avatar-link" href="%href%"><img class="%class%" src="%src%" /></a>', $user_id, $size); 
        }
    } else {
        $classes[] = 'avatar-default';
        $template = use_filters('ds_template_no_avatar', '<img class="%class%" src="%src%" />', $user_id, $size); 
        $mask['%src%'] = get_site_url('/style/user/avatar.gif'); 
    }
    
    $avatar = str_replace(array_keys($mask), array_values($mask), $template); 

    if ($avatar) {
        return use_filters('ds_get_avatar', $avatar, $user_id, $size); 
    }

    return ''; 
}

function user_collision($massive, $im = 0)
{
    global $user;
    $new = false;

    for ($i = 0; $i < count($massive); $i++) {
        $collision_q = db::query("SELECT * FROM `user_collision` WHERE `id_user` = '" . $massive[$i] . "' OR `id_user2` = '" . $massive[$i] . "'");

        while ($collision = $collision_q->fetch_assoc()) {
            if ($collision['id_user'] == $massive[$i])
                $coll = $collision['id_user2'];
            else 
                $coll = $collision['id_user'];

            $ank_coll2 = get_user($coll);

            if (!in_array ($coll, $massive) && ($user['level'] > $ank_coll2['level']) && ($im == 0 || $user['id'] != $ank_coll2['id'])) {
                $massive[] = $coll;
                $new = true;
            }
        }
    }

    if ($new)
        $massive = user_collision($massive);

    return $massive;
}

function user_access( $access, $u_id = null, $exit = false )
{
    if ( $u_id == null )
        global $user;
    else
        $user = get_user( $u_id );
    if ( !isset( $user['group_access'] ) || $user['group_access'] == null ) {
        if ( $exit !== false ) {
            header( 'Location: ' . $exit );
            exit;
        } else
            return false;
    }
    if ( $exit !== false ) {
        if ( db::count("SELECT COUNT(*) FROM `user_group_access` WHERE `id_group` = '$user[group_access]' AND `id_access` = '" . my_esc( $access ) . "'") == 0 ) {
            header( "Location: $exit" );
            exit;
        }
    } else
        return ( db::count("SELECT COUNT(*) FROM `user_group_access` WHERE `id_group` = '$user[group_access]' AND `id_access` = '" . my_esc( $access ) . "'") == 1 ? true : false );
}

function shif( $str )
{
    return md5(trim($str));
}

function cookie_encrypt($data, $id = 0)
{
    $key = defined('SALT_COOKIE_USER') ? SALT_COOKIE_USER : md5(get_user_ip()); 

    $l = strlen($key);
    if ($l < 16)
        $key = str_repeat($key, ceil(16/$l));

    if ($m = strlen($data) % 8)
        $data .= str_repeat("\x00",  8 - $m);
    if (function_exists('openssl_encrypt'))
        $val = openssl_encrypt($data, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
    elseif (function_exists('mcrypt_encrypt'))
        $val = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_ECB);
    else 
        $val = $data; 

    return base64_encode($val);
}

function cookie_decrypt($data, $id = 0)
{
    $key = defined('SALT_COOKIE_USER') ? SALT_COOKIE_USER : md5(get_user_ip()); 
    $data = base64_decode($data); 

    $l = strlen($key);
    if ($l < 16)
        $key = str_repeat($key, ceil(16/$l));

    if (function_exists('openssl_encrypt'))
        $val = openssl_decrypt($data, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
    elseif (function_exists('mcrypt_encrypt'))
        $val = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_ECB);
    else 
        $val = $data; 

    return $val;
}

function get_salt() 
{
    $hash = md5(SALT_COOKIE_USER);
    return $hash; 
}

function online($user = NULL)
{
    global $set, $time;
    static $users;
    
    if (!isset($users[$user]))
    {
        if (db::count("SELECT COUNT(id) FROM `user` WHERE `id` = '$user' AND `date_last` > '" . (time() - 60) . "' LIMIT 1") == 1)
        {
            if ($set['show_away'] == 0)$on = 'online';
            else
            {
                $ank = db::fetch("SELECT `date_last` FROM `user` WHERE `id` = '$user' LIMIT 1", ARRAY_A);
                if ((time() - $ank['date_last']) == 0)
                $on = 'online';
                else
                $on = 'away: ' . (time() - $ank['date_last']) . ' сек';
            }
            $ank = db::fetch("SELECT * FROM `user` WHERE `id` = '$user' LIMIT 1", ARRAY_A);

            if ($ank['browser'] == 'wap')
                $users[$user] = " <img src='/style/icons/online.gif' alt='*' /> ";
            else
                $users[$user] = " <img src='/style/icons/online_web.gif' alt='*' /> ";
        }
        else
        {
            $users[$user]=null;
        }
    }
    return $users[$user];
}

// только для зарегистрированых
function only_reg( $link = NULL )
{
    global $user;
    if ( !isset( $user ) ) {
        if ( $link == NULL )
            $link = '/index.php?' . SID;
        header( "Location: $link" );
        exit;
    }
}

// только для незарегистрированых
function only_unreg( $link = NULL )
{
    global $user;
    if ( isset( $user ) ) {
        if ( $link == NULL )
            $link = '/index.php?' . SID;
        header( "Location: $link" ); 
        exit;
    }
}

// только для тех, у кого уровень доступа больше или равен $level
function only_level( $level = 0, $link = NULL )
{
    global $user;
    if ( !isset( $user ) || $user['level'] < $level ) {
        if ( $link == NULL )
            $link = '/index.php?' . SID;
        header( "Location: $link" );
        exit;
    }
}

function is_user_access($access, $user_id = '')
{
    if ($user_id == '')
        $user = get_user(get_user_id());
    else
        $user = get_user($user_id);

    if (!isset($user['group_access']) || $user['group_access'] == null) {
        return false; 
    }

    $is_group_access = db::count("SELECT COUNT(`id_group`) FROM `user_group_access` 
                                WHERE `id_group` = '" . $user['group_access'] . "' AND `id_access` = '" . $access . "'"); 

    return ($is_group_access ? true : false);
}

/**
* Проверяет наличие пользователя по логину и паролю 
* Кеширует данные для дальнейшего их использования
* @return bolean 
*/ 

function is_auth_user($value, $password, $key = 'id') 
{
    global $ds_users_cache; 

    if (!$value || !$password) {
        return false; 
    }

    $password = shif($password); 
    $user = db::fetch("SELECT * FROM `user` WHERE `$key` = '" . db::esc($value) . "' AND `pass` = '" . $password . "' LIMIT 1", ARRAY_A);

    if (isset($user['id'])) {
        if (!isset($ds_users_cache[$user['group_access']])) {
            $group_access = get_group_access($user['group_access']);

            $user['level'] = $group_access['level'];
            $user['group_name'] = $group_access['group_name'];

            $ds_users_cache[$user['id']] = use_filters('ds_users_cache_add', $user); 
        }
        return true; 
    }
    return false; 
}

function is_user() {
    global $user; 
    if (isset($user['id'])) {
        return true; 
    }
    return false; 
}

/**
* Проверяет в сети ли указанный пользователь
*/ 
function is_online($user = false) 
{
    if (!$user) {
        $user_id = get_user_id(); 
    } elseif (is_numeric($user)) {
        $user = get_user($user); 
    }

    $time = use_filters('is_user_online', 60); 

    if (isset($user['id']) && $user['date_last'] + $time > time()) {
        return true; 
    }
    return false; 
}

/**
* Возвращает ссылку на страницу пользователя
* $user - ID или массив пользователя
* @return string
*/ 
function get_user_url($user = false) 
{
    if ($user === false) {
        $user = get_user_id(); 
    } 

    if (is_numeric($user)) {
        $user = get_user($user); 
    }

    if (isset($user['id'])) {
        return use_filters('get_user_url', get_site_url('/info.php?id=' . $user['id']), $user); 
    }
}

function get_user_id() 
{
    if (isset($_SESSION['id_user'])) {
        return (int) $_SESSION['id_user']; 
    }
    return ; 
}

function get_ip_address() 
{
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (validate_ip($ip)) {
                    return $ip;
                }
            }
        }
    }

    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

function validate_email($email) 
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true; 
    }
    return false; 
}

function validate_login($str) 
{
    $validate = true; 
    if (!preg_match("#^([A-z0-9\-\_])+$#ui", $str)) {
        $validate = false; 
    } 
    return $validate; 
}

/**
* Возвращаяет ник пользователя
*/ 
function get_user_nick($user_id = false) 
{
    if (!$user_id) {
        $user_id = get_user_id(); 
    }

    $user = get_user($user_id); 

    if (isset($user['id'])) 
        return use_filters('get_user_nick', $user['nick']); 
}

function get_user_by_nick($nick) 
{
    $user = db::fetch("SELECT id FROM user WHERE nick = '" . db::esc($nick) . "' LIMIT 1");

    if (empty($user)) {
        $user['id'] = 0; 
    }

    $user = get_user($user['id']); 
    return $user; 
}

/**
* Получает данные пользователя
* $user_id ID пользователя
* @return array 
*/

function get_user($user_id = false)
{
    if ($user_id === false) 
        $user_id = get_user_id(); 
    
    $ds_users = ds_get('ds_users', array());

    if ( $user_id == 0 ) {
        $user = use_filters('ds_system_user', array(
            'id' => 0, 
            'pol' => 1, 
            'level' => 999, 
            'nick' => __('Система'), 
            'group_name' => __('Системный робот'), 
            'ank_o_sebe' => __('Создан для уведомлений'), 
        )); 
        return $user;
    } else {
        $user_id = (int) $user_id;

        if (!isset($ds_users[$user_id])) {
            $ds_users[$user_id] = db::fetch("SELECT * FROM `user` WHERE `id` = '$user_id' LIMIT 1", ARRAY_A);

            if (isset($ds_users[$user_id]['id'])) {
                $group_access = get_group_access($ds_users[$user_id]['group_access']);

                if ( $group_access['group_name'] == null ) {
                    $ds_users[$user_id]['level']      = 0;
                    $ds_users[$user_id]['group_name'] = 'Пользователь';
                } else {
                    $ds_users[$user_id]['level']      = $group_access['level'];
                    $ds_users[$user_id]['group_name'] = $group_access['group_name'];
                }
            } else {
                $ds_users[$user_id] = false;
            }
        }

        ds_set('ds_users', $ds_users);

        return $ds_users[$user_id];
    }
}

/**
* Получает и кеширует данные анкеты пользователя
* $user_id ID пользователя
* @return array 
*/ 
function get_user_profile($user_id = null) 
{
    if ($user_id == null) 
        $user_id = get_user_ID(); 

    $default = use_filters('ds_user_profile_default', array(
        'city' => null, 
    )); 
    $ds_profiles = ds_get('ds_profiles', array());

    if (isset($ds_profiles[$user_id])) {
        return $ds_profiles[$user_id]; 
    }

    $ds_profiles[$user_id] = array(); 

    $array = db::select("SELECT * FROM `user_profile` WHERE `user_id` = '" . $user_id . "'", ARRAY_A);
    if ($array) {
        foreach($array AS $field) {
            $ds_profiles[$user_id][$field['profile_key']] = $field['profile_value']; 
        }
    }

    $ds_profiles[$user_id] = array_replace($default, $ds_profiles[$user_id]); 
    $ds_profiles[$user_id] = use_filters('ds_get_user_profile', $ds_profiles[$user_id], $user_id); 

    ds_set('ds_profiles', $ds_profiles);

    return $ds_profiles[$user_id]; 
}

/**
* Получает и кеширует группу пользователя
* $group_id ID группы
* @return array 
*/ 
function get_group_access($group_id) 
{
    global $ds_group_accesses; 

    if (!isset($ds_group_accesses[$group_id])) {
        $group_access = db::fetch("SELECT `level`, `name` AS `group_name` 
                                                        FROM `user_group` 
                                                        WHERE `id` = '" . (int) $group_id . "' LIMIT 1", ARRAY_A);

        $ds_group_accesses[$group_id] = $group_access;
    }

    return $ds_group_accesses[$group_id]; 
}

/**
* Добавляет или обновляет дополнительные поля пользователя
*/ 
function update_user_meta($user_id, $meta_key, $meta_value) 
{
    $meta = db::fetch("SELECT * FROM user_meta WHERE user_id = '" . $user_id . "' AND `meta_key` = '" . $meta_key . "' LIMIT 1"); 

    if (is_array($meta_value)) {
        $meta_value = serialize($meta_value); 
    }

    if (isset($meta['meta_id'])) {
        db::query("UPDATE user_meta SET meta_value = '" . $meta_value . "' WHERE meta_id = '" . $meta['meta_id'] . "' LIMIT 1"); 
    } else {
        db::insert('user_meta', array(
            'user_id' => $user_id, 
            'meta_key' => $meta_key, 
            'meta_value' => $meta_value, 
        )); 
    }
}

/**
* Получает метаданные пользователя
* @return array | NULL
*/ 
function get_user_meta($user_id, $meta_key = false) 
{
    $metadata = ds_get('get_user_meta', array()); 

    if ($meta_key !== false) {
        if (isset($metadata[$user_id][$meta_key])) {
            return $metadata[$user_id][$meta_key]; 
        }
    } else {
        if (isset($metadata[$user_id])) {
            return $metadata[$user_id]; 
        }
    }

    $meta = db::select("SELECT * FROM user_meta WHERE user_id = '" . $user_id . "'"); 

    foreach($meta AS $key => $value) {
        $metadata[$user_id][$value['meta_key']] = $value['meta_value']; 
    }

    ds_set('get_user_meta', $metadata); 

    if ($meta_key) {
        if (isset($metadata[$user_id][$meta_key])) {
            return $metadata[$user_id][$meta_key]; 
        }
    } else {
        if (isset($metadata[$user_id])) {
            return $metadata[$user_id]; 
        }
    }
}

/**
* Удаляет метаданные пользователя
*/ 
function delete_user_meta($user_id, $meta_key = NULL, $meta_value = NULL)  
{
    if ($user_id && $meta_key !== NULL && $meta_value !== NULL) {
        db::query("DELETE FROM user_meta WHERE user_id = '" . $user_id . "' AND `meta_key` = '" . $meta_key . "' AND `meta_value` = '" . $meta_value . "'"); 
    } elseif ($user_id && $meta_key !== NULL) {
        db::query("DELETE FROM user_meta WHERE user_id = '" . $user_id . "' AND `meta_key` = '" . $meta_key . "'"); 
    } elseif ($user_id) {
        db::query("DELETE FROM user_meta WHERE user_id = '" . $user_id . "'"); 
    }
}


/**
* Генератор пароля
* $k_simb колличество символов
* @return string
*/ 
function passgen($k_simb = 8, $types = 3) 
{
    $password = '';    
    $small = 'abcdefghijkmnpqrstuvwxyz';    
    $large = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';    
    $numbers = '1234567890';    
    
    mt_srand((double)microtime()*1000000);     
    
    for ($i = 0; $i < $k_simb; $i++) 
    {        
        $type = mt_rand(1,min($types,3));    
            
        switch ($type) 
        {        
            case 3:        
            $password .= $large[mt_rand(0, strlen($large) - 1)];            
            break;            
            case 2:            
            $password .= $small[mt_rand(0, strlen($small) - 1)];            
            break;            
            case 1:            
            $password .= $numbers[mt_rand(0,9)];            
            break;        
        }    
    }    

    return $password;
}

/**
* Обновляет информацию о пользователе
* Хук фильтр: {update_user_information}
*/ 

function update_user_information() 
{
    global $user, $ds_user_update; 

    $ds_user_update['date_last'] = time(); 
    $ds_user_update = use_filters('update_user_information', $ds_user_update); 

    if (isset($user['id']) && count($ds_user_update) > 0) {
        db::update('user', $ds_user_update, array(array(
            'field' => 'id', 
            'value' => $user['id'], 
        ))); 
    }
}


/**
* Добавляет данные о пользователе
*/ 
function add_user_update($key, $value) 
{
    global $ds_user_update; 
    $ds_user_update[$key] = $value; 
}

/**
* Счетчики пользователя
* Хук фильтр: {ds_user_counters}
*/ 
function get_user_counters($user_id = false) 
{
    if ($user_id === false) {
        $user_id = get_user_id(); 
    }

    $user = get_user($user_id); 

    if (!isset($user['id'])) {
        return ; 
    }

    $counter = ds_get('ds_user_counters', array()); 
    if (!empty($counter[$user_id])) {
        return use_filters('ds_user_counters', $counter[$user_id]); 
    }

    $counter[$user_id]['mail'] = array(
        'title' => __('Сообщения'), 
        'count' => db::count("SELECT COUNT(`mail`.`id`) FROM `mail`
             LEFT JOIN `mail_contacts` ON `mail`.`user_id` = `mail_contacts`.`contact_id` AND `mail_contacts`.`user_id` = '" . $user_id . "'
             WHERE `mail`.`contact_id` = '" . $user_id . "' AND `mail_contacts`.`status` != 'ignore' AND `mail`.`read` = '0'"), 
    ); 

    ds_set('ds_user_counters', $counter); 

    return use_filters('ds_user_counters', $counter[$user_id]); 
}

/**
* Выход с сайта
*/ 
function ds_user_logout() 
{
    setcookie('id_user');
    setcookie('pass');
    session_destroy();
    do_event('ds_user_logout'); 
    
    ds_redirect(get_site_url()); 
}

/**
* Добавляет область блока в профиле
*/ 
function add_profile_box($name, $args = array()) 
{
    $profile_boxes = ds_get('ds_profile_boxes', array()); 
    if (!isset($profile_boxes[$name])) {
        $profile_boxes[$name] = $args; 
    }
    ds_set('ds_profile_boxes', use_filters('add_profile_boxes', $profile_boxes)); 
}

/**
* Добавляет область блока в профиле
*/ 
function get_profile_boxes() 
{
    $profile_boxes = ds_get('ds_profile_boxes', array()); 
    return $profile_boxes; 
}

/** 
* Добавляет в область блока содержимое 
*/ 
function add_profile_item($boxId, $args) 
{
    $profile_items = ds_get('ds_profile_items', array()); 
    
    $profile_items[$boxId][] = $args; 
    
    ds_set('ds_profile_items', use_filters('add_profile_items', $profile_items)); 
}

/**
* Функция получает обработанный контент области блока профиля
*/ 
function get_profile_items($user_id, $box_id, $args) 
{
    $profile_items = ds_get('ds_profile_items', array());
    $pre_items = array(); 

    foreach($profile_items[$box_id] AS $item)
    {
        // Шаблон 
        if (!isset($item['template'])) {
            $item['template'] = use_filters('ds_template_profile_item', '<div class="profile-box-item %class">%content</div>'); 
        }

        // Хук фильтр (динамический)
        $filter = 'filters_' . md5(microtime()); 

        // Callback функция содержимого
        if (isset($item['function'])) {
            add_filter($filter, $item['function']); 
        }

        // Получаем контент в блок области профиля
        $content = use_filters($filter, $user_id); 

        if ($content) {
            $pre_items[] = str_replace(array('%class', '%content'), array($item['class'], $content), $item['template']);
        }
    }

    return join('', $pre_items);
}

/**
* Вывод ника пользователя
*/
function get_profile_title($user_id) {
    $ank = get_user($user_id); 
    $content = sprintf('%s %s %s %s', group($ank['id']), use_filters('ds_user_nick', $ank['nick']), medal($ank['id']), online($ank['id']));

    if ((user_access('user_ban_set') || user_access('user_ban_set_h') || user_access('user_ban_unset')) && $ank['id'] != get_user_id()) {
        $content .= ' <a class="ds-link-ban" href="' . get_site_url('/adm_panel/ban.php?id=' . $ank['id']) . '">Бан</a> ';
    }
    
    $title = str_replace('%content', $content, use_filters('ds_template_profile_title', '%content'));
    return $title; 
}

/**
* Вывод аватара пользователя в профиле
*/
function get_profile_avatar($user_id) 
{
    $content = avatar($user_id, 1, 128); 
    $avatar = str_replace('%content', $content, use_filters('ds_template_profile_avatar', '%content'));
    return $avatar; 
}


function get_profile_menu($menu) 
{
    $counter = use_filters('ds_template_profile_menu_counter', '<span class="counter">(%count)</span>'); 
    $template = '<a href="%link" class="%class">%icon %title %count</span></a>'; 

    $pre_html = array(); 
    foreach($menu AS $key => $value) 
    {
        if (!isset($value['template'])) {
            $value['template'] = use_filters('ds_template_profile_menu_link', $template);
        }

        if (!isset($value['template'])) {
            $value['template'] = use_filters('ds_template_profile_menu_link', $template);
        }

        $count = ''; 
        if (isset($value['count'])) {
            $count = str_replace('%count', $value['count'], $counter);
        }

        $keys = array('%title', '%link', '%count', '%class', '%icon'); 
        $values = array($value['title'], $value['link'], $count, '', $value['icon']); 

        $pre_html[] = str_replace($keys, $values, $value['template']);
    }

    return join('', $pre_html); 
}

/**
* Вывод меню пользователя в профиле
*/
function get_profile_media($user_id) 
{ 
    $ank = get_user($user_id); 

    $menu['photos'] = array(
        'link' => get_site_url('/photos/index/' . $ank['nick'] . '/'), 
        'title' => __('Фотографии'), 
        'icon' => '<img src="' . get_site_url('/style/icons/foto.png') . '" alt="Photo" />', 
        'count' => get_count_files_user($user_id, 'photos'), 
    ); 

    $menu['files'] = array(
        'link' => get_site_url('/files/index/' . $ank['nick'] . '/'), 
        'title' => __('Файлы'), 
        'icon' => '<img src="' . get_site_url('/style/icons/files.gif') . '" alt="Files" />', 
        'count' => get_count_files_user($user_id, 'files'), 
    ); 

    $menu['music'] = array(
        'link' => get_site_url('/music/index/' . $ank['nick'] . '/'), 
        'title' => __('Музыка'), 
        'icon' => '<img src="' . get_site_url('/style/icons/foto.png') . '" alt="Music" />', 
        'count' => get_count_files_user($user_id, 'music'), 
    ); 

    return get_profile_menu(use_filters('get_profile_menu_media', $menu)); 
}

function get_profile_anketa($user_id) 
{
    $menu['anketa'] = array(
        'link' => get_site_url('/user/anketa/?id=' . $user_id), 
        'title' => __('Анкета'), 
        'icon' => '<img src="' . get_site_url('/style/icons/anketa.gif') . '" alt="Anketa" />', 
    ); 

    return get_profile_menu(use_filters('get_profile_menu_anketa', $menu)); 
}

function get_profile_friends($user_id) 
{
    $menu['friends'] = array(
        'link' => get_friends_link($user_id), 
        'title' => __('Друзья'), 
        'icon' => '<img src="' . get_site_url('/style/icons/druzya.png') . '" alt="Friends" />', 
        'count' => db::count("SELECT COUNT(*) FROM `frends` WHERE `user` = '$user_id' AND `i` = '1'"), 
    ); 

    return get_profile_menu(use_filters('get_profile_menu_friends', $menu)); 
}

function get_profile_action($user_id) 
{
    $ank = get_user($user_id); 
    $menu = array(); 

    if (get_user_id() && $ank['id'] != get_user_id()) 
    {
        $menu['message'] = array(
            'link' => get_site_url('/mail.php?id=' . $user_id), 
            'title' => __('Написать сообщение'), 
            'icon' => '<img src="' . get_site_url('/style/icons/pochta.gif') . '" alt="Message" />', 
        );     

        $labels = array(
            -100 => __('Ошибка данных'), 
            'sent' => __('Заявка отправлена'), 
            'locked' => __('Вы заблокированы'), 
            'unlock' => __('Разблокировать'), 
            'add' => __('Добавить в друзья'), 
            'confirm' => __('Подтвердить дружбу'), 
            'delete' => __('Удалить из друзей'), 
            'subscribed' => __('Вы подписаны'), 
            'read' => __('На вас подписаны'), 
            'friends' => __('Вы друзья'), 
        );

        $links = get_friends_action_links(get_user_id(), $ank['id']); 

        foreach($links AS $key => $link) {
            if (count($links) == 1) {
                $menu['friends_' . $key] = array(
                    'link' => $link['url'], 
                    'title' => $link['title'], 
                    'icon' => '<img src="' . get_site_url('/style/icons/druzya.png') . '" />', 
                );               
            } else {
                $menu['friends_' . $key] = array(
                    'link' => $link['url'], 
                    'title' => $link['title'], 
                    'icon' => '<img src="' . get_site_url('/style/icons/druzya.png') . '" />', 
                ); 
            }
        }
    } 

    elseif (is_user()) {  
        $menu['settings'] = array(
            'link' => get_site_url('/user/settings/'), 
            'title' => __('Мои настройки'), 
            'icon' => '<img src="' . get_site_url('/style/icons/settings.png') . '" alt="Settings" />', 
            'template' => '<div class="ds-profile-group"><a href="%link" class="%class">%icon %title</span></a> | <a class="%class" href="' . get_site_url('/umenu.php') . '">' . __('Меню') . '</a></div>', 
        );     
    }

    return get_profile_menu(use_filters('get_profile_menu_friends', $menu)); 
}

/**
* Функция отвечает за вывод странички пользователя
*/ 
function ds_profile_view($user_id) 
{
    $ank = get_user($user_id); 

    // Зарегистрированные области 
    $boxes = get_profile_boxes(); 

    $pre_html = array(); 
    foreach($boxes AS $box_id => $args) 
    {
        // Шаблон блока области
        $template = use_filters('ds_template_profile_box', '<div class="%class">%content</div>'); 

        // Содержимое блока области
        $box_content = get_profile_items($user_id, $box_id, $args); 

        if (!isset($args['class'])) {
            $args['class'] = 'profile-box';
        }

        // Помещаем сформированный html в шаблон
        if ($box_content) {
            $pre_html[] = str_replace(array('%class', '%content'), array($args['class'], $box_content), $template); 
        }
    }

    echo join('', $pre_html); 
}

/**
* Регистрируем стандартный профиля пользователя
*/ 
function ds_profile_load() 
{
    // Область заголовка и аватара
    add_profile_box('ds_profile_head'); 

    // Вывод ника и времени последнего посещения
    add_profile_item('ds_profile_head', array(
        'function' => 'get_profile_title', 
        'class'    => 'ds-profile-title', 
    )); 

    // Аватар пользователя
    add_profile_item('ds_profile_head', array(
        'function' => 'get_profile_avatar', 
        'class'    => 'ds-profile-avatar', 
    )); 

    // Область меню пользователя
    add_profile_box('ds_profile_body'); 

    // Анкета пользователя
    add_profile_item('ds_profile_body', array(
        'function' => 'get_profile_anketa', 
        'class'    => 'ds-profile-menu', 
    )); 

    // Друзья пользователя
    add_profile_item('ds_profile_body', array(
        'function' => 'get_profile_friends', 
        'class'    => 'ds-profile-menu', 
    )); 

    // Вывод фото, файлы и музыки
    add_profile_item('ds_profile_body', array(
        'function' => 'get_profile_media', 
        'class'    => 'ds-profile-menu', 
    )); 

    add_profile_item('ds_profile_body', array(
        'function' => 'get_profile_action', 
        'class'    => 'ds-profile-menu',  
    )); 
}