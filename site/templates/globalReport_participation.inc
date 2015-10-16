<table class="table table-condensed table-hover">
  <tr>
    <td colspan="8"><h3><?php echo $reportTitle; ?></h3></td>
  </tr>
  <?php
    foreach($allPlayers as $player) {
      $vv = 0;
      $v = 0;
      $r = 0;
      $rr = 0;
      $abs = 0;
      $out = '';
      $listAbsent = '';
      echo '<tr>';
      echo '<th>';
      echo $player->title;
      echo '</th>';
      $events = $player->find("template=event, task.category='participation', date>=$period->dateStart, date<=$period->dateEnd, sort=-date")->reverse();
      $nbPart = $events->count();
      if ($events->count() > 0) {
        foreach ($events as $index=>$event) {
          $task = $pages->get("$event->task");
          switch ($task->name) {
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
            case 'abs' : 
              $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
              $abs += 1;
              $nbPart -= 1;
              $listAbsent .= '- '.strftime("%d/%m", $event->date).'<br />';
              break;
            case 'absent' : 
              $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
              $abs += 1;
              $nbPart -= 1;
              $listAbsent .= '- '.strftime("%d/%m", $event->date).'<br />';
              break;
            default: break;
          }
          if (in_array($index, [10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200])) $out .= '<br />';
        }
        // Player's average and stats
        $percentPresent = (int) round((100*$nbPart)/$events->count());
        if ($percentPresent >= 30) {
          // Participation quality formula
          $ratio = (int) round(((($vv*2)+($v*1.6)-$rr)*100)/($nbPart*2));
          if ( $ratio < 0) { $ratio = 0; }
        } else {
          $ratio = 'absent';
        }

        echo '<td class="text-left">';
          if (is_int($ratio)) {
            if ($ratio >= 80) {
              echo '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-success">VV</span>';
            }
            if ($ratio < 80 && $ratio >= 55) {
              echo '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-success">V</span>';
            }
            if ($ratio < 55 && $ratio >= 35) {
              echo '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-danger">R</span>';
            }
            if ($ratio < 35 && $ratio >= 0) {
              echo '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-danger">RR</span>';
            }
          } else {
            if ($ratio === 'absent') {
              echo '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-default">NN</span>';
            }
          }
        echo '</td>';
        echo '<td class="text-left">';
        if (is_int($ratio)) {
          echo '<span data-toggle="tooltip" title="% participation positive">'.$ratio.'%</span>';
        }
        echo '</td>';
        echo '<td class="text-left">';
        echo '<span data-toggle="tooltip" title="Participation positive">'.($v+$vv).' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Participation négative">'.($r+$rr).' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
        echo '</td>';
        echo '<td class="text-left">';
        echo 'Du <span>'.strftime("%d/%m", $events[0]->date).'</span>';
        echo ' au <span>'.strftime("%d/%m", $events[$events->count-1]->date).'</span>';
        echo '</td>';
        echo '<td class="text-left">';
        echo $nbPart.' cours';
        echo '</td>';
        echo '<td class="text-left">';
        if ($abs > 0) {
          echo '<span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'">['.$abs.' abs.]';
        }
        echo '</td>';
        echo '<td class="text-left">';
        echo $out;
        echo '</td>';
      } else {
        echo '<td>-</td>';
      }
      echo '</tr>'; 
    }
  ?>
</table>

