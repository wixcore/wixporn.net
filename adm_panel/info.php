<?php

require('../sys/inc/core.php' );

user_access( 'adm_info', null, 'index.php?' . SID );

if (isset($_GET['from']) && isset($_GET['to'])) {
    $dirupdate = ROOTPATH . '/sys/upgrade/update'; 
    $from = str_replace('.', '', $_GET['from']); 
    $to = str_replace('.', '', $_GET['to']); 

    if (is_dir($dirupdate)) {
        $updiropen = opendir($dirupdate);

        while ($filebase = readdir($updiropen)) {
            if (preg_match('/update\-([0-9]+)_to_([0-9]+)\.php$/m', $filebase, $matches)) {
                if ($matches[1] >= $from) {
                    require $dirupdate . '/' . $filebase;                  
                }
                unlink($dirupdate . '/' . $filebase);  
            }
        }
    } 
}

$set[ 'title' ] = __('Общая информация');
get_header_admin(); 

include_once H . 'sys/inc/testing.php';

if ( isset( $err ) ) {
    if ( is_array( $err ) ) {
        foreach ( $err as $key => $value ) {
            echo "<div class='err'>$value</div>\n";
        }
    } else
        echo "<div class='err'>$err</div>\n";
}

if ( user_access( 'adm_panel_show' ) ) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "</div>\n";
}

get_footer_admin(); 