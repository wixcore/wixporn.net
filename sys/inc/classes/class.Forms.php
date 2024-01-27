<?php 

class Forms
{
    private $fields = array(); 
    private $fields_default = array(
        'field_title'         => '', 
        'field_type'          => '', 
        'field_name'          => '', 
        'field_desc'          => '', 
        'field_value'         => '', 
        'field_after'         => '', 
        'field_before'        => '', 
        'field_placeholder'   => '', 
        'field_attr'          => array(), 
    );
    
    private $attr = array(); 
    
    public function __construct($action = '', $method = 'post') {
        $this->attr = array(
            'action' => $action, 
            'method' => $method, 
        ); 
    }

    public function get_form_array() 
    {
        $attr = array(); 
        $attr['action'] = $this->attr['action'];
        $attr['method'] = $this->attr['method'];

        return array(
            'attr' => $this->get_attributes($attr), 
            'fields' => $this->fields, 
        ); 
    }

    public function get_attributes($attributes) {
        $attrHtml = array(); 

        if (is_array($attributes)) {
            foreach($attributes AS $key => $value) {
                $attrHtml[] = $key . '="' . $value . '"';
            }
        }

        return join(' ', $attrHtml); 
    }

    public function add_field($args, $section_id = 'auto') {
        if (!isset($args['field_type'])) {
            $args['field_type'] = 'text';
        }

        if (preg_match('/^(text|password|tel|email|number)$/i', $args['field_type'])) {
            $field = $this->input($args); 
        } elseif ($args['field_type'] == 'hidden') {
            $field = $this->hidden($args); 
        } elseif ($args['field_type'] == 'captcha') {
            $field = $this->captcha($args); 
        } elseif ($args['field_type'] == 'select') {
            $field = $this->select($args); 
        } elseif ($args['field_type'] == 'textarea') {
            $field = $this->textarea($args); 
        } elseif ($args['field_type'] == 'editor') {
            $field = $this->editor($args); 
        } elseif ($args['field_type'] == 'checkbox') {
            $field = $this->checkbox($args); 
        } elseif ($args['field_type'] == 'radio') {
            $field = $this->radio($args); 
        } elseif ($args['field_type'] == 'submit') {
            return $this->button($args); 
        } elseif ($args['field_type'] == 'date') {
            $field = $this->date($args); 
        }
        
        $this->fields[$section_id][] = $field;
    }

    public function get_field($args) {
        if (!isset($args['field_type'])) {
            $args['field_type'] = 'text';
        }

        if (preg_match('/^(text|password|tel|email|number)$/i', $args['field_type'])) {
            return $this->input($args); 
        } elseif ($args['field_type'] == 'hidden') {
            return $this->hidden($args); 
        } elseif ($args['field_type'] == 'captcha') {
            return $this->captcha($args); 
        } elseif ($args['field_type'] == 'select') {
            return $this->select($args); 
        } elseif ($args['field_type'] == 'textarea') {
            return $this->textarea($args); 
        } elseif ($args['field_type'] == 'editor') {
            return $this->editor($args); 
        } elseif ($args['field_type'] == 'checkbox') {
            return $this->checkbox($args); 
        } elseif ($args['field_type'] == 'submit') {
            return $this->button($args); 
        } elseif ($args['field_type'] == 'date') {
            return $this->date($args); 
        }
    }

    public function checkbox($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);
        
        $html = '<div class="form-group">'; 
        $html .= '<div class="form-control form-checkbox-group">'; 

        $html .= '<label class="d-block"><input type="checkbox" value="' . $attr['field_value'] . '" name="' . $attr['field_name'] . '"' 
                      . ($attr['field_checked'] != 0 ? 'checked' : '') . ' ' . $this->get_attributes($attr['field_attr']) . ' /> ' 
                      . $attr['field_title'] . '</label>';

        $html .= '</div>';

        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    public function radio($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);
        
        $html = '<div class="form-group">'; 
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Radio' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }

