<?php
namespace ProcessWire;

$sent = false;
$error = '';
$emailTo = 'planetalert@tuxfamily.org';

// sanitize form values or create empty
$form = array(
  'fullname' => $sanitizer->text($input->post->fullname),
  'playerClass' => $sanitizer->text($input->post->playerClass),
  'email' => $sanitizer->email($input->post->email),
  'comments' => $sanitizer->textarea($input->post->comments),
); 

// check if the form was submitted
if($input->post->submit) {
	
  // determine if any fields were ommitted or didn't validate
  foreach($form as $key => $value) {
    if ($value != 'email') {
      if(empty($value)) $error = "<p class='error'>An error occurred.<br />Please check that all fields have been completed.</p>";
    }
  }

  // if no errors, email the form results
  if(!$error) {
      $msg = "Full name: $form[fullname] ($form[playerClass])\n" . 
             "Email: $form[email]\n" . 
             "Comments: $form[comments]"; 

      mail($emailTo, "Contact Form", $msg, "From: $form[email]");

      // populate body with success message, or pull it from another PW field
      $page->body = "<h2>Thank you! Your message has been sent.</h2>"; 
      $sent = true;	
  }
}

if(!$sent) {

  // sanitize values for placement in markup
  foreach($form as $key => $value) {
      $form[$key] = htmlentities($value, ENT_QUOTES, "UTF-8"); 
  }

  // append form to body copy
  $page->body .= <<< _OUT

    $error
    <form role="form" class="form-horizontal" action="./" method="post">
      <div class="form-group">
        <label for="fullname" class="col-sm-2 control-label">First AND last name *</label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="fullname" name="fullname" value="$form[fullname]" placeholder="First AND last name" />
        </div>
      </div>
      <div class="form-group">
        <label for="playerClass" class="col-sm-2 control-label">Class *</label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="playerClass" name="playerClass" value="$form[playerClass]" placeholder="Class" />
        </div>
      </div>
      <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Your email adress</label>
        <div class="col-sm-8">
          <input type="email" class="form-control" name="email" id="email" value="$form[email]" placeholder="Your email adress" />
        </div>
      </div>
      <div class="form-group">
        <label for="comments" class="col-sm-2 control-label">Your message *</label>
        <div class="col-sm-8">
          <textarea id="comments" class="form-control" name="comments" rows="6">$form[comments]</textarea>
          <p><em>Please watch out your spelling and DO NOT use SMS syntax ;)</em></p>
          <p>(* Fields MUST be filled !)</p>
        </div>
      </div>
      <div class="col-sm-offset-2 col-sm-8">
        <input type="submit" name="submit" class="btn btn-block btn-primary" value="Send message" />
      </div>
    </form>

_OUT;

}

include ('./head.inc');

echo $page->body;

include ('./foot.inc');
?>
