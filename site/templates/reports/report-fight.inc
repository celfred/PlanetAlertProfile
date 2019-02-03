<?php
  $out = '';
  $total = 0;
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    $out .= '<ul>';
    foreach($allPlayers as $p) {
      if ($monsterId != '-1') {
        $allTests = $p->find("template=event, task.name~=fight|best-time, refPage=$monsterId, date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
      } else {
        $allTests = $p->find("template=event, task.name~=fight|best-time, refPage!='', date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
      }
      $inClassAllTestsCount = $allTests->find("inClass=1")->count();
      if ($allTests->count() > 0) {
        $total += $allTests->count();
        $out_03 = '<ul>';
        $prevDate = '';
        $prevName = '';
        foreach($allTests as $t) {
          switch ($t->task->name) {
            case 'fight-vv' : $class="success"; $result="VV";
              break;
            case 'fight-v' : $class="success"; $result="V";
              break;
            case 'fight-r' : $class="danger"; $result="R";
              break;
            case 'fight-rr' : $class="danger"; $result="RR";
              break;
            default: $class = ""; $result = "";
          }
          if ($prevDate == date('Y-m-d', $t->date) && $prevName == $t->refPage->name) {
            $error = 'Error detected ?';
          } else {
            $error = '';
          }
          $out_03 .= '<li>';
          $out_03 .= date('d/m', $t->date).' → '.$t->refPage->title.' [lvl '.$t->refPage->level.'] <span class="label label-'.$class.'">'.$result.'</span> <span class="label label-danger">'.$error.'</span>';
          if ($t->inClass == 1) {
            $out_03 .= ' ['.__("in class").']';
          } else {
            $out_03 .= ' ['.__("not in class").']';
          }
          $out_03 .='</li>';
          $prevDate = date('Y-m-d', $t->date);
          $prevName = $t->refPage->name;
        }
        $out_03 .= '</ul>';
        $out_02 = '<li><strong>'.$p->title.'</strong> : ';
        $out_02 .= '<span class="label label-success">'.sprintf(_n('%d fight', '%d fights', $allTests->count()), $allTests->count()).'</span>';
        if ($inClassAllTestsCount > 0) {
          $out_02 .= ' ['.sprintf(__('%d in class'), $inClassAllTestsCount).']</li>';
        }
        $out .= $out_02.$out_03;
      }
    }
    $out .= '</ul>';
    if ($total > 0) {
      $out .= '<p class="label label-success">Total : '.sprintf(_n('%d fight for the team', '%d fights for the team', $total), $total).'</p>';
    } else {
      $out .= '<p>'.__("No fights.").'</p>';
    }
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
