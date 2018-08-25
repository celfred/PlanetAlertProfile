<?php namespace ProcessWire;
  if (!$config->ajax) {
    include("./head.inc"); 

    if ($user->isLoggedin() || $user->isSuperuser()) {
      echo '<div ng-app="exerciseApp">';
      // Get player's equipment to set scores alternatives
      $weaponRatio = 0;
      $protectionRatio = 0;
      if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
        $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
        $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
      }
      if (isset($bestWeapon)) { $weaponRatio = $bestWeapon->XP; }
      if (isset($bestProtection)) { $protectionRatio = $bestProtection->HP; }

      // Get exercise type
      include('./exTemplates/'.$page->type->name.'.php');

      echo '</div>';
    } else {
      echo '<div class="well"><p>You have to log in to see this page.</p></div>';
    }

    include("./foot.inc"); 
  } else { // Ajax monster infos
    $out = '';
    $out .= '<div class="row">';
    $out .= '<div class="col-sm-4 text-center">';
    $out .= '<h3><span class="label label-primary">'.$page->title.'</span></h3>';
    $out .= '<p>Level '.$page->level.'</p>';
    $out .= '<small>Type : '.$page->type->title.' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$page->type->summary.'"></span></small>';
      $out .= '<h3 class="thumbnail">';
      if ($page->image) { $mini = '<img src="'.$page->image->getCrop('big')->url.'" alt="Photo" />'; }
      $out .= $mini;
      $out .= '</h3>';
    $out .= '</div>';
    $out .= '<div class="col-sm-8 text-left">';
      $page->of(false);
      $out .= '<p class="text-center"><h3>'.$page->summary.' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$page->summary->getLanguageValue($french).'"></i></h3></p>';
      // Get player's stats
      if ($user->isLoggedin()) {
        $player = $pages->get("template='player', login=$user->name");
        if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
          $page = setMonster($player, $page);
          if ($page->fightNb > 0) {
          } else {
            $page->fightNb = 0;
          }
        } else { // Never trained (for admin)
          $page->isTrainable = 1;
          $page->isFightable = 1;
          $page->lastTrainingInterval = -1;
          $page->waitForTrain = 0;
        }
        $out .= "<br /><br />";
        $out .= '<p>Your activity :</p>';
        $out .= '<ul>';
        $out .= '<li><i class="glyphicon glyphicon-headphones"></i> <span class="label label-primary">'.$page->utGain.' UT</span>';
        if ($page->isTrainable == 1) {
          $helmet = $pages->get("name=memory-helmet");
          $out .= '→ <a class="btn btn-primary" href="'.$pages->get("name=underground-training")->url.'?id='.$page->id.'"><img src="'.$helmet->image->getCrop("mini")->url.'" alt="Use the Memory Helmet" /> Use the Memory Helmet !</a>';
          if ($page->lastTrainingInterval != -1) {
            $out .= '<p>Last training session : '.$page->lastTrainingInterval.'</p>';
          } else {
            $out .= '<p>You have never trained on this monster.</p>';
          }
        } else {
          if ($page->lastTrainingInterval == 0) {
            $out .= '<p>Last training session : Today !</p>';
          } else {
            $out .= '<p>Last training session : '.$page->lastTrainingInterval.'</p>';
          }
          if ($page->waitForTrain == 1) {
            $out .= '<p>You have to wait for tomorrow before training again on this monster.</p>';
          } else {
            $out .= '<p>You have to wait '.$page->waitForTrain.' days before training again on this monster.</p>';
          }
        }
        $out .= '</li>';
        $out .= '<li><i class="glyphicon glyphicon-flash"></i> <span class="label label-primary">'.$page->fightNb.' fight·s</span>';
        if ($page->isFightable == 1) {
          $out .= '→ <a class="btn btn-primary" href="'.$page->url.'"><i class="glyphicon glyphicon-flash"></i> Fight  the monster !</a>';
          if ($page->lastFightInterval != -1) {
            $out .= '<p>Last fight : '.$page->lastFightInterval.'</p>';
          } else {
            $out .= '<p>You have never fought this monster.</p>';
          }
        } else {
          if ($page->lastFightInterval == -1) {
            $out .= '<p>You must have 20UT to fight this monster.</p>';
          } else {
            if ($page->lastTrainingInterval != 0) {
              $out .= '<p>You have to wait '.$page->waitForFight.' days to fight this monster.</p>';
            } else {
              $out .= '<p>You can\'t fight this monster. You have used the Memory Helmet today so '.$page->title.' walked away.</p>';
            }
          }
        }
        // Show last result
        if (isset($page->quality) && $page->fightNb > 0) {
          $out .= '<p>Average result : '.averageLabel($page->quality).'</p>';
        }
        $out .= '</li>';
        $out .= '</ul>';
      }
    $out .= '</div>';
    $out .= '<div class="col-sm-8 text-left">';
      $out .= "<br /><br />";
      if ($page->mostTrained && $page->mostTrained->team->name != "no-team" ) { $team = ' ['.$page->mostTrained->team->title.']'; } else { $team = ''; }
      $out .= '<p><i class="glyphicon glyphicon-thumbs-up"></i> Most trained player : ';
      if ($page->mostTrained) {
        $out .='<span class="label label-success">'.$page->mostTrained->title.$team.' → '.$page->best.'UT</span>';
      } else {
        $out .='Nobody !';
      }
      $out .= '</p>';
    $out .= '</div>';
    $out .= '</div>';

    echo $out;
  }
?>
