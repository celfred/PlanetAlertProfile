<?php
  include("./my-functions.inc");

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    $playerId = $input->post->playerId;
    $player = $pages->get($playerId);
    $player->of(false);

    $training = $input->post->training;
    $exerciseId = $input->post->exerciseId;
    $monster = $pages->get($exerciseId)->title;
    $summary = $pages->get($exerciseId)->summary;
    $result = $input->post->result;

    if ($training == true) { // Training session
      // Increase UT value
      $player->underground_training = $player->underground_training + $result;

      if ($result>=1 && $result <=5) {
        $task = $pages->get("name=ut-action-v");
      } else if ($result > 5) {
        $task = $pages->get("name=ut-action-vv");
      }

      if ($exerciseId && $player && $task) {
        $newsBoard = 0;
        // Update player's scores
        updateScore($player, $task);

        // Save player's new scores
        $player->save();
        
        // Record history
        //$taskComment = 'Training "'.$summary.'" [+'.$result.'U.T.]';
        $taskComment = $summary.'" [+'.$result.'U.T.]';
        $refPage = $exerciseId;
        saveHistory($player, $task, $taskComment, $newsBoard, $refPage);
        
        // Record to log file
        $this->log($taskComment.','.$result);

        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->playerTeam."\r\n";
        $msg .= "Training : ". $monster."\r\n";
        $msg .= "Result : ". $result;
        mail("planetalert@tuxfamily.org", "submitFight", $msg, "From: planetalert@tuxfamily.org");
      }
    } else { // Monster fight
      $playerHP = $input->post->playerHP;
      $monsterHP = $input->post->monsterHP;
      $nbAttacks = $input->post->nbAttacks;

      switch($result) {
        case 'RR' :
          $task = $pages->get("name=fight-rr");
          break;
        case 'R' :
          $task = $pages->get("name=fight-r");
          break;
        case 'V' :
          $task = $pages->get("name=fight-v");
          break;
        case 'VV' :
          $task = $pages->get("name=fight-vv");
          break;
        default:
          break;
      }

      if ($exerciseId && $player && $task) {
        $newsBoard = 0;
        // Update player's scores
        // TODO : Set manual score depending on the exercise difficulty?
        updateScore($player, $task);

        // Save player's new scores
        $player->save();

        // Record history
        $taskComment = 'Fight vs. '.$monster.' ['.$result.']';
        saveHistory($player, $task, $taskComment, $newsBoard);
        
        // Record to log file
        $this->log($taskComment.','.$playerHP.','.$monsterHP.','.$nbAttacks.','.$result);

        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->playerTeam."\r\n";
        $msg .= "Fight : ". $monster."\r\n";
        $msg .= "Result : ". $result;
        mail("planetalert@tuxfamily.org", "submitFight", $msg, "From: planetalert@tuxfamily.org");
      }
    }
    
  }
?>
