<?php
  $out = '';
  $taskCount = 0;
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    if ($selected && $taskId!= -1) {
      include("report-title.inc"); 
      $out .= '<ul>';
      foreach($allPlayers as $p) {
        $prevTask = $p->find("template=event,task=$task, date>$period->dateStart, date<$period->dateEnd, sort=-date");
        if ($prevTask->count() > 0) {
          $taskCount += $prevTask->count();
          $out .= '<li>'.$p->title. ': <span class="label label-success">'.sprintf(_n('%d result', '%d results', $prevTask->count()), $prevTask->count()).'</span></li>';
          $out .= '<ul>';
          foreach($prevTask as $t) {
            $tDate = strftime("%d/%m/%y", $t->date).' - ';
            $out .= '<li>';
            $out .= $tDate. $t->summary;
            if ($t->inClass == 1) {
              $out .= ' [in class]';
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
        }
      }
      $out .= '</ul>';
      $out .= '<p class="label label-primary">Total count : '.sprintf(_n('%d result', '%d results', $taskCount), $taskCount).'</p>';
    } else {
      $out .= __("No selected task.");
    }
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
