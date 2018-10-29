<?php namespace ProcessWire;
  // Get user info
  if ($user->isSuperuser()) {
    $player = $pages->get("template=player, name=test");
  } else {
    if ($user->hasRole('teacher')) {
      $player = $pages->get("template=player, name=test");
    } else {
      $player = $pages->get("template=player, login=$user->name");
    }
  }

  $redirectUrl = $player->url;
  $out = '<div ng-controller="FightCtrl" ng-init="init(\''.$pages->get("name=service-pages")->url.'\', \''.$page->id.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$weaponRatio.'\', \''.$protectionRatio.'\', \''.$pages->get("name=submit-fight")->url.'\')">';

  $out .= '<div id="exHeader">';
  $out .= '<h2 class="row well text-center">';
  $out .= '<span class="label label-default">'.__("Monster fight").'</span>';
  $out .= '<span class="">  ';
  $out .= '<img class="pull-left" src="'.$page->image->getCrop("thumbnail")->url.'" alt="Monster" />';
  $out .= $page->title;
  $out .= ' vs. ';
  $out .= $player->title;
  $out .= '</span>';
  $out .= '<span class="pull-right">';
  $out .= '<span class="avatarContainer">';
  if ($player->avatar) {
    $out .= '<img class="" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
  } else {
    $out .= '<Avatar>';
  }
  if ($weaponRatio > 0) { // Player has weapons
    $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
    if ($bestWeapon->id && $bestWeapon->image) {
        $out .= '<img ng-class="{weapon:true, superpose:true, blink:correct}" src="'.$bestWeapon->image->getCrop("small")->url.'" alt="'.$bestWeapon->title.'" />';
    }
  }
  if ($protectionRatio > 0) { // Player has protections
    $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
    if ($bestProtection->id && $bestProtection->image) {
        $out .= '<img ng-class="{protection:true, superpose:true, blink:wrong}" src="'.$bestProtection->image->getCrop("small")->url.'" alt="'.$bestProtection->title.'" />';
    }
  }
  $out .= '</span>';
  $out .= '</span>';
  $out .= '</h2>';
  $out .= '<h3 class="row well text-center">';
  $out .= $page->summary;
  $out .= '</h3>';
  $out .= '</div>';

  // Scoring table
  include("scoring.inc"); 

  // First step : Display exercise summary to prepare the activity
  $out .= '<h3 class="alert alert-info" role="alert">';
  $out .= '<strong><span class="glyphicon glyphicon-hand-up"></span> '.$page->type->summary.'</strong>';
  $out .= '<span class="glyphicon glyphicon-question-sign pull-right" data-toggle="tooltip" data-html="true" title="'.__("Attack = I know!<br />Dodge = I don't know.<br />Tip : Use 'Enter' to play faster ;)").'"></span>';
  $out .= '<br /><br />';
  if ($user->language->name != 'french') {
    $page->of(false);
    if ($page->type->getLanguageValue($french) != '') {
      echo '<a class="" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
      echo '<div class="collapse" id="collapseDiv">';
      echo '<div class="well">';
      echo nl2br($page->type->getLanguageValue($french));
      echo '</div>';
      echo '</div>';
    }
  }
  $out .= '<br /><br />';
  $out .= '<button class="btn btn-primary btn-lg btn-block text-center" ng-disabled="waitForStart" ng-click="startFight()" id="startFight">'.__("I understand. Start the fight !").'</button>';
  $out .= '</h3>';

  $out .= '<div id="fightForm" ng-class="{row:true, hidden: wonFight}">';
  $out .= '<div class="text-left">';
  if ($page->image) {
    $out .= '<img class="pull-left squeeze" src="'.$page->image->getCrop('thumbnail')->url.'" alt="Monster" />';
  } else {
    $out .= '<img class="squeeze" src="'.$page->type->photo->eq(0)->getCrop('thumbnail')->url.'" alt="Antenna" />';
  }
  $out .= '<span ng-class="{damage:true, blink: true, hidden: hideMonsterDamage}">- {{monsterDamage}}'.__("HP").'</span>';
  $out .= '<div ng-class="{\'bubble-left\': true, explode: correct}">';
  $out .= '<h2 class="jumbleW inline" ng-repeat="w in word track by $index">';
  $out .= '<span class="label label-primary" ng-click="pickWord(w, $index)" ng-bind-html="w|paTags"></span>';
  $out .= '</h2>';
  $out .= ' <h3><span class="label label-danger blink" ng-show="showCorrection">{{showCorrection}} {{feedback}}</span></h3> ';
  $out .= '<button class="btn btn-danger btn-xs" ng-click="clear()">'.__("Try again").'</button>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="text-right">';
  $out .= '<div class="bubble-right" style="width: 50%;">';
  $out .= '<h3 class="text-left">{{playerAnswer}}</h3>';
  $out .= '&nbsp;';
  $out .= '<button ng-click="attack()" ng-disabled="waitForStart" class="btn btn-success">'.__("Attack !").'</button>';
  $out .= '&nbsp;';
  $out .= '<button ng-click="dodge()" ng-disabled="waitForStart" class="btn btn-info">'.__("Dodge").'</button>';
  $out .= '&nbsp;';
  $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.__("Attack = I know!<br />Dodge = I don't know.<br />Tip : Use 'Enter' to play faster ;)").'"></span>';
  $out .='</div>';
  $out .='</h3>';
  $out .= '<span class="pull-right">';
  $out .= '<span ng-class="{damage:true, blink: true, hidden: hidePlayerDamage}">- {{playerDamage}}'.__("HP").'</span>';
  $out .= '<span class="avatarContainer">';
  if ($player->avatar) {
    $out .= '<img class="" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
  } else {
    $out .= '<Avatar>';
  }
  if ($bestWeapon->id && $bestWeapon->image) {
    $out .= '<img ng-class="{weapon:true, superpose:true, blink:correct}" src="'.$bestWeapon->image->getCrop("small")->url.'" alt="'.$bestWeapon->title.'" />';
  }
  if ($bestProtection->id && $bestProtection->image) {
    $out .= '<img ng-class="{protection:true, superpose:true, blink:wrong}" src="'.$bestProtection->image->getCrop("small")->url.'" alt="'.$bestProtection->title.'" />';
  }
  $out .= '</span>';
  $out .= '</span>';
  $out .='</div>';
  $out .= '<h3 class="text-center">'.$page->instructions.'</h3>';
  $out .= '</div>';
  $out .= '</div>';

  echo $out;
?>
