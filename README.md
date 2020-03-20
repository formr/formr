# Formr

Formr is a PHP micro-framework which installs easily and helps you build, layout and validate forms quickly, painlessly, and without all the complicated, messy overhead.

Find docs and screencasts here: [http://formr.github.io](http://formr.github.io)

If you find Formr useful, please consider starring the project and/or making a [donation](https://paypal.me/timgavin). Thank you!

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
Simply enter your form fields as a comma delimited string and Formr will build the form, complete with opening and closing tags, a submit button, and email validation - plus all values retained upon `POST`. Easy!

```php
$form = new Formr('bootstrap');
echo $form->create_form('Name, Email, Comments|textarea');
```

### Produces the following HTML

```html
<form action="/index.php" method="post" accept-charset="utf-8">

    <div id="_name" class="form-group">
        <label class="control-label" for="name">
            Name
        </label>
        <input type="text" name="name" id="name" class="form-control">
    </div>

    <div id="_email" class="form-group">
        <label class="control-label" for="email">
            Email
        </label>
        <input type="email" name="email" id="email" class="form-control">
    </div>

    <div id="_comments" class="form-group">
        <label class="control-label" for="comments">
            Comments
        </label>
        <textarea name="comments" id="comments" class="form-control"></textarea>
    </div>

    <div id="_button" class="form-group">
        <label class="sr-only" for="button"></label>
        <button type="submit" name="button" id="button" class="btn btn-primary">Submit</button>
    </div>

</form>
```

## Basic Example with More Control

Using the `create()` method tells Formr you want control over adding the form tags and submit button yourself. Otherwise it's the same as the Basic Example above.

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
            Age
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

Formr has several common forms already baked in, and it's really easy to create and save your own.

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
    'text' => 'name, Name:',
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
        <div id="_name" class="form-group">
            <label class="control-label" for="name">
                Name:
            </label>
            <input type="text" name="name" class="form-control" id="name">
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

## Build Forms in HTML

You have full control over how you build your forms...

```html
<div class="my-wrapper-class">
    <?php echo $form->input_text('name', 'Name'); ?>
</div>

<div class="my-wrapper-class">
    <?php echo $form->input_email('email', 'Email address', 'john@example.com', 'emailID', 'placeholder="email@domain.com"'); ?>
</div>
```

#### Produces the following HTML

```html
<div class="my-wrapper-class">
    <label for="name">
        Name
    </label>
    <input type="text" name="name" id="name">
</div>

<div class="my-wrapper-class">
    <label for="emailID">
        Email address
    </label>
    <input type="email" name="email" id="emailID" value="john@example.com" placeholder="email@domain.com">
</div>
```

## Retrieving POST Values

It's super easy to retrieve your `POST` values and assign them to variables!

```php
$name = $form->post('name');
$email = $form->post('email');
```

## Validation

#### Formr can easly process and validate your forms

Like the `create()` method, we can pass a list of our form labels to the `validate()` method, which will get the `POST` values of our form fields and put them into an array. If your field name is `email`, a `valid_email` validation rule will be applied automatically!

#### Basic usage

```php
$form->validate('Name, Email, Comments');
```

Let's make sure the form was submitted, then we'll validate and get the value of our email field from the array.

```php
if($form->submitted()) {
    $data = $form->validate('Name, Email, Comments');
    $email = $data['email'];
}
```

#### Adding Rules

Let's make sure the `Name` field is a minimum of 3 characters and a maximum of 30 by adding our validation rules wrapped in parentheses.

```php
$form->validate('Name(min_length[3]|max_length[30]), Email, Comments');
```

## Fine-Tune Your Validation

Of course you can get more in-depth with your validation, and even add custom error messaging! The following is a basic example, however Formr's validation methods are quite powerful and include, among other things, comparing values between fields and hashing using `bcrypt()`

Let's get the value of our `email` field using the `post()` method.

```php
$email = $form->post('email');
```

Now let's make sure it's a valid email address by entering the `valid_email` validation rule in the third parameter. If there's an error, the text entered into the second parameter will notify the user to correct the `Email` field.

```php
$email = $form->post('email','Email','valid_email');
```

We can take that a step further and enter a full custom error message in the second parameter to make our forms even more user-friendly.

```php
$email = $form->post('email','Email|Please enter a valid email address','valid_email');
```

## Full Example

```php
<?php
// include the Formr class
require_once 'Formr/class.formr.php';

// create our form object and use Bootstrap 4 as our form wrapper
$form = new Formr('bootstrap');

// make all fields required
$form->required = '*';

// check if the form has been submitted
if($form->submitted())
{
    // make sure our Message field has at least 10 characters
    $form->validate('Message(min_length[10])');

    // let's email the form
    $to = 'me@domain.com';
    $from = 'donotreply@domain.com';
    $subject = 'Contact Form Submission';

    // this processes our form, cleans the input, and formats it into an HTML email
    if($form->send_email($to, $subject, 'POST', $from, 'HTML'))
    {
        // email sent; print a thank you message
        $form->success_message('Thank you for filling out our form!');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Formr</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <?php
            // print messages, formatted using Bootstrap alerts
            echo $form->messages();

            // create the form
            echo $form->create_form('First name, Last name, Email address, Message|textarea');
        ?>
    </div>
</body>
</html>
```
