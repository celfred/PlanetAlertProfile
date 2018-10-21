<?php
  $out = '';
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    $out .= '<table class="table table-condensed table-hover">';
      $teamPlaces = 0;
      $teamEquipment = 0;
      $teamHealth = array();
      $teamPart = array();
      $teamKarma = 0;

      $out .= '<tr>';
      $out .= '<td colspan="7"><h3><?php echo $reportTitle; ?></h3></td>';
      $out .= '</tr>';
      $out .= '<tr>';
      if (!$input->get['pages2pdf']) {
        $out .= '<td></td>';
        $out .= '<th>Karma</th>';
        $out .= '<th><i class="glyphicon glyphicon-signal" data-toggle="tooltip" title="Level"></i></th>';
        $out .= '<th><i class="glyphicon glyphicon-picture" data-toggle="tooltip" title="Lieux"></i></th>';
        $out .= '<th><i class="glyphicon glyphicon-shopping-cart" data-toggle="tooltip" title="Équipement"></i></th>';
        $out .= '<th><i class="glyphicon glyphicon-usd" data-toggle="tooltip" title="Gold coins"></i></th>';
        $out .= '<th><i class="glyphicon glyphicon-heart" data-toggle="tooltip" title="Health points"></i></th>';
      } else {
        $out .= '<td></td>';
        $out .= '<th>Karma</th>';
        $out .= '<th>Level</th>';
        $out .= '<th>Lieux</th>';
        $out .= '<th>Équipement</th>';
        $out .= '<th>Gold coins</th>';
        $out .= '<th>Health points</th>';
      }
    $out .= '</tr>';
    foreach($allPlayers as $player) {
      $teamKarma += $player->reputation;
      $out .= '<tr>';
      $out .= '<th>';
      if (!$input->get['pages2pdf']) {
        $out .= $player->title;
      } else {
        $out .= $player->title.' '.$player->lastName;
      }
      $out .= '</th>';

      $out .= '<td>';
      $out .= $player->reputation;
      $out .= '</td>';
      $out .= '<td class="text-center">';
      $out .= $player->level;
      $out .= '</td>';
      $out .= '<td>';
      if ($player->places->count > 0) {
        $list = '';
        foreach ($player->places as $place) {
          $list .= $place->title."<br />";
        }
      $out .= '<span data-toggle="tooltip" data-html="true" title="'.$list.'">'.$player->places->count.'</span>';
        $teamPlaces += 1;
      }
      $out .= '</td>';
      $out .= '<td>';
      if ($player->equipment->count > 0) {
        $list = '';
        foreach ($player->equipment as $item) {
          $list .= $item->title."<br />";
        }
        $out .= '<span data-toggle="tooltip" data-html="true" title="'.$list.'">'.$player->equipment->count.'</span>';
        $teamEquipment += 1;
      }
      $out .= '</td>';
      $out .= '<td>';
      $out .= $player->GC;
      $out .= '</td>';
      $out .= '<td>';
      $out .= $player->HP.'/50';
      array_push($teamHealth, $player->HP);
      $out .= '</td>';
      $out .= '</tr>';
    }
    
    // Team stats
    $out .= '<tr>';
    $out .= '<th>';
    $out .= 'Totaux et moyennes';
    $out .= '</th>';
    $out .= '<th>';
    $out .= '<span data-toggle="tooltip" title="Karma moyen">'. (int) round($teamKarma/$allPlayers->count()) . '</span>';
    $out .= '</th>';
    $out .= '<th>';
    $out .= '</th>';
    $out .= '<th>';
    $out .= $teamPlaces;
    $out .= '</th>';
    $out .= '<th>';
    $out .= $teamEquipment;
    $out .= '</th>';
    $out .= '<th>';
    $out .= '</th>';
    $out .= '<th>';
    $out .= calculate_average($teamHealth);
    $out .= '</th>';
    $out .= '</tr>';
  $out .= '</table>';
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
