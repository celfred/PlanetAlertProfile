<table class="table table-condensed table-hover">
  <tr>
    <td></td>
    <th colspan="<?php echo $categories->count; ?>"><h3><?php echo $reportTitle; ?></th>
  <!--  <td colspan="4"><h3>Planet Alert</h3></td> -->
  </tr>
  <tr>
  <td></td>
  <?php // Build all categories columns
  foreach ($categories as $category) {
    echo '<th>';
    if (!$input->get['pages2pdf']) {
      switch ($category->name) {
        case 'participation' : echo '<i class="glyphicon glyphicon-comment" data-toggle="tooltip" title="'. $category->title.'"></i> '; break;
        case 'homework' : echo '<i class="glyphicon glyphicon-pencil" data-toggle="tooltip" title="'. $category->title.'"></i>'; break;
        case 'groupwork' : echo '<i class="glyphicon glyphicon-user" data-toggle="tooltip" title="'. $category->title.'"></i><i class="glyphicon glyphicon-user" title="'. $category->title.'"></i>'; break;
        case 'travail-individuel' : echo '<i class="glyphicon glyphicon-user" data-toggle="tooltip" title="'. $category->title.'"></i>'; break;
        case 'manual-cat' : echo '<i class="glyphicon glyphicon-cog" data-toggle="tooltip" title="'. $category->title.'"></i>'; break;
        case 'test' : echo '<i class="glyphicon glyphicon-ok" data-toggle="tooltip" title="'. $category->title.'"></i>'; break;
        /* case 'oublis' : echo '<i class="glyphicon glyphicon-cloud" data-toggle="tooltip" title="'. $category->title.'"></i>'; break; */
        default : echo $category->title;
      }
    } else {
      echo $category->title;
    }
    echo '</th>';
  }
  ?>
  </tr>
  <?php
    $teamPlaces = 0;
    $teamEquipment = 0;
    $teamHealth = array();
    $teamForgotStuff = 0;
    $teamForgotSigned = 0;
    $teamNoHk = 0;
    $teamHalfHk = 0;
    $teamExtraHk = 0;
    $teamAbsent = 0;
    $teamPart = array();

    foreach($allPlayers as $player) {
      echo '<tr>';
      echo '<th>';
      if (!$input->get['pages2pdf']) {
        echo $player->title;
      } else {
        echo $player->title.' '.$player->lastName;
      }
      echo '</th>';
      foreach ($categories as $category) {
        echo '<td>';
        // Find events within the selected period
        $events = $player->find("template=event, task.category=$category, date>=$period->dateStart, date<=$period->dateEnd");
        $posPart = $events->count();
        $nbPart = $events->count();
        $nbAbsent = 0;
        $vv = 0;
        $v = 0;
        $r = 0;
        $rr = 0;
        $list = '';
        $listForgotStuff = '';
        $listForgotSigned = '';
        $listAbsent = '';
        $listNoHk = '';
        $listHk = '';
        $listHalfHk = '';
        $listExtraHk = '';
        $nbForgotSigned = 0;
        $nbForgotStuff = 0;
        $nbNoHk = 0;
        $nbHalfHk = 0;
        $nbHk = 0;
        $nbExtraHk = 0;
        $posItems = $events->count();
        $nbItems = $events->count();
        // Is $player concerned with this category?
        if ($events->count() > 0) {
          foreach ($events as $event) {
            $task = $pages->get("$event->task");
            if ($task->HP < 0) {
              $className = 'negative';
              // Participation stat
              if ($category->name == 'participation') {
                $posPart = $posPart-1;
              }
              $posItems = $posItems-1;
              $sign = '-';
            } else {
              if ($task->name != 'absent') {
                $className = 'positive';
              } else {
                $nbPart = $nbPart-1;
                $nbAbsent = $nbAbsent+1;
                $posPart = $posPart-1;
              }
              $sign = '+';
            }
            if (strlen(trim($event->summary)) > 0) {
              $list .= $sign.' '.strftime("%d/%m", $event->date).' '.$event->summary."<br />";
            } else {
              $list .= $sign.' '.strftime("%d/%m", $event->date).' '.$event->title."<br />";
            }
            if ($event->task->name == 'absent') {
              $listAbsent .= '- '.strftime("%d/%m", $event->date).'<br />';
              $teamAbsent += 1;
            }
            if ($category->name == 'homework') {
              switch ($task->name) {
                case 'no-homework' :
                  $nbNoHk += 1;
                  $listNoHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary."<br />";
                  $teamNoHk += 1;
                  break;
                case 'homework' : // Don't display regular homework
                  $nbHk += 1;
                  $listHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary."<br />";
                  break;
                case 'homework-half-done' :
                  $nbHalfHk += 1;
                  $teamHalfHk += 1;
                  $listHalfHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary."<br />";
                  break;
                case 'extra-homework' :
                  $nbExtraHk += 1;
                  $teamExtraHk += 1;
                  $listExtraHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                  break;
                case 'very-extra-homework' :
                  $nbExtraHk += 1;
                  $listExtraHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                  break;
                case 'material' :
                  $nbForgotStuff += 1;
                  $listForgotStuff .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                  $teamForgotStuff += 1;
                  break;
                case 'signature' :
                  $nbForgotSigned += 1;
                  $listForgotSigned .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                  $teamForgotSigned += 1;
                  break;
                default : break;
              }
            }
            if ($category->name == 'participation') {
                switch ($task->name) {
                  case 'communication-rr' :
                    $rr += 1;
                    break;
                  case 'communication-r' :
                    $r += 1;
                    break;
                  case 'communication-v' : 
                    $v += 1;
                    break;
                  case 'communication-vv' :
                    $vv += 1;
                    break;
                  case 'absent' : 
                    $abs += 1;
                    $nbPart -= 1;
                    break;
                  default: break;
                }
            }
          }
          if ($category->name == 'participation') {
            $ratio = round(((($vv*2)+($v*1.6)-$rr)*100)/($nbPart*2));
            if ( $ratio < 0) { $ratio = 0; }

            if ($nbPart != 0) {
              echo '<span data-toggle="tooltip" title="% Participation positive">'.$ratio.'%</span>';
            }
            if ($nbAbsent != 0) {
              echo ' <span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'" class="absent">['.$nbAbsent.' abs.]</span>';
            }
            array_push($teamPart, $ratio);
          }
          if ($category->name == 'homework') {
            if (!$input->get['pages2pdf']) {
              if ($nbNoHk > 0) {
                echo  '<span data-toggle="tooltip" data-html="true" title="'. $listNoHk .'"><i class="glyphicon glyphicon-remove-circle"></i>&nbsp;<span>'.$nbNoHk.'</span></span>';
                echo '&nbsp;&nbsp;&nbsp;';
              }
              if ($nbHalfHk > 0) {
                echo  '<span data-toggle="tooltip" data-html="true" title="'. $listHalfHk .'"><i class="glyphicon glyphicon-ban-circle"></i>&nbsp;<span>'.$nbHalfHk.'</span></span>';
                echo '&nbsp;&nbsp;&nbsp;';
              }
              if ($nbExtraHk > 0) {
                echo  '<span data-toggle="tooltip" data-html="true" title="'. $listExtraHk .'"><i class="glyphicon glyphicon-ok-circle"></i>&nbsp;<span>'.$nbExtraHk.'</span></span>';
              }
            } else {
              if ($nbNoHk > 0) {
                echo  '<span>'.$nbNoHk.' NoHk</span>';
                echo '&nbsp;&nbsp;';
              }
              if ($nbHalfHk > 0) {
                echo  '<span>'.$nbHalfHk.' HfHk</span>';
                echo '&nbsp;&nbsp;';
              }
              if ($nbExtraHk > 0) {
                echo  '<span>'.$nbExtraHk.' XtHk</span>';
              }
            }
          }
          if ($category->name == 'homework' && ($nbForgotSigned != 0 || $nbForgotStuff != 00)) {
            if (!$input->get['pages2pdf']) {
              if ($nbForgotStuff > 0) {
                echo  '<span data-toggle="tooltip" data-html="true" title="'. $listForgotStuff .'"><i class="glyphicon glyphicon-file"></i>&nbsp;<span class="">'.$nbForgotStuff.'</span></span>';
                echo '&nbsp;&nbsp;&nbsp;';
              }
              if ($nbForgotSigned > 0) {
                echo  '<span data-toggle="tooltip" data-html="true" title="'. $listForgotSigned .'"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span class="">'.$nbForgotSigned.'</span></span> ';
              }
            } else {
              echo  '<span>'.$nbForgotStuff.' Mat.</span>';
              echo '&nbsp;&nbsp;&nbsp;';
              echo  '<span>'.$nbForgotSigned.' Sign.</span>';
            }
          }
          if ($category->name != 'participation' && $category->name != 'homework' && $category->name != 'oublis') {
            $ratio = round(($posItems*100)/$nbItems);
            echo '<p>';
            echo '<span data-toggle="tooltip" title="% positif">'.$ratio.'%</span>';
            echo '&nbsp;<span data-toggle="tooltip" data-html="true" title="'.$list.'">('.$posItems.'/'.$nbItems.')</span>';
            echo '</p>';
          }
        } else {
          echo '-';
        }
        echo '</td>';
      }
      echo '</tr>';
    }
    
    // Team stats
    echo '<tr>';
    echo '<th>';
    echo 'Totaux et moyennes';
    echo '</th>';
    foreach ($categories as $category) {
      echo '<th>';
      if ($category->name == 'participation') {
        echo '<span data-toggle="tooltip" title="Participation moyenne">'.calculate_average($teamPart).'%';
        echo '&nbsp;&nbsp;&nbsp;';
        echo '['.$teamAbsent.' abs.]</span>';
      }
      if ($category->name == 'homework') {
        if (!$input->get['pages2pdf']) {
          echo  '<span data-toggle="tooltip" title="No hk"><i class="glyphicon glyphicon-remove-circle"></i>&nbsp;<span>'.$teamNoHk.'</span></span>';
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span data-toggle="tooltip" title="Half-hk"><i class="glyphicon glyphicon-ban-circle"></i>&nbsp;<span>'.$teamHalfHk.'</span></span>';
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span data-toggle="tooltip" title="Extra-hk"><i class="glyphicon glyphicon-ok-circle"></i>&nbsp;<span>'.$teamExtraHk.'</span></span>';
        } else {
          echo  '<span>'.$teamNoHk.' NoHk</span>';
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span>'.$teamHalfHk.' HfHk</span>';
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span>'.$teamExtraHk.' XtHk</span>';
          echo '&nbsp;&nbsp;&nbsp;';
        }
        if (!$input->get['pages2pdf']) {
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span data-toggle="tooltip" title="Oubli matériel"><i class="glyphicon glyphicon-file"></i>&nbsp;<span>'.$teamForgotStuff.'</span></span>';
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span data-toggle="tooltip" title="Oubli signature"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span>'.$teamForgotSigned.'</span></span> ';
        } else {
          echo  '<span>'.$teamForgotStuff.' Mat.</span>';
          echo '&nbsp;&nbsp;&nbsp;';
          echo  '<span>'.$teamForgotSigned.' Sign.</span>';
        }
      }
      echo '</th>';
    }
    echo '</tr>';
?>
</table>

