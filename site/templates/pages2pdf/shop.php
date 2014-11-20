<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');

$weapons = $pages->find("template='equipment', category='weapons', sort='level'");
$protections = $pages->find("template='equipment',category='protections', sort='level'");
$items = $pages->find("template='item', sort='level'");

$out = '';

$out .= '<img style="float: left;" src="'.$logo.'" />';
$out .= '<img style="float: right;" src="'.$logo.'" />';
$out .= '<h1 style="text-align: center; text-decoration : underline;">The Shop</h1>';

$out .= '<table class="">';

$out .= '<tr>';
$out .= '<th colspan="7"><h2>Weapons ('. $weapons->count.' items)</h2></th>';
$out .= '</tr>';
$out .= '<tr>';
$out .= '<th>Minimum level</th>';
$out .= '<th>GC</th>';
$out .= '<th>XP</th>';
$out .= '<th>Name</th>';
$out .= '<th>&nbsp;</th>';
$out .= '<th colspan="2">Summary</th>';
$out .= '</tr>';
foreach($weapons as $weapon) {
  $thumbImage = $weapon->image->getThumb('mini');
  $out .= '<tr>';
  $out .= '<td>'.$weapon->level.'</td>';
  $out .= '<td>'.$weapon->GC.'</td>';
  $out .= '<td>+'.$weapon->XP.'</td>';
  $out .= '<td>'.$weapon->title.'</td>';
  $out .= '<td><img src="'.$thumbImage.'" /></td>';
  $out .= '<td colspan="2">'.$weapon->summary.'</td>';
  $out .= '</tr>';
}

$out .= '<tr>';
$out .= '<th colspan="7"><h2>Protections ('. $protections->count.' items)</h2></th>';
$out .= '</tr>';
$out .= '<tr>';
$out .= '<th>Minimum level</th>';
$out .= '<th>GC</th>';
$out .= '<th>HP</th>';
$out .= '<th>Name</th>';
$out .= '<th>&nbsp;</th>';
$out .= '<th colspan="2">Summary</th>';
$out .= '</tr>';
foreach($protections as $protection) {
  $thumbImage = $protection->image->getThumb('mini');
  $out .= '<tr>';
  $out .= '<td>'.$protection->level.'</td>';
  $out .= '<td>'.$protection->GC.'</td>';
  $out .= '<td>+'.$protection->HP.'</td>';
  $out .= '<td>'.$protection->title.'</td>';
  $out .= '<td><img src="'.$thumbImage.'" /></td>';
  $out .= '<td colspan="2">'.$protection->summary.'</td>';
  $out .= '</tr>';
}

$out .= '<tr>';
$out .= '<th colspan="7"><h2>Potions ('. $items->count .' items)</th>';
$out .= '</tr>';
$out .= '<tr>';
$out .= '<th>Minimum level</th>';
$out .= '<th>GC</th>';
$out .= '<th>HP</th>';
$out .= '<th>XP</th>';
$out .= '<th>Name</th>';
$out .= '<th>&nbsp;</th>';
$out .= '<th>Summary</th>';
$out .= '</tr>';
foreach($items as $item) {
  $thumbImage = $item->image->getThumb('mini');
  $out .= '<tr>';
  $out .= '<td>'.$item->level.'</td>';
  $out .= '<td>'.$item->GC.'</td>';
  $out .= '<td>'.$item->HP.'</td>';
  $out .= '<td>'.$item->XP.'</td>';
  $out .= '<td>'.$item->title.'</td>';
  $out .= '<td><img src="'.$thumbImage.'" /></td>';
  $out .= '<td>'.$item->summary.'</td>';
  $out .= '</tr>';
}

$out .= '</table>';

echo $out;


?>

