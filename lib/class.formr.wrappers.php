<?php

class Wrapper extends Formr {
    
    
    
    public $container;
    
    
    
    public function __construct($errors) {
		$this->errors = $errors;
	}
    
    
    
    # default css classes - go ahead and add/change whatever you like...
    public static function css_defaults() {
		$array = array(
			'div'		=> 'div',
			'label'		=> 'label',
			'input'		=> 'input',
			'help'		=> 'help',
			'button'	=> 'button',
			'warning'	=> 'warning',
			'error'		=> 'error',
			'text-error'=> 'text-error',
			'success'	=> 'success',
			'checkbox'	=> 'checkbox',
			'radio'		=> 'radio',
			'link'		=> 'link',
			'list-ul'	=> 'list-ul',
			'list-ol'	=> 'list-ol',
			'list-dl'	=> 'list-dl',
			'alert-e'   => 'alert-error',
			'alert-w' 	=> 'alert-warning',
			'alert-s' 	=> 'alert-success',
			'alert-i'	=> 'alert-info'
		);
		
		return $array;
	}
	
	
	
	/*
		 CUSTOM WRAPPERS
		 Add your own wrapper methods here...
		 
		 (TODO: This is a little hacky and needs to be updated and improved. Wrapper should be a plugin...)
	
	*/
	
	
	
	# bootstrap 3 css classes - put here for easier updating
	public static function bootstrap_css($key='') {
		
		$array = array(
			'div'				=> 'form-group',
			'label'				=> 'control-label',
			'input'				=> 'form-control',
			'help'				=> 'help-block',
			'button'			=> 'btn',
			'warning'			=> 'has-warning',
			'error'				=> 'has-error',
			'text-error'		=> 'text-danger',
			'success'			=> 'has-success',
			'checkbox'			=> 'checkbox',
			'checkbox-inline'	=> 'checkbox-inline',
			'radio'				=> 'radio',
			'link'				=> 'alert-link',
			'list-ul'			=> 'list-unstyled',
			'list-ol'			=> 'list-unstyled',
			'list-dl'			=> 'list-unstyled',
			'alert-e'   		=> 'alert alert-danger',
			'alert-w' 			=> 'alert alert-warning',
			'alert-s' 			=> 'alert alert-success',
			'alert-i'			=> 'alert alert-info',
		);
		
		if($key) {
			return $array[$key];
		} else {
			return $array;
		}
	
	}
    
    
    
    # bootstrap field wrapper
    public function bootstrap($element='',$data='') {
		
		
		if(empty($data)) {
			return false;
		}
		
		# if an ID is not present, create one using the name field
		if(empty($data['id'])) {
			$data['id'] = $data['name'];
		}
		
		$return = null;
		
		
		if($data['type'] == 'checkbox') {
			# input is a checkbox
			# don't print the label if we're printing an array
			
			# notice that we're adding an id to the enclosing div, so that you may prepend/append jQuery, etc.
			if(substr($data['value'],-1) != ']') {
				$return = $this->_nl(1).'<div id="_'.$data['id'].'" class="';
				
				# inline checkbox
				if(!empty($data['checkbox-inline'])) {
					$return .= static::bootstrap_css('checkbox-inline');
				} else {
					$return .= static::bootstrap_css('checkbox');
				}
				
			} else {
				$return = $this->_nl(1).'<div id="_'.$data['id'].'" class="'.static::bootstrap_css('div').'">';
			}
		}
		elseif($data['type'] == 'radio') {
			# input is a radio
			# don't print the label if we're printing an array
			if(substr($data['value'],-1) != ']') {
				$return = $this->_nl(1).'<div id="_'.$data['id'].'" class="'.static::bootstrap_css('radio');
				
				# inline radio
				if(!empty($data['radio-inline'])) {
					$return .= static::bootstrap_css('radio-inline');
				} else {
					$return .= static::bootstrap_css('radio');
				}
				
			} else {
				$return = $this->_nl(1).'<div id="_'.$data['id'].'" class="'.static::bootstrap_css('div').'">';
			}
		} else {
			$return = $this->_nl(1).'<div id="_'.$data['id'].'" class="'.static::bootstrap_css('div');
		}
		
		# concatenate the error class if required
		if($this->in_errors($data['name'])) {
			$return .= ' '.static::bootstrap_css('error');
		}
		
		if(substr($data['value'],-1) != ']') {
			$return .= '">';
		}
			
		
		
		# always add a label...
		# if the label is empty add .sr-only, otherwise add .control-label
		// if(!empty($data['label'])) {
			if(!empty($data['label']) || (isset($data['label']) && $data['label'] === "0")) {
			$label_class = static::bootstrap_css('label');
		} else {
			$label_class = 'sr-only';
		}
			
		# see if we're in a checkbox array...
		if(substr($data['name'],-1) == ']') {
			# we are. we don't want to color each checkbox label if there's an error - we only want to color the main label for the group
			$return .= $this->_t(1).'<label for="'.$data['id'].'">'.$data['label'].$this->_nl(1);
		} else {
			if($data['type'] == 'checkbox' || $data['type'] == 'radio') {
				# no default class on a checkbox or radio
				$return .= $this->_nl(1).$this->_t(1).'<label class="'.$label_class.'" for="'.$data['id'].'">'.$data['label'].$this->_nl(1).$this->_t(1);
			} else {
				$return .= $this->_nl(1).$this->_t(1).'<label class="'.$label_class.'" for="'.$data['id'].'">'.$data['label'];
			}
		}
		
		# add a required field indicator
		if($this->_check_required($data['name']) && !empty($data['label'])) {
			$return .= $this->required_indicator;
		}
		
		# close the label if NOT a checbox or radio
		if($data['type'] != 'checkbox' && $data['type'] != 'radio') {
			$return .= '</label>'.$this->_nl(1);
		}

			
		
			
		# add the field element
		$return .= $this->_t(1).$element;
		
		
		
		# inline help text
		if(!empty($data['inline'])) {
			
			# help-block text
			# if the text is surrounded by square brackets, show only on form error
			if(mb_substr($data['inline'],0,1) == '[') {
				if($this->in_errors($data['name'])) {
					# trim the brackets and show on error
					$return .= $this->_nl(1).$this->_t(1).'<p class="'.static::bootstrap_css('help').'">'.trim($data['inline'],'[]').'</p>';
				}
			} else {
				# show this text on page load
				$return .= $this->_nl(1).$this->_t(1).'<p class="'.static::bootstrap_css('help').'">'.$data['inline'].'</p>';
			}
		}
		
		
		
		# checkbox/radio: add the label text and close the label tag
		if(!empty($data['label']) && $data['type'] == 'checkbox' || $data['type'] == 'radio') {
			$return .= $data['label'];
			$return .= $this->_nl(1).$this->_t(1).'</label>'.$this->_nl(1);
			$return .= '</div>'.$this->_nl(1);
		} else {
			# close the controls div
			$return .= $this->_nl(1).'</div>'.$this->_nl(1);
		}
		
		
		
		return $return;
	}

}
