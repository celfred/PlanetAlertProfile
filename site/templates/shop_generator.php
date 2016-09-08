<?php
if (!$config->ajax) {
  include("./head.inc"); 
} else {
  include("./my-functions.inc"); 
}

$allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
$allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
$allPeople = $pages->find("template=people, name!=people, sort=title");

$playerId = $input->urlSegment1;
$player = $pages->get($playerId);
$allPlayers = $pages->find("template='player', team=$player->team");

$out = '';

$out .= "<h2 class='well text-center'>Marketplace for {$player->title} ({$player->team->title})</h2>";
$out .= "<h3 class='text-center well'>";
$out .= "<img src='{$config->urls->templates}img/gold_mini.png' alt='' />&nbsp;<span id='remainingGC'>{$player->GC}</span> GC available.";
$out .= "</h3>";

$out .= '<form id="marketPlaceForm" name="marketPlaceForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
$out .= '<input type="hidden" name="player" value="'.$player->id.'" />';
// Possible equipment
$possibleEquipment = $allEquipments->find("GC<=$player->GC, level<=$player->level, id!=$player->equipment, parent.name!=potions, sort=-parent.name, sort=name");
$possiblePotions = $allEquipments->find("GC<=$player->GC, level<=$player->level, parent.name=potions, sort=name");

// Possible places
$possiblePlaces = $allPlaces->find("GC<=$player->GC, level<=$player->level, id!=$player->places,sort=name");
// Possible people
$possiblePeople = $allPeople->find("GC<=$player->GC, level<=$player->level, id!=$player->people,sort=name");

$out .= '<section class="row">';
$out .= "<ul class='itemList col-md-6'>";
if ( $possibleEquipment->count() > 0) {
  foreach($possibleEquipment as $item) {
    // List items by category
    if ($item->parent->name !== $lastCat) {
      $out .= '<li class="label label-primary">'.$item->parent->title.'</li>';
    }
    $out .= '<li>';
    $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
    if ($item->image) {
      $out .= ' <img src="'.$item->image->getThumb('mini').'" alt="Image" /> ';
    }
    $out .= $item->title.' ['.$item->GC.'GC]';
    $out .= '</label>';
    $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$item->summary.'" ></span>';
    $out .= '</li>';
    $lastCat = $item->parent->name;
  }
} else {
  $out .= "<li><h3>No possible equipment !</h3></li>";
}
// Add potions
$out .= '<li class="label label-primary">Potions</li>';
foreach($possiblePotions as $item) {
  $out .= '<li>';
  $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
  if ($item->image) {
    $out .= ' <img src="'.$item->image->getThumb('mini').'" alt="Image" /> ';
  }
  $out .= $item->title.' ['.$item->GC.'GC]';
  $out .= '</label>';
  $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$item->summary.'" ></span>';
  $out .= '</li>';
}
$out .= "</ul>";


if ( $possiblePlaces->count() > 0) {
  $out .= "<ul class='itemList col-md-6'>";
  $out .= '<li class="label label-primary">Possible Places</li>';
  foreach($possiblePlaces as $item) {
    $out .= '<li>';
    $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->country->title.'] ['.$item->GC.'GC]</label></li>';
  }
  $out .= "</ul>";
} else {
  $out .= "<ul class='itemList col-md-6'>";
  $out .= "<li><h3>No possible place !</h3></li>";
  $out .= "</ul>";
}
if ($player->rank->name == '4emes' || $player->rank->name == '3emes') {
  if ( $possiblePeople->count() > 0) {
    $out .= "<ul class='itemList col-md-6'>";
    $out .= "<li class='label label-primary'>Possible People</li>";
    foreach($possiblePeople as $item) {
      $out .= '<li>';
      $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->GC.'GC]</label></li>';
      /* if ($item->photo) { */
      /*   $out .= ' <img src="'.$item->photo->eq(0)->getThumb('mini').'" alt="Image" /> '; */
      /* } */
    }
    $out .= "</ul>";
  } else {
      $out .= "<ul class='itemList col-md-6'>";
      $out .= "<li><h3>No possible people !</h3></li>";
      $out .= "</ul>";
  }
} else {
  $out .= "<ul class='itemList col-md-6'>";
  $out .= "<li><h3>No possible people (4emes and 3emes only)!</h3></li>";
  $out .= "</ul>";
}

$out .= '</section>';

if ( $possibleEquipment->count() > 0 || $possiblePlaces->count() > 0 || $possiblePotions->count() > 0 ) {
  $out .= '<input type="submit" name="marketPlaceSubmit" value="Yes, buy the selected items!" class="btn btn-block btn-primary" disabled="disabled" />';
  $out .= '<a href="'.$homepage->url.'players/'.$player->team->name.'" class="btn btn-block btn-danger">No, go back to team\'s page.</a>';
}

$out .= '</form>';

echo $out;

if (!$config->ajax) {
  include("./foot.inc"); 
}
?>
