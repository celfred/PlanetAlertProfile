<?php
  $out = '';
  $out .= '<section class="well">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    include("report-title.inc"); 
    // Limit to logged in user
    if ($user->isSuperuser()) {
      $allMonstersIds = $pages->findIds("parent.name=monsters, template=exercise, include=all");
    } else {
      $allMonstersIds = $pages->findIds("parent.name=monsters, template=exercise, (teacher=$user), (created_users_id=$user->id), include=all");
    }
    $teamUt = 0;
    foreach($allPlayers as $p) {
      $playersTrainings = $pages->find("has_parent=$p, template=event, task.name~=ut-action, refPage!='', date>=$period->dateStart, date<=$period->dateEnd");
      $outUt = 0; // Out of class UT
      $inUt= 0; // in class UT
      $out .= '<ul>';
      $out .= '<li><strong>'.$p->title.'</strong> : ';
      if ($playersTrainings->count() > 0) {
        $out .= '<span class="label label-primary">';
        $out .= sprintf(_n("%d session", "%d sessions", $playersTrainings->count()), $playersTrainings->count());
        $out .= '</span>';
      } else {
        $out .= '-';
      }
      $out .= '</li>';
      foreach($allMonstersIds as $mId) {
        $mTrainings  = $playersTrainings->find("refPage.id=$mId")->sort("refPage->title");
        if ($mTrainings->count() > 0) {
          $out .= '<ul>';
          foreach($mTrainings as $mT) {
            preg_match("/\[\+([\d]+)U\.T\.\]/", $mT->summary, $matches);
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
          $out .=	' ['.$inUt.' '.__("in class").'] - ';
          $out .= sprintf(_n("%d session", "%d sessions", $mTrainings->count()), $mTrainings->count()).'</li>';
          $out .= '</ul>';
        }
      }
      $teamUt += ($inUt+$outUt);
      $out .= '</ul>';
    }
    $out .= '<p class="label label-success">Total : +'.sprintf(__("%dUT for the team"), $teamUt).'</p>';
  } else {
    $out .= $noAuthMessage;
  }
  $out .= '</section>';

  echo $out;
?>
