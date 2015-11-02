<?php
  include("./head.inc"); 

  // TODO : Test player login

  echo '<div ng-app="exerciseApp">';
  
  // Get player's equipment to set scores alternatives
  $weaponRatio = 0;
  $protectionRatio = 0;
  if ($player && $player->equipment->count() > 0) {
    foreach ($player->equipment as $equipment) {
      if ($equipment->parent()->name === 'weapons') {
        $weaponRatio += $equipment->XP;
      }
      if ($equipment->parent->name === 'protections') {
        $protectionRatio += $equipment->HP;
      }
    }
    // Limit to 5
    if ($weaponRatio > 5) { $weaponRatio = 5; }
    if ($protectionRatio > 5) { $protectionRatio = 5; }
  }

  // Get exercise type
  include('./exTemplates/'.$page->type->name.'.php');

  echo '</div>';

  include("./foot.inc"); 
?>
