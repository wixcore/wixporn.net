<script>
$(function() {
    $("#tabs").tabs();
});
</script>

<style>
.just-colors {
    display: flex;
    width: 100%;
    max-width: 400px;
    justify-content: space-between;
}
.just-colors input {
    display: inline-block;
    height: 50px;
    flex: auto;
    border: 0;
    padding: 0;
    margin: 0;
    background: 0;
}
.just-colors span {
  display: inline-block;
  height: 50px; 
  vertical-align: middle;
  flex: auto;
}
.justweb-input label {
    display: block;
    margin: 5px 0;
    font-weight: bold;
    font-size: 18px;
    cursor: pointer;
}
</style>

<?php 
$presets = jw_theme_presets(); 
$settings = jw_theme_settings(); 
?>

<div id="tabs" class="ds-tabs">
    <ul>
        <li><a href="#justweb-general"><?php echo __t('Общие', LANGUAGE_DOMAIN); ?></a></li>
        <li><a href="#justweb-preset"><?php echo __t('Дизайн', LANGUAGE_DOMAIN); ?></a></li>
    </ul>
    <div id="justweb-general">
        <?php 
        $forms = new Forms(); 

        $fields = array(
            array(
                'field_title' => __t('Логотип', LANGUAGE_DOMAIN), 
                'field_name' => 'logotype', 
                'field_value' => text($settings['logotype']), 
                'field_type' => 'text', 
            ), 
            array(
                'field_title' => __t('Копирайт', LANGUAGE_DOMAIN), 
                'field_name' => 'copyright', 
                'field_value' => text($settings['copyright']), 
                'field_type' => 'text', 
            ), 
        ); 

        foreach($fields AS $field) {
            $forms->add_field($field); 
        }
        
        echo $forms->display();
        ?>
    </div>
    <div id="justweb-preset">
        <h3><?php echo __t('Основной стиль', LANGUAGE_DOMAIN); ?></h3>

        <div class="jw-preset">
        <?php foreach($presets AS $key => $preset) : ?>
            <input id="<?php echo $key; ?>" type="radio" name="preset" value="<?php echo $key; ?>" <?php echo ($settings['preset'] == $key ? 'checked' : ''); ?> />
            <label for="<?php echo $key; ?>"><?php echo $preset['name']; ?></label>
            <div class="just-colors">
            <?php foreach($preset['colors'] AS $color) : ?>
                <span style="background: <?php echo $color; ?>"></span>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<p>
    <button name="justweb_save" class="button" type="submit"><?php echo __t('Сохранить', LANGUAGE_DOMAIN); ?></button>
</p>