<?php 

$query = (isset($_GET['q']) ? $_GET['q'] : ''); 
do_event('ds_search_init', $query); 

$set['title'] = __('Поиск по сайту'); 
get_header(); 

$registered_search = get_register_search(); 

$mask = array(
	'%query%' => $query, 
	'%icon%' => '', 
	'%after_item%' => '', 
	'%before_item%' => '', 
	'%class_list%' => '', 
	'%after%' => '', 
	'%before%' => '', 
); 

echo '<div class="list list-search">'; 
foreach($registered_search AS $key => $search) 
{
	if (is_callable($search['callback'])) {
		$count = call_user_func($search['callback'], $query);

		$mask_list = use_filters('ds_search_mask_list_item', array_merge($mask, array(
			'%title%' => $search['name'], 
		    '%class_list%' => 'list-item', 
		    '%counter%' => $count, 
		    '%link%' => str_replace('%query%', $query, $search['url']), 
		))); 

		$template_list = use_filters('ds_search_template_list_item', '<div class="%class_list%"><a class="%class_list%-link" href="%link%">%icon% <span class="%class_list%-title">%title%</span> <span class="%class_list%-counter">%counter%</span></a></div>');

		echo str_replace(array_keys($mask_list), array_values($mask_list), $template_list); 
	}
}
echo '</div>'; 

get_footer(); 