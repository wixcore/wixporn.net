<?php 
echo '<?xml version="1.0" encoding="utf-8"?>'; 
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php ds_document_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo get_site_url('/sys/static/images/favicon.ico'); ?>" />

    <link href="https://fonts.googleapis.com/css?family=Paytone+One|Ramaraja|Wendy+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo get_site_url('/sys/static/css/font-awesome.min.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo get_site_url('/sys/static/jquery-ui/jquery-ui.min.css'); ?>">
    <?php $ver = filemtime(ROOTPATH . '/sys/static/css/admin.css'); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo get_site_url('/sys/static/css/admin.css?ver=' . $ver); ?>" media="all">

    <script type='text/javascript' src='<?php echo get_site_url('/sys/static/js/jquery-3.4.1.min.js'); ?>'></script>
    <script type='text/javascript' src='<?php echo get_site_url('/sys/static/jquery-ui/jquery-ui.min.js'); ?>'></script>
    <?php $ver = filemtime(ROOTPATH . '/sys/static/js/admin.js'); ?>
    <script type='text/javascript' src='<?php echo get_site_url('/sys/static/js/admin.js?ver=' . $ver); ?>'></script>

    <?php ds_admin_head(); ?>

    <script><?php echo 'var ajax_url = \'' . get_site_url('/ds-ajax/') . '\';'; ?></script>
</head>
<body>
	<div class="header">
        <nav class="header-menu">
            <a class="link link-home" href="<?php echo get_site_url('/adm_panel/'); ?>">
                <span class="icon-ds">CS</span> CMS-Social <sup>v3</sup>
            </a>
            <a class="link link-toggle-menu" href="javascript:void(0)">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </a>
            <div class="header-menu-add">
            <?php if (is_user_access('update_core')) : ?>
                <a class="link link-update" href="<?php echo get_site_url('/adm_panel/update.php'); ?>">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                    <?php 
                    if (function_exists('get_list_updates')) { 
                        $updates = get_list_updates(); 

                        if ($updates['count_any'] > 0) {
                            echo '<span class="counter">' . $updates['count_any'] . '</span>';
                        }                        
                    }
                    ?>
                </a>
            <?php endif; ?>
            </div>
        </nav>
	</div>
    <div class="wrap">
        <?php echo ds_admin_menu(); ?>
        <div class="link-toggle-menu wrap-admin-menu-close"></div>

        <div class="content">
            <div class="content-title">
                <?php ds_document_title(); ?> 
                <?php 
                $mask = array(
                    'class' => '', 
                    'title' => '', 
                    'url' => '', 
                ); 
                $admin_title_action = use_filters('ds_admin_title_action', array());   
                if (is_array($admin_title_action) && $admin_title_action) {
                    foreach($admin_title_action AS $link) {
                        $link = array_merge($mask, $link); 
                        echo '<a href="' . $link['url'] . '" class="page-title-action ' . $link['class'] . '">' . $link['title'] . '</a>';
                    }                    
                }
                ?>
            </div>
                <?php ds_admin_messages(); ?>
                <?php ds_admin_errors(); ?> 