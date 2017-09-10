<?php
namespace ProcessWire;
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
?>
