<?php 

function calculate_average($arr) {
  $total = 0;
  $count = count($arr); //total numbers in array
  foreach ($arr as $value) {
      $total = $total + $value; // total value of array numbers
  }
  $average = round($total/$count); // get average value
  return $average;
}

$reportTitle = '';
if ($input->get['sort']) {
  $sort = $input->get['sort'];
} else {
  $sort = 'title';
}
if ($input->urlSegment1 && $input->urlSegment2 == '') { // Class report
  $team = $input->urlSegment1;
  $allPlayers = $pages->find("team=$team, template=player, sort='". $sort ."'");
  $selectedPlayer = false;
  $teamParticipation = false;
  $reportTitle = 'Suivi du travail ('.$team.')<br />';
  $reportTitle .= ' [généré le '. date('d/m/Y \à H:i:s').']';
} else if ($input->urlSegment2 != '' && $input->urlSegment2 != 'participation') { // 1 player report
  $playerId = $input->urlSegment2;
  $selectedPlayer = $pages->get($playerId);
  $teamParticipation = false;

  $reportTitle = 'Bilan de '.$selectedPlayer->title.' ('. $selectedPlayer->team.')';;
  $reportTitle .= ' [généré le '. date('d/m/Y \à H:i:s').']';

  // List all recorded events for selected player
  $events = $selectedPlayer->find("template=event, sort=category");
} else if ($input->urlSegment2 != '' && $input->urlSegment2 == 'participation') { // Team participation
  $team = $input->urlSegment1;
  $allPlayers = $pages->find("team=$team, template=player, sort='".$sort."'");
  $teamParticipation = true;
  $selectedPlayer = false;
  $reportTitle = 'Participation ('.$team.')';
  if ($input->urlSegment3) {
    $limit = true;
    $reportTitle .= ' (10 derniers cours)';
  } else {
    $limit = false;
  }
  $reportTitle .= ' [généré le '. date('d/m/Y \à H:i:s').']';
}

$categories = $pages->find("parent='/categories/',sort=sort")->not("name=shop|potions|protections|place|weapons|attitude");

