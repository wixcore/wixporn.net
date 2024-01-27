<?php 

/**
* header.php для админ панели
* Хук событие: {init_head_admin_theme}
*/
function get_header_admin() 
{
    do_event('init_head_admin_theme');
    global $err, $set; 

    $ds_errors = ds_get('ds_errors', array()); 

    if (is_array($err)) {
        $ds_errors = array_merge($err, $ds_errors); 
    } elseif (!empty($err)) {
        $ds_errors[] = $err; 
    }
    ds_set('ds_errors', $ds_errors); 

    if (is_file(ROOTPATH.'/sys/inc/admin/header.php')) {
        require(ROOTPATH.'/sys/inc/admin/header.php'); 
    }

    do_event('init_head_admin_theme_loaded');
}

/**
* footer.php для админ панели
* Хук событие: {init_foot_admin_theme}
*/
function get_footer_admin() 
{
    do_event('init_foot_admin_theme'); 

    if (is_file(ROOTPATH.'/sys/inc/admin/footer.php')) {
        require(ROOTPATH.'/sys/inc/admin/footer.php'); 
    }
    
    do_event('init_foot_admin_theme_loaded');
}


/**
* header.php для шаблонов сайта
* Хук событие: {init_head_theme}
*/
function get_header() 
{
    do_event('init_head_theme');

    global $err, $set; 

    $ds_errors = ds_get('ds_errors', array()); 

    if (is_array($err)) {
        $ds_errors = array_merge($err, $ds_errors); 
    } elseif (!empty($err)) {
        $ds_errors[] = $err; 
    }
    ds_set('ds_errors', $ds_errors); 

    if (is_file(get_theme_directory().'/header.php')) {
        require(get_theme_directory().'/header.php'); 
    }

    do_event('init_head_theme_loaded');
}

/**
* footer.php для шаблонов сайта
* Хук событие: {init_foot_theme}
*/
function get_footer() 
{
    do_event('init_foot_theme'); 

    if (is_file(get_theme_directory().'/footer.php')) {
        require(get_theme_directory().'/footer.php'); 
    }
    
    do_event('init_foot_theme_loaded');
}

/**
* Путь до папки с темой
*/ 
function get_theme_directory() 
{
    global $set; 
    return (ROOTPATH . '/style/themes/' . $set['set_them']); 
}

/**
* Путь до папки с темами
*/ 
function get_themes_directory() 
{
    return (ROOTPATH . '/style/themes'); 
}

/**
* URL адрес до папки с темой
*/ 
function get_theme_uri() 
{
    global $set; 
    return get_site_url('/style/themes/' . $set['set_them']); 
}

/**
* Вывод сообщений в теме шаблона
*/ 
function ds_messages() 
{
    do_event('pre_output_messages');

    if (isset($_SESSION['message'])) {
        do_event('ds_messages_output');
        echo use_filters('ds_message_filter', '<div class="msg">' . $_SESSION['message'] . '</div>'); 
        $_SESSION['message'] = NULL; 
    }
}

/**
* Вывод сообщений ошибок в теме шаблона
*/ 
function ds_errors() 
{
    $template = use_filters('ds_template_error', '<div class="err">%error</div>'); 

    $errors = get_errors(); 

    do_event('pre_output_errors');
    foreach($errors AS $error) {
        echo str_replace('%error', $error, $template); 
    }
    do_event('ds_errors_output');
}

function get_errors() 
{
    $err = use_filters('ds_errors', ds_get('ds_errors', array())); 

    if (!empty($err)) {
        if (is_array($err)) {
            return $err; 
        } else {
            return $err; 
        }
    }

    return $err; 
}

function is_errors() {
    $err = ds_get('ds_errors', false);
    if ($err) return true; 
    return false; 
}

function add_error($msg = '') 
{
    $err = ds_get('ds_errors', array()); 
    $err[] = $msg; 
    ds_set('ds_errors', $err);
}

function ds_head() 
{
    do_event('init_head'); 
    ds_theme_styles_html();
}

