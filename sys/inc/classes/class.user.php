<?php

/**
* Устаревший класс
*/

class user
{
    /**
     * / Ссылка и Ник юзера
     */
    public static function nick( $user = 0, $url = 1, $on = 0, $medal = 0 )
    {
        /*
         * $url == 0        Выводит только ник
         * $url == 1        Выводит ник с ссылкой на страницу юзера
         */
        $ank        = get_user($user);
        $nick       = null;

        // Вывод ника 
        if ( $user == 0 ) {
            $ank = array(
                'id' => '0',
                'nick' => 'Cистема',
                'pol' => '1',
                'rating' => '0',
                'browser' => 'wap',
                'date_last' => time() 
            );
        } elseif ( !$ank ) {
            $ank = array(
                'id' => '0',
                'nick' => '[Удален]',
                'pol' => '1',
                'rating' => '0',
                'browser' => 'wap',
                'date_last' => time() 
            );
        }

        if ( $url == true ) {
            $nick = ' <a href="/info.php?id=' . $user . '">' . text( $ank['nick'] ) . '</a> ';
        } else {
            $nick = text( $ank['nick'] );
        }

        return $nick ;
    }

    /**
     * / Аватар, иконка группы пользователя
     */
    public static function avatar( $user = 0, $type = 0 )
    {
        /*
         * $type == 0 - Выводит аватар и иконку вместе
         * $type == 1 - Выводит только аватар
         * $type == 2 - Выводит только иконку
         */
        global $time, $set;
        $AVATAR = '';
        $icon   = '';
        if ( $user != 0 ) {
            $ank = db::fetch('SELECT `pol`, `id`, `group_access` FROM `user` WHERE `id` = "' . $user . '" LIMIT 1 ', ARRAY_A);
        }
        if ( $user == 0 ) {
            $ank = array(
                 'id' => '0',
                'pol' => '1',
                'group_access' => '0' 
            );
        } elseif ( !$ank ) {
            $ank = array(
                 'id' => '0',
                'pol' => '1',
                'group_access' => '0' 
            );
        }

        // Аватар
        if ( $type == 0 || $type == 1 ) {
            $AVATAR = get_avatar($user, 'thumbnail'); 
        }

        // Иконка пользователя
        if ( $type == 0 || $type == 2 ) {
            if ( db::count("SELECT COUNT(*) FROM `ban` WHERE `id_user` = '$user' AND (`time` > '" . time() . "' OR `navsegda` = '1')") != 0 ) {
                $ic_id = 'ban';
            } else {
                if ( $ank['group_access'] > 7 && ( $ank['group_access'] < 10 || $ank['group_access'] > 14 ) ) {
                    if ( $ank['pol'] == 1 ) {
                        $ic_id = 1;
                    } else {
                        $ic_id = 2;
                    }
                } elseif ( ( $ank['group_access'] > 1 && $ank['group_access'] <= 7 ) || ( $ank['group_access'] > 10 && $ank['group_access'] <= 14 ) ) {
                    if ( $ank['pol'] == 1 ) {
                        $ic_id = 3;
                    } else {
                        $ic_id = 4;
                    }
                } else {
                    if ( $ank['pol'] == 1 ) {
                        $ic_id = 5;
                    } else {
                        $ic_id = 6;
                    }
                }
            }
            $icon = '<img src="/style/user/' . $ic_id . '.png" alt="" class="icon" id="icon_group" /> ';
        }
        return '<span class="image-avatar">' . $AVATAR . '</span> ' . $icon;
    }

    /**
     * / Функция выборки пользовательских данных
     * / Выводин данные из таблицы user
     * / и генериует аватар, иконки медалей и онлайна в массив
     * $ank['link'], $ank['avatar'], $ank['online'], 
     * $ank['medal'], $ank['icon']
     */
    static function get_user( $ID = 0, $photo = 1 )
    {
        /*
         * $ID    - ID юзера 
         * $photo - Параметр на выборку аватара
         */
        global $user;
        $ID                = (int) $ID;
        $ank               = array( );
        $ank['group_name'] = null;
        // Если вы авторизованы, и функция вызывает 
        // ваш ID, то просто берем данные из $user
        if ( $user['id'] == $ID ) {
            $ank = $user;
        } else {
            $ank = db::fetch('SELECT * FROM `user` WHERE `id` = "' . $ID . '" LIMIT 1', ARRAY_A);
        }
        // Если система или неопределенный юзер
        if ( $ID == 0 ) {
            $ank = array(
                 'id' => '0',
                'pol' => '1',
                'group_access' => '0',
                'level' => '999' 
            );
        } elseif ( !$ank ) {
            $ank = array(
                 'id' => '0',
                'pol' => '1',
                'group_access' => '0',
                'level' => '0' 
            );
        } else {
            $tmp_us            = db::fetch("SELECT `level`,`name` AS `group_name` FROM `user_group` WHERE `id` = '" . $ank['group_access'] . "' LIMIT 1", ARRAY_A);
            $ank['group_name'] = $tmp_us['group_name'];
            $ank['level']      = $tmp_us['level'];
        }

        // Если поставлен параметр выводить фото
        if ( $photo ) {
            $ank['avatar'] = get_avatar($ank['id'], 'thumbnail');
        }

        $ank['online'] = '';
        $ank['medal'] = '';
        
        // Иконка пользователя
        if ( db::count("SELECT COUNT(*) FROM `ban` WHERE `id_user` = '$ID' AND (`time` > '" . time() . "' OR `navsegda` = '1')") != 0 ) {
            $ic_id = 'ban';
        } else {
            if ( $ank['group_access'] > 7 && ( $ank['group_access'] < 10 || $ank['group_access'] > 14 ) ) {
                if ( $ank['pol'] == 1 ) {
                    $ic_id = 1;
                } else {
                    $ic_id = 2;
                }
            } elseif ( ( $ank['group_access'] > 1 && $ank['group_access'] <= 7 ) || ( $ank['group_access'] > 10 && $ank['group_access'] <= 14 ) ) {
                if ( $ank['pol'] == 1 ) {
                    $ic_id = 3;
                } else {
                    $ic_id = 4;
                }
            } else {
                if ( $ank['pol'] == 1 ) {
                    $ic_id = 5;
                } else {
                    $ic_id = 6;
                }
            }
        }
        $ank['icon'] = '<img src="/style/user/' . $ic_id . '.png" alt="" class="icon" id="icon_group" /> ';
        $ank['link'] = ' <a href="/info.php?id=' . $ID . '">' . text( $ank['nick'] ) . '</a> ';
        $ank['nick'] = text( $ank['nick'] );
        return $ank;
    }
}
