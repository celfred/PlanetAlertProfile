<?php
  include("./head.inc"); 

  $out = '';
  $anyFight = false;
  // Test player login
  if ($player && $user->isLoggedin() || $user->isSuperuser()) {
    // Display Personal Mission Analyzer
    $helmet = $player->equipment->get("name=memory-helmet");
    if ($helmet->id) {
      // Display Personal Mission Analyzer
      echo pma($player);
    }

    $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name");
    foreach($allMonsters as $m) {
      $m = isFightAllowed($player, $m);
      if ($m->isFightable !== 0) {
        $anyFight = true;
      }
    }
    $availableFights = $allMonsters->find("isFightable=1, interval>0");
    $waitingFights = $allMonsters->find("isFightable=0, interval!= -1, sort=spaced, sort=allFightsNb");
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
        $out .= '<li><span class="">'.$m->title.'</span> : you have only <span class="badge badge-primary">'.$m->utGain.'UT...</span></li>';
      }
      $out .= '</ul>';
    }
    $out .= '</div>';

    /* $out .= '<table id="fightingTable" class="table table-condensed table-hover">'; */
    /*   $out .= '<thead>'; */
    /*   $out .= '<tr>'; */
    /*   $out .= '<th></th>'; */
    /*   $out .= '<th>Monster\'s Name</th>'; */
    /*   $out .= '<th>Level</th>'; */
    /*   $out .= '<th>Summary</th>'; */
    /*   $out .= '<th>UT Gained</th>'; */
    /*   $out .= '<th># of fights</th>'; */
    /*   $out .= '<th>Last fight</th>'; */
    /*   $out .= '<th>Action</th>'; */
    /*   $out .= '</tr>'; */
    /*   $out .= '</thead>'; */
    /*   $out .= '<tbody>'; */
    /*   foreach($allMonsters as $m) { */
    /*     $m = isFightAllowed($player, $m); */
    /*     if ($m->image) { */
    /*       $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getThumb('thumbnail')."\" alt=\"image\" />' src='".$m->image->getThumb('mini')."' alt='image' />"; */
    /*     } else { */
    /*       $mini = ''; */
    /*     } */
    /*     if ($m->isFightable !== 0) { */
    /*       $anyFight = true; */
    /*       $out .= '<tr>'; */
    /*       $out .= '<td>'. $mini .'</td>'; */
    /*       $out .= '<td>'.$m->title.'</td>'; */
    /*       $out .= '<td>'.$m->level.'</td>'; */
    /*       $out .= '<td>'.$m->summary.' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$m->frenchSummary.'"></span></td>'; */
    /*       $out .= '<td>'.$m->utGain.'</td>'; */
    /*       $allFights = $player->find("template=event, task.name=fight-vv|fight-v|fight-r|fight-rr, refPage=$m, sort=-date"); */
    /*       $out .= '<td>'.$allFights->count().'</td>'; */
    /*       if ($m->lastFightDate != 0) { */
    /*         switch ($m->interval) { */
    /*           case 0 : */
    /*             $interval = "Today !"; */
    /*             break; */
    /*           case 1 : */ 
    /*             $interval = "1 day ago."; */
    /*             break; */
    /*           default: */
    /*             $interval = $m->interval." days ago."; */
    /*         } */
    /*         /1* $out .= '<td><span class="label label-success">'.date("F d, Y", $m->lastFightDate).$interval.'</span></td>'; *1/ */
    /*         $out .= '<td><span class="label label-success">'.$interval.'</span></td>'; */
    /*       } else { */
    /*         $out .= '<td>Not fought yet.</td>'; */
    /*       } */
    /*       $out .= $m->isFightable; */
    /*       if ($m->isFightable == 2) { */
    /*         $out .= '<td>Come back tomorrow ;)</td>'; */
    /*       } else { */
    /*         $out .= '<td><a class="label label-sm label-primary" href="'.$m->url.'">Fight this monster !</a></td>'; */
    /*       } */
    /*     } */
    /*     // Get previous player's statistics */
    /*     $prevUt = $player->find('template=event,refPage='.$m->id.', sort=-date'); */
    /*   } */
    /*   $out .= '<tbody>'; */
    /* $out .= '</table>'; */

    if (!$anyFight) {
      $out = '<p class="well">You have not trained enough to fight any monster. Go to the <a href="'.$pages->get("name=underground-training")->url.'">Training Zone</a> first and use the Memory helmet. You need at least +20UT on an monster to be able to fight!</p>';
    }

    echo $out;

    echo '</div>';
    echo '</div>';
  } else {
    echo '<p class="alert alert-warning">Sorry, but you don\'t have access to the Fighting Zone. Contact the administrator if you think this is an error.</p> ';
  }

  include("./foot.inc"); 
?>
