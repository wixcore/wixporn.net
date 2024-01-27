<?php 

class Widget 
{
	public $instance = array(); 

	/**
	* Заголовок виджета
	*/ 
	public $widget_title = ''; 

	/**
	* Описание виджета
	*/ 
	public $widget_description = ''; 

	/**
	* Если есть метод form, выводим форму редактора
	*/ 
	public $is_options = true; 

	/**
	* Иконка виджета в HTML формате
	*/ 
	public $widget_icon = ''; 

	public function __construct($args) 
	{
		$this->widget_title = (isset($args['title']) ? $args['title'] : ''); 
		$this->widget_description = (isset($args['description']) ? $args['description'] : ''); 
		$this->widget_icon = (isset($args['icon']) ? $args['icon'] : '<i class="fa fa-window-maximize" aria-hidden="true"></i>'); 
	}

	public function save($widget_id = null, $args) 
	{
		if ($widget_id) { 
			$this->instance = $this->update($this->instance, $_POST); 

			foreach($this->instance AS $key => $value) {
				$this->instance[$key] = db::esc($value); 
			}

			$data = array_merge($args, array(
				'instance' => $this->instance, 
			)); 
			
			update_option($widget_id, $data, 'widget');
		}
	}

	public function update($instance_old, $instance_new) 
	{
		unset($instance_new['widget_name']); 
		unset($instance_new['widget_id']); 
		unset($instance_new['area_id']); 

		return $instance_new; 
	}

	public function form($instance) 
	{
		return false; 
	}

	public function widget($instance) {
		echo '<div class="err">' . __('У класса %s отсутствует метод %s::widget()', get_class($this), get_class($this)) . '</div>'; 
	}

	public function setup($option_id = null) 
	{
		$option = get_option($option_id);
		if ($option) {
			$data = json_decode($option, true); 

			if (is_array($data['instance'])) {
				$this->instance = $data['instance']; 

				foreach($this->instance AS $key => $value) {
					$this->instance[$key] = $value; 
				}

				if (isset($this->instance['title'])) {
					$this->widget_title = $this->widget_title . ': <b>' . $this->instance['title'] . '</b>'; 
				}
			}
		} 
	}

	public function get_field($id) 
	{
		$instance = $this->instance; 

		if (isset($instance[$id])) {
			return $instance[$id]; 
		}

		return ''; 
	}
}