function ds_foot() 
{
    do_event('init_foot'); 
    ds_theme_scripts_html();
}

function ds_admin_head() 
{
    do_event('init_admin_head'); 
    ds_theme_styles_html();
}

function ds_admin_foot() 
{
    do_event('init_admin_foot'); 
    ds_theme_scripts_html();
}

function ds_themes() 
{
    $path = get_themes_directory(); 
    $opdirbase = opendir($path);

    $templates = array(); 
    while ($filebase = readdir($opdirbase)) 
    {
        if (is_dir($path . '/' . $filebase) && !preg_match('/[\.]{1,2}/', $filebase)) {
            $templates[$filebase] = $filebase; 
        }
    }

    return $templates;
}

/**
* Функция возвращает конечный заголовок страницы
* @return string 
*/
function ds_get_document_title() {
    $title = Registry::get('document_title'); 

    global $set; 

    if (isset($set['title'])) {
        $title = $set['title']; 
    } elseif (empty($title)) {
        $title = get_settings('title');
    }
    
    return use_filters('ds_get_document_title', $title);
}

/**
* Функция выводит конечный заголовок страницы
* @display string 
*/
function ds_document_title() {
    echo use_filters('ds_document_title', ds_get_document_title());
}

function ds_title() 
{
    do_event('ds_output_title'); 
}

/**
* Регистрация классов для тега body
* Принимает аргументы string | array
*/
function add_body_class($class) {
    $classes = ds_get('ds_body_classes', array()); 
    
    if (is_array($class)) {
        foreach($class AS $key => $value) {
            if (!isset($classes[$value])) {
                $classes[] = $value; 
            }
        }
    }
    
    elseif (!isset($classes[$class])) {
        $classes[] = $class; 
    }
    
    ds_set('ds_body_classes', $classes); 
}

/**
* Удаление классов у тега body
* Принимает аргументы string | array
*/
function remove_body_class($class) {
    $classes = ds_get('ds_body_classes', array()); 
    
    if (is_array($class)) {
        foreach($class AS $key => $value) {
            if ($search = array_search($value)) {
                unset($classes[$search]); 
            }
        }
    }
    
    else { 
        if ($search = array_search($class)) {
            unset($classes[$search]); 
        }
    }
    
    ds_set('ds_body_classes', $classes); 
}

/**
* Возвращает все зарегистрированные классы body
* @return string
*/
function get_body_class() 
{ 
    $classes = ds_get('ds_body_classes', array()); 
    
    // если авторизован
    if (is_user()) {
        $classes[] = 'logged-in';
    }
    
    // Главная страница
    if (is_home()) {
        $classes[] = 'home';
    }
    
    return use_filters('ds_body_class', $classes); 
}

/**
* Выводит все зарегистрированные классы body
* @display string
*/
function body_class() 
{
    echo 'class="' . join(' ', get_body_class()) . '"'; 
}

/**
* Инициализация css файлов для шаблона темы
*/
function ds_theme_styles_init() 
{
    $version = get_version(); 
    ds_theme_style_add(get_site_url('/sys/static/css/php-grid.css'), 'php-grid', $version, 'all'); 
    ds_theme_style_add(get_site_url('/sys/static/css/spectrum.css'), 'spectrum', $version, 'all'); 
    ds_theme_style_add(get_site_url('/sys/static/css/upload.css'), 'upload', $version, 'all'); 
    ds_theme_style_add(get_site_url('/sys/static/css/font-awesome.min.css'), 'font-awesome-icons', $version, 'all'); 
    ds_theme_style_add(get_site_url('/sys/static/css/modal.css'), 'modal', $version, 'all'); 

    if (is_file(get_theme_directory() . '/style.css')) {
        $version = filemtime(get_theme_directory() . '/style.css'); 
        ds_theme_style_add(use_filters('ds_base_default_theme_style', get_theme_uri() . '/style.css'), 'style', $version, 'all'); 
    }
    do_event('ds_theme_styles_init'); 
}

