<?php namespace ProcessWire;

include("./head.inc"); 

$out = '';

// Make French version available for guests (and English players)
$out .= '<div class="frenchVersion">';
if ($user->language->name == 'default') {
  $page->of(false);
  $out .= $page->body->getLanguageValue($french);
  $out .= '<p class="text-center"><a href="#" class="frenchVersion">[<i class="glyphicon glyphicon-remove"></i> '.__("Close").']</a></p>';
}
$out .= '</div>';

$out .= $page->body;

if ($user->language->name == 'default') {
  $out .= '<a href="#" class="frenchVersion">[<img class="img-rounded" src="'.$urls->templates.'img/flag_fr.png" alt="French flag." /> French version]</a>';
}

echo $out;

include("./foot.inc"); 
?>
