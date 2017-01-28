<?php
  include("./my-functions.inc");

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    $playerId = $input->post->player;
    $itemId = $input->post->item;
    if ($itemId) {
      // Get item's data
      $newItem = $pages->get($itemId);
    }

    // Check if equipment, place or people not already there
    // Except 'Potions'
    $already = false;
    $player = $pages->get($playerId);
    foreach ($player->equipment as $eq) {
      if ($eq->id == $itemId) {
        if($newItem->parent->name !== 'potions') {
          $already = true;
        }
      }
    }
    foreach ($player->places as $pl) {
      if ($pl->id == $itemId) {
        $already = true;
      }
    }
    foreach ($player->people as $pl) {
      if ($pl->id == $itemId) {
        $already = true;
      }
    }

    if ($already == false) {
      if($input->post->buyFormSubmit) { // buyForm submitted
        $player = $pages->get($playerId);
        $task = $pages->get("name='buy'");
        $task->comment = $newItem->title;
        $task->refPage = $newItem;
        $task->linkedId = false;
        updateScore($player, $task, true);
        // No need to checkDeath, Buyform can't cause death
        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->team->title."\r\n";
        $msg .= "Item : ". $newItem->title;
        if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
          mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");
        }
      }

      if($input->post->marketPlaceSubmit) { // marketPlaceForm submitted
        $checkedItems = $input->post->item;
        $playerId = $input->post->player;
        $player = $pages->get($playerId);

        foreach($checkedItems as $item=>$state) {
          // Get item's data
          $newItem = $pages->get($item);
         
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
          if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");
          }
        }
      }

      if($input->post->donateFormSubmit) { // donateForm submitted
        $playerId = $input->post->player;
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
          // Notify admin
          $msg = "Player : ". $player->title." [".$player->team->title."]\r\n";
          $msg .= "Donation amount : ". $amount."\r\n";
          $msg .= "Donated to : ". $receiver->title." [".$receiver->team->title."]";
          if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
            mail("planetalert@tuxfamily.org", "donationForm", $msg, "From: planetalert@tuxfamily.org");
          }
        }
      }
    }

    // Redirect to player's profile
    $session->redirect($pages->get('/players')->url.$player->team->name.'/'.$player->name);
  }

  if ($user->isSuperuser()) { // Admin front-end
    // TODO : Give superUser possibility to record a donation?
    if (isset($input->get->form) && $input->get->form == 'unpublish' && $input->get->newsId != '') {
      $n = $pages->get($input->get->newsId);
      $n->of(false);
      if ($n->publish == 0) {
        $n->publish = 1;
        $n->save();
        //echo 'Unpublish from Newsboard.';
      } else {
        $n->publish = 0;
        $n->save();
        //echo 'News will disappear on reload.';
      }
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

      // Redirect to team page
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
      // Redirect to MarketPlace
      $session->redirect($pages->get('/shop')->url.$player->team->name);
    }

    if($input->post->donateFormSubmit) { // donateForm submitted
      $playerId = $input->post->player;
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

        // Redirection to Team page
        $session->redirect($pages->get("name=players")->url.$player->team->name);
      }
    }
  } // End if superUser
?>
