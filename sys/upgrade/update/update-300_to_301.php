<?php 

if (is_file(ROOTPATH . '/pages/chat.php')) {
	unlink(ROOTPATH . '/pages/chat.php'); 
}

db::query("ALTER TABLE `mail_contacts` ADD `time_update` INT(11) NOT NULL AFTER `status`"); 