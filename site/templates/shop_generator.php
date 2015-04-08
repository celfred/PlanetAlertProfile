<?php
if (!$config->ajax) {
  include("./head.inc"); 
} else {
  include("./my-functions.inc"); 
}

$allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
$allPlaces = $pages->get("/places/")->find("template='place', sort='title'");

$playerId = $input->urlSegment1;
$player = $pages->get($playerId);
$allPlayers = $pages->find("template='player', team=$player->team");

$out = '';

$out .= '<form id="marketPlaceForm" name="marketPlaceForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
$out .= "<h2 class='text-center'>Marketplace for {$player->title} ({$player->team->title})</h2>";
$out .= "<h3 class='text-center well'><img src='{$config->urls->templates}img/gold_mini.png' alt='' />&nbsp;<span id='remainingGC'>{$player->GC}</span> GC available.</h3>";
$out .= '<input type="hidden" name="player" value="'.$player->id.'" />';
$out .= '<input type="hidden" name="team" value="'.$player->team.'" />';

// Possible equipment
$possibleEquipment = $allEquipments->find("GC<=$player->GC, level<=$player->level, id!=$player->equipment");

$possiblePlaces = $allPlaces->find("GC<=$player->GC, level<=$player->level, id!=$player->places");
// Delete completed places
foreach($possiblePlaces as $place) {
  if (placeFreedomRate($place, $allPlayers) === 100) {
    $possiblePlaces->remove($place);
  }
}

/*
if ( $possibleEquipment.count() > 0 || $possiblePlaces.count() >0) {
  $out .= '<input type="submit" name="marketPlaceSubmit" value="Yes, buy the selected items!" class="btn btn-block btn-primary" disabled="disabled" />';
}
 */

$out .= '<section class="row">';
if ( $possibleEquipment.count() > 0) {
  $out .= "<ul class='itemList col-md-6'><h3>Possible equipment</h3>";
  foreach($possibleEquipment as $item) {
    $out .= '<li>';
    $out .= '<label title="'.$item->summary.'" for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->GC.'GC]</label></li>';
  }
  $out .= "</ul>";
} else {
  $out .= "<ul class='itemList col-md-6'>";
  $out .= "<li><h3>No equipment to buy!</h3></li>";
  $out .= "</ul>";
}


if ( $possiblePlaces.count() > 0) {
  $out .= "<ul class='itemList col-md-6'><h3>Possible places</h3>";
  foreach($possiblePlaces as $item) {
    $out .= '<li>';
    $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->country->title.'] ['.$item->GC.'GC]</label></li>';
  }
  $out .= "</ul>";
} else {
  $out .= "<ul class='itemList col-md-6'>";
  $out .= "<li><h3>No place to free!</h3></li>";
  $out .= "</ul>";
}
$out .= '</section>';

if ( $possibleEquipment.count() > 0 || $possiblePlaces.count() >0) {
  $out .= '<input type="submit" name="marketPlaceSubmit" value="Yes, buy the selected items!" class="btn btn-block btn-primary" disabled="disabled" />';
  $out .= '<a href="'.$homepage->url.'players/'.$player->team->name.'" class="btn btn-block btn-danger">No, go back to team\'s page.</a>';
}

$out .= '</form>';

echo $out;

if (!$config->ajax) {
  include("./foot.inc"); 
}
?>
