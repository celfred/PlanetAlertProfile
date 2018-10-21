<?php
  $out = '';
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    $out .= '<table class="table table-condensed table-hover">';
      foreach($allPlayers as $player) {
        $listAbsent = '';
        $events = $player->find("template=event, task.category='participation', date>=$period->dateStart, date<=$period->dateEnd, sort=-date")->reverse();
        $out .= '<tr>';
          $out .= '<th>'.$player->title.'</th>';
          setParticipation($player, $period->dateStart, $period->dateEnd);
          $out .= '<td class="text-left">';
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
          $out .=  '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-'.$class.'">'.$player->participation.'</span>';
          $out .= '</td>';
          if ($events->count > 0) {
            $out .= '<td class="text-left">';
              $out .=  '<span data-toggle="tooltip" title="Quality ratio">'.$player->partRatio.'</span>';
            $out .= '</td>';
            $out .= '<td class="text-left">';
            $out .= '<span data-toggle="tooltip" title="Participation positive">'.$player->partPositive.' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Participation négative">'.$player->partNegative.' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
            $out .= '</td>';
            $out .= '<td class="text-left">';
            $out .= $events->find("task.name!=abs|absent")->count().' cours';
            $out .= '</td>';
            $out .= '<td class="text-left">';
            if ($player->absent->count() > 0) {
              $listAbsent = '';
              foreach ($player->absent as $abs) {
                $listAbsent .= '- '.strftime("%d/%m", $abs->date).'<br />';
              }
              $out .= '<span data-toggle="tooltip" data-html="true" title="'.$listAbsent.'">['.$player->absent->count().' abs.]';
            } else {
              $out .= '-';
            }
            $out .= '</td>';
            $out .= '<td class="text-left">';
            foreach ($events as $index=>$event) {
              $task = $pages->get("$event->task");
              switch ($task->name) {
                case 'communication-rr' :
                  $out .= '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">RR</span>';
                  break;
                case 'communication-r' :
                  $out .= '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">R</span>';
                  break;
                case 'communication-v' : 
                  $out .= '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">V</span>';
                  break;
                case 'communication-vv' :
                  $out .= '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">VV</span>';
                  break;
                case 'abs' : 
                  $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
                  break;
                case 'absent' : 
                  $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $event->date).'">-</span>';
                  break;
                default: break;
              }
              if (!$input->get['pages2pdf']) {
                if (in_array($index, [10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200])) { $out .= '<br />'; }
              } else {
                if (in_array($index, [30,60,90,120,150,180,210])) { $out .= '<br />'; }
              }
            }
            $out .= '</td>';
          } else {
            $out .= '<td class="text-left" colspan="6">No event over the period.';
            $out .= '</td>';
          }
        $out .= '</tr>'; 
      }
    $out .= '</table>';
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
