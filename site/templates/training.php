<?php namespace ProcessWire;
  include("./head.inc"); 

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser() || $user->hasRole('teacher')) {
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $user->hasRole('teacher') || $player->team->forceHelmet == 1) {
      $helmet = $pages->get("name=memory-helmet");
      if ($user->isSuperuser() || $user->hasRole('teacher')) {
        $player = $pages->get("parent.name=players, name=test");
        $request = false;
      }
    } else {
      $helmet = $player->equipment->get("name=memory-helmet");
    }
    if ($helmet) { // Display training catalogue
      $out = '<div>';
      // Set all available monsters
      if ($user->hasRole('player')) {
        // Check if player has the Visualizer (or forced by admin)
        if ($player->team->is("name=test-team")) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise")->sort("name");
          $allMonstersNb = $allMonsters->count();
        } else if ($player->equipment->has("name~=visualizer") || $player->team->forceVisualizer == 1) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1")->sort("name");
          $allMonstersNb = $allMonsters->count();
        } else {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=0")->sort("name");
          $hiddenMonstersNb = $pages->count("parent.name=monsters, template=exercise, (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1), special=1");
        }
        // Check if fightRequest
        if ($player->fight_request == 0) { $request = false; } else { $request = $player->fight_request; }
      } else if ($user->hasRole('teacher')) {
        $allMonsters = $pages->find("parent.name=monsters, template=exercise, (created_users_id=$user->id),(exerciseOwner.singleTeacher=$user,exerciseOwner.publish=1, summary!='')")->sort("name");
      } else if ($user->isSuperuser()) {
        $allMonsters = $pages->find("parent.name=monsters, template=exercise, sort=name, include=all");
      }
      $out .= '<div class="well">';
      $out .= '<h2 class="text-center">';
        $out .= '<span class="pull-left glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->summary.'"></span>';
        $out .= $page->title;
        if ($helmet->image) {
          $out .= '<img class="pull-right" src="'.$helmet->image->url.'" alt="Helmet" />';
        }
      $out .= '</h2>';
      if (isset($hiddenMonstersNb)) { // Display helpAlert for Electronic visualizer
        $helpAlert = true;
        $link = '<a href="'.$pages->get("name=shop")->url.'/details/electronic-visualizer">Electronic Visualizer</a>';
        $helpTitle = __("Some monsters are absent !");
        $helpMessage = '<img src="'.$pages->get("name~=visualizer")->image->getCrop("small")->url.'" alt="image" /> ';
        $helpMessage .= '<h4>'.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link).'</h4>';
      }
      include("./helpAlert.inc.php"); 
      $allCategories = $pages->find("parent.name=topics, sort=name");
      $out .= '<div id="Filters" class="text-center">';
        $out .= '  <ul class="list-inline well">';
          foreach ($allCategories as $category) {
            if ($allMonsters->get("topic=$category")) {
              $out .= '<li><label for="'.$category->name.'" class="btn btn-primary btn-xs">'.$category->title.' <input type="checkbox" value="'.$category->title.'" class="categoryFilter" name="categoryFilter" id="'.$category->name.'"></label></li>';
            }
          }
        $out .= '</ul>';
      $out .= '</div>';
      $out .= '<table id="trainingTable" class="table table-condensed table-hover">';
        $out .= '<thead>';
        $out .= '<tr>';
        $out .= '<th>'.__("Name").'</th>';
        $out .= '<th>'.__("Level").'</th>';
        $out .= '<th style="width:250px;">'.__("Summary").'</th>';
        $out .= '<th>'.__("U.T. gained").'</th>';
        $out .= '<th>'.__("Last training session").'</th>';
        $out .= '<th>'.__("Actions");
        $out .= ' <i class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="- '.__('Fight requests require at least +1UT on a monster<br />- Limited to 1 fight request').'"></i></th>';
        $out .= '<th>'.__("Most trained player").'</th>';
        $out .= '<th>'.__("Master time").'</th>';
        $out .= '</tr>';
        $out .= '</thead>';
        $out .= '<tbody>';
        $today = new \DateTime("today");
        foreach($allMonsters as $m) {
          $m->of(false);
          if ($user->hasRole('player')) {
            // Prepare player's training possibilities
            setMonster($player, $m);
            if ($m->bestTrainedPlayerId != 0) {
              $bestTrained = $pages->get($m->bestTrainedPlayerId);
              $m->bestTrainedTitle = $bestTrained->title;
              $m->bestTrainedTeam = $bestTrained->team->title;
              if ($m->bestTrainedPlayerId == $player->id) {
                $m->isBestTrained = true;
              } else {
                $m->isBestTrained = false;
              }
            }
            if ($m->bestTimePlayerId != 0) {
              $master = $pages->get($m->bestTimePlayerId);
              $m->bestTimePlayerTitle = $master->title;
              $m->bestTimeTeam = $master->team->title;
              if ($m->bestTrainedPlayerId == $player->id) {
                $m->isMaster = true;
              } else {
                $m->isMaster = false;
              }
            }
          }
          if ($user->hasRole("teacher")) {
            // Never trained (for admin)
            $m->isTrainable = 1;
            $m->lastTrainingInterval = -1;
            $m->waitForTrain = 0;
          }
          $topics = $m->topic->implode(', ', '{title}');
          $out .= '<tr>';
          $out .= '<td data-search="'.$topics.','.$m->name.'">';
          $out .= $m->title;
          // Find # of days compared to today to set 'New' indicator
          $date2 = new \DateTime(date("Y-m-d", $m->published));
          $interval = $today->diff($date2);
          if ($interval->days < 7) {
            $out .= ' <span class="badge">'.__("New").'</span>';
          }
          if ($m->special) {
            $out .= ' <span class="badge">'.__("Detected").' !</span>';
          }
          $out .= '</td>';
          $out .= '<td>';
          $out .= $m->level;
          $out .= '</td>';
          $out .= '<td>';
          $m->summary == '' ? $summary = '-' : $summary = $m->summary;
          $out .= $summary;
          if ($user->language->name != 'french') {
            $m->of(false);
            if ($m->summary->getLanguageValue($french) != '') {
              $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$m->summary->getLanguageValue($french).'"></span>';
            }
          }
          // Data preview
          $exData = $m->exData;
          $allLines = preg_split('/$\r|\n/', $sanitizer->entitiesMarkdown($exData));
          $listWords = prepareListWords($allLines, $m->type->name);
          $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
          $out .= '</td>';
          $out .= '<td>';
          if ($user->hasRole('player')) {
            if ($m->utGain > 0) {
              $out .= '<span class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> +'.$m->utGain.'</span> ';
            } else {
              $out .= '<span class="label label-danger"><span class="glyphicon glyphicon-thumbs-down"></span> 0</span> ';
            }
          } else {
            if ($user->isSuperuser()) {
              $out .= '[Admin]';
            } else {
              $out .= '['.__("Teacher").']';
            }
          }
          $out .= '</td>';
          // Last training session date
          $out .= '<td>';
          if ($user->hasRole('player')) {
            if ($m->lastTrainingInterval != '-1') {
              $out .= $m->lastTrainingInterval;
            } else {
              $out .= '-';
            }
          } else {
            if ($user->isSuperuser()) {
              $out .= '[Admin]';
            } else {
              $out .= '['.__("Teacher").']';
            }
          }
          $out .= '</td>';
          $out .= '<td>';
          if ($m->isTrainable == 1) {
            $out .= ' <a class="btn btn-primary btn-xs" href="'.$m->url.'train"><i class="glyphicon glyphicon-headphones" data-toggle="tooltip" title="'.__("Put the helmet on !").'"></i></a>';
          } else {
            if ($m->waitForTrain == 1) { // Trained today
              $out .= __('Come back tomorrow ;)');
            } else {
              $out .= sprintf(__("Come back in %d days ;)"), $m->waitForTrain);
            }
          }
          if ($request == 0) {
            $msg = sprintf(__("Fight request for %s"), $m->title);
            $out .= ' <span><a class="btn btn-danger btn-xs simpleConfirm" href="'.$page->url.'" data-href="'.$pages->get("name=submitforms")->url.'?form=fightRequest&monsterId='.$m->id.'&playerId='.$player->id.'" data-msg="'.$msg.'" data-reload="true"><i class="glyphicon glyphicon-education" data-toggle="tooltip" title="'.__("Ask teacher for an in-class Fight!").'"></i></a></span>';
          } else if ($request == $m->id) {
            $out .= ' <span class="glyphicon glyphicon-ok-circle" data-toggle="tooltip" title="'.__('Your teacher has already been warned about this request.').'"></span>';
          }
          $out .= '</td>';
          // Find best trained player on this monster
          $out .= '<td data-sort="'.$m->best.'">';
          if ($m->bestTrainedPlayerId != 0) {
            if ($m->isBestTrained) { $class = 'success'; } else { $class = 'primary'; }
            $out .= '<span class="label label-'.$class.'">'.$m->best.' '.__("UT").' - '.$m->bestTrainedTitle.' ['.$m->bestTrainedTeam.']</span>';
          } else {
            $out .= '<span>No record yet.</span>';
          }
          $out .= '</td>';
          $out .= '<td data-sort="'.$m->masterTime.'">';
          if ($m->bestTimePlayerTitle) {
            if ($m->isMaster) { $class = 'success'; } else { $class = 'primary'; }
            $out .= '<span class="label label-'.$class.'">'.ms2string($m->masterTime).' '.__('by').' '.$m->bestTimePlayerTitle.' ['.$m->bestTimeTeam.']</span>';
          } else {
            $out .= '-';
          }
          $out .= '</td>';
          $out .= '</tr>';
        }
      $out .= '</tbody>';
      $out .= '</table>';
      echo $out;
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo $noAuthMessage;
  }

  include("./foot.inc"); 
?>
