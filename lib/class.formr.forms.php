<?php

use Formr\Formr;

class Forms extends Formr
{
    # these methods are used to wrap your form elements.
    # documentation: https://github.com/formr/extend

    public static function contact($validate = ''): array
    {
        if (! $validate) {
            /**
             * this section is where we'll build the form using an array. the array key contains
             * the input type, and the array value contains all of the field's attributes.
             * 'array_key'  => 'array value'
             * 'field input type' => 'name , label text , value , input ID , string , .inline-text , selected/checked , $options'
             */

            return [
                'text1' => 'fname,First name:,,fname,,[please enter your first name]',
                'text2' => 'lname,Last name:,,lname,,[please enter your last name]',
                'email' => 'email,Email:,,email,,[please enter your email address]',
                'text3' => 'city,City:,,city,,[please enter your city]',
                'select1' => 'state,State:,,state,,[please select your state],,state',
                'text4' => 'zip,Zip/Postal Code:,,zip,,[please enter your zip code]',
                'select2' => 'country,Country:,,country,,[please select your country],US,country',
                'textarea' => 'comments,Comments:,,comments,,[please enter some comments]',
                'submit' => 'submit,,Submit Form,submit'
            ];
        } else {
            /**
             * now we'll build the corresponding key and human readable text and validation rules for the fastpost() method.
             * the key MUST match the field name! Separate your validation rules with a pipe | character, NOT a comma!
             * 'field name' => '[human readable text, validation rules']
             */

            return [
                'fname' => ['Please enter your first name'],
                'lname' => ['Please enter your last name'],
                'email' => ['Please enter your email address', 'valid_email'],
                'city' => ['Please enter your city'],
                'state' => ['Please select your state'],
                'zip' => ['Please enter your zip code', 'int|min_length[5]|max_length[10]'],
                'country' => ['Please select your country'],
                'comments' => ['Please enter your comments']
            ];
        }
    }

    public static function short_contact($validate = ''): array
    {
        if (! $validate) {
            # here we'll build the form array for the fastform() function
            return [
                'text1' => 'fname,First name:,,fname',
                'text2' => 'lname,Last name:,,lname',
                'email' => 'email,Email:,,email',
                'textarea' => 'comments,Comments:,,comments'
            ];
        } else {
            # now we'll build the corresponding key and human readable text and validation rules for the fastpost() function
            return [
                'fname' => ['First name'],
                'lname' => ['Last name'],
                'email' => ['Email address', 'valid_email'],
                'comments' => ['Comments']
            ];
        }
    }

    public static function signup($validate = ''): array
    {
        if (! $validate) {
            # here we'll build the form array for the fastform() function
            return [
                'text1' => 'email,Email:,,email',
                'password2' => 'password,Password:,,password',
                'password3' => 'confirm,Confirm password:,,confirm'
            ];
        } else {
            # now we'll build the corresponding key and human readable text and validation rules for the fastpost() function
            return [
                'email' => ['Email address', 'valid_email'],
                'password' => ['Password', 'min_length[6]|hash'],
                'confirm' => ['Confirm Password', 'min_length[6]|matches[password]']
            ];
        }
    }

    # alias for signup
    public static function registration($validate = ''): array
    {
        return static::signup($validate);
    }

    public static function login($validate = ''): array
    {
        if (! $validate) {
            # here we'll build the form array for the fastform() function
            return [
                'text' => 'username,,,username,placeholder="username"',
                'password' => 'password,,,password,placeholder="password"',
                'submit' => 'submit,,Login'
            ];
        } else {
            # build the corresponding key and human readable text and validation rules for the fastpost() function
            return [
                'username' => ['Username', 'required'],
                'password' => ['Password', 'required|hash']
            ];
        }
    }

    public static function canadian_contact($validate = ''): array
    {
        if (! $validate) {
            # here we'll build the form array for the fastform() function
            return [
                'text1' => 'fname,First name:,,fname',
                'text2' => 'lname,Last name:,,lname',
                'email' => 'email,Email:,,email',
                'text3' => 'city,City:,,city',
                'select1' => 'province,Province:,,province,,,,province',
                'text4' => 'zip,Zip/Postal Code:,,zip',
                'select2' => 'country,Country:,,country,,,CA,country',
                'textarea' => 'comments,Comments:,,comments'
            ];
        } else {
            # build the corresponding key, human readable text and validation rules for the fastpost() function
            return [
                'fname' => ['First name'],
                'lname' => ['Last name'],
                'email' => ['Email address', 'valid_email'],
                'city' => ['City'],
                'province' => ['Province'],
                'postal' => ['Postal code', 'alphanumeric|min_length[6]|max_length[7]'],
                'country' => ['Country'],
                'comments' => ['Comments']
            ];
        }
    }
}
