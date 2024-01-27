<?php

$show_all = true;
only_unreg();

if ($set['reg_select'] == 'close') {
    add_error(__('Регистрация временно приостановлена')); 
}

do_event('ds_reg_init'); 

/**
* Подтверждение E-Mail по ссылке
*/ 
if (isset($_GET['id']) && isset($_GET['hash'])) {
    $hash = $_GET['hash']; 
    $ank = get_user($_GET['id']);

    if (!empty($ank['activation']) && md5($ank['activation'] . ':' . SALT_FORMS_FIELDS) === $_GET['hash']) {
        db::update('user', array('activation' => null), array('id' => $ank['id'])); 
        delete_user_meta($user['id'], '__reg_sent_email'); 

        $_SESSION['message'] = __('Вы успешно подтвердили свой E-Mail');
        $redirect_url = use_filters('ds_email_confirm_redirect', get_user_url($ank)); 

        ds_redirect($redirect_url); 
    } else {
        add_error(__('Ошибка при подтверждении E-Mail')); 
    }
}

if (isset($_POST['activate_resent']) && isset($_SESSION['id_user'])) {
    $ank = get_user($_SESSION['id_user']); 
    $time_sent = get_user_meta($ank['id'], '__reg_sent_email'); 
    $time_resent = (60 * 1) - (time() - $time_sent); 

    if ($time_resent > 0) {
        add_error(__('Время для повторной отправки кода подтверждения, еще не наступило.')); 
    }

    if (!is_errors()) {
        $content = __('Регистрация на сайте %s (повторная отправка кода)', $_SERVER['HTTP_HOST'], get_current_url()) . "\n\n"; 
        $content .= __('Код подтверждения: ' . $ank['activation']) . "\n\n"; 
        $content .= __('Для завершения регистрации введите код, или перейдите по этой ссылке: %s', get_query_url(array(
            'id' => $ank['id'], 
            'hash' => md5($ank['activation'] . ':' . SALT_FORMS_FIELDS), 
        ))); 

        $mail_title = use_filters('ds_reg_mail_title', __('Регистрация на %s', $_SERVER['HTTP_HOST'])); 
        $mail_message = use_filters('ds_reg_mail_message', $content); 
        $mail_headers = use_filters('ds_reg_mail_headers', array()); 
        $mail_attachments = use_filters('ds_reg_mail_attachments', array());  

        ds_mail($ank['email'], $mail_title, $mail_message, $mail_headers, $mail_attachments);
        update_user_meta($ank['id'], '__reg_sent_email', time()); 
        $_SESSION['message'] = __('На ваш E-Mail %s был повторно отправлен код, для подтверждения регистрации.', $ank['email']); 
        ds_redirect('?action=activation'); 
    }
}

if (isset($_POST['activate_submit'])) {
    $ank = get_user($_SESSION['id_user']); 

    if (!empty($ank['id'])) {
        $time_sent = get_user_meta($ank['id'], '__reg_sent_email'); 

        if ($_POST['hash'] != md5($time_sent . ':' . SALT_FORMS_FIELDS)) {
            add_error(__('Подозрительный запрос, повторите попытку позже.')); 
        } elseif ($ank['activation'] != trim($_POST['activation'])) {
            add_error(__('Неверный код активации')); 
        }

        if (!is_errors()) {
            delete_user_meta($ank['id'], '__reg_sent_email'); 
            db::update('user', array(
                'activation' => '', 
            ), array(
                'id' => $ank['id'],
            )); 

            $_SESSION['message'] = __('Вы успешно активировали свой аккаунт'); 
            ds_redirect(get_user_url($ank)); 
        }
    }
}

