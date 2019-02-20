<?php namespace ProcessWire;
  $whitelist = array(
      '127.0.0.1',
      '::1'
  );

  if ($user->isLoggedin() && $user->isSuperuser() == false) {
    if (isset($input->get->form) && $input->get->form == 'fightRequest' && $input->get->playerId != '' && $input->get->result != '' && $input->get->monsterId != '') { // Manage fight requests results
      $player = $pages->get($input->get->playerId);
      $monster = $pages->get($input->get->monsterId);
      $result = $input->get->result;
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
      // Update player's scores
      $task->comment = __("Fight request").' ; '.$monster->title.' ['.$result.']';
      $task->refPage = $monster;
      $task->linkedId = false;
      $task->inClass = 1;
      updateScore($player, $task, true);
      checkDeath($player, true);
      // Set group captains
      setGroupCaptain($player->id);
      // Remove fight request
      $player->setAndSave('fight-request', '0');
      // Record to log file
      $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result;
      $log->save('monster-fights', $logText);
    } else {
      $player = $pages->get("template=player, login=$user->name");
      $monster = $pages->get($input->post->exerciseId);
      $result = $input->post->result;
      $training = $input->post->training;
      $speedQuiz = $input->post->speedQuiz;
      $headTeacher = getHeadTeacher($user);
      $user->language = $headTeacher->language;

      if ($training == true) { // Training session
        if ($result>=1 && $result <=3) { // Excellent training session if 3UT or more
          $task = $pages->get("name=ut-action-v"); 
        } else if ($result > 3) {
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
            updateScore($player, $task, true);
            // No need to checkDeath, Underground Training can't cause death
            // Set group captains
            setGroupCaptain($player->id);
            // Check if new record
            list($utGain, $inClassGain) = utGain($monster, $player);
            $newUtGain = $utGain+$inClassGain;
            if ($newUtGain > $monster->best) {
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
            $msg .= __("Monster")." : ". $sanitizer->markupToText($monster->title)."\r\n";
            $msg .= __("Result")." : +". $result.__("UT")."\r\n";
            $msg .= __("Total training on this monster")." : ". $utGain."\r\n";
            $bestTrained = $pages->get($monster->bestTrainedPlayerId);
            $msg .= __("New best player").' :  '. $best.' ('.$bestTrained->title.' ['.$bestTrained->team->title.'] : '.$monster->best.')\r\n';
            $msg .= __("Player's global UT")." : ". $player->underground_training."\r\n";

            if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
              $adminMail = $users->get("name=admin")->email;
              $mail = wireMail();
              $mail->from($adminMail);
              $mail->subject($subject);
              $mail->body($sanitizer->entities1($msg));
              if (isset($headTeacher) && $headTeacher->email != '') {
                $mail->to($headTeacher->email, 'Planet Alert');
              } else {
                $mail->to($adminMail, 'Planet Alert');
              }
              $numSent = $mail->send();
            }
          }
        }
      } else { // Monster fight or Speed Quiz
        if ($speedQuiz) {
          $playerTime = $input->post->playerTime;
          if ($monster->id && $player->id) {
            if ($monster->bestTime == 0 || ($monster->bestTime != 0 && $playerTime < $monster->bestTime)) { // New Master best time
              $result = __("New Master best time!");
              if ($monster->bestTimePlayerId != 0 && $player->bestTimePlayerId != $player->id ) { // Keep old best id if changed
                $oldBest = $monster->bestTimePlayerId;
              } else {
                $oldBest = 0;
              }
              $monster->bestTime = $playerTime;
              $monster->bestTimePlayerId = $player->id;
              $monster->of(false);
              $monster->save();
              // Save also new player best time
              $tmpPage = $player->child("name=tmp")->tmpMonstersActivity->get("monster=$monster");
              if ($tmpPage->bestTime == 0 || ($tmpPage->bestTime != 0 && $playerTime < $tmpPage->bestTime)) {
                $tmpPage->setAndSave('bestTime', $playerTime);
              }
              // Save Master skill
              setMaster($player);
              // Update scores
              // New player gets credit
              $task = $pages->get("template=task, name=best-time");
              $task->comment = __('Best time on ').$monster->title.' : '.ms2string($monster->bestTime);
              $task->refPage = $monster;
              $task->linkedId = false;
              $linkedId = updateScore($player, $task, true);
              // Old best player loses HP
              if (isset($oldBest) && $oldBest != 0 && $oldBest != $player->id) {
                $task = $pages->get("template=task, name=best-time-lost");
                $task->comment = __('Best time lost on ').$monster->title;
                $task->comment .= __("set by").' '.$player->title.' ['.$player->team->title.']';
                $task->refPage = $monster;
                $task->linkedId = $linkedId;
                updateScore($oldBest, $task, true);
              }
              echo '1';
            } else { // Check if new player best time
              $tmpPage = $player->child("name=tmp")->tmpMonstersActivity->get("monster=$monster");
              if ($tmpPage->bestTime == 0 || ($tmpPage->bestTime != 0 && $playerTime < $tmpPage->bestTime)) { // New personal best time
                $result = __("New personal best time!");
                $tmpPage->bestTime = $playerTime;
                $tmpPage->of(false);
                $tmpPage->save();
                echo '2';
              } else {
                $result = __("No best time");
                echo '0';
              }
            }
            // Record to log file
            $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$playerTime;
            $log->save('speed-quiz', $logText);

            // Notify teacher (or admin)
            $subject = _('Speed Quiz ').' : ';
            $subject .= $player->title. ' ['.$player->team->title.']';
            $subject .= ' → '.$result;
            $subject .= ' ['.$monster->title.']';
            $msg = __("Player")." : ". $player->title." [".$player->team->title."]\r\n";
            $msg .= __("Monster")." : ". $sanitizer->markupToText($monster->title)."\r\n";
            $msg .= __("Result")." : ". $result;

            if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
              $adminMail = $users->get("name=admin")->email;
              $mail = wireMail();
              $mail->from($adminMail);
              $mail->subject($subject);
              $mail->body($sanitizer->entities1($msg));
              if (isset($headTeacher) && $headTeacher->email != '') {
                $mail->to($headTeacher->email, 'Planet Alert');
              } else {
                $mail->to($adminMail, 'Planet Alert');
              }
              $numSent = $mail->send();
            }
          }
        } else {
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
              setGroupCaptain($player->id);
              // Record to log file
              $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result.', '.$quality;
              $log->save('monster-fights', $logText);

              // Notify teacher (or admin)
              $subject = _('Monster fight ').' : ';
              $subject .= $player->title. ' ['.$player->team->title.']';
              $subject .= ' → '.$result;
              $subject .= ' ['.$monster->title.']';
              $msg = __("Player")." : ". $player->title." [".$player->team->title."]\r\n";
              $msg .= __("Monster")." : ". $sanitizer->markupToText($monster->title)."\r\n";
              $msg .= __("Result")." : ". $result;
              $msg .= ' ['.__("Quality")." : ".$quality."]\r\n";
              $msg .= __("New player global FP")." : ". $player->fighting_power."\r\n";

              if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
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
        }
      }
    }
  }
?>
