<?
echo "Версия CMS-Social v.".get_version()."<br />\n";

list ($php_ver1,$php_ver2,$php_ver3)=explode('.', strtok(strtok(phpversion(),'-'),' '), 3);

if ($php_ver1 >= 7) {
	echo "<span class='on'>Версия PHP: $php_ver1.$php_ver2.$php_ver3 (OK)</span><br />\n";
} else {
	echo "<span class='off'>Версия PHP: $php_ver1.$php_ver2.$php_ver3</span><br />\n";
	$err[]="Тестирование на версии php $php_ver1.$php_ver2.$php_ver3 не осуществялось";
}

if (function_exists('set_time_limit'))
	echo "<span class='on'>set_time_limit: OK</span><br />\n";
else echo "<span class='on'>set_time_limit: Запрещено</span><br />\n";

if (ini_get('session.use_trans_sid') == true) {
	echo "<span class='on'>session.use_trans_sid: OK</span><br />\n";
} else {
	echo "<span class='off'>session.use_trans_sid: Нет</span><br />\n";
	$err[] = 'Будет теряться сессия на браузерах без поддержки COOKIE';
	$err[] = 'Добавьте в корневой .htaccess строку <b>php_value session.use_trans_sid 1</b>';
}

if (ini_get('magic_quotes_gpc') == 0) {
	echo "<span class='on'>magic_quotes_gpc: 0 (OK)</span><br />\n";
} else {
	echo "<span class='off'>magic_quotes_gpc: Включено</span><br />\n";
	$err[]='Включено экранирование кавычек';
	$err[]='Добавьте в корневой .htaccess строку <b>php_value magic_quotes_gpc 0</b>';
}

if (ini_get('arg_separator.output') == '&amp;') {
	echo "<span class='on'>arg_separator.output: &amp;amp; (OK)</span><br />\n";
} else {
	echo "<span class='off'>arg_separator.output: ".output_text(ini_get('arg_separator.output'))."</span><br />\n";
	$err[] = 'Возможно появление ошибки xml';
	$err[] = 'Добавьте в корневой .htaccess строку <b>php_value arg_separator.output &amp;amp;</b>';
}

$mod_rewrite = @file_get_contents(get_site_url('/ds-ajax/?action=mod_rewrite_test')); 

if ($mod_rewrite == 'OK') {
	echo "<span class='on'>mod_rewrite: OK</span><br />\n";
} else {
	echo "<span class='off'>mod_rewrite: Нет</span><br />\n";
	$err[]='Необходима поддержка mod_rewrite';
}

if (function_exists('imagecreatefromstring') && function_exists('gd_info'))
{
	$gdinfo=gd_info();
	echo "<span class='on'>GD: ".$gdinfo['GD Version']." OK</span><br />\n";
} else {
	echo "<span class='off'>GD: Нет</span><br />\n";
	$err[]='GD необходима для корректной работы движка';
}

if (function_exists('mysqli_info'))
{
	echo "<span class='on'>MySQLi: OK</span><br />\n";
} else {
	echo "<span class='off'>MySQLi: Нет</span><br />\n";
	$err[]='Без MySQLi работа не возможна';
}

if (function_exists('iconv')) {
	echo "<span class='on'>Iconv: OK</span><br />\n";
} else {
	echo "<span class='off'>Iconv: Нет</span><br />\n";
	$err[]='Без Iconv работа не возможна';
}

if (class_exists('ffmpeg_movie'))
{
	echo "<span class='on'>FFmpeg: OK</span><br />\n";
} else {
	echo "<span class='on'>FFmpeg: Нет</span><br />\n";
	echo "* Без FFmpeg автоматическое создание скриношотов к видео недоступно<br />\n";
}

if (ini_get('register_globals')==false)
{
	echo "<span class='on'>register_globals off: OK</span><br />\n";
} else {
	echo "<span class='off'>register_globals on: !!!</span><br />\n";
	$err[]='register_globals включен. Грубое нарушение безопасности';
}

if (function_exists('openssl_encrypt')) {
	echo "<span class='on'>Шифрование COOKIE: OK</span><br />\n";
} elseif (function_exists('mcrypt_cbc')) {
	echo "<span class='on'>Шифрование COOKIE: OK</span><br />\n";
} else {
	echo "<span class='on'>Шифрование COOKIE: нет</span><br />\n";
	echo "* openssl и mcrypt не доступны<br />\n";
}
