<?php 

$ds_files_unlink = array(
	'/sys/inc/chmod_test.php', 
); 

foreach($ds_files_unlink AS $file_delete) {
	if (is_file(ROOTPATH . $file_delete)) {
		unlink(ROOTPATH . $file_delete); 
	}	
}

db::query("ALTER TABLE `user` CHANGE `level` `level` INT(1) NOT NULL DEFAULT '0'"); 