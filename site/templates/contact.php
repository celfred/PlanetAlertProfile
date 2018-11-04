<?php namespace ProcessWire;
  if (!$config->ajax) {
    include("./head.inc");
  }
  $out = '';
  $error = '';
  $feedback = false;
  $numSent= false;
  $emailTo = $users->get("name=admin")->email;
  if ($user->hasRole('player')) {
    $player = $pages->get("login=$user->name");
    $headTeacher = getHeadTeacher($user);
    if ($headTeacher && $headTeacher->email != '') {
      $emailTo = $headTeacher->email;
    }
  }

  if($input->post->submit) { // Form was submitted
    $form = array( // Sanitize form values or create empty
      'fullname' => $sanitizer->text($input->post->fullname),
      'playerClass' => $sanitizer->text($input->post->playerClass),
      'email' => $sanitizer->email($input->post->email),
      'subject' => $sanitizer->text($input->post->subject),
      'message' => $sanitizer->textarea($input->post->message)
    ); 
    // Form treatment
    $antispam_code =  $sanitizer->int($input->post->antispam_code);
    if ($user->isLoggedin() || ($session->get('antispam_code') == $antispam_code)) {
      $session->remove('antispam_code');
      foreach($form as $key => $value) { // Determine if any fields were ommitted or didn't validate
        if ($key != 'email' && $key != 'playerClass') {
          if(empty($value)) $error = '<h2 class="text-center"><span class="label label-danger">'.__("An error occurred. Please check that all fields marked with * have been completed.").'</span></h2>';
        }
        if ($key == 'subject') { // Adapt subject
          switch($value) {
          case 'extra-hk':
            $subject = __("Extra-homework");
            break;
          case 'question':
            $subject = __("Question");
            break;
          case 'bug':
            $subject = __("Bug");
            break;
          case 'idea':
            $subject = __("Idea");
            break;
          case 'contact':
            $subject = __("Guest contact");
            break;
          default :
            $subject = __("Other");
          }
          $subject .= ' ['.$form['fullname'];
          if ($form['playerClass'] != '') {
            $subject .= ' - '.$form['playerClass'];
          }
          $subject .= ']';
        }
      }
    } else {
      $error = '<h2 class="text-center"><span class="label label-danger">'.__("An error occurred. Please check that all fields have been completed.").'</span></h2>';
    }

    if($error == '') { // No errors, email the form results
      $mail = wireMail();
      $mail->to($emailTo, 'Planet Alert');
      $mail->from("flenglish@tuxfamily.org");
      $mail->fromName($form['fullname']);
      $mail->subject($subject);
      $mail->body($form['message']);
      $numSent = $mail->send();
      if ($numSent == 1) {
        $feedback = '<h2 class="text-center">'.__("Thank you! Your message has been sent.").'</h2>'; 
      } else {
        $numSent = false;
        $feedback = '<h2 class="text-center"><span class="label label-danger">'.__("An error occured ! Your message may have not been sent.").'</span></h2>';
      }
      foreach($form as $key => $value) {
        $form[$key] = htmlentities($value, ENT_QUOTES, "UTF-8"); 
      }
    }
  } else {
    $form = array( // Init values
      'fullname' => '',
      'playerClass' => '',
      'email' => '',
      'subject' => '',
      'message' => '',
    ); 
    // Basic anti-spam (for guests)
    $sendFormInteger = mt_rand(1000,9999);
    $session->set('antispam_code', $sendFormInteger);
  }

  if ($error != ''|| $feedback != false) {
    $out = $error;
    $out .= $feedback;
  }
  if (!$input->post->submit || $error != '') { // Form was not submitted or failed
    $out .= '<section class="row text-center">';
    if ($user->isGuest() || $user->hasRole('teacher')) {
      $out .= '<h1 class="text-center">'.__("Contact Planet Alert admin !").'</h1>';
    } else {
      $out .= '<h1 class="text-center">'.__("Contact your teacher !").'</h1>';
    }
    $out .= '<p><em>'.__("Please watch out your spelling and DO NOT use SMS syntax ;)").'</em></p>';
    $out .= '<p>'.__("(* Fields MUST be filled !)").'</p>';
    $out .= '</section>';
    $out .= '<form id="contactForm" role="form" class="form-horizontal" action="'.$page->url.'" method="post">';
      if ($user->hasRole('player')) {
        $out .= '<div class="form-group">';
          $out .= '<label for="subject" class="col-sm-2 control-label">'.__("Reason for your message").' *</label>';
          $out .= '<div class="col-sm-8">';
          $out .= '<select class="form-control" id="subject" name="subject">';
          $out .= '<option value="extra-hk">'.__("I did an extra-training !").'</option>';
          $out .= '<option value="question">'.__("I have a question.").'</option>';
          $out .= '<option value="bug">'.__("I've found a bug !").'</option>';
          $out .= '<option value="idea">'.__("I have an idea for the class !").'</option>';
          $out .= '<option value="other">'.__("I have something to tell you.").'</option>';
          $out .= '</select>';
          $out .= '</div>';
        $out .= '</div>';
        $out .= '<input type="hidden" id="fullname" name="fullname" value="'.$player->title.' '.$player->lastName.'" />';
        $out .= '<input type="hidden" id="playerClass" name="playerClass" value="'.$player->team->title.'" />';
      } else {
        $out .= '<input type="hidden" id="subject" name="subject" value="contact" />';
        $out .= '<div class="form-group">';
          $out .= '<label for="fullname" class="col-sm-2 control-label">'.__("First AND last name").' *</label>';
          $out .= '<div class="col-sm-8">';
          $out .= '<input type="text" class="form-control" id="fullname" name="fullname" value="'.$form['fullname'].'" placeholder="'.__("First AND last name").'" required="required" />';
          $out .= '</div>';
        $out .= '</div>';
        if (!$user->hasRole('teacher')) {
          $out .= '<div class="form-group">';
            $out .= '<label for="playerClass" class="col-sm-2 control-label">'.__("Class").'</label>';
            $out .= '<div class="col-sm-8">';
            $out .= '<input type="text" class="form-control" id="playerClass" name="playerClass" value="'.$form['playerClass'].'" placeholder="'.__("Class").'" />';
            $out .= '</div>';
          $out .= '</div>';
        }
        $out .= '<div class="form-group">';
          $out .= '<label for="email" class="col-sm-2 control-label">'.__("Your email address (if you expect a reply)").'</label>';
          $out .= '<div class="col-sm-8">';
          $out .= '<input type="email" class="form-control" name="email" id="email" value="'.$form['email'].'" placeholder="'.__("Your email address").'" />';
          $out .= '</div>';
        $out .= '</div>';
      }
      $out .= '<div class="form-group">';
        $out .= '<label for="message" class="col-sm-2 control-label">'.__("Your message").' *</label>';
        $out .= '<div class="col-sm-8">';
        $out .= '<textarea id="message" class="form-control" name="message" rows="13" required="required">'.$form['message'].'</textarea>';
        $out .= '</div>';
      $out .= '</div>';
      if ($user->isGuest()) {
        $out .= '<div class="form-group">';
          $out .= '<label for="antispam_code" class="col-sm-2 control-label">Anti spam code* → <strong>'.$session->get('antispam_code').'</strong></label>';
          $out .= '<div class="col-sm-8">';
          $out .= '<input type="text" name="antispam_code" value="" class="form-control" placeholder="'.__('Copy anti-spam code here').'" required="required" />';
          $out .= '</div>';
        $out .= '</div>';
      }
      $out .= '<div class="col-sm-offset-2 col-sm-8">';
        $out .= '<input id="contactFormSubmit" type="submit" name="submit" class="btn btn-block btn-primary" value="'.__("Send message").'" />';
      $out .= '</div>';
    $out .= '</form>';
  }

  echo $out;

  if (!$config->ajax) {
    include("./foot.inc");
  }
?>
