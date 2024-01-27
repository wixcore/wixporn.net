<?php

$show_all = true;
only_unreg();

do_event('ds_auth_init'); 

if ( isset( $_GET['pass'] ) && $_GET['pass'] = 'ok' )
    $_SESSION['message'] = __('Пароль отправлен вам на E-Mail');

if ( $set['guest_select'] == '1' )
    $_SESSION['message'] = __("Доступ к сайту разрешен только авторизованым пользователям");

if (isset($_POST['auth'])) {
	$login = use_filters('ds_esc_input', $_POST['login']); 
	$password = use_filters('ds_esc_input', $_POST['password']); 

	$fields_login = use_filters('ds_auth_login_fields', array(
		'id', 'email', 'nick'
	)); 

	$where_login = array(); 
	foreach($fields_login AS $field) {
		$where_login[] = "`" . $field . "` = '" . $login . "'";
	}

	if (!$user = db::fetch("SELECT `id` FROM `user` WHERE (" . join(' OR ', $where_login) . ") AND `pass` = '" . shif($password) . "' LIMIT 1", ARRAY_A)) {
        add_error(__('Неправильный логин или пароль'));
	}

	if (!is_errors() && isset($user['id'])) {
	    $_SESSION['id_user'] = $user['id'];
	    $user = get_user($user['id']);

	    if (isset($_POST['remember_me']) && $_POST['remember_me']) {
	        setcookie('id_user', $user['id'], time() + 60 * 60 * 24 * 365);
	        setcookie('pass', cookie_encrypt($password), time() + 60 * 60 * 24 * 365);
	    }

	    do_event('user_authorize_success', $user); 
	    ds_redirect(get_site_url('/')); 
	}
}


$set['title'] = __('Авторизация');

get_header(); 

?>
<form class="ds-auth-form" action="?" method="POST">
	<?php do_event('ds_auth_fields_before'); ?>
	<div class="ds-auth-input ds-auth-input-login">
		<label for="login"><?php echo __('Логин или E-Mail'); ?></label>
		<input id="login" type="text" name="login" />
	</div>
	<div class="ds-auth-input ds-auth-input-password">
		<label for="password"><?php echo __('Пароль'); ?></label>
		<input id="password" type="password" name="password" />
	</div>
	<div class="ds-auth-input ds-auth-input-remember">
		<input id="remember_me" type="checkbox" name="remember_me" checked="" />
		<label for="remember_me"><?php echo __('Запомнить меня'); ?></label>
	</div>
	<?php do_event('ds_auth_fields_after'); ?>
	<div class="ds-auth-submit">
		<button class="button" type="submit"><?php echo __('Войти'); ?></button> <a href="<?php echo get_site_url('/pass.php'); ?>"><?php echo __('Забыли пароль?'); ?></a>
	</div>
	<?php do_event('ds_auth_form_end'); ?>

	<input type="hidden" name="auth" value="1" />
</form>
<?

get_footer();  