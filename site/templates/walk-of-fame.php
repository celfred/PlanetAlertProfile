<?php
include("./head.inc"); 

$allPlayers = $pages->find("template=player, level>1, sort=-level");

$out = '';

$out .= '<section class="row">';
$out .= '<h1 class="well text-center">';
$out .= $page->title;
$out .= '<span class="pull-right" data-toggle="tooltip" title="You need to be at least Level 10 to appear in the Walk of Fame"><span class="glyphicon glyphicon-question-sign"></span></span>';
$out .= '</h1>';

$out .= '<div class="masonryContainer" data-masonry=\'{ \"itemSelector\": \".grid-item\", \"columnWidth\": 200 }\'>';
foreach ($allPlayers as $p) {
  if ($p->team->name != 'no-team') {
    $team = ' ['.$p->team->title.']';
  } else {
    $team = '';
  }
  if ($p->avatar) {
    if ($p->level >= 10 && $p->level<15) { $width = 80; }
    if ($p->level >= 15 && $p->level<20) { $width = 110; }
    $width = 20+$p->level*6;
    $avatar = '<img class="img-thumbnail" data-toggle="tooltip" data-html="true" title="Level '.$p->level.'" src="'.$p->avatar->getThumb('thumbnail').'" width="'.$width.'" alt="avatar" />';
  } else {
    $avatar = '';
  }
  $panel = '<div class="masonryImage playerDiv">';
  $panel .= '<div>'.$avatar.'</div>';
  $panel .= '<span class="label label-default">'.$p->title.$team.'</span>';
  $panel .= '</div>';
  $out .= $panel;
}
$out .= '</div>';

$out .= '</section>';

echo $out;

include("./foot.inc"); 
?>
