<?php 

$ds_files_unlink = array(
	'/adm_panel/referals.php', 
	'/adm_panel/statistic.php', 
); 

foreach($ds_files_unlink AS $file_delete) {
	if (is_file(ROOTPATH . $file_delete)) {
		unlink(ROOTPATH . $file_delete); 
	}	
}

$plugins = ds_plugins(); 

if ($plugins) {
	foreach($plugins AS $key => $value) {
	    foreach($value AS $k => $v) {
	        unset($value[$k]); 
	        $value[strtolower($k)] = $v; 
	    }
	    $plugins[$key] = $value; 
	}

	update_option('ds_plugins', $plugins, 'plugins'); 	
}
