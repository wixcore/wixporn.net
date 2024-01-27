<?php 

require( '../sys/inc/core.php' );

$action = (isset($_GET['action']) ? $_GET['action'] : 'list'); 
$slug = (isset($_GET['slug']) ? $_GET['slug'] : ''); 

user_access( 'adm_themes', null, 'index.php?' . SID );

add_event('init_head_admin_theme', 'admin_widgets_add_scripts'); 
function admin_widgets_add_scripts() { 
	ds_theme_script_add(get_site_url('/sys/static/js/admin.widgets.js'), 'widgets-admin');
}

$set['title'] = __('Виджеты');

get_header_admin(); 

$areas = get_widgets_areas(); 
?>
<div class="widgets">
	<div class="widgets-areas col-3">
	<?
	foreach($areas AS $uid => $area) {
		?>
	    <div class="box-item">
	        <div class="list-item-title"><a href="?slug=<?php echo $uid; ?>&action=editor"><?php echo $area['title']; ?></a></div>

	        <?php if (isset($area['params']['description'])) : ?>
	        	<div class="list-item-description"><?php echo $area['params']['description']; ?></div>
	    	<?php endif; ?>
	    </div>
	    <?
	}
	?>
	</div>
	<?php if ($slug) : ?>
	<div class="col-6 widgets-area-editor">
		<div class="widgets-area sortable-ui" data-area="<?php echo $uid; ?>">
			<?php 
			$area_id = '_widgets-' . $uid; 
			$data = get_option($area_id); 

			$widgets = array(); 
			if ($data) {
				$widgets = json_decode($data, true); 
			}

			if (is_array($widgets)) {
				foreach($widgets AS $key => $elem) {
					if (class_exists($elem['id'])) {
						$className = $elem['id'];  
						$widget = new $className();

						if (isset($elem['widget_id'])) {
							$widget->setup($elem['widget_id']); 
						} else {
							$elem['widget_id'] = 0;    
						}
						?>
						<div class="widget-ui" data-widget="<?php echo $elem['id']; ?>" data-widget_id="<?php echo $elem['widget_id']; ?>">
							<span class="ui-move-handle ui-draggable-handle"><?php echo $widget->widget_icon; ?></span>
							<div class="widget-content">
								<div class="widget-title"><?php echo $widget->widget_title; ?></div>
								<div class="widget-links">
									<a class="widget-ui-edit">Редактировать</a><span> | </span><a class="ds-link-delete widget-ui-remove">Удалить</a>
								</div>
								<div class="widget-editor"><span class="button-process"></span></div>
							</div>
							<div class="widget-action">
								<a class="widget-up"><i class="fa fa-chevron-up"></i></a>
								<a class="widget-down"><i class="fa fa-chevron-down"></i></a>
							</div>
						</div>
						<?						
					}

				}				
			}
			?>
		</div>

		<button class="button button-primary widget-area-add">Добавить виджет</button>

		<textarea style="display: none;" name="widgets_area_content"><?php echo text($data); ?></textarea>
	</div>

	<div class="widgets-list col-3">
		<?php 
		$widgets = get_widgets(); 

		if (isset($widgets)) {
			foreach($widgets AS $className => $args) {
				$widget = new $className(); 
				?>
				<div class="widget-ui" data-draggable="1" data-widget="<?php echo $className; ?>" data-loaded="-1">
					<span class="ui-move-handle"><?php echo $widget->widget_icon; ?></span> 
					<div class="widget-content">
						<span class="widget-title" title="<?php echo $widget->widget_description; ?>"><?php echo $widget->widget_title; ?></span>
					</div>
					<div class="widget-action">
						<button class="button widget-add"><?php echo __('Вставить'); ?></button>
					</div>
				</div>		
				<?		
			}
		}
		?>
	</div>
	<?php endif; ?>
</div>
<?

get_footer_admin(); 
