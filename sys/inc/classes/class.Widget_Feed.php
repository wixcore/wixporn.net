<?php 

class Widget_Feed extends Widget
{
	public function __construct() {
		parent::__construct(array(
			'title' => __('Новости друзей'), 
			'icon' => '<i class="fa fa-bullhorn" aria-hidden="true"></i>', 
		));
	}

	public function form($instance) 
	{
		$ajax_load_more = $this->get_field('ajax_load_more') ?: 'scroll'; 
		$ajax_p_str = $this->get_field('p_str') ?: 5; 
		?>
		<div class="form-group">
			<label for="wf-pstr" class="d-block label-title"><?php echo __('Записей на страницу'); ?></label>
			<input id="wf-pstr" type="tel" name="p_str" placeholder="<?php echo __('По умолчанию: %s', 5); ?>" value="<?php echo (int) $ajax_p_str; ?>" />

			<div class="label-title"><?php echo __('Метод пагинации виджета'); ?></div>

			<div class="form-control form-checkbox-group">
				<label class="d-block">
					<input type="radio" name="ajax_load_more" value="scroll" <?php echo ($ajax_load_more != 'scroll' ?: 'checked'); ?> /> 
					<?php echo __('При прокрутке страницы'); ?></label>
				
				<label class="d-block">
					<input type="radio" name="ajax_load_more" value="button" <?php echo ($ajax_load_more != 'button' ?: 'checked'); ?> /> 
					<?php echo __('Кнопка (показать ещё)'); ?></label>
			</div>
		</div>
		<?
	}

	public function widget($instance) 
	{
		if (!is_user()) return ;

		$p_str = ($instance['p_str'] ? $instance['p_str'] : 5); 
		$ajax_load_more = $this->get_field('ajax_load_more') ?: 'scroll';

		?>
		<div class="widget widget-feed">
			<div class="posts posts-feed" id="widget-feed">
			<?php 
			$args = array(
				'user_id' => get_user_id(), 
				'p_str' => ($instance['p_str'] ? $instance['p_str'] : 5), 
			); 

			$query = new DB_Feeds($args); 
			foreach($query->items AS $feed) {
				ds_output_feed($feed); 
			}
			?>
			</div>

			<?php if ($query->pages > 1) : ?>
			<div class="button button-primary more-feed" data-p_str="<?php echo $p_str; ?>" 
								   data-container=".posts-feed" 
								   data-ajaxType="<?php echo $ajax_load_more; ?>" 
								   data-pages="<?php echo $query->pages; ?>" 
								   data-paged="1" 
								   data-user="<?php echo get_user_id(); ?>">
				<span><?php echo __('Показать ещё'); ?></span>
			</div>
			<?php endif; ?>
		</div>
		<?
	}
}