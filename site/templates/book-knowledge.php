<?php namespace ProcessWire; /* book-knowledge template */
  include("./head.inc");

  if ($user->isSuperuser()) {
    $player = $pages->get("template=player, name=test");
  }
  $out = '';

  $out .= '<h1 class="text-center">The Book of Knowledge</h1>';

  $allLessons = $page->children();

  // TODO : Make a dataTable ?
  $out .= '<ul>';
  foreach($allLessons as $l) {
    // Possible credit depends on player's equipment
    if ($user->isLoggedin()) {
      setDelta($player, $l->task);
    }
    $length = strlen($l->body);
    $out .= '<li>';
    $out .= 'Level '.$l->level.' →  <a href="'.$l->url.'">'.$l->title.'</a> <span class="label label-default">+'.($l->task->GC+$player->deltaGC).'GC</span> <span class="label label-default">+'.($l->task->XP+$player->deltaXP).'XP</span>';
    $out .= ' ('.$length.' signs)';
    $out .= '</li>';
  }
  $out .= '</ul>';

  echo $out;

  include("./foot.inc");
?>
