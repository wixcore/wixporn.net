<?php 

class Install
{
	private $config; 
	private $errors; 
	private $messages; 
	private $step; 

	public function __construct() 
	{
		$step = 1; 

		// Права на запись в корневой каталог
		if (!is_writable(ROOTPATH)) {
			$this->errors[] = __('Корневой каталог сайта не доступен для записи'); 
		}

		// Подключена ли база данных 
		if ($this->is_database()) {
			$step = 2; 
		}

		// Регистрация администратора
		if (defined('DB_CONNECT')) {
			if ($this->is_administrator()) {
				$step = 3; 
			}
		}

		$this->step = $step; 

		add_event('ds_install_setup', array($this, 'setup')); 
	}

	public function errors() {
		if (!empty($this->errors) && is_array($this->errors)) {
			foreach($this->errors AS $error) {
				echo '<div class="alert alert-error">' . $error . '</div>';
			}
		}
	}

	public function messages() {
		if (!empty($this->messages) && is_array($this->messages)) {
			foreach($this->messages AS $message) {
				echo '<div class="alert alert-success">' . $message . '</div>';
			}
		}
	}

	public function check_sql_install() 
	{
		$path_sql = ROOTPATH . '/sys/upgrade/db_install'; 

		if (!is_dir($path_sql)) {
			$this->errors[] = __('Папка с SQL файлами не найдена!'); 
			return false; 
		}

		$q = db::query('SHOW TABLES');

		while ($tables = $q->fetch_assoc()) {
			$table = array_values($tables); 
			$_ver_table[$table[0]] = 1;
		}

		$opdirtables = opendir($path_sql);

		$k_sql = 0; 
		$ok_sql = 0; 
		
		while ($filetables = readdir($opdirtables))
		{
			if (preg_match('#\.sql$#i',$filetables)) {
				$table_name = preg_replace('#\.sql$#i', null, $filetables);
				$sql = SQLParser::getQueriesFromFile($path_sql . '/' . $filetables);

				$continue = false; 
				if (isset($_ver_table[$table_name])) {
					$continue = true; 
				}

				for ($i = 0; $i < count($sql); $i++)
				{
					$k_sql++; 

					if ($continue === false && db::query($sql[$i])) {
						$ok_sql++; 
					}
				}
			}
		}

		$this->messages[] = __('Выполнено %s из %s запросов', $ok_sql, $k_sql); 

		closedir($opdirtables);

		/**
		* Удаление файлов обновлений
		*/ 
		$dirupdate = ROOTPATH . '/sys/upgrade/update'; 

		if (is_dir($dirupdate)) {
			$updiropen = opendir($dirupdate);

		    while ($filebase = readdir($updiropen)) {
		        if (preg_match('/update\-([0-9]+)_to_([0-9]+)\.php$/m', $filebase)) {
		            unlink($dirupdate . '/' . $filebase); 
		        }
		    }
		}

		closedir($updiropen);
	}

	public function get_data_config() 
	{
		$str = "<?php\n\n"; 

		$str .= "/*\r\n* MySQL параметры\r\n*/\r\n\n";
		$str .= "/* Имя пользователя базы данных */\r\n";
		$str .= "define( 'DB_USER', '" . $this->config['dbuser'] . "' );\r\n\n";

		$str .= "/* Имя базы данных */\r\n";
		$str .= "define( 'DB_NAME', '" . $this->config['dbname'] . "' );\r\n\n";

		$str .= "/* Пароль пользователя базы данных */\r\n";
		$str .= "define( 'DB_PASSWORD', '" . $this->config['dbpass'] . "' );\r\n\n";

		$str .= "/* Сервер базы данных */\r\n";
		$str .= "define( 'DB_HOST', '" . $this->config['dbhost'] . "' );\r\n\n";

		$str .= "\n/*@#salt#\n* Уникальные ключи аутентификации\n*\n* Чтобы аннулировать Cookie пользователей, просто измените эти значения!\n* Это заставит пользователей пройти авторизацию заново, под своим логином и паролем \n*/\r\n\n\n";

		$str .= "/* Соль для шифрования Cookie авторизации */\r\n";
		$str .= "define( 'SALT_COOKIE_USER', '" . base64_encode(passgen()) . "' );\r\n\n";

		$str .= "/* Соль для шифрования полей форм */\r\n";
		$str .= "define( 'SALT_FORMS_FIELDS', '" . base64_encode(passgen()) . "' );\r\n\n";

		$str .= "/*#salt#@*/"; 

		$str = use_filters('get_data_config', $str); 

		return $str; 
	}

