<?php namespace ProcessWire;
/* Single Task template */

  include("./head.inc"); 

  $out = '';

  // Get personalized values
  if (!$user->isSuperuser()) {
    $headTeacher = getHeadTeacher($user);
    $page = checkModTask($page, $headTeacher);
  }
  $sign = '';
  $out .= '<div class="well">';
  $out .= '<span class="badge badge-default">'.$page->category->title.'</span>&nbsp;';
  $out .= '<br />';
  $out .= '<br />';
  $out .= '<h2 class="inline"><strong>'.$page->title.'</strong>&nbsp;&nbsp;';
  if ($page->XP != 0) {
    if ($page->XP > 0) { $sign = '+'; }
    $page->GC > 0 ? $type = 'success' : $type = 'danger';
    $out .= '<span class="label label-'.$type.'">XP : '.$sign.$page->XP.'</span>&nbsp;';
  }
  if ($page->HP != 0) {
    if ($page->HP > 0) { $sign = '+'; }
    $page->GC > 0 ? $type = 'success' : $type = 'danger';
    $out .= '<span class="label label-'.$type.'">HP : '.$sign.$page->HP.'</span>&nbsp;';
  }
  if ($page->GC != 0) {
    if ($page->GC > 0) { $sign = '+'; }
    $page->GC > 0 ? $type = 'success' : $type = 'danger';
    $out .= '<span class="label label-'.$type.'">GC : '.$sign.$page->GC.'</span>&nbsp;';
  }
  $out .= '</h2>';
  if ($page->GC != 0 || $page->HP != 0 || $page->XP != 0) {
    $out .= '<span>(Depending on your equipment!)</span>';
  }
  $out .= '<h2 class="">'.$page->summary;
  $out .= '</h2>';
  $out .= '<br />';
  if ($user->language->name == 'default') {
    $page->of(false);
    if ($page->summary->getLanguageValue($french) != '') {
      $out .= '<a class="" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
      $out .= '<div class="collapse" id="collapseDiv">';
      $out .= '<div class="well">';
        $out .= nl2br($page->summary->getLanguageValue($french));
      $out .= '</div>';
      $out .= '</div>';
    }
  }

  $out .= '<a class="btn btn-block btn-primary" href="'.$pages->get('name=tasks')->url.'">Back to the Actions list.</a>';
  echo $out;

  include("./foot.inc"); 
?>
