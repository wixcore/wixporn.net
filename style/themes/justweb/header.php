<?php 

if (is_ajax()) {
    ?>
    <script>
    if (window.jQuery.fn.ajaxpage === undefined) {
        window.location.reload(true); 
    }
    </script>
    <div id="ajax-meta" style="display: none;" data-title="<?php ds_document_title(); ?>" data-body="<?php echo join(' ', get_body_class()); ?>" data-message="<?php echo (!empty($_SESSION['message']) ? $_SESSION['message'] : ''); ?>"></div>
    <?

    $_SESSION['message'] = null; 
    return ; 
}

$counters = justweb_counters();
$preset = jw_user_preset(); 
$justweb = jw_theme_settings(); 

echo '<?xml version="1.0" encoding="utf-8"?>'; 
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru-RU">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?php ds_document_title(); ?></title>
      <?php ds_head(); ?>  
    <link rel="shortcut icon" href="<?php echo get_theme_uri(); ?>/images/favicon.ico" />
    <script>var theme_uri = '<?php echo get_theme_uri(); ?>';</script>

    <meta name="theme-color" content="<?php echo (isset($preset['primary']) ? $preset['primary'] : '#36585e'); ?>">
</head>
<body <?php body_class(); ?>>
	<!-- Header panel -->
    <div class="wrap_header">
        <div class="container header">
            <input type="checkbox" id="header-player-toggle" />
            
            <div class="header-logo">
                <a href="/"><?php echo $justweb['logotype']; ?></a>
            </div>

            <div class="header-search">
                <form action="/search/">
                	<input type="search" name="q" placeholder="<?php echo __t('Поиск по сайту', LANGUAGE_DOMAIN); ?>" />
                </form>
                
                <?php if (is_user()) : ?>
                <div class="header-music" data-toggle="1">
                    <label class="header-player-toggle" for="header-player-toggle"><i class="flaticon-019-headphones"></i></label>
                    <div class="dpl" data-god="1" data-title="" data-id="" data-src="" data-hash="" data-uniquie="">
                        <div class="dpl-toggle"></div>
                    </div>
                </div>
                <?php endif; ?> 

                <div class="header-people">
                    <a href="<?php echo get_site_url('/online.php'); ?>">
                        <strong class="counter" data-type="users_online" id="counter_users_online" data-count="<?php echo $counters['users_online']['count']; ?>"><?php echo $counters['users_online']['count']; ?></strong>
                        <span><?php echo __t('Сейчас на сайте', LANGUAGE_DOMAIN); ?></span>
                    </a>
                </div>
            </div>

            <div class="mobile-sidebar-toggle"><i class="fa fa-bars"></i></div>

            <div class="header-nav">
            <?php if (is_user()) : ?>
                <a href="<?php echo get_site_url('/feed/'); ?>"><i class="flaticon-074-wifi"></i></a>
                <a href="#"><i class="flaticon-160-chat"></i></a>
                <a href="<?php echo get_site_url('/user/notify/'); ?>">
                    <i class="flaticon-161-bell"></i>
                    <span class="counter" data-type="notify" data-count="<?php echo $counters['notify']['count']; ?>"><?php echo $counters['notify']['count']; ?></span>
                </a>
                <a href="<?php echo get_user_url(); ?>"><i class="flaticon-097-user"></i></a>
            <?php endif; ?>
            </div>

            <?php if (is_user()) : ?>
            <div class="header-player">
                <div class="dpl dpl-header" data-god="1" data-title="" data-id="" data-src="" data-hash="" data-uniquie="">
                    <div class="dpl-toggle"></div>
                    <div class="dpl-group">
                        <div class="dpl-title" data-title=""> <?php echo __t('Заголовок', LANGUAGE_DOMAIN); ?></div>
                        <div class="dpl-time">0:00</div>
                        <div class="dpl-progress" onclick="setPlayerSeeked(this);">
                            <div class="dpl-progress-loaded"></div>
                            <div class="dpl-progress-bar"></div>
                        </div>
                    </div>
                    <div class="dpl-volume"><div class="dpl-volume-bar"></div></div>
                    <div class="dpl-buttons">
                        <button class="dpl-btn dpl-repeat"><i class="fa fa-retweet" aria-hidden="true"></i></button>
                        <button class="dpl-btn dpl-shuffle"><i class="fa fa-random" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="music_playlist">
                    <?php echo __t('Вы еще не загрузили ни одной песни', LANGUAGE_DOMAIN); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!--/ Header panel -->
    
    <!-- Wrap Site Container -->
    <div class="container wrap_content">

        <!-- Require Sidebar -->
    	<div class="page_sidebar">
    		<?php require(dirname(__FILE__) . '/sidebar.php'); ?>
    	</div>
        <!--/ Require Sidebar -->
        
        <!-- Body site Content -->
        <div class="page_content" id="page_content">


            <?php ds_messages(); ?>
            <?php ds_errors(); ?>
        