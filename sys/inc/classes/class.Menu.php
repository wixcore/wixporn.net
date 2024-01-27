<?php 

/**
* Класс отвечает за формирование меню навигации
**/

class Menu 
{
	private $registry; 
	private $default = array(
		'title'     => '', 
		'url'       => '', 
		'access'    => '', 
		'position'  => 1, 
		'counter'   => '', 
		'icon'      => '', 
		'slug'      => '', 
	); 

	public function add_menu($name = 'menu', $args) 
	{
		if (!is_array($args)) return false; 
		$menu = array_merge($this->default, $args); 
		$this->registry[$name]['items'][] = $menu; 
	}

	public function get_menu($name) 
	{ 
		return $this->registry[$name]; 
	}

	public function get_template_menu($menu, $class = 'menu', $offset = 0) 
	{
		if (empty($menu)) return ; 

		usort($menu, "sort_position"); 
		$ulClasses = array($class); 

		$liContent = ''; 

		foreach($menu AS $key => $value) {

			$submenu = false; 
	    	if (!empty($value['submenu'])) {
	    		$submenu = $this->get_template_menu($value['submenu'], 'submenu', $offset + 1); 
	    	}

			if (!empty($value['access']) && !is_user_access($value['access'])) {
				continue; 
			}

			$liClasses = array($class . '-item'); 
			if (!empty($submenu)) {
				$liClasses[] = 'submenu-exists'; 
			}
			if (isset($value['class'])) {
				$liClasses[] = $value['class']; 
			}

			$liContent .= '<li class="' . join(' ', $liClasses) . '">';

			// Ссылка
	    	if (!empty($value['url'])) {
	    		$liContent .= '<a class="menu-link" href="' . $value['url'] . '" title="' . $value['title'] . '">'; 
	    	}

	    	// Иконка меню
	    	if ($value['icon']) {
	    		if (preg_match('/^fa\-([A-z0-9\-]+)$/m', $value['icon'])) {
	    			$liContent .= '<i class="fa ' . $value['icon'] . '"></i>';
	    		} else {
	    			$liContent .= '<span class="' . $class . '-icon"><img class="icon" src="' . $value['icon'] . '" alt="*" /></span>';
	    		}
	    	}

	    	// Заголовок
	    	$liContent .= '<span class="' . $class . '-title">' . $value['title'] . '</span>'; 

	    	if (!empty($value['url'])) {
	    		$liContent .= '</a>'; 
	    	}

	    	if ($submenu) {
	    		$liContent .= $submenu; 
	    	}

			$liContent .= '</li>';
		}

		if ($offset) {
			$ulClasses[] = 'menu-offset-' . $offset; 
		}

		$toggleMenu = ''; 
		if ($offset && $liContent) {
			$toggleMenu = '<a href="javascript:void(0)" class="menu-toggle"></a>';
		}


		return ($liContent ? $toggleMenu . '<ul class="' . join(' ', $ulClasses) . '">' . $liContent . '</ul>' : ''); 
	}
}
