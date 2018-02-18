<?php namespace ProcessWire; /* book-knowledge template */
  include("./head.inc");

  if ($user->isSuperuser()) {
    $player = $pages->get("template=player, name=test");
  }

  $out = '';

  $out .= '<h1 class="text-center">The Book of Knowledge</h1>';

  $allLessons = $page->children();

  // TODO : Make a dataTable ?
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
      $out .= '<td> <a href="'.$l->url.'">'.$l->title.' <span class="glyphicon glyphicon-eye-open"></span></a></td>';
      $out .= '<td>';
        $out .= '<span class="label label-default">'.$l->topic->implode(', ', '{title}').'</span>';
      $out .= '</td>';
      $out .= '<td>'.$l->summary.'</td>';
      $out .= '<td><span class="label label-default">+'.($l->task->GC+$player->deltaGC).'GC</span> <span class="label label-default">+'.($l->task->XP+$player->deltaXP).'XP</span>';
      $length = strlen($l->body);
      $out .= ' ('.$length.' signs)</td>';
      $bought = $player->get("name=history")->find("task.name=buy-pdf, refPage=$l");
      if ($bought->count() == 1) {
        $out .= '<td><a href="'.$l->url.'?pages2pdf=1" class="btn btn-primary btn-sm">Download PDF</a></td>';
      } else {
        $out .= '<td>-</td>';
      }
      $out .= '</tr>';
    }
  $out .= '</table>';

  echo $out;

  include("./foot.inc");
?>
