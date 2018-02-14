<?php namespace ProcessWire; /* book-knowledge template */
  include("./head.inc");

  $out = '';

  $out .= '<h1>The Book of Knowledge</h1>';

  $allLessons = $page->children();

  $out .= '<ul>';
  foreach($allLessons as $l) {
    $length = strlen($l->body);
    $out .= '<li>';
    $out .= 'Level '.$l->level.' : <a href="'.$l->url.'">'.$l->title.'</a> : +'.$l->GC.'GC, +'.$l->XP.'XP';
    $out .= ' ('.$length.' signs)';
    $out .= '</li>';
  }
  $out .= '</ul>';

  echo $out;

  include("./foot.inc");
?>
