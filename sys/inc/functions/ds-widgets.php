<?php 

/**
* Регистрирует стандартные виджеты и области виджетов
* @uses ./sys/inc/events.php
*/ 
function ds_widgets_init() 
{
	// Регистрируем область главной страницы
	add_widgets_area('home', __('Главная страница')); 

	do_event('ds_widgets_init'); 
}

/**
* Регистрирует облать для виджетов
* @return bolean 
*/ 
function add_widgets_area($uid, $title, $args = array()) 
{
	$areas = ds_get('ds_widgets_areas', array()); 

	if (!isset($areas[$uid])) {
		$areas[$uid] = array(
			'id' => $uid, 
			'title' => $title, 
			'params' => $args, 
		); 

		ds_set('ds_widgets_areas', $areas);  

		do_event('ds_add_widgets_area', $uid, $title, $args); 

		return true; 
	}

	do_event('error_add_widgets_area', $uid, $title, $args); 

	return false; 
}

/**
* Получает все облати для виджетов
* @return array
*/ 
function get_widgets_areas() 
{
	$areas = ds_get('ds_widgets_areas', array()); 
	return $areas; 
}

/**
* Получает информацию облати для виджетов
* @return array | null  
*/ 
function get_widgets_area($uid) 
{
	$areas = ds_get('ds_widgets_areas', array()); 

	if (isset($areas[$uid])) {
		return $areas[$uid]; 
	}

	return null; 
}

/**
* Отключает облать виджетов
* @return array | null  
*/ 
function remove_widgets_area($uid) 
{
	$areas = ds_get('ds_widgets_areas', array()); 

	if (isset($areas[$uid])) {
		unset($areas[$uid]); 

		ds_set('ds_widgets_areas', $areas); 
		return true; 
	}

	return false; 
}

/**
* Регистрирует новый виджет
* @return bolean 
*/ 
function register_widget($className) 
{
	$widgets = ds_get('ds_widgets', array()); 

	if (!class_exists($className)) {
		return false; 
	}

	if (!isset($widgets[$className])) {
		$widgets[$className] = array(
			'id' => $className, 
		); 

		ds_set('ds_widgets', $widgets);  
		do_event('ds_add_widgets', $className); 

		return true; 
	}

	return false; 
}

function get_widgets($area_id = null) 
{
	if ($area_id == null) {
		$widgets = ds_get('ds_widgets', array()); 
		return $widgets; 
	} else {
		$area_data = get_option('_widgets-' . $area_id); 

		$widgets = array(); 
		if ($area_data) {
			$widgets = json_decode($area_data, true); 
		}

		return $widgets; 
	}
}

function do_widgets($uid) 
{
	$area = get_widgets_area($uid); 

	if (isset($area['id'])) {
		$widgets = get_widgets($area['id']); 

		foreach($widgets AS $item) {
			if (class_exists($item['id'])) {
				$className = $item['id']; 
				$widget = new $className(); 

				if (isset($item['widget_id']))
					$widget->setup($item['widget_id']); 

				echo $widget->widget($widget->instance); 
			}
		}
	}
}