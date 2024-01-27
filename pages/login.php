<?php
$show_all   = true;
$input_page = true;

only_unreg();
if ( isset( $_GET['id'] ) && isset( $_GET['pass'] ) ) {
    if ( db::count("SELECT COUNT(*) FROM `user` WHERE `id` = '" . intval( $_GET['id'] ) . "' AND `pass` = '" . shif( $_GET['pass'] ) . "' LIMIT 1") == 1 ) {
        $user = get_user( $_GET['id'] );
        $_SESSION['id_user'] = $user['id'];
        db::query("UPDATE `user` SET `date_aut` = " . time() . " WHERE `id` = '$user[id]' LIMIT 1");
        db::query("UPDATE `user` SET `date_last` = " . time() . " WHERE `id` = '$user[id]' LIMIT 1");
        db::query("INSERT INTO `user_log` (`id_user`, `time`, `ua`, `ip`, `method`) values('$user[id]', '$time', '$user[ua]' , '$user[ip]', '0')");
    } else
        $_SESSION['err'] = 'Неправильный логин или пароль';
} elseif ( isset( $_POST['nick'] ) && isset( $_POST['pass'] ) ) {
    if ( db::count("SELECT COUNT(*) FROM `user` WHERE `nick` = '" . my_esc( $_POST['nick'] ) . "' AND `pass` = '" . shif( $_POST['pass'] ) . "' LIMIT 1") ) {
        $user = db::fetch("SELECT `id` FROM `user` WHERE `nick` = '" . my_esc( $_POST['nick'] ) . "' AND `pass` = '" . shif( $_POST['pass'] ) . "' LIMIT 1", ARRAY_A); 

        $_SESSION['id_user'] = $user['id'];
        $user = get_user( $user['id'] );

        if ( isset( $_POST['aut_save'] ) && $_POST['aut_save'] ) {
            setcookie( 'id_user', $user['id'], time() + 60 * 60 * 24 * 365 );
            setcookie( 'pass', cookie_encrypt( $_POST['pass'] ), time() + 60 * 60 * 24 * 365 );
        }

        db::query("UPDATE `user` SET `date_aut` = '$time', `date_last` = '$time' WHERE `id` = '$user[id]' LIMIT 1");
        db::query("INSERT INTO `user_log` (`id_user`, `time`, `ua`, `ip`, `method`) values('$user[id]', '$time', '$user[ua]' , '$user[ip]', '1')");
    } else
        $_SESSION['err'] = 'Неправильный логин или пароль';
} elseif ( isset( $_COOKIE['id_user'] ) && isset( $_COOKIE['pass'] ) && $_COOKIE['id_user'] && $_COOKIE['pass'] ) {
    if ( db::count("SELECT COUNT(*) FROM `user` WHERE `id` = " . intval( $_COOKIE['id_user'] ) . " AND `pass` = '" . shif(cookie_decrypt($_COOKIE['pass'])) . "' LIMIT 1") == 1 ) {
        $user = get_user( $_COOKIE['id_user'] );
        $_SESSION['id_user'] = $user['id'];
        db::query("UPDATE `user` SET `date_aut` = '$time', `date_last` = '$time' WHERE `id` = '$user[id]' LIMIT 1");
        $user['type_input'] = 'cookie';
    } else {
        $_SESSION['err'] = 'Ошибка авторизации по COOKIE';
        setcookie( 'id_user' );
        setcookie( 'pass' );
    }
} else
    $_SESSION['err'] = 'Ошибка авторизации';
if ( !isset( $user ) ) {
    header( 'Location: /aut.php' );
    exit;
}
if ( isset( $ip2['add'] ) )
    db::query("UPDATE `user` SET `ip` = " . ip2long( $ip2['add'] ) . " WHERE `id` = '$user[id]' LIMIT 1");
else
    db::query("UPDATE `user` SET `ip` = null WHERE `id` = '$user[id]' LIMIT 1");
if ( isset( $ip2['cl'] ) )
    db::query("UPDATE `user` SET `ip_cl` = " . ip2long( $ip2['cl'] ) . " WHERE `id` = '$user[id]' LIMIT 1");
else
    db::query("UPDATE `user` SET `ip_cl` = null WHERE `id` = '$user[id]' LIMIT 1");
if ( isset( $ip2['xff'] ) )
    db::query("UPDATE `user` SET `ip_xff` = " . ip2long( $ip2['xff'] ) . " WHERE `id` = '$user[id]' LIMIT 1");
else
    db::query("UPDATE `user` SET `ip_xff` = null WHERE `id` = '$user[id]' LIMIT 1");
if ( $ua )
    db::query("UPDATE `user` SET `ua` = '" . my_esc( $ua ) . "' WHERE `id` = '$user[id]' LIMIT 1");
db::query("UPDATE `user` SET `sess` = '$sess' WHERE `id` = '$user[id]' LIMIT 1");
db::query("UPDATE `user` SET `browser` = '" . ( $webbrowser == true ? "wap" : "web") . "' WHERE `id` = '$user[id]' LIMIT 1" );

header('Location: ' . get_site_url('/my_aut.php'));