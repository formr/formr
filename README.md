# Formr

Formr is a PHP micro-framework which installs easily and helps you build, layout and validate forms quickly, painlessly, and without all the complicated, messy overhead.

Find docs and screencasts here: [http://formr.github.io](http://formr.github.io)

If you find Formr useful, please consider starring the project and/or making a [donation](https://paypal.me/timgavin). Thank you!

![formr](https://user-images.githubusercontent.com/1012049/60391635-c9f4f280-9aa7-11e9-8167-fce28a70220c.gif)

## Features

- Create complex forms with server-side processing and validation in only minutes
- Bootstrap ready; automatically wrap all of your form elements and messages in Bootstrap classes
- Instantly make one field required, all fields required, or all but one field required
- Built-in `POST` validation rules, including validating email, comparisons, slugging and hashing
- Automatically build and format `label` tags, saving lots of time
- Create and validate radio groups and checkbox arrays in seconds
- Automatically wrap field elements in `p`, `div`, `ul`, `ol`, `dl`, Bootstrap's `.form-control` or roll your own
- Extensible: roll your own form &amp; validation sets and dropdown menus and share 'em with others
- Extensible: easily create your own field element wrappers
- Send plain text and HTML emails
- Upload and resize images
- Generate CSRF tokens and set the expiration time
- Object-oriented; supports multiple forms per page
- Little helpers to assist in building, layout, testing and debugging
- And a ton of other cool stuff!

## Installation
Download the .zip file and place the Formr folder in your project, then include the Formr class and create a new form object; that's it!

```php
require_once 'Formr/class.formr.php';
$form = new Formr();
```

## Bootstrap Ready
Bootstrap form classes are ready to go! Just tell Formr you want to use Bootstrap when creating a new form and Formr will take care of the rest.

```php
require_once 'Formr/class.formr.php';
$form = new Formr('bootstrap');
```

## Basic Example

Simply enter your form fields as a comma delimited string and Formr will build the form, complete with email validation and all values retained upon POST.

```php
$form = new Formr('bootstrap');
echo $form->form_open();
echo $form->create('First name, Last name, Email address, Age|number, Comments|textarea');
echo $form->input_submit();
echo $form->form_close();
```

#### Produces the following HTML

```html
<form action="/index.php" method="post" accept-charset="utf-8">
    <div id="_first_name" class="form-group">
        <label class="control-label" for="first_name">
            First name
        </label>
        <input type="text" name="first_name" id="first_name" class="form-control">
    </div>
    <div id="_last_name" class="form-group">
        <label class="control-label" for="last_name">
            Last name
        </label>
        <input type="text" name="last_name" id="last_name" class="form-control">
    </div>
    <div id="_email_address" class="form-group">
        <label class="control-label" for="email_address">
            Email address
        </label>
        <input type="email" name="email_address" id="email_address" class="form-control">
    </div>
    <div id="_age" class="form-group">
        <label class="control-label" for="age">
            Email address
        </label>
        <input type="number" name="age" id="age" class="form-control">
    </div>
    <div id="_comments" class="form-group">
        <label class="control-label" for="comments">
            Comments
        </label>
        <textarea name="comments" id="comments" class="form-control"></textarea>
    </div>
    <div id="_submit" class="form-group">
        <label class="sr-only" for="submit"></label>
        <input type="submit" name="submit" value="Submit" class="btn" id="submit">
    </div>
</form>
```

## Pre-Built Forms

Formr has several common forms already baked in, and it's easy to create and save your own.

```php
$form = new Formr();
echo $form->fastform('contact');
```

#### Produces the following HTML

```html
<form action="/index.php" method="post" accept-charset="utf-8">
    <fieldset>
        <label for="fname">
            First name:
        </label> 
        <input type="text" name="fname" id="fname" class="input">

        <label for="lname">
            Last name:
        </label> 
        <input type="text" name="lname" id="lname" class="input">

        <label for="email">
            Email:
        </label> 
        <input type="email" name="email" id="email" class="input">

        <label for="comments">
            Comments:
        </label> 
        <textarea name="comments" id="comments" class="input" ></textarea>

        <input type="submit" name="submit" value="Submit" id="submit">
    </fieldset>
</form>
```

## Build Forms With Arrays

```php
$data = [
    'text' => 'fname, First name:',
    'email' => 'email, Email:',
    'checkbox' => 'agree, I Agree',
];

$form = new Formr('bootstrap');
echo $form->fastform($data);
```

#### Produces the following HTML

```html
<form action="/index.php" method="post" accept-charset="utf-8">
    <fieldset>
        <div id="_fname" class="form-group">
            <label class="control-label" for="fname">
                First name:
            </label>
            <input type="text" name="fname" class="form-control" id="fname">
        </div>
        
        <div id="_email" class="form-group">
            <label class="control-label" for="email">
                Email:
            </label>
            <input type="email" name="email" class="form-control" id="email">
        </div>
        
        <div id="_agree" class="checkbox">
            <label for="agree">
                <input type="checkbox" name="agree" value="agree" id="agree"> I Agree
            </label>
        </div>
        
        <div id="_submit" class="form-group">
            <label class="sr-only" for="submit"></label>
            <input type="submit" name="submit" value="Submit" id="submit" class="btn">
        </div>
    </fieldset>
</form>
```

## Build Forms Your Way

You have full control over how you build your forms...

```html
<div class="my-wrapper-class">
    <?php echo $form->input_text('first_name', 'First name'); ?>
</div>

<div class="my-wrapper-class">
    <?php echo $form->input_email('email', 'Email address', 'john@example.com', 'emailID', 'placeholder="email@domain.com"'); ?>
</div>
```

#### Produces the following HTML

```html
<div class="my-wrapper-class">
    <label for="first_name">
        First name
    </label>
    <input type="text" name="first_name" id="first_name">
</div>

<div class="my-wrapper-class">
    <label for="emailID">
        Email address
    </label>
    <input type="email" name="email" id="emailID" value="john@example.com" placeholder="email@domain.com">
</div>
```

## Validation

Formr can easly process and validate your forms.

The following is a very basic example, however Formr's validation methods are quite powerful and include among other things comparing values between fields and hashing using `bcrypt()`

Let's get the `POST` value of an `email` field.

```php
$email = $form->post('email');
```

Now let's make sure it's a valid email address by entering the `valid_email` validation rule in the third parameter. If there's an error, the text entered into the second parameter will notify the user to correct the `Email` field.

```php
$email = $form->post('email','Email','valid_email');
```

We can take that a step further and enter a full custom error message in the second parameter to make our forms even more user-friendly.

```php
$form->post('email','Email|Please enter a valid email address','valid_email');
```

## Full Example

```php
<?php
// include the Formr class
require_once 'Formr/class.formr.php';

// create our form object and use Bootstrap as our form wrapper
$form = new Formr('bootstrap');

// make all fields required
$form->required = '*';

// check if the form has been submitted
if($form->submit())
{
    // get the values of our form fields
    $first_name = $form->post('first_name');
    $last_name = $form->post('last_name');
    $email = $form->post('email_address', 'Email', 'valid_email');
    
    // email the results
    $to = 'recipient@email.com';
    $from = 'me@domain.com';
    $subject = 'Ahoy, Matey!';
    
    // this takes all of the POST values and automatically formats them in an HTML email!
    $form->send_email($to, $subject, 'POST', $from, 'HTML');
}

// print any error messages if they're available
echo $form->messages()

// open the form
echo $form->form_open();

// add the form fields
echo $form->create('First name, Last name, Email address');

// add a submit button
echo $form->input_submit();

// close the form
echo $form->form_close();
```
