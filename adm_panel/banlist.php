<?php
// Основной файл системы
require('../sys/inc/core.php');

user_access('adm_banlist', null, 'index.php?' . SID);

$set['title'] = 'Банлист';
get_header_admin(); 
$k_post = db::count("SELECT COUNT(*) FROM `ban` WHERE `time` > '$time'");
$k_page = k_page($k_post, $set['p_str']);
$page   = page($k_page);
$start  = $set['p_str'] * $page - $set['p_str'];
$q      = db::query("SELECT * FROM `ban` WHERE `time` > '$time' ORDER BY `id` DESC LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
if ($k_post == 0) {
    echo "   <tr>\n";
    echo "  <td class='p_t'>\n";
    echo "Нет нарушений\n";
    echo "  </td>\n";
    echo "   </tr>\n";
}
while ($ban = $q->fetch_assoc()) {
    echo "   <tr>\n";
    $ank = get_user($ban['id_user']);
    if ($set['set_show_icon'] == 2) {
        echo "  <td class='icon48' rowspan='2'>\n";
        avatar($ank['id']);
        echo "  </td>\n";
    } elseif ($set['set_show_icon'] == 1) {
        echo "  <td class='icon14'>\n";
        echo "" . status($ank['id']) . "";
        echo "  </td>\n";
    }
    echo "  <td class='p_t'>\n";
    echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a>" . online($ank['id']) . "\n";
    echo "  </td>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
    if ($set['set_show_icon'] == 1)
        echo "  <td class='p_m' colspan='2'>\n";
    else
        echo "  <td class='p_m'>\n";
    $user_ban = get_user($ban['id_ban']);
    echo "<span class=\"ank_n\">Забанен до " . vremja($ban['time']) . ":</span><br />\n";
    echo "<span class=\"ank_d\">" . output_text($ban['prich']) . "</span>\n($user_ban[nick])<br />\n";
    if ((isset($access['ban_set']) || isset($access['ban_unset'])) && ($ank['level'] < $user['level'] || $user['level'] == 4))
        echo "<a href='/adm_panel/ban.php?id=$ank[id]'>Подробно</a><br />\n";
    echo "  </td>\n";
    echo "   </tr>\n";
}
echo "</table>\n";
if ($k_page > 1)
    str("?", $k_page, $page); // Вывод страниц
if (user_access('adm_panel_show')) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "</div>\n";
}
get_footer_admin(); 