<?php
  include("./my-functions.inc");

  if ($user->isSuperuser()) { // Admin front-end

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
        }
        if ($newItem->template == 'place') {
          $player->places->add($newItem);
        }

        // Save player's new scores
        $player->save();

        // Record history
        $task = $pages->get("name='buy'");
        $taskComment = $newItem->title;
        saveHistory($player, $task, $taskComment);
      }
    // Redirect to marketPlace
    $session->redirect($pages->get('/shop')->url.$input->post->team);
    }
  } // End if superUser


?>
