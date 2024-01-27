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

        $html .= '<div class="input-group mb-3">'; 
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
        $html .= '<div class="input-group mb-3"><select class="form-control" id="Select' . $attr['field_name'] . '" name="' . $attr['field_name'] . '" ' . $this->get_attributes($attr['field_attr']) . ' >'; 
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
        $html .= '<div class="input-group mb-3">'; 
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
        $html .= '<div class="input-group mb-3">'; 
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

        $html .= '<div class="input-group mb-3">'; 
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
        
        $html = '<div class="form-group">'; 
        if (!empty($attr['field_title'])) {
            $html .= '<label class="label-title" for="Textarea' . $attr['field_name'] . '">' . $attr['field_title'] . '</label>'; 
        }

        $html .= '<div class="input-group mb-3">'; 
        //$html .= '<textarea rows="5" class="form-control" id="Textarea' . $attr['field_name'] . '" name="' . $attr['field_name'] . '" placeholder="' . (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : '') . '" ' . $this->get_attributes($attr['field_attr']) . '>' . $attr['field_value'] . '</textarea>'; 

        $html .= get_editor($attr['field_name'], $attr['field_value'], array(
            'placeholder' => (!empty($attr['field_placeholder']) ? $attr['field_placeholder'] : ''), 
        )); 
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