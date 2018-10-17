<?php
  $categories = $pages->find("parent='/categories/',sort=sort")->not("name=shop|potions|protections|place|weapons|manual-cat|oublis|group-items");

  $out = '';
  $out .= '<table class="report table">';
  $out .= '<tr>';
  $out .= '<td>';
  if (isset($selected->avatar)) {
    $out .= '<img src="'.$selected->avatar->url.'" width="95" alt="No avatar" />';
  }
  $out .= '</td>';
  $out .= '<th>';
  $out .= '<h1>'.$reportTitle.'</h1>';
  $out .= '</th>';
  $out .= '</tr>';
  $allEvents = $selected->find("template=event, date>=$period->dateStart, date<=$period->dateEnd");
  // Get events
  foreach($categories as $category) {
    $globalData[$category->name] = $allEvents->find("task.category=$category, sort=-date");
  }
  $specialData['noHk'] = $allEvents->find("task.name=no-homework, sort=-date");
  $specialData['noSigned'] = $allEvents->find("task.name=signature, sort=-date");
  $specialData['noMaterial'] = $allEvents->find("task.name=material, sort=-date");
  $specialData['xtHk'] = $allEvents->find("task.name=extra-homework, sort=-date");
  foreach($globalData as $category=>$events) {
    $nbEvents = $events->count();
    $out .= '<tr><td colspan="2">';
    $out .= '<div class="well">';
    switch ($category) {
      case 'homework' :
        $noHk = $events->find("task.name=no-homework, sort=-date");
        $xtHk = $events->find("task.name=extra-homework|very-extra-homework, sort=-date");
        $out .= '<h3><span class="label label-default pull-left">Travail à la maison</span></h3>';
        if ($noHk->count() == 0) {
          $out .= '<i class="glyphicon glyphicon-thumbs-up"></i> <span class="">Toujours fait! C\'est bien!</span>';
        } else {
          $out .= '<i class="glyphicon glyphicon-thumbs-down"></i> '.$noHk->count().' fois non fait :(';
        }
        $out .= '<br />';
        if ( $xtHk->count() > 0) {
          $out .= '<i class="glyphicon glyphicon-thumbs-up"></i> <span class=""> Travail supplémentaire optionnel : '.$xtHk->count().' fois fait. C\'est bien.</span>';
        } else {
          $out .= '<span class=""> Aucun travail supplémentaire (optionnel) fait, c\'est dommage...</span>';
        }
        // Forgotten
        $out .= '<br />';
        $noSigned = $events->find("task.name=signature, sort=-date");
        $noMaterial = $events->find("task.name=material, sort=-date");
        if ($noSigned->count() > 0 || $noMaterial->count() > 0) {
          if ($noSigned->count() > 0) {
            $out .= '<i class="glyphicon glyphicon-thumbs-down"></i> Oubli(s) de signatures : '.$noSigned->count();
          }
          if ($noMaterial->count() > 0) {
            if ($noSigned->count() > 0) {
              $out .= ' / ';
            }
            $out .= ' Oubli(s) de matériel : '.$noMaterial->count();
          }
        } else {
          $out .= '<i class="glyphicon glyphicon-thumbs-up"></i> <span class="">Pas d\'oublis (signatures ou matériel) sur la période. C\'est bien !';
        }
        break;
      default :
        $cat = wire('pages')->get("name=$category")->title;
        $out .= '<h3><span class="label label-default pull-left">'.$cat.'</span></h3>';
        // Calculate positive ratio to set an average RR/R/V/VV
        $vv = 0;
        $v = 0;
        $r = 0;
        $rr = 0;
        if ($nbEvents > 0) {
          foreach ($events as $index=>$event) {
            $task = $pages->get("$event->task");
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
          $out .= '<span>Moyenne sur la période : </span>';
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
          $inClassEventsCount = $allEvents->find("inClass=1")->count();
          if ($inClassEventsCount > 0) {
            $out .= ' - <span>'.$nbEvents.' évaluations ('.$inClassEventsCount.' en classe)</span> ';
          } else {
            $out .= ' - <span>'.$nbEvents.' évaluations</span> ';
          }
          if (!$input->get['pages2pdf']) {
            $out .= ' [ <span data-toggle="tooltip" title="Positif (V+VV)">'.($v+$vv).' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Négatif (R+RR)">'.($r+$rr).' <i class="glyphicon glyphicon-thumbs-down"></i></span> ]';
          } else {
            $out .= ' [<span>'.($v+$vv).' V+VV</span> <span>'.($r+$rr).' R+RR</span> ]';
          }
          if (isset($abs) && $abs != 0) {
            $out .= ' <span data-toggle="tooltip" data-html="true" title="Absences" class="absent">['.$abs.' abs.]</span>';
          }
          $out .= '</span>';
        } else {
          $out .= ' <span class="">Pas de données sur la période.</span>';
        }
    }
    if ($nbEvents > 0) {
      // List all events
      $out .= '<ul class="text-left">';
      $out .= '<br />';
      switch($category) {
        case 'participation':
          $counter = 0;
          foreach( $events as $event) {
            $counter = $counter+1;
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
            if ( $counter == 40) {
              $out .= '<br />';
              $counter = 0;
            }
          }
          break;
        default :
          foreach( $events as $event) {
            if ($event->task->name != 'homework') {
              $eventTitle = $event->title;
              // Get rid of admin debugging comment [HP,GC...]
              $eventTitle = \preg_replace(' (\[.*\])', '', $eventTitle);
              $out .= '<li>';
              if ($event->task->HP < 0) {
                $out .= '<i class="glyphicon glyphicon-thumbs-down"></i> ';
              } else {
                $out .= '<i class="glyphicon glyphicon-thumbs-up"></i> ';
              }
              $out .= strftime("%d/%m", $event->date). ' : ' . $eventTitle;
              if ($event->summary !== '') {
                $out .= ' ['.$event->summary.']';
                if ($event->inClass == 1 && $event->task->is("name~=fight|ut-action")) {
                  $out .= ' [en classe]';
                }
              }
              $out .= '</li>';
            }
          }
      }
      $out .= '</ul>';
    }
    $out .= '</div>';
    $out .= '</td></tr>';
  }
  $out .= '</table>';

  echo $out;
?>
