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
  $selectedPlayer = $input->post->selectedPlayer;
  $allPlayers = $pages->find("template='player', team=$selectedTeam, sort='name'");

  
  $reportTitle = '';
  $out = '';
  $lastQuestion = '';

  if ($input->post->selectedIds) { // Players have been checked
    if ($input->post->selectedIds !== '') {
      $display = 'hidden'; // Hide players list
      $formerSelected = explode(',' ,$input->post->selectedIds);
    }
  } else {
    if ($input->post->selectedPlayer) { // Last selected player
      $formerSelected = -1;
      $lastChecked = "checked = 'checked'";
      $display = 'hidden'; // Hide players list
    }
  }

  // Get minimum number of invasions in the team (to find who is the next invaded player)
  
  // Find players having at least 1 place
  $allConcerned = $allPlayers->find("places.count>0");

  // Set nbInvasion foreach players
  foreach($allConcerned as $player) {
    $nbInvasions[$player->id] = $player->find("template=event, task.name=right-invasion|wrong-invasion")->count();
    $player->nbInvasions = $nbInvasions[$player->id];
  }
  // Find minimum nb of invasions (for pre-checked players at first load)
  $min = min($nbInvasions);
  $allMinConcerned = $allConcerned->find("nbInvasions=$min");
  if (!$formerSelected) { // First load
    foreach($allMinConcerned as $player) {
      // Disabled since quick selection tools have been added
      //$player->checked = "checked='checked'"; 
    }
  } else { // Some players have already been checked
    foreach($allPlayers as $player) {
      if (in_array($player->id, $formerSelected)) {
        $player->checked = "checked='checked'";
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
        $out .= '<h3>Monster invasion ! Team '.$player->team->title.' has to react!</h3>';
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
          $photo = $pages->get("$placeId")->photo->getRandom();
            $out .= '<img src="'.$photo->url.'" alt="Photo" />';
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
        $out .= '<label for="lastQuestion" class=""><input type="checkbox" id="lastQuestion" name="lastQuestion" value="lastQuestion" '.$lastChecked.' /> Last question</label>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-success generateQuiz" type="submit" name="RightButton" value="right"><span class="glyphicon glyphicon-ok"></span> Right</button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-danger generateQuiz" type="submit" name="WrongButton" value="wrong"><span class="glyphicon glyphicon-remove"></span> Wrong</button>';

      $out .= '</div>';
    }

    // Players list display
    $nbPlaces = [];
    $ratio = [];
    $out .= '<div class="well">';
    $out .= '<button id="toggle" class="btn btn-default">See list</button>';
    $out .= '<ul class="list-group '.$display.'">';
      foreach($allPlayers as $player) {
        $nbPlayerPlaces = $player->places->count();
        if (!in_array($nbPlayerPlaces, $nbPlaces, true)) {
          array_push($nbPlaces, $nbPlayerPlaces);
        }
        if ($nbPlayerPlaces === 0) {
          $disabled = "disabled='disabled'";
          $details = "";
          $class = "disabled";
        } else {
          $disabled = "";
          $class = "";
          if ($player->places->count == 1) {
            $details = "({$nbPlayerPlaces} place, ";
            if ($player->nbInvasions == 1) {
              $details .= "{$player->nbInvasions} invasion)";
            } else {
              $details .= "{$player->nbInvasions} invasions)";
            }
          } else {
            $details = "({$nbPlayerPlaces} places, ";
            if ($player->nbInvasions == 1) {
              $details .= "{$player->nbInvasions} invasion)";
            } else {
              $details .= "{$player->nbInvasions} invasions)";
            }
          }
          $playerRatio = $player->nbInvasions-$nbPlayerPlaces;
          $details .= ' ['.$playerRatio.']';
          array_push($ratio, $playerRatio);
        }
        $out .= "<li class='list-group-item'><label class='{$class}' for='ch[{$player->id}]'><input type='checkbox' id='ch[{$player->id}]' value='{$player->id}' {$player->checked} {$disabled} data-nbPlaces='{$nbPlayerPlaces}' data-nbInvasions='{$player->nbInvasions}' data-ratio='{$playerRatio}'> {$player->title} {$details}</label></li>";
      }
    $out .= '<li class="list-group-item"># of Places selection :<br />';
    sort($nbPlaces);
    foreach($nbPlaces as $nb) {
      $out .= '<label class="btn btn-info btn-xs"><input type="checkbox" class="tickNbPlaces" value="'.$nb.'"><span class="">'.$nb.'</span></label>';
    }
    $out .= '</li>';
    $out .= '<li class="list-group-item"># of Invasions selection :<br />';
    $nbInvasions = array_unique($nbInvasions);
    sort($nbInvasions);
    foreach($nbInvasions as $nb) {
      $out .= '<label class="btn btn-info btn-xs"><input type="checkbox" class="tickNbInvasions" value="'.$nb.'"><span class="">'.$nb.'</span></label>';
    }
    $out .= '</li>';
    $out .= '<li class="list-group-item">Ratio selection :<br />';
    $ratio = array_unique($ratio);
    sort($ratio);
    foreach($ratio as $nb) {
      if ($nb !== 0) {
        $out .= '<label class="btn btn-info btn-xs"><input type="checkbox" class="tickRatio" value="'.$nb.'">'.$nb.'</label>';
      } else {
        $out .= '<label class="btn btn-primary btn-xs"><input type="checkbox" class="tickRatio" value="'.$nb.'">'.$nb.'</label>';
      }
    }
    $out .= '</li>';
    $out .= '<li class="list-group-item">';
    $out .= '<button id="tickAll" class="btn btn-success btn-sm">Tick all</button>';
    $out .= '<button id="untickAll" class="btn btn-danger btn-sm">Untick all</button>';
    $out .= '</li>';
    $out .= '</ul>';
    $out .= '<button type="submit" class="btn btn-info btn-block generateQuiz">Generate</button>';
    $out .= '<input type="hidden" id="selectedIds" name="selectedIds" value="'.$input->post->selectedIds.'">';
    $out .= '<input type="hidden" id="selectedPlayer" name="selectedPlayer" value="">';
    $out .= '</form>';
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
