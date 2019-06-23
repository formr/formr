<?php

class Wrapper extends Formr
{
    public $container;

    public function __construct($instance)
    {
        $this->formr = $instance;
    }

    # default css classes - go ahead and add/change whatever you like...
    public static function css_defaults()
    {
        $array = [
            'div' => 'div',
            'label' => 'label',
            'input' => 'input',
            'help' => 'help',
            'button' => 'button',
            'warning' => 'warning',
            'error' => 'error',
            'text-error' => 'text-error',
            'success' => 'success',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'link' => 'link',
            'list-ul' => 'list-ul',
            'list-ol' => 'list-ol',
            'list-dl' => 'list-dl',
            'alert-e' => 'alert-error',
            'alert-w' => 'alert-warning',
            'alert-s' => 'alert-success',
            'alert-i' => 'alert-info'
        ];

        return $array;
    }


    # bootstrap 3 css classes
    public static function bootstrap3_css($key = '')
    {
        $array = [
            'div' => 'form-group',
            'label' => 'control-label',
            'input' => 'form-control',
            'file' => 'form-control',
            'help' => 'help-block',
            'button' => 'btn',
            'button-primary' => 'btn btn-primary',
            'warning' => 'has-warning',
            'error' => 'has-error',
            'text-error' => 'text-danger',
            'success' => 'has-success',
            'checkbox' => 'checkbox',
            'checkbox-inline' => 'checkbox-inline',
            'form-check-input' => 'form-check-input',
            'radio' => 'radio',
            'link' => 'alert-link',
            'list-ul' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-dl' => 'list-unstyled',
            'alert-e'=> 'alert alert-danger',
            'alert-w' => 'alert alert-warning',
            'alert-s' => 'alert alert-success',
            'alert-i' => 'alert alert-info',
            'is-invalid' => 'is-invalid',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    # bootstrap 3 field wrapper
    public function bootstrap3($element = '', $data = '')
    {
        if (empty($data)) {
            return false;
        }

        # if an ID is not present, create one using the name field
        if (!$this->formr->is_not_empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        $return = null;

        if ($data['type'] == 'checkbox') {
            # input is a checkbox
            # don't print the label if we're printing an array

            # notice that we're adding an id to the enclosing div, so that you may prepend/append jQuery, etc.
            if (substr($data['value'], -1) != ']') {
                $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="';

                # inline checkbox
                if (!empty($data['checkbox-inline'])) {
                    $return .= static::bootstrap3_css('checkbox-inline');
                } else {
                    $return .= static::bootstrap3_css('checkbox');
                }
            } else {
                $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="' . static::bootstrap3_css('div') . '">';
            }
        } elseif ($data['type'] == 'radio') {
            # input is a radio
            # don't print the label if we're printing an array
            if (substr($data['value'], -1) != ']') {
                $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="' . static::bootstrap3_css('radio');

                # inline radio
                if (!empty($data['radio-inline'])) {
                    $return .= static::bootstrap3_css('radio-inline');
                } else {
                    $return .= static::bootstrap3_css('radio');
                }
            } else {
                $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="' . static::bootstrap3_css('div') . '">';
            }
        } else {
            $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="' . static::bootstrap3_css('div');
        }

        # concatenate the error class if required
        if ($this->formr->in_errors($data['name'])) {
            $return .= ' ' . static::bootstrap3_css('error');
        }

        if (substr($data['value'], -1) != ']') {
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
        if (substr($data['name'], -1) == ']') {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            $return .= $this->formr->_t(1) . '<label for="' . $data['id'] . '">' . $data['label'] . $this->formr->_nl(1);
        } else {
            if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
                # no default class on a checkbox or radio
                # don't insert the label text here; we're doing it elsewhere
                if($this->formr->is_not_empty($data['label'])) {
                    $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<label class="' . $label_class . '" for="' . $data['id'] . '">' . $this->formr->_nl(1) . $this->formr->_t(1);
                }
            } else {
                $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<label class="' . $label_class . '" for="' . $data['id'] . '">' . $data['label'];
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
            $return .= '</label>' . $this->formr->_nl(1);
        }

        # add the field element
        $return .= $this->formr->_t(1) . $element;

        # inline help text
        if (!empty($data['inline'])) {

            # help-block text
            # if the text is surrounded by square brackets, show only on form error
            if (mb_substr($data['inline'], 0, 1) == '[') {
                if ($this->formr->in_errors($data['name'])) {
                    # trim the brackets and show on error
                    $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<p class="' . static::bootstrap3_css('help') . '">' . trim($data['inline'], '[]') . '</p>';
                }
            } else {
                # show this text on page load
                $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<p class="' . static::bootstrap3_css('help') . '">' . $data['inline'] . '</p>';
            }
        }

        # checkbox/radio: add the label text and close the label tag
        if (!empty($data['label']) && $data['type'] == 'checkbox' || $data['type'] == 'radio') {
            $return .= ' '.$data['label'];
            # add a required field indicator
            if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
                $return .= $this->formr->required_indicator;
            }
            $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '</label>' . $this->formr->_nl(1);
            $return .= '</div>' . $this->formr->_nl(1);
        } else {
            # close the controls div
            $return .= $this->formr->_nl(1) . '</div>' . $this->formr->_nl(1);
        }

        return $return;
    }

    # bootstrap 4 css classes
    public static function bootstrap4_css($key = '')
    {
        $array = [
            'div' => 'form-group',
            'label' => 'control-label',
            'input' => 'form-control',
            'file' => 'form-control-file',
            'help' => 'help-block',
            'button' => 'btn',
            'button-primary' => 'btn btn-primary',
            'warning' => 'has-warning',
            'error' => 'invalid-feedback',
            'text-error' => 'text-danger',
            'success' => 'has-success',
            'checkbox' => 'form-check',
            'checkbox-label' => 'form-check-label',
            'checkbox-inline' => 'form-check form-check-inline',
            'form-check-input' => 'form-check-input',
            'radio' => 'form-check',
            'link' => 'alert-link',
            'list-ul' => 'list-unstyled',
            'list-ol' => 'list-unstyled',
            'list-dl' => 'list-unstyled',
            'alert-e'=> 'alert alert-danger',
            'alert-w' => 'alert alert-warning',
            'alert-s' => 'alert alert-success',
            'alert-i' => 'alert alert-info',
            'is-invalid' => 'is-invalid',
        ];

        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    # bootstrap 4 field wrapper
    public function bootstrap4($element = '', $data = '')
    {
        if (empty($data)) {
            return false;
        }

        # if an ID is not present, create one using the name field
        if (!$this->formr->is_not_empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        $return = null;

        if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
            # input is a checkbox or radio
            # don't print the label if we're printing an array

            # notice that we're adding an id to the enclosing div, so that you may prepend/append jQuery, etc.
            if (substr($data['value'], -1) != ']') {
                $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="';

                # inline checkbox
                if (!empty($data['checkbox-inline'])) {
                    $return .= static::bootstrap4_css('checkbox-inline');
                } else {
                    $return .= static::bootstrap4_css('checkbox');
                }
            } else {
                $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="' . static::bootstrap4_css('div') . '">';
            }
        } else {
            $return = $this->formr->_nl(1) . '<div id="_' . $data['id'] . '" class="' . static::bootstrap4_css('div');
        }

        if (substr($data['value'], -1) != ']') {
            $return .= '">';
        }

        # add the field element here (before the label) if checkbox or radio
        if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
            $return .= $this->formr->_nl(1) . $this->formr->_t(1) . $element;
        }


        # always add a label...
        # if the label is empty add .sr-only, otherwise add .control-label
        if ($this->formr->is_not_empty($data['label'])) {
            if($data['type'] == 'checkbox' || $data['type'] == 'radio') {
                $label_class = static::bootstrap4_css('checkbox-label');
            } else {
                $label_class = static::bootstrap4_css('label');
            }
        } else {
            $label_class = 'sr-only';
        }

        # see if we're in a checkbox array...
        if (substr($data['name'], -1) == ']') {
            # we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
            $return .= $this->formr->_t(1) . '<label for="' . $data['id'] . '">' . $data['label'] . $this->formr->_nl(1);
        } else {
            if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
                # no default class on a checkbox or radio
                # don't insert the label text here; we're doing it elsewhere
                if($this->formr->is_not_empty($data['label'])) {
                    $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<label class="' . $label_class . '" for="' . $data['id'] . '">';
                }
            } else {
                $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<label class="' . $label_class . '" for="' . $data['id'] . '">' . $data['label'];
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
            $return .= '</label>' . $this->formr->_nl(1);
        }

        # add the field element here if NOT a checkbox or radio
        if ($data['type'] != 'checkbox' && $data['type'] != 'radio') {
            $return .= $this->formr->_t(1) . $element;
        }

        # inline help text
        if (!empty($data['inline'])) {

            # help-block text
            # if the text is surrounded by square brackets, show only on form error
            if (mb_substr($data['inline'], 0, 1) == '[') {
                if ($this->formr->in_errors($data['name'])) {
                    # trim the brackets and show on error
                    $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<p class="' . static::bootstrap4_css('help') . '">' . trim($data['inline'], '[]') . '</p>';
                }
            } else {
                # show this text on page load
                $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '<p class="' . static::bootstrap4_css('help') . '">' . $data['inline'] . '</p>';
            }
        }

        # checkbox/radio: add the label text and close the label tag
        if (!empty($data['label']) && $data['type'] == 'checkbox' || $data['type'] == 'radio') {
            $return .= ' '.$data['label'];
            # add a required field indicator
            if ($this->formr->_check_required($data['name']) && $this->formr->is_not_empty($data['label'])) {
                $return .= $this->formr->required_indicator;
            }
            $return .= $this->formr->_nl(1) . $this->formr->_t(1) . '</label>' . $this->formr->_nl(1);
            $return .= '</div>' . $this->formr->_nl(1);
        } else {
            # close the controls div
            $return .= $this->formr->_nl(1) . '</div>' . $this->formr->_nl(1);
        }

        return $return;
    }
}
