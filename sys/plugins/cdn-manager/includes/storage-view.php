<?php 

$percent = 0; 
if ($term['size']) {
    $percent = ceil($term['size'] * 100 / $cdn['size']);  
}


echo '<button class="sss">Test</button>';
echo '<button class="sss2">Test2</button>';

echo '<h3>' . __p('Общая информация', 'cdn-manager') . '</h3>'; 

$info = db::fetch("SELECT SUM(size) AS size FROM files"); 
echo size_file($info['size']); 

echo '<div class="cdn-window">'; 
echo __p('Файлов: %s', 'cdn-manager', '<span class="cdn-files-count">' . $term['files'] . '</span>') . '<br />'; 
echo __p('Занято: %s', 'cdn-manager', '<span class="cdn-size-uses">' . size_file($term['size']) . '</span>') . '<br />'; 
echo __p('Свободно: %s', 'cdn-manager', '<span class="cdn-size-avail">' . size_file($cdn['size'] - $term['size']) . '</span>') . '<br />'; 
echo __p('Заполнено на %s', 'cdn-manager', '<span class="cdn-uses-percent">' . $percent . '%</span>'); 
echo '<div class="progress"><div style="width: ' . $percent . '%" class="progress-bar"></div></div>';
echo '</div>';  

echo '<button id="move_files_to_local" data-id="' . $cdn_id . '" class="btn btn-primary">' . __p('Перенести на сайт', 'cdn-manager') . '</button> '; 
echo '<button id="move_files_to_storage" data-id="' . $cdn_id . '" class="btn btn-default">' . __p('Перенести в хранилище', 'cdn-manager') . '</button>'; 