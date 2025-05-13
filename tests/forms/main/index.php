<?php
// include Formr
require_once '../../../vendor/autoload.php';

// create our form object and use Bulma as our form wrapper
$form = new Formr\Formr('bulma');

// make all fields required
$form->required = '*';

// check if the form has been submitted
if ($form->submitted())
{
    // get our form values and assign them to a variable
    $data = $form->validate('Name, Email, Comments');

    // show a success message if no errors
    if($form->ok()) {
        $form->success_message = "Thank you, {$data['name']}!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
<body class="container">
    <?php $form->messages(); ?>
    <?php $form->create_form('Name, Email, Comments|textarea'); ?>
</body>
</html>