echo  '<table class="table table-condensed table-hover">';
if (!$selectedPlayer) { // Class report
  if (!$teamParticipation) { // Class report
    echo  '<tr>';
    echo  '<td></td>';
    echo  '<th colspan="'. $categories->count.'"><h2>'. $reportTitle .'</h2></th>';
    echo  '<td colspan="4"><h3>Planet Alert</h3></td>';
    echo  '</tr>';
    echo  '<tr>';
    echo  '<td></td>';
    // Build all categories columns
    foreach ($categories as $category) {
      echo '<th>';
      echo '<span class="" title="">'. $category->title.'</span>';
      /*
      switch ($category->name) {
        case 'participation' : echo '<span class="" title="'. $category->title.'">Participation</span> '; break;
        case 'homework' : echo '<i class="glyphicon glyphicon-pencil" title="'. $category->title.'"></i>'; break;
        case 'groupwork' : echo '<i class="glyphicon glyphicon-user" title="'. $category->title.'"></i><i class="glyphicon glyphicon-user" title="'. $category->title.'"></i>'; break;
        case 'travail-individuel' : echo '<i class="glyphicon glyphicon-user" title="'. $category->title.'"></i>'; break;
        case 'manual-cat' : echo '<i class="glyphicon glyphicon-cog" title="'. $category->title.'"></i>'; break;
        case 'test' : echo '<i class="glyphicon glyphicon-ok" title="'. $category->title.'"></i>'; break;
        case 'oublis' : echo '<i class="glyphicon glyphicon-cloud" title="'. $category->title.'"></i>'; break;
        default : echo $category->title;
      }
      */
      echo '</th>';
    }
    echo '<td><span>Lieux</span></td>';
    echo '<td><span>Équipement</span></td>';
    echo '<td><span>Or</span></td>';
    echo '<td><span>Santé</span></td>';
    echo '</tr>';

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
      echo $player->title.' '.$player->lastName;
      echo '</th>';
      foreach ($categories as $category) {
        echo '<td>';
        $events = $player->find("template=event, task.category=$category");
        $posPart = $events->count(); // Get positive participation
        $nbPart = $events->count(); // Max participation
        $vv = 0;
        $v = 0;
        $r = 0;
        $rr = 0;
        $nbAbsent = 0;
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
              $list .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->summary."\r\n";
            } else {
              $list .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
            }
            if ($event->task->name == 'absent') {
              $listAbsent .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
              $teamAbsent += 1;
            }
            if ($category->name == 'homework') {
              switch ($task->name) {
                case 'no-homework' :
                  $nbNoHk += 1;
                  $listNoHk .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  //echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊝</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'homework' : // Don't display regular homework
                  $nbHk += 1;
                  $listHk .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  //echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊙</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'homework-half-done' :
                  $nbHalfHk += 1;
                  $listHalfHk .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  //echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊘</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'entrainement-supplementaire-simple' :
                  $nbExtraHk += 1;
                  $listExtraHk .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  //echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊕</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'awarded-homework' :
                  $nbExtraHk += 1;
                  $listExtraHk .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  $teamExtraHk += 1;
                  //echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊕</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                default : break;
              }
            }
            if ($category->name == 'oublis') {
              switch ($task->name) {
                case 'forgotten-weapons' :
                  $nbForgotStuff += 1;
                  $listForgotStuff .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  $teamForgotStuff += 1;
                  //echo  '<span title="'. $list .'"><i class="glyphicon glyphicon-file"></i>&nbsp;<span class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></span> ';
                  break;
                case 'test-not-signed' :
                  $nbForgotSigned += 1;
                  $listForgotSigned .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
                  $teamForgotSigned += 1;
                  //echo  '<span title="'. $list .'"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></span> ';
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
              echo $ratio.'%';
              //echo ' ('.$posPart.'/'.$nbPart.')';
            }
            if ($nbAbsent != 0) {
              echo '<span title="'.$listAbsent.'" class="absent">['.$nbAbsent.' abs.]</span>';
            }
            array_push($teamPart, $ratio);
          }
          if ($category->name == 'oublis') {
            if ($nbForgotStuff > 0) {
              echo  '<span title="'. $listForgotStuff .'">M:<span class="'. $className .'">'.$nbForgotStuff.'</span></span>';
            echo '&nbsp;';
            }
            if ($nbForgotSigned > 0) {
              echo  '<span title="'. $listForgotSigned .'">S:<span class="'. $className .'">'.$nbForgotSigned.'</span></span> ';
            }
          }
          if ($category->name == 'homework') {
            if ($nbNoHk > 0) {
              echo  '<span title="'. $listNoHk .'">&nbsp;<span>'.$nbNoHk.'</span>-</span>';
              echo '&nbsp;';
            }
            if ($nbHalfHk > 0) {
              echo  '<span title="'. $listHalfHk .'">&nbsp;<span>'.$nbHalfHk.'</span>½</span>';
              echo '&nbsp;';
            }
            if ($nbExtraHk > 0) {
              echo  '<span title="'. $listExtraHk .'">&nbsp;<span>'.$nbExtraHk.'</span>+</span>';
            }
          }
          if ($category->name != 'participation' && $category->name != 'homework' && $category->name != 'oublis') {
            $ratio = round(($posItems*100)/$nbItems);
            echo '<p title="'.$list.'">';
            echo $ratio.'%';
            echo ' ('.$posItems.'/'.$nbItems.')';
            echo '</p>';
          }
        } else {
          echo '-';
        }
        echo '</td>';
      }
      // Places and Equipment
      /*
      $items = $player->places;
      array_push($items, $player->equipment);
      if ($items) {
        echo '<td>';
        foreach ($items as $item) {
          echo '<p>';
          echo $item->title;
          echo '</p>';
        }
        echo '</td>';
      }
      */
      echo '<td>';
      if ($player->places->count > 0) {
        $list = '';
        foreach ($player->places as $place) {
          $list .= $place->title."\r\n";
        }
        echo '<p title="'.$list.'">';
        echo '<i class="glyphicon glyphicon-picture"></i> '.$player->places->count;
        echo '</p>';
        $teamPlaces += 1;
      }
      echo '</td>';
      echo '<td>';
      if ($player->equipment->count > 0) {
        $list = '';
        foreach ($player->equipment as $item) {
          $list .= $item->title."\r\n";
        }
        echo '<p title="'.$list.'">';
        echo '<i class="glyphicon glyphicon-shopping-cart"></i> '.$player->equipment->count;
        echo '</p>';
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
    foreach ($categories as $category) {
      echo '<th>';
      if ($category->name == 'participation') {
        echo calculate_average($teamPart).'%';
        echo '&nbsp;';
        echo '['.$teamAbsent.' abs.]';
      }
      if ($category->name == 'homework') {
        echo  '<span>'.$teamNoHk.'-</span>';
        echo '&nbsp;';
        echo  '<span>'.$teamHalfHk.'½</span>';
        echo '&nbsp;';
        echo  '<span>'.$teamExtraHk.'+</span>';
      }
      if ($category->name == 'oublis') {
        echo  '<span>M:'.$teamForgotStuff.'</span>';
        echo '&nbsp;';
        echo  '<span>S:'.$teamForgotSigned.'</span> ';
      }
      echo '</th>';
    }
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
  } else { // Team Participation report
    echo '<tr>';
    echo '<th colspan="8"><h2>'. $reportTitle;
    echo '</h2></th>';
    echo '</tr>';
    foreach($allPlayers as $player) {
      $vv = 0;
      $v = 0;
      $r = 0;
      $rr = 0;
      $abs = 0;
      $out = '';
      echo '<tr>';
      echo '<th>';
      echo $player->title.' '.$player->lastName;
      echo '</th>';
      if ($limit) { // Limit to last lessons
        $events = $player->find("template=event, task.category='participation', limit=10, sort=-created")->reverse();
        $nbPart = $events->count();
      } else {
        $events = $player->find("template=event, task.category='participation'");
        $nbPart = $events->count();
      }
      if ($events->count() > 0) {
        foreach ($events as $event) {
          $task = $pages->get("$event->task");
          switch ($task->name) {
            case 'communication-rr' :
              $out .= '<span class="participation label label-danger" title="['.strftime("%d/%m", $event->created).']">&nbsp;RR&nbsp;</span>';
              //$out .= '['.strftime("%d/%m", $event->created).']';
              $rr += 1;
              break;
            case 'communication-r' :
              $out .= '<span class="participation label label-danger">&nbsp;R&nbsp;</span>';
              //$out .= '['.strftime("%d/%m", $event->created).']';
              $r +=1;
              break;
            case 'communication-v' : 
              $out .= '<span class="participation label label-success" title="['.strftime("%d/%m", $event->created).']">&nbsp;V&nbsp;</span>';
              //$out .= '['.strftime("%d/%m", $event->created).']';
              $v +=1;
              break;
            case 'communication-vv' :
              $out .= '<span class="participation label label-success" title="['.strftime("%d/%m", $event->created).']">&nbsp;VV&nbsp;</span>';
              //$out .= '['.strftime("%d/%m", $event->created).']';
              $vv +=1;
              break;
            case 'absent' : 
              $out .= '<span class="participation label label-info">- ['.strftime("%d/%m", $event->created).']</span>';
              $abs += 1;
              $nbPart -= 1;
              break;
            default: break;
          }
        }
        // Player's average and stats
        // Player's average and stats
        if ($nbPart > 7) { // No more than 3 absences
          $ratio = (int) round(((($vv*2)+($v*1.6)-$rr)*100)/($nbPart*2));
          if ( $ratio < 0) { $ratio = 0; }
        } else {
          $ratio = 'absent';
        }

        echo '<td class="text-left">';
          if (is_int($ratio)) {
            if ($ratio >= 80) {
              echo '<span class="label label-success">&nbsp;VV&nbsp;</span>';
            }
            if ($ratio < 80 && $ratio >= 55) {
              echo '<span class="label label-success">&nbsp;V&nbsp;</span>';
            }
            if ($ratio < 55 && $ratio >= 35) {
              echo '<span class="label label-danger">&nbsp;R&nbsp;</span>';
            }
            if ($ratio < 35 && $ratio >= 0) {
              echo '<span class="label label-danger">&nbsp;RR&nbsp;</span>';
            }
          } else {
            if ($ratio === 'absent') {
              echo '<span class="label label-default">NN</span>';
            }
          }
        echo '</td>';
        echo '<td class="text-left">';
        if (is_int($ratio)) {
          echo $ratio.'%';
        }
        //echo $ratio.'%&nbsp;&nbsp;&nbsp;&nbsp;'.($v+$vv).'<i class="glyphicon glyphicon-thumbs-up"></i> '.($nbPart-($v+$vv)).'<i class="glyphicon glyphicon-thumbs-down"></i>&nbsp;&nbsp;&nbsp;&nbsp;('.$nbPart.' cours)';
        //$out .='RR:'.$rr.' / R:'.$r.' / V:'.$v.' / VV:'.$vv.'&nbsp;&nbsp;&nbsp;';
        echo '</td>';
        echo '<td>';
        echo '<span class="">'.($v+$vv).'+</span>&nbsp;&nbsp;<span class="">'.($nbPart-($v+$vv)).'-</span>';
        echo '</td>';
        echo '<td class="text-left">';
        echo 'Du <span>'.strftime("%d/%m", $events[0]->created).'</span>';
        echo ' au <span>'.strftime("%d/%m", $events[$events->count-1]->created).'</span>';
        echo '</td>';
        echo '<td>';
        echo $nbPart.' cours';
        echo '</td>';
        echo '<td>';
        if ($abs > 0) {
          echo '['.$abs.' abs.]';
        }
        echo '</td>';
        echo '<td>';
        echo  $out;
        echo '</td>';
      } else {
        echo '<td>-</td>';
      }
    }
  }
} else { // Player's report TODO
  // Build all categories columns
  $categories = $pages->find("parent='/categories'")->not("name=shop|potions|protections|place|weapons|attitude");
  if ($events->count() > 0) {
    foreach ($events as $event) {
      $task = $pages->get("$event->task");
      if ($task->HP < 0) {
        $className = 'negative';
      } else {
        $className = 'positive';
      }
      echo  '<tr class="'. $className .'">';
      echo '<td>';
      echo '['. strftime("%d/%m", $event->created).'] ';
      if ($task.length > 0) { // Task is set only in new version for the moment
        echo $task->summary;
      } else {
        echo $event->title;
      }
      echo '</td>';
      echo '</tr>';
    }
  }
}

echo '</table>';

?>

