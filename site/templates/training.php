<?php namespace ProcessWire;
  include("./head.inc"); 

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser() || $user->hasRole('teacher')) {
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $user->hasRole('teacher') || $player->team->forceHelmet == 1) {
      $helmet = $pages->get("name=memory-helmet");
    } else {
      $helmet = $player->equipment->get('memory-helmet');
    }
    if ($helmet) {
      $out = '<div>';
      if (!$input->get->id) { // Display training catalogue
        // Set all available monsters
        if ($user->isSuperuser()) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, sort=name, include=all");
        }
        if ($user->hasRole('teacher')) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, (created_users_id=$user->id),(exerciseOwner.singleTeacher=$user,exerciseOwner.publish=1, summary!='')")->sort("name");
        }
        if ($user->hasRole('player')) {
          // Check if player has the Visualizer (or forced by admin)
          if ($player->equipment->has("name~=visualizer") || $player->team->forceVisualizer == 1) {
            $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1")->sort("name");
          } else {
            $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=0")->sort("name");
            $hiddenMonstersNb = $pages->count("parent.name=monsters, template=exercise, (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1), special=1");
          }
        }
        $out .= '<br />';
        $out .= '<div class="well">';
        $out .= '<h2 class="text-center">'.$page->title;
        $out .= '<span class="pull-left glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->summary.'"></span>';
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
        $out .= '<div id="Filters" data-fcolindex="1" class="text-center">';
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
          $out .= '<th>'.__("Topic").'</th>';
          $out .= '<th>'.__("Level").'</th>';
          $out .= '<th>'.__("Summary").'</th>';
          $out .= '<th>'.__("# of words").'</th>';
          $out .= '<th>'.__("U.T. gained").'</th>';
          $out .= '<th>'.__("Last training session").'</th>';
          $out .= '<th>'.__("Action").'</th>';
          $out .= '<th>'.__("Most trained player").'</th>';
          $out .= '<th>'.__("Master time").'</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
        $today = new \DateTime("today");
        foreach($allMonsters as $m) {
          if ($user->hasRole('player')) {
            // Prepare player's training possibilities
            setMonster($player, $m);
          } else { // Never trained (for admin)
            $m->isTrainable = 1;
            $m->lastTrainingInterval = -1;
            $m->waitForTrain = 0;
          }
          $out .= '<tr>';
          $out .= '<td>';
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
          $out .= '<span class="label label-default">'.$m->topic->implode(', ', '{title}').'</span>';
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
          $out .= '</td>';
          // Count # of words
          $exData = $m->exData;
          $allLines = preg_split('/$\r|\n/', $sanitizer->entitiesMarkdown($exData));
          /* Unused because triggers a bug with tooltip display */
          /* $out .= '<td data-sort="'.count($allLines).'">'; */
          $out .= '<td>';
          $listWords = prepareListWords($allLines, $m->type->name);
          switch ($m->type->name) {
            case 'translate' :
              $out .= count($allLines).' '.__("words");
              break;
            case 'quiz' :
              $out .= count($allLines).' '.__("questions");
              break;
            case 'image-map' :
              $out .= count($allLines).' '.__("words");
              break;
            case 'jumble' :
              $out .= count($allLines).' '.__("sentences");
              break;
            default : continue;
          }
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
            $out .= ' <a class="btn btn-primary" href="'.$m->url.'train"><i class="glyphicon glyphicon-headphones"></i> '.__("Put the helmet on !").'</a>';
          } else {
            if ($m->waitForTrain == 1) { // Trained today
              $out .= __('Come back tomorrow ;)');
            } else {
              $out .= sprintf(__("Come back in %d days ;)"), $m->waitForTrain);
            }
          }
          $out .= '</td>';
          // Find best trained player on this monster
          if ($m->mostTrained) {
            if (isset($player) && $m->mostTrained == $player) {
              $class = 'success';
            } else {
              $class = 'primary';
            }
          }
          $out .= '<td data-sort="'.$m->best.'">';
          if ($m->mostTrained) {
            $out .= '<span class="label label-'.$class.'">'.$m->best.' '.__("UT").' - '.$m->mostTrained->title.' ['.$m->mostTrained->team->title.']</span>';
          } else {
            $out .= '<span>No record yet.</span>';
          }
          $out .= '</td>';
          $out .= '<td data-sort="'.$m->bestTime.'">';
          if ($m->bestTime) {
            $out .= ms2string($m->bestTime).' '.__('by').' '.$m->bestTimePlayer->title.' ['.$m->bestTimePlayer->team->title.']';
          } else {
            $out .= '-';
          }
          $out .= '</td>';
          $out .= '</tr>';
        }
        $out .= '</tbody>';
        $out .= '</table>';
      }
      echo $out;
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo $noAuthMessage;
  }

  include("./foot.inc"); 
?>
