<?php namespace ProcessWire;
  include("./head.inc"); 

  $out = '';
  if (isset($player) && $user->isLoggedin() || $user->isSuperuser()) {
    if (!$user->isSuperuser()) {
      $lock = $pages->get("$player->team")->lockFights;
    } else {
      $lock = 0;
    }
    if ($lock == 1) { // Fights are locked by admin
      echo '<p class="alert alert-warning">Sorry, but the administrator has disabled this option for the moment.</p> ';
    } else { // Fights are allowed
      // Set all available monsters
      if (!$user->isSuperuser()) {
        // Check if player has the Visualizer (or forced by admin)
        if ($player->equipment->has('name~=visualizer') || $player->team->forceVisualizer == 1) {
          $allMonsters = $pages->find("template=exercise, sort=level, sort=name");
        } else { // Limit to visible monsters
          $allMonsters = $pages->find("template=exercise, sort=level, sort=name, special=0");
          $hiddenMonstersNb = $pages->count("template=exercise, special=1");
        }
      } else {
        $allMonsters = $pages->find("template=exercise, sort=level, sort=name, include=all");
        $availableFights = $allMonsters;
      }

      if (!$user->isSuperuser()) {
        // Prepare player's fighting possibilities
        foreach($allMonsters as $m) {
          setMonster($player, $m);
        }
        $availableFights = $allMonsters->find("isFightable=1");
        $waitingFights = $allMonsters->find("isFightable=0, lastFightInterval!=-1")->sort("waitForFight, allFightsNb");
        $impossibleFights = $allMonsters->find("isFightable=0, lastFightInterval=-1")->sort("-utGain, title");
      }

      $out .= '<div class="well">';
        $out .= '<h2 class="text-center">'.$page->title.'</h2>';
        $out .= $page->summary;
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->frenchSummary.'"></span>';

        $out .= '<h4 class="text-center">';
        $out .= 'There are currently '.$allMonsters->count().' monsters detected.';
        if (isset($hiddenMonstersNb)) {
          $out .= '<p>('.$hiddenMonstersNb.' monsters are absent because you don\'t have the <a href="'.$pages->get("name=shop")->url.'/details/electronic-visualizer">Electronic Visualizer</a>.)</p>';
        } else {
          $out .= '<p>(All monsters are visible thanks to your Electronic Visualizer.)</p>';
        }
        $out .= '</h4>';

        if (isset($availableFights) && $availableFights->count() > 0) {
          $out .= '<br />';
          $out .= '<h4><span class="label label-danger">Monsters at proximity ! (You can fight them!)</span></h4>';
          $out .= '<ul class="list list-inline">';
          foreach($availableFights as $m) {
            if ($m->image) {
              $mini = "<img class='' src='".$m->image->getCrop('small')->url."' alt='image' />";
            } else {
              $mini = '';
            }
            $out .= '<li><a href="'.$m->url.'" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="'.$m->summary.'">'.$mini.' '.$m->title.'</a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<h4><span class="label label-danger">There are no monsters at proximity !</h4>';
        }

        if (isset($waitingFights) && $waitingFights->count() > 0) {
          $out .= '<br />';
          $out .= '<h4><span class="label label-success">Approaching monsters ! (You can\'t fight them today. You must wait.)</span></h4>';
          $out .= '<ul class="list">';
          foreach($waitingFights as $m) {
            if ($m->image) {
              $mini = "<img class='' src='".$m->image->getCrop('mini')->url."' alt='image' />";
            } else {
              $mini = '';
            }
            $out .= '<li>';
            if ($m->waitForFight == 1) {
              $out .= '<span class="label label-success">'.$mini.' '.$m->title.'</span> will be at proximity in <span class="badge badge-primary">tomorrow !</span>';
            } else {
              $out .= '<span class="label label-success">'.$mini.' '.$m->title.'</span> will be at proximity in <span class="badge badge-primary">'.$m->waitForFight.' days</span>';
            }
            if ($m->lastTrainingInterval == 0) {
              $out .= ' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Memory helmet used today. '.$m->title.' detected it and walked away."></i></li>';
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
        }

        if (isset($impossibleFights) && $impossibleFights->count() > 0) {
          $out .= '<br /><br />';
          $utZone = $pages->get("name=underground-training")->url;
          $out .= '<h4><span class="glyphicon glyphicon-thumbs-down"></span> Out of reach monsters ! (You can\'t fight them, you must do <a href="'.$utZone.'">underground training</a> first and get at least <span class="label label-success">+20UT</span>)</h4>';
          $out .= '<ul class="list list-inline">';
          foreach($impossibleFights as $m) {
            $out .= '<li><span class="">['.$m->title.' '.$m->utGain.'UT]</span></li>';
          }
          $out .= '</ul>';
        }
      $out .= '</div>';

      echo $out;
    }
  } else {
    echo '<p class="alert alert-warning">Sorry, but you don\'t have access to the Fighting Zone. Contact the administrator if you think this is an error.</p> ';
  }

  include("./foot.inc"); 
?>
