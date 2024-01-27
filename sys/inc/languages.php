<?php 

/**
* Получает языковой пакет 
* $type - Путь языкового пакета
* Ядро    : core
* Плагины : plugins/{slug}
* Шаблоны : themes/{slug}
* 
* @return array
*/ 

function get_translations($type = 'core', $slug = null)  
{
    $lang = get_language();  

    $translations = ds_get('ds_translations', array()); 

    if (isset($translations[$type][$lang])) {
        return use_filters('ds_get_translations', $translations[$type][$lang], $type, $slug); 
    }

    $require_lang = null; 

    if ($type == 'plugins') {
        if (is_file(ROOTPATH . '/sys/plugins/' . $slug . '/languages/' . $lang . '.lng')) {
            $require_lang = ROOTPATH . '/sys/plugins/' . $slug . '/languages/' . $lang . '.lng'; 
        } elseif (is_file(ROOTPATH . '/sys/languages/plugins/' . $slug . '/' . $lang . '.lng')) {
            $require_lang = ROOTPATH . '/sys/languages/plugins/' . $slug . '/' . $lang . '.lng'; 
        }
    }
    
    if ($type == 'themes') {
        if (is_file(ROOTPATH . '/style/themes/' . $slug . '/languages/' . $lang . '.lng')) {
            $require_lang = ROOTPATH . '/style/themes/' . $slug . '/languages/' . $lang . '.lng'; 
        } elseif (is_file(ROOTPATH . '/sys/languages/themes/' . $slug . '/' . $lang . '.lng')) {
            $require_lang = ROOTPATH . '/sys/languages/themes/' . $slug . '/' . $lang . '.lng'; 
        }
    }

    if ($type == 'core') {
        $require_lang = ROOTPATH . '/sys/languages/core/' . $lang . '.lng'; 
    }

    $require_lang = use_filters('ds_require_translations', $require_lang, $type, $lang);  

    $translations[$type][$lang] = array(); 

    if ($require_lang !== null && is_file($require_lang)) { 
        $language_strings = json_decode(file_get_contents($require_lang), true); 

        if (is_array($language_strings)) {
            $translations[$type][$lang] = $language_strings; 
        }
    }

    ds_set('ds_translations', $translations);  

    return use_filters('ds_get_translations', $translations[$type][$lang], $type, $slug);  
}

function is_translations($type = 'core', $lang = null) 
{
    if (!$lang) 
        return false; 

    if ($type == 'core' && is_file(ROOTPATH . '/sys/languages/core/' . $lang . '.lng')) {
        return true; 
    } elseif ($type == 'plugins') {
        if (is_file(ROOTPATH . '/sys/plugins/' . $slug . '/languages/' . $lang . '.lng')) {
            return true; 
        } elseif (is_file(ROOTPATH . '/sys/languages/plugins/' . $slug . '/' . $lang . '.lng')) {
            return true; 
        }
    } elseif ($type == 'themes') {
        if (is_file(ROOTPATH . '/style/themes/' . $slug . '/languages/' . $lang . '.lng')) {
            return true; 
        } elseif (is_file(ROOTPATH . '/sys/languages/themes/' . $slug . '/' . $lang . '.lng')) {
            return true; 
        }
    }
    return use_filters('ds_is_translations', false, $type, $lang); 
}

function get_sprintf_arguments($args = array(), $offset = 1) 
{
    $args4eval = array(); 
    for ($i = $offset; $i < count($args); $i++) {
        $args4eval[] = '$args[' . $i . ']';
    }

    return $args4eval; 
}

/** 
* Системные переводы ядра CMS-Social
* Рекомендуем не использовать эту функцию
* Вместо этого используйте __p() для плагинов
* и __t() для шаблонов движка
*/
function __($string) 
{
    $args = func_get_args(); 
    $args4eval = get_sprintf_arguments($args, 1);
    $translations = get_translations('core'); 

    if (isset($translations[$string])) {
    	  $string = (!empty($translations[$string]) ? $translations[$string] : $string); 
    } else {
        $string = use_filters('core_translate_string_not_found', $string, $translations); 
    }

    if (count($args4eval) > 0) {
        eval('$string = sprintf($string, ' . implode(', ', $args4eval) . ');');
    }
    
    return $string; 
}

