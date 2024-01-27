<?php 

/** 
* Установка событий по умолчанию 
* Крайне не рекомендуется изменять этот файл
*/ 

// Загрузка опций autoload
add_event('ds_mysql_init', 'ds_options_load', 10); 

// Панель навигации авторизаванного
add_event('user_panel', 'get_user_panel', 10); 

// Обработка зарегистрированных request запросов
add_event('ds_request_init', 'ds_rewrite_rule_start', 10); 

// Вывод заголовка в шаблоне
add_event('ds_output_title', 'ds_document_title', 10); 

// Инициализация стилей для шаблонов темы
add_event('init_head_theme', 'ds_theme_styles_init', 10); 

// Инициализация javascript для шаблонов темы
add_event('init_foot_theme', 'ds_theme_scripts_init', 10); 

// Фильтр эмоджи смайлов
add_filter('ds_output_text', 'ds_filter_emoji', 11); 

// Обновление информации о пользователе
add_event('ds_init', 'update_user_information', 10); 

// Отправка комментариев
add_event('ds_init', 'ds_comments_init', 10); 

// Инициализация файлов пользователя
add_event('ds_init', 'handle_files_init', 10); 

// Инициализация ленты пользователя
add_event('ds_init', 'handle_feeds_init', 10); 

// Инициализация обсуждений пользователя
add_event('ds_init', 'handle_discussions_init', 10); 

// Инициализация лент пользователя
add_event('ds_init', 'handle_lenta_init', 10); 

// Инициализация лайков 
add_event('ds_init', 'handle_likes_init', 10); 

// Инициализация друзей
add_event('ds_init', 'handle_friends_init', 10); 

// Установщик системы
add_event('ds_session_init', 'ds_check_installed', 10); 

// Меню админки
add_event('ds_admin_init', 'ds_admin_menu_load', 10); 

// Проверка обновления плагинов
add_event('ds_admin_init', 'check_plugins_update', 10); 

// Проверка обновления ядра
add_event('ds_admin_init', 'check_core_update', 10); 

// Настройки админки
add_event('ds_admin_init', 'ds_admin_settings_load', 11); 

// Вывод странички профиля
add_event('ds_profile', 'ds_profile_view', 10); 

// Регистрация стандартных AJAX запросов
add_event('pre_init_ajax', 'ds_default_ajax', 10); 

// Инициализация страницы настроек пользователя
add_event('ds_user_init', 'default_user_settings', 10); 

// Регистрируем виджеты и области виджетов
add_event('ds_init', 'ds_widgets_init'); 

// Регистрируем уведомления
add_event('ds_init', 'ds_notify_init'); 

// Инициализируем поиск по сайту
add_event('ds_init', 'ds_seacrh_init'); 

// Чистка вложений при удалении файла
add_event('ds_file_deleted', 'clear_object_attachments', 1, 10); 

// Инициализация прав доступа пользователя
add_event('ds_plugins_loaded', 'setup_user_access'); 

ds_rewrite_rule('search\/', H.'pages/search.php');
ds_rewrite_rule('exit\.php', 'ds_user_logout'); 
ds_rewrite_rule('(.*)\.php', H.'pages/$1.php');
ds_rewrite_rule('feed\/', H.'user/feed/index.php');
ds_rewrite_rule('feed\/([0-9]+)', H.'user/feed/comments.php', 'post_id=$1');

ds_rewrite_rule('ds-ajax\/', ROOTPATH . '/sys/inc/ajax.php'); 
