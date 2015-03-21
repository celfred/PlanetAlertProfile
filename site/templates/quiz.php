<?php // Quiz template

include("./head.inc"); 

if ($user->isSuperuser()) {
  // Nav tabs
  include("./tabList.inc"); 

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
    updateScore($player, $task);

    // Save player's new scores
    $player->save();

    // Record history
    saveHistory($player, $task, $taskComment);

    // Check if last question was whecked, then redirect
    if ($input->post->lastQuestion) {
      $session->redirect($homepage->url.'players/'.$input->urlSegment1);
    }
  }

  $selectedTeam = $input->urlSegment1;
  $selectedPlayer = $input->urlSegment2;
  $allPlayers = $pages->find("template='player', team=$selectedTeam, sort='name'");

  $reportTitle = '';
  $out = '';

  // Get minimum number of invasions in the team (to find who is the next invaded player)
  
  // Find players having at least 1 place
  $allConcerned = $allPlayers->find("places.count>0");

  // Set nbInvasion foreach players
  foreach($allConcerned as $player) {
    $nbInvasions[$player->id] = $player->find("template=event, task.name=right-invasion|wrong-invasion")->count();
    $player->nbInvasions = $nbInvasions[$player->id];
  }
  // Find minimum nb of invasions (for pre-checked players)
  $min = min($nbInvasions);
  $allMinConcerned = $allConcerned->find("nbInvasions=$min");
  foreach($allMinConcerned as $player) {
    $player->checked = "checked='checked'";
  }

  if ($selectedTeam) {
    // No selected player : Players list display
    if (!$selectedPlayer) {
      $out .= '<div class="well">';
      $out .= '<ul class="list-group">';
        foreach($allPlayers as $player) {
          if ($player->places->count === 0) {
            $disabled = "disabled='disabled'";
            $details = "";
            $class = "disabled";
          } else {
            $disabled = "";
            $class = "";
            if ($player->places->count == 1) {
              $details = "({$player->places->count} place, ";
              if ($player->nbInvasions == 1) {
                $details .= "{$player->nbInvasions} invasion)";
              } else {
                $details .= "{$player->nbInvasions} invasions)";
              }
            } else {
              $details = "({$player->places->count} places, ";
              if ($player->nbInvasions == 1) {
                $details .= "{$player->nbInvasions} invasion)";
              } else {
                $details .= "{$player->nbInvasions} invasions)";
              }
            }
          }
          $out .= "<li class='list-group-item'><label class='{$class}' for='ch{$player->id}'><input type='checkbox' id='ch{$player->id}' value='{$pages->get('/quiz')->url}{$sanitizer->pageName($player->team)}/{$sanitizer->pageName($player->id)}' {$player->checked} {$disabled}> {$player->title} {$details}</label></li>";
        }
      $out .= '</ul>';
      $out .= '<button id="tickAll" class="btn btn-success">Tick all</button>';
      $out .= '<button id="untickAll" class="btn btn-danger">Untick all</button>';
      $out .= '<button id="generateQuiz" class="btn btn-default btn-block">Generate</button>';
      $out .= '</div>';
    }
    
    // A player is selected : Quiz display
    if ($selectedPlayer) {
      $player = $pages->get($selectedPlayer);
      $quiz = pick_question($player);
      $out .= '<div class="well quiz">';
        $logo = $homepage->photo->eq(0)->getThumb('thumbnail');
        $out .= '<img class="monster" src="'.$logo.'" />';
        $out .= '<img class="avatar" src="'.$player->avatar->url.'" />';
        $out .= '<h1 class="playerName">'.$player->title.'</h1>';
        $out .= '<h3>Monster invasion ! Team '.$player->team->title.' has to react!</h3>';
        $out .= '<form id="quizForm" name="quizForm" action="'.$page->url.$input->urlSegment1.'" method="post" role="form">';
          $out .= '<h2 class="alert alert-danger text-center">';
          $out .= $quiz['question'].'&nbsp;&nbsp;';
          $out .= '</h2>';
          // Display map if necessary
          if ( $quiz['type'] === 'map' ) {
            $out .= '<section class="">';
            $out .= '<object id="worldMap" type="image/svg+xml" data="'.$config->urls->templates.'img/worldMap.svg" style="width: 100%; height: 400px; border:1px solid black; ">Your browser does not support SVG</object>';
            $out .= '</section>';
          }
          $out .= '<a id="showAnswer" class="label label-info lead">[Check answer]</a>';
          $out .= '<h2 id="answer" class="lead text-center">';
          $out .= $quiz['answer'];
          $out .= '</h2>';
          $out .= '<input type="hidden" name="playerId" value="'.$player->id.'" />';
          $out .= '<input type="hidden" name="question" value="'.$sanitizer->text($quiz['question']).'" />';
          $out .= '<input type="hidden" name="answer" value="'.$sanitizer->text($quiz['answer']).'" />';
          $out .= '<p class="text-center">';
          //$out .= '<label for="lastQuestion" class=""><input type="checkbox" id="lastQuestion" name="lastQuestion" value="lastQuestion" /> Last question</label>';
          //$out .= '&nbsp;&nbsp;';
          $out .= '<button class="btn btn-success" type="submit" name="RightButton" value="right"><span class="glyphicon glyphicon-ok"></span> Right</button>';
          $out .= '&nbsp;&nbsp;';
          $out .= '<button class="btn btn-danger" type="submit" name="WrongButton" value="wrong"><span class="glyphicon glyphicon-remove"></span> Wrong</button>';
          $out .= '&nbsp;&nbsp;';
          $out .= '<a class="btn btn-info" href="'.$page->url.$input->urlSegment1.'">Pass player</a>';
        $out .= '</form>';
      }

    $out .= '</div>';
  } else {
    $out .= '<p class="text-center lead well">Select a team and prepare for a... Monster invasion!</p>';
  }

} else {
  $out = '<p>Admin only ;)</p>';
}

echo $out;

include("./foot.inc"); 

?>
