<?php 

require( '../sys/inc/core.php' );

user_access( 'plugins', null, 'index.php?' . SID );

$action = (isset($_GET['action']) ? $_GET['action'] : 'list'); 
$slug = (isset($_GET['slug']) ? $_GET['slug'] : ''); 
$set[ 'title' ] = __('Плагины');

if ($action == 'list') {
    add_event('ds_admin_title_action', function($links = array()) {
        $links['add'] = array(
            'title' => __('Установить'), 
            'url'   => get_admin_url('plugins.php', 'action=add'), 
        ); 
        
        return $links; 
    });     
}

if ($action == 'add') {
    $set[ 'title' ] = __('Установка плагинов');
}

if (isset($_FILES['file']) && is_file($_FILES['file']['tmp_name'])) {
    if (ds_plugin_upload($_FILES['file']['tmp_name'])) {
        $_SESSION['message'] = __('Плагин успешно установлен'); 
        ds_redirect('?'); 
    }
} 

if ($action && $slug) {
    if ($action == 'install') {
        if (ds_plugin_install($slug)) {
            $_SESSION['message'] = __('Плагин установлен');  
        }
    }
    
    if ($action == 'activate') {
        if (ds_plugin_activate($slug)) {
            $_SESSION['message'] = __('Плагин активирован'); 
        }
    }
    
    if ($action == 'deactivate') {
        if (ds_plugin_deactivate($slug)) {
            $_SESSION['message'] = __('Плагин деактивирован'); 
        }
    }

    if ($action == 'remove') {
        if (ds_plugin_remove($slug)) {
            $_SESSION['message'] = __('Плагин удален из системы'); 
        }
    }

    if (!is_errors()) {
        ds_redirect('?'); 
    }
}

get_header_admin(); 
?>

<?php if ($action == 'add') : ?>
<div class="form-plugins-upload">
    <form action="?" method="POST" enctype="multipart/form-data">
        <input type="file" name="file"><input type="submit" value="Загрузить">
    </form>
</div>
<?php endif; ?>

<?
$plugins = new Plugins(); 
$list = $plugins->listPlugins(); 

if ($action == 'add') {
    ?>
    <div class="cards cards-plugins" id="result">
        <?php echo __('Загрузка данных...'); ?>
    </div>

    <script>
    jQuery(function($) {
        $.ajax(ajax_url, {
            data: 'action=plugins_search_api', 
            success: function(resp) {
                $('#result').html(resp); 
            }
        }); 

        $(document).on('click', '.plugin-install', function() {
            var b = $(this).addClass('button-process').html('<?php echo __('Установка'); ?>...'); 

            $.ajax(ajax_url, {
                data: 'action=plugins_install_api&slug=' + $(this).data('slug'), 
                dataType: 'json', 
                success: function(resp) {
                    b.removeClass('plugin-install'); 

                    if (resp.status == 'success') {
                        b.removeClass('button-process').addClass('button-installed').html('<i class="fa fa-check" aria-hidden="true"></i> ' + resp.message).attr('href', 'javascript:void(0)'); 

                        setTimeout(function() {
                            b.removeClass('button-installed').addClass('button-primary').attr('href', resp.href).html(resp.title); 
                        }, 2000); 
                    } else {
                        b.removeClass('button-process').addClass('button-error').html(resp.message); 

                        if (resp.errors) {
                            var wrapErrors = $('<div/>', {
                                class: 'plugin-install-errors', 
                            }); 

                            $(b).replaceWith(wrapErrors); 

                            for(var key in resp.errors) {
                                $(wrapErrors).append($('<div/>', {
                                    class: 'alert alert-error', 
                                    text: resp.errors[key], 
                                })); 
                            }
                        }
                    }
                }
            }); 

            return false; 
        }); 
    }); 
    </script>
    <?
}

else {
    if (empty($list)) : 
        do_event('ds_plugins_empty'); 
        ?>
        <div class="empty empty-plugins">
            <?php echo __('Нет установленных плагинов'); ?>
        </div>
        <?
    endif; 

    if (!empty($list)) : 
        do_event('ds_plugins_list'); 

        ?><div class="list"><?
        foreach($list AS $plugin) 
        {
            $plug_action = array(); 
            $plug_action['activate'] = '<a href="?slug=' . $plugin['slug'] . '&action=' . ($plugin['active'] == 1 ? 'deactivate' : 'activate') . '">' . 
                        __($plugin['active'] == 1 ? 'Деактивировать' : 'Активировать') . '</a>';

            $plug_action = use_filters('ds_plugin_' . $plugin['slug'] . '_action', $plug_action); 
            $plug_action = use_filters('ds_plugins_action', $plug_action); 

            if ($plugin['active'] == 0) {
                $plug_action[] = '<a class="ds-link-delete" href="?slug=' . $plugin['slug'] . '&action=remove">' . __('Удалить') . '</a>';
            }
            ?>
            <div class="list-item <?php echo ($plugin['active'] == 1 ? 'active' : ''); ?>">
                <div class="list-item-title"><?php echo $plugin['name']; ?></div>
                <div class="list-item-description"><?php echo $plugin['full']; ?></div>
                <div class="list-item-action">
                    <?php echo join(' | ', $plug_action); ?>
                </div>
            </div>
            <?
        }
        ?></div><?
    endif; 
}
get_footer_admin(); 