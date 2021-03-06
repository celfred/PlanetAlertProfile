<?php 

$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');
$largeLogo = $pages->get('/')->photo->eq(0)->url;

$out = '';

if ($input->urlSegment1  == 'all') { // All places catalog
  $places = $pages->find("template='place',sort='level'")->not("name=places");

  $currentLevel = 0;
  foreach ($places as $place) {
    if ($place->level !== $currentLevel) {
      if ($currentLevel > 0) {
        $out .= '</table>';
        $out .= '<pagebreak />';
      } 
      $out .= '<div style="text-align: center; border: 3px solid #000; border-radius: 50px;">';
      $out .= '<img style="margin: 30px;" src="'.$largeLogo.'" />';
      $nbPlaces = $pages->find("template='place',level=$place->level")->count();
      $out .= '<h1>The Map</h1>';
      $out .= '<p style="font-size: 50px; text-decoration: underline;">Level '.$place->level.'<p>';
      $out .= '<p style="font-size: 40px;">'.$nbPlaces.' places</p>';
      $out .= '<img style="margin: 50px;" src="'.$logo.'" />';
      $out .= '</div>';
      $out .= '<pagebreak />';
      $out .=  '<table class="table table-condensed table-hover">';
      $out .= '<tr>';
        $out .= '<th>'.__('Level').'</th>';
        $out .= '<th>'.__('Gold coins (GC)').'</th>';
        $out .= '<th>'.__('Place').'</th>';
        $out .= '<th>'.__('Country').'</th>';
        $out .= '<th>'.__('City').'</th>';
        $out .= '<th>'.__('Summary').'</th>';
        $out .= '<th>'.__('Photo').'</th>';
      $out .= '<tr>';
    }
    $currentLevel = $place->level;
    $out .= '<tr>';
      $out .= '<td>'.$place->level.'</td>';
      $out .= '<td>'.$place->GC.'</td>';
      $out .= '<td>'.$place->title.'</td>';
      $out .= '<td>'.$place->country->title.'</td>';
      $out .= '<td>'.$place->city->title.'</td>';
      $out .= '<td>'.$place->summary.'</td>';
      $out .= '<td>';
      $thumbImage = $place->photo->eq(0)->getCrop('thumbnail');
      $out .= '<img style="border: 2px solid #000; max-width:2.5cm;" src="'.$thumbImage->url.'" />';
      $out .= '</td>';
    $out .= '</tr>';
  }

  $out .= '</table>';
} else { // 1 place PDF
  $place = $pages->get("template=place, name=$page->name");
  $thumbImage = $place->photo->eq(0)->getCrop('thumbnail');

  for ($i=0; $i<5; $i++) {
    $out .= '<table class="miniTable">';
    $out .= '<tr>';
    $out .= '<td colspan="3" rowspan="2" style="width: 0.8cm; border: 2px solid #000;">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<th colspan="2" style="width:4.5cm; height:0.7cm;">'.$page->title.'</th>';

    $out .= '<td class="empty" style="width:0.5cm">&nbsp;</td>';

    $out .= '<td colspan="3" rowspan="2" style="width: 0.8cm; border: 2px solid #000;">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<th colspan="2" style="width:4.5cm;">'.$page->title.'</th>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    if ($page->template == 'people') { $field = $page->nationality; }
    if ($page->template == 'place') { 
      if ( $page->city->id ) {
        $field = $page->city->title;
      } else {
        $field = '-';
      }
    }
    $out .= '<th style="width: 2cm; height:0.7cm;">'.$field.'</th>';
    $out .= '<th style="width: 2cm;">'.$page->country->title.'</th>';

    $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';

    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    if ($page->template == 'people') { $field = $page->nationality; }
    if ($page->template == 'place') {
      if ( $page->city->id ) {
        $field = $page->city->title;
      } else {
        $field = '-';
      }
    }
    $out .= '<th style="width: 2cm; height:0.7cm;">'.$field.'</th>';
    $out .= '<th style="width: 2cm;">'.$page->country->title.'</th>';
    $out .= '</tr>';

    $out .= '<tr>';
    if ($thumbImage->width < $thumbImage->height) {
      $out .= '<td colspan="8" style="width:3cm; height:4cm;"><img style="max-width:2.8cm; height:3.3cm; border: 1px solid #000;" src="'.$thumbImage->url.'" /></td>';
    } else {
      $out .= '<td colspan="8" style="width:3cm; height:4cm;"><img style="width:2.8cm; max-height:3.3cm; border: 1px solid #000;" src="'.$thumbImage->url.'" /></td>';
    }
    $textLength = strlen($page->summary);
    $fontSize = '10px;';
    if ($textLength >= 600) { $fontSize = '8px'; }
    if ($textLength >= 500 && $textLength < 600) { $fontSize = '10px'; }
    if ($textLength >= 400 && $textLength < 500) { $fontSize = '11px'; }
    if ($textLength >= 300 && $textLength < 400) { $fontSize = '12px'; }
    if ($textLength >= 200 && $textLength < 300) { $fontSize = '14px'; }
    if ($textLength < 200) { $fontSize = '16px'; }
    $out .= '<td colspan="2" style="width:7cm; font-size:'.$fontSize.';">'.$page->summary.'</td>';

    $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';

    if ($thumbImage->width < $thumbImage->height) {
      $out .= '<td colspan="8" style="width:3cm; height:4cm;"><img style="max-width:2.8cm; height:3.3cm; border: 1px solid #000;" src="'.$thumbImage->url.'" /></td>';
    } else {
      $out .= '<td colspan="8" style="width:3cm; height:4cm;"><img style="width:2.8cm; max-height:3.3cm; border: 1px solid #000;" src="'.$thumbImage->url.'" /></td>';
    }
    $textLength = strlen($page->summary);
    $fontSize = '10px;';
    if ($textLength >= 600) { $fontSize = '8px'; }
    if ($textLength >= 500 && $textLength < 600) { $fontSize = '10px'; }
    if ($textLength >= 400 && $textLength < 500) { $fontSize = '11px'; }
    if ($textLength >= 300 && $textLength < 400) { $fontSize = '12px'; }
    if ($textLength >= 200 && $textLength < 300) { $fontSize = '14px'; }
    if ($textLength < 200) { $fontSize = '16px'; }
    $out .= '<td colspan="2" style="width:7cm; font-size:'.$fontSize.';">'.$page->summary.'</td>';
    $out .= '</tr>';

    $out .= '</table>';
  }

}

echo $out;

?>