function __p($string, $plug) 
{
    $args = func_get_args(); 
    $args4eval = get_sprintf_arguments($args, 2);
    $translations = get_translations('plugins', $plug); 

    if (isset($translations[$string])) {
        $string = (!empty($translations[$string]) ? $translations[$string] : $string); 
    } else {
        $string = use_filters('plugin_translate_string_not_found', $string, $translations, $plug); 
    }

    if (count($args4eval) > 0) {
        eval('$string = sprintf($string, ' . implode(', ', $args4eval) . ');');
    }
    
    return $string; 
}

function __t($string, $them) 
{
    $args = func_get_args(); 
    $args4eval = get_sprintf_arguments($args, 2);
    $translations = get_translations('themes', $them); 

    if (isset($translations[$string])) {
        $string = (!empty($translations[$string]) ? $translations[$string] : $string); 
    } else {
        $string = use_filters('theme_translate_string_not_found', $string, $translations, $them); 
    }

    if (count($args4eval) > 0) {
        eval('$string = sprintf($string, ' . implode(', ', $args4eval) . ');');
    }
    
    return $string; 
}

function get_core_languages() 
{
    $languages = array(
       'af' => array(
           'code'         => 'af', 
           'native_name'  => 'Afrikaans', 
           'english_name' => 'Afrikaans', 
        ),
       'ar' => array(
           'code'         => 'ar', 
           'native_name'  => 'العربية', 
           'english_name' => 'Arabic', 
        ),
       'ary' => array(
           'code'         => 'ary', 
           'native_name'  => 'العربية المغربية', 
           'english_name' => 'Moroccan Arabic', 
        ),
       'as' => array(
           'code'         => 'as', 
           'native_name'  => 'অসমীয়া', 
           'english_name' => 'Assamese', 
        ),
       'az' => array(
           'code'         => 'az', 
           'native_name'  => 'Azərbaycan dili', 
           'english_name' => 'Azerbaijani', 
        ),
       'azb' => array(
           'code'         => 'azb', 
           'native_name'  => 'گؤنئی آذربایجان', 
           'english_name' => 'South Azerbaijani', 
        ),
       'bel' => array(
           'code'         => 'bel', 
           'native_name'  => 'Беларуская мова', 
           'english_name' => 'Belarusian', 
        ),
       'bg_BG' => array(
           'code'         => 'bg_BG', 
           'native_name'  => 'Български', 
           'english_name' => 'Bulgarian', 
        ),
       'bn_BD' => array(
           'code'         => 'bn_BD', 
           'native_name'  => 'বাংলা', 
           'english_name' => 'Bengali (Bangladesh)', 
        ),
       'bo' => array(
           'code'         => 'bo', 
           'native_name'  => 'བོད་ཡིག', 
           'english_name' => 'Tibetan', 
        ),
       'bs_BA' => array(
           'code'         => 'bs_BA', 
           'native_name'  => 'Bosanski', 
           'english_name' => 'Bosnian', 
        ),
       'ca' => array(
           'code'         => 'ca', 
           'native_name'  => 'Català', 
           'english_name' => 'Catalan', 
        ),
       'ceb' => array(
           'code'         => 'ceb', 
           'native_name'  => 'Cebuano', 
           'english_name' => 'Cebuano', 
        ),
       'cs_CZ' => array(
           'code'         => 'cs_CZ', 
           'native_name'  => 'Čeština', 
           'english_name' => 'Czech', 
        ),
       'cy' => array(
           'code'         => 'cy', 
           'native_name'  => 'Cymraeg', 
           'english_name' => 'Welsh', 
        ),
       'da_DK' => array(
           'code'         => 'da_DK', 
           'native_name'  => 'Dansk', 
           'english_name' => 'Danish', 
        ),
       'de_DE_formal' => array(
           'code'         => 'de_DE_formal', 
           'native_name'  => 'Deutsch (Sie)', 
           'english_name' => 'German (Formal)', 
        ),
       'de_CH' => array(
           'code'         => 'de_CH', 
           'native_name'  => 'Deutsch (Schweiz)', 
           'english_name' => 'German (Switzerland)', 
        ),
       'de_CH_informal' => array(
           'code'         => 'de_CH_informal', 
           'native_name'  => 'Deutsch (Schweiz, Du)', 
           'english_name' => 'German (Switzerland, Informal)', 
        ),
       'de_AT' => array(
           'code'         => 'de_AT', 
           'native_name'  => 'Deutsch (Österreich)', 
           'english_name' => 'German (Austria)', 
        ),
       'de_DE' => array(
           'code'         => 'de_DE', 
           'native_name'  => 'Deutsch', 
           'english_name' => 'German', 
        ),
       'dzo' => array(
           'code'         => 'dzo', 
           'native_name'  => 'རྫོང་ཁ', 
           'english_name' => 'Dzongkha', 
        ),
       'el' => array(
           'code'         => 'el', 
           'native_name'  => 'Ελληνικά', 
           'english_name' => 'Greek', 
        ),
       'en_US' => array(
           'code'         => 'en_US', 
           'native_name'  => 'English (United States)', 
           'english_name' => 'English (United States)', 
        ),
       'en_GB' => array(
           'code'         => 'en_GB', 
           'native_name'  => 'English (UK)', 
           'english_name' => 'English (UK)', 
        ),
       'en_CA' => array(
           'code'         => 'en_CA', 
           'native_name'  => 'English (Canada)', 
           'english_name' => 'English (Canada)', 
        ),
       'en_AU' => array(
           'code'         => 'en_AU', 
           'native_name'  => 'English (Australia)', 
           'english_name' => 'English (Australia)', 
        ),
       'en_NZ' => array(
           'code'         => 'en_NZ', 
           'native_name'  => 'English (New Zealand)', 
           'english_name' => 'English (New Zealand)', 
        ),
       'en_ZA' => array(
           'code'         => 'en_ZA', 
           'native_name'  => 'English (South Africa)', 
           'english_name' => 'English (South Africa)', 
        ),
       'eo' => array(
           'code'         => 'eo', 
           'native_name'  => 'Esperanto', 
           'english_name' => 'Esperanto', 
        ),
       'es_VE' => array(
           'code'         => 'es_VE', 
           'native_name'  => 'Español de Venezuela', 
           'english_name' => 'Spanish (Venezuela)', 
        ),
       'es_AR' => array(
           'code'         => 'es_AR', 
           'native_name'  => 'Español de Argentina', 
           'english_name' => 'Spanish (Argentina)', 
        ),
       'es_CR' => array(
           'code'         => 'es_CR', 
           'native_name'  => 'Español de Costa Rica', 
           'english_name' => 'Spanish (Costa Rica)', 
        ),
       'es_MX' => array(
           'code'         => 'es_MX', 
           'native_name'  => 'Español de México', 
           'english_name' => 'Spanish (Mexico)', 
        ),
       'es_ES' => array(
           'code'         => 'es_ES', 
           'native_name'  => 'Español', 
           'english_name' => 'Spanish (Spain)', 
        ),
       'es_UY' => array(
           'code'         => 'es_UY', 
           'native_name'  => 'Español de Uruguay', 
           'english_name' => 'Spanish (Uruguay)', 
        ),
       'es_CL' => array(
           'code'         => 'es_CL', 
           'native_name'  => 'Español de Chile', 
           'english_name' => 'Spanish (Chile)', 
        ),
       'es_GT' => array(
           'code'         => 'es_GT', 
           'native_name'  => 'Español de Guatemala', 
           'english_name' => 'Spanish (Guatemala)', 
        ),
       'es_PE' => array(
           'code'         => 'es_PE', 
           'native_name'  => 'Español de Perú', 
           'english_name' => 'Spanish (Peru)', 
        ),
       'es_CO' => array(
           'code'         => 'es_CO', 
           'native_name'  => 'Español de Colombia', 
           'english_name' => 'Spanish (Colombia)', 
        ),
       'et' => array(
           'code'         => 'et', 
           'native_name'  => 'Eesti', 
           'english_name' => 'Estonian', 
        ),
       'eu' => array(
           'code'         => 'eu', 
           'native_name'  => 'Euskara', 
           'english_name' => 'Basque', 
        ),
       'fa_IR' => array(
           'code'         => 'fa_IR', 
           'native_name'  => 'فارسی', 
           'english_name' => 'Persian', 
        ),
       'fi' => array(
           'code'         => 'fi', 
           'native_name'  => 'Suomi', 
           'english_name' => 'Finnish', 
        ),
       'fr_FR' => array(
           'code'         => 'fr_FR', 
           'native_name'  => 'Français', 
           'english_name' => 'French (France)', 
        ),
       'fr_CA' => array(
           'code'         => 'fr_CA', 
           'native_name'  => 'Français du Canada', 
           'english_name' => 'French (Canada)', 
        ),
       'fr_BE' => array(
           'code'         => 'fr_BE', 
           'native_name'  => 'Français de Belgique', 
           'english_name' => 'French (Belgium)', 
        ),
       'fur' => array(
           'code'         => 'fur', 
           'native_name'  => 'Friulian', 
           'english_name' => 'Friulian', 
        ),
       'gd' => array(
           'code'         => 'gd', 
           'native_name'  => 'Gàidhlig', 
           'english_name' => 'Scottish Gaelic', 
        ),
       'gl_ES' => array(
           'code'         => 'gl_ES', 
           'native_name'  => 'Galego', 
           'english_name' => 'Galician', 
        ),
       'gu' => array(
           'code'         => 'gu', 
           'native_name'  => 'ગુજરાતી', 
           'english_name' => 'Gujarati', 
        ),
       'haz' => array(
           'code'         => 'haz', 
           'native_name'  => 'هزاره گی', 
           'english_name' => 'Hazaragi', 
        ),
       'he_IL' => array(
           'code'         => 'he_IL', 
           'native_name'  => 'עִבְרִית', 
           'english_name' => 'Hebrew', 
        ),
       'hi_IN' => array(
           'code'         => 'hi_IN', 
           'native_name'  => 'हिन्दी', 
           'english_name' => 'Hindi', 
        ),
       'hr' => array(
           'code'         => 'hr', 
           'native_name'  => 'Hrvatski', 
           'english_name' => 'Croatian', 
        ),
       'hsb' => array(
           'code'         => 'hsb', 
           'native_name'  => 'Hornjoserbšćina', 
           'english_name' => 'Upper Sorbian', 
        ),
       'hu_HU' => array(
           'code'         => 'hu_HU', 
           'native_name'  => 'Magyar', 
           'english_name' => 'Hungarian', 
        ),
       'hy' => array(
           'code'         => 'hy', 
           'native_name'  => 'Հայերեն', 
           'english_name' => 'Armenian', 
        ),
       'id_ID' => array(
           'code'         => 'id_ID', 
           'native_name'  => 'Bahasa Indonesia', 
           'english_name' => 'Indonesian', 
        ),
       'is_IS' => array(
           'code'         => 'is_IS', 
           'native_name'  => 'Íslenska', 
           'english_name' => 'Icelandic', 
        ),
       'it_IT' => array(
           'code'         => 'it_IT', 
           'native_name'  => 'Italiano', 
           'english_name' => 'Italian', 
        ),
       'ja' => array(
           'code'         => 'ja', 
           'native_name'  => '日本語', 
           'english_name' => 'Japanese', 
        ),
       'jv_ID' => array(
           'code'         => 'jv_ID', 
           'native_name'  => 'Basa Jawa', 
           'english_name' => 'Javanese', 
        ),
       'ka_GE' => array(
           'code'         => 'ka_GE', 
           'native_name'  => 'ქართული', 
           'english_name' => 'Georgian', 
        ),
       'kab' => array(
           'code'         => 'kab', 
           'native_name'  => 'Taqbaylit', 
           'english_name' => 'Kabyle', 
        ),
       'kk' => array(
           'code'         => 'kk', 
           'native_name'  => 'Қазақ тілі', 
           'english_name' => 'Kazakh', 
        ),
       'km' => array(
           'code'         => 'km', 
           'native_name'  => 'ភាសាខ្មែរ', 
           'english_name' => 'Khmer', 
        ),
       'kn' => array(
           'code'         => 'kn', 
           'native_name'  => 'ಕನ್ನಡ', 
           'english_name' => 'Kannada', 
        ),
       'ko_KR' => array(
           'code'         => 'ko_KR', 
           'native_name'  => '한국어', 
           'english_name' => 'Korean', 
        ),
       'ckb' => array(
           'code'         => 'ckb', 
           'native_name'  => 'كوردی‎', 
           'english_name' => 'Kurdish (Sorani)', 
        ),
       'lo' => array(
           'code'         => 'lo', 
           'native_name'  => 'ພາສາລາວ', 
           'english_name' => 'Lao', 
        ),
       'lt_LT' => array(
           'code'         => 'lt_LT', 
           'native_name'  => 'Lietuvių kalba', 
           'english_name' => 'Lithuanian', 
        ),
       'lv' => array(
           'code'         => 'lv', 
           'native_name'  => 'Latviešu valoda', 
           'english_name' => 'Latvian', 
        ),
       'mk_MK' => array(
           'code'         => 'mk_MK', 
           'native_name'  => 'Македонски јазик', 
           'english_name' => 'Macedonian', 
        ),
       'ml_IN' => array(
           'code'         => 'ml_IN', 
           'native_name'  => 'മലയാളം', 
           'english_name' => 'Malayalam', 
        ),
       'mn' => array(
           'code'         => 'mn', 
           'native_name'  => 'Монгол', 
           'english_name' => 'Mongolian', 
        ),
       'mr' => array(
           'code'         => 'mr', 
           'native_name'  => 'मराठी', 
           'english_name' => 'Marathi', 
        ),
       'ms_MY' => array(
           'code'         => 'ms_MY', 
           'native_name'  => 'Bahasa Melayu', 
           'english_name' => 'Malay', 
        ),
       'my_MM' => array(
           'code'         => 'my_MM', 
           'native_name'  => 'ဗမာစာ', 
           'english_name' => 'Myanmar (Burmese)', 
        ),
       'nb_NO' => array(
           'code'         => 'nb_NO', 
           'native_name'  => 'Norsk bokmål', 
           'english_name' => 'Norwegian (Bokmål)', 
        ),
       'ne_NP' => array(
           'code'         => 'ne_NP', 
           'native_name'  => 'नेपाली', 
           'english_name' => 'Nepali', 
        ),
       'nl_NL' => array(
           'code'         => 'nl_NL', 
           'native_name'  => 'Nederlands', 
           'english_name' => 'Dutch', 
        ),
       'nl_BE' => array(
           'code'         => 'nl_BE', 
           'native_name'  => 'Nederlands (België)', 
           'english_name' => 'Dutch (Belgium)', 
        ),
       'nl_NL_formal' => array(
           'code'         => 'nl_NL_formal', 
           'native_name'  => 'Nederlands (Formeel)', 
           'english_name' => 'Dutch (Formal)', 
        ),
       'nn_NO' => array(
           'code'         => 'nn_NO', 
           'native_name'  => 'Norsk nynorsk', 
           'english_name' => 'Norwegian (Nynorsk)', 
        ),
       'oci' => array(
           'code'         => 'oci', 
           'native_name'  => 'Occitan', 
           'english_name' => 'Occitan', 
        ),
       'pa_IN' => array(
           'code'         => 'pa_IN', 
           'native_name'  => 'ਪੰਜਾਬੀ', 
           'english_name' => 'Punjabi', 
        ),
       'pl_PL' => array(
           'code'         => 'pl_PL', 
           'native_name'  => 'Polski', 
           'english_name' => 'Polish', 
        ),
       'ps' => array(
           'code'         => 'ps', 
           'native_name'  => 'پښتو', 
           'english_name' => 'Pashto', 
        ),
       'pt_PT_ao90' => array(
           'code'         => 'pt_PT_ao90', 
           'native_name'  => 'Português (AO90)', 
           'english_name' => 'Portuguese (Portugal, AO90)', 
        ),
       'pt_PT' => array(
           'code'         => 'pt_PT', 
           'native_name'  => 'Português', 
           'english_name' => 'Portuguese (Portugal)', 
        ),
       'pt_BR' => array(
           'code'         => 'pt_BR', 
           'native_name'  => 'Português do Brasil', 
           'english_name' => 'Portuguese (Brazil)', 
        ),
       'pt_AO' => array(
           'code'         => 'pt_AO', 
           'native_name'  => 'Português de Angola', 
           'english_name' => 'Portuguese (Angola)', 
        ),
       'rhg' => array(
           'code'         => 'rhg', 
           'native_name'  => 'Ruáinga', 
           'english_name' => 'Rohingya', 
        ),
       'ro_RO' => array(
           'code'         => 'ro_RO', 
           'native_name'  => 'Română', 
           'english_name' => 'Romanian', 
        ),
       'ru_RU' => array(
           'code'         => 'ru_RU', 
           'native_name'  => 'Русский', 
           'english_name' => 'Russian', 
        ),
       'sah' => array(
           'code'         => 'sah', 
           'native_name'  => 'Сахалыы', 
           'english_name' => 'Sakha', 
        ),
       'snd' => array(
           'code'         => 'snd', 
           'native_name'  => 'سنڌي', 
           'english_name' => 'Sindhi', 
        ),
       'si_LK' => array(
           'code'         => 'si_LK', 
           'native_name'  => 'සිංහල', 
           'english_name' => 'Sinhala', 
        ),
       'sk_SK' => array(
           'code'         => 'sk_SK', 
           'native_name'  => 'Slovenčina', 
           'english_name' => 'Slovak', 
        ),
       'skr' => array(
           'code'         => 'skr', 
           'native_name'  => 'سرائیکی', 
           'english_name' => 'Saraiki', 
        ),
       'sl_SI' => array(
           'code'         => 'sl_SI', 
           'native_name'  => 'Slovenščina', 
           'english_name' => 'Slovenian', 
        ),
       'sq' => array(
           'code'         => 'sq', 
           'native_name'  => 'Shqip', 
           'english_name' => 'Albanian', 
        ),
       'sr_RS' => array(
           'code'         => 'sr_RS', 
           'native_name'  => 'Српски језик', 
           'english_name' => 'Serbian', 
        ),
       'sv_SE' => array(
           'code'         => 'sv_SE', 
           'native_name'  => 'Svenska', 
           'english_name' => 'Swedish', 
        ),
       'sw' => array(
           'code'         => 'sw', 
           'native_name'  => 'Kiswahili', 
           'english_name' => 'Swahili', 
        ),
       'szl' => array(
           'code'         => 'szl', 
           'native_name'  => 'Ślōnskŏ gŏdka', 
           'english_name' => 'Silesian', 
        ),
       'ta_IN' => array(
           'code'         => 'ta_IN', 
           'native_name'  => 'தமிழ்', 
           'english_name' => 'Tamil', 
        ),
       'te' => array(
           'code'         => 'te', 
           'native_name'  => 'తెలుగు', 
           'english_name' => 'Telugu', 
        ),
       'th' => array(
           'code'         => 'th', 
           'native_name'  => 'ไทย', 
           'english_name' => 'Thai', 
        ),
       'tl' => array(
           'code'         => 'tl', 
           'native_name'  => 'Tagalog', 
           'english_name' => 'Tagalog', 
        ),
       'tr_TR' => array(
           'code'         => 'tr_TR', 
           'native_name'  => 'Türkçe', 
           'english_name' => 'Turkish', 
        ),
       'tt_RU' => array(
           'code'         => 'tt_RU', 
           'native_name'  => 'Татар теле', 
           'english_name' => 'Tatar', 
        ),
       'tah' => array(
           'code'         => 'tah', 
           'native_name'  => 'Reo Tahiti', 
           'english_name' => 'Tahitian', 
        ),
       'ug_CN' => array(
           'code'         => 'ug_CN', 
           'native_name'  => 'ئۇيغۇرچە', 
           'english_name' => 'Uighur', 
        ),
       'uk' => array(
           'code'         => 'uk', 
           'native_name'  => 'Українська', 
           'english_name' => 'Ukrainian', 
        ),
       'ur' => array(
           'code'         => 'ur', 
           'native_name'  => 'اردو', 
           'english_name' => 'Urdu', 
        ),
       'uz_UZ' => array(
           'code'         => 'uz_UZ', 
           'native_name'  => 'O‘zbekcha', 
           'english_name' => 'Uzbek', 
        ),
       'vi' => array(
           'code'         => 'vi', 
           'native_name'  => 'Tiếng Việt', 
           'english_name' => 'Vietnamese', 
        ),
       'zh_TW' => array(
           'code'         => 'zh_TW', 
           'native_name'  => '繁體中文', 
           'english_name' => 'Chinese (Taiwan)', 
        ),
       'zh_CN' => array(
           'code'         => 'zh_CN', 
           'native_name'  => '简体中文', 
           'english_name' => 'Chinese (China)', 
        ),
       'zh_HK' => array(
           'code'         => 'zh_HK', 
           'native_name'  => '香港中文版 ', 
           'english_name' => 'Chinese (Hong Kong)', 
        ),
    );

    return use_filters('ds_core_languages', $languages);   
}