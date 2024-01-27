<?php 

user_access( 'adm_mysql', null, 'index.php?' . SID );

$set['title'] = __('Настройка профиля'); 
get_header_admin(); 


get_footer_admin(); 