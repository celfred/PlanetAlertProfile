<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');
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
      $out .= '<th>';
      $out .= 'Level';
      $out .= '</th>';
      $out .= '<th>';
      $out .= 'Gold coins (GC)';
      $out .= '</th>';
      $out .= '<th>';
      $out .= 'Place';
      $out .= '</th>';
      $out .= '<th>';
      $out .= 'Country';
      $out .= '</th>';
      $out .= '<th>';
      $out .= 'City';
      $out .= '</th>';
      $out .= '<th>';
      $out .= 'Summary';
      $out .= '</th>';
      $out .= '<th>';
      $out .= '# of owners';
      $out .= '</th>';
      $out .= '<th>';
      $out .= 'Photo';
      $out .= '</th>';
      $out .= '<tr>';
    }
    $currentLevel = $place->level;
    $out .= '<tr>';
    $out .= '<td>';
    $out .= $place->level;
    $out .= '</td>';
    $out .= '<td>';
    $out .= $place->GC;
    $out .= '</td>';
    $out .= '<td>';
    $out .= $place->title;
    $out .= '</td>';
    $out .= '<td>';
    $out .= $place->country->title;
    $out .= '</td>';
    $out .= '<td>';
    $out .= $place->city->title;
    $out .= '</td>';
    $out .= '<td>';
    $out .= $place->summary;
    $out .= '</td>';
    $out .= '<td>';
    $out .= $place->maxOwners;
    $out .= '</td>';
    $out .= '<td>';
    $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
    $out .= '<img style="border: 2px solid #000;" src="'.$thumbImage.'" />';
    //$out .= $thumbImage;
    $out .= '</td>';
    $out .= '</tr>';
  }

  $out .= '</table>';
} else { // 1 place PDF
  $place = $pages->get("template='place', name='$page->name'");
  $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');

  for ($i=0; $i<5; $i++) {
    $out .= '<table class="miniTable">';
    $out .= '<tr>';
    $out .= '<td colspan="2" rowspan="2" style="width: 0.6cm; border: 2px solid #000;">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
    $out .= '<th colspan="2" style="width:4.5cm; height:0.7cm;">'.$page->title.'</th>';

    $out .= '<td class="empty" style="width:0.5cm">&nbsp;</td>';

    $out .= '<td colspan="2" rowspan="2" style="width: 0.6cm; border: 2px solid #000;">&nbsp;</td>';
    $out .= '<td style="width:0.2cm">&nbsp;</td>';
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
    $out .= '<td>&nbsp;</td>';
    if ($page->template == 'people') { $field = $page->nationality; }
    if ($page->template == 'place') { $field = $page->city->title; }
    $out .= '<th style="width: 2cm; height:0.7cm;">'.$field.'</th>';
    $out .= '<th style="width: 2cm;">'.$page->country->title.'</th>';

    $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';

    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    if ($page->template == 'people') { $field = $page->nationality; }
    if ($page->template == 'place') { $field = $page->city->title; }
    $out .= '<th>'.$field.'</th>';
    $out .= '<th>'.$page->country->title.'</th>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td colspan="8" style="width:2cm; height:3.5cm;"><img style="border: 2px solid #000;" src="'.$thumbImage.'" /></td>';
    $out .= '<td colspan="2" style="width:7cm;">'.$page->summary.'</td>';

    $out .= '<td class="empty">&nbsp;&nbsp;&nbsp;</td>';

    $out .= '<td colspan="8" style="width:2cm;"><img style="border: 2px solid #000;" src="'.$thumbImage.'" /></td>';
    $out .= '<td colspan="2" style="width:7cm;">'.$page->summary.'</td>';
    $out .= '</tr>';

    $out .= '</table>';
  }

}

echo $out;

?>

