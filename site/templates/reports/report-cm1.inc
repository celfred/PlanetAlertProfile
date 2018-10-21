<?php
  $out = '';
  // Redefine function for PDF ?
  function pluralize($count, $singular, $plural = false) {
    if (!$plural) $plural = $singular . 's';
    return ($count == 1 || $count == 0 ? $singular : $plural) ;
  }

  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
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
      $out .= '<table class="cm1Table" style="width:12cm">';
      $out .= '<tr>';
        $out .= '<td>';
          $out .= '<h4 class="text-center">';
          $out .= $player->title.' '.$player->lastName;
          $out .= ' - <b>Bilan '.$period->title.'</b>';
          $out .= '</h4>';
          if (count($negativeEvents) == 0) {
            $out .= '<h3 class="text-center">BRAVO ! Aucune action négative !</h6>';
          };
          if (count($negativeEvents) > 4) {
            $out .= '<h3 class="text-center">ATTENTION ! Trop d\'actions négatives !</h6>';
          };
        $out .= '</td>';
      $out .= '</tr>';
      $out .= '<tr>';
        $out .= '<td class="text-left">';
          // Attitude
          $posResult = '<span>'.count($posAttitudeEvents).' '.pluralize(count($posAttitudeEvents), "action positive", "actions positives").'</span>';
          $negResult = '<span>'.count($negAttitudeEvents).' '.pluralize(count($negAttitudeEvents), "action négative", "actions negatives").'</span>';
          $out .= '<p><b>Attitude</b> : '.$posResult.' - '.$negResult.'</p>';
          if (count($negAttitudeEvents) > 0) {
            $out .= '<ul>';
            foreach ($negAttitudeEvents as $e) {
              $out .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->title;
              if ($e->summary != '') {
                $out .= ' → '.$e->summary;
              }
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
        $out .= '</td>';
      $out .= '</tr>';
      $out .= '<tr>';
        $out .= '<td class="text-left">';
          // Individual work
          $posResult = '<span>'.count($posIndividualEvents).' '.pluralize(count($posIndividualEvents), "action positive", "actions positives").'</span>';
          $negResult = '<span>'.count($negIndividualEvents).' '.pluralize(count($negIndividualEvents), "action négative", "actions negatives").'</span>';
          $out .= '<p><b>Travail individuel</b> : '.$posResult.' - '.$negResult.'</p>';
          if (count($negIndividualEvents) > 0) {
            $out .= '<ul>';
            foreach ($negIndividualEvents as $e) {
              $out .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->title;
              if ($e->summary != '') {
                $out .= ' → '.$e->summary;
              }
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
        $out .= '</td>';
      $out .= '</tr>';
      $out .= '<tr>';
        $out .= '<td class="text-left">';
          // Group work
          $posResult = '<span>'.count($posGroupEvents).' '.pluralize(count($posGroupEvents), "action positive", "actions positives").'</span>';
          $negResult = '<span>'.count($negGroupEvents).' '.pluralize(count($negGroupEvents), "action négative", "actions negatives").'</span>';
          $out .= '<p><b>Travail de groupe</b> : '.$posResult.' - '.$negResult.'</p>';
        $out .= '</td>';
      $out .= '</tr>';
      $out .= '<tr>';
        $out .= '<td class="text-left" style="height:1.3cm;">';
        $out .= '&nbsp;<u>Signature</u> ⇒ ';
        $out .= '</td>';
      $out .= '</tr>';
      $out .= '</table>';
      $out .= '<br />';
      if (in_array($counter, array(4, 8, 12, 16, 20, 24, 28, 32)) && $input->get['pages2pdf']) {
        $out .= '<pagebreak />';
      }
      $counter++;
    }
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
