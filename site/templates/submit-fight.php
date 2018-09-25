<?php namespace ProcessWire;
  $whitelist = array(
      '127.0.0.1',
      '::1'
  );

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
        setMonster($player, $monster);
        $task->comment = $monster->title.' [+'.$result.'U.T.]';
        $task->refPage = $monster;
        $task->linkedId = false;
        // test if training is possible
        if ($monster->isTrainable == 0 || $monster->spaced != 0) {
          // Record to log file
          $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result. ' - Training not allowed!';
          $log->save('underground-training', $logText);
        } else {
          $best = __('No');
          $previousUt = $player->underground_training;
          updateScore($player, $task, true);
          // No need to checkDeath, Underground Training can't cause death
          // Set group captains
          setCaptains($player->team);
          // Check if new record
          list($utGain, $inClassGain) = utGain($monster, $player);
          $newUtGain = $utGain+$inClassGain;
          if ($utGain > $monster->best) {
            setBestPlayer($monster, $player, $newUtGain);
            $best = __('Yes');
            echo '1';
          }
          
          // Record to log file
          $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result;
          $log->save('underground-training', $logText);

          // Notify teacher (or admin)
          $subject = _('Underground Training ').' : ';
          $subject .= $player->title. ' ['.$player->team->title.']';
          $subject .= ' → +'.$result.__("UT");
          $subject .= ' ['.$monster->title.']';
          $msg = __("Player")." : ". $player->title." [".$player->team->title."]\r\n";
          $msg .= __("Monster")." : ". $monster->title."\r\n";
          $msg .= __("Result")." : +". $result.__("UT")."\r\n";
          $msg .= __("Player's total training on this monster")." : ". $utGain."\r\n";
          $msg .= __("New best player")." :  ". $best." (".$monster->mostTrained->title.":".$monster->best.")\r\n";
          $msg .= __("Player's global UT")." : ". $player->underground_training."\r\n";

          if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            $adminMail = $users->get("name=admin")->email;
            $mail = wireMail();
            $mail->from($adminMail);
            $mail->subject($subject);
            $mail->body($msg);
            if (isset($headTeacher) && $headTeacher->email != '') {
              $mail->to($headTeacher->email, 'Planet Alert');
            } else {
              $mail->to($adminMail, 'Planet Alert');
            }
            $numSent = $mail->send();
          }
        }
      }
    } else { // Monster fight
      $quality = $input->post->quality;

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

      /* echo 'Before Saving'; */
      if ($monster->id && $player->id && $task->id) {
        $monster = setMonster($player, $monster);
        // Update player's scores
        $task->comment = $monster->title.' ['.$result.']';
        $task->refPage = $monster;
        $task->linkedId = false;
        // test if fight is possible
        if ($monster->isFightable == 0) {
          // Record to log file
          $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result.', '.$quality.' - Fight not allowed!';
          $log->save('monster-fights', $logText);
        } else {
          updateScore($player, $task, true);
          checkDeath($player, true);
          // Set group captains
          setCaptains($player->team);
          // Record to log file
          $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result.', '.$quality;
          $log->save('monster-fights', $logText);

          // Notify teacher (or admin)
          $subject = _('Monster fight ').' : ';
          $subject .= $player->title. ' ['.$player->team->title.']';
          $subject .= ' → '.$result;
          $subject .= ' ['.$monster->title.']';
          $msg = __("Player")." : ". $player->title." [".$player->team->title."]\r\n";
          $msg .= __("Monster")." : ". $monster->title."\r\n";
          $msg .= __("Result")." : ". $result;
          $msg .= ' ['.__("Quality")." : ".$quality."]\r\n";
          $msg .= __("New player global FP")." : ". $player->fighting_power."\r\n";

          if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            $adminMail = $users->get("name=admin")->email;
            $mail = wireMail();
            $mail->from($adminMail);
            $mail->subject($subject);
            $mail->body($msg);
            if (isset($headTeacher) && $headTeacher->email != '') {
              $mail->to($headTeacher->email, 'Planet Alert');
            } else {
              $mail->to($adminMail, 'Planet Alert');
            }
            $numSent = $mail->send();
          }
          $msg = "Player : ". $player->title."\r\n";
          $msg .= "Team : ". $player->team->title."\r\n";
          $msg .= "Fight : ". $monster->title."\r\n";
          $msg .= "Result : ". $result;
          if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            if (isset($headTeacher) && $headTeacher->email != '') {
              mail($headTeacher->email, "submitFight", $msg, "From: planetalert@tuxfamily.org");
            } else {
              mail($users->get("name=admin")->email, "submitFight", $msg, "From: planetalert@tuxfamily.org");
            }
          }
        }
      }
    }
  }
?>
