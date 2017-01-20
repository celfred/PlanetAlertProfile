<?php
  // Get user info
  if ($user->isSuperuser()) {
    $player->title = 'ADMIN';
  } else {
    $player = $pages->get("template=player, login=$user->name");
  }

  $redirectUrl = $player->url;
  $out = '<div ng-controller="FightCtrl" ng-init="init(\''.$pages->get("name=service-pages")->url.'\', \''.$page->id.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$weaponRatio.'\', \''.$protectionRatio.'\', \''.$pages->get("name=submit-fight")->url.'\')">';

  $out .= '<h2 class="row well text-center">';
  $out .= '<span class="label label-default">Monster fight</span>';
  $out .= '<span class="">  ';
  $out .= '<img class="pull-left" src="'.$page->image->url.'" alt="Avatar" />';
  $out .= $page->title;
  $out .= ' vs. ';
  $out .= $player->title;
  $out .= '</span>';
  $out .= '<span class="pull-right">';
  $out .= '<span class="avatarContainer">';
  if ($player->avatar) {
    $out .= '<img class="" src="'.$player->avatar->getThumb("thumbnail").'" alt="Avatar" />';
  } else {
    $out .= '<Avatar>';
  }
  if ($weaponRatio > 0) { // Player has weapons
    $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
    if ($bestWeapon->id && $bestWeapon->image) {
        $out .= '<img ng-class="{weapon:true, superpose:true, blink:correct}" src="'.$bestWeapon->image->getThumb("small").'" alt="'.$bestWeapon->title.'" />';
    }
  }
  if ($protectionRatio > 0) { // Player has protections
    $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
    if ($bestProtection->id && $bestProtection->image) {
        $out .= '<img ng-class="{protection:true, superpose:true, blink:wrong}" src="'.$bestProtection->image->getThumb("small").'" alt="'.$bestProtection->title.'" />';
    }
  }
  $out .= '</span>';
  $out .= '</span>';
  $out .= '</h2>';
  $out .= '<h3 id="exTitle" class="row well text-center">';
  $out .= $page->summary;
  $out .= '</h3>';

  // Scoring table
  $out .= '<div id="energyDiv" class="row text-center">';
  // Monster's health points
  $out .= '<div class="row text-center">';
  $out .= '<div class="col-sm-3">';
  if ($page->image) {
    $out .= '<img class="pull-right" src="'.$page->image->getThumb("mini").'" alt="Avatar" />';
  }
  $out .= '</div>';
  $out .= '<div class="col-sm-6">';
  $out .= '<div class="progress progress-lg" data-toggle="tooltip" title="Health points">';
  $out .= '<div class="progress-bar progress-bar-striped progress-bar-danger active" role="progressbar" aria-valuenow="{{monsterHP}}" aria-valuemin="0" aria-valuemax="100" style="width:{{monsterHP}}%">';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="col-sm-3">';
  $out .= '</div>';
  $out .= '</div>';
  // Player's health points
  $out .= '<div class="row text-center">';
  $out .= '<div class="col-sm-3">';
  if ($player->avatar) {
    $out .= '<img class="pull-right" src="'.$player->avatar->getThumb("mini").'" alt="Avatar" />';
  } else {
    $out .= '<Avatar>';
  }
  $out .= '</div>';
  $out .= '<div class="col-sm-6">';
  $out .= '<div class="progress progress-lg" data-toggle="tooltip" title="Health points">';
  $out .= '<div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="{{playerHP}}" aria-valuemin="0" aria-valuemax="100" style="width:{{playerHP}}%">';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="col-sm-3">';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';

  // First step : Display exercise summary to prepare the activity
  $out .= '<h3 class="alert alert-info" role="alert">';
  $out .= '<strong><span class="glyphicon glyphicon-hand-up"></span> '.$page->type->summary.'</strong>';
  $out .= '<span class="glyphicon glyphicon-question-sign pull-right" data-toggle="tooltip" data-html="true" title="Attack = I know!<br />Dodge = I don\'t know.<br />Tip : Use \'Enter\' to play faster ;)"></span>';
  $out .= '<br /><br />';
  $out .= '<a role="button" class="" data-toggle="collapse" href="#collapseDiv'.$n->id.'" aria-expanded="false" aria-controls="collapseDiv">[French version]</a>';
  $out .= '<div class="collapse" id="collapseDiv'.$n->id.'"><div class="well">';
  if ($page->type->frenchSummary != '') {
    $out .= $page->type->frenchSummary;
  } else {
    $out .= 'French version in preparation, sorry ;)';
  }
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<br /><br />';
  $out .= '<button class="btn btn-primary btn-lg btn-block text-center" ng-disabled="waitForStart" ng-click="startFight()" id="startFight">I understand. Start the fight ! </button>';
  $out .= '</h3>';

  $out .= '<div id="fightForm" class="row">';
  $out .= '<div class="text-left">';
  /* $out .= 'Monsterpower : {{monsterPower}} / playerPower:{{playerPower}}'; */
  if ($page->image) {
    $out .= '<img class="pull-left squeeze" src="'.$page->image->url.'" alt="Avatar" />';
  } else {
    $out .= '<img class="squeeze" src="'.$page->type->photo->eq(0)->getThumb('thumbnail').'" alt="Antenna" />';
  }
  $out .= '<span ng-class="{damage:true, blink: true, hidden: hideMonsterDamage}">- {{monsterDamage}}HP</span>';
  $out .= '<div ng-class="{\'bubble-left\': true, explode: correct}">';
  $out .= '<h3 class="inline" ng-bind-html="word"></h3>&nbsp;';
  $out .= '<h2 class="inline"><span class="label label-danger blink" ng-bind-html="showCorrection"></span></h2>  ';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="text-right">';
  $out .= '<div class="bubble-right">';
  $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="Your answer" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
  $out .= '&nbsp;';
  $out .= '<button ng-click="attack()" class="btn btn-success">Attack!</button>';
  $out .= '&nbsp;';
  $out .= '<button ng-click="dodge()" class="btn btn-info">Dodge</button>';
  $out .= '&nbsp;';
  $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="Attack = I know!<br />Dodge = I don\'t know.<br />Tip : Use \'Enter\' to play faster ;)"></span>';
  $out .='</div>';
  $out .='</h3>';
  $out .= '<span class="pull-right">';
  $out .= '<span ng-class="{damage:true, blink: true, hidden: hidePlayerDamage}">- {{playerDamage}}HP</span>';
  $out .= '<span class="avatarContainer">';
  if ($player->avatar) {
    $out .= '<img class="" src="'.$player->avatar->getThumb("thumbnail").'" alt="Avatar" />';
  } else {
    $out .= '<Avatar>';
  }
  if ($bestWeapon->id && $bestWeapon->image) {
    $out .= '<img ng-class="{weapon:true, superpose:true, blink:correct}" src="'.$bestWeapon->image->getThumb("small").'" alt="'.$bestWeapon->title.'" />';
  }
  if ($bestProtection->id && $bestProtection->image) {
    $out .= '<img ng-class="{protection:true, superpose:true, blink:wrong}" src="'.$bestProtection->image->getThumb("small").'" alt="'.$bestProtection->title.'" />';
  }
  $out .= '</span>';
  $out .= '</span>';
  $out .='</div>';
  $out .= '</div>';
  $out .= '</div>';

  echo $out;
?>

