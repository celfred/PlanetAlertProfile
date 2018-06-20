<?php namespace ProcessWire;

include("./head.inc"); 

if (isset($player) && $user->isLoggedin() || $user->isSuperuser()) { // Test player login
  // Test if group has unlocked Electronic Visualizer
  // or if admin has forced it in Team options
  if ($user->isSuperuser() || $player->team->forceVisualizer == 1) {
    $visualizer = $pages->get("name=electronic-visualizer");
  } else {
    $visualizer = $player->equipment->get('electronic-visualizer');
  }
  if ($visualizer) {
    $allMonsters = $pages->find("template=exercise");

    $out = '';

    $out .= '<section class="row">';

    // Display Personal Analyzer if user is logged in
    if ($user->isLoggedin() && $user->isSuperuser()==false) {
      $player = $pages->get("login=$user->name");
      echo pma($player);
    }

    $out .= '<section class="well text-center">';
      $out .= '<h2>';
        $out .= '<img class="" src="'.$visualizer->image->getCrop("small")->url.'" alt="image" /> ';
        $out .= $visualizer->title;
        $out .= ' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="All monsters are visible. The bigger the monster is, the closest to you it is. This means you should take action !"></i>';
      $out .= '</h2>';
      $out .= '<p class="text-center">';
        $out .= 'Limit to ';
        $out .= '<button class="limitButton btn btn-success" id="limitAll">All monsters</button>';
        $out .= ' ';
        $out .= '<button class="limitButton btn btn-primary" id="limitTrainable"><i class="glyphicon glyphicon-headphones"></i> monsters I can TRAIN on</button>';
        $out .= ' ';
        $out .= '<button class="limitButton btn btn-primary" id="limitFightable"><i class="glyphicon glyphicon-flash"></i> monsters I can FIGHT</button>';
        $out .= ' ';
        $out .= '<button class="limitButton btn btn-primary" id="limitNever"><i class="glyphicon glyphicon-remove"></i> monsters I have NEVER trained on</button>';
      $out .= '</p>';
    $out .= '</section>';

    $allMonsters->sort('level, title');
    $previousLevel = 1;
    $out .= '<p class="label label-danger">Level 1</p>';
    $out .= '<div class="grid">';
    foreach ($allMonsters as $m) {
      if ($m->level !== $previousLevel) { $out .= '</div><p class="label label-danger">Level '.$m->level.'</p><div class="grid">'; }
      if (!$user->isSuperuser() && $user->isLoggedin() && isset($player)) {
        if ($player->equipment->has('name=memory-helmet')) {
          $m = setMonstersActivity($player, $m);
        }
      } else { // Never trained (for admin)
        $m->isTrainable = 1;
        $m->isFightable = 1;
        $m->lastTrainingInterval = -1;
        $m->waitForTrain = 0;
      }
      if (isset($m->image)) {
        $class = 'grid-item';
        if ($m->lastTrainingInterval == -1) { // Never trained
            $class .= ' grid-item--width2 trainable neverTrained';
        } else {
          if ($m->isFightable) {
            $class .= ' grid-item--width3 trainable fightable';
          } else {
            if ($m->isTrainable) {
              $class .= ' grid-item--width2 trainable';
            }
          }
        }
        if ($m->special) { $class .= ' special'; }
        $out .= '<div class="'.$class.' monsterDiv text-center">';
        if ($m->isTrainable) {
          if ($m->special) {
            $out .= '<span class="label label-danger">'.$m->title.'</span>';
          } else {
            $out .= '<span class="label label-primary">'.$m->title.'</span>';
          }
        }
        $out .= '<img class="monsterInfo img-thumbnail" data-href="'.$m->url.'" data-toggle="tooltip" data-html="true" title="'.$m->title.'<br />Level '.$m->level.'<br />'.$m->summary.'" src="'.$m->image->url.'" alt="image" />';
        $out .= '</div>';
      }
      $previousLevel = $m->level;
    }
    $out .= '</div>';

    $out .= '</section>';
  } else {
    $out = '';
    $shop = $pages->get("name=shop");
    $out .= '<p>Your group has to buy the <a href="'.$shop->url.'/details/electronic-visualizer">Electronic Visualizer</a> to access this page.</p>';
  }
} else {
  $out = '';
  $out .= '<p>You need to log in to access this page.</p>';
}

echo $out;

include("./foot.inc"); 
?>