	public function is_database() 
	{
		// Если конфиг создан значит база подключена
		if ( defined('DB_CONNECT') ) {
			return true; 
		}

		if (isset($_POST['step']) && $_POST['step'] == 'database') {
			$this->config = array(
				'dbhost' => $_POST['dbhost'], 
				'dbuser' => $_POST['dbuser'], 
				'dbpass' => $_POST['dbpass'], 
				'dbname' => $_POST['dbname'], 
			); 

			if (empty($this->config['dbhost'])) $this->config['dbhost'] = 'localhost';
			if (empty($this->config['dbuser'])) $this->errors[] = __('Не указан пользователь базы данных'); 
			if (empty($this->config['dbname'])) $this->errors[] = __('Не указано имя базы данных'); 

			if (!empty($this->errors)) {
				return false; 
			}

        	ob_start(); 
			db::connect($this->config['dbhost'], $this->config['dbuser'], $this->config['dbpass'], $this->config['dbname']); 

			if (mysqli_connect_errno()) {
				$this->errors[] = __('Не удалось подключиться к базе данных') . "<br />" . mysqli_connect_error(); 
			} else {
				$this->check_sql_install();

				if (!file_put_contents(ROOTPATH.'/config.php', $this->get_data_config())) {
					$this->errors[] = __('Не удалось создать конфигурационный файл'); 
					return false; 
				} else {
					$set = get_settings();  
					save_settings($set, $type = 'autoload'); 
				}
				return true;
			}
			ob_clean(); 
		}

		return false; 
	}

	public function is_administrator() 
	{
		$user_admin = db::count("SELECT COUNT(*) FROM user WHERE level = 4 AND group_access = 15"); 

		if ($user_admin) {
			return true; 
		}

		if (isset($_POST['step']) && $_POST['step'] == 'user') {
			$user = array(); 
			$user['nick'] = trim($_POST['user_login']); 
			$user['pass'] = shif(trim($_POST['user_password'])); 
			$user['email'] = trim($_POST['user_email']); 
			$user['level'] = 4; 
			$user['group_access'] = 15; 
			$user['date_reg'] = time(); 
			$user['date_last'] = time(); 
			$user['pol'] = 1; 

			if (!validate_login($user['nick'])) {
				$this->errors[] = __('В нике присутствуют запрещенные символы'); 
			} elseif (db::count("SELECT COUNT(*) FROM `user` WHERE `nick` = '" . my_esc($user['nick']) . "' LIMIT 1")) {
				$this->errors[] = __('Выбранный ник уже занят другим пользователем'); 
			} elseif (strlen2($user['nick']) < 2) {
				$this->errors[] = __('Ник короче 2-х символов');
			} elseif (strlen2($user['nick']) > 32) {
				$this->errors[] = __('Ник длиннее 32-ти символов');
			} elseif (preg_match("/([a-z]+)/ui", $user['nick']) && preg_match("/([а-я]+)/ui", $user['nick'])) {
				$this->errors[] = __('Разрешается использовать символы только русского или только английского алфавита');
			}

			if (!validate_email($user['email'])) {
				$this->errors[] = __('E-Mail указан не верно');
			}
			
			if (strlen2($_POST['user_password']) < 6) {
				$this->errors[] = __('Пароль короче 6-ти символов');
			} elseif (strlen2($_POST['user_password']) > 20) {
				$this->errors[] = __('Пароль длиннее 20-ти символов');
			} elseif ($_POST['user_password'] !== $_POST['user_confirm']) {
				$this->errors[] = __('Пароли не совпадают');
			}

			if (empty($this->errors)) {
				db::insert('user', $user); 
				$user['id'] = db::insert_id(); 

				if (!empty($user['id'])) {
					$_SESSION['id_user'] = $user['id'];
					setcookie('id_user', $user['id'], time() + 60 * 60 * 24 * 365);
					setcookie('pass', cookie_encrypt(trim($_POST['user_password']), $user['id']), time() + 60 * 60 * 24 * 365);	
					return true; 				
				} else {
					$this->errors[] = __('Не удалось создать учетную запись администратора');
				}
			}
		}

		return false; 
	}

	public function get_value($key, $value) 
	{
		return $value; 
	}

