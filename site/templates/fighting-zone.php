<?php namespace ProcessWire;
  include("./head.inc"); 

  $out = '';
  if (isset($player) && $user->isLoggedin() || $user->isSuperuser() || $user->hasRole('teacher')) {
    if ($user->hasRole('player')) {
      $lock = $pages->get("$player->team")->lockFights;
    } else {
      $lock = 0;
    }
    if ($lock == 1) { // Fights are locked by admin
      echo '<p class="alert alert-warning">'.__("Your teacher has disabled this option for the moment.").'</p> ';
    } else { // Fights are allowed
      // Set all available monsters
      if (!isset($player)) {
        $playerId = $input->urlSegment1;
        if ($playerId && $playerId != '') { // Teacher wants to see a player's fighting zone
          $player = $pages->get($playerId);
        } else { // All monsters are available for superUsers or teachers (debugging mode)
          if ($user->hasRole('teacher')) {
            $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$user")->sort("level, name");
          }
          if ($user->isSuperuser()) {
            $allMonsters = $pages->find("parent.name=monsters, template=exercise, include=all")->sort("level, name");
          }
          $availableFights = $allMonsters;
        }
      }
      // Check if player has the Visualizer (or forced by admin)
      if (isset($player)) {
        if ($player->equipment->has('name~=visualizer') || $player->team->forceVisualizer == 1) {
          $allMonsters = $pages->find("template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, summary!=''")->sort("level, name");
        } else { // Limit to visible monsters
          $allMonsters = $pages->find("template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=0, summary!=''")->sort("level, name");
          $hiddenMonstersNb = $pages->count("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=1, summary!=''");
        }
      }

      if (isset($player)) {
        // Prepare player's fighting possibilities
        foreach($allMonsters as $m) {
          setMonster($player, $m);
        }
        $availableFights = $allMonsters->find("isFightable=1");
        $waitingFights = $allMonsters->find("isFightable=0, lastFightInterval!=-1")->sort("waitForFight, allFightsNb");
        $impossibleFights = $allMonsters->find("isFightable=0, lastFightInterval=-1")->sort("-utGain, title");
      }

      $out .= '<div class="well">';
        $out .= '<h2 class="text-center">'.$page->title;
        if (($user->isSuperuser() || $user->hasRole('teacher')) && isset($playerId) && $playerId != '') {
          $out .= ' ('.$player->title.')';
        }
        $out .= '<span class="pull-left glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" data-placement="right" title="'.$page->summary.'"></span>';
        $page->of(false);
        if ($page->summary->getLanguageValue($french) != '') {
          $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" data-placement="left" title="'.$page->summary->getLanguageValue($french).'"></span>';
        }
        $out .= '</h2>';
        $out .= '<h4 class="text-center">';
        $out .= sprintf(__("There are currently %d monsters detected."), $allMonsters->count());
        if (isset($hiddenMonstersNb)) {
          $link = '<a href="'.$pages->get("name=shop")->url.'/details/electronic-visualizer">Electronic Visualizer</a>';
          /* $out .= '<p>('.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link).')</p>'; */
        } else {
          /* $out .= '<p>('.__("All monsters are visible thanks to your Electronic Visualizer.").')</p>'; */
        }
        $out .= '</h4>';

        if (isset($availableFights) && $availableFights->count() > 0) {
          $out .= '<br />';
          $out .= '<h4><span class="label label-danger">'.__("Monsters at proximity ! (You can fight them!)").'</span></h4>';
          $out .= '<ul class="list list-inline">';
          foreach($availableFights as $m) {
            if ($m->image) {
              $mini = "<img class='' src='".$m->image->getCrop('small')->url."' alt='image' />";
            } else {
              $mini = '';
            }
            $out .= '<li><a href="'.$m->url.'fight" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="'.$m->summary.'">'.$mini.' '.$m->title.'</a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<h4><span class="label label-danger">'.__("There are no monsters at proximity !").'</h4>';
        }

        if (isset($waitingFights) && $waitingFights->count() > 0) {
          $out .= '<br />';
          $out .= '<h4><span class="label label-success">'.__("Approaching monsters ! (You can't fight them today. You must wait.)").'</span></h4>';
          $out .= '<ul class="list">';
          foreach($waitingFights as $m) {
            if ($m->image) {
              $mini = "<img class='' src='".$m->image->getCrop('mini')->url."' alt='image' />";
            } else {
              $mini = '';
            }
            $out .= '<li>';
            $out .= '<span class="label label-success">'.$mini.' '.$m->title.'</span> '.__("will be at proximity in").' ';
            if ($m->waitForFight == 1) {
              $out .= '<span class="badge badge-primary">'.__("tomorrow").' !</span>';
            } else {
              $out .= '<span class="badge badge-primary">'.$m->waitForFight.' '.__("days").'</span>';
            }
            if ($m->lastTrainingInterval == 0) {
              $out .= ' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.sprintf(__('Memory helmet used today. %s detected it and walked away.'), $m->title).'"></i></li>';
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
        }

        if (isset($impossibleFights) && $impossibleFights->count() > 0) {
          $out .= '<br /><br />';
          $utZoneLink = '<a href="'.$pages->get("name=underground-training")->url.'">'.__("underground training").'</a>';
          $out .= '<h4><span class="glyphicon glyphicon-thumbs-down"></span> '.__("Out of reach monsters ! (You can't fight them)").'</h4>';
          $label = '<span class="label label-success">+20'.__('UT').'</span>';
          $out .= '<p>'.sprintf(__('You must do %1$s first and get at least %2$s'), $utZoneLink, $label).'</p>';
          $out .= '<ul class="list list-inline">';
          foreach($impossibleFights as $m) {
            $out .= '<li><span class="">['.$m->title.' '.$m->utGain.__('UT').']</span></li>';
          }
          $out .= '</ul>';
        }
      $out .= '</div>';

      // helpAlert
      if ($user->hasRole('player')) {
        if ($player->equipment->has('name~=visualizer') || $player->team->forceVisualizer == 1) {
          if (isset($impossibleFights) && $impossibleFights->count() > 0) {
            $helpAlert = true;
            $helpTitle = sprintf(__("%d monsters are out of reach !"), $impossibleFights->count());
            $helpMessage = '<h4>'.__("You need to have at least 20UT to be able to fight them.").'</h4>';
          }
        } else {
          $helpAlert = true;
          $helpTitle = __("Some monsters are absent !");
          $helpMessage = '<img src="'.$pages->get("name~=visualizer")->image->getCrop("small")->url.'" alt="image" /> ';
          $helpMessage .= '<h4>'.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link).'</h4>';
        }
      } else {
        $helpAlert = true;
        if (isset($player)) {
          $helpTitle = sprintf(__("Viewing Fighting zone of %s !"), $player->title);;
          $helpMessage = '<h4>'.__("Available monsters depend on player's training.").'</h4>';
        } else {
          $helpTitle = __("Teacher access !");
          $helpMessage = '<h4>'.__('All monsters are fightable for testing.').'</h4>';
        }
      }
        include("./helpAlert.inc.php"); 

      echo $out;
    }
  } else {
    echo $noAuthMessage;
  }

  include("./foot.inc"); 
?>