/**
* Инициализация javascript для шаблона темы
*/
function ds_theme_scripts_init() 
{
    $version = get_version(); 
    ds_theme_script_add(get_site_url('/sys/static/js/jquery-3.4.1.min.js'), 'jquery', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/spectrum.js'), 'spectrum', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/ds-emoji.js'), 'emoji', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/ds-editor.js'), 'editor', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/ds-player.js'), 'audio-player', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/ds-ajax.js'), 'ajax-events', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/ds-likes.js'), 'likes', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/upload.js'), 'upload', $version); 
    ds_theme_script_add(get_site_url('/sys/static/js/comments.js'), 'comments', $version); 

    do_event('ds_theme_scripts_init'); 
}

/**
* Добавляет перевод 
*/ 
function ds_script_translate_add($url, $uniquie, $version) 
{
    $translates = Registry::get('enqueue_script_translate'); 
    if (empty($translates)) {
        $translates = array();
    }
    
    if (!isset($translates[$uniquie])) {
        $translates[$uniquie] = array(
            'url' => $url, 
            'version' => $version, 
            'uniquie' => $uniquie, 
            'media' => $media, 
        );
        Registry::set('enqueue_script_translate', $translates); 
        return true; 
    }
    
    return false; 
}

/**
* Функция регистрирует файл стилей
* @return bolean
*/

function ds_theme_style_add($url = '', $uniquie = 'style', $version = '', $media = 'all') {
    $styles = Registry::get('enqueue_style'); 
    if (empty($styles)) {
        $styles = array();
    }
    
    if (!isset($styles[$uniquie])) {
        $styles[$uniquie] = array(
            'url' => $url, 
            'version' => $version, 
            'uniquie' => $uniquie, 
            'media' => $media, 
        );
        Registry::set('enqueue_style', $styles); 
        return true; 
    }
    
    return false; 
}

/**
* Удаляет все объявленные стили
*/
function ds_theme_styles_delete_all() {
    Registry::set('enqueue_style', array());
}

/**
* Функция удаляет файл стилей из вывода
* @return bolean
*/
function ds_theme_style_delete($uniquie = '') {
    $styles = Registry::get('enqueue_style'); 
    
    if (isset($styles[$uniquie])) {
        unset($styles[$uniquie]);
        if (empty($styles)) {
            $styles = array(); 
        }
        Registry::set('enqueue_style', $styles); 
        return true; 
    }
    
    return false; 
}

/**
* Вернет массив указанного стиля
* @return array 
*/
function ds_theme_get_style($uniquie = '') {
    $styles = Registry::get('enqueue_style'); 
    if (isset($styles[$uniquie])) {
        return $styles[$uniquie];
    }
    return array(); 
}

/**
* Вернет URI указанного стиля
* @return string 
*/
function ds_theme_get_style_uri($uniquie = '') {
    if ($style = ds_theme_get_style($uniquie)) {
        return $style['url']; 
    }
}

/**
* Выводит URI указанного стиля
* @display string 
*/
function ds_theme_style_uri($uniquie = '') {
    echo ds_theme_get_style_uri($uniquie); 
}

/**
* Вернет html код с указанным стилем
* @return string 
*/
function ds_theme_get_style_html($uniquie = '') {
    if ($style = ds_theme_get_style($uniquie)) {
        $css  = '<link rel="stylesheet"';
        $css .= ' id="css-' . $uniquie . '"';
        $css .= ' type="text/css"';
        $css .= ' href="' . $style['url'] . ($style['version'] ? '?version=' . $style['version'] : '') . '"';
        $css .= ' media="' . $style['media'] . '">'; 
        
        return $css; 
    }
}

/**
* Выводит html код с указанным стилем
* @display html 
*/
function ds_theme_style_html($uniquie = '') {
    echo ds_theme_get_style_html($uniquie); 
}

/**
* Вернет массив всех объявленных стилей
* @return array 
*/
function ds_theme_get_styles($uniquie = false) {
    $styles = Registry::get('enqueue_style'); 
    if ($styles) {
        $array = array(); 
        foreach($styles AS $key => $value) {
            if ($uniquie === true) {
                $array[$key] = $value; 
            } else {
                $array[] = $value; 
            }
        }
        return $array; 
    }
    return array(); 
}

