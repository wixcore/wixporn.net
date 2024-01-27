<?php

$set['title'] = __('Сейчас на сайте'); 
get_header(); 

$k_post = db::count("SELECT COUNT(id) FROM `user` WHERE `date_last` > '" . ( time() - 180 ) . "'");
$k_page = k_page( $k_post, $set['p_str'] );
$page   = page( $k_page );
$start  = $set['p_str'] * $page - $set['p_str'];

$q      = db::query("SELECT id FROM `user` WHERE `date_last` > '" . ( time() - 180 ) . "' ORDER BY `date_last` DESC LIMIT $start, $set[p_str]");

if ( $k_post == 0 ) {
    echo '<div class="mess">';
    echo __('Сейчас на сайте никого нет');
    echo '</div>';
} else {
    ?><div class="ds-posts-list" data-type="users"><?

    while ($ank = $q->fetch_assoc()) 
    {
        $ank = get_user($ank['id']); 

        $classes = array(); 

        $header = array( 
            'image' => '<a href="' . get_user_url($ank['id']) . '">' . get_avatar($ank['id']) . '</a>', 
            'content' => array(
                'post_title' => '<a href="' . get_user_url($ank['id']) . '">' . get_user_nick($ank['id']) . '</a>', 
                'post_content' => get_user_shortinfo($ank), 
            ), 
            'action' => array(), 
        ); 

        echo get_template_post(array(
            'href' => get_user_url($ank['id']), 
            'header' => $header, 
            'post_classes' => $classes, 
        )); 
    }
    ?>
    </div>
    <?

    if ($k_page > 1) {
        str( "?", $k_page, $page );    
    }
}

get_footer(); 