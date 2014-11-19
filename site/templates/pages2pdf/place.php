<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');

if ($input->urlSegment1  == 'all') { // All places catalog
  $places = $pages->find("template='place',sort='level'")->not("name=places");

  echo '<img style="float: left;" src="'.$logo.'" />';
  echo '<img style="float: right;" src="'.$logo.'" />';
  echo '<h1 style="text-align: center; text-decoration : underline;">The Map : '.$places->count .' Places</h1>';
  echo  '<table class="table table-condensed table-hover">';
    echo '<tr>';
    echo '<th>';
    echo 'Level';
    echo '</th>';
    echo '<th>';
    echo 'Gold coins (GC)';
    echo '</th>';
    echo '<th>';
    echo 'Place';
    echo '</th>';
    echo '<th>';
    echo 'Country';
    echo '</th>';
    echo '<th>';
    echo 'City';
    echo '</th>';
    echo '<th>';
    echo 'Summary';
    echo '</th>';
    echo '<th>';
    echo '# of owners';
    echo '</th>';
    echo '<th>';
    echo 'Photo';
    echo '</th>';
    echo '<tr>';
  foreach ($places as $place) {
    echo '<tr>';
    echo '<td>';
    echo $place->level;
    echo '</td>';
    echo '<td>';
    echo $place->GC;
    echo '</td>';
    echo '<td>';
    echo $place->title;
    echo '</td>';
    echo '<td>';
    echo $place->country->title;
    echo '</td>';
    echo '<td>';
    echo $place->city->title;
    echo '</td>';
    echo '<td>';
    echo $place->summary;
    echo '</td>';
    echo '<td>';
    echo $place->maxOwners;
    echo '</td>';
    echo '<td>';
    $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
    echo '<img style="border: 2px solid #000;" src="'.$thumbImage.'" />';
    //echo $thumbImage;
    echo '</td>';
    echo '</tr>';
  }

  echo '</table>';
} else { // 1 place PDF
  $place = $pages->get("template='place', name='$page->name'");
  $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');

  $out = '';

  for ($i=0; $i<5; $i++) {
    $out .= '<table class="miniTable">';
    $out .= '<tr>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<th colspan="2">'.$page->title.'</th>';

    $out .= '<td class="empty">&nbsp;&nbsp;&nbsp;</td>';

    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<th colspan="2">'.$page->title.'</th>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<th>'.$page->city->title.'</th>';
    $out .= '<th>'.$page->country->title.'</th>';

    $out .= '<td class="empty">&nbsp;&nbsp;&nbsp;</td>';

    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<td>&nbsp;</td>';
    $out .= '<th>'.$page->city->title.'</th>';
    $out .= '<th>'.$page->country->title.'</th>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td colspan="6"><img style="border: 2px solid #000;" src="'.$thumbImage.'" /></td>';
    $out .= '<td colspan="2">'.$page->summary.'</td>';

    $out .= '<td class="empty">&nbsp;&nbsp;&nbsp;</td>';

    $out .= '<td colspan="6"><img style="border: 2px solid #000;" src="'.$thumbImage.'" /></td>';
    $out .= '<td colspan="2">'.$page->summary.'</td>';
    $out .= '</tr>';

    $out .= '</table>';
  }

  echo $out;
}


?>

