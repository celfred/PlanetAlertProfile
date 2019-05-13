<?php namespace ProcessWire;
if (!$config->ajax) {
  include("./head.inc");
}

if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) { // IE detected
  $out = $wrongBrowserMessage;
} else {
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
  $pPotions = $items->find("template=item, category.name=potions");
  $pItems = $items->find("template=item, category.name=group-items");

  if (!$user->hasRole('teacher') && !$user->isSuperuser()) {
    if ($user->hasRole('player') && $user->name==$player->login) { // Check if correct player is logged in
      //Limit to 3 items a day
      $today = new \DateTime("today");
      $limitDate = strtotime($today->format('Y-m-d'));
      $todayItemsCount = $player->get("name=history")->find("date>=$limitDate, task.name=buy|free")->count();

      $out .= '<div class="row text-center">';
        $out .= "<h2>{$page->title}</h2>";
        $out .= '<p class="reloadRequired alert alert-warning hidden">'.__("Values will be updated after reloading.").'</p>';
        $out .= miniProfile($player, 'equipment');
        if ($todayItemsCount < 3 ) {
          $out .= '<p class="alert alert-warning">'.__("Items bought today").' : <span id="todayItemsCount">'.$todayItemsCount.'</span> ';
          $out .= __("(limited to 3 !)").'</p>';
        }
      $out .= '</div>';

      $out .= "<p class='text-center alert alert-warning hidden'>You have reached the 3 items limit for today ! Come back tomorrow !</p>";

      if ($player->coma == 0 && $todayItemsCount < 3) {
        // Available Places
        $out .= '<p class="label label-primary">'.__("Available Places").'</p>';
        if ($pPlaces->count() > 0) {
          $out .= '<ul class="list-unstyled list-inline">';
          foreach ($pPlaces as $item) {
            if ($item->photo) { $mini = $item->photo->eq(0)->getCrop("thumbnail"); }
            $out .= '<li class="possibleItems" data-gc="'.$item->GC.'">';
            $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p class="possibleItems">'.__("Nothing available.").'</p>';
        }
        
        // Available People
        if (($player->rank && $player->rank->is("index>=8")) || ($player->team->is("name!=no-team") && $player->team->is("rank.index>=8"))) {
          $out .= '<p class="label label-primary">'.__("Available People").'</p>';
          if ($pPeople->count() > 0) {
            $out .= '<ul class="list-unstyled list-inline">';
            foreach ($pPeople as $item) {
              if ($item->photo) { $mini = $item->photo->eq(0)->getCrop("thumbnail"); }
              $out .= '<li class="possibleItems" data-gc="'.$item->GC.'">';
              $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a>';
              $out .= '</li>';
            }
            $out .= '</ul>';
          } else {
            $out .= '<p class="possibleItems">'.__("Nothing available.").'</p>';
          }
        }

        // Available Equipment
        $out .= '<p class="label label-primary">'.__("Available Equipment").'</p>';
        if ($pEquipment->count() > 0) {
          $out .= '<ul class="list-unstyled list-inline">';
          foreach ($pEquipment as $item) {
            if ($item->image) { $mini = $item->image->getCrop("thumbnail"); }
            $out .= '<li class="possibleItems" data-gc="'.$item->GC.'"><a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p class="possibleItems">'.__("Nothing available.").'</p>';
        }
       
        // Available Group items
        $out .= '<p class="label label-primary">'.__("Available group items").'</p>';
        if ($pItems->count() > 0) {
          $out .= '<ul class="list-unstyled list-inline">';
          foreach ($pItems as $item) {
            if ($item->image) { $mini = $item->image->getCrop("thumbnail"); }
            $out .= '<li class="possibleItems" data-gc="'.$item->GC.'">';
            $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a>';
            $out .='</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p class="possibleItems">'.__("Nothing available.").'</p>';
        }

        // Available Potions
        $out .= '<p class="label label-primary">'.__("Available Potions").'</p>';
        if ($pPotions->count() > 0) {
          $out .= '<ul class="list-unstyled list-inline">';
          foreach ($pPotions as $item) {
            if ($item->image) { $mini = $item->image->getCrop("small"); }
            $out .= '<li class="possibleItems" data-gc="'.$item->GC.'">';
            $out .= '<a href="#" class="showInfo buy" data-href="'.$pages->get("name=submitforms")->url.'" data-playerId="'.$player->id.'" data-id="'.$item->id.'"><img class="thumbnail" src="'.$mini->url.'" data-toggle="tooltip" data-html="true" title="'.$item->title.'" /></a>';
            $out .='</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p class="possibleItems">'.__("Nothing available.").'</p>';
        }
        if ($lockedItems->count() > 0 && $player->team->name != 'no-team') {
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
        if ($todayItemsCount >= 3) {
          $out .= "<p class='text-center alert alert-warning'>".__("You have reached the 3 items limit for today ! Come back tomorrow !")."</p>";
        } else {
          $out .= "<p class='badge badge-danger'>".__("Your player is in a COMA state. Get the Healing potion as soon as possible !")."</p>";
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

      $out .= '</div>';
    } else {
      $out .= '<p class="alert alert-warning">'.__("You need to log in to access this page. Contact the administrator if you think this is an error.").'</p> ';
    }
  } else { // Teacher / Admin's marketPlace
    $out .= '<div class="row well">';
    $out .= "<h2 class='text-center'>{$page->title} ({$player->title} [{$player->team->title}])</h2>";
    $out .= "<h3 class='text-center well'>";
    $out .= "<img src='{$config->urls->templates}img/gold_mini.png' alt='gold coins.' />&nbsp;<span id='remainingGC'>{$player->GC}</span> ".__("GC available.");
    $out .= " (<span id='nbChecked'>0</span> ".__("checked").") ";
    $out .= "<span class='badge badge-warning'>".__("3 items per day limit !")."</span>";
    $out .= "</h3>";
    
    // Possible equipment
    if ($player->coma == 0) {
      $out .= '<section>';
      $out .= '<form id="marketPlaceForm" name="marketPlaceForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
      $out .= '<input type="hidden" name="player" value="'.$player->id.'" />';

      $out .= "<ul class='itemList col-md-4'>";
      if ($pEquipment->count() > 0) {
        $lastCat = '';
        foreach($pEquipment as $item) {
        // List items by category
        if ($item->parent->name !== $lastCat) {
          $out .= '<li class="label label-primary">'.$item->parent->title.'</li>';
        }
        $out .= '<li>';
        $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
        if ($item->image) {
          $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="'.$item->title.'." /> ';
        }
        $out .= $item->title.' ['.$item->GC.__('GC').']';
        $out .= '</label>';
        $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="'.__("Click for info").'" ></span></a>';
        $out .= '</li>';
        $lastCat = $item->parent->name;
      }
    } else {
      $out .= "<li><h3>".__('Nothing available.')."</h3></li>";
    }
    // Add group items
    if (!isset($player->group->id)) {
      $warning = ' <span class="label label-warning">'.__("No groups are set. This item will be individual !").'</span>';
    } else {
      $warning = '';
    }
    if ($pItems->count() > 0) {
      $out .= '<li class="label label-primary">'.__("Group items").'</li>';
      foreach($pItems as $item) {
          $out .= '<li>';
          $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
          if ($item->image) {
            $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="'.$item->title.'." /> ';
          }
          $out .= $item->title.' ['.$item->GC.__('GC').']';
          $out .= '</label>';
          $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="'.__("Click for info").'" ></span></a>';
          $out .= $warning;
          $out .= '</li>';
      }
    } else {
      $out .= "<li><h3>".__('Nothing available.')."</h3></li>";
    }
    // Add potions
    if ($pPotions->count() > 0) {
      $out .= '<li class="label label-primary">Potions</li>';
      foreach($pPotions as $item) {
          $out .= '<li>';
          $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> ';
          if ($item->image) {
            $out .= ' <img src="'.$item->image->getCrop('mini')->url.'" alt="'.$item->title.'." /> ';
          }
          $out .= $item->title.' ['.$item->GC.__('GC').']';
          $out .= '</label>';
          $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="'.__("Click for info").'" ></span></a>';
          $out .= '</li>';
      }
    } else {
      $out .= "<li><h3>".__('Nothing available.')."</h3></li>";
    }
    $out .= "</ul>";

    if ($pPlaces->count() > 0) {
      $out .= "<ul class='itemList col-md-4'>";
      $out .= '<li class="label label-primary">'.__("Possible Places").'</li>';
      foreach($pPlaces as $item) {
        $out .= '<li>';
        $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->country->title.'] ['.$item->GC.__('GC').']</label>';
        $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="'.("Click for info").'" ></span></a>';
        $item = setElement($item, $player->team);
        $out .= ' <span class="'.$item->cssClass.'">['.$item->owners.'/'.$item->teamRate.']</span>';
        $out .= '</li>';
      }
      $out .= "</ul>";
    } else {
      $out .= "<ul class='itemList col-md-4'>";
      $out .= "<li><h3>".__('Nothing available.')."</h3></li>";
      $out .= "</ul>";
    }
    if (($player->rank && $player->rank->is("index>=8")) || ($player->team && $player->team->rank->is("index>=8"))) {
      if ($pPeople->count() > 0) {
        $out .= "<ul class='itemList col-md-4'>";
        $out .= "<li class='label label-primary'>".__('Possible People')."</li>";
        foreach($pPeople as $item) {
          $out .= '<li>';
          $out .= '<label for="item['.$item->id.']"><input type="checkbox" id="item['.$item->id.']" name="item['.$item->id.']" ondblclick="return false;" onclick="shopCheck(this, $(\'#remainingGC\').text(),'.$item->GC.')" data-gc="'.$item->GC.'" /> '.$item->title.' ['.$item->GC.__('GC').']</label>';
          $out .= ' <a href="#" class="showInfo" data-href="" data-id="'.$item->id.'"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-html="true" title="'.__("Click for info").'" ></span></a>';
          $item = setElement($item, $player->team);
          $out .= ' <span class="'.$item->cssClass.'">['.$item->owners.'/'.$item->teamRate.']</span>';
          $out .= '</li>';
        }
        $out .= "</ul>";
      } else {
        $out .= "<ul class='itemList col-md-4'>";
        $out .= "<li><h3>".__("Nothing available.")."</h3></li>";
        $out .= "</ul>";
      }
    } else {
      $out .= "<ul class='itemList col-md-6'>";
      $out .= "<li><h3>".__("No possible people (4emes and 3emes only)!")."</h3></li>";
      $out .= "</ul>";
    }
    if ($pEquipment->count() > 0 || $pPlaces->count() > 0 || $pPotions->count() > 0) {
      $out .= '<input type="submit" name="marketPlaceSubmit" value="'.__("Buy the selected items").'" class="btn btn-block btn-primary" disabled="disabled" />';
      $out .= '<a href="'.$pages->get('/')->url.'players/'.$player->team->name.'" class="btn btn-block btn-danger">'.__("Go back to team page").'</a>';
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
}

echo $out;

if (!$config->ajax) {
  include("./foot.inc"); 
}

?>
