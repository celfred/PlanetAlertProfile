<?php namespace ProcessWire;

  include("./head.inc"); 

  $playerPage = $pages->get("template=player,name=".$input->urlSegment1);
  if ($user->isSuperuser() || ($user->hasRole('teacher') && $playerPage->team->teacher->has("id=$user->id")) || ($user->isLoggedin() && $user->name == $playerPage->login && $playerPage->skills->has("name=fighter"))) {
    $requirements = true;
  } else {
    $requirements = false;
  }

  $out = '';

  if ($requirements) {
    $tmpPage = $playerPage->child("name=tmp");
    if ($user->isSuperuser()) {
      $allMonsters = $pages->get("name=monsters")->children("include=all")->sort("level, name");
    } else {
      if ($user->hasRole('player')) {
        if ($playerPage->equipment->has("name~=visualizer") || $playerPage->team->forceVisualizer == 1) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1")->sort("name");
        } else {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=0")->sort("name");
          $hiddenMonstersNb = $pages->count("parent.name=monsters, template=exercise, (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1), special=1");
        }
      } else {
        $allMonsters = $pages->get("name=monsters")->children("(created_users_id=$headTeacher->id), (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1)")->sort("level, name");
      }
    }
    $out .= '<h2 class="text-center">';
    $out .= '<span class="label label-primary">';
    $out .= '<span class="glyphicon glyphicon-time"></span> ';
    $out .= __("Welcome to the Fighters Playground !");
    if ($user->isSuperuser()) {
      $out .= ' ('.__('for').' '.$playerPage->title.' ['.$playerPage->team->title.'])';
    }
    $out .= '</span>';
    $out .= '</h2>';
    if (isset($hiddenMonstersNb)) { // Display helpAlert for Electronic visualizer
      $helpAlert = true;
      $link = '<a href="'.$pages->get("name=shop")->url.'/details/electronic-visualizer">Electronic Visualizer</a>';
      $helpTitle = __("Some monsters are absent !");
      $helpMessage = '<img src="'.$pages->get("name~=visualizer")->image->getCrop("small")->url.'" alt="image" /> ';
      $helpMessage .= '<h4>'.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link).'</h4>';
    }
    $out .= '<table id="fightersTable" class="table table-hover table-condensed">';
    $out .= '<thead>';
    $out .= '<tr>';
    $out .= '<th>'.__("Monster").'</th>';
    $out .= '<th>'.__("Your training activity").'</th>';
    $out .= '<th>'.__("Your fights").'</th>';
    $out .= '<th>'.__("Speed Quiz Access").' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="1 successful fight (VV) required !"></span></th>';
    $out .= '<th>'.__("Your best time").'</th>';
    $out .= '<th>'.__("Master Best time").'</th>';
    $out .= '</tr>';
    $out .= '</thead>';
    $out .= '<tbody>';
    foreach ($allMonsters as $m) {
      $concerned = $tmpPage->tmpMonstersActivity->get("monster=$m");
      $out .= '<tr>';
      $out .= '<td class="text-right">';
      $out .= $m->title.' <span class="monsterInfo" data-toggle="tooltip" title="'.__('Click for more information').'" data-href="'.$m->url.'"><span class="glyphicon glyphicon-info-sign"></span></span>';
      $out .= '</td>';
      $out .= '<td>';
      if ($concerned) {
        $out .= '<span class="label label-primary">'.($concerned->inUt+$concerned->outUt).__("UT").'</span>';
      } else {
        $out .= '-';
      }
      $out .= '</td>';
      $out .= '<td>';
      $validFights = 0;
      if ($concerned) {
        if ($concerned->fightNb > 0) {
          $allFights = $playerPage->find("task.name~=fight, refPage=$m");
          foreach ($allFights as $f) {
            switch($f->task->name) {
              case 'fight-vv' : $validFights++; $out .= '<span class="label label-success">VV</span>'; break;
              case 'fight-v' : $out .= '<span class="label label-success">V</span>'; break;
              case 'fight-r' : $out .= '<span class="label label-danger">R</span>'; break;
              case 'fight-rr' : $out .= '<span class="label label-danger">RR</span>'; break;
              default : $out .= '<span class="label label-primary">-</span>';
            }
            $out .= '&nbsp;';
          }
        } else {
          $out .= '-';
        }
      } else {
        $out .= '-';
      }
      $out .= '</td>';
      $out .= '<td class="">';
        if ($validFights >= 1) {
          $out .= '<a class="btn btn-xs btn-primary" href="'.$pages->get("name=speed-quiz")->url.$m->id.'"><span class="glyphicon glyphicon-time" data-toggle="tooltip" title="'.__("Start a Speed Quiz !").'"></span></a>';
          if ($user->isSuperuser() || $user->hasRole('teacher')) {
            $out .= ' <a class="btn btn-xs btn-primary" href="'.$pages->get("name=speed-quiz")->url.$m->id.'">'.__("Teacher's access").'</a>';
          }
        } else {
          $out .= __("No access");
          if ($user->isSuperuser() || $user->hasRole('teacher')) {
            $out .= ' <a class="btn btn-xs btn-primary" href="'.$pages->get("name=speed-quiz")->url.$m->id.'">'.__("Teacher's access").'</a>';
          }
        }
      $out .= '</td>';
      $out .= '<td>';
      if (isset($concerned) && $concerned->bestTime) {
        $out .= ms2string($concerned->bestTime);
      } else {
        $out .= '-';
      }
      $out .= '</td>';
      $out .= '<td>';
      if ($m->bestTime) {
        $master = $pages->get($m->bestTimePlayerId);
        $out .= '<span class="label label-success">';
        $out .= ms2string($m->bestTime).' '.__('by').' ';
        if ($user->name == $master->name) {
          $out .= 'YOU !';
        } else {
          $out .= $master->title.' ['.$master->team->title.']';
        }
        $out .= '</span>';
      } else {
        $out .= '-';
      }
      $out .= '</td>';
      $out .= '</tr>';
    }
    $out .= '</tbody>';
    $out .= '</table>';
  } else {
    $out .= $noAuthMessage;
  }

  echo $out;

  include("./foot.inc"); 

