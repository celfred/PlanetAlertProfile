<?php
  include("./head.inc"); 

  $out = '';
  /* $anyFight = false; */
  // Test player login
  if ($player && $user->isLoggedin() || $user->isSuperuser()) {
    // Test if fights have been disabled by Admin
    $lock = $pages->get("name=admin-actions")->lockFights->get("playerTeam=$player->playerTeam");
    if ($lock) {
      echo '<p class="alert alert-warning">Sorry, but the administrator has disabled this option for the moment.</p> ';
    } else {
      // Display Personal Mission Analyzer
      $helmet = $player->equipment->get("name=memory-helmet");
      if ($helmet->id) {
        // Display Personal Mission Analyzer
        echo pma($player);
      }

      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name");
      foreach($allMonsters as $m) {
        $m = isFightAllowed($player, $m);
      }
      $availableFights = $allMonsters->find("isFightable=1");
      $waitingFights = $allMonsters->find("isFightable=0, interval!=-1, sort=spaced, sort=allFightsNb");
      $impossibleFights = $allMonsters->find("isFightable=0, interval=-1");

      $out .= '<div class="row">';
        $out .= '<div class="col-sm-12 text-center">';
        $out .= '<h2><span class="label label-default">This is the '.$page->title.'</span></h2>';
        $out .= '</div>';
      $out .= '</div>';

      $out .= '<div class="well">';
        $out .= $page->summary;
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->frenchSummary.'"></span>';
      $out .= '</div>';


      $out .= '<div class="well">';
      if ($availableFights->count() > 0) {
        $out .= '<h4><span class="label label-danger">Monsters at proximity ! (You can fight them!)</span></h4>';
        $out .= '<ul class="list list-inline">';
        foreach($availableFights as $m) {
          if ($m->image) {
            $mini = "<img class='' src='".$m->image->getThumb('mini')."' alt='image' />";
          } else {
            $mini = '';
          }
          $out .= '<li><a href="'.$m->url.'" data-toggle="tooltip" data-html="true" title="'.$m->summary.'" class="btn btn-lg btn-primary"><span class="">'.$mini.' '.$m->title.'</span></a></li>';
        }
        $out .= '</ul>';
      }

      if ($waitingFights->count() > 0) {
        $out .= '<h4><span class="label label-success">Monsters repelled that will come back soon ! (You can\'t fight them today...)</span></h4>';
        $out .= '<ul class="list">';
        foreach($waitingFights as $m) {
          if ($m->image) {
            $mini = "<img class='' src='".$m->image->getThumb('mini')."' alt='image' />";
          } else {
            $mini = '';
          }
          if ($m->spaced > 1) {
            $out .= '<li><span class="label label-success">'.$mini.' '.$m->title.'</span> will be at proximity in <span class="badge badge-primary">'.$m->spaced.' days</span></li>';
          } else {
            $out .= '<li><span class="label label-success">'.$mini.' '.$m->title.'</span> will be at proximity in <span class="badge badge-primary">tomorrow !</span></li>';
          }
        }
        $out .= '</ul>';
      }

      if ($impossibleFights->count() > 0) {
        $out .= '<p class="label label-warning">Out of reach monsters ! (You can\'t fight them, you must do underground training first)</p>';
        $out .= '<ul class="list list-inline">';
        foreach($impossibleFights as $m) {
          $out .= '<li><span class="">'.$m->title.'</span> <span class="badge badge-primary">only '.$m->utGain.'UT</span></li>';
        }
        $out .= '</ul>';
      }
      $out .= '</div>';

      echo $out;

      echo '</div>';
      echo '</div>';
    }

  } else {
    echo '<p class="alert alert-warning">Sorry, but you don\'t have access to the Fighting Zone. Contact the administrator if you think this is an error.</p> ';
  }

  include("./foot.inc"); 
?>
