<?php namespace ProcessWire; /* tmp template */
  include("./head.inc"); 

  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    $tmpPages = $page->tmpMonstersActivity;
    $tmpPages->sort("monster.name");

    $recalculate = $input->get("recalculate");
    $confirm = $input->get("confirm");
    $init = $input->get("init");
    $out = '';

    $player = $page->parent();
    $out .= '<div class="row text-center">';
      $out .= '<h4>tmpCache for <a href="'.$pages->get("name=players")->url.$player->team->name.'/'.$player->name.'">'.$player->title.'</a></h4>';
      // Action buttons
      $out .= '<p>';
      $out .= '<a class="btn btn-danger" href="'.$page->url.'?recalculate=1&confirm=1&init=1">Initialize</a>';
      $nbCached = count($page->tmpMonstersActivity->children());
      if ($nbCached > 0) {
        $out .= '<a class="btn btn-info" href="'.$page->url.'?recalculate=1&confirm=0">Recalculate / Check</a>';
      }
      $out .= '</p>';
      $out .= '<p>';
      if ($recalculate) {
        if ($confirm) {
          if ($init) { initTmpMonstersActivity($player); }
          $out .= '<p>';
          $out .= 'New values have been saved.';
          $out .= ' <a class="btn btn-success" href="'.$page->url.'">Reload tmp page</a>';
          $out .= '</p>';
        } else {
          $out .= '<a class="btn btn-primary" href="'.$page->url.'?recalculate=1&confirm=1">Save new values ?</a>';
        }
      }
      $out .= '</p>';

      if (!$confirm) {
        if ($nbCached > 0) {
          $out .= '<p>Never trained on '.$page->index.' monsters.';
        } else {
          $out .= '<p>No tmp activity. You should initialize tmp page.</p>';
        }
        if ($recalculate) {
          $neverTrained =  ($pages->count("parent.name=monsters")-$tmpPages->count());
          if ($neverTrained != $page->index) {
            $out .= '<span class="label label-danger">⇒ '.$neverTrained.'</span>';
            if ($neverTrained < 0) {
              $out .= '<p class="label label-danger"><i class="glyphicon glyphicon-warning-sign"></i> Error detected ! Page should be initialized !</p>';
            }
            $page->index = $neverTrained;
          } else {
            $out .= '<span class="label label-success">OK</span>';
          }
        }
      }
      /* if ($recalculate != 1) { */
      /*   if ($page->tmpMonstersActivity->eq(0)) { */
      /*     $out .= '<a class="btn btn-info" href="'.$page->url.'?recalculate=1&confirm=0">Recalculate tmp page</a>'; */
      /*   } else { */
      /*     $out .= '<a class="btn btn-info" href="'.$page->url.'?recalculate=1&confirm=1&init=1">Init tmp page from player\'s history ?</a>'; */
      /*   } */
      /* } else { */
      /*   if ($confirm != 1) { */
      /*       $out .= '<a class="btn btn-info" href="'.$page->url.'?recalculate=1&confirm=1">Save new values ?</a>'; */
      /*   } else { */
      /*     if ($init) { */
      /*       initTmpMonstersActivity($player); */
      /*     } */
      /*     $out .= 'New values have been saved.'; */
      /*     $out .= ' <a class="btn btn-success" href="'.$page->url.'?recalculate=1&confirm=0">Reload tmp page</a>'; */
      /*   } */
      /* } */
      /* if (!$init && $tmpPages->count() > 0) { */
      /*   $out .= '<p>Never trained on '.$page->index.' monsters. '; */
      /*     if ($recalculate == 1) { */
      /*       $neverTrained =  ($pages->count("parent.name=monsters")-$tmpPages->count()); */
      /*       if ($neverTrained != $page->index) { */
      /*         $out .= '<span class="label label-danger">⇒ '.$neverTrained.'</span>'; */
      /*         if ($neverTrained < 0) { */
      /*           $out .= '<p class="label label-danger"><i class="glyphicon glyphicon-warning-sign"></i> Error detected !</p>'; */
      /*           $out .= '<p><a class="btn btn-danger" href="'.$page->url.'?recalculate=1&confirm=1&init=1">Init tmp page from player\'s history ?</a></p>'; */
      /*         } */
      /*         $page->index = $neverTrained; */
      /*       } else { */
      /*         $out .= '<span class="label label-success">OK</span>'; */
      /*       } */
      /*     } */
      /*   $out .= '</p>'; */
      /* } */
    $out .= '</div>';

    if ($confirm != 1 && !$init && $page->tmpMonstersActivity->count() > 0) {
      $out .= '<table class="table table-condensed">';
        $out .= '<tr>';
        $out .= '<th>Monster Activity</th>';
        $out .= '</tr>';
        $out .= '<tr>';
        foreach($page->tmpMonstersActivity->eq(0)->fields as $f) {
          $out .= '<td>';
          if ($f->name == 'date') {
            $out .= $f->label;
          } else {
            $out .= $f->name;
          }
          $out .= '</td>';
        }
        $out .= '</tr>';
        foreach($tmpPages as $p) {
          $out .= '<tr>';
            foreach ($p->fields as $f) {
              if ($f->name == 'monster') {
                $out .= '<td>'.$p->$f->title.'</td>';
                if ($recalculate) {
                  $m = $p->$f;
                  $m = setMonster($player, $m);
                }
              } else {
                $out .= '<td>';
                if (stripos($f->name, 'date') !== false && $p->$f != '') { 
                  if ($p->$f != '') {
                    $out .= date("d/m/Y", $p->$f);
                  }
                } else {
                  $out .= $p->$f;
                }
                if ($recalculate) {
                  $out .= '<br />';
                  if ($m->$f == $p->$f) {
                    $out .= '<span class="label label-success">OK</span>';
                  } else {
                    $out .= '<span class="label label-danger">⇒ ';
                    if (stripos($f->name, 'date') !== false && $m->$f != '') { 
                      $out .= date("d/m/Y", $m->$f);
                    } else {
                      $out .= $m->$f;
                    }
                    $p->$f = $m->$f; // Set new value
                    $out .= '</span>';
                  }
                }
                $out .= '</td>';
              }
            }
            if ($confirm) { // Save new monster cache
              if ($m->trainNb == 0 && $m->fightNb == 0) {
                $page->tmpMonstersActivity->remove($p);
              }
              $p->of(false);
              $p->save();
            }
          $out .= '</tr>';
        }
      $out .= '</table>';
    }

    if (!$init && $confirm == 1) { // Save new cache
      $page->of(false);
      $page->save();
    }
  } else {
    $out = 'This page is for admin only.';
  }

  echo $out;

  include("./foot.inc"); 
?>
