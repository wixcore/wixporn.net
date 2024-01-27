<?php 

class JW_Widget_Online extends Widget 
{
	public function __construct() {
		parent::__construct(array(
			'title' => __t('Пользователи онлайн', LANGUAGE_DOMAIN), 
			'icon' => '<i class="fa fa-group" aria-hidden="true"></i>', 
		));
	}

	public function form($instance) 
	{
		
	}

	public function widget($instance) 
	{
		$counters = justweb_counters(); 
		?>
		<div class="widget widget-online">
			<a class="widget-link-online" href="<?php echo get_site_url('/online.php'); ?>">
				<i class="fa fa-users" aria-hidden="true"></i> <?php echo __t('Сейчас онлайн: %s', LANGUAGE_DOMAIN, '<span data-type="users_online" data-count="' . $counters['users_online']['count'] . '">' . $counters['users_online']['count'] . '</span>'); ?>
			</a>
		</div>
		<?
	}
}