/**
* Вернет html код всех объявленных стилей
* @return string 
*/
function ds_theme_get_styles_html() {
    $styles = Registry::get('enqueue_style'); 

    if ($styles) {
        $array = array(); 
        foreach($styles AS $key => $value) {
            $array[] = ds_theme_get_style_html($key); 
        }
        return implode("\n", $array) . "\n"; 
    }
}

/**
* Выводит html код всех объявленных стилей
* @display html 
*/
function ds_theme_styles_html() {
    echo ds_theme_get_styles_html();
}


/**
* Функция регистрирует файл javascript
* @return bolean
*/

function ds_theme_script_add($url = '', $uniquie = 'script', $version = '') {
    $scripts = ds_get('enqueue_script'); 
    if (empty($scripts)) {
        $scripts = array();
    }
    
    if (!isset($scripts[$uniquie])) {
        $scripts[$uniquie] = array(
            'url' => $url, 
            'version' => $version, 
            'uniquie' => $uniquie, 
        );
        ds_set('enqueue_script', $scripts); 
        return true; 
    }
    
    return false; 
}

/**
* Удаляет все объявленные скрипты
*/
function ds_theme_scripts_delete_all() {
    ds_set('enqueue_script', array());
}

/**
* Функция удаляет файл скрипта из вывода
* @return bolean
*/
function ds_theme_script_delete($uniquie = '') {
    $scripts = ds_get('enqueue_script'); 
    
    if (isset($scripts[$uniquie])) {
        unset($scripts[$uniquie]);
        if (empty($scripts)) {
            $scripts = array(); 
        }
        ds_set('enqueue_script', $scripts); 
        return true; 
    }
    
    return false; 
}

/**
* Вернет массив указанного javascript
* @return array 
*/
function ds_theme_get_script($uniquie = '') {
    $scripts = ds_get('enqueue_script'); 
    if (isset($scripts[$uniquie])) {
        return $scripts[$uniquie];
    }
    return array(); 
}

/**
* Вернет URI указанного javascript
* @return string 
*/
function ds_theme_get_script_uri($uniquie = '') {
    if ($script = ds_theme_get_script($uniquie)) {
        return $script['url']; 
    }
}

/**
* Выводит URI указанного javascript
* @display string 
*/
function ds_theme_script_uri($uniquie = '') {
    echo ds_theme_get_script_uri($uniquie); 
}

/**
* Вернет html код с указанным javascript
* @return string 
*/
function ds_theme_get_script_html($uniquie = '') {
    if ($script = ds_theme_get_script($uniquie)) {

        $javascript  = "";

        $translates = ds_get('enqueue_script_translate'); 
        if (isset($translates[$uniquie])) {

            $javascript .= "<script></script>";
        }

        $javascript .= "<script ";
        $javascript .= 'id="js-' . $uniquie . '" ';
        $javascript .= 'type="text/javascript" ';
        $javascript .= 'src="' . $script['url'] . ($script['version'] ? '?version=' . $script['version'] : '') . '"';
        $javascript .= '></script>'; 
        
        return $javascript; 
    }
}

/**
* Выводит html код с указанным javascript
* @display html 
*/
function ds_theme_script_html($uniquie = '') {
    echo ds_theme_get_script_html($uniquie); 
}

/**
* Вернет массив всех объявленных javascript
* @return array 
*/
function ds_theme_get_scripts($uniquie = false) {
    $scripts = ds_get('enqueue_script'); 
    if ($scripts) {
        $array = array(); 
        foreach($scripts AS $key => $value) {
            if ($uniquie === true) {
                $array[$key] = $value; 
            } else {
                $array[] = $value; 
            }
        }
        return $array; 
    }
    return array(); 
}

