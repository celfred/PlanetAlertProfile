<?php // Quiz template

include("./head.inc"); 

if ($user->isSuperuser()) {
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

  $allPlayers = $pages->find("template='player',sort='name'");
  echo '<span style="margin: 5px 20px;">';
    echo '<span>Teams : </span>';
    foreach($allTeams as $team) {
      echo "<span class='btn btn-primary'><a class='' href='{$pages->get('/quiz')->url}{$sanitizer->pageName($team->name)}'>{$team->title}</a></span> ";
    }
    echo '<span style="float: right;">';
    // List of players
    echo '<span>Players : </span>';
    echo '<select id="players_list">';
      foreach($allPlayers as $player) {
        echo "<option value='{$pages->get('/quiz')->url}{$sanitizer->pageName($player->team)}/{$sanitizer->pageName($player->id)}'>{$player->title} ({$player->team->title})</a></option>";
      }
    echo '</select>';
    echo '<button id="playerQuizButton">Generate</button>';
  echo '</span>';

  $reportTitle = '';
  $out = '';

  if ($input->urlSegment1 && $input->urlSegment2 == '') { // Team quiz (draw random player)
    $team = $input->urlSegment1;
    //$allPlayers = $pages->find("team=$team, template=player");
    // Find players having at least 1 free place
    $allConcerned = $pages->find("team=$team, template=player, places.count>0")->shuffle();

    // Get minimum number of invasions in the team (to find who is the next invaded player)
    foreach($allConcerned as $player) {
      //$nbInvasions[$player->id] = $player->count("parent=history, task.name='right-invasion|wrong-invasion'");
      $nbInvasions[$player->id] = $player->count("parent=history, task.name='right-invasion|wrong-invasion'");
      $player->nbInvasions = $nbInvasions[$player->id];
    }
    // Find minimum nb of invasions
    $min = min($nbInvasions);
    $allMinConcerned = $allConcerned->find("nbInvasions=$min");
    // Pick 1 random player from $allMinConcerned
    $player = $allMinConcerned->getRandom();
    $selectedTeam = $player->team->title;
    $quiz = pick_question($player);
  } else if ($input->urlSegment2 != '') { // 1 player quiz
    $playerId = $input->urlSegment2;
    $player = $pages->get($playerId);
    $selectedTeam = $player->team->title;
    $quiz = pick_question($player);
  }
  
  if ($selectedTeam) {
    // Prepare display
    $out .= '<div class="well quiz">';
      $logo = $homepage->photo->eq(0)->getThumb('thumbnail');
      $out .= '<img class="monster" src="'.$logo.'" />';
      $out .= '<img class="avatar" src="'.$player->avatar->url.'" />';
      $out .= '<h1 class="playerName">'.$player->title.'</h1>';
      $out .= '<h3>Monster invasion ! Team '.$selectedTeam.' has to react!</h3>';
      $out .= '<form id="quizForm" name="quizForm" action="'.$page->url.$input->urlSegment1.'" method="post" role="form">';
        /*
        $out .= '<p class="text-center lead">';
        $out .= $player->title;
        $out .= '</p>';
        */
        $out .= '<h2 class="alert alert-danger text-center">';
        $out .= $quiz['question'].'&nbsp;&nbsp;';
        $out .= '<a id="showAnswer" class="label label-info">[Check answer]</a>';
        $out .= '</h2>';
        $out .= '<h2 id="answer" class="lead text-center">';
        $out .= $quiz['answer'];
        $out .= '</h2>';
        $out .= '<input type="hidden" name="playerId" value="'.$player->id.'" />';
        $out .= '<input type="hidden" name="question" value="'.$sanitizer->text($quiz['question']).'" />';
        $out .= '<input type="hidden" name="answer" value="'.$sanitizer->text($quiz['answer']).'" />';
        $out .= '<p class="text-center">';
        $out .= '<label for="lastQuestion" class=""><input type="checkbox" id="lastQuestion" name="lastQuestion" value="lastQuestion" /> Last question</label>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-success" type="submit" name="RightButton" value="right"><span class="glyphicon glyphicon-ok"></span> Right</button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-danger" type="submit" name="WrongButton" value="wrong"><span class="glyphicon glyphicon-remove"></span> Wrong</button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<a class="btn btn-info" href="'.$page->url.$input->urlSegment1.'">Pass player</a>';
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
