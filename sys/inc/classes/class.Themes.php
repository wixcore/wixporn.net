<?php 

class Themes
{
    private $file_headers = array(
    		'name'        => 'Theme Name',
    		'themeuri'    => 'Theme URI',
    		'description' => 'Description',
    		'author'      => 'Author',
    		'authoruri'   => 'Author URI',
    		'version'     => 'Version',
    		'status'      => 'Status',
    		'tags'        => 'Tags',
    		'textdomain'  => 'Text Domain',
    		'domainuri'   => 'Domain Path',
  	);


    public function listThemes($optionType = '') 
    {
        $path = get_themes_directory(); 
        $opdirbase = opendir($path);
        
        $themes = array(); 
        $sumHtml  = array(); 

        $registered = get_themes();

        if (!empty($registered)) {
            foreach($registered AS $key => $theme) {
                if (!is_dir($path . '/' . $key)) {
                    ds_theme_remove($key); 
                    unset($registered[$key]);
                }
            }
        }
        
        while ($filebase = readdir($opdirbase)) 
        {
            if (is_dir($path . '/' . $filebase) && !preg_match('/[\.]{1,2}/', $filebase)) {
              	if (is_file($path . '/' . $filebase . '/style.css')) {
                    if (isset($registered[$filebase])) {
                        $themes[] = $registered[$filebase];
                    } else {
                        $themes[] = $this->getThemeInfo($path . '/' . $filebase, $filebase); 
                    }
              	}
            }
        }
        
        return $themes; 
    }


    public function getThemeInfo($path, $file) 
    {

        $registered = get_themes();

        $info = array(); 
        if (is_file($path . '/style.css')) {
            $fileStyles = file_get_contents($path . '/style.css'); 
            
            foreach($this->file_headers AS $key => $value) {
                if (preg_match('|' . $value . ' ?: ?(.*)$|mi', $fileStyles, $matches)) {
                    $info[$key] = text($matches[1]);
                }
            }
        }

        $info['slug'] = basename($path);

        if (empty($info['name'])) {
            $info['name'] = $info['slug']; 
        }
        
        if (!isset($registered[$file])) {
            $info['active'] = 0; 
            ds_theme_add($file, $info);
        }

        return $info;
    }
}