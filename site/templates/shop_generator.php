<?php namespace ProcessWire;
if (!$config->ajax) {
  include("./head.inc");
}

/* $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'"); */
/* $allPlaces = $pages->get("/places/")->find("template='place', sort='title'"); */
/* $allPeople = $pages->find("template=people, name!=people, sort=title"); */

$out = '<div id="showInfo" data-href="'.$pages->get('name=ajax-content')->url.'"></div>';

$playerId = $input->urlSegment1;
$player = $pages->get($playerId);

// Find player's possible items
$allItems = possibleElements($player);
$items = $allItems['unlocked'];
$lockedItems = $allItems['locked'];
$pPlaces = $items->find("template=place");
$pPeople = $items->find("template=people");
$pEquipment = $items->find("template=equipment");
$pPotions = $items->find("template=item");


if (!$user->isSuperuser()) {
  if ($user->isLoggedin() && $user->name==$player->login) { // Check if correct player is logged in
    $out .= '<div class="row text-center">';
    $out .= "<h2>Marketplace for {$player->title} ({$player->team->title})</h2>";
    $out .= miniProfile($player, 'equipment');
    $out .= '</div>';

    if ($player->coma == 0) {
      // Available Places
      $out .= '<p class="label label-primary">Available Places</p>';
      if ($pPlaces->count() > 0) {
        $out .= '<ul class="list-unstyled list-inline">';
        foreach ($pPlaces as $item) {
          if ($item->photo) { $mini = $item->photo->eq(0)->getCrop("thumbnail"); }
          $out .= '<li><a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a></li>';
        }
        $out .= '</ul>';
      } else {
        $out .= '<p>Nothing available.</p>';
      }
      
      // Available People
      if ($player->rank->is("name=4emes|3emes") || ($player->team->is("name!=no-team") && $player->team->is("rank.name=4emes|3emes"))) {
        $out .= '<p class="label label-primary">Available People</p>';
        if ($pPeople->count() > 0) {
          $out .= '<ul class="list-unstyled list-inline">';
          foreach ($pPeople as $item) {
            if ($item->photo) { $mini = $item->photo->eq(0)->getCrop("thumbnail"); }
            $out .= '<li><a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>Nothing available.</p>';
        }
      }

      // Available Equipment
      $out .= '<p class="label label-primary">Available Equipment</p>';
      if ($pEquipment->count() > 0) {
        $out .= '<ul class="list-unstyled list-inline">';
        foreach ($pEquipment as $item) {
          if ($item->image) { $mini = $item->image->getCrop("thumbnail"); }
          $out .= '<li><a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a></li>';
        }
        $out .= '</ul>';
      } else {
        $out .= '<p>Nothing available.</p>';
      }
     
      // Available Potions
      $out .= '<p class="label label-primary">Available Potions</p>';
      if ($pPotions->count() > 0) {
        $out .= '<ul class="list-unstyled list-inline">';
        foreach ($pPotions as $item) {
          if ($item->image) { $mini = $item->image->getCrop("small"); }
          $out .= '<li>';
          $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a>';
          $out .='</li>';
        }
        $out .= '</ul>';
      } else {
        $out .= '<p>Nothing available.</p>';
      }
      if ($lockedItems->count() > 0) {
        $out .= '<p class="label label-warning">Locked potions (bought within 15 days)</p>';
        $out .= '<ul class="list-unstyled list-inline">';
        $today = new \DateTime("today");
        foreach ($lockedItems as $l) {
          if ($l->image) { $mini = $l->image->getCrop("small"); }
          $out .= '<li data-toggle="tooltip" data-html="true" title="'.$l->title.'<br />Unlocked in '.$l->locked.' days">';
          $out .= '<img class="thumbnail" src="'.$mini->url.'" /></a>';
          $out .= '</li>';
        }
        $out .= '</ul>';
      }
    } else {
      $out .= "<p class='badge badge-danger'>Your player is in a COMA state. Get the Healing potion as soon as possible !</p>";
      $healingPotion = $pages->get("name=health-potion");
      if ($player->GC >= $healingPotion->GC) {
        $out .= '<ul class="list-unstyled list-inline">';
        $out .= '<li>';
        $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$healingPotion->id.'"><img class="thumbnail" src="'.$healingPotion->image->getCrop("small")->url.'" data-toggle="tooltip" data-html="true" title="'.$healingPotion->title.'" /></a>';
        $out .='</li>';
        $out .= '</ul>';
      }
    }

    $out .= '</div>';
  } else {
    $out .= '<p class="alert alert-warning">You need to log in to access this page. Contact the administrator if you think this is an error.</p> ';
  }
} else { // Admin's marketPlace
  $out .= '<div class="row well">';
  $out .= "<h2 class='text-center'>Marketplace for {$player->title} ({$player->team->title})</h2>";
  $out .= "<h3 class='text-center well'>";
  $out .= "<img src='{$config->urls->templates}img/gold_mini.png' alt='' />&nbsp;<span id='remainingGC'>{$player->GC}</span> GC available. (<span id='nbChecked'>0</span> checked) <span class='badge badge-warning'>3 items per day limit !</span>";
  $out .= "</h3>";
  
  // Possible equipment
  if ($player->coma == 0) {
    $out .= '<section>';
    $out .= '<form id="marketPlaceForm" name="marketPlaceForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
    $out .= '<input type="hidden" name="player" value="'.$player->id.'" />';

    $out .= "<ul class='itemList col-md-4'>";
    if ( $pEquipment->count() > 0) {
      $lastCat = '';
      foreach($pEquipment as $item) {
      // List items by category
      if ($item->parent->name !== $lastCat) {
        $out .= '<li class="label label-primary">'.$item->parent->title.'</li>';
      }
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
      /* if (!$item->locked) { */
      /* } else { */
        /* $out .= '<li>'; */
        /* $out .= '<label class="strikeText"> '; */
        /* if ($item->image) { */
        /*   $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="Image" /> '; */
        /* } */
        /* $out .= $item->title; */
        /* $out .= ' <span class="badge badge-danger">'.$item->locked.'</span>'; */
        /* $out .= '</label>'; */
        /* $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>'; */
        /* $out .= '</li>'; */
      /* } */
      $lastCat = $item->parent->name;
    }
  } else {
    $out .= "<li><h3>No possible equipment !</h3></li>";
  }
  // Add potions
  if ($pPotions->count() > 0) {
    $out .= '<li class="label label-primary">Potions</li>';
    foreach($pPotions as $item) {
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
    }
  } else {
    $out .= "<li><h3>No possible potion !</h3></li>";
  }
  $out .= "</ul>";

  if ( $pPlaces->count() > 0) {
    $out .= "<ul class='itemList col-md-4'>";
    $out .= '<li class="label label-primary">Possible Places</li>';
    foreach($pPlaces as $item) {
      $out .= '<li>';
      $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->country->title.'] ['.$item->GC.'GC]</label>';
      $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
      $out .= '</li>';
    }
    $out .= "</ul>";
  } else {
    $out .= "<ul class='itemList col-md-4'>";
    $out .= "<li><h3>No possible place !</h3></li>";
    $out .= "</ul>";
  }
  if ($player->rank->name == '4emes' || $player->rank->name == '3emes') {
    if ( $pPeople->count() > 0) {
      $out .= "<ul class='itemList col-md-4'>";
      $out .= "<li class='label label-primary'>Possible People</li>";
      foreach($pPeople as $item) {
        $out .= '<li>';
        $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->GC.'GC]</label>';
        $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="Click for info" ></span></a>';
        $out .= '</li>';
      }
      $out .= "</ul>";
    } else {
      $out .= "<ul class='itemList col-md-4'>";
      $out .= "<li><h3>No possible people !</h3></li>";
      $out .= "</ul>";
    }
  } else {
    $out .= "<ul class='itemList col-md-6'>";
    $out .= "<li><h3>No possible people (4emes and 3emes only)!</h3></li>";
    $out .= "</ul>";
  }
  if ( $pEquipment->count() > 0 || $pPlaces->count() > 0 || $pPotions->count() > 0 ) {
    $out .= '<input type="submit" name="marketPlaceSubmit" value="Buy the selected items" class="btn btn-block btn-primary" disabled="disabled" />';
    $out .= '<a href="'.$pages->get('/')->url.'players/'.$player->team->name.'" class="btn btn-block btn-danger">Go back to team page</a>';
  }
  $out .= '</form>';
  $out .= '</section>';

  } else { // Coma state, only Health potion is available
      $out .= "<p class='badge badge-danger'>The player is in a COMA state. Get the Healing potion as soon as possible !</p>";
      $healingPotion = $pages->get("name=health-potion");
      if ($player->GC >= $healingPotion->GC) {
        $out .= '<ul class="list-unstyled list-inline">';
        $out .= '<li>';
        $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$healingPotion->id.'"><img class="thumbnail" src="'.$healingPotion->image->getCrop("small")->url.'" data-toggle="tooltip" data-html="true" title="'.$healingPotion->title.'" /></a>';
        $out .='</li>';
        $out .= '</ul>';
      }
  }
}

echo $out;

if (!$config->ajax) {
  include("./foot.inc"); 
} else { // Have JS functions available
  echo '<script type="text/javascript" src="'.$config->urls->templates.'scripts/main.js"></script>';
}

?>
