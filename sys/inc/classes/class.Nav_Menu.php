<?php 

class Nav_Menu
{
	/**
	* Формирует HTML код елеманта меню с подменю
	* $elem:access - Уровень доступа к элементу меню
	* 
	* @return string
	*/ 
	public function elem_wrap($elem, $args, $depth) 
	{
		$classes = array();
		$classes[] = 'submenu'; 
		
		$submenu = sprintf($args['wrap_submenu'],
			join(' ', $classes), 
			$this->walk($elem['children'], $args, $depth + 1), 
		); 
		
		$classes = array();
		$classes[] = 'menu-item'; 
		
		if ($submenu) {
			$classes[] = 'has-children'; 
		}
		
		$output = sprintf($args['wrap_item'], 
			join(' ', $classes), 
			$this->elem_link($elem, $args, $depth),
			(!empty($submenu) ? $submenu : ''),
		); 

		return $output; 
	}

	/**
	* Формирует HTML код контейнера елеманта меню 
	* $elem:access - Уровень доступа к элементу меню
	*
	* @return string
	*/ 
	public function elem_item($elem, $args, $depth) 
	{
		$classes = array();
		$classes[] = 'menu-item'; 

		$output = sprintf($args['wrap_item'], 
			join(' ', $classes), 
			$this->elem_link($elem, $args, $depth),
			'',
		); 

		return $output;  
	}

	/**
	* Формирует HTML код ссылки елеманта меню 
	* $elem:title  - Заголовок ссылки 
	* $elem:url    - URL ссылки 
	* $elem:rel    - Атрибут rel 
	*
	* @return string
	*/ 
	public function elem_link($elem, $args, $depth) 
	{
		$attr = array(); 
		if ($elem['url']) {
			$attr['href'] = get_site_url($elem['url']); 
		}

		if (!empty($elem['rel'])) {
			$attr['rel'] = $elem['rel']; 
		}

		foreach($attr AS $k => $v) {
			$attr[$k] = $k . '="' . $v . '"'; 
		}

		$output = '<a ' . join(' ', $attr) . '>' . $elem['title'] . '</a>';
		return $output; 
	}

	/**
	* Создает древовидное меню 
	* @return string
	*/ 
	public function walk($elems, $args, $depth = 1) 
	{
		$default = array(
			'title' => '', 
			'url'   => '', 
		); 

		foreach($elems AS $elem) 
		{
			$elem = array_replace($default, $elem); 

			if (isset($elem['children'])) {
				$html_item = $this->elem_wrap($elem, $args, $depth); 
			} else {
				$html_item = $this->elem_item($elem, $args, $depth);  
			}

			$output[] = $html_item;
		}

		return join('', $output);
	}

	/**
	* Выводит или возвращает сформированый HTML код меню\
	* @return string
	*/ 
	public function display($elems, $args) 
	{
		// HTML код меню 
		$output = $this->walk($elems, $args); 

		// Классы меню родительского меню
		if (empty($args['container_class'])) {
			$classes = array(); 
			$classes[] = 'menu'; 

			if ($args['location']) {
				$classes[] = 'menu-' . $args['location']; 
			}	
			
			$container_class = use_filters('ds_nav_menu_container_class', join(' ', $classes)); 		
		} else {
			$container_class = use_filters('ds_nav_menu_container_class', $args['container_class']); 
		}

		// ID контейнера меню 
		$container_id = (!empty($args['container_id']) ? $args['container_id'] : 'menu_' . $args['location']); 

		// Выводим отформатированый HTML код меню
		echo sprintf($args['wrap_menu'],
			$container_id, 
			$container_class, 
			$output, 
		); 
	}
}