<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');

$places = $pages->find("template='place',sort='level'")->not("name=places");
$players = $pages->find("template='player', team=$input->urlSegment1");

foreach($places as $place) {
  $owned = $players->find("places='$place->id'");
  if ($owned->count == $place->maxOwners) {
    $isOwned[] = $place;
  } else if ($place->maxOwners-$owned->count == 1) {
    $oneLeft[] = $place;
  } else if ($place->maxOwners-$owned->count == 2) {
    $twoLeft[] = $place;
  }

}
$nbPlaces = $places->count;
$nbFree = count($isOwned);

$freedomRate = round(($nbFree*100)/$nbPlaces);

$out = '';

$out .= $owned;
$out .= '<img style="float: left;" src="'.$logo.'" />';
$out .= '<img style="float: right;" src="'.$logo.'" />';
$out .= '<h1 style="text-align: center; text-decoration : underline;">Free Places (Team:'.strtoupper($input->urlSegment1).')</h1>';
$out .= '<h1 style="text-align: center;">'.$freedomRate.'% of the world is free!</h1>';

$out .= '<table class="">';

foreach($isOwned as $place) {
  $out .= '<tr class="negative">';
  $out .= '<td>FREE!</td>';
  $out .= '<td><h1>'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</h1></td>';
  $out .= '</tr>';
}
foreach($oneLeft as $place) {
  $out .= '<tr>';
  $out .= '<td>1 needed!</td>';
  $out .= '<td><h2>'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</h2></td>';
  $out .= '</tr>';
}
foreach($twoLeft as $place) {
  $out .= '<tr>';
  $out .= '<td>2 needed!</td>';
  $out .= '<td><h3>'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</h3></td>';
  $out .= '</tr>';
}

$out .= '</table>';

echo $out;


?>

