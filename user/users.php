<?php
require( '../sys/inc/core.php' );
$set['title'] = 'Пользователи';
get_header(); 
$sort = 'id';
$por  = 'DESC';
if ( isset( $_GET['ASC'] ) )
    $por = 'ASC';
if ( isset( $_GET['DESC'] ) )
    $por = 'DESC';
switch ( @$_GET['sort'] ) {
    case 'balls':
        $sql_sort = '`user`.`balls`';
        $sort     = 'balls';
        break;
    case 'level':
        $sql_sort = '`user_group`.`level`';
        $sort     = 'level';
        break;
    case 'rating':
        $sql_sort = '`user`.`rating`';
        $sort     = 'rating';
        break;
    case 'pol':
        $sql_sort = '`user`.`pol`';
        $sort     = 'pol';
        break;
    default:
        $sql_sort = '`user`.`id`';
        $sort     = 'id';
        break;
}
if ( !isset( $_GET['go'] ) ) {
    $k_post = db::count("SELECT COUNT(*) FROM `user`");
    $k_page = k_page( $k_post, $set['p_str'] );
    $page   = page( $k_page );
    $start  = $set['p_str'] * $page - $set['p_str'];
    echo "<div class='main'>
		Сортировать по: <br />
		<select name='menu' onchange='top.location.href = this.options[this.selectedIndex].value;'> 
		<option selected>-Выбрать-
		<option value='?sort=balls&amp;DESC&amp;page=$page'>баллы</option>
		<option value='?sort=level&amp;DESC&amp;page=$page'>статус</option>
		<option value='?sort=rating&amp;DESC&amp;page=$page'>рейтинг</option>
		<option value='?sort=id&amp;ASC&amp;page=$page'>id</option>
		<option value='?sort=pol&amp;ASC&amp;page=$page>пол</option>
		<option value='?sort=id&amp;DESC&amp;page=$page>новые</option>
		</select></option>
		</div>
		<table class='post'>\n";
    if ( $k_post == 0 ) {
        echo '<div class="mess">';
        echo 'Нет результатов';
        echo '</div>';
    }
    $q = db::query("SELECT `user`.`id` FROM `user` LEFT JOIN `user_group` ON `user`.`group_access` = `user_group`.`id` ORDER BY $sql_sort $por LIMIT $start, $set[p_str]");
    while ( $ank =  $q->fetch_assoc() ) {
        $ank = get_user( $ank['id'] );
        if ( $num == 0 ) {
            echo '<div class="nav1">';
            $num = 1;
        } elseif ( $num == 1 ) {
            echo '<div class="nav2">';
            $num = 0;
        }
        echo status( $ank['id'] ), group( $ank['id'] );
        echo " <a href='/info.php?id=$ank[id]'>$ank[nick]</a> \n";
        echo "" . medal( $ank['id'] ) . " " . online( $ank['id'] ) . "<br />";
        if ( $ank['group_access'] > 1 )
            echo "<span class='status'>$ank[group_name]</span><br />\n";
        if ( $sort == 'rating' )
            echo "<span class=\"ank_n\">Рейтинг:</span> <span class=\"ank_d\">$ank[rating]</span><br />\n";
        if ( $sort == 'balls' )
            echo "<span class=\"ank_n\">Баллы:</span> <span class=\"ank_d\">$ank[balls]</span><br />\n";
        if ( $sort == 'pol' )
            echo "<span class=\"ank_n\">Пол:</span> <span class=\"ank_d\">" . ( ( $ank['pol'] == 1 ) ? 'Мужской' : 'Женский' ) . "</span><br />\n";
        if ( $sort == 'id' )
            echo "<span class=\"ank_n\">Регистрация:</span> <span class=\"ank_d\">" . vremja( $ank['date_reg'] ) . "</span><br />\n";
        echo "<span class=\"ank_n\">Посл. посещение:</span> <span class=\"ank_d\">" . vremja( $ank['date_last'] ) . "</span><br />\n";
        if ( user_access( 'user_prof_edit' ) && $user['level'] > $ank['level'] ) {
            echo "<a href='/adm_panel/user.php?id=$ank[id]'>Редактировать профиль</a><br />\n";
        }
        echo '</div>';
    }
    echo "</table>\n";
    if ( $k_page > 1 )
        str( "users.php?sort=$sort&amp;$por&amp;", $k_page, $page );
}
$usearch = NULL;
if ( isset( $_SESSION['usearch'] ) )
    $usearch = $_SESSION['usearch'];
