<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');

$player = $pages->get("name=$page->name");
if ($player->avatar) {
  $avatar =  '<img style="float: left;" src="'.$player->avatar->url.'" width="150" alt="Avatar" />';
} else {
  $avatar = '<Avatar>';
}

$out .= '<table>';
$out .= '<tr><td>';
$out .= $avatar;
$out .= '</td>';
$out .= '<td rowspan="2" style="width:8cm; background-color: #C366FF; border-right: 0px;">';
$out .= '<h1>';
$out .= 'Player\'s profile page for '.$player->title;
$out .= '</h1>';
$out .= '</td>';
$out .= '<td rowspan="2" style="background-color: #C366FF; border-left: 0px; padding: 0px;">';
$out .= '<img src="'.$logo.'" width="100" height="100" /> ';
$out .= '</td>';
$out .= '</tr>>';
$out .= '<tr><td>';
$out .= 'Login: '.$player->login.' / Password : ___________________';
$out .= '</td>';
$out .= '<tr>';
$out .= '</table>';

$allElements = $player->people;
$allElements = $allElements->add($player->places);
$allEquipment = $player->equipment->sort("category.name");

$out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
$out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
$out .= '<h3 style="margin: 0px;">My Equipment</h3>';
$out .= '<table style="border: 0px; margin-top: 0px;">';
$index = 0;
foreach($allEquipment as $e) {
  if (in_array($index, [0, 8, 16, 24, 32])) {
    $out .= '<tr>';
  }
  $out .= '<td style="background-color: #FFF; text-align: left; border:0px;">';
  $out .= '<img src="'.$e->image->url.'" alt="photo " />';
  $out .= '</td>';
  if (in_array($index, [7, 15, 23, 31] )) {
    $out .= '</tr>';
  }
  $index++;
}
$out .= '</table>';
$out .= '</div>';
$out .= '</div>';

$out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
$out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
$out .= '<h3 style="margin: 0px;">My Acts of Freedom</h3>';
for ($i=0; $i<$allElements->count(); $i+=2) {
  if (in_array($i, [6, 11, 16, 21, 26])) {
    $out .= '</div>';
    $out .= '</div>';
    $out .= '<pagebreak />';
    $out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
    $out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
    $out .= '<h3>My Acts of Freedom</h3>';
  }
  $e = $allElements->eq($i);
  $nextEl = $allElements->eq($i+1);
  if ($e) { $thumbImage = $e->photo->eq(0)->getThumb("thumbnail"); }
  if ($nextEl) { $nextThumbImage = $nextEl->photo->eq(0)->getThumb("thumbnail"); }

  $out .= '<table class="miniTable">';
  $out .= '<tr>';
  $out .= '<td colspan="2" rowspan="2" style="width: 0.6cm; border: 2px solid #000;">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<th colspan="2" style="width: 4.5cm; height:0.7cm;">'.$e->title.'</th>';

  if ($nextEl) {
    $out .= '<td class="empty" style="width: 0.5cm;">&nbsp;</td>';
    $out .= '<td colspan="2" rowspan="2" style="width: 0.6cm; border: 2px solid #000;">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<th colspan="2" style="width: 4.5cm;">'.$nextEl->title.'</th>';
    $out .= '</tr>';
  } else {
    $out .= '<td class="empty" style="">&nbsp;</td>';
  }

  $out .= '<tr>';
  $out .= '<td style="">&nbsp;</td>';
  $out .= '<td style="">&nbsp;</td>';
  $out .= '<td style="">&nbsp;</td>';
  $out .= '<td style="">&nbsp;</td>';
  $out .= '<td style="">&nbsp;</td>';
  $out .= '<td style="">&nbsp;</td>';
  if ($e->template == 'people') { $field = $e->nationality; }
  if ($e->template == 'place') { $field = $e->city->title; }
  $out .= '<th style="width: 2cm; height:0.7cm;">'.$field.'</th>';
  $out .= '<th style="width: 2cm;">'.$e->country->title.'</th>';

  if ($nextEl) {
    $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    if ($nextEl->template == 'people') { $field = $nextEl->nationality; }
    if ($nextEl->template == 'place') { $field = $nextEl->city->title; }
    $out .= '<th style="width:2cm">'.$field.'</th>';
    $out .= '<th style="width:2cm;">'.$nextEl->country->title.'</th>';
    $out .= '</tr>';
  } else {
    $out .= '<td class="empty" style="">&nbsp;</td>';
  }


  $out .= '<tr>';
  $out .= '<td colspan="8" style="width:2cm; height:3.5cm;"><img style="border: 2px solid #000;" src="'.$thumbImage.'" /></td>';
  $textLength = strlen($e->summary);
  $fontSize = '10px;';
  if ($textLength >= 600) { $fontSize = '8px'; }
  if ($textLength >= 500 && $textLength < 600) { $fontSize = '10px'; }
  if ($textLength >= 400 && $textLength < 500) { $fontSize = '11px'; }
  if ($textLength >= 300 && $textLength < 400) { $fontSize = '12px'; }
  if ($textLength >= 200 && $textLength < 300) { $fontSize = '14px'; }
  if ($textLength < 200) { $fontSize = '16px'; }
  $out .= '<td colspan="2" style="width:7cm; font-size:'.$fontSize.';">'.$e->summary.'</td>';

  if ($nextEl) {
    $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';
    $out .= '<td colspan="8" style="width:2cm;"><img style="border: 2px solid #000;" src="'.$nextThumbImage.'" /></td>';
    $textLength = strlen($nextEl->summary);
    $fontSize = '10px;';
    if ($textLength >= 600) { $fontSize = '8px'; }
    if ($textLength >= 500 && $textLength < 600) { $fontSize = '10px'; }
    if ($textLength >= 400 && $textLength < 500) { $fontSize = '11px'; }
    if ($textLength >= 300 && $textLength < 400) { $fontSize = '12px'; }
    if ($textLength >= 200 && $textLength < 300) { $fontSize = '14px'; }
    if ($textLength < 200) { $fontSize = '16px'; }
    $out .= '<td colspan="2" style="width:7cm; font-size:'.$fontSize.';">'.$nextEl->summary.'</td>';
    $out .= '</tr>';
  } else {
    $out .= '<td class="empty" style="width:10.5cm">&nbsp;</td>';
  }

  $out .= '</table>';
}
$out .= '</div>';
$out .= '</div>';


echo $out;


?>

