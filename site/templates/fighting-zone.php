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
        $headTeacher = getHeadTeacher($player);
        if ($player->team->is("name=test-team")) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise")->sort("level, name");
        } else if ($player->equipment->has('name~=visualizer') || $player->team->forceVisualizer == 1) {
          $allMonsters = $pages->find("template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1")->sort("level, name");
        } else { // Limit to visible monsters
          $allMonsters = $pages->find("template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=0")->sort("level, name");
          $hiddenMonstersNb = $pages->count("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=1, summary!=''");
        }
      }

      if (isset($player)) {
        $todaysLimit = 3; // Limit number of fights a day
        if ($player->equipment->has("name=recovering-potion")) {
          $todaysLimit = $todaysLimit*2;
        }
        $today = new \DateTime("today");
        $limitDate = strtotime($today->format('Y-m-d'));
        $todaysFightsNb = $pages->find("has_parent=$player, task.name~=fight, date>=$limitDate")->count();
      }

      if ($todaysFightsNb >= $todaysLimit) {
        $out .= '<div class="well">';
        $out .= '<span class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> '.__("Congratulations !").'</span>';
        $out .= '<h4>'.__("You've reached today's fight limit ! Continuing would be dangerous. You must take a rest !").'</h4>';
        if (!$player->equipment->has("name=recovering-potion")) {
          $recoveringPotion = $pages->get("name=recovering-potion");
          $recoverLink = '<a href="'.$shop->url.'details/'.$recoveringPotion->name.'">'.$recoveringPotion->title.'</a>';
          $out .= '<p>'.sprintf(__('You can buy the %s to double your fighting limit !'), $recoverLink).'</p>';
        }
        $out .= '</div>';
      } else {
        if (isset($player)) {
          // Prepare player's fighting possibilities
          foreach($allMonsters as $m) {
            setMonster($player, $m);
          }
          $availableFights = $allMonsters->find("isFightable=1");
          $waitingFights = $allMonsters->find("isFightable=0, utGain>=20")->sort("waitForFight, allFightsNb");
          $impossibleFights = $allMonsters->find("isFightable=0, utGain<20")->sort("-utGain, title");
        }

        $out .= '<div class="well">';
          $out .= '<h2 class="text-center">'.$page->title;
          if (($user->isSuperuser() || $user->hasRole('teacher')) && isset($playerId) && $playerId != '') {
            $out .= ' ('.$player->title.')';
          }
          $out .= '<span class="pull-left glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" data-placement="right" title="'.$page->summary.'"></span>';
          $page->of(false);
          if ($page->summary->getLanguageValue($french) != '') {
            $out .= '<img class="img-rounded pull-right" src="'.$urls->templates.'img/flag_fr.png" data-toggle="tooltip" data-html="true" data-placement="left" title="'.$page->summary->getLanguageValue($french).'" />';
          }
          $out .= '</h2>';
          $out .= '<h4 class="text-center">';
          $out .= sprintf(__("There are currently %d monsters detected."), $allMonsters->count());
          $out .= '<p>'.sprintf(__('You are limited to %2$d fights a day ! (You have done %1$d)'), $todaysFightsNb, $todaysLimit).'</p>';
          if (isset($hiddenMonstersNb)) {
            $link = '<a href="'.$pages->get("name=shop")->url.'/details/electronic-visualizer">'.__('Electronic Visualizer').'</a>';
          }
          $out .= '</h4>';

          if (isset($availableFights) && $availableFights->count() > 0) {
            $out .= '<br />';
            $out .= '<h4><span class="label label-danger">'.__("Monsters at proximity ! (You can fight them!)").'</span></h4>';
            $out .= '<ul class="list list-inline">';
            foreach($availableFights as $m) {
              if ($m->image) {
                $mini = "<img class='' src='".$m->image->getCrop('small')->url."' alt='".$m->title.".' />";
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
                $mini = "<img class='' src='".$m->image->getCrop('mini')->url."' alt='".$m->title.".' />";
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
      }

      // helpAlert
      if ($user->hasRole('player')) {
        if ($player->equipment->has('name~=visualizer') || $player->team->forceVisualizer == 1) {
          if (isset($impossibleFights) && $impossibleFights->count() > 0) {
            $helpAlert = true;
            $helpTitle = sprintf(__("%d monsters are out of reach !"), $impossibleFights->count());
            $helpMessage = '<h4>'.__("You need to have at least 20UT to be able to fight them.").'</h4>';
          }
          if ($todaysFightsNb >= $todaysLimit) {
            $helpAlert = true;
            $helpTitle = sprintf(__("You've done %d fights today !"), $todaysFightsNb);
            $helpMessage = '<h4>'.__("Continuing would be dangerous. You must take a rest !").'</h4>';
          }
        } else {
          $helpAlert = true;
          $helpTitle = __("Some monsters are absent !");
          $helpMessage = '<img src="'.$pages->get("name~=visualizer")->image->getCrop("small")->url.'" alt="Electronic visualizer." /> ';
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
