<?php

trait Bootstrap
{
    # Wrapper for the Bootstrap framework
    # https://bootstrap.com

    # default Bootstrap CSS
    public static function bootstrap_css($key = ''): array|string
    {
        return static::bootstrap5_css($key);
    }

    # default Bootstrap library
    public function bootstrap($element = '', $data = ''): string
    {
        return $this->bootstrap5($element, $data);
    }

    public static function bootstrap5_css($key = ''): array|string
    {
        # bootstrap 5 css classes

        $array = [
            'alert-e' => 'alert alert-danger',
            'alert-i' => 'alert alert-info',
            'alert-s' => 'alert alert-success',
            'alert-w' => 'alert alert-warning',
            'button' => 'btn',
            'button-danger' => 'btn btn-danger',
            'button-primary' => 'btn btn-primary',
            'button-secondary' => 'btn btn-secondary',
            'checkbox' => 'form-check-input',
            'checkbox-label' => 'form-check-label',
            'checkbox-inline' => 'form-check-input',
            'div' => 'mb-3',
            'error' => 'invalid-feedback',
            'file' => 'form-control',
            'form-check-input' => 'form-check-input',
            'help' => 'form-text',
            'input' => 'form-control',
            'is-invalid' => 'is-invalid',
            'is-valid' => 'is-valid',
            'label' => 'form-label',
            'link' => 'alert-link',
            'list-dl' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-ul' => 'list-unstyled',
            'radio' => 'form-check-input',
            'success' => 'has-success',
            'text-error' => 'text-danger',
            'warning' => 'has-warning',
        ];

        if ($key) {
            return $array[$key] ?? '';
        }

        return $array;
    }

    public function bootstrap5($element = '', $data = ''): string
    {
        # bootstrap 5 field wrapper

        if (empty($data)) {
            return '';
        }

        # create our $return variable
        $return = PHP_EOL;

        # optional: add a comment for easier debugging in the html
        $return .= $this->formr->_print_field_comment($data);

        # open the wrapping div
        if ($this->formr->use_element_wrapper_div) {
            $return .= "<div class=\"mb-3\">".PHP_EOL;
        }

        # checkbox or radio
        if ($this->formr->type_is_checkbox($data)) {
            $return .= "<div class=\"form-check\">".PHP_EOL;
            $return .= $element.PHP_EOL;
            $return .= "<label class=\"form-check-label\" for=\"{$this->formr->make_id($data)}\">".PHP_EOL;
            $return .= $data['label'].$this->formr->insert_required_indicator($data).PHP_EOL;
            $return .= "</label>".PHP_EOL;
            $return .= "</div>".PHP_EOL;
        } else {
            # everything else
            if ($this->formr->is_not_empty($data['label'])) {
                $return .= "<label for=\"{$this->formr->make_id($data)}\" class=\"form-label\">".PHP_EOL;
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
                    $return .= "<div id=\"{$data['name']}\" class=\"form-text text-danger\">".trim($data['inline'], '[]')."</div>".PHP_EOL;
                }
            } else {
                # show this text on page load
                $return .= "<div id=\"{$data['name']}\" class=\"form-text\">{$data['inline']}</div>".PHP_EOL;
            }
        } else {
            # show error message
            if ($this->formr->in_errors($data['name']) && $this->formr->inline_errors) {
                $return .= "<div class=\"form-text text-danger\">{$this->formr->errors[$data['name']]}</div>".PHP_EOL;
            }
        }

        # close the wrapping div
        if ($this->formr->use_element_wrapper_div) {
            $return .= "</div>".PHP_EOL;
        }

