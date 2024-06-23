<?php

namespace Formr;

/**
 * Formr (1.5.0)
 * a php library for rapid form development
 * https://formr.github.io
 * requires php >= 8.1 and gd (for uploads)
 * copyright(c) 2013-2024 Tim Gavin
 * https://github.com/timgavin
 **/

# load the default classes
require_once 'lib/class.formr.dropdowns.php';
require_once 'lib/class.formr.forms.php';
require_once 'lib/class.formr.wrappers.php';

# load the 'plugin' classes
if (file_exists(__DIR__.'/my_classes/my.wrappers.php')) {
    require_once __DIR__.'/my_classes/my.wrappers.php';
}

if (file_exists(__DIR__.'/my_classes/my.dropdowns.php')) {
    require_once __DIR__.'/my_classes/my.dropdowns.php';
}

if (file_exists(__DIR__.'/my_classes/my.forms.php')) {
    require_once __DIR__.'/my_classes/my.forms.php';
}

class Formr
{
    public $version = '1.5.0';

    # each of these public properties acts as a 'preference' for Formr
    # and can be defined after instantiation. see documentation for more info.

    # default form action (useful with fastform())
    public $action;

    # default character set
    public $charset = 'utf-8';

    # comment each form field for easier debugging
    public $comments = false;

    # suppress Formr's validation error messages and only display your own
    public $custom_validation_messages = false;

    # default string delimiters
    # $delimiter[0] is for separating field values in fastform()
    # $delimiter[1] is for parsing values within fastform() strings and the post() validation rules
    # example : input_text('Name $delimiter[0] Label $delimiter[0] Value[Value1 $delimiter[1] Value2 $delimiter[1] Value3 ]');
    # example : form->post('email','Email','valid_email $delimiter[1] min[3] $delimiter[1] max[60]')
    # property was made public so you can modify it as you see fit
    public $delimiter = [',', '|'];

    # default doctype
    public $doctype = 'html';

    # default error message header
    public $error_heading_plural = 'Please Correct the Following Errors';

    # default error message header
    public $error_heading_singular = 'Please Correct the Following Error';

    # add an error message to messages()
    public $error_message;

    # create an empty errors array for form validation
    public $errors = [];

    # format dates for validation rules
    public $format_rule_dates = 'M d, Y';

    # add a honeypot field
    public $honeypot;

    # sanitize input with HTMLPurifier
    public $html_purifier;

    # form's ID
    public $id;

    # add an info message to messages()
    public $info_message;

    # inline validation is off by default
    public $inline_errors = false;

    # inline validation CSS class: displays error icon next to form fields
    public $inline_errors_class = 'error_inline';

    # link from error messages to related fields by setting anchor tags
    public $link_errors = false;

    # default form method (useful with fastform())
    public $method = 'post';

    # removes all line breaks and minifies code
    public $minify = false;

    # form's name
    public $name;

    # adds a new line: \r\n
    public $nl;

    # Google ReCaptcha v3
    # get keys here: https://www.google.com/recaptcha/admin/create
    public $recaptcha_action_name;
    public $recaptcha_score = 0.5;
    public $recaptcha_secret_key;
    public $recaptcha_site_key;
    public $recaptcha_use_curl = false;

    # form fields are not required by default
    public $required = false;

    # visually lets the user know a field is required inside the field's label tag
    public $required_indicator = '';

    # use a salt when hashing
    public $salt;

    # sanitize html $_POST values with FILTER_SANITIZE_SPECIAL_CHARS
    public $sanitize_html = false;

    # define a session
    public $session;

    # use session values in form fields on page load
    public $session_values;

    # show valid status (green outline) on fields if using a framework
    public $show_valid = false;

    # default submit button value
    public $submit = 'Submit';

    # add a success message to messages()
    public $success_message;

    # accepted mime types for uploading files
    public $upload_accepted_mimes;

    # accepted file types for uploading files
    public $upload_accepted_types;

    # the full path to the directory in which we're uploading files
    public $upload_dir;

    # max file size for uploaded files (2MB)
    public $upload_max_filesize = 2097152;

    # rename a file after upload
    public $upload_rename;

    # resize images after upload
    public $upload_resize;

    # init the $uploads property
    public $uploads = true;

    # add a warning message to messages()
    public $warning_message;

    # put default class names into an array
    private $controls = [];

    # default wrapper types which Formr supports
    private $default_wrapper_types = ['div', 'p', 'ul', 'ol', 'dl', 'li'];

    # we can turn off automatic echoing of elements in the constructor
    private $echo;

    # exclude these input types from certain operations, namely classes and wrappers
    protected $excluded_types = ['submit', 'button', 'reset', 'checkbox', 'radio'];

    # we don't want to create form attributes from these keywords if they're in the $data array
    private $no_keys = ['string', 'checked', 'selected', 'required', 'inline', 'label', 'fastform', 'options', 'group', 'multiple'];

    # used with checkbox arrays
    protected $checkbox_values;

    # used in lib classes
    protected Formr $formr;

    # used when validating input
    protected $required_fields;

    # use Formr's default <div> wrapper for elements
    protected $use_default_wrapper = true;

    # toggle wrapping <div> for elements
    protected $use_element_wrapper_div;

    # the formr wrapper
    protected $wrapper;
    protected $wrapper_obj;

    function __construct($wrapper = '', $switch = '')
    {
        $this->instantiateWrapper($wrapper);

        # create the Formr session
        if (! isset($_SESSION['formr'])) {
            $_SESSION['formr'] = [];
        }

        # for checkbox array values
        $this->checkbox_values = [];

        # determine if we're switching things on/off
        $switches = array_map('trim', explode(',', $switch));

        # determines if echoing elements & messages should be suppressed
        $this->echo = (in_array('hush', $switches) ? 'hush' : null);

        # determines if we should *not* wrap our form elements in a <div>
        $this->use_element_wrapper_div = ! in_array('nowrap', $switches);
    }


    # WRAPPER
    protected function instantiateWrapper($wrapper): void
    {
        $wrapper = strtolower(trim($wrapper));

        # determine our field wrapper and CSS classes
        $wrapper_css = $wrapper.'_css';

        if (empty($wrapper)) {
            # no wrapper specified, use Formr's default wrapper
            $this->wrapper = '';
            $this->wrapper_obj = new \Wrapper($this);
            $this->controls = \Wrapper::default_css();
        } elseif (class_exists('\MyWrappers') && method_exists('\MyWrappers', $wrapper_css) && is_callable(['MyWrappers', $wrapper_css])) {
            # user-created wrapper
            $this->wrapper = $wrapper;
            $this->wrapper_obj = new \MyWrappers($this);
            $this->controls = \MyWrappers::$wrapper_css();
        } elseif (class_exists('\Wrapper') && method_exists(\Wrapper::class, $wrapper_css) && is_callable([\Wrapper::class, $wrapper_css])) {
            # user-defined wrapper (bootstrap, bulma, etc.)
            $this->wrapper = $wrapper;
            $this->wrapper_obj = new \Wrapper($this);
            $this->controls = \Wrapper::$wrapper_css();
        } else {
            $this->_error_message("<h4>Wrapper Not Found</h4><p>If you are using Custom Wrappers, please make sure the Custom Wrapper file is located at <code>my_classes/my.wrappers.php</code>, and that you spelled your Wrapper name correctly.</p><p>If you are NOT using Custom Wrappers, please make sure a file does not exist at <code>my_classes/my.wrappers.php</code>");
            exit;
        }
    }

    protected function printWrapperMessages($data): string
    {
        if (! empty($data['inline']) && $this->is_in_brackets($data['inline'])) {
            if ($this->in_errors($data['name'])) {
                return "<p class=\"{$this->controls['help']} {$this->controls['is-invalid']}\">".trim($data['inline'], '[]')."</p>".PHP_EOL;
            } else {
                return "<p class=\"{$this->controls['help']}\">".trim($data['inline'], '[]')."</p>".PHP_EOL;
            }
        } elseif ($this->in_errors($data['name'])) {
            return "<p class=\"{$this->controls['help']} {$this->controls['is-invalid']}\">{$this->errors[$data['name']]}</p>".PHP_EOL;
        }

        return '';
    }

    protected function printWrapperLabel($data): string
    {
        if (! empty($data['label'])) {
            return "<label class=\"{$this->controls['label']}\">{$data['label']}".$this->insert_required_indicator($data)."</label>".PHP_EOL;
        }

        return '';
    }

    protected function _wrapper_type(): array
    {
        # determines what our field element wrapper will be

        $return = [];

        if (is_array($this->wrapper)) {
            # the user entered a custom wrapper
            $return['type'] = 'array';
            $return['open'] = $this->wrapper[0];
            $return['close'] = $this->wrapper[1];
        } else {
            # use a pre-defined wrapper
            if (! in_array($this->wrapper, ['ul', 'ol', 'dl', 'p', 'div'])) {
                # set the wrapper's name
                $return['type'] = $this->wrapper;
                $return['open'] = $return['close'] = null;
            }

            # if tags were entered, strip the brackets
            $str = strtolower(trim($this->wrapper, '<>'));

            # wrapper is a list
            if ($str == 'ul') {
                $return['type'] = 'ul';
                $return['open'] = '<ul class="'.$this->controls['list-ul'].'">';
                $return['close'] = '</ul>';
            }
            if ($str == 'ol') {
                $return['type'] = 'ol';
                $return['open'] = '<ol class="'.$this->controls['list-ol'].'">';
                $return['close'] = '</ol>';
            }
            if ($str == 'dl') {
                $return['type'] = 'dl';
                $return['open'] = '<dl class="'.$this->controls['list-dl'].'">';
                $return['close'] = '</dl>';
            }

            # wrapper is a <p>
            if ($str == 'p') {
                $return['type'] = 'p';
                $return['open'] = '<p>';
                $return['close'] = '</p>';
            }

            # wrapper is a <div>
            if ($str == 'div') {
                $return['type'] = 'div';
                $return['open'] = '<div>';
                $return['close'] = '</div>';
            }
        }

        return $return;
    }

    protected function _wrapper($element, $data)
    {
        # wraps and formats field elements
        # $element is the field element in HTML
        # $data is the $data array containing the element's attributes

        # get the wrapper type
        $wrapper_context = $this->_wrapper_type();

        # enclose the element in a custom field wrapper (such as bootstrap) from the Wrapper class
        if (! empty($this->wrapper) && ! in_array($this->wrapper, $this->default_wrapper_types)) {
            # dynamically build the method's name...
            # $method = the method's name in the Wrapper class
            $method = $wrapper_context['type'];

            return $this->_echo($this->wrapper_obj->$method($element, $data));
        }

        # enclose the element in the default wrapper
        return $this->_echo($this->wrapper_obj->default_wrapper($wrapper_context, $element, $data));
    }

    protected function _open_list_wrapper()
    {
        $wrapper = $this->_wrapper_type();

        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol' || $wrapper['type'] == 'dl') {
            return $wrapper['open'];
        }

