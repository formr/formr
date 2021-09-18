<?php

trait Bulma
{
    # Wrapper for the Bulma framework
    # https://bulma.io
    
    public static function bulma_css($key = '')
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
            'checkbox' => 'checkbox',
            'div' => 'div',
            'file' => 'file-input',
            'help' => 'help',
            'is-invalid' => 'is-danger',
            'is-valid' => 'is-success',
            'input' => 'input',
            'label' => 'label',
            'radio' => 'radio',
            'textarea' => 'textarea',
        ];
        
        if ($key) {
            return $array[$key];
        } else {
            return $array;
        }
    }

    public function bulma($element = '', $data = '')
    {
        if (empty($data)) {
            return false;
        }
        
        # define our $return variable with a new line
        $return = $this->nl;
        
        # add a comment if $form->comments is enabled
        $return .= $this->formr->_print_field_comment($data);
        
        # build a checkbox
        if ($data['type'] == 'checkbox' || $data['type'] == 'radio')
        {
            if($data['type'] == 'checkbox') {
                $return .= '<div class="field">' . $this->nl;
            }
            
            $return .= '    <label class="'.$data['type'].'">' . $this->nl;
            $return .=       $element . $this->nl;
            $return .=       $data['label'] . $this->nl;
            $return .= '    </label>' . $this->nl;
            
            if($data['type'] == 'checkbox') {
                $return .= '  </div>' . $this->nl;
                $return .= '</div>' . $this->nl;
            }
            
            return $return;
            
        }
        elseif ($data['type'] == 'file') {
            
            # file element
            $return .= '<div class="field">' . $this->nl;
            $return .=   $data['label'] ? '' : '<label class="label">' . $data['label'] . '</label>' . $this->nl;
            $return .= '  <div class="file">' . $this->nl;
            $return .= '    <label class="file-label">' . $this->nl;
            $return .=       $element . $this->nl;
            $return .= '      <span class="file-cta">' . $this->nl;
            $return .= '        <span class="file-icon">' . $this->nl;
            $return .= '          <i class="fas fa-upload"></i>' . $this->nl;
            $return .= '        </span>' . $this->nl;
            $return .= '        <span class="file-label">' . $this->nl;
            $return .= '          Choose file(s)...' . $this->nl;
            $return .= '        </span>' . $this->nl;
            $return .= '      </span>' . $this->nl;
            $return .= '    </label>' . $this->nl;
            $return .= '  </div>' . $this->nl;
            $return .= '</div>' . $this->nl;
            
            return $return;
            
        }
        elseif ($data['type'] == 'select') {
            
            #select menu
            $return .= '<div class="field">' . $this->nl;
            $return .=   $data['label'] ? '' : '<label class="label">' . $data['label'] . '</label>' . $this->nl;
            $return .= '  <div class="control">' . $this->nl;
            $return .= '    <div class="select">' . $this->nl;
            $return .=       $element . $this->nl;
            $return .= '    </div>' . $this->nl;
            $return .= '  </div>' . $this->nl;
            $return .= '</div>' . $this->nl;
            
            return $return;
            
        } else {
        
            # everything else
            $return .= $this->nl . '<div id="_' . $this->formr->make_id($data) . '" class="field">' . $this->nl;
            
            if ($this->formr->is_not_empty($data['label'])) {
                $return .= '<label class="label" for="' . $this->formr->make_id($data) . '">' . $this->nl;
                $return .=   $data['label'];
                $return .=   $this->formr->insert_required_indicator($data) . $this->nl;
                $return .= '</label>' . $this->nl;
            }
            
            $return .= '<div class="control has-icons-right">' . $this->nl;
            $return .= $element . $this->nl;
            
            # show fontawesome icons and highlight fields if error or success
            if($this->formr->submitted()) {
                if ($this->formr->in_errors($data['name'])) {
                    $return .= '<span class="icon is-small is-right">' . $this->nl;
                    $return .= '    <i class="fas fa-exclamation-triangle"></i>' . $this->nl;
                    $return .= '</span>' . $this->nl;
                } else {
                    if($this->formr->show_valid && !in_array($data['type'], $this->formr->excluded_types)) {
                        $return .= '<span class="icon is-small is-right">' . $this->nl;
                        $return .= '    <i class="fas fa-check"></i>' . $this->nl;
                        $return .= '</span>' . $this->nl;
                    }
                }
            }
            
            $return .= '</div>' . $this->nl;
            $return .= '</div>' . $this->nl;
            
            # bulma inline help
            if (!empty($data['inline'])) {
                if ($this->formr->is_in_brackets($data['inline'])) {
                    if ($this->formr->in_errors($data['name'])) {
                        $return .= '<p class="help is-danger">' . trim($data['inline'], '[]') . '</p>' . $this->nl;
                    }
                } else {
                    $return .= '<p class="help">' . $data['inline'] . '</p>' . $this->nl;
                }
            }
            
            return $return . $this->nl;
        }
    }
}