  <?php
    // Redefine function for PDF ?
    function pluralize($count, $singular, $plural = false) {
      if (!$plural) $plural = $singular . 's';
      return ($count == 1 || $count == 0 ? $singular : $plural) ;
    }

    $counter = 0;
    foreach($allPlayers as $player) {
      $positiveEvents = $player->find("template=event, date>=$period->dateStart, date<=$period->dateEnd, (task.XP>0), (task.GC>0)")->sort("date");
      $negativeEvents = $player->find("template=event, date>=$period->dateStart, date<=$period->dateEnd, task.HP<0")->sort("date");
      $posAttitudeEvents = $positiveEvents->find("task.category.name=attitude");
      $posIndividualEvents = $positiveEvents->find("task.category.name=individual-work");
      $posGroupEvents = $positiveEvents->find("task.category.name=groupwork");
      $negAttitudeEvents = $negativeEvents->find("task.category.name=attitude");
      $negIndividualEvents = $negativeEvents->find("task.category.name=individual-work");
      $negGroupEvents = $negativeEvents->find("task.category.name=groupwork");
      echo '<table class="cm1Table" style="width:12cm">';
      echo '<tr>';
        echo '<td>';
          echo '<h4 class="text-center">';
          echo $player->title.' '.$player->lastName;
          echo ' - <b>Bilan '.$period->title.'</b>';
          echo '</h4>';
          if (count($negativeEvents) == 0) {
            echo '<h3 class="text-center">BRAVO ! Aucune action négative !</h6>';
          };
          if (count($negativeEvents) > 4) {
            echo '<h3 class="text-center">ATTENTION ! Trop d\'actions négatives !</h6>';
          };
        echo '</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td class="text-left">';
          // Attitude
          $posResult = '<span>'.count($posAttitudeEvents).' '.pluralize(count($posAttitudeEvents), "action positive", "actions positives").'</span>';
          $negResult = '<span>'.count($negAttitudeEvents).' '.pluralize(count($negAttitudeEvents), "action négative", "actions negatives").'</span>';
          echo '<p><b>Attitude</b> : '.$posResult.' - '.$negResult.'</p>';
          if (count($negAttitudeEvents) > 0) {
            echo '<ul>';
            foreach ($negAttitudeEvents as $e) {
              echo '<li>'.strftime("%d/%m", $e->date).' : '.$e->title;
              if ($e->summary != '') {
                echo ' → '.$e->summary;
              }
              echo '</li>';
            }
            echo '</ul>';
          }
        echo '</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td class="text-left">';
          // Individual work
          $posResult = '<span>'.count($posIndividualEvents).' '.pluralize(count($posIndividualEvents), "action positive", "actions positives").'</span>';
          $negResult = '<span>'.count($negIndividualEvents).' '.pluralize(count($negIndividualEvents), "action négative", "actions negatives").'</span>';
          echo '<p><b>Travail individuel</b> : '.$posResult.' - '.$negResult.'</p>';
          if (count($negIndividualEvents) > 0) {
            echo '<ul>';
            foreach ($negIndividualEvents as $e) {
              echo '<li>'.strftime("%d/%m", $e->date).' : '.$e->title;
              if ($e->summary != '') {
                echo ' → '.$e->summary;
              }
              echo '</li>';
            }
            echo '</ul>';
          }
        echo '</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td class="text-left">';
          // Group work
          $posResult = '<span>'.count($posGroupEvents).' '.pluralize(count($posGroupEvents), "action positive", "actions positives").'</span>';
          $negResult = '<span>'.count($negGroupEvents).' '.pluralize(count($negGroupEvents), "action négative", "actions negatives").'</span>';
          echo '<p><b>Travail de groupe</b> : '.$posResult.' - '.$negResult.'</p>';
        echo '</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td class="text-left" style="height:1.3cm;">';
        echo "&nbsp;<u>Signature</u> ⇒ ";
        echo '</td>';
      echo '</tr>';
      echo '</table>';
      echo '<br />';
      if (in_array($counter, array(4, 8, 12, 16, 20, 24, 28, 32)) && $input->get['pages2pdf']) {
        echo '<pagebreak />';
      }
      $counter++;
    }
?>
