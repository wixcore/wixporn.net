<?php 

class Widget_Text extends Widget
{
	public function __construct() {
		parent::__construct(array(
			'title' => __('Текст'), 
			'icon' => '<i class="fa fa-font" aria-hidden="true"></i>', 
		));
	}

	public function form($instance) 
	{
		?>
		<p><input type="text" name="title" placeholder="<?php echo __('Заголовок'); ?>" value="<?php echo text($this->get_field('title')); ?>" /></p>
		<p><textarea type="text" name="text" placeholder="<?php echo __('Текст'); ?>.."><?php echo text($this->get_field('text')); ?></textarea></p>
		<?
	}

	public function widget($instance) {
		?>
		<div class="widget widget-text">
			<div class="widget-title"><?php echo text($this->get_field('title')); ?></div>
			<div class="widget-content"><?php echo output_text($this->get_field('text')); ?></div>
		</div>
		<?
	}
}