<?php

trait Bulma
{
    # Wrapper for the Bulma framework
    # https://bulma.io

    public static function bulma_css($key = ''): array|string
    {
        $array = [
            'alert-e' => 'is-danger',
            'alert-i' => 'is-info',
            'alert-s' => 'is-success',
            'alert-w' => 'is-warning',
            'button' => 'button',
            'button-danger' => 'button is-danger',
            'button-primary' => 'button is-primary',
            'button-secondary' => 'button is-link',
            'checkbox' => '',
            'div' => 'my-3',
            'file' => 'file-input',
            'help' => 'help',
            'is-invalid' => 'is-danger',
            'is-valid' => 'is-success',
            'input' => 'input',
            'label' => 'label',
            'radio' => '',
            'textarea' => 'textarea',
        ];

        if ($key) {
            return $array[$key] ?? '';
        }

        return $array;
    }

    public function bulma($element = '', $data = ''): string
    {
        if (empty($data)) {
            return '';
        }

        # define our $return variable
        $return = null;

        # add a comment if $form->comments is enabled
        $return .= $this->_print_field_comment($data);

        # open the wrapper
        if ($this->formr->use_element_wrapper_div) {
            $return .= "<div class=\"field\">".PHP_EOL;
        }

        if ($this->type_is_checkbox($data)) {
            # checkbox and radio
            $return .= "<label class=\"{$data['type']}\">".PHP_EOL;
            $return .= $element.PHP_EOL;
            $return .= $data['label'].PHP_EOL;
            $return .= "</label>".PHP_EOL;
            $return .= $this->formr->printWrapperMessages($data);
        } elseif ($data['type'] == 'file') {
            # files
            $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
            $return .= "<div class=\"file\">".PHP_EOL;
            $return .= "    <label class=\"file-label\">".PHP_EOL;
            $return .=          $element.PHP_EOL;
            $return .= "        <span class=\"file-cta\">".PHP_EOL;
            $return .= "            <span class=\"file-icon\">".PHP_EOL;
            $return .= "                <i class=\"fas fa-upload\"></i>".PHP_EOL;
            $return .= "            </span>".PHP_EOL;
            $return .= "            <span class=\"file-label\">".PHP_EOL;
            $return .= "                Choose file(s)...".PHP_EOL;
            $return .= "            </span>".PHP_EOL;
            $return .= "        </span>".PHP_EOL;
            $return .= "    </label>".PHP_EOL;
            $return .= "</div>".PHP_EOL;
        } elseif ($data['type'] == 'select') {
            # select menus
            $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
            $return .= "<div class=\"control\">".PHP_EOL;
            $return .= "    <div class=\"select\">".PHP_EOL;
            $return .=          $element.PHP_EOL;
            $return .= "    </div>".PHP_EOL;
            $return .=      $this->formr->printWrapperMessages($data);
            $return .= "</div>".PHP_EOL;
        } else {
            # everything else
            $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
            $return .= "<div class=\"control has-icons-left has-icons-right\">".PHP_EOL;
            $return .= $element.PHP_EOL;
            if ($this->formr->submitted()) {
                # show fontawesome icons and highlight fields if error or success
                if ($this->formr->in_errors($data['name'])) {
                    $return .= '<span class="icon is-small is-right">'.PHP_EOL;
                    $return .= '    <i class="fas fa-exclamation-triangle"></i>'.PHP_EOL;
                    $return .= '</span>'.PHP_EOL;
                } else {
                    if ($this->formr->show_valid && ! in_array($data['type'], $this->formr->excluded_types)) {
                        $return .= '<span class="icon is-small is-right">'.PHP_EOL;
                        $return .= '    <i class="fas fa-check"></i>'.PHP_EOL;
                        $return .= '</span>'.PHP_EOL;
                    }
                }
            }
            $return .= $this->formr->printWrapperMessages($data);
            $return .= "</div>".PHP_EOL;
        }

        # close the wrapper
        if ($this->formr->use_element_wrapper_div) {
            $return .= '</div>'.PHP_EOL;
        }

        return $return.PHP_EOL;
    }
}