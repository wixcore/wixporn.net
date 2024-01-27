<?php

only_reg();
$set['title'] = __('Личный кабинет');

get_header(); 
?>
<div class="box-group-wrap ds-file-image ds-file-image-jpg">
    <div class="box-group">
        <div class="box-group-title"><?php echo __('Мой профиль'); ?></div>

        <div class="box-group-links">
            <a class="box-link" href="<?php echo get_site_url('/info.php'); ?>"><i class="fa fa-user"></i> <?php echo __('Моя страничка'); ?></a>
        </div>
        <div class="box-group-links">
            <a class="box-link" href="<?php echo get_site_url('/user/anketa/?id=' . $user['id']); ?>"><i class="fa fa-file-text-o"></i> <?php echo __('Моя анкета'); ?></a>
        </div>
        <div class="box-group-links">
            <a class="box-link" href="<?php echo get_site_url('/avatar.php'); ?>"><i class="fa fa-id-badge"></i> <?php echo __('Мой аватар'); ?></a>
        </div>
    </div>
    <div class="box-group">
        <div class="box-group-title"><?php echo __('Мои настройки'); ?></div>

        <div class="box-group-links">
            <a class="box-link" href="<?php echo get_site_url('/user/settings/'); ?>"><i class="fa fa-gear"></i> <?php echo __('Общие настройки'); ?></a>
        </div>
    </div>
    <div class="box-group">
        <?php if (user_access( 'adm_panel_show' )) : ?>
        <div class="box-group-links">
            <a class="box-link" href="<?php echo get_site_url('/adm_panel/'); ?>"><i class="fa fa-tachometer"></i> <?php echo __('Админка'); ?></a>
        </div>
        <?php endif; ?>

        <div class="box-group-links">
            <a class="box-link" href="<?php echo get_site_url('/exit.php'); ?>"><i class="fa fa-power-off"></i> <?php echo __('Выход'); ?></a>
        </div>
    </div>
</div>
<?
get_footer(); 