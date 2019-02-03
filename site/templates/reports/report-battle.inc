<?php
  $out = '';
  $total = 0;
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    $out .= '<ul>';
    foreach($allPlayers as $p) {
      $allTests = $p->find("template=event, task.name~=battle, date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
      $inClassAllTestsCount = $allTests->find("inClass=1")->count();
      if ($allTests->count() > 0) {
        $total += $allTests->count();
        $out_03 = '<ul>';
        $prevLinkedId = '';
        $prevName = '';
        foreach($allTests as $t) {
          switch ($t->task->name) {
            case 'battle-vv' : $class="success"; $result="VV";
              break;
            case 'battle-v' : $class="success"; $result="V";
              break;
            case 'battle-r' : $class="danger"; $result="R";
              break;
            case 'battle-rr' : $class="danger"; $result="RR";
              break;
            default: $class = ""; $result = "";
          }
          $error = '';
          $out_03 .= '<li>';
          $out_03 .= date('d/m', $t->date);
          $out_03 .= ' → ';
          $out_03 .= '<span class="label label-'.$class.'">'.$result.'</span> ';
          $out_03 .= '<span>'.$t->summary.'</span> ';
          $out_03 .= '<span class="badge">'.$t->linkedId.'</span> ';
          $out_03 .= '<span class="label label-danger">'.$error.'</span>';
          $out_03 .='</li>';
          $prevLinkedId = $t->linkedId;
        }
        $out_03 .= '</ul>';
        $out_02 = '<li><strong>'.$p->title.'</strong> : ';
        $out_02 .= '<span class="label label-success">'.sprintf(_n('%d battle', '%d battles', $allTests->count()), $allTests->count()).'</span>';
        if ($inClassAllTestsCount > 0) {
          $out_02 .= ' ['.sprintf(__('%d in class'), $inClassAllTestsCount).']</li>';
        }
        $out .= $out_02.$out_03;
      }
    }
    $out .= '</ul>';
    if ($total > 0) {
      $out .= '<p class="label label-success">Total : '.sprintf(_n('%d battle for the team', '%d battles for the team', $total), $total).'</p>';
    } else {
      $out .= '<p>'.__("No battles.").'</p>';
    }
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
