<?php // Quiz template

include("./head.inc"); 

$out = '';
if ($user->isSuperuser()) {
  // Nav tabs
  include("./tabList.inc"); 
  
  if ($input->post->quizFormSubmit) {
    $quizzing = true;
  } else {
    $quizzing = false;
  }

  if ($input->post->RightButton || $input->post->WrongButton) { // Quiz form submitted
    $player = $pages->get($input->post->playerId);
    $player->of(false);

    if ($input->post->RightButton) { // Correct answer
      $task = $pages->get("name=right-invasion");
    } else if ($input->post->WrongButton) { // Wrong answer
      $task = $pages->get("name=wrong-invasion");
    }
   
    // Update player's scores
    $taskComment = $input->post->question.' ['.$input->post->answer.']';
    $refPage = $pages->get($input->post->quizId);
    updateScore($player, $task, $taskComment, $refPage, '', true);
    checkDeath($player, true);
  }

  $selectedTeam = $input->urlSegment1;
  $selectedIds = $input->post->selected; // Checked players
  $allPlayers = $pages->find("template='player', playerTeam=$selectedTeam, sort='name'");
  $allConcerned = $allPlayers->find("places.count>=3"); // Find players having at least 3 places

  if ( count($selectedIds) > 0 ) { // Players have been checked
    // Pick one
    shuffle($selectedIds);
    $selectedPlayer = $selectedIds[0];
    // Get rid of it
    array_splice($selectedIds, 0, 1);
    $display = 'hidden'; // Hide players list
  } else {
    $display = 'shown'; // Show players list
  }

  // Set nbInvasion foreach players
  foreach($allConcerned as $p) {
    $p->nbInvasions = $p->find("template=event, task.name=right-invasion|wrong-invasion")->count();
    if (in_array($p, $selectedIds)) { // Keep checked players
      $p->checked = "checked='checked'";
    } else {
      if ( $quizzing == true ) { // Quiz has already started
        $p->checked = '';
      } else { // First load, check all concerned players
        $p->checked = "checked='checked'";
      }
    }
  }

  if ($selectedTeam) {
    $out .= '<form id="quizForm" name="quizForm" action="'.$page->url.$input->urlSegment1.'" method="post" role="form">';
    // A player is selected : Quiz display
    if ($selectedPlayer) {
      $player = $pages->get($selectedPlayer);
      $quiz = pick_question($player);
      $out .= '<div class="well quiz">';
        $logo = $homepage->photo->eq(0)->getThumb('thumbnail');
        $out .= '<img class="monster" src="'.$logo.'" />';
        $out .= '<img class="avatar" src="'.$player->avatar->url.'" />';
        $out .= '<h1 class="playerName">'.$player->title.'</h1>';
        $out .= '<h3>Monster invasion ! Team '.$player->playerTeam.' has to react!</h3>';
        $out .= '<h2 class="alert alert-danger text-center">';
        $out .= $quiz['question'].'&nbsp;&nbsp;';
        $out .= '</h2>';
        // Display map if necessary
        if ( $quiz['type'] === 'map' ) {
          $out .= '<section class="">';
          $out .= '<object id="worldMap" type="image/svg+xml" data="'.$config->urls->templates.'img/worldMap.svg" style="width: 100%; height: 400px; border:1px solid black; ">Your browser does not support SVG</object>';
          $out .= '</section>';
        }
        // Display photo if necessary
        if ( $quiz['type'] === 'photo' ) {
          $out .= '<section class="text-center">';
          $placeId = $quiz['id'];
          $options = array('upscaling'=>false);
          $photo = $pages->get("$placeId")->photo->getRandom()->size(300,300, $options);
            $out .= '<img src="'.$photo->url.'" alt="Photo" />';
          $out .= '</section>';
        }
        $out .= '<a id="showAnswer" class="label label-info lead">[Check answer]</a>';
        $out .= '<h2 id="answer" class="lead text-center">';
        $out .= $quiz['answer'];
        $out .= '</h2>';
        $out .= '<input type="hidden" name="playerId" value="'.$player->id.'" />';
        $out .= '<input type="hidden" name="quizId" value="'.$sanitizer->text($quiz['id']).'" />';
        $out .= '<input type="hidden" name="question" value="'.$sanitizer->text($quiz['question']).'" />';
        $out .= '<input type="hidden" name="answer" value="'.$sanitizer->text($quiz['answer']).'" />';
        $out .= '<p class="text-center">';
        $out .= '<button class="btn btn-success generateQuiz" type="submit" name="RightButton" value="right"><span class="glyphicon glyphicon-ok"></span> Right</button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-danger generateQuiz" type="submit" name="WrongButton" value="wrong"><span class="glyphicon glyphicon-remove"></span> Wrong</button>';
        $out .= '</p>';

      $out .= '</div>';
    }

    // Players list display
    $out .= '<section class="well">';
    $out .= '<button id="toggle" class="btn btn-default">See list</button>';
    $out .= '<div id="quizMenu" class="'.$display.'">';
    $out .= '<p>You need at least 3 free elements to appear in the list.</p>';
    $out .= '<ul class="list-group">';
      foreach($allConcerned as $p) {
          $details = "({$p->nbInvasions} inv. / ";
          $details .= "{$p->places->count()} el.)";
          $out .= "<li class='list-group-item'><label for='ch[{$p->id}]'><input type='checkbox' id='ch[{$p->id}]' name='selected[]' value='{$p->id}' {$p->checked}'> {$p->title} {$details}</label></li>";
      }
      $out .= '<button id="tickAll" class="btn btn-success btn-sm">Tick all</button>';
      $out .= '<button id="untickAll" class="btn btn-danger btn-sm">Untick all</button>';
    $out .= '</ul>';
    $out .= '<p>(Not concerned : ';
      $notConcerned = '';
      foreach($allPlayers as $p) {
        if (!($allConcerned->get($p))) {
          $notConcerned .= $p->title.', ';
        }
      }
      $notConcerned = trim($notConcerned, ', ').')';
      $out .= $notConcerned;
    $out .= '</p>';
    // TODO : Build Ambassadors list
    /* $out .= '<a class="btn btn-info pickAmbassador" data-list="'.$ambassadors.'">Pick an Ambassador</a>'; */
    $out .= '</div>';
    $out .= '</section>';
    $out .= '<input type="hidden" name="quizFormSubmit" value="Save" />';
    $out .= '<button type="submit" name="quizFormSubmitButton" class="btn btn-info btn-block generateQuiz">Generate</button>';
    $out .= '</form>';
    
  }

} else {
  if ($user->isLoggedin()) {
    $player = $pages->get("login=$user->name");

    $quiz = pick_question($player);
    $out .= '<div class="well quiz">';
      $logo = $homepage->photo->eq(0)->getThumb('thumbnail');
      $out .= '<img class="monster" src="'.$logo.'" />';
      $out .= '<h3>Defensive preparation ! <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="This is a simple practice area. Click on \'Check answer\' below to see the solution. Then you can click on \'Next question\'. Stop the session when you\'re tired :)"></span></h3>';
      $out .= '<h2 class="alert alert-danger text-center">';
      $out .= $quiz['question'].'&nbsp;&nbsp;';
      $out .= '</h2>';
      // Display map if necessary
      if ( $quiz['type'] === 'map' ) {
        $out .= '<section class="">';
        $out .= '<object id="worldMap" type="image/svg+xml" data="'.$config->urls->templates.'img/worldMap.svg" style="width: 100%; height: 400px; border:1px solid black; ">Your browser does not support SVG</object>';
        $out .= '</section>';
      }
      // Display photo if necessary
      if ( $quiz['type'] === 'photo' ) {
        $out .= '<section class="text-center">';
        $placeId = $quiz['id'];
        $options = array('upscaling'=>false);
        $photo = $pages->get("$placeId")->photo->getRandom()->size(300,300, $options);
          $out .= '<img src="'.$photo->url.'" alt="Photo" />';
        $out .= '</section>';
      }
      $out .= '<a id="showAnswer" class="label label-info lead">[Check answer]</a>';
      $out .= '<h2 id="answer" class="lead text-center">';
      $out .= $quiz['answer'];
      $out .= ' <a class="btn btn-primary" href="'.$page->url.'">Next question</a>';
      $out .= '</h2>';
    $out .= '</div>';

  } else {
    $out = '<p>You need to be logged in to access this page.</p>';
  }

}

echo $out;

include("./foot.inc"); 

?>
