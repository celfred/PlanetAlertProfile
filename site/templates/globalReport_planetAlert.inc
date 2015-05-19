<table class="table table-condensed table-hover">
  <tr>
    <td colspan="7"><h3><?php echo $reportTitle; ?></h3></td>
  </tr>
  <tr>
  <?php
    $teamPlaces = 0;
    $teamEquipment = 0;
    $teamHealth = array();
    $teamPart = array();
    $teamKarma = 0;

    if (!$input->get['pages2pdf']) {
      ?>
      <td></td>
      <th>Karma</th>
      <th><i class="glyphicon glyphicon-signal" data-toggle="tooltip" title="Level"></i></th>
      <th><i class="glyphicon glyphicon-picture" data-toggle="tooltip" title="Lieux"></i></th>
      <th><i class="glyphicon glyphicon-shopping-cart" data-toggle="tooltip" title="Équipement"></i></th>
      <th><i class="glyphicon glyphicon-usd" data-toggle="tooltip" title="Gold coins"></i></th>
      <th><i class="glyphicon glyphicon-heart" data-toggle="tooltip" title="Health points"></i></th>
      <?php
    } else {
      ?>
      <td></td>
      <th>Karma</th>
      <th>Level</th>
      <th>Lieux</th>
      <th>Équipement</th>
      <th>Gold coins</th>
      <th>Health points</th>
      <?php
    }
  ?>
  </tr>

  <?php
    foreach($allPlayers as $player) {
      $teamKarma += $player->karma;
      echo '<tr>';
      echo '<th>';
      if (!$input->get['pages2pdf']) {
        echo $player->title;
      } else {
        echo $player->title.' '.$player->lastName;
      }
      echo '</th>';

      echo '<td>';
      echo $player->karma;
      echo '</td>';
      echo '<td class="text-center">';
      echo $player->level;
      echo '</td>';
      echo '<td>';
      if ($player->places->count > 0) {
        $list = '';
        foreach ($player->places as $place) {
          $list .= $place->title."<br />";
        }
      echo '<span data-toggle="tooltip" data-html="true" title="'.$list.'">'.$player->places->count.'</span>';
        $teamPlaces += 1;
      }
      echo '</td>';
      echo '<td>';
      if ($player->equipment->count > 0) {
        $list = '';
        foreach ($player->equipment as $item) {
          $list .= $item->title."<br />";
        }
        echo '<span data-toggle="tooltip" data-html="true" title="'.$list.'">'.$player->equipment->count.'</span>';
        $teamEquipment += 1;
      }
      echo '</td>';
      echo '<td>';
      echo $player->GC;
      echo '</td>';
      echo '<td>';
      echo $player->HP.'/50';
      array_push($teamHealth, $player->HP);
      echo '</td>';
      echo '</tr>';
    }
    
    // Team stats
    echo '<tr>';
    echo '<th>';
    echo 'Totaux et moyennes';
    echo '</th>';
    echo '<th>';
    echo '<span data-toggle="tooltip" title="Karma moyen">'. (int) round($teamKarma/$allPlayers->count()) . '</span>';
    echo '</th>';
    echo '<th>';
    echo '</th>';
    echo '<th>';
    echo $teamPlaces;
    echo '</th>';
    echo '<th>';
    echo $teamEquipment;
    echo '</th>';
    echo '<th>';
    echo '</th>';
    echo '<th>';
    echo calculate_average($teamHealth);
    echo '</th>';
    echo '</tr>';
?>
</table>
