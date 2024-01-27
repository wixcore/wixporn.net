<?php 

class Plugins
{
    private $file_headers = array(
        'Name'        => 'Plugin Name',
        'PluginURI'    => 'Plugin URI',
        'Description' => 'Description',
        'Author'      => 'Author',
        'AuthorURI'   => 'Author URI',
        'Version'     => 'Version',
        'Status'      => 'Status',
        'Tags'        => 'Tags',
        'TextDomain'  => 'Text Domain',
        'DomainURI'  => 'Domain Path',
  	);

    public function init($optionType) 
    {
        $action = (!empty($_GET['action']) ? $_GET['action'] : ''); 
        $elid = (!empty($_GET['elid']) ? $_GET['elid'] : ''); 
        
        if ($action == 'activation') {
            ds_plugin_activate($elid);
            redirect('?module=' . ds_admin_get_page()); 
        }

        if ($action == 'deactivation') {
            ds_plugin_deactivate($elid);
            redirect('?module=' . ds_admin_get_page()); 
        }

        if ($action == 'remove') {
            if (!is_plugin_active( $elid )) {
                ds_plugin_remove($elid);
                redirect('?module=' . ds_admin_get_page()); 
            }
        }
    }
    
    public function getButtonRemove($btn, $post) 
    {
        $plugin = $post['slug'];

        if (is_plugin_active( $plugin )) {
            $btn['class'] = 'btn-sm btn-danger hidden-xs disabled'; 
        }
        
        return $btn; 
    }

    public function getButtonActivation($btn, $post) 
    {
        $plugin = $post['slug'];

        if (is_plugin_active($plugin)) {
            $link = array(
                'url' => '?module=plugins&action=deactivation&elid=%slug%', 
                'icon' => 'fa fa-stop', 
                'class' => 'btn-sm btn-secondary hidden-xs', 
                //'name' => __('Отключить'), 
            ); 
        }

        else {
            $link = array(
                'url' => '?module=plugins&action=activation&elid=%slug%', 
                'icon' => 'fa fa-play', 
                'class' => 'btn-sm btn-primary hidden-xs', 
                //'name' => __('Включить'), 
            ); 
        }
        
        return array_merge($btn, $link); 
    }

    public function listPlugins($optionType = '') 
    {
        $path = PATH_PLUGINS . DIRECTORY_SEPARATOR; 
        $opdirbase = opendir($path);
        
        $plugins = array(); 
        $sumHtml  = array(); 

        $registered = ds_plugins();

        if (!empty($registered)) {
            foreach($registered AS $key => $plugin) {
                if (!is_file(PATH_PLUGINS . $plugin['script'])) {
                    //ds_plugin_deactivate($key); 
                    //add_error(__('Плагин %s был деактивирован, файл плагина не найден.', '<pre>' . $plugin['script'] . '</pre>')); 
                    unset($registered[$key]);
                }
            }
        }
        
        while ($filebase = readdir($opdirbase)) 
        {
            if (is_dir($path . $filebase) && !preg_match('/[\.]{1,2}/', $filebase)) {
                $plugdir = opendir($path . $filebase); 

                while($plugfile = readdir($plugdir)) 
                {
                    if (preg_match('/\.php$/m', $plugfile)) {
                        $read = file_get_contents($path . $filebase . DIRECTORY_SEPARATOR . $plugfile); 

                        if (strpos($read, 'Plugin Name') !== false) {
                            $plugin = $this->getPluginInfo($path . $filebase . DIRECTORY_SEPARATOR . $plugfile); 
                            $plugin['active'] = (is_plugin_active($plugin['slug']) ? 1 : 0); 
                            $plugins[] = $plugin;
                        }
                    }
                }
            } elseif (is_file($path . $filebase)) {
                if (preg_match('/\.php$/m', $filebase)) {
                    $read = file_get_contents($path . $filebase); 

                    if (strpos($read, 'Plugin Name') !== false) {
                        $plugin = $this->getPluginInfo($path . $filebase); 
                        $plugin['active'] = (is_plugin_active($plugin['slug']) ? 1 : 0); 
                        $plugins[] = $plugin;
                    }
                }
            }
        }
        
        return $plugins; 
    }


    public function getPluginInfo($pathfile, $add = true) 
    {
        $registered = ds_plugins();

        $info = array(); 
        if (is_file($pathfile)) {
            $plugin = file_get_contents($pathfile); 
            
            foreach($this->file_headers AS $key => $value) {
                if (preg_match('|' . $value . ' ?: ?(.*)$|mi', $plugin, $matches)) {
                    $info[strtolower($key)] = text($matches[1]);
                }
            }
        }

        $info['script'] = str_replace(PATH_PLUGINS, '', $pathfile); 
        
        if (empty($info['textdomain'])) {
            $info['slug'] = basename(dirname($info['script'])); 

            if (empty($info['slug'])) {
                $info['slug'] = basename($info['script']);
            }
        } else {
            $info['slug'] = $info['textdomain']; 
        }

        $info['full'] = (!empty($info['description']) ? $info['description'] . '<br />' : '');
        
        $more = array(); 
        if (!empty($info['version'])) {
            $more[] = __('Версия') . ': ' . $info['version'];
        }

        if (!empty($info['status'])) {
            $more[] = __('Статус') . ': ' . $info['status'];
        }

        if (!empty($info['author'])) {
            if (!empty($info['authoruri'])) {
                $more[] = __('Автор') . ': ' . '<a target="_blank" href="' . $info['pluginuri'] . '">' . $info['author'] . '</a>';
            } else {
                $more[] = __('Автор') . ': ' . $info['author'];
            }
        }

        if (!empty($info['pluginuri'])) {
            $more[] = '<a target="_blank" href="' . $info['pluginuri'] . '">' . __('Подробнее') . '</a>';
        }
        
        $info['full'] .= join(' | ', $more); 
        
        if (empty($info['name'])) {
            $info['name'] = substr($info['slug'], strrpos($info['slug'], '/') + 1); 
        }

        if ($add == true && !isset($registered[$info['slug']])) {
            $info['active'] = 0; 
            ds_plugin_add($info['slug'], $info);
        }

        return $info;
    }
}