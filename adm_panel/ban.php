<?php

// Основной файл системы
require('../sys/inc/core.php'); 

if (!user_access('user_ban_set') && !user_access('user_ban_set_h') && !user_access('user_ban_unset')){header("Location: /index.php?".SID);exit;}

if (isset($_GET['id'])) { 
    $ank['id']=intval($_GET['id']);
} else {
    header("Location: /index.php?".SID);
    exit;
}

if (db::count("SELECT COUNT(*) FROM `user` WHERE `id` = '$ank[id]' LIMIT 1")==0)
{
    header("Location: /index.php?".SID);
    exit;
}

$ank = get_user($ank['id']);

if ($user['level'] <= $ank['level'])
{
    header("Location: /index.php?".SID);
    exit;
}

$set['title'] = 'Бан пользователя '.$ank['nick'];

if (isset($_GET['delete']) && db::count("SELECT COUNT(*) FROM `ban` WHERE `id_user` = '$ank[id]' AND `id` = '".intval($_GET['delete'])."'") && user_access('user_ban_unset'))
{
    $ban_info = db::fetch("SELECT * FROM `ban` WHERE `id_user` = '$ank[id]' AND `id` = '".intval($_GET['delete'])."'", ARRAY_A);
    $ank2 = db::fetch("SELECT * FROM `user` WHERE `id` = '$ban_info[id_ban]' LIMIT 1", ARRAY_A);
    
    if (($user['level']>$ank2['level'] || $user['id'] == $ank2['id']) || $user['level'] == 4)
    {
        db::query("DELETE FROM `ban` WHERE `id` = '".intval($_GET['delete'])."' LIMIT 1");
        admin_log('Пользователи','Бан',"Удаление нарушения с пользователя '[url=/amd_panel/ban.php?id=$ank[id]]$ank[nick][/url]'");
        $_SESSION['message'] = 'Нарушение удалено';
        header("Location: ?id=$ank[id]");
        exit;
    }
    else
    $err[]='Нет прав';
}

if (isset($_GET['unset']) && db::count("SELECT COUNT(*) FROM `ban` WHERE `id_user` = '$ank[id]' AND `id` = '".intval($_GET['unset'])."'") && user_access('user_ban_unset'))
{
    $ban_info = db::fetch("SELECT * FROM `ban` WHERE `id_user` = '$ank[id]' AND `id` = '".intval($_GET['unset'])."'", ARRAY_A);
    $ank2 = db::fetch("SELECT * FROM `user` WHERE `id` = '$ban_info[id_ban]' LIMIT 1", ARRAY_A);
    
    if (($user['level'] > $ank2['level'] || $user['id'] == $ank2['id']) || $user['level'] == 4)
    {
        db::query("UPDATE `ban` SET `time` = '$time', `navsegda` = '0' WHERE `id` = '".intval($_GET['unset'])."' LIMIT 1");
        admin_log('Пользователи','Бан',"Снятие бана пользователя '[url=/amd_panel/ban.php?id=$ank[id]]$ank[nick][/url]'");
        $_SESSION['message'] = 'Время бана обнулено';
        header("Location: ?id=$ank[id]");
        exit;
    }
    else
    $err[]='Нет прав';
}

