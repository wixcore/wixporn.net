<?PHP
define( 'H', dirname(__FILE__) . '/' );
session_name( 'SESS' );
session_start();
require H . 'sys/inc/captcha.php';
$_SESSION['captcha'] = '';
for ( $i = 0; $i < 5; $i++ ) {
    $_SESSION['captcha'] .= mt_rand( 0, 9 );
}
$captcha = new captcha( $_SESSION['captcha'] );
$captcha->create();
$captcha->colorize();
$captcha->output();