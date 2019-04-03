<?php namespace ProcessWire;
  $out .= '<span class="avatarContainer">';
    if ($player->avatar) {
      $out .= '<img class="" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
    } else {
      $out .= '<Avatar>';
    }
    if ($weaponRatio > 0) { // Player has weapons
      $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
      if ($bestWeapon->image) {
          $out .= '<img ng-class="{weapon:true, superpose:true}" src="'.$bestWeapon->image->getCrop("small")->url.'" alt="'.$bestWeapon->title.'" />';
      }
    }
    if ($protectionRatio > 0) { // Player has protections
      $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
      if ($bestProtection->image) {
          $out .= '<img ng-class="{protection:true, superpose:true}" src="'.$bestProtection->image->getCrop("small")->url.'" alt="'.$bestProtection->title.'" />';
      }
    }
  $out .= '</span>';
?>
