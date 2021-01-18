<?php

require_once 'wrappers/autoload.php';

class Wrapper extends Formr\Formr
{
    use Bootstrap, Bulma;

    public function __construct($instance) {
        $this->formr = $instance;
        $this->nl = "\r\n";
    }
    
    public static function default_css($key = '')
    {
        /*
            These are Formr's default CSS classes; they are used if a framework is not specified when creating a form.
            
            The array *value* is the class name that you would add to your CSS file.
            <style>
                .alert-error {
                    color: red;
                }
            </style>
        */

        $array = [
            'alert-e' => 'alert-error',
            'alert-w' => 'alert-warning',
            'alert-s' => 'alert-success',
            'alert-i' => 'alert-info',
            'button' => 'button',
            'div' => 'field-wrap',
            'file' => 'file',
            'is-invalid' => 'is-invalid',
            'label' => 'label',
            'link' => 'link',
            'list-dl' => 'list-dl',
            'list-ol' => 'list-ol',
            'list-ul' => 'list-ul',
            'text-error' => 'text-error',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    public function default_wrapper($wrapper, $element, $data)
    {        
        # this is the default field wrapper; used if a framework is not specified
        
        # enter the name of the css function so we can use it when calling css classes
        $css = 'default_css';
        
        # the type of lists Formr will accept as a wrapper
        $list_tags = ['ul', 'ol', 'dl'];
        
        # define our $return variable with a new line
        $return = $this->nl;

        # add a comment if $form->comments is enabled
        $return .= $this->formr->_print_field_comment($data) . $this->nl;

        # open the user-defined wrapper
        if ($wrapper['open']) {
            # don't print if wrapping with 'ul', 'li', or 'dl'
            if (!in_array($wrapper['type'], $list_tags)) {
                $return .= $wrapper['open'] . $this->nl;
            }
        } else {
            # 'div' was entered
            if($wrapper['type'] != '') {
                $return .= '<div class="'.static::$css('div').'" id="_' . $this->formr->make_id($data) . '">' . $this->nl;
            }
        }

        # add the list item tag if wrapping in a list
        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol') {
            $return .= '<li>' . $this->nl;
        }
        if ($wrapper['type'] == 'dl') {
            $return .= '<dt>' . $this->nl;
        }

        # checkboxes and radios
        if (in_array($data['type'], $this->_input_types('checkbox'))) {
            # wrap checkboxes and radios in a label
            if (!empty($data['label'])) {
                $return .= $this->label_open($data['value'], $data['label'], $data['id']) . $this->nl;
            }
            
            # add the field element
            $return .= $element;
            
            if (!empty($data['label'])) {
                $return .= ' ' . $this->label_close($data) . $this->nl;
            }
        } else {
            # everything else
            if (!empty($data['label'])) {
                $return .= $this->label($data) . $this->nl;
            }
            # add the field element
            $return .= $element . $this->nl;
        }

        # close the list tag if required
        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol') {
            $return .= '</li>' . $this->nl;
        }
        if ($wrapper['type'] == 'dl') {
            $return .= '</dt>' . $this->nl;
        }

        # close the user-defined wrapper
        if ($wrapper['close']) {
            # don't print if wrapping with 'ul', 'li', or 'dl'
            if (!in_array($wrapper['type'], $list_tags)) {
                $return .= $wrapper['close'];
            }
        } else {
            # close the div
            if($wrapper['type'] != '') {
                $return .= '</div>';
            }
        }

        return $return . $this->nl;
    }
}
