<?php 

function ds_nav_menu($args = array(), $elems = null) 
{
	$default = array(
		'location' => '', 
		'handler' => new Nav_Menu, 
		'wrap_menu' => '<ul id="%1$s" class="%2$s">%3$s</ul>', 
		'wrap_submenu' => '<ul class="%1$s">%2$s</ul>', 
		'wrap_item' => '<li class="%1$s">%2$s%3$s</li>', 
		'user_access' => 'all', 
	); 

	$args = array_merge($default, $args); 

	if ($elems == null) {
		$elems = array(); 
	}
	
	if ($args['location']) {
		call_user_func(array(&$args['handler'], 'display'), $elems, $args);
	}
}