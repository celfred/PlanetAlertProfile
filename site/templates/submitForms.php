<?php
  include("./my-functions.inc");

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    $playerId = $input->post->player;
    $itemId = $input->post->item;
    if ($itemId) {
      // Get item's data
      $newItem = $pages->get($itemId);
    }

    // Check if equipment or place not already there
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

    if ($already == false) {
      if($input->post->buyFormSubmit) { // buyForm submitted
        // Modify player's page
        $player = $pages->get($playerId);
        $player->of(false);
       
        // Set new values
        $player->GC = (int) $player->GC - $newItem->GC;
        if ($newItem->template == 'equipment' || $newItem->template == 'item') {
          switch($newItem->parent->name) {
            case 'potions' : // instant use potions?
              $player->HP = $player->HP + $newItem->HP;
              if ($player->HP > 50) {
                $player->HP = 50;
              }
              $player->equipment->add($newItem);
              break;
            default:
              $player->equipment->add($newItem);
              break;
          }

          // Update player's scores
          $task = $pages->get("name='buy'");
          updateScore($player, $task);

          // Save player's new scores
          $player->save();

          // Record history
          $newsBoard = 1;
          $taskComment = $newItem->title;
          saveHistory($player, $task, $taskComment, $newsBoard);
          
          // Notify admin
          $msg = "Player : ". $player->title."\r\n";
          $msg .= "Team :". $player->playerTeam."\r\n";
          $msg .= "Item :". $newItem->title;
          mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");
        }
      }

      if($input->post->marketPlaceSubmit) { // marketPlaceForm submitted
        $checkedItems = $input->post->item;
        $playerId = $input->post->player;

        foreach($checkedItems as $item=>$state) {
          // Modify player's page
          $player = $pages->get($playerId);
          $player->of(false);
          
          // Get item's data
          $newItem = $pages->get($item);
         
          // Set new values
          $player->GC = (int) $player->GC - $newItem->GC;
          if ($newItem->template == 'equipment' || $newItem->template == 'item') {
            switch($newItem->parent->name) {
              case 'potions' : // instant use potions?
                $player->HP = $player->HP + $newItem->HP;
                if ($player->HP > 50) {
                  $player->HP = 50;
                }
                $player->equipment->add($newItem);
                break;
              default:
                $player->equipment->add($newItem);
                break;
            }
            $task = $pages->get("name='buy'");
            $newsBoard = 1;
          }
          if ($newItem->template == 'place') {
            $player->places->add($newItem);
            $task = $pages->get("name='free'");
            $newsBoard = 1;
          }

          // Update player's scores
          updateScore($player, $task);

          // Save player's new scores
          $player->save();

          // Record history
          $taskComment = $newItem->title;
          saveHistory($player, $task, $taskComment, $newsBoard);
          
          // Notify admin
          $msg = "Player : ". $player->title."\r\n";
          $msg .= "Team :". $player->playerTeam."\r\n";
          $msg .= "Item :". $newItem->title;
          mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");
        }
      }

      if($input->post->donateFormSubmit) { // donateForm submitted
        $playerId = $input->post->player;
        $player = $pages->get($playerId);
        $player->of(false);
        $amount = $input->post->amount;
        $receiverId = $input->post->receiver;
        $receiver = $pages->get($receiverId);
        $receiver->of(false);
        
        // Save donation
        if ($player && $receiverId && $amount != 0) {
          // Modify player's page
          $player->GC = $player->GC - $amount;
          $task = $pages->get("template='task', name='donation'");
          $player->HP = $player->HP + $task->GC;
          $player->donation = $player->donation + $amount;

          $player->save();
          // Record history
          $taskComment = 'Donation of '.$amount. ' GC to '.$receiver->title.' ['.$receiver->playerTeam.']';
          $newsBoard = 1;
          saveHistory($player, $task, $taskComment, $newsBoard);

          // Modify receiver's page
          $receiver->GC = $receiver->GC + $amount;
          $receiver->save();
          // Record history
          $task = $pages->get("template='task', name='donated'");
          $taskComment = 'Donation of '.$amount. ' GC by '.$player->title.' ['.$player->playerTeam.']';
          $newsBoard = 0;
          saveHistory($receiver, $task, $taskComment, $newsBoard);
          
          // Notify admin
          $msg = "Player : ". $player->title." [".$player->playerTeam."]\r\n";
          $msg .= "Donation amount :". $amount;
          $msg .= "Donated to :". $receiver->title." [".$receiver->playerTeam."]";
          mail("planetalert@tuxfamily.org", "donationForm", $msg, "From: planetalert@tuxfamily.org");
        }
      }
    }

    // Redirect to player's profile
    $session->redirect($pages->get('/players')->url.$player->playerTeam.'/'.$player->name);
  }

  if ($user->isSuperuser()) { // Admin front-end
    // TODO : Give superUser possibility to record a donation
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
      foreach($checkedPlayers as $plyr_task=>$state) {
        list($playerId, $taskId) = explode('_', $plyr_task);
        $comment = 'comment_'.$playerId.'_'.$taskId;
        $taskComment = trim($input->post->$comment);

        $player = $pages->get($playerId);
        $player->of(false);

        // Update player's scores
        $task = $pages->get($taskId); 
        updateScore($player, $task);

        // Save player's new scores
        $player->save();

        // Record history
        saveHistory($player, $task, $taskComment);

        $team = $player->playerTeam;

        // Check if extra-action needs to be taken
        // For example : 3 forgotten homework...
        if (checkHk($player)) {
          // Update player's scores
          $task = $pages->get("template=task, name=penalty"); 
          //updateScore($player, $task);
          $player->GC = round($player->GC/2); // Penalty = Half GC taken away
          // Save player's page
          $player->save();
          // Register a new penalty
          $comment = 'Automatic homework penalty';
          saveHistory($player, $task, $comment, 1);
        }
      }
      // Redirect to team page
      $session->redirect($pages->get('/players')->url.$team);
    }

    if($input->post->marketPlaceSubmit) { // marketPlaceForm submitted
      $checkedItems = $input->post->item;
      $playerId = $input->post->player;

      foreach($checkedItems as $item=>$state) {
        // Modify player's page
        $player = $pages->get($playerId);
        $player->of(false);
        
        // Get item's data
        $newItem = $pages->get($item);
       
        // Set new values
        $player->GC = (int) $player->GC - $newItem->GC;
        if ($newItem->template == 'equipment' || $newItem->template == 'item') {
          switch($newItem->parent->name) {
            case 'potions' : // instant use potions?
              $player->HP = $player->HP + $newItem->HP;
              if ($player->HP > 50) {
                $player->HP = 50;
              }
              $player->equipment->add($newItem);
              break;
            default:
              $player->equipment->add($newItem);
              break;
          }
          $task = $pages->get("name='buy'");
          $newsBoard = 1;
        }
        if ($newItem->template == 'place') {
          $player->places->add($newItem);
          $task = $pages->get("name='free'");
          $newsBoard = 1;
        }
        // Update player's scores
        updateScore($player, $task);

        // Save player's new scores
        $player->save();

        // Record history
        $taskComment = $newItem->title;
        saveHistory($player, $task, $taskComment, $newsBoard);
      }
      // Redirect to marketPlace
      $team = $player->playerTeam;
      $session->redirect($pages->get('/shop')->url.$team);
    }
  } // End if superUser

?>
