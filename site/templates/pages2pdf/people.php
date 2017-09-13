<?php 

$out = '';

$id = $input->get->id;
$people = $pages->get("template=people, id=$id");
$thumbImage = $people->photo->eq(0)->getCrop('thumbnail');
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
  $out .= '<th colspan="2" style="width:4.5cm; height:0.7cm;">'.$people->title.'</th>';

  $out .= '<td class="empty" style="width:0.5cm">&nbsp;</td>';

  $out .= '<td colspan="2" rowspan="2" style="width: 0.6cm; border: 2px solid #000;">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<td style="width:0.2cm">&nbsp;</td>';
  $out .= '<th colspan="2" style="width:4.5cm;">'.$people->title.'</th>';
  $out .= '</tr>';

  $out .= '<tr>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  if ($page->template == 'people') { $field = $people->nationality; }
  $out .= '<th style="width: 2cm; height:0.7cm;">'.$field.'</th>';
  $out .= '<th style="width: 2cm;">'.$people->country->title.'</th>';

  $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';

  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  $out .= '<td>&nbsp;</td>';
  if ($page->template == 'people') { $field = $people->nationality; }
  $out .= '<th style="width: 2cm; height:0.7cm;">'.$field.'</th>';
  $out .= '<th style="width: 2cm;">'.$people->country->title.'</th>';
  $out .= '</tr>';

  $out .= '<tr>';
  $out .= '<td colspan="8" style="width:2cm; height:3.5cm;"><img style="border: 2px solid #000;" src="'.$thumbImage->url.'" /></td>';
  $textLength = strlen($people->summary);
  $fontSize = '10px;';
  if ($textLength >= 600) { $fontSize = '8px'; }
  if ($textLength >= 500 && $textLength < 600) { $fontSize = '10px'; }
  if ($textLength >= 400 && $textLength < 500) { $fontSize = '11px'; }
  if ($textLength >= 300 && $textLength < 400) { $fontSize = '12px'; }
  if ($textLength >= 200 && $textLength < 300) { $fontSize = '14px'; }
  if ($textLength < 200) { $fontSize = '16px'; }
  $out .= '<td colspan="2" style="width:7cm; font-size:'.$fontSize.';">'.$people->summary.'</td>';

  $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';

  $out .= '<td colspan="8" style="width:2cm; height:3.5cm;"><img style="border: 2px solid #000;" src="'.$thumbImage->url.'" /></td>';
  $textLength = strlen($people->summary);
  $fontSize = '10px;';
  if ($textLength >= 600) { $fontSize = '8px'; }
  if ($textLength >= 500 && $textLength < 600) { $fontSize = '10px'; }
  if ($textLength >= 400 && $textLength < 500) { $fontSize = '11px'; }
  if ($textLength >= 300 && $textLength < 400) { $fontSize = '12px'; }
  if ($textLength >= 200 && $textLength < 300) { $fontSize = '14px'; }
  if ($textLength < 200) { $fontSize = '16px'; }
  $out .= '<td colspan="2" style="width:7cm; font-size:'.$fontSize.';">'.$people->summary.'</td>';
  $out .= '</tr>';

  $out .= '</table>';
}

echo $out;

?>