$reg = array(); 
if (isset($_POST['reg'])) {
    $reg = use_filters('ds_user_reg_data', array(
        'nick' => trim($_POST['login']), 
        'email' => (isset($_POST['email']) ? trim($_POST['email']) : ''), 
        'pol' => ($_POST['gender'] == 1 ? 1 : 0), 
        'password' => trim($_POST['password']), 
        'confirm' => $_POST['confirm'], 
    ), $_POST); 

    if (strlen2($reg['nick']) < 3) {
        add_error(__('Логин слишком короткий')); 
    } elseif (strlen2($reg['nick']) > 32) {
        add_error(__('Логин слишком длинный')); 
    } elseif (!validate_login($reg['nick'])) {
        add_error(__('Неправильный формат логина')); 
    } elseif (user_exists($reg['nick']) !== false) {
        add_error(__('Логин %s уже используется', text($reg['nick']))); 
    }

    if ($set['reg_select'] == 'open_mail') {
        if (empty($reg['email'])) {
            add_error(__('Вы не указали E-Mail адрес')); 
        }    
    }

    if (use_filters('ds_reg_field_submit', true)) {
        if ($_SESSION['captcha'] != $_POST['captcha']) {
            add_error(__('Неправильный код с картинки')); 
        }        
    }

    if ($reg['email']) {
        if (!validate_email($reg['email'])) {
            add_error(__('Проверьте правильность E-Mail адреса')); 
        } elseif (user_exists($reg['email'], 'email') !== false) {
            add_error(__('Этот E-Mail уже привязан к другому аккаунту')); 
        }  
    }

    if (empty($reg['password']) || strlen2($reg['password']) < 6 || strlen2($reg['password']) > 18) {
        add_error(__('Пароль должен содержать от %s до %s символов', 6, 18)); 
    } elseif ($reg['password'] !== $reg['confirm']) {
        add_error(__('Пароли не совпадают')); 
    }
    
    $activation = ($set['reg_select'] == 'open_mail' ? mt_rand(1000, 9999) : '');

    if (!is_errors()) {
        db::insert('user', use_filters('ds_user_reg_insert', array(
            'nick' => $reg['nick'], 
            'email' => $reg['email'], 
            'pass' => shif($reg['password']), 
            'pol' => $reg['pol'], 
            'activation' => $activation, 
            'date_reg' => time(), 
            'date_last' => time(), 
        ))); 

        $user_id = db::insert_id(); 
        $user = get_user($user_id); 

        if (isset($user['id'])) {
            $_SESSION['id_user'] = $user['id'];
            setcookie('id_user', $user['id'], time() + 60 * 60 * 24 * 365);
            setcookie('pass', cookie_encrypt($reg['password']), time() + 60 * 60 * 24 * 365);

            if ($reg['email']) { 
                $content = __('Регистрация на сайте %s', $_SERVER['HTTP_HOST'], get_current_url()) . "\n\n"; 
                $content .= __('Данные для входа:') . "\n"; 
                $content .= __('Логин: %s', $reg['nick']) . "\n"; 
                $content .= __('Пароль: %s', $reg['password']) . "\n\n"; 

                if ($set['reg_select'] == 'open_mail') {
                    $content .= __('Код подтверждения: ' . $activation) . "\n\n"; 
                    $content .= __('Для завершения регистрации введите код, или перейдите по этой ссылке: %s', get_query_url(array(
                        'id' => $user_id, 
                        'hash' => md5($activation . ':' . SALT_FORMS_FIELDS), 
                    )));                     
                }

                $mail_title = use_filters('ds_reg_mail_title', __('Регистрация на %s', $_SERVER['HTTP_HOST'])); 
                $mail_message = use_filters('ds_reg_mail_message', $content); 
                $mail_headers = use_filters('ds_reg_mail_headers', array()); 
                $mail_attachments = use_filters('ds_reg_mail_attachments', array()); 

                ds_mail($reg['email'], $mail_title, $mail_message, $mail_headers, $mail_attachments);
                update_user_meta($user['id'], '__reg_sent_email', time()); 
            }
        }

        do_event('ds_user_reg_success', $user); 

        $_SESSION['message'] = __('Вы успешно зарегистрировались'); 
        ds_redirect(use_filters('ds_user_reg_redirect', get_user_url($user_id))); 
    }
}

$set['title'] = __('Регистрация');
get_header(); 

if ($set['reg_select'] == 'close') {
    ?>
    <div class="registration-suspend">
        <?php echo __('Регистрация временно приостановлена'); ?>
    </div>
    <?php
}

