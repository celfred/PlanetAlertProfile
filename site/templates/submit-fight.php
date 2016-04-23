<?php
  $whitelist = array(
      '127.0.0.1',
      '::1'
  );

  include("./my-functions.inc");

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    $player = $pages->get("template=player, login=$user->name");
    $exerciseId = $input->post->exerciseId;
    $monster = $pages->get($exerciseId);
    $result = $input->post->result;
    $training = $input->post->training;

    if ($training == true) { // Training session
      if ($result>=1 && $result <=5) {
        $task = $pages->get("name=ut-action-v");
      } else if ($result > 5) {
        $task = $pages->get("name=ut-action-vv");
      }

      if ($monster->id && $player->id && $task->id) {
        $taskComment = $monster->title.' [+'.$result.'U.T.]';
        updateScore($player, $task, $taskComment, $monster, '', true);
        // Check if new record
        $utGain = utGain($monster, $player);
        if ($utGain > $monster->best) {
          setBestPlayer($monster, $player, $utGain);
          echo '1';
        }
        
        // Record to log file
        $logText = $player->id.' ('.$player->title.' ['.$player->playerTeam.']),'.$monster->id.' ('.$monster->title.'),'.$result;
        $log->save('underground-training', $logText);

        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->playerTeam."\r\n";
        $msg .= "Training : ". $monster->title."\r\n";
        $msg .= "Result : ". $result;

        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
          mail("planetalert@tuxfamily.org", "submitFight", $msg, "From: planetalert@tuxfamily.org");
        }
      }
    } else { // Monster fight TODO
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
        $taskComment = 'Fight vs. '.$monster->title.' ['.$result.']';
        saveHistory($player, $task, $taskComment, $newsBoard);
        
        // Record to log file
        $this->log($taskComment.','.$playerHP.','.$monsterHP.','.$nbAttacks.','.$result);

        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->playerTeam."\r\n";
        $msg .= "Fight : ". $monster->title."\r\n";
        $msg .= "Result : ". $result;
        mail("planetalert@tuxfamily.org", "submitFight", $msg, "From: planetalert@tuxfamily.org");
      }
    }
  }
?>