        return $return;
    }

    public static function bootstrap4_css($key = ''): array|string
    {
        # bootstrap 4 css classes

        $array = [
            'alert-e' => 'alert alert-danger',
            'alert-i' => 'alert alert-info',
            'alert-s' => 'alert alert-success',
            'alert-w' => 'alert alert-warning',
            'button' => 'btn',
            'button-danger' => 'btn btn-danger',
            'button-primary' => 'btn btn-primary',
            'button-secondary' => 'btn btn-secondary',
            'checkbox' => 'form-check',
            'checkbox-label' => 'form-check-label',
            'checkbox-inline' => 'form-check form-check-inline',
            'div' => 'form-group',
            'error' => 'invalid-feedback',
            'file' => 'form-control-file',
            'form-check-input' => 'form-check-input',
            'help' => 'form-text',
            'input' => 'form-control',
            'is-invalid' => 'is-invalid',
            'is-valid' => 'is-valid',
            'label' => 'control-label',
            'link' => 'alert-link',
            'list-dl' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-ul' => 'list-unstyled',
            'radio' => 'form-check',
            'success' => 'has-success',
            'text-error' => 'text-danger',
            'warning' => 'has-warning',
        ];

        if ($key) {
            return $array[$key] ?? '';
        }

        return $array;
    }

    public function bootstrap4($element = '', $data = ''): string
    {
        # bootstrap 4 field wrapper

        if (empty($data)) {
            return '';
        }

        # create our $return variable
        $return = PHP_EOL;

        # optional: add a comment for easier debugging in the html
        $return .= $this->formr->_print_field_comment($data);

        if ($this->formr->type_is_checkbox($data)) {
            # input is a checkbox or radio
            # don't print the <label> if we're printing an array
            if (! $this->formr->is_array($data['value'])) {
                # add an ID to the wrapping <div> so that we can access it via javascript
                $return .= PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="';

                if (! empty($data['checkbox-inline'])) {
                    # this is an inline checkbox
                    $return .= static::bootstrap4_css('checkbox-inline');
                } else {
                    $return .= static::bootstrap4_css('checkbox');
                }

                # close the <div>
                $return .= '">';
            }
        } else {
            # open the wrapping <div> tag
            if ($this->formr->use_element_wrapper_div) {
                $return .= PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="'.static::bootstrap4_css('div').'">';
            }
        }

        # add the checkbox/radio element here (before the <label>)
        if ($this->formr->type_is_checkbox($data)) {
            $return .= PHP_EOL.$element;
        }

        # if the <label> is empty add .sr-only
        if ($this->formr->is_not_empty($data['label'])) {
            if ($this->formr->type_is_checkbox($data)) {
                $label_class = static::bootstrap4_css('checkbox-label');
            } else {
                $label_class = static::bootstrap4_css('label');
            }
        } else {
            $label_class = 'sr-only';
        }

        # see if we're in a checkbox array...
        if ($this->formr->is_array($data['name']) && $this->formr->type_is_checkbox($data)) {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            # we'll add the label text later...
            $return .= '<label for="'.$this->formr->make_id($data).'">'.PHP_EOL;
        } else {
            # we are not in a checkbox array
            if ($this->formr->type_is_checkbox($data)) {
                # no default class on a checkbox or radio
                if ($this->formr->is_not_empty($data['label'])) {
                    # open the <label>, but don't insert the label text here; we're doing it elsewhere
                    $return .= PHP_EOL.'<label class="'.$label_class.'" for="'.$this->formr->make_id($data).'">';
                }
            } else {
                # open the <label> and insert the label text
                $return .= PHP_EOL.'<label class="'.$label_class.'" for="'.$data['name'].'">'.$data['label'];
            }
        }

        # add a required field indicator if applicable
        if (! $this->formr->type_is_checkbox($data)) {
            $return .= $this->formr->insert_required_indicator($data);
        }

        # close the <label> if NOT a checkbox or radio
        if (! $this->formr->type_is_checkbox($data)) {
            $return .= "</label>".PHP_EOL;
        }

        # add the field element here if NOT a checkbox or radio
        if (! $this->formr->type_is_checkbox($data)) {
            $return .= $element.PHP_EOL;
        }

        # inline help text
        if (! empty($data['inline'])) {
            # help-block text
            # if the text is surrounded by square brackets, show only on form error
            if ($this->formr->is_in_brackets($data['inline'])) {
                if ($this->formr->in_errors($data['name'])) {
                    # trim the brackets and show on error
                    $return .= PHP_EOL.'<p class="'.static::bootstrap4_css('help').' '.static::bootstrap4_css('text-error').'">'.trim($data['inline'], '[]').'</p>';
                }
            } else {
                # show this text on page load
                $return .= PHP_EOL.'<p class="'.static::bootstrap4_css('help').'">'.$data['inline'].'</p>';
            }
        } else {
            if ($this->formr->in_errors($data['name']) && $this->formr->inline_errors) {
                $return .= '<div class="text-danger">'.$this->formr->errors[$data['name']].'</div>';
            }
        }

        # checkbox/radio: add the label text and close the label tag
        if ($this->formr->is_not_empty($data['label']) && $this->formr->type_is_checkbox($data)) {
            # add label text
            $return .= ' '.$data['label'];

            # add a required field indicator (*)
            if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
                $return .= $this->formr->required_indicator;
            }

            # close the <label> tag
            $return .= "</label>".PHP_EOL;
        }

        if (! $this->formr->is_array($data['value']) && $this->formr->use_element_wrapper_div) {
            # close the wrapping <div>

            $return .= "</div>".PHP_EOL;
        }

        return $return;
    }

    public static function bootstrap3_css($key = ''): array|string
    {
        # bootstrap 3 css classes

        $array = [
            'alert-e' => 'alert alert-danger',
            'alert-w' => 'alert alert-warning',
            'alert-s' => 'alert alert-success',
            'alert-i' => 'alert alert-info',
            'button' => 'btn',
            'button-danger' => 'btn btn-danger',
            'button-primary' => 'btn btn-primary',
            'button-secondary' => 'btn btn-secondary',
            'checkbox' => 'checkbox',
            'checkbox-inline' => 'checkbox-inline',
            'div' => 'form-group',
            'error' => 'has-error',
            'file' => 'form-control',
            'form-check-input' => 'form-check-input',
            'help' => 'help-block',
            'input' => 'form-control',
            'label' => 'control-label',
            'link' => 'alert-link',
            'list-dl' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-ul' => 'list-unstyled',
            'is-invalid' => 'is-invalid',
            'is-valid' => 'is-valid',
            'radio' => 'radio',
            'success' => 'has-success',
            'text-error' => 'text-danger',
            'warning' => 'has-warning',
        ];

        if ($key) {
            return $array[$key] ?? '';
        }

        return $array;
    }

    public function bootstrap3($element = '', $data = ''): string
    {
        # bootstrap 3 field wrapper

        if (empty($data)) {
            return '';
        }

        # set the label array value to null if a label is not present
        if (! isset($data['label'])) {
            $data['label'] = null;
        }

        $return = PHP_EOL;

        if ($data['type'] == 'checkbox') {
            # input is a checkbox
            # notice that we're adding an id to the enclosing div, so that you may prepend/append jQuery, etc.
            if (! str_ends_with($data['value'], ']')) {
                $return = PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="';

                # inline checkbox
                if (! empty($data['checkbox-inline'])) {
                    $return .= static::bootstrap3_css('checkbox-inline');
                } else {
                    $return .= static::bootstrap3_css('checkbox');
                }
            } else {
                $return = PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="'.static::bootstrap3_css('div').'">';
            }
        } elseif ($data['type'] == 'radio') {
            # input is a radio
            # don't print the label if we're printing an array
            if (! str_ends_with($data['value'], ']')) {
                $return = PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="'.static::bootstrap3_css('radio');

                # inline radio
                if (! empty($data['radio-inline'])) {
                    $return .= static::bootstrap3_css('radio-inline');
                } else {
                    $return .= static::bootstrap3_css('radio');
                }
            } else {
                $return = PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="'.static::bootstrap3_css('div').'">';
            }
        } else {
            $return = PHP_EOL.'<div id="_'.$this->formr->make_id($data).'" class="'.static::bootstrap3_css('div');
        }

        # concatenate the error class if required
        if ($this->formr->in_errors($data['name'])) {
            $return .= ' '.static::bootstrap3_css('error');
        }

        if (! str_ends_with($data['value'], ']')) {
            $return .= '">';
        }

        # always add a label...
        # if the label is empty add .sr-only, otherwise add .control-label
        if ($this->formr->is_not_empty($data['label'])) {
            $label_class = static::bootstrap3_css('label');
        } else {
            $label_class = 'sr-only';
        }

        # see if we're in a checkbox array...
        if (str_ends_with($data['name'], ']') && ($data['type'] == 'checkbox' || $data['type'] == 'radio')) {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            # we'll add the label text later...
            $return .= '<label for="'.$this->formr->make_id($data).'">'.PHP_EOL;
        } else {
            if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
                # no default class on a checkbox or radio
                # don't insert the label text here; we're doing it elsewhere
                if ($this->formr->is_not_empty($data['label'])) {
                    $return .= PHP_EOL.'<label class="'.$label_class.'" for="'.$this->formr->make_id($data).'">'.PHP_EOL;
                }
            } else {
                $return .= PHP_EOL.'<label class="'.$label_class.'" for="'.$this->formr->make_id($data).'">'.$data['label'];
            }
        }

        # add a required field indicator
        if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
            if ($data['type'] != 'checkbox' && $data['type'] != 'radio') {
                $return .= $this->formr->required_indicator;
            }
        }

        # close the label if NOT a checkbox or radio
        if ($data['type'] != 'checkbox' && $data['type'] != 'radio') {
            $return .= '</label>'.PHP_EOL;
        }

        # add the field element
        $return .= $element;

        # inline help text
        if (! empty($data['inline'])) {
            # help-block text
            # if the text is surrounded by square brackets, show only on form error
            if (mb_substr($data['inline'], 0, 1) == '[') {
                if ($this->formr->in_errors($data['name'])) {
                    # trim the brackets and show on error
                    $return .= PHP_EOL.'<p class="'.static::bootstrap3_css('help').'">'.trim($data['inline'], '[]').'</p>';
                }
            } else {
                # show this text on page load
                $return .= PHP_EOL.'<p class="'.static::bootstrap3_css('help').'">'.$data['inline'].'</p>';
            }
        } else {
            if ($this->formr->in_errors($data['name']) && $this->formr->inline_errors) {
                $return .= '<div class="text-danger">'.$this->formr->errors[$data['name']].'</div>';
            }
        }

        # checkbox/radio: add the label text and close the label tag
        if (! empty($data['label']) && $data['type'] == 'checkbox' || $data['type'] == 'radio') {
            $return .= ' '.$data['label'];

            # add a required field indicator
            if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
                $return .= $this->formr->required_indicator;
            }

            $return .= PHP_EOL.'</label>'.PHP_EOL;
            $return .= '</div>'.PHP_EOL;
        } else {
            # close the controls div
            $return .= PHP_EOL.'</div>'.PHP_EOL;
        }

        return $return;
    }
}
