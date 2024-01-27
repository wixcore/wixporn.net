<?php

require('../sys/inc/core.php');
user_access( 'adm_panel_show', null, '/index.php?' . SID);

$admin = (isset($_GET['admin']) ? get_admin_url($_GET['admin']) : false); 
if ($admin) {
    do_event('ds_admin_page_' . text($_GET['admin']) . '_init'); 
    
    $ds_admin_page = get_admin_page($admin); 

    if (isset($ds_admin_page['function'])) {
        add_event('ds_admin_content', $ds_admin_page['function']); 
    }
    
    do_event('admin_page_' . $admin . '_init'); 
}

$set['title'] = (isset($ds_admin_page['title']) ? text($ds_admin_page['title']) : __('Админка'));
get_header_admin(); 

do_event('pre_admin_content'); 
if (isset($ds_admin_page)) {
    do_event('ds_admin_content'); 
}

do_event('after_admin_content'); 

get_footer_admin(); 