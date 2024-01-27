<?php
require( '../sys/inc/core.php' );

$action = (isset($_GET['action']) ? $_GET['action'] : 'list'); 
$slug = (isset($_GET['slug']) ? $_GET['slug'] : ''); 

user_access( 'adm_themes', null, 'index.php?' . SID );

if ($action && $slug) {
    if ($action == 'activate') {
        update_option('set_them', $slug, 'autoload'); 
        $_SESSION['message'] = __('Тема успешно включена');
    }  

    ds_redirect('?');   
}


$set['title'] = __('Темы оформления');

get_header_admin(); 

$themes = new Themes(); 
$list = $themes->listThemes(); 

?><div class="list"><?
foreach($list AS $theme) 
{
    $them_action = array(); 
    $them_action['activate'] = '<a href="?slug=' . $theme['slug'] . '&action=' . (is_theme_active($theme['slug']) ? 'deactivate' : 'activate') . '">' . 
                (is_theme_active($theme['slug']) ? __('Деактивировать') : __('Активировать')) . '</a>';

    $them_action = use_filters('ds_theme_' . $theme['slug'] . '_action', $them_action); 
    $them_action = use_filters('ds_themes_action', $them_action); 

    if (!is_theme_active($theme['slug'])) {
        $them_action[] = '<a class="ds-link-delete" href="?slug=' . $theme['slug'] . '&action=remove">' . __('Удалить') . '</a>';
    }

    $theme['full'] = (!empty($theme['description']) ? $theme['description'] . '<br />' : '');
    
    $more = array(); 
    if (!empty($theme['version'])) {
        $more[] = __('Версия') . ': ' . $theme['version'] . ' ' . (!empty($theme['status']) ? $theme['status'] : '');
    }

    if (!empty($theme['author'])) {
        if (!empty($theme['authoruri'])) {
            $more[] = __('Автор') . ': ' . '<a target="_blank" href="' . $theme['authoruri'] . '">' . $theme['author'] . '</a>';
        } else {
            $more[] = __('Автор') . ': ' . $theme['author'];
        }
    }

    if (!empty($theme['themeuri'])) {
        $more[] = '<a target="_blank" href="' . $theme['themeuri'] . '">' . __('Подробнее') . '</a>';
    }
    
    $theme['full'] .= join(' | ', $more); 
    ?>
    <div class="list-item <?php echo (is_theme_active($theme['slug']) ? 'active' : ''); ?>">
        <div class="list-item-title"><?php echo $theme['name']; ?></div>
        <div class="list-item-description"><?php echo $theme['full']; ?></div>
        <div class="list-item-action">
            <?php echo join(' | ', $them_action); ?>
        </div> 
    </div>
    <?
}
?></div><?

get_footer_admin(); 