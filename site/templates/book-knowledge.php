<?php namespace ProcessWire; /* book-knowledge template */
  include("./head.inc");

  $out = '';

  $out .= '<h1>The Book of Knowledge</h1>';

  $allLessons = $page->children();

  $out .= '<ul>';
  foreach($allLessons as $l) {
    // TODO : Calculate possible credit according to player's equipment
    $length = strlen($l->body);
    $out .= '<li>';
    $out .= 'Level '.$l->level.' →  <a href="'.$l->url.'">'.$l->title.'</a> <span class="label label-default">+'.$l->GC.'GC</span> <span class="label label-default">+'.$l->XP.'XP</span>';
    $out .= ' ('.$length.' signs)';
    $out .= '</li>';
  }
  $out .= '</ul>';

  echo $out;

  include("./foot.inc");
?>