	public function setup() 
	{
		if ($this->step == 3) {
			echo '<div class="wrap-installed">'; 
			echo '<div class="cms-installed">';
			echo __('Установка завершена!');
			echo '</div>';

			echo '<a class="btn-installed" href="' . get_site_url('/') . '">' . __('Перейти на сайт') . '</a>';
			echo '</div>'; 
		}

		else {
			echo '<form action="?" method="POST">';
			$form = $this->get_form(); 

			if (isset($form['title'])) {
				echo '<div class="title">' . $form['title'] . '</div>';
			}

			$this->messages(); 
			$this->errors(); 

			if (!empty($form['items'])) {
				echo '<div class="form-group">';
				foreach($form['items'] AS $input)
				{
					if (!isset($input['type']))
						$input['type'] = 'text';  

					if (!isset($input['value']))
						$input['value'] = '';  

					$input['value'] = $this->get_value($input['key'], $input['value']);  

					if (isset($input['title']) && $input['title']) {
						echo '<div class="form-title">'  .$input['title'] . '</div>'; 
					}

					if (preg_match('/^(text|hidden|password|email|tel)$/', $input['type'])) {
						echo '<div class="form-input form-type-' . $input['type'] . '"><input type="'  .$input['type'] . '" name="'  . $input['key'] . '" value="' . $input['value'] . '" /></div>'; 
					}

					if (isset($input['description']) && $input['description']) {
						echo '<div class="form-description">'  .$input['description'] . '</div>'; 
					}
				}
				
				echo '<div class="form-buttons"><button class="btn btn-next" type="submit">' . $form['submit'] . '</button></div>';
				echo '</div>'; 			
			}

			echo '</form>';			
		}

	}

	public function get_form() 
	{
		$inputs[1]['title'] = __('Заполните информацию о подключении к базе данных, если у вас возникли трудности на этом этапе, обратитесь к вашему хостинг-провайдеру.'); 
		$inputs[1]['submit'] = __('Продолжить'); 
		$inputs[1]['items'] = array(
			array(
				'title' => __('Имя базы данных'), 
				'key' => 'dbname', 
				'description' => __('Укажите название базы данных'), 
			), 
			array(
				'title' => __('Имя пользователя'), 
				'key' => 'dbuser', 
			), 
			array(
				'title' => __('Пароль'), 
				'key' => 'dbpass', 
				'type' => 'password', 
			), 
			array(
				'title' => __('Сервер базы данных'), 
				'key' => 'dbhost', 
				'value' => 'localhost', 
			), 
			array(
				'key' => 'step', 
				'type' => 'hidden', 
				'value' => 'database', 
			), 
		); 

		$inputs[2]['title'] = __('Регистрация администратора'); 
		$inputs[2]['submit'] = __('Зарегистрироваться'); 
		$inputs[2]['items'] = array(
			array(
				'title' => __('Логин'), 
				'key' => 'user_login', 
				'type' => 'text', 
				'value' => 'Admin', 
			), 
			array(
				'title' => __('E-Mail администратора'), 
				'key' => 'user_email', 
				'type' => 'text', 
				'value' => '', 
			), 
			array(
				'title' => __('Пароль'), 
				'key' => 'user_password', 
				'type' => 'password', 
				'value' => '', 
			), 
			array(
				'title' => __('Подтвердите пароль'), 
				'key' => 'user_confirm', 
				'type' => 'password', 
				'value' => '', 
			), 
			array(
				'key' => 'step', 
				'type' => 'hidden', 
				'value' => 'user', 
			), 
		); 

		return $inputs[$this->step]; 
	}

	public function get_header() 
	{
		/*echo '<?xml version="1.0" encoding="utf-8"?>';*/
		echo '<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
			<head>	
			<meta charset="UTF-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>' . __('Установка CMS-Social') . '</title>
			<link rel="stylesheet" href="' . get_site_url('/sys/static/css/install.css') . '" type="text/css" />
			<link rel="shortcut icon" href="' . get_site_url('/sys/static/images/favicon.ico') . '" />
			<script id="jquery" type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
			</head><body><div class="document">'; 

		echo '<div class="logo"><a href="https://cms-social.ru" target="_blank"><img src="' . get_site_url('/sys/static/images/logo.png') . '" alt="Logo" /></a></div>';
	}

	public function get_footer() 
	{ 
		echo '</div><footer>
			' . __('Генерация страницы: %sсек.', get_page_gen($per = 3)) . '
			</footer></body></html>';
	}
}