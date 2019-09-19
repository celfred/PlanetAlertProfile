<?php
  $out = '';
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    if ($selected && $categoryId!= -1) {
      include("report-title.inc"); 
      foreach($allPlayers as $p) {
        $playersEvents = $pages->find("has_parent=$p, template=event, task.category=$category, date>=$period->dateStart, date<=$period->dateEnd");
        $out .= '<ul>';
        $out .= '<li><strong>'.$p->title.'</strong> : ';
        if ($playersEvents->count() > 0) {
          $out .= '<ul>';
          foreach($playersEvents as $e) {
            $out .= '<li>';
            $out .= date('d/m', $e->date).' → ';
            if ($e->task->HP < 0) {
              $out .= '<span class="label label-danger">';
            } else {
              $out .= '<span class="label label-success">';
            }
            $out .= $e->title.'</span> : ';
            $out .= $e->summary;
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<span>'.__("0 events").'</span>';
        }
        $out .= '</li>';
        $out .= '</ul>';
      }
    } else {
      $out .= __("No selected category.");
    }
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
