<?php 

$presets = jw_theme_presets(); 
$settings = jw_theme_settings(); 
$options = get_user_options(get_user_id(), 'justweb'); 
$opt = array_replace($settings, $options); 
?>
<form action="?do=justweb" method="POST" class="justweb-settings">
    <?php foreach($presets AS $key => $preset) : ?>
    	<div class="form-group">
	        <input id="<?php echo $key; ?>" type="radio" name="preset" value="<?php echo $key; ?>" <?php echo ($opt['preset'] == $key ? 'checked' : ''); ?> />
	        <label for="<?php echo $key; ?>"><?php echo $preset['name']; ?></label>
	        <div class="just-colors">
	        <?php foreach($preset['colors'] AS $color) : ?>
	            <span style="background: <?php echo $color; ?>"></span>
	        <?php endforeach; ?>
	        </div>
	    </div>
    <?php endforeach; ?>

	<div class="form-group">
		<button name="save_settings" class="button"><?php echo __t('Сохранить', LANGUAGE_DOMAIN); ?></button>
	</div>
</form>
<?