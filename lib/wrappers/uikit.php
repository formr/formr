<?php

trait Uikit
{
    # Wrapper for the Uikit framework
    # https://getuikit.com/

    public static function uikit_css($key = ''): array|string
    {
        $array = [
            'alert-e' => 'uk-alert-danger',
            'alert-i' => 'uk-alert-primary',
            'alert-s' => 'uk-alert-success',
            'alert-w' => 'uk-alert-warning',
            'button' => 'uk-button uk-button-default',
            'button-danger' => 'uk-button uk-button-danger',
            'button-primary' => 'uk-button uk-button-primary',
            'button-secondary' => 'uk-button uk-button-secondary',
            'checkbox' => 'uk-checkbox',
            'checkbox-label' => 'uk-form-label',
            'checkbox-inline' => '',
            'div' => 'uk-margin',
            'error' => '',
            'file' => 'uk-input uk-form-width-medium',
            'form-check-input' => '',
            'help' => 'uk-text-meta',
            'input' => 'uk-input',
            'is-invalid' => 'uk-form-danger',
            'is-valid' => 'uk-form-success',            
            'label' => 'uk-form-label',
            'link' => '',
            'list-dl' => '',
            'list-ol' => '',
            'list-ul' => '',
            'message-header' => '',
            'message-body' => '',
            'radio' => 'uk-radio',
            'success' => '',
            'textarea' => 'uk-textarea',
            'text-error' => '',
            'warning' => '',
        ];

        if ($key) {
            return $array[$key] ?? '';
        }

        return $array;
    }

    public function uikit($element = '', $data = ''): string
    {
        if (empty($data)) {
            return '';
        }

        # create our $return variable
        $return = PHP_EOL;

        # add a comment if $form->comments is enabled
        $return .= $this->_print_field_comment($data);

        # open the wrapper
        if ($this->formr->use_element_wrapper_div) {
            $return .= "<div class=\"field\">".PHP_EOL;
        }

        # checkbox or radio
        if ($this->formr->type_is_checkbox($data)) {
            $return .= "<div class=\"form-check\">".PHP_EOL;
            $return .= $element.PHP_EOL;
            $return .= "<label class=\"form-check-label\" for=\"{$this->formr->make_id($data)}\">".PHP_EOL;
            $return .= $data['label'].$this->formr->insert_required_indicator($data).PHP_EOL;
            $return .= "</label>".PHP_EOL;
            $return .= "</div>".PHP_EOL;
        } elseif ($data['type'] == 'file') {
            # files
            $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
            $return .= "<div class=\"file\" uk-form-custom=\"target: true\">".PHP_EOL;
            $return .=          $element.PHP_EOL;
            $return .= "    <input class=\"uk-input uk-form-width-medium\" type=\"text\" placeholder=\"Select file\" aria-label=\"Custom controls\" disabled>".PHP_EOL;
            $return .= "</div>".PHP_EOL;
        } else {
            # everything else
            if ($this->formr->is_not_empty($data['label'])) {
                $return .= "<label for=\"{$this->formr->make_id($data)}\" class=\"uk-form-label\">".PHP_EOL;
                $return .= $data['label'].$this->formr->insert_required_indicator($data);
                $return .= "</label>".PHP_EOL;
            }

            $return .= $element;
        }

        # add inline help
        if (! empty($data['inline'])) {
            if ($this->formr->is_in_brackets($data['inline'])) {
                if ($this->formr->in_errors($data['name'])) {
                    # if the text is surrounded by square brackets, show only on form error
                    $return .= "<div id=\"{$data['name']}\" class=\"form-text uk-text-danger\">".trim($data['inline'], '[]')."</div>".PHP_EOL;
                }
            } else {
                # show this text on page load
                $return .= "<div id=\"{$data['name']}\" class=\"form-text\">{$data['inline']}</div>".PHP_EOL;
            }
        } else {
            # show error message
            if ($this->formr->in_errors($data['name']) && $this->formr->inline_errors) {
                $return .= "<div class=\"form-text uk-text-danger\">{$this->formr->errors[$data['name']]}</div>".PHP_EOL;
            }
        }

        // if ($this->type_is_checkbox($data)) {
        //     # checkbox and radio
        //     $return .= "<div class=\"uk-margin uk-grid-small uk-child-width-auto uk-grid {$data['type']}\">".PHP_EOL;
        //     $return .= $element.PHP_EOL;
        //     $return .= $data['label'].PHP_EOL;
        //     $return .= "</div>".PHP_EOL;
        //     $return .= $this->formr->printWrapperMessages($data);
        // } elseif ($data['type'] == 'file') {
        //     # files
        //     $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
        //     $return .= "<div class=\"file\">".PHP_EOL;
        //     $return .= "    <label class=\"file-label\">".PHP_EOL;
        //     $return .=          $element.PHP_EOL;
        //     $return .= "        <span class=\"file-cta\">".PHP_EOL;
        //     $return .= "            <span class=\"file-icon\">".PHP_EOL;
        //     $return .= "                <i class=\"fas fa-upload\"></i>".PHP_EOL;
        //     $return .= "            </span>".PHP_EOL;
        //     $return .= "            <span class=\"file-label\">".PHP_EOL;
        //     $return .= "                Choose file(s)...".PHP_EOL;
        //     $return .= "            </span>".PHP_EOL;
        //     $return .= "        </span>".PHP_EOL;
        //     $return .= "    </label>".PHP_EOL;
        //     $return .= "</div>".PHP_EOL;
        // } elseif ($data['type'] == 'select') {
        //     # select menus
        //     $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
        //     $return .= "<div class=\"control\">".PHP_EOL;
        //     $return .= "    <div class=\"select\">".PHP_EOL;
        //     $return .=          $element.PHP_EOL;
        //     $return .= "    </div>".PHP_EOL;
        //     $return .=      $this->formr->printWrapperMessages($data);
        //     $return .= "</div>".PHP_EOL;
        // } else {
        //     # everything else
        //     $return .= $this->formr->printWrapperLabel($data).PHP_EOL;
        //     $return .= "<div class=\"control has-icons-left has-icons-right\">".PHP_EOL;
        //     $return .= $element.PHP_EOL;
        //     if ($this->formr->submitted()) {
        //         # show fontawesome icons and highlight fields if error or success
        //         if ($this->formr->in_errors($data['name'])) {
        //             $return .= '<span class="icon is-small is-right">'.PHP_EOL;
        //             $return .= '    <i class="fas fa-exclamation-triangle"></i>'.PHP_EOL;
        //             $return .= '</span>'.PHP_EOL;
        //         } else {
        //             if ($this->formr->show_valid && ! in_array($data['type'], $this->formr->excluded_types)) {
        //                 $return .= '<span class="icon is-small is-right">'.PHP_EOL;
        //                 $return .= '    <i class="fas fa-check"></i>'.PHP_EOL;
        //                 $return .= '</span>'.PHP_EOL;
        //             }
        //         }
        //     }
        //     $return .= $this->formr->printWrapperMessages($data);
        //     $return .= "</div>".PHP_EOL;
        // }

        # close the wrapper
        if ($this->formr->use_element_wrapper_div) {
            $return .= '</div>'.PHP_EOL;
        }

        return $return.PHP_EOL;
    }
}