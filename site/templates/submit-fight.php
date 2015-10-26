<?php
  include("./my-functions.inc");

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    $playerId = $input->post->playerId;
    $player = $pages->get($playerId);
    $player->of(false);

    $exerciseId = $input->post->exerciseId;
    $monster = $pages->get($exerciseId)->title;
    $playerHP = $input->post->playerHP;
    $monsterHP = $input->post->monsterHP;
    $nbAttacks = $input->post->nbAttacks;
    $result = $input->post->result;

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
      $msg .= "Fight : ". $monster;
      mail("planetalert@tuxfamily.org", "submitFight", $msg, "From: planetalert@tuxfamily.org");
    }
  }
?>
