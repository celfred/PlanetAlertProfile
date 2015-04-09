<?php
  include("./my-functions.inc");

  if ($user->isLoggedin()) {
    if($input->post->buyFormSubmit) { // buyForm submitted
      $playerId = $input->post->player;
      $itemId = $input->post->item;

      if ($itemId) {
        // Modify player's page
        $player = $pages->get($playerId);
        $player->of(false);
        
        // Get item's data
        $newItem = $pages->get($itemId);
       
        // Set new values
        $player->GC = (int) $player->GC - $newItem->GC;
        if ($newItem->template == 'equipment') {
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

        // Save player's new scores
        $player->save();

        // Record history
        $taskComment = $newItem->title;
        saveHistory($player, $task, $taskComment, $newsBoard);
      }
      
    // Notify admin
    $msg = "Player : ". $player->title;
    $msg .= "Team :". $player->team->title;
    $msg .= "Item :". $item->title;
    mail("planetalert@tuxfamily.org", "buyForm", $msg, "From: planetalert@tuxfamily.org");

    // Redirect to player's profile
    $session->redirect($pages->get('/players')->url.$player->team->name.'/'.$player->name);

    }
  }

  if ($user->isSuperuser()) { // Admin front-end
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
      }
    // Redirect to team page
    $session->redirect($pages->get('/players')->url.$input->post->team);
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
        if ($newItem->template == 'equipment') {
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

        // Save player's new scores
        $player->save();

        // Record history
        $taskComment = $newItem->title;
        saveHistory($player, $task, $taskComment, $newsBoard);
      }
    // Redirect to marketPlace
    $session->redirect($pages->get('/shop')->url.$input->post->team);
    }
  } // End if superUser


?>