        $html .= '<div class="input-group">'; 
        if (is_array($attr['field_values'])) {
            foreach($attr['field_values'] AS $value) {
                $html .= '<label class="d-block"><input type="radio" value="' . $value['value'] . '" name="' . $attr['field_name'] . '"' 
                      . ($attr['field_value'] == $value['value'] ? 'checked' : '') . ' ' . $this->get_attributes($attr['field_attr']) . ' /> ' 
                      . $value['title'] . '</label>';
            }
        }
        $html .= '</div>';

        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    public function select($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);
        
        $html = '<div class="form-group">'; 
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Select' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }
        $html .= '<div class="input-group"><select class="form-control" id="Select' . $attr['field_name'] . '" name="' . $attr['field_name'] . '" ' . $this->get_attributes($attr['field_attr']) . ' >'; 
        if (is_array($attr['field_values'])) {
            foreach($attr['field_values'] AS $value) {
                $html .= '<option value="' . $value['value'] . '" ' . ($attr['field_value'] == $value['value'] ? 'selected' : '') . '>' . $value['title'] . '</option>';
            }
        }
        $html .= '</select></div>';

        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    public function date($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);

        $html = '<div class="form-group">';
        
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Input' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }
        $html .= '<div class="input-group">'; 
        if (!empty($attr['field_before'])) {
            $html .= '<div class="input-group-prepend"><span class="input-group-text">' . $attr['field_before'] . '</span></div>';
        }
        
        $html .= '<input class="form-control" id="Input' . $attr['field_name'] . '" type="' . $attr['field_type'] . '" name="' . $attr['field_name'] . '" value="' . $attr['field_value'] . '" placeholder="' . (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : '') . '" ' . $this->get_attributes($attr['field_attr']) . '  />'; 

        if (!empty($attr['field_after'])) {
            $html .= '<div class="input-group-append"><span class="input-group-text">' . $attr['field_after'] . '</span></div>';
        }
        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
        
    public function input($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);

        $html = '<div class="form-group">';
        
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Input' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }
        $html .= '<div class="input-group">'; 
        if (!empty($attr['field_before'])) {
            $html .= '<div class="input-group-prepend"><span class="input-group-text">' . $attr['field_before'] . '</span></div>';
        }
        $html .= '<input class="form-control" id="Input' . $attr['field_name'] . '" type="' . $attr['field_type'] . '" name="' . $attr['field_name'] . '" value="' . $attr['field_value'] . '" placeholder="' . (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : '') . '" ' . $this->get_attributes($attr['field_attr']) . '  />'; 

        if (!empty($attr['field_after'])) {
            $html .= '<div class="input-group-append"><span class="input-group-text">' . $attr['field_after'] . '</span></div>';
        }
        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
        
    public function captcha($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);

        $html = '<div class="form-group">';
        
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Input' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }
        $html .= '<div class="input-group input-captcha">'; 
        if (!empty($attr['field_before'])) {
            $html .= '<div class="input-group-prepend"><span class="input-group-text">' . $attr['field_before'] . '</span></div>';
        }
        $html .= '<img src="' . get_site_url('/captcha.php?v=' . time()) . '" alt="Captcha" onclick="(function(e){e.src=e.src.replace(/v=([0-9]+)/g,\'v=\' + Date.now())}(this))" /><input class="form-control" id="Input' . $attr['field_name'] . '" type="text" name="' . $attr['field_name'] . '" value="' . $attr['field_value'] . '" placeholder="' . (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : '') . '" ' . $this->get_attributes($attr['field_attr']) . '  />'; 

        if (!empty($attr['field_after'])) {
            $html .= '<div class="input-group-append"><span class="input-group-text">' . $attr['field_after'] . '</span></div>';
        }
        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
    
