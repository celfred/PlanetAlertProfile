<?php
namespace ProcessWire;

$sent = false;
$error = '';

$emailTo = $users->get("name=admin")->email;
if ($user->hasRole('player')) {
  $headTeacher = getHeadTeacher($user);
  if ($headTeacher && $headTeacher->email != '') {
    $emailTo = $headTeacher->email;
  }
}


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
      if(empty($value)) $error = "<p class='error'>".__("An error occurred.<br />Please check that all fields have been completed.")."</p>";
    }
  }

  // if no errors, email the form results
  if(!$error) {
      $msg = "Full name: $form[fullname] ($form[playerClass])\n" . 
             "Email: $form[email]\n" . 
             "Comments: $form[comments]"; 

      mail($emailTo, "Contact Form", $msg, "From: $form[email]");

      // populate body with success message, or pull it from another PW field
      $page->body = '<h2 class="text-center">'.__("Thank you! Your message has been sent.").'</h2>'; 
      $sent = true;
  }
}

if(!$sent) {
  // sanitize values for placement in markup
  foreach($form as $key => $value) {
    $form[$key] = htmlentities($value, ENT_QUOTES, "UTF-8"); 
  }
}

include ('./head.inc');

echo $error;

echo $page->body;

if (!$sent) {
  $out = '';
  $out .= '<form role="form" class="form-horizontal" action="./" method="post">';
  $out .= '<div class="form-group">';
  $out .= '<label for="fullname" class="col-sm-2 control-label">'.__("First AND last name").' *</label>';
  $out .= '<div class="col-sm-8">';
  $out .= '<input type="text" class="form-control" id="fullname" name="fullname" value="'.$form['fullname'].'" placeholder="'.__("First AND last name").'" />';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="form-group">';
  $out .= '<label for="playerClass" class="col-sm-2 control-label">'.__("Class").' *</label>';
  $out .= '<div class="col-sm-8">';
  $out .= '<input type="text" class="form-control" id="playerClass" name="playerClass" value="'.$form['playerClass'].'" placeholder="'.__("Class").'" />';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="form-group">';
  $out .= '<label for="email" class="col-sm-2 control-label">'.__("Your email address (so your teacher can reply)").'</label>';
  $out .= '<div class="col-sm-8">';
  $out .= '<input type="email" class="form-control" name="email" id="email" value="'.$form['email'].'" placeholder="'.__("Your email address").'" />';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="form-group">';
  $out .= '<label for="comments" class="col-sm-2 control-label">'.__("Your message").' *</label>';
  $out .= '<div class="col-sm-8">';
  $out .= '<textarea id="comments" class="form-control" name="comments" rows="6">'.$form['comments'].'</textarea>';
  $out .= '<p><em>'.__("Please watch out your spelling and DO NOT use SMS syntax ;)").'</em></p>';
  $out .= '<p>'.__("(* Fields MUST be filled !)").'</p>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="col-sm-offset-2 col-sm-8">';
  $out .= '<input type="submit" name="submit" class="btn btn-block btn-primary" value="'.__("Send message").'" />';
  $out .= '</div>';
  $out .= '</form>';
  echo $out;
}

include ('./foot.inc');
?>
