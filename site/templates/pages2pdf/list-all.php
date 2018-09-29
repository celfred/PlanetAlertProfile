<?php 
// NO more in use?

$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');

$places = $pages->find("template='place',sort='level'")->not("name=places");
$players = $pages->find("template='player', team=$input->urlSegment1");
$currentLevel = 0;
$totalOwned = 0;
$out = '';

// Find free or almost free places
foreach($places as $place) {
  $owned = $players->find("places='$place->id'");
  if ($owned->count == $place->maxOwners) {
    $isOwned[$place->level][] = $place;
    $totalOwned++;
  } else if ($place->maxOwners-$owned->count == 1) {
    $oneLeft[] = $place;
  } else if ($place->maxOwners-$owned->count == 2) {
    $twoLeft[] = $place;
  } else { // Available places
    $placesLeft[] = $place;
  }
}

// Check level complete
for ($i=1; $i<11; $i++) {
  $nbPlaces = $pages->find("template='place',level=$i")->count();
  if (count($isOwned[$i]) == $nbPlaces) {
    $completedLevels[] = $i;
  }
}

// Team Score
$nbPlaces = $places->count;
$freedomRate = round(($totalOwned*100)/$nbPlaces);

$out .= '<table class="big">';
$out .= '<tr>';
$out .= '<td>';
$out .= '<img style="" src="'.$logo->url.'" />';
$out .= '</td>';
$out .= '<td colspan="3">';
$out .= '<h1 style="text-align: center; text-decoration : underline;">'.__("Free Places").' (';
$out .= __("Team").':'.strtoupper($input->urlSegment1).')</h1>';
//$out .= '<h1 style="text-align: center;">'.$freedomRate.'% of the world is free!</h1>';
$out .= '</td>';
$out .= '</tr>';

$out .= '<tr>';
$out .= '<td>'.__("# of owners").'</td>';
$out .= '<td>'.__("Level").'</td>';
$out .= '<td>'.__("GC").'</td>';
$out .= '<td>'.__("Place").'</td>';
$out .= '</tr>';

// Display free places (by level)
for ($i=1; $i<11; $i++) {
  if (in_array($i, $completedLevels) == false) {
    foreach($isOwned[$i] as $place) { // Level not completed, display free places
      $out .= '<tr class="negative">';
      $out .= '<td>'.__("FREE!").'</td>';
      $out .= '<td>'.$place->level.'</td>';
      $out .= '<td>'.$place->GC.'</td>';
      $out .= '<td><h2>'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</h2></td>';
      $out .= '</tr>';
    } 
  } else { // Level completed
    $out .= '<tr class="complete">';
    $out .= '<td colspan="4"><h1 style="font-variant: small-caps;">'.sprintf(__("Level %d complete !"), $i).'</h1></td>';
    $out .= '</tr>';
  }
}

// Display available places
foreach($oneLeft as $place) {
  $nbOwners = $players->find("places='$place->id'")->count();
  $out .= '<tr>';
  //$out .= '<td>1 needed!</td>';
  $out .= '<td>'.$nbOwners.'/'.$place->maxOwners.'</td>';
  $out .= '<td>'.$place->level.'</td>';
  $out .= '<td>'.$place->GC.'</td>';
  $out .= '<td class="important">'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</td>';
  $out .= '</tr>';
}
foreach($twoLeft as $place) {
  $nbOwners = $players->find("places='$place->id'")->count();
  $out .= '<tr>';
  //$out .= '<td>2 needed!</td>';
  $out .= '<td>'.$nbOwners.'/'.$place->maxOwners.'</td>';
  $out .= '<td>'.$place->level.'</td>';
  $out .= '<td>'.$place->GC.'</td>';
  $out .= '<td class="important">'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</td>';
  $out .= '</tr>';
}
foreach($placesLeft as $place) {
  $nbOwners = $players->find("places='$place->id'")->count();
  if (in_array($place, $isOwned[$place->level]) == false) {
  $out .= '<tr class="">';
  $out .= '<td>'.$nbOwners.'/'.$place->maxOwners.'</td>';
  $out .= '<td>'.$place->level.'</td>';
  $out .= '<td>'.$place->GC.'</td>';
  $out .= '<td class="important">'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</td>';
  $out .= '</tr>';
  }
}

$out .= '</table>';

echo $out;

?>
