<?php namespace ProcessWire; /* book-knowledge template */
  include("./head.inc");

  if ($user->isSuperuser()) {
    $player = $pages->get("template=player, name=test");
  }

  $out = '';

  $knowledgeItem = $pages->get("template=item, name~=book-knowledge");
  $out .= '<div class="well">';
  $out .= '<img class="pull-right" src="'.$knowledgeItem->image->getCrop("thumbnail")->url.'" alt="Image" /> ';
  $out .= '<h3 class="text-center">';
  $out .= $page->title;
  $out .= '</h3>';

  $allLessons = $page->children();

  $out .= '<table id="lessonsTable" class="table table-condensed table-hover">';
    $out .= '<thead>';
      $out .= '<th>Level</th>';
      $out .= '<th>Title</th>';
      $out .= '<th>Topics</th>';
      $out .= '<th>Summary</th>';
      $out .= '<th>Possible Credit</th>';
      $out .= '<th>PDF</th>';
    $out .= '</thead>';

    foreach($allLessons as $l) {
      // Possible credit depends on player's equipment
      if ($user->isLoggedin()) {
        setDelta($player, $l->task);
      }
      $out .= '<tr>';
      $out .= '<td>'.$l->level.' </td>';
        if ($user->isLoggedin() && ($player->equipment->has("name~=book-knowledge") || $player->team->forceKnowledge == 1)) {
          $out .= '<td> <a href="'.$l->url.'">'.$l->title.' <span class="glyphicon glyphicon-eye-open"></span></a></td>';
        } else {
          $out .= '<td>'.$l->title.'</td>';
        }
      $out .= '<td>';
        $out .= '<span class="label label-default">'.$l->topic->implode(', ', '{title}').'</span>';
      $out .= '</td>';
      $out .= '<td>'.$l->summary.'</td>';
      if ($user->isLoggedin()) {
        $out .= '<td>'.$l->task->title.' <span class="label label-default">+'.($l->task->GC+$player->deltaGC).'GC</span> <span class="label label-default">+'.($l->task->XP+$player->deltaXP).'XP</span>';
        /* $length = strlen($l->body); */
        /* $out .= ' ('.$length.' signs)</td>'; */
      } else {
        $out .= '<td>'.$l->task->title.'</td>';
      }
      if ($user->isLoggedin() && ($player->equipment->has("name~=book-knowledge") || $player->team->forceKnowledge == 1)) {
        $bought = $player->get("name=history")->find("task.name=buy-pdf, refPage=$l");
        if ($bought->count() == 1) {
          $out .= '<td><a href="'.$l->url.'?pages2pdf=1" class="btn btn-primary btn-sm">Download PDF</a></td>';
        } else {
          if ($user->isSuperuser()) {
            $out .= '<td><a href="'.$l->url.'?pages2pdf=1" class="btn btn-primary btn-sm">Download PDF</a></td>';
          } else {
            $out .= '<td>-</td>';
          }
        }
      } else {
        $out .= '<td>-</td>';
      }
      $out .= '</tr>';
    }
  $out .= '</table>';
  $out .= '</div>';

  echo $out;

  include("./foot.inc");
?>