/**
* Вернет html код всех объявленных javascript
* @return string 
*/
function ds_theme_get_scripts_html() {
    $scripts = ds_get('enqueue_script'); 

    if ($scripts) {
        $array = array(); 
        foreach($scripts AS $key => $value) {
            $array[] = ds_theme_get_script_html($key); 
        }
        return implode("\n", $array) . "\n"; 
    }
}

/**
* Выводит html код всех объявленных javascript
* @display html 
*/
function ds_theme_scripts_html() {
    echo ds_theme_get_scripts_html();
}

function get_icon_html($str = '')
{
    if (preg_match('/fa\-[A-z\-]+/', $str)) {
        return '<i class="fa ' . $str . '"></i>'; 
    }

    return $str; 
}


/**
* Функция добавляет тему в список тем
* @return bolean
*/ 
function ds_theme_add($slug, $info) 
{
    $themes = get_themes();
    if (!isset($themes[$slug])) {
            $themes[$slug] = $info; 
            $themes[$slug]['active'] = '0'; 
            update_option('ds_themes', json_encode($themes, JSON_UNESCAPED_UNICODE), 'themes'); 
            return true;
    }

    return false; 
}

/**
* Функция удаляет тему из системы
* @return bolean
*/ 
function ds_theme_remove($slug) 
{
    $themes = get_themes();

    do_event('ds_theme_remove', $slug);
    
    if (isset($themes[$slug])) {
        do_event('ds_theme_' . $slug . '_remove', $slug); 

        if (is_dir(get_themes_directory() . '/' . $slug)) {
            delete_dir(get_themes_directory() . '/' . $slug); 
        }

        unset($themes[$slug]); 
        update_option('ds_themes', json_encode($themes, JSON_UNESCAPED_UNICODE), 'themes'); 
        return true;
    }

    return false; 
}


function get_themes() 
{
    if ($themes = ds_get('ds_themes')) {
        return $themes;
    }

    $json = get_option('ds_themes'); 

    if ($json) {
          $themes = json_decode($json, 1);
    }

    return is_array($themes) ? $themes : array(); 
}

function is_theme_active($slug) 
{
    $settings = get_settings(); 

    if ($settings['set_them'] == $slug) {
        return true; 
    }
    return false; 
}


function get_paged() 
{
    $page = 1;

    if (isset($_GET['page']))
        $page = intval($_GET['page']);

    return $page; 
}

// Выдает текущую страницу
function page( $k_page = 1 )
{
    $page = 1;
    if ( isset( $_GET['page'] ) ) {
        if ( $_GET['page'] == 'end' )
            $page = intval( $k_page );
        elseif ( is_numeric( $_GET['page'] ) )
            $page = intval( $_GET['page'] );
    }
    if ( $page < 1 )
        $page = 1;
    if ( $page > $k_page )
        $page = $k_page;
    return $page;
}

// Высчитывает количество страниц
function k_page( $k_post = 0, $k_p_str = 10 )
{
    if ( $k_post != 0 ) {
        $v_pages = ceil( $k_post / $k_p_str );
        return $v_pages;
    } else
        return 1;
}

// Вывод номеров страниц (только на первый взгляд кажется сложно ;))
function str( $link = '?', $k_page = 1, $page = 1 )
{
    if ( $page < 1 )
        $page = 1;
    echo '<ul class="nav pagination">';
    if ( $page != 1 )
        echo '<li class="nav-item"><a href="' . get_query_url(array('page' => 1)) . '" title="Первая страница">&lt;</a></li>';
    if ( $page != 1 )
        echo '<li class="nav-item"><a href="' . get_query_url(array('page' => 1)) . '" title="Страница №1">1</a></li>';
    else
        echo '<li class="nav-item nav-number">1</li>';
    for ( $ot = -3; $ot <= 3; $ot++ ) {
        if ( $page + $ot > 1 && $page + $ot < $k_page ) {
            if ( $ot == -3 && $page + $ot > 2 )
                echo '<li class="nav-item nav-dots">..</li>';
            if ( $ot != 0 )
                echo '<li class="nav-item"><a href="' . get_query_url(array('page' => $page + $ot)) . '" title="Страница №' . ( $page + $ot ) . '">' . ( $page + $ot ) . '</a></li>';
            else
                echo '<li class="nav-item nav-number">' . ( $page + $ot ) . '</li>';
            if ( $ot == 3 && $page + $ot < $k_page - 1 )
                echo '<li class="nav-item nav-dots">..</li>';
        }
    }
    if ( $page != $k_page )
        echo '<li class="nav-item"><a href="' . get_query_url(array('page' => $k_page)) . '" title="Страница №' . $k_page . '">' . $k_page . '</a></li>';
    elseif ( $k_page > 1 )
        echo '<li class="nav-item nav-number">' . $k_page . '</li>';
    if ( $page != $k_page )
        echo '<li class="nav-item"><a href="' . get_query_url(array('page' => $k_page)) . '" title="Последняя страница">&gt;</a></li>';
    echo '</ul>';
}