if ( isset( $_POST['usearch'] ) )
    $usearch = $_POST['usearch'];
if ( $usearch == NULL )
    unset( $_SESSION['usearch'] );
else
    $_SESSION['usearch'] = $usearch;
$usearch = preg_replace( "#( ){1,}#", "", $usearch );
if ( isset( $_GET['go'] ) && $usearch != NULL ) {
    $k_post = db::count("SELECT COUNT(*) FROM `user` WHERE `nick` like '%" . my_esc( $usearch ) . "%' OR `id` = '" . intval( $usearch ) . "'");
    $k_page = k_page( $k_post, $set['p_str'] );
    $page   = page( $k_page );
    $start  = $set['p_str'] * $page - $set['p_str'];
    echo "<table class='post'>
		<div class='main'>
		Сортировать по: <br />
		 <select name='menu' onchange='top.location.href = this.options[this.selectedIndex].value;'> 
	 	<option selected>-Выбрать-
		<option value='?sort=balls&amp;DESC&amp;page=$page'>баллы</option>
		<option value='?sort=level&amp;DESC&amp;page=$page'>статус</option>
		<option value='?sort=rating&amp;DESC&amp;page=$page'>рейтинг</option>
		<option value='?sort=id&amp;ASC&amp;page=$page'>id</option>
		<option value='?sort=pol&amp;ASC&amp;page=$page>пол</option>
		<option value='?sort=id&amp;DESC&amp;page=$page>новые</option>
		</select></option>
		</div>";
    if ( $k_post == 0 ) {
        echo "   <tr>
	<td class='p_t'>
		Нет результатов
		</td>
		</tr>\n";
    }
    $q = db::query("SELECT `id` FROM `user` WHERE `nick` like '%" . my_esc( $usearch ) . "%' OR `id` = '" . intval( $usearch ) . "' ORDER BY `$sort` $por LIMIT $start, $set[p_str]");
    while ( $ank =  $q->fetch_assoc() ) {
        $ank = get_user( $ank['id'] );
        if ( $num == 0 ) {
            echo '<div class="nav1">';
            $num = 1;
        } elseif ( $num == 1 ) {
            echo '<div class="nav2">';
            $num = 0;
        }
        echo "" . status( $ank['id'] ) . " " . group( $ank['id'] ) . "";
        echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a>\n";
        echo "" . medal( $ank['id'] ) . " " . online( $ank['id'] ) . "";
        if ( $ank['level'] != 0 )
            echo "<span class=\"status\">$ank[group_name]</span><br />\n";
        if ( $sort == 'rating' )
            echo "<span class=\"ank_n\">Рейтинг:</span> <span class=\"ank_d\">$ank[rating]</span><br />\n";
        if ( $sort == 'balls' )
            echo "<span class=\"ank_n\">Баллы</span> <span class=\"ank_d\">$ank[balls]</span><br />\n";
        if ( $sort == 'pol' )
            echo "<span class=\"ank_n\">Пол:</span> <span class=\"ank_d\">" . ( ( $ank['pol'] == 1 ) ? 'Мужской' : 'Женский' ) . "</span><br />\n";
        if ( $sort == 'id' )
            echo "<span class=\"ank_n\">Регистрация:</span> <span class=\"ank_d\">" . vremja( $ank['date_reg'] ) . "</span><br />\n";
        echo "<span class=\"ank_n\">Посл. посещение:</span> <span class=\"ank_d\">" . vremja( $ank['date_last'] ) . "</span><br />\n";
        if ( user_access( 'user_prof_edit' ) && $user['level'] > $ank['level'] ) {
            echo "<a href='/adm_panel/user.php?id=$ank[id]'>Редактировать профиль</a><br />\n";
        }
        echo '</div>';
    }
    echo "</table>\n";
    if ( $k_page > 1 )
        str( "users.php?go&amp;sort=$sort&amp;$por&amp;", $k_page, $page );
} else
    echo "<div class=\"post\">\nВведите ID или НИК юзера</div>\n";
echo "<form method=\"post\" action=\"?go&amp;sort=$sort&amp;$por\">";
$usearch = stripcslashes( htmlspecialchars( $usearch ) );
echo "<input type=\"text\" name=\"usearch\" maxlength=\"16\" value=\"$usearch\" /><br />\n";
echo "<input type=\"submit\" value=\"Найти юзера\" />";
echo "</form>\n";
get_footer(); 