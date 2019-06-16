# Formr

Formr is a PHP micro-framework which installs easily and helps you build, layout and validate forms quickly, painlessly, and without all the complicated, messy overhead.

Find docs and screencasts here: [http://formr.github.io](http://formr.github.io)

## Features

- Create complex forms with server-side processing and validation in only minutes
- Instantly make one field required, all fields required, or all but one field required
- Built-in `POST` validation rules, including validating email, comparisons, slugging and hashing
- Bootstrap ready; automatically wrap all of your form elements and messages in Bootstrap classes
- Automatically build and format `label` tags, saving lots of time
- Extensible: roll your own form &amp; validation sets and dropdown menus and share 'em with others
- Create and validate radio groups and checkbox arrays in seconds
- Automatically wrap field elements in `p`, `div`, `ul`, `ol`, `dl`, Bootstrap's `.form-control` or roll your own
- Object-oriented; supports multiple forms per page
- Little helpers to assist in building, layout, testing and debugging
- And a ton of other cool stuff!

## Basic Example

Simply enter your form fields as a comma delimited string and Formr will build the form, complete with email validation and all values retained upon POST.

```php
echo $form->form_open();
echo $form->create('First name, Last name, Email address');
echo $form->input_submit();
echo $form->form_close();
```

### Produces the following HTML

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
    <div id="_submit" class="form-group">
        <label class="sr-only" for="submit"></label>
        <input type="submit" name="submit" value="Submit" class="btn" id="submit">
    </div>
</form>
```