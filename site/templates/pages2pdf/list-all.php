<?php 

$logo = $pages->get('/')->photo->eq(0)->getThumb('thumbnail');

if ($input->urlSegment2) { // Player details
  $player = $pages->get("template=player, login=$input->urlSegment2");
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
  $out .= '<h3 style="margin: 0px;">My Places / My People</h3>';
  for ($i=0; $i<$allElements->count(); $i+=2) {
    if (in_array($i, [6, 11, 16, 21, 26])) {
      $out .= '</div>';
      $out .= '</div>';
      $out .= '<pagebreak />';
      $out .= '<div style="margin-top: 10px; text-align: center; background-color: #C366FF; padding: 5px;">';
      $out .= '<div style="padding: 0px; background-color: #FFF; border-radius: 20px 20px 0px 0px;">';
      $out .= '<h3>My Places / My People</h3>';
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
    $out .= '<th colspan="2" style="width: 4.5cm;">'.$e->title.'</th>';

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
    $out .= '<th style="width: 2cm;">'.$field.'</th>';
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
    $out .= '<td colspan="8" style="width:2cm;"><img style="border: 2px solid #000;" src="'.$thumbImage.'" /></td>';
    $out .= '<td colspan="2" style="width:7cm;">'.$e->summary.'</td>';

    if ($nextEl) {
      $out .= '<td class="empty" style="width:0.5cm;">&nbsp;</td>';
      $out .= '<td colspan="8" style="width:2cm;"><img style="border: 2px solid #000;" src="'.$nextThumbImage.'" /></td>';
      $out .= '<td colspan="2" style="width:7cm;">'.$nextEl->summary.'</td>';
      $out .= '</tr>';
    } else {
      $out .= '<td class="empty" style="width:10.5cm">&nbsp;</td>';
    }

    $out .= '</table>';
  }
  $out .= '</div>';
  $out .= '</div>';

} else { // List all places
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
  $out .= '<img style="" src="'.$logo.'" />';
  $out .= '</td>';
  $out .= '<td colspan="3">';
  $out .= '<h1 style="text-align: center; text-decoration : underline;">Free Places (Team:'.strtoupper($input->urlSegment1).')</h1>';
  //$out .= '<h1 style="text-align: center;">'.$freedomRate.'% of the world is free!</h1>';
  $out .= '</td>';
  $out .= '</tr>';

  $out .= '<tr>';
  $out .= '<td># of owners</td>';
  $out .= '<td>Level</td>';
  $out .= '<td>GC</td>';
  $out .= '<td>Place</td>';
  $out .= '</tr>';

  // Display free places (by level)
  for ($i=1; $i<11; $i++) {
    if (in_array($i, $completedLevels) == false) {
      foreach($isOwned[$i] as $place) { // Level not completed, display free places
        $out .= '<tr class="negative">';
        $out .= '<td>FREE!</td>';
        $out .= '<td>'.$place->level.'</td>';
        $out .= '<td>'.$place->GC.'</td>';
        $out .= '<td><h2>'.$place->title.' ('.$place->city->title.', '.$place->country->title.')</h2></td>';
        $out .= '</tr>';
      } 
    } else { // Level completed
      $out .= '<tr class="complete">';
      $out .= '<td colspan="4"><h1 style="font-variant: small-caps;">Level '.$i.' complete!</h1></td>';
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
}

echo $out;


?>

