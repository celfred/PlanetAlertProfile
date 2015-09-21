<?php
  $out = '';

  // Get data content
  $line = explode("\n", $page->exData);
  foreach ($line as $l) {
    $words[] = explode(',', $l);
  }

  // TODO : Get exercise step from url?
  // Adapt form action
  //$step = $input->urlsegment...?
  
  $out .= '<h1 class="row well text-center"><span class="label label-default">Monster fight</span>';
  // Get user info
  if ($user->isSuperuser()) {
    $player->title = 'ADMIN';
  } else {
    $player = $pages->get("template=player, login=$user->name");
  }
  $out .= '</h1>';

  // Scoring table
  $out .= '<div class="row text-center">';
  // Monster's health points
  $out .= '<div class="col-sm-4">';
  $out .= '<div class="progress progress-lg" data-toggle="tooltip" title="Health points">';
  $out .= '<div class="progress-bar progress-bar-striped progress-bar-danger active" role="progressbar" style="width:100%">';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="col-sm-2">';
  $out .= '<img class="pull-left" src="'.$page->image->url.'" alt="Picture" />';
  $out .= '</div>';
  // Player's health points
  $out .= '<div class="col-sm-2">';
  $out .= '<img class="pull-right" src="'.$player->avatar->getThumb("thumbnail").'" alt="Picture" />';
  $out .= '</div>';
  $out .= '<div class="col-sm-4">';
  $out .= '<div class="progress progress-lg" data-toggle="tooltip" title="Health points">';
  $out .= '<div class="progress-bar progress-bar-striped progress-bar-danger active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="col-sm-12">';
  $out .= '<h2 class="">'.$page->title;
  $out .= ' VS ';
  $out .= $player->title.'</h2>';
  $out .= '</div>';
  $out .= '</div>';

  // First step : Display exercise summary to prepare the activity
  $out .= '<h3 class="alert alert-info alert-dismissible" role="alert">'.$page->summary.'<br />';
  $out .= '<strong>'.$page->type->summary.'</strong><br /><br />';
  $out .= '<p class="text-center"><button class="btn btn-default btn-lg btn-block" id="startFight">I understand. Start the fight ! </button></p>';
  $out .= '</h3>';

  // TODO : Detect tab blur -> alert message -> loose battle
  
  // Display a random word
  $randL = array_rand($words, 1);
  $randW = array_rand($words[$randL], 1);
  $question = trim($words[$randL][$randW], ' ');
  $randW == 0 ? $con = 1 : $con = 0;
  $correction = $words[$randL][$con];
  $out .= '<form role="form" class="form-horizontal" id="fightForm" action="" method="post">';
  $out .= '<div class="form-group form-group-lg">';
  if ($page->image) {
    $out .= '<img src="'.$page->image->url.'" alt="Picture" />';
  }
  $out .= '<label for="answer" class="bubble-left control-label"><h1>'.$question.' ?</h1></label>';
  $out .= '</div>';
  $out .= '<div class="bubble-right col-sm-8">';
  $out .= '<input type="text" class="form-control input-lg" id="answers" name="answer" placeholder="Your answer" />';
  $out .= '<button type="submit" class="btn btn-danger btn-block">Attack!</button>';
  $out .= '</div>';
  if ($player->avatar) {
    $out .= '<img class="" src="'.$player->avatar->url.'" alt="Avatar" />';
  }
  $out .= '<input type="hidden" name="correction" value="'.$correction.'" />';
  $out .= '</div>';
  $out .= '</form>';

  //print_r($word);
  echo $out;
?>
