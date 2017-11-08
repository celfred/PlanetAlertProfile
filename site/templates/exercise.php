<?php namespace ProcessWire;
  if (!$config->ajax) {
    include("./head.inc"); 

    if ($user->isLoggedin() || $user->isSuperuser()) {
      echo '<div ng-app="exerciseApp">';
      // Get player's equipment to set scores alternatives
      $weaponRatio = 0;
      $protectionRatio = 0;
      if (!$user->isSuperuser()) {
        $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
        $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
      }
      if ($bestWeapon->id) { $weaponRatio = $bestWeapon->XP; }
      if ($bestProtection->id) { $protectionRatio = $bestProtection->HP; }

      // Get exercise type
      include('./exTemplates/'.$page->type->name.'.php');

      echo '</div>';
    } else {
      echo '<div class="well"><p>You have to log in to see this page.</p></div>';
    }

    include("./foot.inc"); 
  } else {
    $out = '';
    $out .= '<div class="row">';
    $out .= '<div class="col-sm-4 text-center">';
    $out .= '<h3><span class="label label-primary">'.$page->title.'</span></h3>';
    $out .= '<p>Level '.$page->level.'</p>';
      $out .= '<h3 class="thumbnail">';
      if ($page->image) { $mini = '<img src="'.$page->image->getCrop('big')->url.'" alt="Photo" />'; }
      $out .= $mini;
      $out .= '</h3>';
    $out .= '</div>';
    $out .= '<div class="col-sm-8 text-justify">';
      $out .= '<br/>';
      $out .= '<p>'.$page->summary.' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$page->frenchSummary.'"></i></p>';
      // Get player's stats
      if ($user->isLoggedin()) {
        $player = $pages->get("template='player', login=$user->name");
        if (!$user->isSuperuser()) {
          $page = setMonstersActivity($player, $page);
        } else { // Never trained (for admin)
          $page->isTrainable = 1;
          $page->isFightable = 1;
          $page->lastTrainingInterval = -1;
          $page->waitForTrain = 0;
        }
        $out .= '<ul>';
        $out .= '<li><i class="glyphicon glyphicon-headphones"></i> UT gained : <span class="label label-primary">'.$page->utGain.'</span>';
        if ($page->isTrainable == 1) {
          $helmet = $pages->get("name=memory-helmet");
          $out .= '→ <button class="btn"><a href="'.$pages->get("name=underground-training")->url.'?id='.$page->id.'"><img src="'.$helmet->image->getCrop("mini")->url.'" alt="Use the Memory Helmet" /> Use the Memory Helmet !</a></button>';
        }
        $out .= '</li>';
        $out .= '<li><i class="glyphicon glyphicon-flash"></i> Nb of fights : <span class="label label-primary">'.$page->allFightsNb.'</span>';
        if ($page->isFightable == 1) {
          $out .= '→ <button class="btn"><a href="'.$pages->url.'"><i class="glyphicon glyphicon-flash"></i> Fight  the monster !</a></button>';
        }
        $out .= '</li>';
        $out .= '</ul>';
      }
    $out .= '</div>';
    $out .= '</div>';
    echo $out;
  }
?>