function get_template_post($data, $slug = 'default') 
{
    $post_classes = 'post'; 

    if (isset($data['post_classes'])) {
        if (is_array($data['post_classes'])) {
            $post_classes .= ' ' . join(' ', $data['post_classes']); 
        } else {
            $post_classes = ' ' . trim($data['post_classes']); 
        }
    }

    $tpl_header = array(); 
    if (is_array($data['header'])) {
        if (isset($data['header']['image'])) {
            $tpl_header[] = '<div class="post-header-image">' . $data['header']['image'] . '</div>';
        }

        if (isset($data['header']['content'])) {
            $tpl = array(); 
            foreach($data['header']['content'] AS $key => $item) {
                if ($key === 'post_time') {
                    $date = use_filters('ds_output_post_time', date('H:i', $item)); 
                    $tpl[] = '<div data-time="' . $item . '" title="' . ds_date('d F Y, H:i:s', $item) . '" class="' . $key . '">' . $date . '</div>';
                } else {
                    $tpl[] = '<div class="' . $key . '">' . $item . '</div>';
                } 
            }

            $tpl_header[] = '<div class="post-header-content">' . join('', $tpl) . '</div>';
        }

        if (!empty($data['header']['action'])) {
            $tpl = array(); 

            if (is_array($data['header']['action']['items'])) {
                foreach($data['header']['action']['items'] AS $key => $item) {
                    $tpl[] = '<a href="' . $item['url'] . '">' . (isset($item['icon']) ? '<i class="fa ' . $item['icon'] . '"></i> ' : '') . $item['title'] . '</a>';  
                }                
            }

            $toggle = '<i class="fa ' . (empty($data['header']['action']['icon']) ? 'fa-ellipsis-h' : $data['header']['action']['icon']) . '"></i>'; 
            $tpl_header[] = '<div class="post-header-action"><span class="post-action-toggle">' . $toggle . '</span><span class="post-action-nav">' . join('', $tpl) . '</span></div>';
        }
    }

    $post_attr = array(); 
    if (isset($data['post_attr'])) {
        foreach($data['post_attr'] AS $key => $value) {
            $post_attr[] = $key . '="' . $value . '"'; 
        }
    }

    $template = use_filters('ds_template_post', array(
        'post' => '<div class="' . $post_classes . '" ' . (isset($data['href']) ? 'data-href="' . $data['href'] . '"' : '') . ' ' . join(' ', $post_attr) . '>%s</div>', 
        'post-header' => '<div class="post-header">%s</div>', 
        'post-content' => '<div class="post-content">%s</div>', 
        'post-panel' => '<div class="post-panel">%s</div>', 
    )); 

    $post = array(); 
    if (!empty($tpl_header)) {
        $post[] = sprintf($template['post-header'], join('', $tpl_header)); 
    }

    if (!empty($data['content'])) {
        $post[] = sprintf($template['post-content'], $data['content']); 
    }

    if (!empty($data['panel'])) {
        $post[] = sprintf($template['post-panel'], $data['panel']); 
    }

    return sprintf($template['post'], join('', $post)); 
}