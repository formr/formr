<?php

require_once 'wrappers/autoload.php';

use Formr\Formr;

class Wrapper extends Formr
{
    use Bootstrap;
    use Bulma;
    use Tailwind;
    use Uikit;

    public function __construct(Formr $formr)
    {
        $this->formr = $formr;
    }

    public static function default_css($key = ''): array|string
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
            'checkbox' => 'checkbox',
            'label' => 'label',
            'link' => 'link',
            'list-dl' => 'list-dl',
            'list-ol' => 'list-ol',
            'list-ul' => 'list-ul',
            'text-error' => 'text-error',
        ];

        if ($key) {
            return $array[$key];
        }

        return $array;
    }

    public function default_wrapper($wrapper, $element, $data): string
    {
        # this is the default field wrapper; used if a framework is not specified

        # enter the name of the css function so we can use it when calling css classes
        $css = 'default_css';

        # the type of lists Formr will accept as a wrapper
        $list_tags = ['ul', 'ol', 'dl'];

        # define our $return variable
        $return = null;

        # add a comment if $form->comments is enabled
        $return .= $this->formr->_print_field_comment($data).PHP_EOL;

        # open the user-defined wrapper
        if ($wrapper['open']) {
            # don't print if wrapping with 'ul', 'li', or 'dl'
            if (! in_array($wrapper['type'], $list_tags)) {
                $return .= $wrapper['open'].PHP_EOL;
            }
        } else {
            # 'div' was entered
            if ($wrapper['type'] != '') {
                $return .= '<div class="'.static::$css('div').'" id="_'.$this->formr->make_id($data).'">'.PHP_EOL;
            }
        }

        # add the list item tag if wrapping in a list
        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol') {
            $return .= '<li>'.PHP_EOL;
        }
        if ($wrapper['type'] == 'dl') {
            $return .= '<dt>'.PHP_EOL;
        }

        # checkboxes and radios
        if (in_array($data['type'], $this->formr->_input_types('checkbox'))) {
            # wrap checkboxes and radios in a label
            if (! empty($data['label'])) {
                $return .= $this->formr->label_open($data['value'], $data['label'], $data['id']);
            }

            # add the field element
            $return .= $element;

            if (! empty($data['label'])) {
                $return .= ' '.$this->formr->label_close($data).PHP_EOL;
            }
        } else {
            # everything else
            if (! empty($data['label'])) {
                $return .= $this->formr->label($data);
            }
            # add the field element
            $return .= $element.PHP_EOL;
        }

        # close the list tag if required
        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol') {
            $return .= '</li>'.PHP_EOL;
        }
        if ($wrapper['type'] == 'dl') {
            $return .= '</dt>'.PHP_EOL;
        }

        # close the user-defined wrapper
        if ($wrapper['close']) {
            # don't print if wrapping with 'ul', 'li', or 'dl'
            if (! in_array($wrapper['type'], $list_tags)) {
                $return .= $wrapper['close'];
            }
        } else {
            # close the div
            if ($wrapper['type'] != '') {
                $return .= '</div>';
            }
        }

        if ($this->formr->in_errors($data['name']) && $this->formr->inline_errors) {
            $return .= '<div class="text-error">'.$this->formr->errors[$data['name']].'</div>';
        }

        return $return.PHP_EOL;
    }
}
