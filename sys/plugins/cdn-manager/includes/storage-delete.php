<?php 

if ($term['files'] > 0) {
	echo '<div class="alert alert-error">' . __('В этом хранилище, находится %s, которые занимают %s! Перед тем как приступить к удалению, вам необходимо перенести все файлы на сайт.', des2num($term['files'], array('файл', 'файла', 'файлов')), size_file($term['size'])) . '</div>'; 

    $percent = 0; 
    if ($term['size']) {
        $percent = ceil($term['size'] * 100 / $cdn['size']);  
    }
}