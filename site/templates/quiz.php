<?php namespace ProcessWire; // Quiz template

include("./head.inc"); 

if ($user->hasRole('teacher') || $user->isSuperuser()) {
  // Nav tabs
  $team = $pages->get("template=team, name=$input->urlSegment1");;
  include("./tabList.inc"); 
  
  $out = '';

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
    $task->comment = $input->post->question.' ['.$input->post->answer.']';
    $task->refPage = $pages->get($input->post->quizId);
    $task->linkedId = false;
    updateScore($player, $task, true);
    checkDeath($player, true);

    // Redirect if last question
    if ($input->post->lastQuestion) {
      $session->redirect($pages->get('/players')->url.$player->team->name);
    }
  }

  $selectedIds = $input->post->selected; // Checked players
  $rank = $team->rank->index;
  if ($rank >= 8) {
    $allPlayers = $allPlayers->find("team=$team"); // Limit to team players
    $allConcerned = new pageArray();
    $notConcerned = new pageArray();
    foreach($allPlayers as $p) { // Find players having at least 3 free elements
      $nbEl = $p->places->count()+$p->people->count();
      if ($nbEl >= 3) {
        $allConcerned->add($p);
      } else {
        $notConcerned->add($p);
      }
    }
    $notConcerned = $notConcerned->implode(', ', '{title}');
  } else {
    $allConcerned = $pages->find("parent.name=players, team=$team, places.count>=3"); // Find players having at least 3 places
    $notConcerned = $pages->find("parent.name=players, team=$team, places.count<3")->implode(', ', '{title}');
  }
  $ambassadors = $pages->find("parent.name=players, team=$team, skills.name=ambassador");
  if ($ambassadors->count() == 0 ) { 
    $ambassadorsNames = __('Nobody.');
    $ambassadorsButton = '';
  } else {
    $ambassadorsNames = $ambassadors->implode(', ', '{title}');
    $ambassadorsIds = $ambassadors->implode(', ', '{id}');
    $ambassadorsButton = ' <a class="btn btn-info btn-sm pickFromList" data-list="'.$ambassadorsIds.'">'.__('Pick a player').'</a>';
  }

  $out .= '<div id="ajaxDecision" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="ambassador"></div>';

  if ($input->post->reloadButton) {
    $selectedPlayer = $input->post->playerId;
    $display = 'hidden'; // Hide players list
  } else {
    if (isset($selectedIds) && count($selectedIds) > 0) { // Players have been checked
      // Shuffle, pick one and get rid of it
      shuffle($selectedIds);
      $selectedPlayer = $selectedIds[0];
      // Get rid of it
      array_splice($selectedIds, 0, 1);
      $display = 'hidden'; // Hide players list
    } else {
      $display = 'shown'; // Show players list on first load
    }
  }

  // Set nbInvasion foreach players
  $allConcerned->sort("name");
  foreach($allConcerned as $p) { // Limited to current schoolyear
    $p->nbInvasions = $p->find("parent.name=history, template=event, task.name=right-invasion|wrong-invasion")->count();
    if ($selectedIds && in_array($p, $selectedIds)) { // Keep checked players
      $p->checked = "checked='checked'";
    } else {
      if ( $quizzing == true ) { // Quiz has already started
        $p->checked = '';
      } else { // First load, check all concerned players
        $p->checked = "checked='checked'";
      }
    }
  }

  if (isset($team)) {
    $out .= '<form id="quizForm" name="quizForm" action="'.$page->url.$input->urlSegment1.'" method="post" role="form">';
    // A player is selected : Quiz display
    if (isset($selectedPlayer)) {
      $player = $pages->get($selectedPlayer);
      $quiz = pick_question($player);
      $out .= '<div class="well quiz">';
        $logo = $homepage->photo->eq(0)->getCrop('thumbnail');
        $out .= '<img class="monster" src="'.$logo->url.'" />';
        if ($player->avatar) {
          $out .= '<img class="avatar" src="'.$player->avatar->url.'" />';
        }
        $out .= '<h1 class="playerName">'.$player->title.'</h1>';
        $out .= '<h3>'.__("Monster invasion ! Your team has to react!").'</h3>';
        // Stats analysis
        $out .= '<h4 class="">';
          $out .= '<span class="">'.__("Your stats on this element").' → </span>';
          $out .= '<span class="label label-success">'.$quiz['stats']['0'].' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
          $out .= ' <span class="label label-danger">'.$quiz['stats']['1'].' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
          if ($quiz['stats']['1'] == 2) { $out .= ' <span class="blink"><i class="glyphicon glyphicon-warning-sign"></i></span>'; }
        $out .= '</h4>';
        $out .= '<h2 class="alert alert-danger text-center">';
        $out .= $quiz['question'].'&nbsp;&nbsp;';
        $out .= '</h2>';
        // Display map if necessary
        if ( $quiz['type'] === 'map' ) {
          $out .= '<section class="">';
            $placeId = $quiz['id'];
            $selectedElement = $pages->get("$placeId");
            $map = $modules->get('MarkupLeafletMap');
            $out .= $map->getLeafletMapHeaderLines();
            $selectedElement->map->zoom = 2;
            $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'Stamen.Toner');
            /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenTopoMap'); */
            /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenStreetMap.Mapnik'); */
            /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenStreetMap.HOT'); */
            /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'Stamen.TonerLite'); */
            $out .= $map->render($selectedElement, 'map', $options); 
          $out .= '</section>';
        }
        // Display photo if necessary
        if ( $quiz['type'] === 'photo' ) {
          $out .= '<section class="text-center">';
          $placeId = $quiz['id'];
          $options = array('upscaling'=>false);
          $selectedElement = $pages->get("$placeId");
          $photo = $selectedElement->photo->getRandom()->size(200,200, $options);
            $out .= '<img src="'.$photo->url.'" alt="'.$selectedElement->title.'." />';
          $out .= '</section>';
        }
        $out .= '<a id="showAnswer" class="label label-info lead">['.__('Check answer').']</a>';
        $out .= '<h2 id="answer" class="lead text-center">';
        $out .= $quiz['answer'];
        $out .= '</h2>';
        $out .= '<input type="hidden" name="playerId" value="'.$player->id.'" />';
        $out .= '<input type="hidden" name="quizId" value="'.$sanitizer->text($quiz['id']).'" />';
        $out .= '<input type="hidden" name="question" value="'.$sanitizer->text($quiz['question']).'" />';
        $out .= '<input type="hidden" name="answer" value="'.$sanitizer->text($quiz['answer']).'" />';
        $out .= '<p class="text-center">';
        $out .= '<button class="btn btn-info generateQuiz" type="submit" name="reloadButton" value="update" title="Re-generate"><span class="glyphicon glyphicon-refresh"></span></button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-success generateQuiz" type="submit" name="RightButton" value="right"><span class="glyphicon glyphicon-ok"></span> '.__('Right').'</button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<button class="btn btn-danger generateQuiz" type="submit" name="WrongButton" value="wrong"><span class="glyphicon glyphicon-remove"></span> '.__('Wrong').'</button>';
        $out .= '&nbsp;&nbsp;';
        $out .= '<label for="lastQuestion"><input type="checkbox" id="lastQuestion" name="lastQuestion" /> '.__('Last question').'</label>';
        $out .= '</p>';

      $out .= '</div>';
    }
    $out .= '<button type="submit" name="quizFormSubmitButton" class="btn btn-info btn-block generateQuiz">'.__('Generate').'</button>';

    // Players list display
    $out .= '<section class="well">';
    $out .= '<button id="toggle" class="btn btn-default">'.__('Toggle list').'</button>';
    $out .= '<div id="quizMenu" class="'.$display.'">';
    $out .= '<p>'.__("You need at least 3 free elements to appear in the list.").'</p>';
    $out .= '<ul class="list-group">';
      foreach($allConcerned as $p) {
          $details = "({$p->nbInvasions} inv. / ";
          if ( $rank >= 8) {
            $freeElements = $p->places->count()+$p->people->count();
          } else {
            $freeElements = $p->places->count();
          }
          $details .= "{$freeElements} el.)";
          $out .= "<li class='list-group-item'><label for='ch[{$p->id}]'><input type='checkbox' id='ch[{$p->id}]' name='selected[]' value='{$p->id}' {$p->checked}'> {$p->title} {$details}</label></li>";
      }
      $out .= '<button id="tickAll" class="btn btn-success btn-sm">'.__('Tick all').'</button>';
      $out .= '<button id="untickAll" class="btn btn-danger btn-sm">'.__('Untick all').'</button>';
    $out .= '</ul>';
    // Ambassadors
    $out .= '<p>'.__('Ambassadors').' : '.$ambassadorsNames;
    $out .= $ambassadorsButton;
    $out .= '</p>';
    $out .= '<h3 class="text-center"><span id="honored" class="label label-primary"></span></h3>';
    // Not concerned
    $out .= '<p>('.__('Not concerned').' : '.$notConcerned.')</p>';
    $out .= '</div>';
    $out .= '</section>';
    $out .= '<input type="hidden" name="quizFormSubmit" value="'.__('Save').'" />';
    $out .= '</form>';
  }
} else {
  if ($user->hasRole('player')) {
    $out = '';
    $quiz = pick_question($player);
    $out .= '<div class="well quiz">';
      $logo = $homepage->photo->eq(0)->getCrop('thumbnail');
      $out .= '<img class="monster" src="'.$logo->url.'" />';
      $out .= '<h3>'.__("Defensive preparation !");
      $out .= ' <span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.__("This is a simple practice area. Click on 'Check answer' below to see the solution. Then you can click on 'Next question'. Stop the session when you're tired :)").'"></span></h3>';
      $out .= '<h2 class="alert alert-danger text-center">';
      $out .= $quiz['question'].'&nbsp;&nbsp;';
      $out .= '</h2>';
      // Display map if necessary
      if ( $quiz['type'] === 'map' ) {
        $out .= '<section class="mapBox">';
          $placeId = $quiz['id'];
          $selectedElement = $pages->get("$placeId");
          $map = $modules->get('MarkupLeafletMap');
          $out .= $map->getLeafletMapHeaderLines();
          $selectedElement->map->zoom = 2;
          $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'Stamen.Toner');
          /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenTopoMap'); */
          /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenStreetMap.Mapnik'); */
          /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'OpenStreetMap.HOT'); */
          /* $options = array('markerIcon' => 'flag', 'markerColour' => 'green', 'provider' => 'Stamen.TonerLite'); */
          $out .= $map->render($selectedElement, 'map', $options); 
        $out .= '</section>';
      }
      // Display photo if necessary
      if ($quiz['type'] === 'photo' ) {
        $out .= '<section class="text-center">';
        $placeId = $quiz['id'];
        $options = array('upscaling'=>false);
        $selectedElement = $pages->get("$placeId");
        $photo = $selectedElement->photo->getRandom()->size(300,300, $options);
          $out .= '<img src="'.$photo->url.'" alt="'.$selectedElement->title.'." />';
        $out .= '</section>';
      }
      $out .= '<a id="showAnswer" class="label label-primary">'.__("[Check answer]").'</a>';
      $out .= '<h2 id="answer" class="lead text-center">';
      $out .= $quiz['answer'];
      $out .= ' <a class="btn btn-primary" href="'.$page->url.'">'.__("Next question").'</a>';
      $out .= '</h2>';
    $out .= '</div>';
  } else {
    $out = $noAuthMessage;
  }
}

echo $out;

include("./foot.inc"); 

?>