        return null;
    }

    protected function _close_list_wrapper()
    {
        $wrapper = $this->_wrapper_type();

        if ($wrapper['type'] == 'ul' || $wrapper['type'] == 'ol' || $wrapper['type'] == 'dl') {
            return $wrapper['close'];
        }

        return null;
    }


    # HELPERS & UTILITY
    public function printr($data, $die = null)
    {
        # aids in debugging by not making you have to type all of
        # this nonsense out each time you want to print_r() something

        echo '<pre>';

        if ($data === 'POST') {
            print_r($_POST);
        } elseif ($data === 'GET') {
            print_r($_GET);
        } else {
            print_r($data);
        }

        echo '</pre>';

        if ($die) {
            exit;
        }
    }

    public function dd($data)
    {
        # same as printr() but kills the script

        $this->printr($data, 'die');
    }

    public function dump($data)
    {
        # alias of printr()

        $this->printr($data);
    }

    protected function _echo($data)
    {
        # echo everything unless 'hush' was passed during init

        if ($this->echo == 'hush') {
            return $data;
        }

        echo $data;

        return null;
    }

    public function form_info()
    {
        # prints the current form settings

        # set some defaults
        $info = [
            'Formr Version' => $this->version,
            'PHP Version' => phpversion(),
            'Form ID' => ! empty($this->id) ? $this->id : 'myForm',
            'Form name' => ! empty($this->name) ? $this->name : 'myForm',
            'Form method' => strtoupper($this->method),
            'Charset' => $this->charset,
            'All Fields Required' => $this->required == '*' ? 'TRUE' : 'FALSE',
            'Link to Error' => $this->link_errors ? 'TRUE' : 'FALSE',
            'Inline Validation' => $this->inline_errors ? 'TRUE' : 'FALSE',
            'Required Indicator' => htmlspecialchars($this->required_indicator),
            'FastForm Wrapper' => htmlspecialchars($this->wrapper),
            'HTML Purifier' => 'FALSE',
        ];

        $return = '';

        if (isset($this->html_purifier)) {
            if (! file_exists($this->html_purifier)) {
                $info['HTML Purifier'] = 'Can\'t find class at the specified path';
            } else {
                $info['HTML Purifier'] = 'TRUE';
            }
        }

        $return .= '<table class="table table-sm">';
        foreach ($info as $key => $value) {
            $return .= '<tr><td><strong>'.$key.'</strong></td><td>'.$value.'</td></tr>';
        }
        $return .= '</table>';

        $return = str_replace('TRUE', '<span style="color:green">TRUE</span>', $return);
        $return = str_replace('FALSE', '<span style="color:red">FALSE</span>', $return);

        return $this->_echo('<h3>Form Settings</h3><pre>'.$return.'</pre><br><br><br>');
    }

    public function info()
    {
        # alias of form_info()
        return $this->form_info();
    }

    public function honeypot($name)
    {
        if ($this->honeypot) {
            $this->_error_message("Sorry! You can only have one Honeypot per form.<br>Please consider removing <code>\$form->honeypot=\"".$this->honeypot."\"</code> from your code.");
        }

        $this->honeypot = $name;

        return $this->_echo('<input type="text" name="'.$name.'" value="" style="display:none">');
    }

    public function submit($form_id = null)
    {
        $this->_check_for_honeypot();

        # checks if submit button was clicked
        if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
            $this->_check_for_csrf();
            $this->_handle_session_checkbox_arrays();

            # checks for a form id to see which form was submitted
            if ($form_id) {
                foreach ($_POST as $key => $value) {
                    if ($key == 'FormrID' && $value == $form_id) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }

        return false;
    }

    public function submitted($form_id = null)
    {
        # alias of submit()
        return $this->submit($form_id);
    }

    public function in_errors($key)
    {
        # checks the errors array for the supplied key

        $key = trim($key, '[]');

        if (in_array($key, $this->errors) || array_key_exists($key, $this->errors)) {
            return true;
        }

        # check if a custom message was added via add_to_errors()
        foreach ($this->errors as $string) {
            # get the key, which comes before the pipe delimiter
            if (strstr($string, '|', true) == $key) {
                return true;
            }
        }

        return false;
    }

    public function in_errors_if($key, $string)
    {
        # if the key is in the errors array, return a user-defined string
        if ($this->in_errors($key)) {
            return $this->_echo($string);
        }

        return false;
    }

    public function in_errors_else($key, $error_string, $default_string)
    {
        # return a different user-defined string depending on if the field is in the errors array
        if (! $this->in_errors($key)) {
            return $this->_echo($default_string);
        }

        return $this->_echo($error_string);
    }

    public function errors()
    {
        # checks the errors array and returns the errors
        if (! empty($this->errors)) {
            return $this->errors;
        }

        return null;
    }

    public function add_to_errors($string)
    {
        # add a string to the errors array
        $this->errors[] = $string;
    }

    public function value($name, $value = '')
    {
        # return SESSION value
        if ($this->session) {
            if (isset($_POST[$name])) {
                $_SESSION[$this->session][$name] = $this->_clean_value($_POST[$name]);
                return true;
            }

            return false;
        }

        # return POSTed field value
        if (isset($_POST[$name])) {
            return $this->_echo($this->_clean_value($_POST[$name]));
        } elseif ($value !== '') {
            return $this->_echo($value);
        }

        return null;
    }

    public function slug($string)
    {
        # create a twitter-style username...
        # allow only letters, numbers and underscores
        $return = str_replace('-', '_', $string);
        $return = str_replace(' ', '_', $return);
        return preg_replace('/[^A-Za-z0-9_]/', '', $return);
    }

    protected function _generate_hash($length = 32)
    {
        # don't add vowels and we won't get dirty words...
        $chars = 'BCDFGHJKLMNPQRSTVWXYZbcdfghjklmnpqrstvwxyz1234567890';

        # length of character list
        $chars_length = (strlen($chars) - 1);

        # create our string
        $string = $chars[rand(0, $chars_length)];

        # generate random string
        for ($i = 1; $i < $length; $i = strlen($string)) {
            # grab a random character
            $r = $chars[rand(0, $chars_length)];

            # make sure the same characters don't appear next to each other
            if ($r != $string[$i - 1]) {
                $string .= $r;
            }
        }

        return $string;
    }

    protected function _input_types($type)
    {
        # defines input types for use in other methods
        if ($type == 'button') {
            return ['submit', 'reset', 'button'];
        }

        if ($type == 'checkbox') {
            return ['checkbox', 'radio'];
        }

        if ($type == 'text') {
            return ['text', 'textarea', 'password', 'color', 'email', 'date', 'datetime', 'datetime_local', 'month', 'number', 'range', 'search', 'tel', 'time', 'url', 'week'];
        }

        return null;
    }

    protected function _fix_classes($element, $data)
    {
        # automatically add Formr (and framework) CSS classes to elements

        $classes = null;

        // get the css classes (if any)
        if (! empty($data['string']) && preg_match('/class="(.*?)"/', $data['string'], $match) == 1) {
            $classes = $match[1];
        }

        # strip the classes - and class element - and get the rest of the string parameter
        $class_string = 'class="'.$classes.'"';
        $string = ! empty($data['string']) ? (str_replace($class_string, '', $data['string'])) : '';

        # add default classes
        if (! empty($this->controls['input']) && ! in_array($data['type'], $this->excluded_types)) {
            if ($data['type'] == 'file') {
                $classes .= ' '.$this->controls['file'];
            } elseif ($data['type'] == 'textarea' && $this->wrapper == 'bulma') {
                $classes .= $this->controls['textarea'];
            } else {
                $classes .= ' '.$this->controls['input'];
            }
        }

        # bootstrap inline checkboxes & radios
        if ($this->type_is_checkbox($data)) {
            if (isset($data['checkbox-inline'])) {
                $classes .= ' '.$this->controls['checkbox-inline'];
            } elseif (isset($data['checkbox'])) {
                $classes .= ' '.$this->controls['checkbox'];
            } else {
                $classes .= ' checkbox';
            }
        }

        if ($this->in_errors($data['name'])) {
            # add 'error' class on element
            if ($this->_wrapper_is('framework')) {
                foreach ($_POST as $key => $value) {
                    if ($key == $data['name']) {
                        $classes .= ' '.$this->controls['is-invalid'];
                    }
                }
            } else {
                $classes .= ' '.$this->controls['text-error'];
            }
        } elseif ($this->submitted() && $this->show_valid) {
            # add 'success' class on element
            if ($this->_wrapper_is('framework')) {
                foreach ($_POST as $key => $value) {
                    if ($key == $data['name']) {
                        $classes .= ' '.$this->controls['is-valid'];
                    }
                }
            }
        }

        if (empty($class_string)) {
            if ($data['type'] == 'submit' || $data['type'] == 'button') {
                if ($this->_wrapper_is('bootstrap')) {
                    $classes = $this->controls['button-primary'];
                } else {
                    $classes = $this->controls['button'];
                }
            }
        }

        return ' class="'.$classes.'" '.$string;
    }

    protected function _set_array_values($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '')
    {
        # puts the entered strings into an array
        if (! is_array($data)) {
            $data = [
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline,
                'selected' => $selected,
                'options' => $options
            ];
        }

        return $data;
    }

    protected function _clean_value($str = '', $allow_html = false)
    {
        # makes entered values a little safer.

        # this function was left somewhat sparse because i didn't want to assume i knew what kind of data you were going to allow,
        # so i just put in some basic sanitizing functions. if you want more control, just tweak this to your heart's desire! :)

        # Formr can also use HTMLPurifier
        # just download the HTMLPurifier class and drop it in at the top of this script. Formr will do the rest.
        # http://htmlpurifier.org

        # return an empty value
        if ($str == '') {
            return '';
        }

        if ($this->html_purifier && file_exists($this->html_purifier) && class_exists('\HTMLPurifier') && class_exists('\HTMLPurifier_Config')) {
            # we're using HTML Purifier

            if ($this->charset == strtolower('utf-8')) {
                # include the HTML Purifier class
                require_once($this->html_purifier);

                # set it up using default settings (feel free to alter these if needed)
                $p_config = \HTMLPurifier_Config::createDefault();
                $purifier = new \HTMLPurifier($p_config);
                return $purifier->purify($str);
            } else {
                $config = \HTMLPurifier_Config::createDefault();
                $config->set('Core', 'Encoding', $this->charset);
                $config->set('HTML', 'Doctype', $this->doctype);
                $purifier = new \HTMLPurifier($config);
            }
        } else {
            if (is_string($str)) {
                $str = trim($str);

                # perform basic sanitization...
                if (! $allow_html) {
                    # strip html tags and prevent against xss
                    $str = strip_tags($str);
                } else {
                    # allow html
                    if ($this->sanitize_html) {
                        $str = filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }

                return $str;
            } else {
                # clean and return the array
                $value = '';

                foreach ($str as $value) {
                    if (! $allow_html) {
                        # strip html tags and prevent against xss
                        $value = strip_tags($value);
                    } else {
                        # allow html
                        if ($this->sanitize_html) {
                            $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                        }
                    }
                }

                return $value;
            }
        }

        return null;
    }

    protected function _build_input_groups($data)
    {
        # we're building a checkbox or radio group based on multiple field names inside $data['value']
        # check if $data['value'] starts with a left bracket
        # if so, we know we have multiple values

        if ($this->is_in_brackets($data['value'])) {
            $return = null;

            # the values are comma-delimited, trim the brackets and break the value apart
            $additional_fields = explode($this->delimiter[1], trim($data['value'], '[]'));

            # output the label text for the group
            if ($this->wrapper == 'bootstrap') {
                # bootstrap control-label
                $return .= '<label class="'.$this->controls['label'].'">'.$data['label'].'</label>'.PHP_EOL;
            } else {
                # 'regular' label
                $return .= '<label>'.$data['label'].'</label>'.PHP_EOL;
            }

            # make sure we're dealing with an array, just to be safe
            if (is_array($additional_fields)) {
                # output the label text for the group
                if (! $this->type_is_checkbox($data)) {
                    $return .= "\t".$this->label($data);
                }

                # loop through each new field name and print it out - wrapped in a label
                foreach ($additional_fields as $key => $value) {
                    # make the element's label the same as the value
                    $data['label'] = ucwords($value);

                    # add the element's value
                    $data['value'] = $value;

                    # make the ID the element's name so that the label is clickable
                    $data['id'] = $value;

                    $data['group'] = true;

                    # if using bootstrap, wrap the elements in a bootstrap class
                    if ($this->_wrapper_is('bootstrap') && ! $this->type_is_checkbox($data)) {
                        $return .= "\t".'<div class="'.$this->controls[$data['type']].'">';
                    }

                    # return the element wrapped in a label
                    if ($this->_wrapper_is('bootstrap') && $this->type_is_checkbox($data)) {
                        $return .= "<div class=\"form-check form-check-inline\">";
                        $return .= $this->_create_input($data);
                        $return .= "<label class=\"form-check-label\" for=\"{$this->make_id($data)}\">{$data['label']}</label>";
                        $return .= "</div>";
                    } else {
                        $return .= "<label for=\"{$this->make_id($data)}\">";
                        $return .= $this->_create_input($data);
                        $return .= $data['label'];
                        $return .= "</label>";
                    }

                    # close the bootstrap class
                    if ($this->_wrapper_is('bootstrap') && ! $this->type_is_checkbox($data)) {
                        $return .= PHP_EOL."\t".'</div>'.PHP_EOL;
                    }
                }
            }

            return $return;
        }

        return false;
    }

    protected function _comment($string)
    {
        # creates an HTML comment

        if ($this->minify || ! $this->comments) {
            return false;
        }

        return '<!-- '.$string.' -->';
    }

    protected function _print_field_comment($data)
    {
        # returns the HTML field comment for display in the wrapper

        if (in_array($data['type'], $this->_input_types('checkbox'))) {
            return $this->_comment($data['id']);
        }

        return $this->_comment($data['name']);
    }

    protected function _check_filesize($handle)
    {
        $kb = 1024;
        $mb = $kb * 1024;

        if ($handle['size'] > $this->upload_max_filesize) {
            # convert bytes to megabytes because it's more human readable
            $size = round($this->upload_max_filesize / $mb, 2).' MB';

            $this->errors['file-size'] = 'File size exceeded. The file can not be larger than '.$size;

            return true;
        }

        return false;
    }

    protected function _get_file_extension($handle)
    {
        # get a file's extension
        return strtolower(ltrim(strrchr($handle['name'], '.'), '.'));
    }

    protected function _check_upload_accepted_types($handle)
    {
        # get the accepted file types
        # we can check either the extension or the mime type, depending on what the user entered

        if (! $this->_upload_accepted_types() && ! $this->_upload_accepted_mimes()) {
            $this->errors['accepted-types'] = 'Oops! You must specify the allowed file types using either $upload_accepted_types or $upload_accepted_mimes.';
            return false;
        }

        # see if it's in the accepted upload types
        if ($this->upload_accepted_types && ! in_array($handle['ext'], $this->_upload_accepted_types())) {
            $this->errors['accepted-types'] = 'Oops! The file was not uploaded because it is in an unsupported file type.';
            return false;
        }

        $parts = getimagesize($handle['tmp_name']);

        # see if it's in the accepted mime types
        if ($this->upload_accepted_mimes && ! in_array($parts['mime'], $this->_upload_accepted_mimes())) {
            $this->errors['accepted-types'] = 'Oops! The file was not uploaded because it is an unsupported mime type.';
            return false;
        }

        return true;
    }

    protected function _slug_filename($filename)
    {
        # slug the filename to make it safer
        return strtolower(preg_replace('/[^A-Z0-9._-]/i', '_', $filename));
    }

    protected function _rename_file($handle)
    {
        $new_filename = null;

        # if the file extension is .jpeg, rename to .jpg
        if ($handle['ext'] == 'jpeg') {
            $handle['ext'] = 'jpg';
        }

        # rename the uploaded file with a unique hash
        if (mb_substr($this->upload_rename, 0, 4) == 'hash') {
            # user wants to specify the length of the hash
            if (mb_substr($this->upload_rename, 0, 5) == 'hash[') {
                # get the length of the hash
                $length = trim($this->upload_rename, 'hash[]');

                # rename the file
                $new_filename = $this->_generate_hash($length).'.'.$handle['ext'];
            } else {
                # rename with the default hash length
                $new_filename = $this->_generate_hash().'.'.$handle['ext'];
            }
        }

        # rename the uploaded file with a custom string
        if (mb_substr($this->upload_rename, 0, 6) == 'string') {
            # strip everything which surrounds our new filename
            $string = str_replace('string[', '', rtrim($this->upload_rename, ']'));

            # append the uploaded file's extension to our new filename
            $new_filename = $string.'.'.$handle['ext'];

            # generate a random filename if there is already a file with the same name
            if (file_exists($this->upload_dir.'/'.$new_filename)) {
                $new_filename = $string.'-'.mt_rand(1000, 9999).'.'.$handle['ext'];
            }
        }

        # rename the uploaded file with a timestamp
        if ($this->upload_rename == 'timestamp') {
            $new_filename = time().'.'.$handle['ext'];
        }

        # rename the uploaded file with a prepended string
        if (str_starts_with($this->upload_rename, 'prepend')) {
            # strip the brackets from our prepend string
            $prepend = trim($this->upload_rename, 'prepend[]');

            # get the file extension
            $ext = '.'.$handle['ext'];

            # remove the extension from the file name
            $name = str_replace($ext, '', $handle['name']);

            $new_filename = $prepend.$name.'.'.$handle['ext'];
        }

        return $new_filename;
    }

    protected function _upload_accepted_types()
    {
        if ($this->upload_accepted_types) {
            # we're allowing jpg, gif and png
            if ($this->upload_accepted_types == 'images') {
                return ['jpg', 'jpeg', 'gif', 'png'];
            }

            # explode the accepted file types into an array
            return explode(',', str_replace('.', '', $this->upload_accepted_types));
        }

        return false;
    }

    protected function _upload_accepted_mimes()
    {
        if ($this->upload_accepted_mimes) {
            # we're allowing jpg, gif and png
            if ($this->upload_accepted_mimes == 'images') {
                return ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'];
            }

            # explode the accepted file types into an array
            return explode(',', $this->upload_accepted_mimes);
        }

        return false;
    }

    protected function _upload_files($name)
    {
        # don't upload if there are form errors
        if (! empty($this->errors)) {
            return false;
        }

        $files = [];

        if (! empty($_FILES[$name]['tmp_name']) && ! empty($_FILES[$name]['tmp_name'][0])) {
            if (is_array($_FILES[$name]['tmp_name']) && count($_FILES[$name]['tmp_name']) > 1) {
                # we're dealing with multiple uploads
                for ($i = 0; $i < count($_FILES[$name]['tmp_name']); $i++) {
                    if (! empty($_FILES[$name]['tmp_name']) && is_uploaded_file($_FILES[$name]['tmp_name'][$i])) {
                        # make for a prettier array and reassign the key/values
                        $handle['key'] = $name;
                        $handle['name'] = $_FILES[$name]['name'][$i];
                        $handle['size'] = $_FILES[$name]['size'][$i];
                        $handle['type'] = $_FILES[$name]['type'][$i];
                        $handle['tmp_name'] = $_FILES[$name]['tmp_name'][$i];

                        # put each array into the $files array
                        $files[] = $this->_process_image($handle);
                    }
                }

                return $files;
            } else {
                # we're dealing with a single upload
                if (is_uploaded_file($_FILES[$name]['tmp_name'][0])) {
                    # we're using the input_upload_multiple() method, so we have to compensate for the array

                    # make for a prettier array and reassign the key/values
                    $handle['key'] = $name;
                    $handle['name'] = $_FILES[$name]['name'][0];
                    $handle['size'] = $_FILES[$name]['size'][0];
                    $handle['type'] = $_FILES[$name]['type'][0];
                    $handle['tmp_name'] = $_FILES[$name]['tmp_name'][0];
                } elseif (is_uploaded_file($_FILES[$name]['tmp_name'])) {
                    # make for a prettier array and reassign the key/values
                    $handle['key'] = $name;
                    $handle['name'] = $_FILES[$name]['name'];
                    $handle['size'] = $_FILES[$name]['size'];
                    $handle['type'] = $_FILES[$name]['type'];
                    $handle['tmp_name'] = $_FILES[$name]['tmp_name'];
                }

                return $this->_process_image($handle);
            }
        }

        return false;
    }

    protected function _process_image($handle)
    {
        # get the file's extension
        $handle['ext'] = $this->_get_file_extension($handle);

        # see if user wants to rename the file
        if ($this->upload_rename) {
            $handle['name'] = $this->_rename_file($handle);
        } else {
            # if the file extension is .jpeg, rename to .jpg
            if ($handle['ext'] == 'jpeg') {
                $handle['name'] = rtrim($handle['filename'], 'jpeg').'jpg';
                $handle['ext'] = 'jpg';
            }
        }

        # make sure file is in the accepted types / accepted mimes array
        if (! $this->_check_upload_accepted_types($handle)) {
            return false;
        }

        # make sure file is not over the max_filesize
        if ($this->_check_filesize($handle)) {
            return false;
        }

        # add a trailing slash if $upload_dir doesn't have one
        if (! str_ends_with($this->upload_dir, '/')) {
            $this->upload_dir = $this->upload_dir.'/';
        }

        # define the upload path and new file name
        $upload_directory = $this->upload_dir.$handle['name'];

        # move the file to its final destination
        if (move_uploaded_file($handle['tmp_name'], $upload_directory)) {
            # see if we're resizing the image
            if ($this->upload_resize) {
                # loop through the resize array and process each key
                foreach ($this->upload_resize as $resize_key => $resize_values) {
                    $handle[$resize_key] = $this->_resize_image($handle, $resize_key, $resize_values);
                }
            }

            # return the array
            return $handle;
        } else {
            $this->errors['upload-error-move'] = 'There was an error uploading a file: it could not be moved to the final destination. Please check the directory permissions on '.$this->upload_dir.' and try again.';
            # we want to display the correct error messages, so we'll return true because the file was uploaded
            return true;
        }
    }

    protected function _resize_image($handle, $resize_key, $resize_values)
    {
        # don't upload if there are form errors
        if (! empty($this->errors)) {
            return false;
        }

        # $upload_resize hasn't been set, so don't resize!
        if (! $this->upload_resize) {
            return false;
        }

        $prepend = '';

        # put the resize values into an array
        $parts = explode(',', $resize_values);

        # get the thumb width
        if (isset($parts[0])) {
            $thumb_width = $parts[0];
            $handle[$resize_key]['width'] = $parts[0];
        } else {
            $this->errors[$handle['key']] = $handle['key'].': image not resized. You must specify a numeric width for this resized image.';
            return false;
        }

        # get the thumb height
        if (isset($parts[1])) {
            $thumb_height = $parts[1];
            $handle[$resize_key]['height'] = $parts[1];
        } else {
            $this->errors[$handle['key']] = $handle['key'].': image not resized. You must specify a numeric height for this resized image.';
            return false;
        }

        # check if we're prepending something onto the file name
        if (isset($parts[2])) {
            $prepend = $parts[2];
            $handle[$resize_key]['prepend'] = $parts[2];
        }

        # if the user hasn't specified a resize (tn) directory, put resized image in the same directory
        if (isset($parts[3])) {
            $thumb_dir = $parts[3];
            $handle[$resize_key]['dir'] = $parts[3];
        } else {
            $thumb_dir = $this->upload_dir;
        }

        # add a trailing slash if necessary
        if (substr($thumb_dir, 0, -1) != '/') {
            $thumb_dir = $thumb_dir.'/';
        }

        # get the resized image's quality, or default to 80% (JPG only)
        $quality = $parts[4] ?? 80;

        # load original image and get size and type
        if ($handle['type'] == 'image/jpeg') {
            $original_file = imagecreatefromjpeg($this->upload_dir.$handle['name']);
        }
        if ($handle['type'] == 'image/png') {
            $original_file = imagecreatefrompng($this->upload_dir.$handle['name']);
        }
        if ($handle['type'] == 'image/gif') {
            $original_file = imagecreatefromgif($this->upload_dir.$handle['name']);
        }

        $original_file_width = imagesx($original_file);
        $original_file_height = imagesy($original_file);

        # calculate resized image's size
        $new_width = $thumb_width;
        $new_height = floor($original_file_height * ($new_width / $original_file_width));

        # if upload width or height is larger than specified width or height, perform resize
        if ($original_file_width > $new_width || $original_file_height > $new_height) {
            # create a new temporary image
            $tmp_image = imagecreatetruecolor($new_width, $new_height);

            # copy and resize old image into new image
            imagecopyresized($tmp_image, $original_file, 0, 0, 0, 0, $new_width, $new_height, $original_file_width, $original_file_height);

            # save resized image
            if ($handle['type'] == 'image/jpeg') {
                imagejpeg($tmp_image, $thumb_dir.$prepend.$handle['name'], $quality);
            }
            if ($handle['type'] == 'image/png') {
                imagepng($tmp_image, $thumb_dir.$prepend.$handle['name']);
            }
            if ($handle['type'] == 'image/gif') {
                imagegif($tmp_image, $thumb_dir.$prepend.$handle['name']);
            }

            $handle[$resize_key]['name'] = $prepend.$handle['name'];

            # return the file name
            return $handle[$resize_key];
        }

        return false;
    }

    protected function _check_required($name)
    {
        # checks the field name to see if that field is required

        $this->required_fields = [];

        # all fields are required
        if ($this->required === '*') {
            return true;
        }

        # required fields are set. determine which individual fields are required
        if ($this->required) {
            # get any required fields
            $required_fields = explode($this->delimiter[0], rtrim($this->required, '[]'));

            # get any omitted fields inside round brackets ()
            if (preg_match_all('#\((([^()]+|(?R))*)\)#', rtrim($this->required, '[]'), $matches)) {
                $fields = implode(',', $matches[1]);
                $omitted_fields = explode($this->delimiter[0], $fields);
            }

            # if the omitted_fields array in not empty...
            if (! empty($omitted_fields)) {
                if (in_array($name, $omitted_fields)) {
                    # field name is not required
                    return false;
                } else {
                    # everything *but* this field is required
                    return true;
                }
            }

            # field name is required
            if (in_array($name, $required_fields)) {
                return true;
            }
        }

        return false;
    }

    protected function _nl($count = 1)
    {
        # adds as many new lines as we need for formatting our html

        if ($this->minify) {
            return '';
        }

        if ($count > 1) {
            return str_repeat("\r\n", $count + 1);
        }

        return "\r\n";
    }

    protected function _t($count = 1)
    {
        # adds as many tabs as we need for formatting our html

        if ($this->minify) {
            return '';
        }

        if ($count > 1) {
            return str_repeat("\t", $count + 1);
        }

        return "\t";
    }

    protected function is_not_empty($value)
    {
        # check if value is not empty - including zeros

        if (! empty($value) || (isset($value) && $value === "0") || (isset($value) && $value === 0)) {
            return true;
        }

        return false;
    }


    # MESSAGING
    public function messages($open_tag = '', $close_tag = '')
    {
        # this function prints client-side validation messages to the browser

        # print a message if the following properties are set
        if ($this->success_message || $this->warning_message || $this->info_message || $this->error_message) {
            if ($this->success_message) {
                $alert_control = 'alert-s';
                $heading = 'Success';
                $parts = explode('|', $this->success_message);
                if (count($parts) > 1) {
                    $heading = $parts[1];
                }
                $message = $parts[0];
            }

            if ($this->warning_message) {
                $alert_control = 'alert-w';
                $heading = 'Warning';
                $parts = explode('|', $this->warning_message);
                if (count($parts) > 1) {
                    $heading = $parts[1];
                }
                $message = $parts[0];
            }

            if ($this->info_message) {
                $alert_control = 'alert-i';
                $heading = 'Info';
                $parts = explode('|', $this->info_message);
                if (count($parts) > 1) {
                    $heading = $parts[1];
                }
                $message = $parts[0];
            }

            if ($this->error_message) {
                $alert_control = 'alert-e';
                $heading = 'Error';
                $parts = explode('|', $this->error_message);
                if (count($parts) > 1) {
                    $heading = $parts[1];
                }
                $message = $parts[0];
            }

            if ($this->_wrapper_is('bootstrap')) {
                $return = $this->_bootstrap_alert($alert_control, $message, $heading);
            } elseif ($this->_wrapper_is('bulma')) {
                $return = $this->_bulma_alert($alert_control, $message, $heading);
            } elseif ($this->_wrapper_is('tailwind')) {
                $return = $this->_tailwind_alert($alert_control, $message, $heading);
            } elseif ($this->_wrapper_is('uikit')) {
                $return = $this->_uikit_alert($alert_control, $message, $heading);
            } else {
                $return = $this->_formr_alert($alert_control, $message, $heading);
            }

            return $this->_echo($return);
        }

        $return = null;

        # flash messages
        if (isset($_SESSION['formr']['flash'])) {
            if (! empty($_SESSION['formr']['flash']['success'])) {
                $return .= $this->success_message($_SESSION['formr']['flash']['success']);
            }

            if (! empty($_SESSION['formr']['flash']['error'])) {
                $return .= $this->error_message($_SESSION['formr']['flash']['error']);
            }

            if (! empty($_SESSION['formr']['flash']['warning'])) {
                $return .= $this->warning_message($_SESSION['formr']['flash']['warning']);
            }

            if (! empty($_SESSION['formr']['flash']['info'])) {
                $return .= $this->info_message($_SESSION['formr']['flash']['info']);
            }

            $_SESSION['formr']['flash'] = null;
            unset($_SESSION['formr']['flash']);

            return $this->_echo($return);
        }

        # returns a user-defined message
        if (isset($this->message)) {
            return $this->_echo($this->message);
        }

        # prints form errors
        if ($this->inline_errors) {
            if (empty($open_tag) && empty($close_tag)) {
                $open_tag = null;
                $close_tag = null;
            }

            if ($this->errors()) {
                # check if the user has supplied their own error messages
                if (empty($this->error_messages)) {
                    $return .= $open_tag;

                    $i = 0;
                    foreach ($this->errors as $key => $value) {
                        if ($this->link_errors) {
                            # user wants to link to the form fields upon error
                            $return .= '<a href="#'.$key.'" class="'.$this->controls['link'].'">'.$value.'</a><br>';
                        } else {
                            # print the message
                            if (strpos($value, '|')) {
                                # remove the key if a custom message was added via add_to_errors()
                                $return .= ltrim(strstr($value, '|'), '|').'<br>';
                            } else {
                                $return .= $value.'<br>';
                            }
                        }
                        $i++;
                    }

                    $return .= $close_tag.PHP_EOL;
                } else {
                    $i = 0;
                    foreach ($this->error_messages as $key => $value) {
                        if ($this->in_errors($key)) {
                            # print the message
                            $return .= $value.PHP_EOL;
                        }
                        $i++;
                    }
                }

                # determine the heading message based on how many errors there are
                if ($i > 1) {
                    $heading = $this->error_heading_plural;
                } else {
                    $heading = $this->error_heading_singular;
                }

                # display the appropriate error dialogue
                if ($this->_wrapper_is('bootstrap')) {
                    return $this->_echo($this->_bootstrap_alert('alert-e', $return, $heading));
                } elseif ($this->_wrapper_is('bulma')) {
                    return $this->_echo($this->_bulma_alert('alert-e', $return, $heading));
                } elseif ($this->_wrapper_is('tailwind')) {
                    return $this->_echo($this->_tailwind_alert('alert-e', $return, $heading));
                } elseif ($this->_wrapper_is('uikit')) {
                    return $this->_echo($this->_uikit_alert('alert-e', $return, $heading));
                } else {
                    return $this->_echo($this->_formr_alert('alert-e', $return, $heading));
                }
            }
        }

        return null;
    }

    public function warning_message($message, $heading = null, $flash = false)
    {
        if ($flash) {
            return $_SESSION['formr']['flash']['warning'] = $message;
        }

        if ($this->_wrapper_is('bootstrap')) {
            $return = $this->_bootstrap_alert('alert-w', $message, $heading);
        } elseif ($this->_wrapper_is('bulma')) {
            $return = $this->_bulma_alert('alert-w', $message, $heading);
        } elseif ($this->_wrapper_is('tailwind')) {
            $return = $this->_tailwind_alert('alert-w', $message, $heading);
        } elseif ($this->_wrapper_is('uikit')) {
            $return = $this->_uikit_alert('alert-w', $message, $heading);
        } else {
            $return = $this->_formr_alert('alert-w', $message, $heading);
        }

        return $this->_echo($return);
    }

    public function success_message($message, $heading = null, $flash = false)
    {
        if ($flash) {
            return $_SESSION['formr']['flash']['success'] = $message;
        }

        if ($this->_wrapper_is('bootstrap')) {
            $return = $this->_bootstrap_alert('alert-s', $message, $heading);
        } elseif ($this->_wrapper_is('bulma')) {
            $return = $this->_bulma_alert('alert-s', $message, $heading);
        } elseif ($this->_wrapper_is('tailwind')) {
            $return = $this->_tailwind_alert('alert-s', $message, $heading);
        } elseif ($this->_wrapper_is('uikit')) {
            $return = $this->_uikit_alert('alert-s', $message, $heading);
        } else {
            $return = $this->_formr_alert('alert-s', $message, $heading);
        }

        return $this->_echo($return);
    }

    public function error_message($message, $heading = null, $flash = false)
    {
        if ($flash) {
            return $_SESSION['formr']['flash']['error'] = $message;
        }

        if ($this->_wrapper_is('bootstrap')) {
            $return = $this->_bootstrap_alert('alert-e', $message, $heading);
        } elseif ($this->_wrapper_is('bulma')) {
            $return = $this->_bulma_alert('alert-e', $message, $heading);
        } elseif ($this->_wrapper_is('tailwind')) {
            $return = $this->_tailwind_alert('alert-e', $message, $heading);
        } elseif ($this->_wrapper_is('uikit')) {
            $return = $this->_uikit_alert('alert-e', $message, $heading);
        } else {
            $return = $this->_formr_alert('alert-e', $message, $heading);
        }

        return $this->_echo($return);
    }

    public function info_message($message, $heading = null, $flash = false)
    {
        if ($flash) {
            return $_SESSION['formr']['flash']['info'] = $message;
        }

        if ($this->_wrapper_is('bootstrap')) {
            $return = $this->_bootstrap_alert('alert-i', $message, $heading);
        } elseif ($this->_wrapper_is('bulma')) {
            $return = $this->_bulma_alert('alert-i', $message, $heading);
        } elseif ($this->_wrapper_is('tailwind')) {
            $return = $this->_tailwind_alert('alert-i', $message, $heading);
        } elseif ($this->_wrapper_is('uikit')) {
            $return = $this->_uikit_alert('alert-i', $message, $heading);
        } else {
            $return = $this->_formr_alert('alert-i', $message, $heading);
        }

        return $this->_echo($return);
    }

    private function _success_message($message)
    {
        $return = '<div style="margin: 20px 20px 40px 20px; padding:15px; background: #53A451; color: white; border-radius: 5px; text-align: center">';
        $return .= $message;
        $return .= '</div>';

        return $this->_echo($return);
    }

    private function _error_message($message)
    {
        if ($this->wrapper) {
            $this->error_message($message);
        } else {
            $return = "\r\n<style>";
            $return .= '.formr-error {margin: 20px; padding:15px; background: #CB444A; color: white; border-radius: 5px;}';
            $return .= 'code {color: yellow;}';
            $return .= 'html {font-family: sans-serif}';
            $return .= "</style>\r\n";
            $return .= '<div class="formr-error">';
            $return .= $message;
            $return .= '</div>';

            return $this->_echo($return);
        }

        return null;
    }

    private function _get_alert_heading($type, $heading = null)
    {
        if (! $heading) {
            if ($type == 'alert-w') {
                $heading = 'Warning';
            } elseif ($type == 'alert-s') {
                $heading = 'Success';
            } elseif ($type == 'alert-e') {
                $heading = 'Error';
            } else {
                $heading = 'Info';
            }
        }

        return $heading;
    }

    private function _bulma_alert($type, $message, $heading = '')
    {
        $return = "<div class=\"message {$this->controls[$type]}\">\r\n";
        $return .= "  <div class=\"message-header\">\r\n";
        $return .= "    <p>{$this->_get_alert_heading($type,$heading)}</p>\r\n";
        $return .= "  </div>\r\n";
        $return .= "  <div class=\"message-body\">\r\n";
        $return .= "    {$message}\r\n";
        $return .= "  </div>\r\n";
        $return .= "</div>\r\n";

        return $return;
    }

    private function _tailwind_alert($type, $message, $heading = '')
    {
        $return = "<div class=\"{$this->controls[$type]}\">\r\n";
        $return .= "  <h4 class=\"{$this->controls['message-header']}\">{$this->_get_alert_heading($type,$heading)}</h4>\r\n";
        $return .= "  <div class=\"{$this->controls['message-body']}\">\r\n";
        $return .= "    {$message}\r\n";
        $return .= "  </div>\r\n";
        $return .= "</div>\r\n";

        return $return;
    }

    private function _uikit_alert($type, $message, $heading = '')
    {
        $return = "<div class=\"{$this->controls[$type]}\">\r\n";
        $return .= "  <h4 class=\"{$this->controls['message-header']}\">{$this->_get_alert_heading($type,$heading)}</h4>\r\n";
        $return .= "  <div class=\"{$this->controls['message-body']}\">\r\n";
        $return .= "    {$message}\r\n";
        $return .= "  </div>\r\n";
        $return .= "</div>\r\n";

        return $return;
    }

    private function _bootstrap_alert($type, $message, $heading = null)
    {
        $return = "<div class=\"{$this->controls[$type]} alert-dismissible fade show\" role=\"alert\">\r\n";
        if ($this->wrapper == 'bootstrap5' || $this->wrapper == 'bootstrap') {
            $return .= "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>";
        } else {
            $return .= "<button type=\"button\" class=\"close\" data-dismiss=\"alert\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button>\r\n";
        }
        if ($heading) {
            $return .= "<h4 class=\"alert-heading\">{$this->_get_alert_heading($type,$heading)}</h4>\r\n";
        }
        $return .= "{$message}\r\n";
        $return .= "</div>\r\n";

        return $return;
    }

    private function _my_wrapper_alert($type, $message, $heading = null)
    {
        $return = "<div class=\"alert {$this->controls[$type]}\">\r\n";
        $return .= "  <div class=\"alert-header\">\r\n";
        $return .= "    <p>{$this->_get_alert_heading($type,$heading)}</p>\r\n";
        $return .= "  </div>\r\n";
        $return .= "  <div class=\"alert-body\">\r\n";
        $return .= "    {$message}\r\n";
        $return .= "  </div>\r\n";
        $return .= "</div>\r\n";

        return $return;
    }

    private function _formr_alert($type, $message, $heading = null)
    {
        if ($type == 'alert-s') {
            return $this->_success_message($message);
        }

        if ($this->wrapper) {
            return $this->_my_wrapper_alert($type, $message, $heading);
        }

        return $this->_error_message($message);
    }


    # PROCESS POST AND VALIDATE
    public function post($name, $label = '', $rules = '')
    {
        return $this->_post($name, $label, $rules);
    }

    public function get($name, $label = '', $rules = '')
    {
        return $this->_post($name, $label, $rules);
    }

    public function fastpost($name)
    {
        # for the truly lazy! ;)
        # returns an associative array of all posted keys/values, minus the submit button (if it's named 'submit')

        $keys = [];

        if ($name == 'POST') {
            foreach ($_POST as $key => $value) {
                if ($key != $this->submit && $key != 'submit' && $key != 'button') {
                    # automatically validate based on field name, ie; email = valid_email
                    # if a field name matches, and rules are assigned in the fastpost_rules() method, they'll be applied
                    $keys[$key] = $this->post($key, $key, $this->_fp_rules($key));
                }
            }
        } else {
            # this part works with the Forms class to allow for quick validation by using pre-built form/validation sets

            if (is_array($name)) {
                $data = $name;
            } else {
                # create the array by passing the function name and the validate flag to the Forms class
                if (! $this->use_default_wrapper && class_exists('\MyForms')) {
                    $data = \MyForms::$name('validate');
                } else {
                    $data = \Forms::$name('validate');
                }
            }

            # run it through the validate function
            foreach ($data as $key => $value) {
                # $value[0] = custom strings
                # $value[1] = validation rules

                if (isset($value[1])) {
                    # a validation rule was set
                    $keys[$key] = $this->post($key, $value[0], $value[1]);
                } else {
                    # a validation rule was not set
                    $keys[$key] = $this->post($key, $value[0]);
                }
            }
        }

        return $keys;
    }

    protected function _post($name, $label = '', $rules = [])
    {
        # this method processes the $_POST/$_GET values and performs validation (if required)

        $this->_check_for_honeypot();

        # set the variable in which we'll store our $_POST/$_GET data
        $post = null;

        # check for uploaded files first
        if ($this->uploads && ! empty($_FILES[$name]['tmp_name'])) {
            if (! $this->upload_dir) {
                $this->_error_message('Please specify an upload directory with the <code>$form->upload_dir</code> property.');
                return false;
            }
            if (! is_dir($this->upload_dir)) {
                $this->_error_message("The specified upload directory <code>{$this->upload_dir}</code> does not exist.");
                return false;
            }
            if (! $this->upload_accepted_types && ! $this->upload_accepted_mimes) {
                $this->_error_message('Please specify the accepted file types with $upload_accepted_mimes or $upload_accepted_types.');
                return false;
            }
            if ($return = $this->_upload_files($name)) {
                return $return;
            }
        }

        # prevents error classes from contaminating all the fields in a group
        if (stristr($name, '[]')) {
            $name = str_replace('[]', '', $name);
        }

        # process the POST data

        # see if we're dealing with $_POST or $_GET
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST[$name]) && $_POST[$name] != '') {
                $post = $_POST[$name];
            }
        } else {
            if (isset($_GET[$name]) && $_GET[$name] != '') {
                $post = $_GET[$name];
            }
        }

        if (is_array($post)) {
            if ($this->session) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    foreach ($_POST[$name] as $key => $value) {
                        # make sure the session array exists and that it's actually an array
                        if (! isset($_SESSION[$this->session][$name]) || ! is_array($_SESSION[$this->session][$name])) {
                            $_SESSION[$this->session][$name] = [];
                        }

                        # replace the session array values from POST
                        $_SESSION[$this->session][$name] = is_array($_POST[$name]) ? $_POST[$name] : [];
                    }
                } else {
                    foreach ($_GET[$name] as $key => $value) {
                        # make sure the session array exists and that it's actually an array
                        if (! isset($_SESSION[$this->session][$name]) || ! is_array($_SESSION[$this->session][$name])) {
                            $_SESSION[$this->session][$name] = [];
                        }

                        # replace the session array values from GET
                        $_SESSION[$this->session][$name] = is_array($_GET[$name]) ? $_GET[$name] : [];
                    }
                }
            }
        } else {
            if ($this->session && ! empty($post)) {
                $_SESSION[$this->session][$name] = $post;
            }
        }

        # check to see if we have a human readable string and a custom error message string
        if (! empty($label) && stristr($label, $this->delimiter[1])) {
            # we have a custom error message string
            $parts = explode($this->delimiter[1], $label);

            # we'll put the human readable label into the $label property
            $label = $parts[0];

            # we'll put the custom error message string into the $string property
            $string = $parts[1];

            $data['string'] = $string;
        }

        # check if this field is required
        # we can't check if isset($_POST[$name]) because checkboxes and radios
        # don't post if they're not ticked so we have to check everything
        if ($this->_check_required($name) && $post == null) {
            if (! empty($label)) {
                if (! isset($string)) {
                    $this->errors[$name] = '<strong>'.$label.'</strong> is required';
                } else {
                    $this->errors[$name] = $string;
                }
            } else {
                $this->errors[$name] = 'The <strong>'.$name.'</strong> field is required';
            }
        }

        # validation rules are a string; let's put them into an array
        if (! is_array($rules)) {
            $rules = explode($this->delimiter[1], $rules ?? '');
        }

        # push 'allow_html' to the back so it processes last
        if (($key = array_search('allow_html', $rules)) !== false) {
            unset($rules[$key]);
            $rules[] = 'allow_html';
        }

        # get busy validating!
        $return = null;

        # get the $data array ready...
        $data['post'] = $post;
        $data['label'] = $label;
        $data['name'] = $name;

        # process validation rules
        # if we're posting an array, don't run it through the validation rules because
        # each individual value could break the validation for the entire group
        if (! is_array($data['post'])) {
            foreach ($rules as $rule) {
                # process boolean validation rules
                $return = $this->_validate_bool_rules($rule, $data);
            }

            foreach ($rules as $rule) {
                # allow HTML?
                $allow_html = ($rule == 'allow_html') ? true : null;

                # process string validation rules
                $return = $this->_validate_string_rules($rule, $return);
            }

            # run it through the cleaning method as a final step
            return $this->_clean_value($return, $allow_html);
        } else {
            # return the array without validation
            return $post;
        }
    }

    protected function _validation_message($data, $message)
    {
        # determines which message to display during validation

        if ($this->_suppress_formr_validation_errors($data)) {
            # always display the user-defined validation message
            $this->errors[$data['name']] = $data['string'];
        } else {
            if (! empty($data['string'])) {
                # display the user-defined validation message
                $this->errors[$data['name']] = $data['string'];
            } else {
                if (! empty($data['label'])) {
                    # show the user-defined field name
                    $this->errors[$data['name']] = $data['label'].' '.$message;
                } else {
                    # fallback to the field name
                    $this->errors[$data['name']] = $data['name'].' '.$message;
                }
            }
        }
    }

    protected function _get_matches($rule)
    {
        preg_match_all("/\[(.*?)]/", $rule, $matches);

        if (! isset($matches[1][0])) {
            $this->_error_message('Oops! The <strong>'.$rule.'</strong> validation rule is missing a required parameter.<br>Read the <a href="https://formr.github.io/validation/#validation-rules" style="color:white; text-decoration:underline" target="_blank">Validation Rule docs</a> for more information on using this rule.');
            return false;
        } else {
            return $matches[1][0];
        }
    }

    protected function _validate_bool_rules($rule, $data)
    {
        # the following rules evaluate the posted string

        if ($rule != 'required' && empty($data['post'])) {
            return null;
        }

        # this rule must match a user-defined regex
        if (mb_substr($rule, 0, 5) == 'regex') {
            $rule = ltrim($rule, 'regex[');
            $rule = rtrim($rule, ']');

            if (preg_match($rule, $data['post'])) {
                $this->_validation_message($data, 'does not match the required parameters');
            }
        } # this rule must *not* match a user-defined regex
        elseif (mb_substr($rule, 0, 9) == 'not_regex') {
            $rule = ltrim($rule, 'not_regex[');
            $rule = rtrim($rule, ']');

            if (! preg_match($rule, $data['post'])) {
                $this->_validation_message($data, 'can not be an exact match');
            }
        } # match one field's contents to another
        elseif (mb_substr($rule, 0, 7) == 'matches') {
            preg_match_all("/\[(.*?)]/", $rule, $matches);
            $match_field = $matches[1][0];

            if ($data['post'] != $_POST[$match_field]) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    $this->_validation_message($data, ' does not match '.$match_field);
                }
            }
        } # min length
        elseif (mb_substr($rule, 0, 10) == 'min_length' || mb_substr($rule, 0, 3) == 'min') {
            if (empty($data['post']) && ! $this->_check_required($data['name'])) {
                return null;
            }

            if (! $match = $this->_get_matches($rule)) {
                return $data['post'];
            }

            if (strlen($data['post']) < $match) {
                $this->_validation_message($data, 'must be at least '.$match.' characters');
            }
        } # max length
        elseif (mb_substr($rule, 0, 10) == 'max_length' || mb_substr($rule, 0, 3) == 'max' || mb_substr($rule, 0, 2) == 'ml') {
            if (! $match = $this->_get_matches($rule)) {
                return $data['post'];
            }

            if ($this->is_not_empty($data['post']) && (strlen($data['post']) > $match)) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    if ($match == 1 && strlen($data['post']) > 1) {
                        $this->_validation_message($data, 'must be 1 character');
                    } else {
                        $this->_validation_message($data, 'can not be more than '.$match.' characters');
                    }
                }
            }
        } # exact length
        elseif (mb_substr($rule, 0, 12) == 'exact_length' || mb_substr($rule, 0, 5) == 'exact' || mb_substr($rule, 0, 2) == 'el') {
            if (! $match = $this->_get_matches($rule)) {
                return $data['post'];
            }

            if (strlen($data['post']) != $match) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    $this->_validation_message($data, 'must be exactly '.$match.' characters');
                }
            }
        } # less than or equal to (number)
        elseif (mb_substr($rule, 0, 18) == 'less_than_or_equal' || mb_substr($rule, 0, 3) == 'lte') {
            if (! is_numeric($data['post'])) {
                if (! empty($data['label'])) {
                    $this->errors[$data['name']] = $data['label'].' must be a number';
                } else {
                    $this->errors[$data['name']] = $data['name'].' must be a number';
                }

                return $data['post'];
            }

            if (! $match = $this->_get_matches($rule)) {
                return $data['post'];
            }

            if ($data['post'] > $match) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    $this->_validation_message($data, 'must be less than, or equal to '.$match);
                }
            }
        } # less than (number)
        elseif (mb_substr($rule, 0, 9) == 'less_than' || mb_substr($rule, 0, 2) == 'lt') {
            if (! is_numeric($data['post'])) {
                if (! empty($data['label'])) {
                    $this->errors[$data['name']] = $data['label'].' must be a number';
                } else {
                    $this->errors[$data['name']] = $data['name'].' must be a number';
                }

                return $data['post'];
            }

            if (! $match = $this->_get_matches($rule)) {
                return $data['post'];
            }

            if ($data['post'] >= $match) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    $this->_validation_message($data, 'must be less than '.$match);
                }
            }
        } # greater than or equal to (number)
        elseif (mb_substr($rule, 0, 21) == 'greater_than_or_equal' || mb_substr($rule, 0, 3) == 'gte') {
            if (! is_numeric($data['post'])) {
                if (! empty($data['label'])) {
                    $this->errors[$data['name']] = $data['label'].' must be a number';
                } else {
                    $this->errors[$data['name']] = $data['name'].' must be a number';
                }

                return $data['post'];
            }

            $match = $this->_get_matches($rule);

            if ($data['post'] < $match) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    $this->_validation_message($data, 'must be greater than, or equal to '.$match);
                }
            }
        } # greater than (number)
        elseif (mb_substr($rule, 0, 12) == 'greater_than' || mb_substr($rule, 0, 2) == 'gt') {
            if (! is_numeric($data['post'])) {
                if (! empty($data['label'])) {
                    $this->errors[$data['name']] = $data['label'].' must be a number';
                } else {
                    $this->errors[$data['name']] = $data['name'].' must be a number';
                }

                return $data['post'];
            }

            if (! $match = $this->_get_matches($rule)) {
                return $data['post'];
            }

            if ($data['post'] <= $match) {
                if ($this->_suppress_formr_validation_errors($data)) {
                    $this->errors[$data['name']] = $data['string'];
                } else {
                    $this->_validation_message($data, 'must be greater than '.$match);
                }
            }
        } # alpha
        elseif ($rule == 'alpha' && ! ctype_alpha(str_replace(' ', '', $data['post']))) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'may only contain letters');
            }
        } # before (the current date)
        elseif ($rule == 'before' && strtotime($data['post']) > strtotime('now')) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be before '.date($this->format_rule_dates, strtotime('now')));
            }
        } # after (the current date)
        elseif ($rule == 'after' && strtotime($data['post']) < strtotime('now')) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be after '.date($this->format_rule_dates, strtotime('now')));
            }
        } # alphanumeric
        elseif ($rule == 'alpha_numeric' && ! ctype_alnum(str_replace(' ', '', $data['post'])) || $rule == 'an' && ! ctype_alnum(str_replace(' ', '', $data['post']))) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'may only contain letters and numbers');
            }
        } # alpha_dash
        elseif ($rule == 'alpha_dash' && preg_match('/[^A-Za-z0-9_-]/', $data['post']) || $rule == 'ad' && preg_match('/[^A-Za-z0-9_-]/', $data['post'])) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'may only contain letters, numbers, hyphens and underscores');
            }
        } # numeric
        elseif ($rule == 'numeric' && ! is_numeric($data['post'])) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be a number or a numeric string');
            }
        } # integer
        elseif (($rule == 'int' || $rule == 'integer') && ! filter_var($data['post'], FILTER_VALIDATE_INT)) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be a number');
            }
        } # valid email
        elseif ($rule == 'valid_email' && ! filter_var($data['post'], FILTER_VALIDATE_EMAIL) || $rule == 'email' && ! filter_var($data['post'], FILTER_VALIDATE_EMAIL)) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be an email address');
            }
        } # valid IP
        elseif ($rule == 'valid_ip' && ! filter_var($data['post'], FILTER_VALIDATE_IP) || $rule == 'ip' && ! filter_var($data['post'], FILTER_VALIDATE_IP)) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be a properly formatted IP address');
            }
        } # valid URL
        elseif ($rule == 'valid_url' && ! filter_var($data['post'], FILTER_VALIDATE_URL) || $rule == 'url' && ! filter_var($data['post'], FILTER_VALIDATE_URL)) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'must be a properly formatted URL');
            }
        } # required
        elseif ($rule == 'required' && ! $this->is_not_empty($data['post'])) {
            if ($this->_suppress_formr_validation_errors($data)) {
                $this->errors[$data['name']] = $data['string'];
            } else {
                $this->_validation_message($data, 'is required');
            }
        }

        return $data['post'];
    }

    protected function _validate_string_rules($rule, $string)
    {
        # the following rules manipulate the posted string

        # sanitize string
        if ($rule == 'sanitize_string') {
            return strip_tags($string);
        } # sanitize URL
        elseif ($rule == 'sanitize_url') {
            return filter_var($string, FILTER_SANITIZE_URL);
        } # sanitize email
        elseif ($rule == 'sanitize_email') {
            return filter_var($string, FILTER_SANITIZE_EMAIL);
        } # sanitize integer
        elseif ($rule == 'sanitize_int') {
            return filter_var($string, FILTER_SANITIZE_NUMBER_INT);
        } # md5
        elseif ($rule == 'md5') {
            return md5($string.$this->salt);
        } # sha1
        elseif ($rule == 'sha1') {
            return sha1($string.$this->salt);
        } # php's password_hash() function
        elseif ($rule == 'hash' && ! empty($string)) {
            return password_hash($string, PASSWORD_DEFAULT);
        } # strip everything but numbers
        elseif ($rule == 'strip_numeric') {
            return preg_replace("/[^0-9]/", '', $string);
        } # create twitter-style username
        elseif ($rule == 'slug') {
            return $this->slug($string);
        } else {
            return $string;
        }
    }

    protected function _fp_rules($key)
    {
        # used during fastpost()
        # if a field name matches, why not do some automatic validation?

        $rules = $this->fastpost_rules();

        if (array_key_exists($key, $rules)) {
            return $rules[$key];
        }

        return false;
    }

    protected function fastpost_rules()
    {
        # validation rules for the fastpost() method

        # basically we're using common field names, and if a posted field name
        # matches one of these field names (keys), the validation rule will be applied

        # 'field name' => 'validation rule'

        return [
            'email' => 'valid_email',
            'zip' => 'int|min_length[5]|max_length[10]',
            'zip_code' => 'int|min_length[5]|max_length[10]',
            'postal' => 'alphanumeric|min_length[6]|max_length[7]',
            'postal_code' => 'alphanumeric|min_length[6]|max_length[7]',
            'age' => 'int',
            'weight' => 'int',
            'url' => 'valid_url',
            'website' => 'valid_url',
            'ip_address' => 'valid_ip'
        ];
    }

    public function validate($string)
    {
        # even easier and more automatic way to process and validate your form fields

        # break apart the comma delimited string of form labels
        $parts = explode(',', $string);

        $array = [];

        foreach ($parts as $label) {
            $key = strtolower(str_replace(' ', '_', trim($label)));

            $rules = null;

            # we are adding validation rules to this field
            if (preg_match('!\(([^)]+)\)!', $label, $match)) {
                # get our field's validation rule(s)
                $rules = $match[1];

                # get the text before the double pipe for our new label
                $explode = explode('(', $label, 2);

                # set our new label text
                $label = $explode[0];

                # set our field's name
                $key = strtolower(str_replace(' ', '_', trim($label)));
            }

            if (str_contains($key, 'email')) {
                # this is an email address, so let's add the valid_email rule as well
                $array[$key] = $this->post($key, ucwords($key), 'valid_email|'.$rules);
            } else {
                $array[$key] = $this->post($key, $label, $rules);
            }
        }

        return $array;
    }


    # FORM
    protected function _form($data)
    {
        # define the form action
        if (! empty($data['action'])) {
            # use action passed directly to function
            $action = $data['action'];
        } else {
            $action = $this->action ?? $_SERVER['SCRIPT_NAME'];
        }

        # the form's method
        if (empty($data['method'])) {
            $data['method'] = 'post';
        }

        # open the form tag
        $return = PHP_EOL.'<form action="'.$action.'"';

        # add the name
        if (! empty($data['name'])) {
            $return .= ' name="'.$data['name'].'"';
        } elseif (isset($this->name)) {
            $return .= ' name="'.$this->name.'"';
        }

        # add an ID
        if (isset($this->id)) {
            $return .= ' id="'.$this->id.'"';
        } else {
            if (empty($data['id']) && ! empty($data['name'])) {
                $return .= ' id="'.$data['name'].'"';
            } elseif (! empty($data['id'])) {
                $return .= ' id="'.$data['id'].'"';
            } else {
                $return .= ' id="formr"';
            }
        }

        # add the method and character set
        $return .= ' method="'.$data['method'].'" accept-charset="'.$this->charset.'"';

        # print any additional user-defined attributes
        if (! empty($data['string'])) {
            $return .= ' '.$data['string'];
        }

        # add multipart if required
        if ($data['form_type'] == 'multipart') {
            $return .= ' enctype="multipart/form-data"';
        }

        # close the form tag
        $return .= '>'.PHP_EOL;

        # add a hidden input with the form's ID so we can check if it's been submitted
        if (isset($this->id)) {
            $return .= '<input type="hidden" name="FormrID" value="'.$this->id.'">';
        } elseif (! empty($data['id'])) {
            $return .= '<input type="hidden" name="FormrID" value="'.$data['id'].'">';
        }

        if ($this->recaptcha_secret_key && $this->recaptcha_site_key) {
            $return .= '<input type="hidden" name="formrToken" id="formrToken" value="formrToken">';
        }

        # print hidden input fields if present
        if (! empty($data['hidden'])) {
            foreach ($data['hidden'] as $key => $value) {
                $return .= '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$value.'">'.PHP_EOL;
            }
            $return .= PHP_EOL;
        }

        # add the honeypot
        if ($this->honeypot) {
            $return .= '<input type="text" name="'.$this->honeypot.'" value="" style="display:none">';
        }

        if ($this->wrapper == 'ul' || $this->wrapper == 'ol' || $this->wrapper == 'dl') {
            $return .= '<'.$this->wrapper.'>';
        }

        return $return;
    }

    public function form_open($name = '', $id = '', $action = '', $method = '', $string = '', $hidden = '')
    {
        if (! $method) {
            $method = $this->method == 'get' ? 'get' : 'post';
        }

        $data = [
            'form_type' => 'open',
            'action' => $action,
            'method' => $method,
            'name' => $name,
            'id' => $id,
            'string' => $string,
            'hidden' => $hidden
        ];

        return $this->_echo($this->_form($data));
    }

    public function form_open_multipart($name = '', $id = '', $action = '', $method = '', $string = '', $hidden = '')
    {
        $data = [
            'form_type' => 'multipart',
            'action' => $action,
            'method' => $method,
            'name' => $name,
            'id' => $id,
            'string' => $string,
            'hidden' => $hidden
        ];

        return $this->_echo($this->_form($data));
    }

    public function form_close()
    {
        $return = null;

        # put checkbox array values into hidden elements
        # we'll match them inside the submit() method after the form has been submitted
        if ($this->session && $this->session_values && ! empty($this->checkbox_values)) {
            foreach ($this->checkbox_values as $key => $value) {
                $return .= PHP_EOL.$this->input_hidden($key.'_values', implode(',', $value));
            }
        }

        if ($this->wrapper == 'ul' || $this->wrapper == 'ol' || $this->wrapper == 'dl') {
            $return .= '</'.$this->wrapper.'>';
        }

        $return .= PHP_EOL.'</form>'.PHP_EOL;

        return $this->_echo($return);
    }

    public function open($name = '', $id = '', $action = '', $method = '', $string = '', $hidden = '')
    {
        # alias of form_open()

        return $this->form_open($name, $id, $action, $method, $string, $hidden);
    }

    public function open_multipart($name = '', $id = '', $action = '', $method = '', $string = '', $hidden = '')
    {
        # alias of form_open_multipart()

        return $this->form_open_multipart($name, $id, $action, $method, $string, $hidden);
    }

    public function close()
    {
        # alias of form_close()

        return $this->form_close();
    }


    # BUTTONS
    protected function _button($data)
    {
        # build the button tag
        $return = '<button type="'.$data['type'].'"';

        # insert the button's name
        if (empty($data['name'])) {
            $data['name'] = 'button';
        }

        $return .= ' name="'.$data['name'].'"';

        if (empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        $return .= ' id="'.$data['id'].'"';

        if (empty($data['value'])) {
            $data['value'] = 'Submit';
        }

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # insert the value and close the <button>
        $return .= '>'.$data['value'].'</button>';

        $element = null;

        if (empty($data['fastform'])) {
            return $this->_wrapper($return, $data);
        }

        # we're using fastform(), which will run the element through wrapper()
        return $return;
    }

    public function input_submit($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (! is_array($data)) {
            if (! $data) {
                $data = 'submit';
            }

            if (! $value) {
                $value = $this->submit ? $this->submit : 'Submit';
            }

            $data = [
                'type' => 'submit',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            ];
        } else {
            $data['type'] = 'submit';
        }

        if ($this->_wrapper_is('framework') && ! $string) {
            $data['string'] = 'class="'.$this->controls['button-primary'].'"';
        }

        return $this->_echo($this->_create_input($data));
    }

    public function input_reset($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'reset',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            ];
        } else {
            $data['type'] = 'reset';
        }

        return $this->_echo($this->_create_input($data));
    }

    public function input_button($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'button',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            ];
        } else {
            $data['type'] = 'button';
        }

        return $this->_echo($this->_button($data));
    }

    public function input_button_submit($data = '', $label = '', $value = '', $id = '', $string = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'submit',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string
            ];
        } else {
            $data['type'] = 'submit';
        }

        if ($this->_wrapper_is('framework') && ! $string) {
            $data['string'] = 'class="'.$this->controls['button-primary'].'"';
        }

        return $this->_echo($this->_button($data));
    }

    public function submit_button($value = 'Submit')
    {
        $data = [
            'type' => 'submit',
            'name' => 'submit',
            'label' => null,
            'value' => $value,
            'id' => 'submit',
            'string' => 'class="button"'
        ];

        if ($this->_wrapper_is('framework')) {
            $data['string'] = 'class="'.$this->controls['button-primary'].'"';
        }

        return $this->_echo($this->_button($data));
    }

    public function reset_button($value = 'Reset')
    {
        $data = [
            'type' => 'reset',
            'name' => 'reset',
            'label' => null,
            'value' => $value,
            'id' => 'reset',
            'string' => 'class="button"'
        ];

        if ($this->_wrapper_is('framework')) {
            $data['string'] = 'class="'.$this->controls['button-danger'].'"';
        }

        return $this->_echo($this->_button($data));
    }


    # INPUTS
    protected function _create_input($data)
    {
        # show an error if the field name hasn't been supplied
        if (! $this->is_not_empty($data['name'])) {
            $this->_error_message('You must provide a name for the <strong>'.$data['type'].'</strong> element.');
            return false;
        }

        # open the element
        $return = '<input';

        # populate the field's value (on page load) with the session value
        if (! in_array($data['type'], $this->_input_types('checkbox'))) {
            if ($data['value'] == '' && $this->session_values && $this->session && ! empty($_SESSION[$this->session][$data['name']])) {
                $data['value'] = $_SESSION[$this->session][$data['name']];
            }
        }

        # if there are form errors, let's insert the posted value into
        # the array so the user doesn't have to enter the value again.
        # also, don't store passwords; always make the user re-type the password.

        if (! in_array($data['type'], $this->_input_types('checkbox'))) {
            # an ID wasn't specified, let's create one using the name
            $data['id'] = $this->make_id($data);

            # assign the value
            if (! empty($_POST[$data['name']]) && $data['type'] != 'password') {
                if ($this->session_values && $this->session) {
                    if ($data['type'] != 'submit' && $data['type'] != 'button') {
                        if (empty($data['value'])) {
                            $this->_error_message('Please define $'.$data['name'].' = $form->post(\''.$data['name'].'\')');
                        } else {
                            $data['value'] = $_SESSION[$this->session][$data['name']];
                        }
                    }
                } else {
                    $data['value'] = $this->_clean_value($_POST[$data['name']], 'allow_html');
                }
            }

            # if we're dealing with an input array: such as <input type="text" name="name[key]">
            # we can print the array's value in a text field, but only with an array key
            # and the array key must match the field's ID - hey, we need *something* to match it with! :P
            if (! empty($_POST) && $data['type'] != 'file' && $data['type'] != 'submit' && $data['type'] != 'reset') {
                # tells us we're dealing with an array because of the trailing bracket ]
                if (str_ends_with(rtrim($data['name']), ']')) {
                    # get the array key from between the brackets []
                    preg_match_all('^\[(.*?)]^', $data['name'], $matches);

                    foreach ($matches[1] as $key) {
                        # strip out the brackets and array key to reveal the field name
                        $string = '['.$key.']';
                        $name = str_replace($string, '', $data['name']);

                        # if the POST array key matches the field's id, print the value
                        if ($key == $data['id']) {
                            $data['value'] = $_POST[$name][$key];
                        }
                    }
                }
            }
        } else {
            # checkboxes and radios..

            if ($this->session && $this->session_values) {
                # let's see if our checkboxes are an array
                if (str_contains($data['name'], '[]')) {
                    # put the values into an array, then we'll put them into a hidden element inside the form_close() method
                    # we do this so we can match the form values against what was actually posted (or not posted)
                    if (! in_array($data['value'], $this->checkbox_values)) {
                        $this->checkbox_values[trim($data['name'], '[]')][] = $data['value'];
                    }
                }
            }

            # an ID wasn't specified, let's create one using the value
            if (empty($data['id'])) {
                $data['id'] = $data['value'];
            }

            # print an error message alerting the user this field needs a value
            if (! $this->is_not_empty($data['value'])) {
                $this->_error_message('Please enter a value for the '.$data['type'].': <strong>'.$data['name'].'</strong>');
            }

            # check the element on initial form load
            if (! $this->submitted()) {
                if (! isset($_POST[$this->_strip_brackets($data['name'])])) {
                    # tick the checkbox/radio if value is in the session
                    if ($this->session_values && $this->session && ! empty($_SESSION[$this->session])) {
                        if (str_contains($data['name'], '[]')) {
                            # we are in a checkbox array
                            if (isset($_SESSION[$this->session][$this->_strip_brackets($data['name'])])) {
                                foreach ($_SESSION[$this->session][$this->_strip_brackets($data['name'])] as $key => $value) {
                                    if ($data['value'] == $value) {
                                        $return .= ' checked';
                                    }
                                }
                            }
                        } else {
                            foreach ($_SESSION[$this->session] as $key => $value) {
                                if ($data['name'] == $key && $data['value'] == $value) {
                                    $return .= ' checked';
                                }
                            }
                        }
                    } else {
                        # tick the checkbox/radio if value is selected
                        if (! empty($data['selected'])) {
                            if ($data['selected'] == $data['value'] || ($data['selected'] == 'checked' || $data['selected'] == 'selected')) {
                                $return .= ' checked';
                            }
                        }
                    }
                }

                # check the session for checkbox groups/arrays
                if (! empty($_SESSION[$this->session])) {
                    foreach ($_SESSION[$this->session] as $value) {
                        if ($data['value'] == $value) {
                            $return .= ' checked';
                        }
                    }
                }
            } else {
                # check the element after the form has been posted
                if (isset($_POST[$this->_strip_brackets($data['name'])]) && $_POST[$this->_strip_brackets($data['name'])] == $data['value']) {
                    $return .= ' checked';
                }

                # checkbox group / checkbox array
                if (! empty($_POST[$this->_strip_brackets($data['name'])]) && is_array($_POST[$this->_strip_brackets($data['name'])])) {
                    foreach ($_POST[$this->_strip_brackets($data['name'])] as $pvalue) {
                        if ($pvalue == $data['value']) {
                            $return .= ' checked';
                        }
                    }
                }
            }
        }

        # loop through the array and print each attribute
        foreach ($data as $key => $value) {
            if (! in_array($key, $this->no_keys)) {
                if ($key != 'checkbox-inline') {
                    if ($value != '') {
                        $return .= ' '.$key.'="'.$value.'"';
                    }
                }
            }
        }

        # last resort: an ID wasn't provided; use the name field as the ID
        # do not auto-generate an ID if the field is an array
        if (! $this->is_not_empty($data['id'])) {
            if (! str_ends_with(rtrim($data['name']), ']')) {
                $return .= ' id="'.trim($data['name'], '[]').'"';
            }
        }

        if (! empty($data['multiple'])) {
            $return .= ' multiple';
        }

        # add user-entered string and additional attributes
        // $return .= $this->_attributes($data);

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # if required
        if ($this->_check_required($data['name']) && $data['type'] != 'submit' && $data['type'] != 'reset') {
            $return .= ' required';
        }

        # insert the closing bracket
        $return .= ' />';

        # if using inline validation
        $return .= $this->_inline($data['name']);

        $return = str_replace('  ', ' ', $return);

        if (empty($data['fastform'])) {
            # the element is completely built, now all we need to do is wrap it
            return $this->_wrapper($return, $data);
        }

        # we're using fastform(), which will run the element through wrapper()
        return $return;
    }

    public function input_text($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'text',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'text';
        }

        return $this->_create_input($data);
    }

    public function input_hidden($data, $value = '')
    {
        $return = '';

        if (is_array($data)) {
            # build the elements
            foreach ($data as $key => $value) {
                $return .= '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$value.'">'.PHP_EOL;
            }
        } else {
            # build the element
            $return = '<input type="hidden" name="'.$data.'" id="'.$data.'" value="'.$value.'">'.PHP_EOL;
        }

        return $this->_echo($return);
    }

    public function input_upload($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'file',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'file';
        }

        return $this->_create_input($data);
    }

    public function input_file($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'file',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'file';
        }

        return $this->_create_input($data);
    }

    public function input_upload_multiple($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'file',
                'multiple' => true,
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'file';
            $data['multiple'] = true;
        }

        return $this->_create_input($data);
    }

    public function input_password($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'password',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'password';
        }

        return $this->_create_input($data);
    }

    public function input_radio($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'radio',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'radio';
        }

        return $this->_create_input($data);
    }

    public function input_radio_inline($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'radio',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline,
                'checkbox-inline' => 'inline'
            ];
        } else {
            $data['type'] = 'radio';
            $data['checkbox-inline'] = 'inline';
        }

        return $this->_create_input($data);
    }

    public function input_checkbox($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'checkbox',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'checkbox';
        }

        return $this->_create_input($data);
    }

    public function input_checkbox_inline($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'checkbox',
                'name' => $data,
                'id' => $id,
                'value' => $value,
                'string' => $string,
                'selected' => $selected,
                'label' => $label,
                'inline' => $inline,
                'checkbox-inline' => 'inline'
            ];
        } else {
            $data['type'] = 'checkbox';
            $data['checkbox-inline'] = 'inline';
        }

        return $this->_create_input($data);
    }

    public function input_image($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'image',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'image';
        }

        return $this->_create_input($data);
    }


    # ADDITIONAL FIELD ELEMENTS
    public function input_color($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'color',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'color';
        }

        return $this->_create_input($data);
    }

    public function input_email($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'email',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'email';
        }

        return $this->_create_input($data);
    }

    public function input_date($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'date',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'date';
        }

        return $this->_create_input($data);
    }

    public function input_datetime($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'datetime',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'datetime-local';
        }

        return $this->_create_input($data);
    }

    public function input_datetime_local($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'datetime-local',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'datetime-local';
        }

        return $this->_create_input($data);
    }

    public function input_month($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'month',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'month';
        }

        return $this->_create_input($data);
    }

    public function input_number($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'number',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'number';
        }

        return $this->_create_input($data);
    }

    public function input_range($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'range',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'range';
        }

        return $this->_create_input($data);
    }

    public function input_search($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'search',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'search';
        }

        return $this->_create_input($data);
    }

    public function input_tel($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'tel',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'tel';
        }

        return $this->_create_input($data);
    }

    public function input_time($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'time',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'time';
        }

        return $this->_create_input($data);
    }

    public function input_url($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'url',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'url';
        }

        return $this->_create_input($data);
    }

    public function input_week($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'week',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'week';
        }

        return $this->_create_input($data);
    }

    public function input($data)
    {
        # create inputs directly from arrays

        if (! array_key_exists('type', $data)) {
            return $this->_error_message('You must assign a field type to the <code>'.$data['name'].'</code> array');
        }

        if ($data['type'] == 'select') {
            return $this->input_select($data);
        } elseif ($data['type'] == 'text') {
            return $this->input_text($data);
        } elseif ($data['type'] == 'password') {
            return $this->input_password($data);
        } elseif ($data['type'] == 'textarea') {
            return $this->input_textarea($data);
        } elseif ($data['type'] == 'file') {
            return $this->input_upload($data);
        } elseif ($data['type'] == 'color') {
            return $this->input_color($data);
        } elseif ($data['type'] == 'email') {
            return $this->input_email($data);
        } elseif ($data['type'] == 'date') {
            return $this->input_date($data);
        } elseif ($data['type'] == 'datetime') {
            return $this->input_datetime($data);
        } elseif ($data['type'] == 'datetime-local') {
            return $this->input_datetime_local($data);
        } elseif ($data['type'] == 'month') {
            return $this->input_month($data);
        } elseif ($data['type'] == 'number') {
            return $this->input_number($data);
        } elseif ($data['type'] == 'range') {
            return $this->input_range($data);
        } elseif ($data['type'] == 'search') {
            return $this->input_search($data);
        } elseif ($data['type'] == 'tel') {
            return $this->input_tel($data);
        } elseif ($data['type'] == 'time') {
            return $this->input_time($data);
        } elseif ($data['type'] == 'url') {
            return $this->input_url($data);
        } elseif ($data['type'] == 'week') {
            return $this->input_week($data);
        }

        return $this->input_text($data);
    }


    # TEXTAREA
    protected function _create_textarea($data)
    {
        # show an error if the field name hasn't been supplied
        if (! $this->is_not_empty($data['name'])) {
            $this->_error_message('You must provide a name for the <strong>'.$data['type'].'</strong> element.');
            return false;
        }

        # if ID is empty, create an ID using the name
        if (! $this->is_not_empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        # open the element
        $return = '<textarea';

        # populate the field's value (on page load) with the session value
        if ($data['value'] == '' && $this->session_values && $this->session && ! empty($_SESSION[$this->session][$data['name']])) {
            $data['value'] = $_SESSION[$this->session][$data['name']];
        }

        # loop through the $data array and print each attribute
        foreach ($data as $key => $value) {
            if (! in_array($key, $this->no_keys) && $key != 'type' && $key != 'value') {
                $return .= ' '.$key.'="'.$value.'"';
            }
        }

        # add user-entered string and additional attributes
        // $return .= ' ' . $this->_attributes($data);

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # if required
        if ($this->_check_required($data['name'], $data)) {
            $return .= ' required';
        }

        # close the opening tag
        $return .= '>';

        # insert the posted value if available
        if (! empty($_POST[$data['name']])) {
            $return .= $_POST[$data['name']];
        } else {
            # insert the default value if available
            if ($this->is_not_empty($data['value'])) {
                $return .= $data['value'];
            }
        }

        # insert the closing tag
        $return .= '</textarea>';

        # if using inline validation
        $return .= $this->_inline($data['name']);

        $return = str_replace('  ', ' ', $return);

        $element = null;

        if (empty($data['fastform'])) {
            return $this->_wrapper($return, $data);
        }

        # we're using fastform(), which will run the element through wrapper()
        return $return;
    }

    public function input_textarea($data, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'textarea',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline
            ];
        } else {
            $data['type'] = 'textarea';
        }

        return $this->_create_textarea($data);
    }


    # SELECT MENU
    protected function _create_select($data)
    {
        # show an error if the field name hasn't been supplied
        if (! $this->is_not_empty($data['name'])) {
            $this->_error_message('You must provide a name for the <strong>'.$data['type'].'</strong> element.');
            return false;
        }

        # open the element
        $return = '<select name="'.$data['name'].'"';

        # if an ID wasn't supplied, create one from the field name
        if (! $this->is_not_empty($data['id'])) {
            $data['id'] = $data['name'];
        }

        # if we're selecting multiple items
        if (is_array($data['selected']) || isset($data['multiple'])) {
            $return .= ' multiple';
        }

        # add ID
        $return .= ' id="'.$data['id'].'"';

        # add user-entered string and additional attributes
        // $return .= ' ' . $this->_attributes($data);

        # 'fix' the classes attribute
        $return .= $this->_fix_classes($return, $data);

        # if required
        if ($this->_check_required($data['name'], $data)) {
            $return .= ' required';
        }

        # close the opening tag
        $return .= '>'.PHP_EOL;

        # a string was entered, so we'll grab the appropriate function from the Dropdowns class
        if (! empty($data['options']) && is_string($data['options'])) {
            if (isset($data['myarray'])) {
                # we're passing an array in the 9th parameter of the input_select() method
                $data['options'] = $this->_dropdowns($data['options'], $data['myarray']);
            } else {
                $data['options'] = $this->_dropdowns($data['options']);
            }
        }

        # if a default selected="selected" value is defined, use that one and give it an empty value
        # if one is set in an array, insert that one as we loop through it later on
        if (! is_array($data['selected']) && ! array_key_exists($data['selected'], $data['options']) && ! empty($data['selected'])) {
            $return .= "\t".'<option value="">'.$data['selected'].'</option>';
        }

        # options are user-defined
        # loop through the options array
        foreach ($data['options'] as $key => $value) {
            # if $value is an array, create an optgroup
            if (is_array($value)) {
                $return .= "\t".'<optgroup label="'.$key.'">';
                # loop through the array
                foreach ($value as $val => $label) {
                    # if the form has been posted, print selected option
                    if (isset($_POST[$data['name']]) && $_POST[$data['name']] == $val) {
                        $return .= "\t\t".'<option value="'.$val.'" selected="selected">'.$label.'</option>';
                    } # print selected option(s) on form load
                    elseif ($data['selected'] == $val || (is_array($data['selected']) && in_array($val, $data['selected']))) {
                        $return .= "\t\t".'<option value="'.$val.'" selected="selected">'.$label.'</option>';
                    } # print remaining options
                    else {
                        $return .= "\t\t".'<option value="'.$val.'">'.$label.'</option>';
                    }
                }
                $return .= "\t".'</optgroup>';
            } else {
                # if the form has been posted, print selected option(s)

                # check if the select is an array (key has brackets, e.g; <select name="foo[]">)
                if (isset($_POST[trim($data['name'], '[]')]) && is_array($_POST[trim($data['name'], '[]')]) && in_array($key, $_POST[trim($data['name'], '[]')])) {
                    $return .= "\t\t".'<option value="'.$key.'" selected>'.$value.'</option>'.PHP_EOL;
                } elseif (isset($_POST[$data['name']]) && $_POST[$data['name']] == $key) {
                    $return .= "\t\t".'<option value="'.$key.'" selected>'.$value.'</option>'.PHP_EOL;
                } # print selected option on form load
                elseif (! isset($_POST[$data['name']]) && $data['selected'] == $key || (is_array($data['selected']) && in_array($key, $data['selected']))) {
                    # populate the field's value (on page load) with the session value
                    if ($this->session_values && $this->session && ! empty($_SESSION[$this->session][$data['name']])) {
                        if ($_SESSION[$this->session][$data['name']] == $key) {
                            $return .= "\t\t".'<option value="'.$key.'" selected>'.$value.'</option>'.PHP_EOL;
                        }
                    } else {
                        if (! isset($data['multiple'])) {
                            $return .= "\t\t".'<option value="'.$key.'" selected>'.$value.'</option>'.PHP_EOL;
                        }
                    }
                } # print remaining options
                else {
                    # user has entered a value in the 'values' argument
                    if (! isset($_POST[$data['name']]) && $data['value'] === $key) {
                        $return .= "\t\t".'<option value="'.$key.'" selected>'.$value.'</option>'.PHP_EOL;
                    } else {
                        $return .= "\t\t".'<option value="'.$key.'">'.$value.'</option>'.PHP_EOL;
                    }
                }
            }
        }

        # close the element
        $return .= "\t".'</select>';

        # if using inline validation
        $return .= $this->_inline($data['name']);

        $return = str_replace('  ', ' ', $return);

        $element = null;

        if (empty($data['fastform'])) {
            if (! $this->wrapper) {
                if ($this->comments) {
                    $element .= PHP_EOL.'<!-- '.$data['name'].' -->'.PHP_EOL;
                }
                if (! empty($data['label'])) {
                    # output the element and label without a wrapper
                    $element .= $this->label($data).PHP_EOL;
                    $element .= $return.PHP_EOL;
                    return $element;
                } else {
                    # just return the element
                    $element .= $return.PHP_EOL;
                    return $this->_wrapper($element, $data);
                }
            } else {
                # wrap the element
                $element .= $return;
                return $this->_wrapper($element, $data);
            }
        }

        # we're using fastform(), which will run the element through wrapper()
        return $return;
    }

    protected function _dropdowns($menu, $data = null)
    {
        # this function enables the Dropdowns class to be used as a plugin
        # all we're doing is returning the selected array from the Dropdowns class

        # if needed, strip underscore from the beginning
        $menu = ltrim($menu, '_');

        # load the appropriate function from the Dropdowns class...

        # we're passing an array in the 9th parameter of the input_select() method for the MyDropdowns class
        if (class_exists('\MyDropdowns')) {
            if ($data) {
                return \MyDropdowns::$menu($data);
            }

            return \MyDropdowns::$menu();
        } else {
            if ($data) {
                return \Dropdowns::$menu($data);
            }

            return \Dropdowns::$menu();
        }
    }

    public function input_select($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'select',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline,
                'selected' => $selected,
                'options' => $options,
                'myarray' => $myarray
            ];
        } else {
            $data['type'] = 'select';
        }

        return $this->_create_select($data);
    }

    public function input_select_multiple($data, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'select',
                'name' => $data,
                'label' => $label,
                'value' => $value,
                'id' => $id,
                'string' => $string,
                'inline' => $inline,
                'selected' => $selected,
                'options' => $options,
                'myarray' => $myarray
            ];
        } else {
            $data['type'] = 'select';
        }

        $data['multiple'] = 'multiple';

        return $this->_create_select($data);
    }


    # INPUT ALIASES, FOR EVEN FASTER FORM BUILDING
    public function text($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_text($name, $label, $value, $id, $string, $inline);
    }

    public function hidden($name, $value = '')
    {
        return $this->input_hidden($name, $value);
    }

    public function file($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_file($name, $label, $value, $id, $string, $inline);
    }

    public function file_multiple($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_upload_multiple($name, $label, $value, $id, $string, $inline);
    }

    public function upload($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_upload($name, $label, $value, $id, $string, $inline);
    }

    public function upload_multiple($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_upload_multiple($name, $label, $value, $id, $string, $inline);
    }

    public function password($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_password($name, $label, $value, $id, $string, $inline);
    }

    public function radio($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        return $this->input_radio($name, $label, $value, $id, $string, $inline, $selected);
    }

    public function radio_inline($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        return $this->input_radio_inline($name, $label, $value, $id, $string, $inline, $selected);
    }

    public function checkbox($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        return $this->input_checkbox($name, $label, $value, $id, $string, $inline, $selected);
    }

    public function checkbox_inline($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '')
    {
        return $this->input_checkbox_inline($name, $label, $value, $id, $string, $inline, $selected);
    }

    public function email($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_email($name, $label, $value, $id, $string, $inline);
    }

    public function textarea($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_textarea($name, $label, $value, $id, $string, $inline);
    }

    public function select($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        return $this->input_select($name, $label, $value, $id, $string, $inline, $selected, $options, $myarray);
    }

    public function select_multiple($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        return $this->input_select_multiple($name, $label, $value, $id, $string, $inline, $selected, $options, $myarray);
    }

    public function dropdown($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        return $this->input_select($name, $label, $value, $id, $string, $inline, $selected, $options, $myarray);
    }

    public function dropdown_multiple($name, $label = '', $value = '', $id = '', $string = '', $inline = '', $selected = '', $options = '', $myarray = null)
    {
        return $this->input_select_multiple($name, $label, $value, $id, $string, $inline, $selected, $options, $myarray);
    }

    public function color($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_color($name, $label, $value, $id, $string, $inline);
    }

    public function date($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_date($name, $label, $value, $id, $string, $inline);
    }

    public function datetime($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_datetime_local($name, $label, $value, $id, $string, $inline);
    }

    public function datetime_local($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_datetime_local($name, $label, $value, $id, $string, $inline);
    }

    public function month($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_month($name, $label, $value, $id, $string, $inline);
    }

    public function number($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_number($name, $label, $value, $id, $string, $inline);
    }

    public function range($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_range($name, $label, $value, $id, $string, $inline);
    }

    public function search($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_search($name, $label, $value, $id, $string, $inline);
    }

    public function tel($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_tel($name, $label, $value, $id, $string, $inline);
    }

    public function time($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_time($name, $label, $value, $id, $string, $inline);
    }

    public function url($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_url($name, $label, $value, $id, $string, $inline);
    }

    public function week($name, $label = '', $value = '', $id = '', $string = '', $inline = '')
    {
        return $this->input_week($name, $label, $value, $id, $string, $inline);
    }

    public function button($name = '', $label = '', $value = '', $id = '', $string = '')
    {
        return $this->input_button($name, $label, $value, $id, $string);
    }


    # FIELDSET
    public function fieldset_open($legend = '', $string = '')
    {
        $return = '<fieldset';

        if ($string) {
            $return .= ' '.$string;
        }

        $return .= '>'.PHP_EOL;

        if ($legend) {
            $return .= '<legend>'.$legend.'</legend>';
        }

        return $this->_echo($return.PHP_EOL);
    }

    public function fieldset_close($string = '')
    {
        $return = '</fieldset>';

        if ($string) {
            $return .= $string;
        }

        return $this->_echo($return.PHP_EOL);
    }


    # LABELS
    protected function _create_label($data)
    {
        $return = null;

        # if there's a post error, create an <a> anchor for this field
        if ($this->errors() && $this->link_errors) {
            $return = '<a name="'.$data['name'].'"></a>';
        }

        # create an ID if one wasn't supplied
        $data['id'] = $this->make_id($data);

        # open the element
        $return .= '<label for="'.$data['id'].'"';

        # add an bootstrap error class if required
        if ($this->in_errors($data['name']) && $this->_wrapper_is('bootstrap')) {
            $return .= ' class="'.$this->controls['text-error'].'"';
        }

        # insert the string data if available

        if (! empty($data['string']) && ! in_array($data['type'], $this->_input_types('button'))) {
            $return .= ' '.$data['string'];
        }

        # close the tag
        $return .= '>';

        # add the label text, etc. if not using the label_open() method
        if ($data['label_type'] != 'open' && $data['type']) {
            # don't include label text or indicators if this is a button
            if (! in_array($data['type'], $this->_input_types('button'))) {
                # add the label text
                if ($this->is_not_empty($data['label'])) {
                    $return .= $data['label'];
                }

                # if required, let the user know by adding an asterisk, etc.
                if ($this->_check_required($data['name']) && ! empty($data['label'])) {
                    $return .= $this->required_indicator;
                }
            }

            # close the element
            $return .= '</label> ';
        }

        return $this->_echo($return);
    }

    public function label($data, $label = '', $id = '', $string = '')
    {
        if (! is_array($data)) {
            $data = [
                'type' => 'label',
                'name' => $data,
                'label' => $label,
                'id' => $id,
                'string' => $string,
                'label_type' => 'label'
            ];
        } else {
            $data['type'] = 'label';
            $data['label_type'] = 'label';
        }

        return $this->_create_label($data);
    }

    public function label_open($data, $label = '', $id = '', $string = '')
    {
        # opens a <label> tag

        if (! is_array($data)) {
            $data = [
                'name' => $data,
                'label' => $label,
                'id' => $id,
                'string' => $string,
                'label_type' => 'open'
            ];
        } else {
            $data['label_type'] = 'open';
        }

        return $this->_create_label($data);
    }

    public function label_close($data = '')
    {
        # closes a <label> tag
        # this is handy if we want to put our label text *after* the form element

        $return = null;

        if (! is_array($data)) {
            if ($data) {
                $return .= $data;
            }
        } else {
            if ($this->_check_required($data['name']) && $data['type'] != 'radio' && ! in_array($data['type'], $this->_input_types('button'))) {
                # we don't want the indicator next to radios and checkboxes if they're in an group/array
                if (empty($data['group'])) {
                    $return .= $this->required_indicator;
                }
            }
            $return .= $data['label'];
        }

        $return .= PHP_EOL.'</label>';

        return $this->_echo($return);
    }


    # SIMPLE FORM CREATION
    public function create($string, $form = false)
    {
        # SIMPLE FORM CREATION
        # create and wrap inputs using labels as our keys

        # set our $return var for later
        $return = null;

        if ($form) {
            if (! $this->id) {
                $this->id = 'myFormr';
            }

            if ($form === 'multipart') {
                $return .= $this->form_open_multipart();
            } else {
                $return .= $this->form_open();
            }
        }

        # break apart the comma delimited string of form labels
        $parts = explode(',', $string);

        # loop through each part and set the $data array values
        foreach ($parts as $label) {
            $data = [
                'type' => 'text',
                'name' => strtolower(str_replace(' ', '_', trim($label))),
                'id' => strtolower(str_replace(' ', '_', trim($label))),
                'value' => null,
                'string' => null,
                'label' => trim($label),
                'inline' => null
            ];

            $return .= $this->_open_list_wrapper();

            # label string contains the word 'email', use email input type
            if (str_contains(strtolower($label), 'email') && ! str_contains(strtolower($label), '|email')) {
                $return .= $this->input_email($data);
            } elseif (str_contains(strtolower($label), '|')) {
                # we want to use an specific input type
                $type = substr($label, strpos($label, '|') + 1);

                # correct our label text by removing the | and input type
                $data['label'] = str_replace('|'.$type, '', $label);

                # correct our input's name
                $data['name'] = strtolower(str_replace(' ', '_', trim($data['label'])));

                # correct our input's ID
                $data['id'] = strtolower(str_replace(' ', '_', trim($data['label'])));

                # define the method's name
                $name = 'input_'.$type;

                # add a default value for checkbox or radio
                if ($type == 'checkbox' || $type == 'radio') {
                    $data['value'] = $data['name'];
                }

                # we want to create a multiple file upload element
                if ($type == 'file[]') {
                    $data['name'] = $data['name'].'[]';
                    $return .= $this->input_upload_multiple($data);
                } else {
                    # return the input
                    $return .= $this->$name($data);
                }
            } else {
                # default to text type
                $return .= $this->input_text($data);
            }

            $return .= $this->_close_list_wrapper();
        }

        if ($form) {
            $return .= $this->input_button_submit();
            $return .= $this->form_close();
        }

        return $return;
    }

    public function create_form($string)
    {
        # alias of create(), except opens and closes form tag, plus adds submit button

        return $this->create($string, true);
    }

    public function create_form_multipart($string)
    {
        # alias of create_form(), except adds enctype="multipart/form-data"

        return $this->create($string, 'multipart');
    }


    # FAST FORM
    public function fastform($input, $csrf = false, $multipart = false)
    {
        # method for automatically building and laying out a form with multiple elements

        # create an empty array outside of looping to store hidden inputs
        $hidden = [];
        $data = [];

        if (is_string($input)) {
            # user entered a string and wants to use a pre-built form in the Forms class
            return $this->_faster_form($input, $csrf, $multipart);
        }

        # build the <form> tag
        if ($multipart) {
            $return = $this->form_open_multipart();
        } else {
            $return = $this->form_open();
        }

        if ($csrf) {
            if (is_integer($csrf)) {
                $timeout = (int)$csrf;
                $return .= $this->csrf($timeout);
            } else {
                $return .= $this->csrf();
            }
        }

        # lets see if we need to wrap this in a list...
        $return .= $this->_open_list_wrapper();

        # loop through the array and print/process each field value
        foreach ($input as $key => $value) {
            # check if we're creating a fieldset
            if (str_contains($key, 'fieldset')) {
                # open the fieldset and add the legend text
                $return .= $this->fieldset_open($value['legend']);

                # loop through the fieldset array and get each form field
                foreach ($value['fields'] as $fieldKey => $fieldValue) {
                    # check if the field is required
                    $this->_check_required($fieldValue);

                    # determine the type of form element we'll need
                    $data = $this->_parse_fastform_values($fieldKey, $fieldValue);

                    # tell other methods we're using FastForm
                    $data['fastform'] = true;

                    # print the form element
                    $return .= $this->_fastform_fields($data);
                }

                $return .= $this->fieldset_close();
            } else {
                # check if the field is required
                $this->_check_required($value);

                # determine the type of form element we'll need
                $data = $this->_parse_fastform_values($key, $value);

                # tell other methods we're using FastForm
                $data['fastform'] = true;

                # we're putting any hidden elements into an array and printing them at the end of the form
                if ($data['type'] == 'hidden') {
                    if (isset($data['value'])) {
                        $hidden[] = $this->input_hidden($data['name'], $data['value']);
                    } else {
                        $hidden[] = $this->input_hidden($data['name'], $data['label']);
                    }
                } else {
                    # print the form element
                    $return .= $this->_fastform_fields($data);
                }
            }
        }

        # see if a submit button was added while building the form
        if ($data['type'] == 'button' || $data['type'] == 'submit') {
            $item = $this->input_button_submit($data);
        } else {
            # create a default submit with no options
            $data['type'] = 'submit';
            $data['name'] = 'submit';
            $data['label'] = '';
            $data['value'] = $this->submit;
            $data['id'] = 'submit';
            $data['string'] = '';
            $data['inline'] = '';
            $data['selected'] = '';
            $data['options'] = '';
            $data['class'] = '';

            $item = $this->input_submit($data);
        }

        $return .= $this->_wrapper($item, $data);

        # close the list tag
        $return .= $this->_close_list_wrapper();

        # if hidden fields are set, print them now
        if (! empty($hidden)) {
            foreach ($hidden as $hidval) {
                $return .= $hidval."\r\n";
            }
        }

        # close the </form>
        $return .= $this->form_close();

        return $return;
    }

    public function fastform_multipart($data, $csrf = false)
    {
        # for file uploads...

        return $this->fastform($data, $csrf, 'multipart');
    }

    private function _faster_form($form_name, $csrf, $multipart)
    {
        # this method enables the Forms class to be used as a plugin so that we can store
        # arrays of frequently used forms and pass them through the fastform() function

        # create the array by passing the function name to the Forms class

        if (! $this->id) {
            $this->id = $form_name;
        }

        if (class_exists('\MyForms') && method_exists('\MyForms', $form_name)) {
            $data = \MyForms::$form_name();
        } else {
            if (! method_exists('\Forms', $form_name)) {
                return $this->error_message("The FastForm method: <code>{$form_name}</code> does not exist");
            }
            $data = \Forms::$form_name();
        }

        # pass the array to the fastform() method
        if ($multipart) {
            return $this->fastform_multipart($data, $csrf);
        } else {
            return $this->fastform($data, $csrf);
        }
    }

    private function _parse_fastform_values($key, $data)
    {
        if (! is_array($data)) {
            # the fastform() values are in a string
            # explode them and get each value
            $explode = explode($this->delimiter[0], $data);

            # $data is currently a string, convert it to an array
            $data = [];

            # determine the field's type
            $data = $this->_fastform_define_field_type($key, $data);

            # start populating the $data array
            if (! empty($explode[0])) {
                $data['name'] = trim($explode[0]);
            } else {
                die('error: please provide a name for the <strong>'.$key.'</strong> field');
            }

            if (! empty($explode[1])) {
                $data['label'] = trim($explode[1]);
            } else {
                $data['label'] = '';
            }

            if (! empty($explode[2])) {
                $data['value'] = trim($explode[2]);
            } else {
                $data['value'] = '';
            }

            if (! empty($explode[3])) {
                $data['id'] = trim($explode[3]);
            } else {
                $data['id'] = '';
            }

            if (! empty($explode[4])) {
                $data['string'] = trim($explode[4]);
            } else {
                $data['string'] = '';
            }

            if (! empty($explode[5])) {
                $data['inline'] = trim($explode[5]);
            } else {
                $data['inline'] = '';
            }

            if (! empty($explode[6])) {
                $data['selected'] = trim($explode[6]);
            } else {
                $data['selected'] = '';
            }

            if (! empty($explode[7])) {
                $data['options'] = trim($explode[7]);
            } else {
                $data['options'] = '';
            }

            # hidden types don't really require an id, so we'll insert the id into the value
            # if ($data['type'] == 'hidden') {
            #     $data['value'] = $data['id'];
            # }
        } else {
            # determine the field's type
            $data = $this->_fastform_define_field_type($key, $data);

            if (empty($data['id'])) {
                $data['id'] = '';
            }
            if (empty($data['value'])) {
                $data['value'] = '';
            }
            if (empty($data['string'])) {
                $data['string'] = '';
            }
            if (empty($data['label'])) {
                $data['label'] = '';
            }
            if (empty($data['inline'])) {
                $data['inline'] = '';
            }
            if (empty($data['selected'])) {
                $data['selected'] = '';
            }
            if (empty($data['options'])) {
                $data['options'] = '';
            }
        }

        return $data;
    }

    private function _fastform_define_field_type($key, $data)
    {
        # this method assigns a field type based on the $key's value
        if (! is_array($data)) {
            $data = [];
        }

        # determines if the field name is in the array's key or value
        if ($this->_starts_with($key, 'select') || $this->_starts_with($key, 'dropdown') || $this->_starts_with($key, 'state') || $this->_starts_with($key, 'states') || $this->_starts_with($key, 'country')) {
            if ($key == 'select_multiple') {
                $data['type'] = 'select_multiple';
            } else {
                $data['type'] = 'select';
            }
        } elseif ($this->_starts_with($key, 'fieldset')) {
            $data['type'] = 'fieldset';
        } elseif ($this->_starts_with($key, 'submit')) {
            $data['type'] = 'submit';
        } elseif ($this->_starts_with($key, 'reset')) {
            $data['type'] = 'reset';
        } elseif ($this->_starts_with($key, 'button')) {
            $data['type'] = 'button';
        } elseif ($this->_starts_with($key, 'hidden')) {
            $data['type'] = 'hidden';
        } elseif ($this->_starts_with($key, 'password')) {
            $data['type'] = 'password';
        } elseif ($this->_starts_with($key, 'file')) {
            $data['type'] = 'file';
        } elseif ($this->_starts_with($key, 'image')) {
            $data['type'] = 'image';
        } elseif ($this->_starts_with($key, 'checkbox')) {
            $data['type'] = 'checkbox';
        } elseif ($this->_starts_with($key, 'radio')) {
            $data['type'] = 'radio';
        } elseif ($this->_starts_with($key, 'textarea')) {
            $data['type'] = 'textarea';
        } elseif ($this->_starts_with($key, 'color')) {
            $data['type'] = 'color';
        } elseif ($this->_starts_with($key, 'email')) {
            $data['type'] = 'email';
        } elseif ($this->_starts_with($key, 'datetime_local')) {
            $data['type'] = 'datetime_local';
        } elseif ($this->_starts_with($key, 'datetime')) {
            $data['type'] = 'datetime';
        } elseif ($this->_starts_with($key, 'date')) {
            $data['type'] = 'date';
        } elseif ($this->_starts_with($key, 'month')) {
            $data['type'] = 'month';
        } elseif ($this->_starts_with($key, 'number')) {
            $data['type'] = 'number';
        } elseif ($this->_starts_with($key, 'range')) {
            $data['type'] = 'range';
        } elseif ($this->_starts_with($key, 'search')) {
            $data['type'] = 'search';
        } elseif ($this->_starts_with($key, 'tel')) {
            $data['type'] = 'tel';
        } elseif ($this->_starts_with($key, 'time')) {
            $data['type'] = 'time';
        } elseif ($this->_starts_with($key, 'url')) {
            $data['type'] = 'url';
        } elseif ($this->_starts_with($key, 'week')) {
            $data['type'] = 'week';
        } elseif ($this->_starts_with($key, 'label')) {
            $data['type'] = 'label';
        } else {
            $data['type'] = 'text';
        }

        return $data;
    }

    private function _fastform_fields($data)
    {
        # determines what kind of form field element we need

        $return = null;

        if ($data['type'] == 'hidden' || $data['type'] == 'submit' || $data['type'] == 'button' || $data['type'] == 'reset') {
            return null;
        }

        if ($data['type'] == 'label') {
            $return .= $this->label($data);
        } elseif ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
            if ($this->is_in_brackets($data['value'])) {
                # we have a radio/checkbox array
                # loop through the value in the array, create elements and put them all into one wrapper with one label

                # put each element value into an array and return them
                $item = $this->_build_input_groups($data);

                # strip out the label for the element

                # wrap the element
            } else {
                # build each element individually and wrap it in a label
                $item = "<label for=\"{$data['id']}\">\r\n";

                if ($data['type'] == 'radio') {
                    $item .= $this->input_radio($data);
                } else {
                    $item .= $this->input_checkbox($data);
                }

                $item .= " {$data['label']}\r\n</label>\r\n";

                # empty the label value so the wrapper function won't build it again

                # wrap it
            }
            $data['label'] = '';
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'select_multiple') {
            $item = $this->input_select_multiple($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'select') {
            $item = $this->input_select($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'text') {
            $item = $this->input_text($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'password') {
            $item = $this->input_password($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'textarea') {
            $item = $this->input_textarea($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'file') {
            $item = $this->input_upload($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'color') {
            $item = $this->input_color($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'email') {
            $item = $this->input_email($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'date') {
            $item = $this->input_date($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'datetime' || $data['type'] == 'datetime-local') {
            $item = $this->input_datetime_local($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'month') {
            $item = $this->input_month($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'number') {
            $item = $this->input_number($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'range') {
            $item = $this->input_range($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'search') {
            $item = $this->input_search($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'tel') {
            $item = $this->input_tel($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'time') {
            $item = $this->input_time($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'url') {
            $item = $this->input_url($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'week') {
            $item = $this->input_week($data);
            $return .= $this->_wrapper($item, $data);
        } elseif ($data['type'] == 'reset') {
            $item = $this->input_reset($data);
            $return .= $this->_wrapper($item, $data);
        } else {
            # default to text
            $item = $this->input_text($data);
            $return .= $this->_wrapper($item, $data);
        }

        return $return;
    }


    # MISC
    private function _inline($name)
    {
        # add div if using inline errors
        if ($this->in_errors($name) && $this->inline_errors) {
            return '<div class="'.$this->inline_errors_class.'"></div>';
        }

        return null;
    }

    private function _starts_with($key, $str)
    {
        # check if a string starts with the given word

        return mb_substr($key, 0, strlen($str)) == $str;
    }

    private function _strip_brackets($str)
    {
        # strip brackets from a string

        return trim($str, '[]');
    }

    private function _suppress_formr_validation_errors($data)
    {
        # suppress Formr's default validation error messages and only show user-defined messages

        if (array_key_exists('string', $data) && $this->custom_validation_messages) {
            return true;
        }

        return false;
    }

    private function _wrapper_is($string)
    {
        # determines if the wrapper is a supported framework or default

        if ($string == 'framework') {
            if (str_contains($this->wrapper, 'bootstrap')) {
                return true;
            }

            if (str_contains($this->wrapper, 'bulma')) {
                return true;
            }

            if (str_contains($this->wrapper, 'tailwind')) {
                return true;
            }

            if (str_contains($this->wrapper, 'uikit')) {
                return true;
            }

            return false;
        }


        if (str_contains($this->wrapper, $string)) {
            return true;
        }

        return false;
    }

    public function heading($key, $string)
    {
        # put your string in here and it'll be highlighted when the field receives an error
        # useful in questionnaires and the like.

        if (array_key_exists($key, $this->errors)) {
            return $this->_echo('<h2><span class="error">'.$string.'</span></h2>');
        }

        return $this->_echo('<h2>'.$string.'</h2>');
    }

    public function send_email($to, $subject, $message, $from = '', $html = false, $headers = null)
    {
        # really simple method for firing off a quick email
        # something I was playing around with and forgot about...
        # TODO? may add to/improve this in the future

        $msg = null;

        if ($html) {
            # we're sending an HTML email

            if (! $headers) {
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                if ($from) {
                    $headers .= "From: ".$this->_clean_value($from)."\r\n";
                }
            }

            $msg .= "<html>\r\n";
            $msg .= "<body>\r\n";
            $msg .= "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\r\n";
        }

        # loop through $_POST and print key => value
        if (strtolower($message) == 'post' || is_array($message)) {
            foreach ($_POST as $key => $value) {
                if ($key != 'submit' && $key != 'button' && $key != 'FormrID' && $key != 'csrf_token') {
                    # make sure it's a valid email address
                    if (! empty($value) && (str_contains(strtolower($key), 'email')) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$key] = 'Please enter a valid email address';
                    }

                    # check if required
                    if ($this->_check_required($key)) {
                        # add to errors array
                        if (empty($value)) {
                            $this->errors[$key] = '<strong>'.str_replace('_', ' ', ltrim($key, '_')).'</strong> is required';
                        }
                    }

                    # if key is prepended with an underscore, replace all underscores with a space
                    # _First_Name becomes First Name

                    if ($key[0] == '_') {
                        $key = str_replace('_', ' ', ltrim($key, '_'));
                    }

                    # if key is an array, print all values

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    if ($html) {
                        $msg .= "<tr>\r\n";
                        $msg .= "\t<td><strong>$key:</strong></td>\r\n";
                        $msg .= "\t<td>".$this->_clean_value($value)."</td>\r\n";
                        $msg .= "</tr>\r\n";
                    } else {
                        $msg .= $key.": \t".$this->_clean_value($value)."\r\n";
                    }
                }
            }
        } else {
            # message is supplied by user
            $msg .= $message;
        }

        if ($html) {
            $msg .= "</table>\r\n";
            $msg .= "</body>\r\n";
            $msg .= "</html>\r\n";
        }

        # send the email
        if (! $this->errors()) {
            if ($html) {
                if (mail($to, $subject, $msg, $headers)) {
                    return true;
                }
            } else {
                if (mail($to, $subject, $msg)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function send_html_email($to, $subject, $message, $from = '', $headers = null)
    {
        return $this->send_email($to, $subject, $message, $from, true, $headers);
    }

    public function get_ip_address($mysql = false)
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        if ($mysql) {
            return ip2long($ip_address);
        }

        return $ip_address;
    }

    public function make_id($data)
    {
        # create an ID from the element's name attribute (if an ID was not specified)

        if ($this->is_not_empty($data['id'])) {
            return trim($data['id'], '[]');
        }

        return trim($data['name'], '[]');
    }

    public function insert_required_indicator($data)
    {
        # insert the required_field indicator if applicable

        if (! in_array($data['type'], $this->excluded_types)) {
            if ($this->_check_required($data['name']) && $this->is_not_empty($data['label'])) {
                return $this->required_indicator;
            }
        }

        return null;
    }

    public function type_is_checkbox($data)
    {
        # determines if the element is a checkbox or radio

        if ($data['type'] == 'checkbox' || $data['type'] == 'radio') {
            return true;
        }

        return false;
    }

    public function is_array($data)
    {
        # determines is the element's name is an array

        if (str_ends_with($data, ']')) {
            return true;
        }

        return false;
    }

    public function is_in_brackets($data)
    {
        # determines if the given word is contained in brackets

        if (! empty($data) && mb_substr($data, 0, 1) == '[') {
            return true;
        }

        return false;
    }

    public function csrf($timeout = 3600)
    {
        # add csrf protection
        # remember to put session_start() at the top of your script!

        if (session_status() == PHP_SESSION_NONE) {
            $this->_error_message('CSRF requires <code>session_start()</code> at the top of the script.');
        }

        # put the token into a session
        if (empty($_SESSION['formr']['token'])) {
            if (function_exists('mcrypt_create_iv')) {
                $_SESSION['formr']['token'] = bin2hex(random_bytes(32));
            } else {
                $_SESSION['formr']['token'] = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }

        # we're putting the token and expiration time into the hidden element
        $string = $_SESSION['formr']['token'].'|'.(time() + $timeout);

        return $this->_echo("<input type=\"hidden\" name=\"csrf_token\" value=\"{$string}\">\r\n\r\n");
    }

    public function redirect($url = null)
    {
        # redirect to the given url after the form has been submitted

        if (! $url || $url == 'self') {
            $url = $_SERVER['PHP_SELF'];
        }

        header('Location: '.$url);

        exit;
    }

    public function unset_session()
    {
        # deletes the formr, and user-defined sessions, handy for testing

        unset($_SESSION['formr']);

        if (isset($_SESSION[$this->session])) {
            unset($_SESSION[$this->session]);
        }

        echo $this->_success_message("SESSION[$this->session] has been unset");

        $this->session = null;
        $this->session_values = null;
    }

    public function ok()
    {
        if (isset($_POST['csrf_token']) && ! isset($_SESSION['formr']['token'])) {
            return false;
        }

        if (empty($this->errors)) {
            return true;
        }

        return false;
    }

    private function _check_for_honeypot()
    {
        # die if the honeypot caught a bear

        if ($this->honeypot && $_SERVER['REQUEST_METHOD'] == 'POST') {
            if (! empty($_POST[$this->honeypot])) {
                die;
            }
        }

        return null;
    }

    private function _check_for_csrf()
    {
        # check if we're using csrf
        if (isset($_POST['csrf_token']) && isset($_SESSION['formr']['token'])) {
            # grab the token and expiration time from the hidden element
            $parts = explode('|', $_POST['csrf_token']);

            # check if token in SESSION equals posted token value
            if (hash_equals((string)$_SESSION['formr']['token'], strval($parts[0]))) {
                # compare current time to time of token expiration
                if (time() >= $parts[1]) {
                    $this->_error_message('Your session has expired. Please refresh the page.');

                    # reset the token
                    $_SESSION['formr']['token'] = null;
                }
            }

            if (isset($message)) {
                $this->error_message($message);
            }
        }
    }

    private function _handle_session_checkbox_arrays()
    {
        if ($this->session && $this->session_values && isset($_SESSION[$this->session])) {
            # Here is where we are handling checkbox arrays in our session.
            # We have created a hidden element inside the _create_input() method which contains the array's values.
            # example: <input type="hidden" name="colors" value="red,green,blue">
            # We're going to go through those values and match them against what was posted...

            # here's where we'll store the checkbox array values we get from the hidden element(s)
            $created_checkbox_values = [];

            # here's where we'll store the values of the checkboxes that were ticked upon submit
            $posted_checkbox_values = [];

            foreach ($_POST as $key => $value) {
                # check if the post value is an array
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        # put the posted checkbox value into the posted array
                        $posted_checkbox_values[$key][] = $v;
                    }
                }

                # the checkbox array's hidden element value is a string, so let's explode it and put it into our array
                if (str_contains($key, '_values')) {
                    $created_checkbox_values[str_replace('_values', '', $key)] = explode(',', $_POST[$key]);
                }
            }

            # we're now going to compare the form's checkbox values to the values that were actually posted
            if (! empty($posted_checkbox_values)) {
                foreach ($created_checkbox_values as $key => $array) {
                    # if a checkbox value is *not* posted in an array group, remove it from the session
                    if (! empty($posted_checkbox_values[$key]) && isset($_SESSION[$this->session][$key])) {
                        $clean1 = array_diff($posted_checkbox_values[$key], $array);
                        $clean2 = array_diff($array, $posted_checkbox_values[$key]);
                        $output = array_merge($clean1, $clean2);
                        foreach ($output as $value) {
                            foreach ($_SESSION[$this->session][$key] as $skey => $svalue) {
                                if ($svalue == $value) {
                                    unset($_SESSION[$this->session][$key][$skey]);
                                }
                            }
                        }
                    } else {
                        # no checkbox values were posted in an array group, so remove the checkbox value from the session's array group
                        foreach ($array as $array_key => $array_value) {
                            if (isset($_SESSION[$this->session][$key])) {
                                # if the array is empty, just remove it
                                if (empty($_SESSION[$this->session][$key])) {
                                    unset($_SESSION[$this->session][$key]);
                                } else {
                                    # remove each un-posted checkbox value from the session
                                    foreach ($_SESSION[$this->session][$key] as $skey => $svalue) {
                                        if ($svalue == $array_value) {
                                            unset($_SESSION[$this->session][$key][$skey]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                # no checkbox arrays were posted at all, so remove the checkbox array(s) from the session
                foreach ($created_checkbox_values as $key => $value) {
                    if (isset($_SESSION[$this->session][$key])) {
                        unset($_SESSION[$this->session][$key]);
                    }
                }
            }
        }
    }

    public function recaptcha_passed()
    {
        # google recaptcha v3
        # here's where we bring it all together and validate with google's servers on the back-end
        # from Gatsby's answer on stackoverflow: https://stackoverflow.com/a/60036326

        if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($this->recaptcha_secret_key && $this->recaptcha_site_key)) {
            $data = [
                'secret' => $this->recaptcha_secret_key,
                'response' => $_POST['formrToken'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            # creates and returns stream context with options supplied in options preset
            $context = stream_context_create($options);

            # use curl or file_get_contents()
            if ($this->recaptcha_use_curl) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => [
                        'secret' => $this->recaptcha_secret_key,
                        'response' => $_POST['formrToken'],
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true
                ]);
                $response = curl_exec($ch);
                curl_close($ch);
            } else {
                $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            }

            # convert the json encoded string to a php variable
            $result = json_decode($response, true);

            if (! empty($result) && $result['success'] && ($result['score'] >= $this->recaptcha_score)) {
                return true;
            }
        }

        return false;
    }

    public function recaptcha_head()
    {
        # google recaptcha v3
        # prints script src for client-side validation
        # from Gatsby's answer on stackoverflow: https://stackoverflow.com/a/60036326

        $this->_echo("<script src=\"https://www.google.com/recaptcha/api.js?render={$this->recaptcha_site_key}\"></script>\r\n");
    }

    public function recaptcha_body()
    {
        # google recaptcha v3
        # prints front-end javascript for client-side token retrieval
        # from Gatsby's answer on stackoverflow: https://stackoverflow.com/a/60036326

        $return = "<script>\r\n";
        $return .= "   grecaptcha.ready(function() {\r\n";
        $return .= "      grecaptcha.execute('{$this->recaptcha_site_key}', {\r\n";
        if ($this->recaptcha_action_name) {
            $return .= "         action:'".$this->recaptcha_action_name."'\r\n";
        } else {
            $return .= "         action:'Formr'\r\n";
        }
        $return .= "      }).then(function(formrToken) {\r\n";
        $return .= "         document.getElementById('formrToken').value = formrToken;\r\n";
        $return .= "      });\r\n";
        $return .= "      // refresh token every minute to prevent expiration\r\n";
        $return .= "      setInterval(function(){\r\n";
        $return .= "         grecaptcha.execute('{$this->recaptcha_site_key}', {\r\n";
        if ($this->recaptcha_action_name) {
            $return .= "         action:'".$this->recaptcha_action_name."'\r\n";
        } else {
            $return .= "         action:'Formr'\r\n";
        }
        $return .= "         }).then(function(formrToken) {\r\n";
        $return .= "            document.getElementById('formrToken').value = formrToken;\r\n";
        $return .= "         });\r\n";
        $return .= "      }, 60000);\r\n";
        $return .= "   });\r\n";
        $return .= "</script>\r\n";

        $this->_echo($return);
    }

    public function clear()
    {
        # will show empty form fields after form is submitted

        $_POST = [];
    }

    public function error($key)
    {
        foreach ($this->errors as $k => $message) {
            if ($key == $k) {
                $this->_echo($message);
            }
        }
    }
}