    public function hidden($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);
        $html = '<input id="Input' . $attr['field_name'] . '" type="hidden" name="' . $attr['field_name'] . '" value="' . $attr['field_value'] . '" ' . $this->get_attributes($attr['field_attr']) . ' />'; 
        return $html;
    }
    
    public function textarea($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);
        
        $html = '<div class="form-group">'; 
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Textarea' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }

        $html .= '<div class="input-group">'; 
        $html .= '<textarea rows="5" class="form-control" id="Textarea' . $attr['field_name'] . '" name="' . $attr['field_name'] . '" placeholder="' . (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : '') . '" ' . $this->get_attributes($attr['field_attr']) . '>' . $attr['field_value'] . '</textarea>'; 
        $html .= '</div>';


        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    public function editor($attr) 
    {
        $attr = array_merge($this->fields_default, $attr);
        $hash = md5(get_salt() . $attr['field_name']); 
        $data = get_text_array($attr['field_value']); 

        $html = '<div class="form-group">'; 
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Textarea' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }

        $html .= '<div class="input-group">'; 
        $html .= get_editor($attr['field_name'], input_value_text(isset($data['content']) ? $data['content'] : $attr['field_value']), array(
            'placeholder' => (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : ''), 
        )); 

        if (is_user()) {
            $html .= '<div class="ds-form-attach">'; 
            $html .= '<div class="choose">'; 
            $html .= '<span class="choose-attachment" data-hash="' . $hash . '"> ' . __('Прикрепить файл') . '</span>';
            $html .= '<div class="choose-types" data-hash="' . $hash . '">';
            $all_media = get_media_types(); 

            foreach($all_media AS $type => $media) {
                if ($media['attachments'] === true) {
                    $html .= '<a data-hash="' . $hash . '" data-type="' . $type . '" data-term="0" data-accept="' . join(',', $media['accept']) . '" class="choose-type choose-type-' . $type . ' load-files"><i class="fa ' . $media['icons']['attachments'] . '"></i> ' . $media['labels']['title'] . '</a>'; 
                }
            }

            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            $attachments = array(); 
            
            if (!empty($data['data']['attachments'])) {
                foreach($data['data']['attachments'] AS $attach_id) {
                    $file = get_file($attach_id); 

                    if (!$file) {
                        continue;
                    }

                    $thumbnail = ''; 
                    if (is_file_thumbnail($file['id'], 'thumbnail')) {
                        $thumbnail = get_file_thumbnail_url($file['id'], 'thumbnail'); 
                    }

                    $attachments[] = '<div style="' . ($thumbnail ? 'background-image: url('.$thumbnail.')' : '') . '" class="attachments-item"><input type="hidden" value="' . $attach_id . '" name="attachments[]"><i class="' . get_file_icon($file, true) . '"></i><span class="title">' . text($file['title']) . '</span><span class="remove">×</span></div>'; 
                }
            }

            $html .= '<div data-hash="' . $hash . '" id="attachments-' . $hash . '" class="attachments">'.join('', $attachments).'</div>';
            $html .= '<div data-hash="' . $hash . '" class="wrap-choose-manager"></div>';             
        }

        $html .= '</div>';

        if ($attr['field_desc']) {
             $html .= '<small class="form-text text-muted">' . $attr['field_desc'] . '</small>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    public function button($atts = array(), $options = array(), $section_id = 'auto') 
    {
        $html = '<div class="form-group">'; 
        if (!empty($options['before'])) {
            $html .= $options['before'];
        }

        if (is_string($atts)) {
            $html .= '<button type="submit" class="button button-primary">' . $atts . '</button>';
        } else {
            $html .= '<button type="submit" class="button button-primary" ' . ($atts['field_name'] ? 'name="' . $atts['field_name'] . '"' : '') . '>' . $atts['field_title'] . '</button>';
        }
        
        if (!empty($options['after'])) {
            $html .= $options['after'];
        }
        $html .= '</div>';

        $this->fields[$section_id][] = $html;

        return $html;
    }
    
    public function display($options = array(), $section_id = 'auto') 
    {
        $attr = array(); 
        $attr['action'] = $this->attr['action'];
        $attr['method'] = $this->attr['method'];

        if (is_array($options)) {
            $attr = array_merge($attr, $options);
        }
        
        $attrHtml = array(); 

        foreach($attr AS $key => $value) {
            $attrHtml[] = $key . '="' . $value . '"';
        }

        $html = implode('', $this->fields[$section_id]); 
        if ($attr['action'] && $attr['method']) {
            $html = '<form ' . join(' ', $attrHtml) . '>' . $html . '</form>';
        }
        
        return $html; 
    }
}