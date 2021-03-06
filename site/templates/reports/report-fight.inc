<?php
  $out = '';
  $total = 0;
  $nbConcernedPlayers = 0;
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    $out .= '<ul>';
    foreach($allPlayers as $p) {
      if ($monsterId != '-1') {
        if (isset($inClass)) {
          $allTests = $p->find("template=event, task.name~=fight|best-time, inClass=1, refPage=$monsterId, date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
        } else {
          $allTests = $p->find("template=event, task.name~=fight|best-time, refPage=$monsterId, date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
        }
      } else {
        if (isset($inClass)) {
          $allTests = $p->find("template=event, task.name~=fight|best-time, inClass=1, refPage!='', date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
        } else {
          $allTests = $p->find("template=event, task.name~=fight|best-time, refPage!='', date>=$period->dateStart, date<=$period->dateEnd, sort=refPage, sort=date");
        }
      }
      $inClassAllTestsCount = $allTests->find("inClass=1")->count();
      if ($allTests->count() > 0) {
        $nbConcernedPlayers++;
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
            $error = __('Error detected ?');
          } else {
            $error = '';
          }
          $out_03 .= '<li>';
          $out_03 .= date('d/m', $t->date).' → '.$t->refPage->title.' [lvl '.$t->refPage->level.'] <span class="label label-'.$class.'">'.$result.'</span> <span class="label label-danger">'.$error.'</span>';
          if ($t->inClass == 1) {
            $checked = 'checked="checked"';
          } else {
            $checked ='';
          }
          $out_03 .= ' <label for="ckbx'.$t->id.'" class="btn btn-danger btn-xs"><input name="ckbx'.$t->id.'" id="ckbx'.$t->id.'" type="checkbox" data-href="'.$pages->get('name=submitforms')->url.'?form=inClass&eventId='.$t->id.'" class="simpleAjax" data-hide-feedback="true" value="" '.$checked.' /> '.__("[in class]").'</label>';
          $out_03 .='</li>';
          $prevDate = date('Y-m-d', $t->date);
          $prevName = $t->refPage->name;
        }
        $out_03 .= '</ul>';
        $out_02 = '<li><strong>'.$p->title.'</strong> : ';
        if (isset($inClass)) {
          $out_02 .= '<span class="label label-success">'.sprintf(_n('%d request', '%d requests', $allTests->count()), $allTests->count()).'</span>';
        } else {
          $out_02 .= '<span class="label label-success">'.sprintf(_n('%d fight', '%d fights', $allTests->count()), $allTests->count()).'</span>';
        }
        if (!isset($inClass) && $inClassAllTestsCount > 0) {
          $out_02 .= ' ['.sprintf(__('%d in class'), $inClassAllTestsCount).']</li>';
        }
        $out .= $out_02.$out_03;
      }
    }
    $out .= '</ul>';
    if (isset($inClass)) {
      if ($total > 0) {
        $out .= '<p class="label label-primary">';
        $out .= 'Total : '.sprintf(_n('%d request', '%d requests', $total), $total).' ';
        $out .= sprintf(_n('for %d player', 'for %d players', $nbConcernedPlayers), $nbConcernedPlayers);
        $out .= '</p>';
      } else {
        $out .= '<p>'.__("No requests.").'</p>';
      }
    } else {
      if ($total > 0) {
        $out .= '<p class="label label-primary">';
        $out .= 'Total : '.sprintf(_n('%d fight', '%d fights', $total), $total).' ';
        $out .= sprintf(_n('for %d player', 'for %d players', $nbConcernedPlayers), $nbConcernedPlayers);
        $out .= '</p>';
      } else {
        $out .= '<p>'.__("No fights.").'</p>';
      }
    }
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
