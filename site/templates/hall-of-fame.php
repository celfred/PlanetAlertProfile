<?php
include("./head.inc"); 

$allPlayers = $pages->find("template=player, level>1")->shuffle();

$out = '';

$out .= '<section class="row">';
$out .= '<h1 class="well text-center">';
$out .= $page->title;
$out .= '<span class="pull-right" data-toggle="tooltip" title="The higher your level is, the bigger your avatar is ;)"><span class="glyphicon glyphicon-question-sign"></span></span>';
$out .= '</h1>';

$out .= '<div class="grid">';
foreach ($allPlayers as $p) {
  if ($p->team->name != 'no-team') {
    $team = ' ['.$p->team->title.']';
  } else {
    $team = '';
  }
  if ($p->avatar) {
    if ($p->level <= 7 ) { $class = 'grid-item'; }
    if ($p->level > 7 && $p->level<=12) { $class = 'grid-item--width2'; }
    if ($p->level > 12 && $p->level<=17) { $class = 'grid-item--width3'; }
    if ($p->level > 17 && $p->level<=22) { $class = 'grid-item--width4'; }
    if ($p->level > 22) { $class = 'grid-item--width5'; }
    $out .= '<div class="'.$class.' playerDiv">';
    $out .= '<a href="'.$p->url.'"><img class="img-thumbnail" data-toggle="tooltip" data-html="true" title="'.$p->title.$team.'<br />Level '.$p->level.'" src="'.$p->avatar->url.'" alt="avatar" /></a>';
    $out .= '</div>';
  }
}
$out .= '</div>';

$out .= '</section>';

echo $out;

include("./foot.inc"); 
?>
