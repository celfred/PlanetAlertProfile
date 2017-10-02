<?php namespace ProcessWire;
if (!$config->ajax) {
  include("./head.inc"); 
}

$allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
$allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
$allPeople = $pages->find("template=people, name!=people, sort=title");

$playerId = $input->urlSegment1;
$player = $pages->get($playerId);

$out = '';

$out .= '<div id="showInfo" data-href="'.$pages->get('name=ajax-content')->url.'"></div>';

$out .= "<h2 class='well text-center'>Marketplace for {$player->title} ({$player->team->title})</h2>";
$out .= "<h3 class='text-center well'>";
$out .= "<img src='{$config->urls->templates}img/gold_mini.png' alt='' />&nbsp;<span id='remainingGC'>{$player->GC}</span> GC available. (<span id='nbChecked'>0</span> checked) <span class='badge badge-warning'>3 items per day limit !</span>";
$out .= "</h3>";

$out .= '<form id="marketPlaceForm" name="marketPlaceForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
$out .= '<input type="hidden" name="player" value="'.$player->id.'" />';
// Possible equipment
if ($player->coma == false) {
  $nbEl = $player->places->count()+$player->people->count();
  $possibleEquipment = $allEquipments->find("GC<=$player->GC, level<=$player->level, freeActs<=$nbEl, id!=$player->equipment, parent.name!=potions, sort=-parent.name, sort=name");
  // Get rid of potions bought within the last 15 days
  $today = new \DateTime("today");
  $interval = new \DateInterval('P15D');
  $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
  $boughtPotions = $player->find("template=event, date>=$limitDate, refPage.name~=potion, refPage.name!=health-potion");
  $possiblePotions = $allEquipments->find("GC<=$player->GC, level<=$player->level, freeActs<=$nbEl, parent.name=potions, sort=name");
  // Get rid of unused potions
  if ($player->usabledItems->count() > 0) {
    foreach ( $player->usabledItems as $u) {
      foreach ($possiblePotions as $p) {
        if ($u->id == $p->id) {
          $p->locked = 'Waiting to be used !';
        }
      }
    }
  }
  foreach ( $boughtPotions as $b) {
    foreach ($possiblePotions as $p) {
      if ($b->refPage->id == $p->id && $p->locked == '') {
        $date1 = new DateTime(date('Y-m-d H:i:s', $today));
        $date2 = new DateTime(date('Y-m-d H:i:s', $b->date));
        $interval = $date1->diff($date2)->format("%a");
        if ($interval == 0) {
          $p->locked = 'Unlocked tomorrow !';
        } else {
          $p->locked = 'Unlocked in '.($interval+1).' days';
        }
      }
    }
  }
  // Get rid of group items if no groups are set
  if ($player->group == '') {
    foreach ($possibleEquipment as $eq) {
      if ($eq->parent->is("name=group-items")) {
        $eq->locked = "No group.";
      }
    }
  }

  // Possible places
  $possiblePlaces = $allPlaces->find("GC<=$player->GC, level<=$player->level, id!=$player->places,sort=name");
  // Possible people
  $possiblePeople = $allPeople->find("GC<=$player->GC, level<=$player->level, id!=$player->people,sort=name");
} else { // Coma state, only Health potion is available
  $possibleEquipment = $allEquipments->find("name=none");
  $possiblePotions = $allEquipments->find("GC<=$player->GC, name=health-potion");
  $possiblePlaces = $allEquipments->find("name=none");
  $possiblePeople = $allEquipments->find("name=none");
}

