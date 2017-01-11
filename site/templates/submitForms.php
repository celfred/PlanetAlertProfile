<?php
  $whitelist = array(
      '127.0.0.1',
      '::1'
  );

  include("./head.inc");
  /* include("./my-functions.inc"); */

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
        $taskComment = $newItem->title;
        updateScore($player, $task, $taskComment, $newItem, '', true);
        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->team->title."\r\n";
        $msg .= "Item : ". $newItem->title;
        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
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
          $taskComment = $newItem->title;
          updateScore($player, $task, $taskComment, $newItem, '', true);

          // Notify admin
          $msg = "Player : ". $player->title."\r\n";
          $msg .= "Team : ". $player->team->title."\r\n";
          $msg .= "Item : ". $newItem->title;
          if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
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
          $taskComment = $amount. ' GC donated to '.$receiver->title.' ['.$receiver->team->title.']';
          updateScore($player, $task, $taskComment, $receiver, '', true);

          // Notify admin
          $msg = "Player : ". $player->title." [".$player->team->title."]\r\n";
          $msg .= "Donation amount : ". $amount."\r\n";
          $msg .= "Donated to : ". $receiver->title." [".$receiver->team->title."]";
          if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
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
    if ($input->get->form && $input->get->form == 'unpublish' && $input->get->newsId != '') {
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
      /* foreach($checkedPlayers as $plyr_task=>$state) { */
        /* list($playerId, $taskId) = explode('_', $plyr_task); */
      // Record checked task for each player
      $current = 0;
      for ($i=0; $i<count($checked); $i++) {
        list($playerId, $taskId) = explode('_', $checked[$i]);
        $comment = 'comment_'.$playerId.'_'.$taskId;

        $player = $pages->get($playerId);

        // Update player's scores and save
        $task = $pages->get($taskId); 
        $taskComment = trim($input->post->$comment);
        updateScore($player, $task, $taskComment, '', '', true);
        $current++;
        outputProgress($current, count($checked), 'Please wait while saving adminTable');
      }
      $current = 0;
      // Check death for each player
      for ($i=0; $i<count($checked); $i++) {
        list($playerId, $taskId) = explode('_', $checked[$i]);
        $player = $pages->get($playerId);
        checkDeath($player, true);
        $current++;
        outputProgress($current, count($checked), 'Analysing Deaths');
      }
      // Redirect to team page // PHP redirection no longer runs because of outputProgress()
      /* $session->redirect($pages->get('/players')->url.$player->team->name); */
      // Use JS redirection instead
      js_redirect($pages->get('/players')->url.$player->team->name);
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
        $taskComment = $refPage->title;
        if ($task->id) {
          updateScore($player, $task, $taskComment, $refPage, '', true);
        }

      }
      // Redirect to marketPlace
      $session->redirect($pages->get('/shop')->url.$player->team->name);
    }
  } // End if superUser

  include("./foot.inc");
?>
