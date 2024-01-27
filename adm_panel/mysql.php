<?php
require( '../sys/inc/core.php' );

user_access( 'adm_mysql', null, 'index.php?' . SID );

$set[ 'title' ] = 'MySQL запрос';

if ( isset( $_GET[ 'set' ] ) && $_GET[ 'set' ] == 'set' && isset( $_POST[ 'query' ] ) ) {
    $sql = trim( $_POST[ 'query' ] );
    if ( $conf[ 'phpversion' ] == 5 ) {
        include_once H . 'sys/inc/sql_parser.php';
        $sql = SQLParser::getQueries( $sql );
    } else {
        $sql = split( ";(\n|\r)*", $sql );
    }
    $k_z    = 0;
    $k_z_ok = 0;
    for ( $i = 0; $i < count( $sql ); $i++ ) {
        if ( $sql[ $i ] != '' ) {
            $k_z++;
            if ( db::query( $sql[ $i ]) ) {
                $k_z_ok++;
            }
        }
    }
    if ( $k_z_ok > 0 ) {
        if ( $k_z_ok == 1 && $k_z = 1 )
            msg( "Запрос успешно выполнен" );
        else
            msg( "Выполнено $k_z_ok запросов из $k_z" );
        admin_log( 'Админка', 'MySQL', "Выполнено $k_z_ok запрос(ов)" );
    }
}
get_header_admin(); 
echo "<form method=\"post\" action=\"mysql.php?set=set\">\n";
echo "<textarea name=\"query\" ></textarea><br />\n";
echo "<input value=\"Выполнить\" type=\"submit\" />\n";
echo "</form>\n";
if ( user_access( 'adm_panel_show' ) ) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "&laquo;<a href='tables.php'>Залить файлом</a><br />\n";
    echo "</div>\n";
}
get_footer_admin(); 