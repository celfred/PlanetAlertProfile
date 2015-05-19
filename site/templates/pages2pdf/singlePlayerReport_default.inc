<table class="table table-condensed table-hover">
  <tr>
    <th colspan="<?php echo $categories->count; ?>"><h3><?php echo $reportTitle; ?></th>
  </tr>
  <tr>
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
        case 'oublis' : echo '<i class="glyphicon glyphicon-cloud" data-toggle="tooltip" title="'. $category->title.'"></i>'; break;
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

    echo '<tr>';
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
              case 'entrainement-supplementaire-simple' :
                $nbExtraHk += 1;
                $teamExtraHk += 1;
                $listExtraHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                break;
              case 'awarded-homework' :
                $nbExtraHk += 1;
                $listExtraHk .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                break;
              default : break;
            }
          }
          if ($category->name == 'oublis') {
            switch ($task->name) {
              case 'forgotten-weapons' :
                $nbForgotStuff += 1;
                $listForgotStuff .= '- '.strftime("%d/%m", $event->date).' '.$event->summary.'<br />';
                $teamForgotStuff += 1;
                break;
              case 'test-not-signed' :
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
        if ($category->name == 'oublis') {
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
?>
</table>

<?php
  $allEvents = $player->find("template=event, date>=$period->dateStart, date<=$period->dateEnd");
  // Get events
  foreach($categories as $category) {
    $globalData[$category->name] = $allEvents->find("task.category=$category, sort=-date");
  }
  $specialData['noHk'] = $allEvents->find("task.name=no-homework, sort=-date");
  $specialData['noSigned'] = $allEvents->find("task.name=test-not-signed, sort=-date");
  $specialData['noMaterial'] = $allEvents->find("task.name=forgotten-weapons, sort=-date");
  $specialData['xtHk'] = $allEvents->find("task.name=extra-homework, sort=-date");
  
  function headLine($category, $events) {
    $out = '';
    $out .= '<h2 class="well">';
    switch ($category) {
      case 'homework' :
        $noHk = $events->find("task.name=no-homework, sort=-date");
        $xtHk = $events->find("task.name=extra-homework, sort=-date");
        $out .= 'Travail non fait : '.$noHk->count();
        $out .= ' / ';
        $out .= 'Travail supplémentaire fait : '.$xtHk->count();
        $out .= '</h2>';
        break;
      case 'shop' :
        $out .= 'Planet Alert : '.$events->count(). ' item(s).';
        $out .= '</h2>';
        break;
      case 'oublis' :
        $noSigned = $events->find("task.name=test-not-signed, sort=-date");
        $noMaterial = $events->find("task.name=forgotten-weapons, sort=-date");
        $out .= 'Oubli(s) de signatures : '.$noSigned->count();
        $out .= ' / ';
        $out .= 'Oubli(s) de matériel : '.$noMaterial->count();
        $out .= '</h2>';
        break;
      default :
      $out .= wire('pages')->get("name=$category")->title;
      $out .= ' : ';
      // Calculate positive ratio to set an average RR/R/V/VV
      $vv = 0;
      $v = 0;
      $r = 0;
      $rr = 0;
      $nbEvents = $events->count();
      if ($nbEvents > 0) {
        foreach ($events as $index=>$event) {
          $task = wire(pages)->get("$event->task");
          if ($task->HP <= -5) {
            $rr += 1;
          }
          if ($task->HP > -5 && $task->HP < 0) {
            $r += 1;
          }
          if ($task->GC > 0 && $task->GC < 5) {
            $v += 1;
          }
          if ($task->GC >= 5) {
            $vv += 1;
          }
          if ($task->name == 'absent') {
            $abs += 1;
            $nbEvents -= 1;
          }
          $ratio = (int) round(((($vv*2)+($v*1.6)-$rr)*100)/($nbEvents*2));
          if ( $ratio < 0) { $ratio = 0; }
        }
        if (is_int($ratio)) {
          if ($ratio >= 80) {
            $out .= '<span data-toggle="tooltip" title="Moyenne pondérée sur la période" class="label label-success">VV</span>';
          }
          if ($ratio < 80 && $ratio >= 55) {
            $out .= '<span data-toggle="tooltip" title="Moyenne pondérée sur la période" class="label label-success">V</span>';
          }
          if ($ratio < 55 && $ratio >= 35) {
            $out .= '<span data-toggle="tooltip" title="Moyenne pondérée sur la période" class="label label-danger">R</span>';
          }
          if ($ratio < 35 && $ratio >= 0) {
            $out .= '<span data-toggle="tooltip" title="Moyenne pondérée sur la période" class="label label-danger">RR</span>';
          }
        }
        $out .= '<span style="font-size: 0.8em;">';
        if (is_int($ratio)) {
          $out .= ' <span data-toggle="tooltip" title="% de qualité">'.$ratio.'%</span>';
        }
        $out .= ' - <span>'.$nbEvents.' évaluations</span> ';

        if (!wire(input)->get['pages2pdf']) {
          $out .= ' [ <span data-toggle="tooltip" title="Positif (V+VV)">'.($v+$vv).' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Négatif (R+RR)">'.($r+$rr).' <i class="glyphicon glyphicon-thumbs-down"></i></span> ]';
        } else {
          $out .= ' [<span>'.($v+$vv).' V+VV</span> <span>'.($r+$rr).' R+RR</span> ]';
        }
        if ($abs != 0) {
          $out .= ' <span data-toggle="tooltip" data-html="true" title="Absences" class="absent">['.$abs.' abs.]</span>';
        }
        $out .= '</span>';
        $out .= '</h2>';
      }
    }

    return $out;
  }

  foreach($globalData as $category=>$events) {
    $out = '';
    // Headline with stats
    echo headLine($category, $events);

    if ($events->count() > 0) {
      // List all events
      echo '<ul>';
      switch($category) {
        case 'participation':
          foreach( $events as $event) {
            switch ($event->task->name) {
              case 'communication-rr' :
                $out .= '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">RR</span>';
                $rr += 1;
                break;
              case 'communication-r' :
                $out .= '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">R</span>';
                $r += 1;
                break;
              case 'communication-v' : 
                $out .= '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">V</span>';
                $v += 1;
                break;
              case 'communication-vv' :
                $out .= '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">VV</span>';
                $vv += 1;
                break;
              case 'absent' : 
                $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
                $abs += 1;
                $nbPart -= 1;
                $listAbsent .= '- '.strftime("%d/%m", $event->date).'<br />';
                break;
              default: break;
            }
          }
          break;
        default :
          foreach( $events as $event) {
            if ($event->task->name != 'homework') {
              $out .= '<li>'.strftime("%d/%m", $event->date). ' : ' . $event->title;
              if ($event->summary !== '') {
                $out .= ' ['.$event->summary.']';
              }
              $out .= '</li>';
            }
          }
      }
      echo $out;
      echo '</ul>';
    }
  }
?>
