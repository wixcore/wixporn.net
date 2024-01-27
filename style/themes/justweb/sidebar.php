<div class="sidebar_profile">
<?php if (is_user()) : ?>
	<a href="<?php echo get_user_url(); ?>">
		<?php echo avatar(get_user_id()); ?><span class="nickname"><?php echo get_user_nick(); ?></span></a>
<?php else: ?>
    <form class="sidebar_form" method="post" action="<?php echo get_site_url('/login.php'); ?>">
        <?php do_event('jusweb_auth_before'); ?>
        <div class="input-text">
        <input type="text" name="nick" maxlength="32" placeholder="<?php echo __t('Логин', LANGUAGE_DOMAIN); ?>" /></div>
        <div class="input-text">
        <input type="password" name="pass" maxlength="32" placeholder="<?php echo __t('Пароль', LANGUAGE_DOMAIN); ?>" /></div>

        <div class="input-button align-right">
            <input type="submit" value="<?php echo __t('Войти', LANGUAGE_DOMAIN); ?>" /> 
            <label><input type="checkbox" name="aut_save" checked="checked" value="1" /> <?php echo __t('Запомнить меня', LANGUAGE_DOMAIN); ?></label>
        </div>
        <div class="input-button text-center">
            <a href="<?php echo get_site_url('/reg.php'); ?>"><?php echo __t('Регистрация', LANGUAGE_DOMAIN); ?></a> | 
            <a href="<?php echo get_site_url('/pass.php'); ?>"><?php echo __t('Забыли пароль?', LANGUAGE_DOMAIN); ?></a> 
        </div>
        <?php do_event('jusweb_auth_after'); ?>
    </form>
<?php endif; ?>
</div>

<div class="sidebar_nav">
<?php if (is_user()) : $counters = get_user_counters(); ?>
    <a class="sidebar_nav-mail" href="<?php echo get_site_url('/konts.php'); ?>">
    	<i class="flaticon-159-email"></i><span class="link_title"><?php echo __t('Сообщения', LANGUAGE_DOMAIN); ?></span>
        <span class="counter" data-type="mail" data-count="<?php echo $counters['mail']['count']; ?>"><?php echo $counters['mail']['count']; ?></span>
    </a>
    <a class="sidebar_nav-files" href="<?php echo get_site_url('/files/index/' . get_user_nick() . '/'); ?>">
    	<i class="flaticon-110-folder"></i><span class="link_title"><?php echo __t('Файлы', LANGUAGE_DOMAIN); ?></span>
    </a>
    <a class="sidebar_nav-photos" href="<?php echo get_site_url('/photos/index/' . get_user_nick() . '/'); ?>">
    	<i class="flaticon-132-picture"></i><span class="link_title"><?php echo __t('Фотографии', LANGUAGE_DOMAIN); ?></span>
    </a>

    <div class="sadibar_nav_music">
        <a class="sidebar_nav-music" href="<?php echo get_site_url('/music/index/' . get_user_nick() . '/'); ?>">
            <i class="flaticon-032-music"></i><span class="link_title"><?php echo __t('Музыка', LANGUAGE_DOMAIN); ?></span>
        </a>        
    </div>
<?php endif; ?>

<?php if (is_user_access('adm_panel_show')) : ?>
    <a class="sidebar_nav-admin" href="<?php echo get_site_url('/adm_panel/'); ?>">
        <i class="flaticon-012-dashboard"></i><span class="link_title"><?php echo __t('Админка', LANGUAGE_DOMAIN); ?></span>
    </a>
<?php endif; ?>
</div>