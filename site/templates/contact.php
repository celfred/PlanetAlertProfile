<?php

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
      if(empty($value)) $error = "<p class='error'>Une erreur est survenue. <br />Merci de vérifier que tous les champs ont été complétés.</p>";
  }

  // if no errors, email the form results
  if(!$error) {
      $msg = "Full name: $form[fullname] ($form[playerClass]\n" . 
             "Email: $form[email]\n" . 
             "Comments: $form[comments]"; 

      mail($emailTo, "Contact Form", $msg, "From: $form[email]");

      // populate body with success message, or pull it from another PW field
      $page->body = "<h2>Merci ! Votre message a bien été envoyé.</h2>"; 
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
    <form role="form" class="form-horizontal" action="contact/" method="post">
      <div class="form-group">
        <label for="fullname" class="col-sm-2 control-label">Prénom et Nom</label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="fullname" name="fullname" value="$form[fullname]" placeholder="Prénom et Nom" />
        </div>
      </div>
      <div class="form-group">
        <label for="playerClass" class="col-sm-2 control-label">Classe</label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="playerClass" name="playerClass" value="$form[playerClass]" placeholder="Classe" />
        </div>
      </div>
      <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Votre adresse email</label>
        <div class="col-sm-8">
          <input type="email" class="form-control" name="email" id="email" value="$form[email]" placeholder="Adresse email" />
        </div>
      </div>
      <div class="form-group">
        <label for="comments" class="col-sm-2 control-label">Votre Message</label>
        <div class="col-sm-8">
          <textarea id="comments" class="form-control" name="comments" rows="6">$form[comments]</textarea>
          <span class="help-box"><em>Merci de faire attention à l'orthographe et de ne pas utiliser de langage SMS ;-)</em><br />Tout message rédigé <strong>sans aucune erreur</strong> (majuscule, ponctuation...) rapportera <strong>+1XP à son auteur</strong></span>
        </div>
      </div>
      <div class="col-sm-offset-2 col-sm-8">
        <input type="submit" name="submit" class="btn btn-block btn-primary" value="Envoyer" />
      </div>
    </form>

_OUT;

}

include ('./head.inc');

echo $page->body;

include ('./foot.inc');
?>