$out .= '<section class="row">';
if ($player->coma == 1) {
  $out .= "<p class='badge badge-danger'>Your player is in a COMA state. Get the Healing potion as soons as possible !</p>";
}
$out .= "<ul class='itemList col-md-4'>";
if ( $possibleEquipment->count() > 0) {
  $lastCat = '';
  foreach($possibleEquipment as $item) {
    // List items by category
    if ($item->parent->name !== $lastCat) {
      $out .= '<li class="label label-primary">'.$item->parent->title.'</li>';
    }
    if (!$item->locked) {
      $out .= '<li>';
      $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
      if ($item->image) {
        $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="Image" /> ';
      }
      $out .= $item->title.' ['.$item->GC.'GC]';
      $out .= '</label>';
      $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$item->summary.'" ></span>';
      $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
      $out .= '</li>';
    } else {
      $out .= '<li>';
      $out .= '<label class="strikeText"> ';
      if ($item->image) {
        $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="Image" /> ';
      }
      $out .= $item->title;
      $out .= ' <span class="badge badge-danger">'.$item->locked.'</span>';
      $out .= '</label>';
      $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
      $out .= '</li>';
    }
    $lastCat = $item->parent->name;
  }
} else {
  if ($player->coma == false) {
    $out .= "<li><h3>No possible equipment !</h3></li>";
  }
}
// Add potions
if ($possiblePotions->count() > 0) {
  $out .= '<li class="label label-primary">Potions</li>';
  foreach($possiblePotions as $item) {
    if (!$item->locked) {
      $out .= '<li>';
      $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
      if ($item->image) {
        $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="Image" /> ';
      }
      $out .= $item->title.' ['.$item->GC.'GC]';
      $out .= '</label>';
      $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$item->summary.'" ></span>';
      $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
      $out .= '</li>';
    } else {
      $out .= '<li>';
      $out .= '<label class="strikeText"> ';
      if ($item->image) {
        $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="Image" /> ';
      }
      $out .= $item->title;
      $out .= ' <span class="badge badge-danger">'.$item->locked.'</span>';
      $out .= '</label>';
      $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
      $out .= '</li>';
    }
  }
} else {
  $out .= "<li><h3>No possible potion !</h3></li>";
}
$out .= "</ul>";


if ( $possiblePlaces->count() > 0) {
  $out .= "<ul class='itemList col-md-4'>";
  $out .= '<li class="label label-primary">Possible Places</li>';
  foreach($possiblePlaces as $item) {
    $out .= '<li>';
    $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->country->title.'] ['.$item->GC.'GC]</label>';
    $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
    $out .= '</li>';
  }
  $out .= "</ul>";
} else {
  $out .= "<ul class='itemList col-md-4'>";
  if ($player->coma == false) {
    $out .= "<li><h3>No possible place !</h3></li>";
    $out .= "</ul>";
  }
}
if ($player->rank->name == '4emes' || $player->rank->name == '3emes') {
  if ( $possiblePeople->count() > 0) {
    $out .= "<ul class='itemList col-md-4'>";
    $out .= "<li class='label label-primary'>Possible People</li>";
    foreach($possiblePeople as $item) {
      $out .= '<li>';
      $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->GC.'GC]</label>';
    $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
    $out .= '</li>';
      /* if ($item->photo) { */
      /*   $out .= ' <img src="'.$item->photo->eq(0)->getCrop('mini')->url.'" alt="Image" /> '; */
      /* } */
    }
    $out .= "</ul>";
  } else {
    if ($player->coma == false) {
      $out .= "<ul class='itemList col-md-4'>";
      $out .= "<li><h3>No possible people !</h3></li>";
      $out .= "</ul>";
    }
  }
} else {
  $out .= "<ul class='itemList col-md-6'>";
  $out .= "<li><h3>No possible people (4emes and 3emes only)!</h3></li>";
  $out .= "</ul>";
}

$out .= '</section>';

if ( $possibleEquipment->count() > 0 || $possiblePlaces->count() > 0 || $possiblePotions->count() > 0 ) {
  $out .= '<input type="submit" name="marketPlaceSubmit" value="Yes, buy the selected items!" class="btn btn-block btn-primary" disabled="disabled" />';
  $out .= '<a href="'.$pages->get('/')->url.'players/'.$player->team->name.'" class="btn btn-block btn-danger">No, go back to team\'s page.</a>';
}

$out .= '</form>';

echo $out;

if (!$config->ajax) {
  include("./foot.inc"); 
} else { // Have JS functions available
  echo '<script type="text/javascript" src="'.$config->urls->templates.'scripts/main.js"></script>';
}

?>