elseif (isset($_SESSION['id_user']) && isset($_GET['action']) && $_GET['action'] == 'activation') { 
$ank = get_user($_SESSION['id_user']); 
$time_sent = get_user_meta($ank['id'], '__reg_sent_email'); 
$time_resent = (60 * 5) - (time() - $time_sent); 
?>
<form class="ds-reg-form" action="?action=activation" method="POST">
    <div class="ds-reg-input ds-reg-input-activation">
        <label for="activation"><?php echo __('Код активации'); ?></label>
        <input id="activation" type="text" name="activation" value="" />
        <div class="ds-reg-description">
            <?php echo __('На ваш E-Mail %s было отправлено сообщение с кодом подтверждения, пожалуйста введите его в эту форму.', '<b>' . $ank['email'] . '</b>'); ?>
        </div>
    </div>
    
    <div class="ds-reg-input ds-reg-input-text">
        <?php if ($time_resent > 0) { ?>
            <?php echo __('Повторная отправка кода подтверждения, может быть через %s', get_time_ago($time_resent)); ?>
        <?php } else { ?>
            <button class="button" name="activate_resent" type="submit"><?php echo __('Отправить код повторно'); ?></button> 
        <?php } ?>
    </div>

    <div class="ds-reg-submit">
        <button class="button" name="activate_submit" type="submit"><?php echo __('Подтвердить'); ?></button> 
    </div>

    <input type="hidden" name="hash" value="<?php echo md5($time_sent . ':' . SALT_FORMS_FIELDS); ?>" />
</form>
<?php } else { ?>
<form class="ds-reg-form" action="?" method="POST">
    <?php do_event('ds_reg_fields_before', $reg); ?>
    
    <?php if (use_filters('ds_reg_field_login', true)) : ?>
    <div class="ds-reg-input ds-reg-input-login">
        <label for="login"><?php echo __('Логин [%s]', 'A-z0-9-_'); ?></label>
        <input id="login" type="text" name="login" value="<?php echo (isset($reg['nick']) ? text($reg['nick']) : ''); ?>" />
    </div>
    <?php endif; ?>

    <?php if (use_filters('ds_reg_field_email', true)) : ?>
    <div class="ds-reg-input ds-reg-input-email">
        <label for="email"><?php echo __('E-Mail адрес'); ?></label>
        <input id="email" type="email" name="email" value="<?php echo (isset($reg['email']) ? text($reg['email']) : ''); ?>" />
    </div>
    <?php endif; ?>

    <?php if (use_filters('ds_reg_field_password', true)) : ?>
    <div class="ds-reg-input ds-reg-input-password">
        <label for="password"><?php echo __('Пароль'); ?></label>
        <input id="password" type="password" name="password" value="<?php echo (isset($reg['password']) ? text($reg['password']) : ''); ?>" />
    </div>
    <?php endif; ?>

    <?php if (use_filters('ds_reg_field_confirm', true)) : ?>
    <div class="ds-reg-input ds-reg-input-confirm">
        <label for="confirm"><?php echo __('Повторите пароль'); ?></label>
        <input id="confirm" type="password" name="confirm" value="<?php echo (isset($reg['confirm']) ? text($reg['confirm']) : ''); ?>" />
    </div>
    <?php endif; ?>
    
    <?php if (use_filters('ds_reg_field_gender', true)) : ?>
    <div class="ds-reg-input ds-reg-input-gender">
        <div><input id="gender_man" type="radio" name="gender" value="1" <?php echo (isset($reg['pol']) && $reg['pol'] == 1 ? 'checked' : (!isset($reg['pol']) ? 'checked' : '')); ?> /><label for="gender_man"><?php echo __('Мужской'); ?></label></div>
        <div><input id="gender_woman" type="radio" name="gender" value="0" <?php echo (isset($reg['pol']) && $reg['pol'] == 0 ? 'checked' : ''); ?> /><label for="gender_woman"><?php echo __('Женский'); ?></label></div>
    </div>
    <?php endif; ?>

    <?php if (use_filters('ds_reg_field_captcha', true)) : ?>
    <div class="ds-reg-input ds-reg-input-captcha">
        <label for="captcha"><?php echo __('Код с картинки'); ?></label>
        <img src="<?php echo get_site_url('/captcha.php?v=' . time()); ?>" alt="Captcha" onclick="(function(e){e.src=e.src.replace(/v=([0-9]+)/g,'v=' + Date.now())}(this))" />
        <input id="captcha" type="text" name="captcha" value="" autocomplete="off" />
    </div>
    <?php endif; ?>

    <?php do_event('ds_reg_fields_after', $reg); ?>

    <?php if (use_filters('ds_reg_field_submit', true)) : ?>
    <div class="ds-reg-submit">
        <button class="button" type="submit"><?php echo __('Зарегистрироваться'); ?></button>
    </div>
    <?php endif; ?>

    <?php do_event('ds_reg_form_end', $reg); ?>

    <input type="hidden" name="reg" value="1" />
</form>
<?php } ?>
<?    
get_footer();  