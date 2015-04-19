<?php

class Forms extends MyForms {
	
	// these functions contain pre-built form arrays that you can simply pass through the fastform() function.
	// you can also build validation sets for each field as well.
	// be one of the cool kids; build a whole mess of form and validation sets and share 'em with your friends.
	// Help extend Formr! :)
	
	
	public static function contact($validate='') {
		
		if(!$validate) {
			
			/**
				in this section is where we'll build the form using an array. the array key contains the input type, and 
				the array value contains all of the field's attributes.
				
				'array_key'  => 'array value'
				
				'field input type' => 'name , label text , value , input ID , string , .inline-text , selected/checked , $options'
			*/
			
			$data = array(
				'text1'		=> 'fname,First name:,,fname,,[please enter your first name]',
				'text2'		=> 'lname,Last name:,,lname,,[please enter your last name]',
				'email'		=> 'email,Email:,,email,,[please enter your email address]',
				'text3'		=> 'city,City:,,city,,[please enter your city]',
				'select1'	=> 'state,State:,,state,,[please select your state],,state',
				'text4'		=> 'zip,Zip/Postal Code:,,zip,,[please enter your zip code]',
				'select2'	=> 'country,Country:,,country,,[please select your country],US,country',
				'textarea'	=> 'comments,Comments:,,comments,,[please enter some comments]',
				'submit'	=> 'submit,,Submit Form,submit,class="btn-primary"'
			);
			return $data;
		
		} else {
			
			/**
				now we'll build the corresponding key and human readable text and validation rules for the fastpost() method
				the key MUST match the field name! Separate your validation rules with a pipe | character, NOT a comma!
				
				'field name' => 'human readable text, validation rules'
			*/
			
			$data = array(
				'fname' 	=> array('Please enter your first name'),
				'lname'		=> array('Please enter your last name'),
				'email'		=> array('Please enter your email address','valid_email'),
				'city'		=> array('Please enter your city'),
				'state'		=> array('Please select your state'),
				'zip'		=> array('Please enter your zip code','int|min_length[5]|max_length[10]'),
				'country'	=> array('Please select your country'),
				'comments'	=> array('Please enter your comments')
			);
			return $data;
		}
		
	}
	
	
	
	public static function short_contact($validate='') {
		
		if(!$validate) {
			
			// here we'll build the form array for the fastform() function
			
			$data = array(
				'text1'		=> 'fname,First name:,,fname',
				'text2'		=> 'lname,Last name:,,lname',
				'email'		=> 'email,Email:,,email',
				'textarea'	=> 'comments,Comments:,,comments'
			);
			return $data;
		
		} else {
			
			// now we'll build the corresponding key and human readable text and validation rules for the fastpost() function
			$data = array(
				'fname' 	=> array('First name'),
				'lname'		=> array('Last name'),
				'email'		=> array('Email address','valid_email'),
				'comments'	=> array('Comments')
			);
			return $data;
		}
		
	}
	
	
	
	public static function signup($validate='') {
		
		if(!$validate) {
			
			// here we'll build the form array for the fastform() function
			$data = array(
				'text1'		=> 'email,Email:,,email',
				'password2'	=> 'password,Password:,,password,placeholder="password"',
				'password3'	=> 'confirm,Confirm password:,,confirm,placeholder="confirm password"',
				
			);
			return $data;
		
		} else {
			
			// now we'll build the corresponding key and human readable text and validation rules for the fastpost() function
			$data = array(
				'email'		=> array('Email address','valid_email'),
				'password'	=> array('Password','min_length[6]|crypt'),
				'confirm'	=> array('Confirm Password','min_length[6]|matches[password]|crypt'),
			);
			return $data;
		}
	}
	
	
	
	public static function login($validate='') {
		
		if(!$validate) {
			
			// here we'll build the form array for the fastform() function
			$data = array(
				'text'		=> 'username,,,username,placeholder="username"',
				'password'	=> 'password,,,password,placeholder="password"',
				'submit'	=> 'submit,,Login,,class="btn-primary"'
			);
			return $data;
		
		} else {
			
			// build the corresponding key and human readable text and validation rules for the fastpost() function
			$data = array(
				'username'	=> array('Username','required'),
				'password'	=> array('Password','required|hash'),
			);
			return $data;
		}
	}
	
	
	
	public static function canadian_contact($validate='') {
		
		if(!$validate) {
			
			// here we'll build the form array for the fastform() function
			$data = array(
				'text1'		=> 'fname,First name:,,fname',
				'text2'		=> 'lname,Last name:,,lname',
				'email'		=> 'email,Email:,,email',
				'text3'		=> 'city,City:,,city',
				'select1'	=> 'province,Province:,,province,,,,province',
				'text4'		=> 'zip,Zip/Postal Code:,,zip',
				'select2'	=> 'country,Country:,,country,,,CA,country',
				'textarea'	=> 'comments,Comments:,,comments'
			);
			return $data;
		
		} else {
			
			// build the cooresponding key, human readable text and validation rules for the fastpost() function
			$data = array(
				'fname' 	=> array('First name'),
				'lname'		=> array('Last name'),
				'email'		=> array('Email address','valid_email'),
				'city'		=> array('City'),
				'province'	=> array('Province'),
				'postal'	=> array('Postal code','alphanumeric|min_length[6]|max_length[7]'),
				'country'	=> array('Country'),
				'comments'	=> array('Comments')
			);
			return $data;
		}
		
	}
		
	
}