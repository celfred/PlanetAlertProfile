<?php 
$out = '';
$logo = 'http://download.tuxfamily.org/planetalert/logo.png';
$favicon = 'http://download.tuxfamily.org/planetalert/favicon.png';
$worldMap = 'http://download.tuxfamily.org/planetalert/map/worldMap-medium-empty.png';

$pageNumber = $input->get->index;
$totalPages = $input->get->total;
$nbElPerPage = 8;
$player = $pages->get("name=$page->name");

$footer = '<div style="text-align:center; font-size: 8px;">';
$footer .= '<img src="'.$favicon.'" alt="Planet Alert" height="20"/> https://planetalert.tuxfamily.org - '.__("Tested on Firefox");
if ($pageNumber > 0) {
  $pageIndex = $pageNumber+1;
  $footer .= ' [Page '.$pageIndex.'/'.$totalPages.']';
}
$footer .= '</div>';
if ($pageNumber == 0) { // Player's equipment
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
  $out .= sprintf(__("Player's profile page for %s"), $player->title);
  if ($player->team->name != 'no-team') {
    $out .= ' ['.$player->team->title.']';
  }
  $out .= '</h1>';
  $out .= '</td>';
  $out .= '<td rowspan="2" style="background-color: #C366FF; border-left: 0px; padding: 0px;">';
  $out .= '<img src="'.$logo.'" width="100" height="100" /> ';
  $out .= '</td>';
  $out .= '</tr>>';
  $out .= '<tr><td>';
  $out .= __('Login').': '.$player->login.' / ';
  $out .= __("Password").' : _________________';
  $out .= '</td>';
  $out .= '<tr>';
  $out .= '</table>';

  $allEquipment = $player->equipment->sort("category.name");
  $out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
  $out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
  $out .= '<h3 style="margin: 0px;">'.__("My Equipment").'</h3>';
  $out .= '<table style="border: 0px; margin-top: 0px;">';
  $index = 0;
  for ($line=0; $line<4; $line++) {
    $eqMax = $line+7;
    $out .= '<tr>';
    $out .= '<td style="height:100px; width:1px; background-color: #FFF; border:0px;"></td>';
    while($index<7) {
      $out .= '<td style="height:100px; background-color: #FFF; text-align: left; border:0px;">';
      $image = $allEquipment->eq($index+($line*7))->image;
      if ($image) {
        if ($image->width() > $image->height()) {
          $thumbImage = $allEquipment->eq($index+($line*7))->image->getCrop("small");
          $out .= '<img src="'.$thumbImage->url.'" alt="photo" />';
        } else {
          $thumbImage = $allEquipment->eq($index+($line*7))->image->getCrop("thumbnail");
          $out .= '<img src="'.$thumbImage->url.'" alt="photo" />';
        }
      }
      $out .= '</td>';
      $index++;
    }
    $out .= '</tr>';
    $index=0;
  }
  $out .= '</table>';
  $out .= '</div>';
  $out .= '</div>';

  $out .= '<div style="margin: 5px; text-align: center;">';
  $out .= '<img src="'.$worldMap.'" height="300" />';
  $out .= '</div>';
  
  $out .= $footer;
} else if ($pageNumber > 0) { // Player's Free Elements
  $allElements = $player->people;
  $allElements = $allElements->add($player->places);
  $allElements->sort("country.name");

  // Manage elements indexes according to pageNumber
  $startIndex = $nbElPerPage*($pageNumber-1);
  $endIndex = $startIndex+10;

  $out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
  $out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
  $out .= '<h3 style="margin: 0px;">'.__("My Acts of Freedom").' - '.$player->title.' ['.$player->team->title.']</h3>';
  // 5 lines to fill the page
  $out .= '<table>';
  for ($line=0; $line<4; $line++) {
    $out .= '<tr><td style="border:0px;height:5.6cm;">';
    $elIndex = $startIndex+($line*2);
    $nextElIndex = $startIndex+($line*2)+1;
    if ($elIndex < $allElements->count()) {
      $e = $allElements->eq($elIndex);
      $nextEl = $allElements->eq($nextElIndex);
      if ($e) { $thumbImage = $e->photo->eq(0)->getCrop("thumbnail"); }
      if ($nextEl) { $nextThumbImage = $nextEl->photo->eq(0)->getCrop("thumbnail"); }
      $out .= '<table class="miniTable">';
      $out .= '<tr>';
      $out .= '<td colspan="3" rowspan="2" style="padding: 0.1cm; width: 0.8cm; border: 2px solid #000;">&nbsp;</td>';
      $out .= '<td style="width:0.2cm">&nbsp;</td>';
      $out .= '<td style="width:0.2cm">&nbsp;</td>';
      $out .= '<td style="width:0.2cm">&nbsp;</td>';
      $out .= '<td style="width:0.2cm">&nbsp;</td>';
      $out .= '<td style="width:0.2cm">&nbsp;</td>';
      $out .= '<th colspan="2" style="width: 4.5cm; height:0.7cm;">'.$e->title.'</th>';

      if ($nextEl) {
        $out .= '<td class="empty" style="width: 0.5cm;">&nbsp;</td>';
        $out .= '<td colspan="3" rowspan="2" style="padding: 0.1cm; width: 0.6cm; border: 2px solid #000;">&nbsp;</td>';
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
        if ($nextEl->template == 'people') { $field = $nextEl->nationality; }
        if ($nextEl->template == 'place') { $field = $nextEl->city->title; }
        $out .= '<th style="width:2cm">'.$field.'</th>';
        $out .= '<th style="width:2cm;">'.$nextEl->country->title.'</th>';
        $out .= '</tr>';
      } else {
        $out .= '<td class="empty" style="">&nbsp;</td>';
      }


      $out .= '<tr>';
      $out .= '<td colspan="8" style="width:2cm; height:3.5cm;"><img style="border: 2px solid #000; max-width:2.5cm;" src="'.$thumbImage->url.'" /></td>';
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
        $out .= '<td colspan="8" style="width:2cm;"><img style="border: 2px solid #000; max-width: 2.5cm;" src="'.$nextThumbImage->url.'" /></td>';
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
    $out .= '</td></tr>';
  }
  $out .= '</table>';
  $out .= '</div>';
  $out .= '</div>';

  $out .= '<div style="margin: 5px; text-align: center;">';
  $out .= '<img src="'.$worldMap.'" height="220" />';
  $out .= '</div>';

  $out .= $footer;
} else { // New empty PDF
  // First page
  $out .= '<table>';
  $out .= '<tr><td style="height:4cm; width:8cm;">';
  $out .= __('My Avatar');
  $out .= '</td>';
  $out .= '<td style="width:8cm; background-color: #C366FF; border-right: 0px;">';
  $out .= '<h1>'.__("Player's profile page").'</h1>';
  $out .= '</td>';
  $out .= '<td style="background-color: #C366FF; border-left: 0px; padding: 0px;">';
  $out .= '<img src="'.$logo.'" width="100" height="100" /> ';
  $out .= '</td>';
  $out .= '</tr>';
  $out .= '<tr>';
  $out .= '<td colspan="3">';
  $out .= '← '.__("Login").' ';
  $out .= '&nbsp;|&nbsp;';
  $out .= __('Password').'  →';
  $out .= '</td>';
  $out .= '</tr>';
  $out .= '</table>';

  $out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
  $out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
  $out .= '<h3 style="margin: 0px;">'.__("My Equipment").'</h3>';
  $out .= '<table style="border: 0px; margin-top: 0px;">';
  $index = 0;
  for ($line=0; $line<3; $line++) {
    $eqMax = $line+7;
    $out .= '<tr>';
    $out .= '<td style="height:4cm; width:1px; background-color: #FFF; border:0px;"></td>';
    $out .= '</tr>';
  }
  $out .= '</table>';
  $out .= '</div>';
  $out .= '</div>';

  $out .= '<div style="margin: 5px; text-align: center;">';
  $out .= '<img src="'.$worldMap.'" height="300" />';
  $out .= '</div>';
  
  $out .= $footer;

  // Second page
  $out .= '<pagebreak>';
  $out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
  $out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
  $out .= '<h3 style="margin: 0px;">'.__("My Acts of Freedom").'</h3>';
  // 5 lines to fill the page
  $out .= '<table style="border:0px;">';
  for ($line=0; $line<4; $line++) {
    $out .= '<tr><td style="border:0px;height:4.5cm;">';
    $out .= '</td>';
    $out .= '</tr>';
  }
  $out .= '</table>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div style="margin: 5px;text-align: center;">';
  $out .= '<img src="'.$worldMap.'" height="300" />';
  $out .= '</div>';
  
  $out .= $footer;
}

echo $out;

?>
