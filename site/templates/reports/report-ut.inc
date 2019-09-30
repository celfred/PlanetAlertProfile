<?php
  $out = '';
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    // Limit to logged in user
    if ($user->isSuperuser()) {
      $allMonstersIds = $pages->findIds("parent.name=monsters, template=exercise, include=all");
    } else {
      $allMonstersIds = $pages->findIds("parent.name=monsters, template=exercise, (created_users_id=$user->id),(exerciseOwner.singleTeacher=$user,exerciseOwner.publish=1, summary!='')");
    }
    $teamUt = 0;
    foreach($allPlayers as $p) {
      $playersTrainings = $pages->find("has_parent=$p, template=event, task.name~=ut-action, refPage!='', date>=$period->dateStart, date<=$period->dateEnd");
      $totalOutUt = 0;
      $totalInUt = 0;
      $out .= '<ul>';
      $out .= '<li><strong>'.$p->title.'</strong> : ';
      if ($playersTrainings->count() > 0) {
        $out .= '<span class="label label-primary">';
        $out .= sprintf(_n("%d session", "%d sessions", $playersTrainings->count()), $playersTrainings->count());
        // Find total UT gained over the period
        foreach($playersTrainings as $t) {
          preg_match("/\[\+([\d]+)U\.?T\.?\]/", $t->summary, $matches);
          if (!$matches) {
            if ($t->inClass == 0) {
              $totalOutUt++;
            } else {
              $totalInUt++;
            }
          } else {
            if ($t->inClass == 0) {
              $totalOutUt = $totalOutUt+$matches[1];
            } else {
              $totalInUt = $totalInUt+$matches[1];
            }
          }
        }
        $out .= ' → '.($totalOutUt+$totalInUt).' '.__('UT');
        $out .= '</span>';
      } else {
        $out .= '-';
      }
      $out .= '</li>';
      foreach($allMonstersIds as $mId) {
        $outUt = 0; // Out of class UT
        $inUt= 0; // in class UT
        $mTrainings  = $playersTrainings->find("refPage.id=$mId")->sort("refPage->title");
        if ($mTrainings->count() > 0) {
          $out .= '<ul>';
          foreach($mTrainings as $mT) {
            preg_match("/\[\+([\d]+)U\.?T\.?\]/", $mT->summary, $matches);
            if (!$matches) {
              if ($mT->inClass == 0) {
                $outUt++;
              } else {
                $inUt++;
              }
            } else {
              if ($mT->inClass == 0) {
                $outUt = $outUt+$matches[1];
              } else {
                $inUt = $inUt+$matches[1];
              }
            }
          }
          $out .= '<li>'.$mT->refPage->title.' → <span class="label label-success">'.($outUt+$inUt).' '.__('UT').'</span>';
          $out .= ' - '.sprintf(_n("%d session", "%d sessions", $mTrainings->count()), $mTrainings->count());
          if ($inUt > 0) {
            $out .= ' - ['.$inUt.'UT '.__("in class").']';
          }
          if ($mTrainings->count() == 1) {
            if ($mT->inClass == 1) {
              $checked = 'checked="checked"';
            } else {
              $checked ='';
            }
            $out .= ' <label for="ckbx'.$mT->id.'" class="btn btn-danger btn-xs"><input name="ckbx'.$mT->id.'" id="ckbx'.$mT->id.'" type="checkbox" data-href="'.$pages->get('name=submitforms')->url.'?form=inClass&eventId='.$mT->id.'" class="simpleAjax" data-hide-feedback="true" value="" '.$checked.' /> [in class]</label>';
          }
          $out .= '</li>';
          $out .= '</ul>';
          $teamUt += ($inUt+$outUt);
        }
      }
      $out .= '</ul>';
    }
    $out .= '<p class="label label-success">Total : +'.sprintf(__("%dUT for the team over the selected period"), $teamUt).'</p>';
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
