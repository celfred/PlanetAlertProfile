<?php
  $whitelist = array(
      '127.0.0.1',
      '::1'
  );

  include("./my-functions.inc");

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    $player = $pages->get("template=player, login=$user->name");
    $monster = $pages->get($input->post->exerciseId);
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
        $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result;
        $log->save('underground-training', $logText);

        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->team->title."\r\n";
        $msg .= "Training : ". $monster->title."\r\n";
        $msg .= "Result : ". $result;

        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
          mail("planetalert@tuxfamily.org", "submitTraining", $msg, "From: planetalert@tuxfamily.org");
        }
      }
    } else { // Monster fight
      $quality = $input->post->quality;

      switch($result) {
        case 'RR' :
          $task = $pages->get("name=test-rr|fight-rr");
          break;
        case 'R' :
          $task = $pages->get("name=test-r|fight-r");
          break;
        case 'V' :
          $task = $pages->get("name=test-v|fight-v");
          break;
        case 'VV' :
          $task = $pages->get("name=test-vv|fight-vv");
          break;
        default:
          break;
      }

      echo 'Before Saving';
      if ($monster->id && $player->id && $task->id) {
        // Update player's scores
        $taskComment = $monster->title.' ['.$result.']';
        updateScore($player, $task, $taskComment, $monster, '', true);
        checkDeath($player, true);
        echo 'Saving';

        // Record to log file
        $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result.', '.$quality;
        $log->save('monster-fights', $logText);

        // Notify admin
        $msg = "Player : ". $player->title."\r\n";
        $msg .= "Team : ". $player->team->title."\r\n";
        $msg .= "Fight : ". $monster->title."\r\n";
        $msg .= "Result : ". $result;
        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
          mail("planetalert@tuxfamily.org", "submitFight", $msg, "From: planetalert@tuxfamily.org");
        }
      }
    }
  }
?>
