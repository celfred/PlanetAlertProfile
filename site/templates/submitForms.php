<?php namespace ProcessWire;

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    // Get logged in player
    $player = $pages->get("template=player, login=$user->name");
    $player->of(false);

    if (isset($input->get->form) && $input->get->form == 'manualTask' && $input->get->playerId != '' && $input->get->taskId != '' && $input->get->lessonId != '') { // Book of Knowledge use
      $player = $pages->get($input->get->playerId);
      $task = $pages->get($input->get->taskId);
      $refPage = $pages->get($input->get->lessonId);
      if ($task->is("name=extra-homework|intensive-extra-homework")) {
        $task->comment = "Good Copy work";
        $task->refPage = $refPage;
        $task->linkedId = false;
        // Record in player's history but don't calculate new scores. Wait for admin's validation
        // TODO : Change : Make an admin's work list in backend > record playerId and task info ???
        //  > Use Book of Knowledge page > Add repeater : playerId, lessonId, date
        //  > Loop over this list in Admin's work
        //  > If 'validated' > create extra-training with refPage in Player's history and updateScore
        // TODO : Clone historyPage with updateScore then delete old history ?
        savePendingLesson($player, $task);
        // setEventDate($task);
        //$historyPage = saveHistory($player, $task, 1);
        //addUsable($player, $historyPage, 1);
      }
    }

    if($input->post->buyFormSubmit) { // buyForm submitted
      $itemId = $input->post->item;
      $newItem = $pages->get($itemId);
      // Set task according to newItem's type
      if ($newItem->template == 'equipment' || $newItem->template == 'item') {
        $task = $pages->get("name='buy'");
      }
      if ($newItem->template == 'place' || $newItem->template == 'people') {
        $task = $pages->get("name='free'");
      }
      // Check if item is not already there
      $already = false;
      foreach ($player->equipment as $eq) {
        if ($eq->id == $newItem->id) {
          if($newItem->parent->name !== 'potions') {
            $already = true;
          }
        }
      }
      foreach ($player->places as $pl) {
        if ($pl->id == $newItem->id) {
          $already = true;
        }
      }
      foreach ($player->people as $pl) {
        if ($pl->id == $newItem->id) {
          $already = true;
        }
      }
      // Check if item's GC is not out of reach
      if ($newItem->GC > $player->GC) {
        $already = true;
      }
      $task->comment = $newItem->title;
      $task->refPage = $newItem;
      $task->linkedId = false;
      if ($newItem->GC <= $player->GC && $already != true) { // Final 'security' check
        updateScore($player, $task, true);
        // No need to checkDeath, Buyform can't cause death
        // Notify admin
        $msg = "Player : ". $player->title."[".$player->team->title."]\r\n";
        $msg .= "Item : ". $newItem->title;
      } else {
        // Notify admin
        $msg = "Player : ". $player->title."[".$player->team->title."]\r\n";
        $msg .= "Item : ". $newItem->title."\r\n";
        $msg .= "An error has occurred.";
      }

      if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
        mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");
      }
    }

    if($input->post->marketPlaceSubmit) { // marketPlaceForm submitted
      $checkedItems = $input->post->item; // Array

      foreach($checkedItems as $item=>$state) {
        $newItem = $pages->get($item);
        // Check if item is not already there
        $already = false;
        foreach ($player->equipment as $eq) {
          if ($eq->id == $newItem->id) {
            if($newItem->parent->name !== 'potions') {
              $already = true;
            }
          }
        }
        foreach ($player->places as $pl) {
          if ($pl->id == $newItem->id) {
            $already = true;
          }
        }
        foreach ($player->people as $pl) {
          if ($pl->id == $newItem->id) {
            $already = true;
          }
        }
        // Check if item's GC is not out of reach
        if ($newItem->GC > $player->GC) {
          $already = true;
        }

        if ($already == false) {
          // Get item's data
          if ($newItem->template == 'equipment' || $newItem->template == 'item') {
            $task = $pages->get("name='buy'");
          }
          if ($newItem->template == 'place' || $newItem->template == 'people') {
            $task = $pages->get("name='free'");
          }
          // Update player's scores and save
          $task->comment = $newItem->title;
          $task->refPage = $newItem;
          $task->linkedId = false;
          updateScore($player, $task, true);
          // No need to checkDeath, MarketPlace can't cause death
          // Notify admin
          $msg = "Player : ". $player->title."\r\n";
          $msg .= "Team : ". $player->team->title."\r\n";
          $msg .= "Item : ". $newItem->title;
          /* $msg .= "Item : ". $newItem->title.$error; */
          if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");
          }
        }
      }
    }

    if($input->post->donateFormSubmit) { // donateForm submitted
      $amount = (integer) $input->post->amount;
      $receiverId = $input->post->receiver;
      $receiver = $pages->get($receiverId);
      $receiver->of(false);
      
      // Save donation
      // If valid amount
      if ($player && $receiverId && $amount != 0 && $amount <= $player->GC) {
        // Modify player's page
        $task = $pages->get("template='task', name='donation'");
        $task->comment = $amount. ' GC donated to '.$receiver->title.' ['.$receiver->team->title.']';
        $task->refPage = $receiver;
        $task->linkedId = false;
        updateScore($player, $task, true);
        // No need to checkDeath, Donation can't cause death
        // Notify admin
        $msg = "Player : ". $player->title." [".$player->team->title."]\r\n";
        $msg .= "Donation amount : ". $amount."\r\n";
        $msg .= "Donated to : ". $receiver->title." [".$receiver->team->title."]";
        if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
          mail("planetalert@tuxfamily.org", "donationForm", $msg, "From: planetalert@tuxfamily.org");
        }
      }
    }
    // Set group captains
    if ($player->team->name != 'no-team') {
      setCaptains($player->team, true);
    }

    // Redirect to player's profile (in main.js, because doesn't work due to Ajax ?)
    /* $session->redirect($pages->get('/players')->url.$player->team->name.'/'.$player->name); */
    $url = $pages->get('/players')->url.$player->team->name.'/'.$player->name;
    /* echo json_encode(array("sender"=>"marketPlace", "url"=>$url, "newItem"=>$newItem->id)); */
  }

  if ($user->isSuperuser()) { // Admin front-end
    // Unpublish News from Newsboard
    if (isset($input->get->form) && $input->get->form == 'unpublish' && $input->get->newsId != '') {
      $n = $pages->get($input->get->newsId);
      $n->of(false);
      if ($n->publish == 0) { // Unpublish
        $n->publish = 1;
        $n->save();
      } else { // News will disappear on reload
        $n->publish = 0;
        $n->save();
      }
    }
    // Use item
    if (isset($input->get->form) && $input->get->form == 'unpublish' && $input->get->usedItemHistoryPageId != '') {
      $historyPage = $pages->get($input->get->usedItemHistoryPageId);
      $player = $historyPage->parent("template=player");
      $usedItem = $historyPage->refPage;
      if ($player->usabledItems->has($usedItem)) { // 'Used today' is ticked
        // Remove item from player's usabledItems list
        $player->of(false);
        $player->usabledItems->remove($usedItem);
        $player->save();
      } else { // Used today is unclicked
        // Restore item in player's usabledItems list
        $player->of(false);
        $player->usabledItems->add($usedItem);
        $player->save();
      }
    }
    // Validate Book of Knowledge
    if (isset($input->get->form) && $input->get->form == 'unpublish' && $input->get->usedPending != '') {
      $pending = $pages->get($input->get->usedPending);
      echo $pending->player->title;
      // TODO 
      // > Create player's history page according to task (with date...)
      // > Remove pendingLesson ? Pb : What if unticked ?
      /* $historyPage = $pages->get($input->get->usedItemHistoryPageId); */
      /* $player = $historyPage->parent("template=player"); */
      /* $usedItem = $historyPage->refPage; */
      /* if ($player->usabledItems->has($usedItem)) { // 'Used today' is ticked */
      /*   // Remove item from player's usabledItems list */
      /*   $player->of(false); */
      /*   $player->usabledItems->remove($usedItem); */
      /*   $player->save(); */
      /* } else { // Used today is unclicked */
      /*   // Restore item in player's usabledItems list */
      /*   $player->of(false); */
      /*   $player->usabledItems->add($usedItem); */
      /*   $player->save(); */
      /* } */
    }

    if (isset($input->get->form) && $input->get->form == 'manualTask' && $input->get->playerId != '' && $input->get->taskId != '') { // Personal Initiative in Decisions, for example
      $player = $pages->get($input->get->playerId);
      $task = $pages->get($input->get->taskId);
      if ($task->name == 'personal-initiative') {
        $task->comment = "Well done 'Talk about [...]'";
        $task->refPage = '';
        $task->linkedId = false;
        updateScore($player, $task, true);
        // Set group captains
        setCaptains($player->team);
      }
    }

    if (isset($input->get->form) && $input->get->form == 'deleteForm' && $input->get->eventId != '') { // Delete an event
      $event = $pages->get($input->get->eventId);
      // Limit to absence (no need to recalculate scores)
      if ($event->is("task.name=absent|abs")) {
        $pages->trash($event);
      }
    }

    if (isset($input->get->form) && $input->get->form == 'buyForm' && $input->get->playerId != '') { // Healing potion in Main Office, Discount in Decision...
      $player = $pages->get($input->get->playerId);
      $item = $pages->get($input->get->itemId);
      if ($item->is("template=place|people")) {
        $task = $pages->get("name='free'");
      } else {
        $task = $pages->get("name='buy'");
      }
      $task->comment = $item->title;
      $task->refPage = $item;
      if ($input->get->discount && $input->get->discount != '') {
        $task->linkedId = $input->get->discount;
      } else {
        $task->linkedId = false;
      }
      updateScore($player, $task, true);
      // Set group captains
      setCaptains($player->team);
    }

    if($input->post->adminTableSubmit) { // adminTableForm submitted
      // Consider checked players only
      $checkedPlayers = $input->post->player;
      $checked = array_keys($checkedPlayers);
      $allNegPlayers = new pageArray();
      // Record checked task for each player
      for ($i=0; $i<count($checked); $i++) {
        list($playerId, $taskId) = explode('_', $checked[$i]);
        $comment = 'comment_'.$playerId.'_'.$taskId;

        $player = $pages->get($playerId);
        $task = $pages->get($taskId); 
        $task->comment = trim($input->post->$comment);
        $task->refPage = false;
        $task->linkedId = false;
        if ($task->HP < 0) { // Negative action, keep concerned players to check death later
          $allNegPlayers->add($player); 
        }
        // Update player's scores and save
        updateScore($player, $task, true);
      }
      // Check death for each players having a negative action
      $allNegPlayers = $allNegPlayers->unique();
      foreach($allNegPlayers as $p) {
        checkDeath($p, true);
      }
      // Set group captains
      setCaptains($player->team);

      // Redirect to team page (in main.js, because doesn't work due to Ajax ?)
      /* $session->redirect($pages->get('/players')->url.$player->team->name); */
      $url = $pages->get('/players')->url.$player->team->name;
      echo json_encode(array("sender"=>"adminTable", "saved"=>count($checked), "url"=>$url));
    }

    if($input->post->marketPlaceSubmit) { // marketPlaceForm submitted
      $checkedItems = $input->post->item;
      $playerId = $input->post->player;

      foreach($checkedItems as $item=>$state) {
        // Modify player's page
        $player = $pages->get($playerId);
        // Get item's data
        $refPage = $pages->get($item);
        
        // Update player's scores and save
        if ($refPage->is("template=place|people")) {
          $task = $pages->get("name=free");
        }
        if ($refPage->is("template=equipment|item")) {
          $task = $pages->get("name=buy");
        }
        $task->comment = $refPage->title;
        $task->refPage = $refPage;
        $task->linkedId = false;
        if ($task->id) {
          updateScore($player, $task, true);
          // No need to checkDeath, Marketplace can't cause death
        }
      }
      // Set group captains
      setCaptains($player->team);
      // Redirect to MarketPlace (in main.js, because doesn't work due to Ajax ?)
      /* $session->redirect($pages->get('/shop')->url.$player->team->name); */
      $url = $pages->get('/shop')->url.$player->team->name;
      echo json_encode(array("sender"=>"marketPlace", "url"=>$url));
    }

    if($input->post->donateFormSubmit) { // donateForm submitted
      $playerId = $input->post->donator;
      $player = $pages->get($playerId);
      $player->of(false);
      $amount = (integer) $input->post->amount;
      $receiverId = $input->post->receiver;
      $receiver = $pages->get($receiverId);
      $receiver->of(false);
      
      // Save donation
      // If valid amount
      if ($player && $receiverId && $amount != 0 && $amount <= $player->GC) {
        // Modify player's page
        $task = $pages->get("template='task', name='donation'");
        $task->comment = $amount. ' GC donated to '.$receiver->title.' ['.$receiver->team->title.']';
        $task->refPage = $receiver;
        $task->linkedId = false;
        updateScore($player, $task, true);
        // No need to checkDeath, Donation can't cause death

        // Set group captains
        setCaptains($player->team);

        // Redirection to Team page (in main.js, because doesn't work due to Ajax ?)
        /* $session->redirect($pages->get("name=players")->url.$player->team->name); */
        $url = $pages->get('/players')->url.$player->team->name;
        echo json_encode(array("sender"=>"marketPlace", "url"=>$url));
      }
    }
  } // End if superUser
?>