if (isset($_POST['ban_pr']) && isset($_POST['time']) && isset($_POST['vremja']) && (user_access('user_ban_set') || user_access('user_ban_set_h')))
{
    $timeban = $time;
    if ($_POST['vremja'] == 'min')$timeban+=intval($_POST['time'])*60;
    if ($_POST['vremja'] == 'chas')$timeban+=intval($_POST['time'])*60*60;
    if ($_POST['vremja'] == 'sut')$timeban+=intval($_POST['time'])*60*60*24;
    if ($_POST['vremja'] == 'mes')$timeban+=intval($_POST['time'])*60*60*24*30;
    
    if ($timeban < $time) { 
        $err[] = 'Ошибка времени бана';
    }

    if (!user_access('user_ban_set'))$timeban=min($timeban, $time+3600*24);

    $pochemu = (isset($_POST['pochemu']) ? (int) $_POST['pochemu'] : '0');
    $razdel = my_esc($_POST['razdel']);
    $post = (int) $_POST['post'];
    $navsegda = (int) $_POST['navsegda'];

    $prich = $_POST['ban_pr'];

    if (strlen2($prich)>1024){$err[]='Сообщение слишком длинное';}
    if (strlen2($prich)<10){$err[]='Необходимо подробнее указать причину';}

    $prich = my_esc($prich);

    if (!isset($err)) {
        db::query("INSERT INTO `ban` (`id_user`, `id_ban`, `prich`, `time`, `pochemu`, `razdel`, `post`, `navsegda`) VALUES ('$ank[id]', '$user[id]', '$prich', '$timeban', '$pochemu', '$razdel', '$post', '$navsegda')");
        admin_log('Пользователи','Бан',"Бан пользователя '[url=/adm_panel/ban.php?id=$ank[id]]$ank[nick][/url]' до ".vremja($timeban)." по причине '$prich'");
        
        $_SESSION['message'] = 'Пользователь успешно забанен';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
get_header_admin(); 

$k_post = db::count("SELECT COUNT(*) FROM `ban` WHERE `id_user` = '$ank[id]'");
$k_page = k_page($k_post,$set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];

echo "<table class='post'>\n";

if ($k_post == 0) {
    echo "<div class='mess'>\n";
    echo "Нет нарушений\n";
    echo "</div>\n";
}

$q = db::query("SELECT * FROM `ban` WHERE `id_user` = '$ank[id]' ORDER BY `time` DESC LIMIT $start, $set[p_str]");

while ($post = $q->fetch_assoc())
{
    if ($num == 0) {
        echo "  <div class='nav1'>\n";
        $num = 1;
    } elseif ($num == 1) {
        echo "  <div class='nav2'>\n";
        $num = 0;
    }
    
    $ank2 = db::fetch("SELECT * FROM `user` WHERE `id` = $post[id_ban] LIMIT 1", ARRAY_A);

    echo user::avatar($ank2['id']) . user::nick($ank2['id'], 1, 1, 1);	
    
    if ($post['navsegda'] == 1){		
        echo " бан <font color=red><b>навсегда</b></font><br />";
    } else {		
        echo " до " . vremja($post['time']) . "<br />";	
    }
    
    echo '<b>Причина:</b> '.$pBan[$post['pochemu']].'<br />';
    echo '<b>Раздел:</b> '.$rBan[$post['razdel']].'<br />';

    echo '<b>Комментарий:</b> ' . output_text($post['prich']) . "<br />\n";
    
    if ($post['time'] > $time && user_access('user_ban_unset')) {
        echo "<font color=red><b>Активен</b></font> | <a href='?id=$ank[id]&amp;unset=$post[id]'>Снять бан</a><br />\n";
    }
    
    echo "<div style='text-align:right;'> <a href='?id=$ank[id]&amp;delete=$post[id]'><img src='/style/icons/delete.gif' alt='*'></a></div>";
    echo "</div>\n";
}

echo "</table>\n";

if ($k_page>1) {
    str('?id='.$ank['id'].'&amp;',$k_page,$page);
}
    
if (user_access('user_ban_set') || user_access('user_ban_set_h'))
{
    echo "<form action=\"ban.php?id=$ank[id]&amp;$passgen\" method=\"post\">\n";
    echo "<div class='nav1'>Раздел:</div>";
    if ($user['group_access'] == 12 || $user['level'] > 1)echo "<input name='razdel' type='radio' value='guest'  checked='checked'/>Гостевая <br />";
    if ($user['group_access'] == 11 || $user['level'] > 1)echo "<input name='razdel' type='radio' value='notes'  checked='checked'/>Дневники <br />";
    if ($user['group_access'] == 3 || $user['level'] > 1)echo "<input name='razdel' type='radio' value='forum'  checked='checked'/>Форум <br />";
    if ($user['group_access'] == 4 || $user['level'] > 1)echo "<input name='razdel' type='radio'  value='files'  checked='checked'/>Файлы <br />";
    if ($user['group_access'] == 2 || $user['level'] > 1)echo "<input name='razdel' type='radio'  value='chat'  checked='checked'/>Чат <br />";
    if ($user['group_access'] == 5 || $user['level'] > 1)echo "<input name='razdel' type='radio'  value='lib'  checked='checked'/>Библиотека<br />";
    if ($user['group_access'] == 6 || $user['level'] > 1)echo "<input name='razdel' type='radio'  value='foto'  checked='checked'/>Фотографии<br />";
    if ($user['level'] > 1)
    echo "<input name='razdel' type='radio' value='all' checked='checked'/>Весь сайт <br />";

    echo "<div class='nav1'>Причина:</div>";
    echo "<input name='pochemu' type='radio' value='1' checked='checked'/>Спам/Реклама<br />";
    echo "<input name='pochemu' type='radio' value='2' />Мошенничество<br />";
    echo "<input name='pochemu' type='radio' value='3' />Нецензурная брань<br />";
    echo "<input name='pochemu' type='radio' value='4' />Клонирование ников<br />";
    echo "<input name='pochemu' type='radio' value='5' />Подстрекательство, провокация и побуждение к агрессии<br />";
    echo "<input name='pochemu' type='radio' value='6' />Флуд<br />";
    echo "<input name='pochemu' type='radio' value='7' />Флейм<br />";
    echo "<input name='pochemu' type='radio' value='0' />Другое<br />";

    echo "<div class='nav1'>Сообщения:</div>";
    echo "<input name='post' type='radio' value='0' checked='checked'/>Показывать <br />";
    echo "<input name='post' type='radio' value='1' />Скрыть<br />";

    echo "<div class='nav1'>Комментарий:</div>\n";
    echo "<textarea name=\"ban_pr\"></textarea><br />\n";
    echo "<div class='nav1'>Время бана ".(user_access('user_ban_set')?null:'(max 1 сутки)').":</div>\n";
    echo "<input type='text' name='time' title='Время бана' value='10' maxlength='11' size='3' />\n";
    echo "<select class='form' name=\"vremja\">\n";
    echo "<option value='min'>Минуты</option>\n";
    echo "<option ".(($k_post > 1)?'selected="selected" ':null)."value='chas'>Часы</option>\n";
    echo "<option value='sut'>Сутки</option>\n";
    echo "<option value='mes'".(user_access('user_ban_set')?null:' disabled="disabled"').">Месяцы</option>\n";
    echo "</select><br />\n";
    echo "<label><input type='checkbox' name='navsegda' value='1' /> Навсегда</label><br />";
    echo "<input type='submit' value='Забанить' />\n";
    echo "</form>\n";
}
else 
{
    echo "<div class='err'>Нет прав для того, чтобы забанить пользователя</div>\n";
}

echo "<div class='foot'>\n";
echo "&raquo;<a href=\"/mail.php?id=$ank[id]\">Написать сообщение</a><br />\n";
echo "&laquo;<a href=\"/info.php?id=$ank[id]\">В анкету</a><br />\n";

if (user_access('adm_panel_show')) {
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
}
echo "</div>\n";

get_footer_admin(); 