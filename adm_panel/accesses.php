<?php
// Основной файл системы
require('../sys/inc/core.php');

user_access('adm_accesses', null, 'index.php?' . SID);

if (isset($_GET['id_group']) && db::count("SELECT COUNT(*) FROM `user_group` WHERE `id` = '" . intval($_GET['id_group']) . "'")) {
    $group        = db::fetch("SELECT * FROM `user_group` WHERE `id` = '" . intval($_GET['id_group']) . "'", ARRAY_A);
    $set['title'] = output_text('Группа "' . $group['name'] . '" - привилегии'); // заголовок страницы
    get_header_admin(); 
    if (isset($_POST['accesses'])) {
        db::query("DELETE FROM `user_group_access` WHERE `id_group` = '$group[id]'");
        $q = db::query("SELECT * FROM `all_accesses`");
        while ($post = $q->fetch_assoc()) {
            $type = $post['type'];
            if (isset($_POST[$type]) && $_POST[$type] == 1)
                db::query("INSERT INTO `user_group_access` (`id_group`, `id_access`) VALUES ('$group[id]', '$post[type]')");
        }
        msg('Привилегии успешно изменены');
    }
    
    echo "<form method='post' action='?id_group=$group[id]&amp;$passgen'>\n";
    $q = db::query("SELECT * FROM `all_accesses` ORDER BY `name` ASC");
    while ($post = $q->fetch_assoc()) {
        echo "<label>";
        echo "<input type='checkbox'" . (db::count("SELECT COUNT(*) FROM `user_group_access` WHERE `id_group` = '$group[id]' AND `id_access` = '$post[type]' LIMIT 1") == 1 ? " checked='checked'" : null) . " name='$post[type]' value='1' />";
        echo $post['name'];
        echo "</label><br />\n";
    }
    echo "<input value='Применить' name='accesses' type='submit' />\n";
    echo "</form>\n";
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='accesses.php'>Группы</a><br />";
    echo "&laquo;<a href='index.php'>Админка</a><br />";
    echo "</div>\n";
    include_once '../sys/inc/tfoot.php';
}
$set['title'] = 'Группы пользователей'; // заголовок страницы
get_header_admin(); 
echo "<div class='menu'>\n";
$accesses = db::query("SELECT * FROM `user_group` ORDER BY `id` ASC");
while ($res = $accesses->fetch_assoc()) {
    echo "<a href='?id_group=$res[id]'>$res[name] (L$res[level], " . db::count("SELECT COUNT(*) FROM `user_group_access` WHERE `id_group` = '$res[id]'") . ")</a><br />\n";
}
echo "</div>\n";
if (user_access('adm_panel_show')) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='index.php'>Админка</a><br />";
    echo "</div>\n";
}
get_footer_admin(); 