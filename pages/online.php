<?php

if ( isset( $_GET['admin'] ) && user_access( 'user_collisions' ) ) {
    if ( $_GET['admin'] == 'close' )
        $_SESSION['admin'] = null;
    else
        $_SESSION['admin'] = true;
}

$set['title'] = __('Сейчас на сайте'); 
get_header(); 

$k_post = db::count("SELECT COUNT(*) FROM `user` WHERE `date_last` > '" . ( time() - 600 ) . "'");
$k_page = k_page( $k_post, $set['p_str'] );
$page   = page( $k_page );
$start  = $set['p_str'] * $page - $set['p_str'];

$q      = db::query("SELECT id, pol, url, level, ip, ip_xff, ip_cl, ua, date_last FROM `user` WHERE `date_last` > '" . ( time() - 600 ) . "' ORDER BY `date_last` DESC LIMIT $start, $set[p_str]");
echo '<table class="post">';

if ( $k_post == 0 ) {
    echo '<div class="mess">';
    echo __('Сейчас на сайте никого нет');
    echo '</div>';
}

while ( $ank =  $q->fetch_assoc() ) {
    
    echo '<div class="' . ( $num % 2 ? "nav1" : "nav2" ) . '">';
    $num++;
    echo user::avatar( $ank['id'], 0 ) . user::nick( $ank['id'], 1, 1, 1 ) . otkuda( $ank['url'] ) . ' <br />';
    if ( isset( $user ) && isset( $_SESSION['admin'] ) ) {
        $mass[0]    = $ank['id'];
        $collisions = user_collision( $mass );
        if ( count( $collisions ) > 1 ) {
            echo '<span class="ank_n">Возможные ники</span> ';
            echo '<span class="ank_d">';
            for ( $i = 1; $i < count( $collisions ); $i++ ) {
                echo ' :: ' . user::nick( $collisions[$i] );
            }
            echo '</span><br />';
        }
        if ( $ank['ip'] != NULL ) {
            if ( user_access( 'user_show_ip' ) && $ank['ip'] != 0 ) {
                echo '<span class="ank_n">IP:</span> <span class="ank_d">' . long2ip( $ank['ip'] ) . '</span>';
                if ( user_access( 'adm_ban_ip' ) )
                    echo ' [<a href="/adm_panel/ban_ip.php?min=' . $ank['ip'] . '">Бан</a>]';
                echo '<br />';
            }
        }
        if ( $ank['ip_cl'] != NULL ) {
            if ( user_access( 'user_show_ip' ) && $ank['ip_cl'] != 0 ) {
                echo '<span class="ank_n">IP (CLIENT):</span> <span class="ank_d">' . long2ip( $ank['ip_cl'] ) . '</span>';
                if ( user_access( 'adm_ban_ip' ) )
                    echo ' [<a href="/adm_panel/ban_ip.php?min=' . $ank['ip_cl'] . '">Бан</a>]';
                echo '<br />';
            }
        }
        if ( $ank['ip_xff'] != NULL ) {
            if ( user_access( 'user_show_ip' ) && $ank['ip_xff'] != 0 ) {
                echo '<span class="ank_n">IP (XFF):</span> <span class="ank_d">' . long2ip( $ank['ip_xff'] ) . '</span>';
                if ( user_access( 'adm_ban_ip' ) )
                    echo ' [<a href="/adm_panel/ban_ip.php?min=' . $ank['ip_xff'] . '">Бан</a>]';
                echo '<br />';
            }
        }
        if ( user_access( 'user_show_ua' ) && $ank['ua'] != NULL )
            echo '<span class="ank_n">Браузер:</span> <span class="ank_d">' . $ank['ua'] . '</span><br />';
        if ( user_access( 'user_show_ip' ) && opsos( $ank['ip'] ) )
            echo '<span class="ank_n">Пров:</span> <span class="ank_d">' . opsos( $ank['ip'] ) . '</span><br />';
        if ( user_access( 'user_show_ip' ) && opsos( $ank['ip_cl'] ) )
            echo '<span class="ank_n">Пров (CL):</span> <span class="ank_d">' . opsos( $ank['ip_cl'] ) . '</span><br />';
        if ( user_access( 'user_show_ip' ) && opsos( $ank['ip_xff'] ) )
            echo '<span class="ank_n">Пров (XFF):</span> <span class="ank_d">' . opsos( $ank['ip_xff'] ) . '</span><br />';
        if ( $user['level'] > $ank['level'] && $user['id'] != $ank['id'] ) {
            if ( user_access( 'user_prof_edit' ) )
                echo '[<a href="/adm_panel/user.php?id=' . $ank['id'] . '"><img src="/style/icons/edit.gif" alt="*" /> ред.</a>] ';
            if ( $user['id'] != $ank['id'] ) {
                if ( user_access( 'user_ban_set' ) || user_access( 'user_ban_set_h' ) || user_access( 'user_ban_unset' ) )
                    echo '[<a href="/adm_panel/ban.php?id=' . $ank['id'] . '"><img src="/style/icons/blicon.gif" alt="*" /> бан</a>] ';
                if ( user_access( 'user_delete' ) ) {
                    echo '[<a href="/adm_panel/delete_user.php?id=' . $ank['id'] . '"><img src="/style/icons/delete.gif" alt="*" /> удл.</a>] ';
                    echo '<br />';
                }
            }
        }
    } else {
        echo '<b>(' . ( ( $ank['pol'] == 1 ) ? 'М' : 'Ж' ) . ')</b>';

        echo ', <img src="/style/icons/time.png" alt="away" /> [' . vremja( $ank['date_last'] ) . ']';
    }
    echo '</div>';
}
echo '</table>';

if ( $k_page > 1 )
    str( "?", $k_page, $page );

if ( user_access( 'user_collisions' ) ) {
?>
	<div class="foot">
	<?= ( !isset( $_SESSION['admin'] ) ? '<a href="?admin">Расширенный режим</a> | <b>Обычный режим</b>' : '<b>Расширенный режим</b> | <a href="?admin=close">Обычный режим</a>' ) ?>
	</div>
	<?php
}

get_footer(); 