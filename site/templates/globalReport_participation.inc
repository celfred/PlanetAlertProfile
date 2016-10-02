<table class="table table-condensed table-hover">
  <tr>
    <td colspan="8"><h3><?php echo $reportTitle; ?></h3></td>
  </tr>
  <?php
    foreach($allPlayers as $player) {
      $out = '';
      $listAbsent = '';
      $events = $player->find("template=event, task.category='participation', date>=$period->dateStart, date<=$period->dateEnd, sort=-date")->reverse();
      echo '<tr>';
      echo '<th>'.$player->title.'</th>';
      setParticipation($player, $events);
      echo '<td class="text-left">';
      switch ($player->participation) {
        case 'NN' : $class='primary';
          break;
        case 'VV' : $class='success';
          break;
        case 'V' : $class='success';
          break;
        case 'R' : $class='danger';
          break;
        case 'RR' : $class='danger';
          break;
        default: $class = '';
      }
      echo  '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-'.$class.'">'.$player->participation.'</span>';
      echo '</td>';
      if ($events->count > 0) {
        echo '<td class="text-left">';
          echo  '<span data-toggle="tooltip" title="% participation positive">'.$player->partRatio.'%</span>';
        echo '</td>';
        echo '<td class="text-left">';
        echo '<span data-toggle="tooltip" title="Participation positive">'.$player->partPositive.' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Participation négative">'.$player->partNegative.' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
        echo '</td>';
        echo '<td class="text-left">';
        echo $events->find("task.name!=abs|absent")->count().' cours';
        echo '</td>';
        echo '<td class="text-left">';
        if ($player->absent->count() > 0) {
          $listAbsent = '';
          foreach ($player->absent as $abs) {
            $listAbsent .= '- '.strftime("%d/%m", $abs->date).'<br />';
          }
          echo '<span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'">['.$player->absent->count().' abs.]';
        } else {
          echo '-';
        }
        echo '</td>';
        echo '<td class="text-left">';
        foreach ($events as $index=>$event) {
          $task = $pages->get("$event->task");
          switch ($task->name) {
            case 'communication-rr' :
              echo '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">RR</span>';
              break;
            case 'communication-r' :
              echo '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">R</span>';
              break;
            case 'communication-v' : 
              echo '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">V</span>';
              break;
            case 'communication-vv' :
              echo '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">VV</span>';
              break;
            case 'abs' : 
              echo '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
              break;
            case 'absent' : 
              echo '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
              break;
            default: break;
          }
          if (in_array($index, [10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200])) { echo '<br />'; }
        }
        echo '</td>';
      } else {
        echo '<td class="text-left" colspan="6">No event over the period.';
        echo '</td>';
      }
      echo '</tr>'; 
    }
  ?>
</table>
