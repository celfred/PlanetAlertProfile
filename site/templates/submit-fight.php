<?php namespace ProcessWire;
  $whitelist = array(
      '127.0.0.1',
      '::1'
  );

  $adminMail = $users->get("name=admin")->email;

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
      $player->setAndSave('fight_request', '');
      // Record to log file
      $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result;
      $log->save('monster-fights', $logText);
    } else {
      $error = false;
      $player = $pages->get("template=player, login=$user->name");
      $monster = $pages->get($input->post->exerciseId);
      $result = $input->post->result;
      $training = $input->post->training;
      $speedQuiz = $input->post->speedQuiz;
      $headTeacher = getHeadTeacher($user);
      $user->language = $headTeacher->language;
      $lastEvent = lastEvent($player);

      if ($lastEvent && $lastEvent->task->is("name~=ut-action|fight") && $lastEvent->refPage == $monster && $lastEvent->date >= (time()-10)) { // Duplicate recording ?
        // Record to log file
        $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result. ' - Duplicate ? > IGNORED';
        $log->save('underground-training', $logText);
        // Notify teacher (or admin)
        $subject = _('Underground Training Duplicate ?').' : ';
        $subject .= $player->title. ' ['.$player->team->title.']';
        $subject .= ' - +'.$result.__("UT");
        $subject .= ' ['.$monster->title.']';
        $msg = __("A previous identical UT session should have been recorded a few seconds ago. You can ignore this warning. If not, please contact the administrator.");
        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
          $message = $mail->new();
          $message->from($adminMail, "Planel Alert");
          if (isset($headTeacher) && $headTeacher->email != '') {
            $message->to($headTeacher->email);
          } else {
            $message->to($adminMail);
          }
          $message->subject($subject);
          $message->body($msg);
          $numSent = $message->send();
        }
      } else {
        if ($training == true) { // Training session
          if ($result>=1 && $result <=3) { // Excellent training session if 4UT or more
            $task = $pages->get("name=ut-action-v"); 
          } else if ($result > 3) {
            $task = $pages->get("name=ut-action-vv");
          }

          if ($monster->id && $player->id && $task->id) {
            setMonster($player, $monster);
            $task->comment = $monster->title.' [+'.$result.__('UT').']';
            $task->refPage = $monster;
            $task->linkedId = false;
            // Check if all challenges have been done today
            $todayChallenge = false;
            $teamChallenges = $pages->get("parent.name=teachers, template=teacherProfile, name=$headTeacher->name")->teamChallenges->get("team=$player->team");
            $totalNbChallenges = $teamChallenges->linkedMonsters->count();
            if ($teamChallenges->linkedMonsters->has($monster)) {
              $nbChallenge = [];
              array_push($nbChallenge, $result);
              $monster->isTrainable = 1; // Overridde isTrainable state
              $monster->spaced = 0; // in any case
              if ($totalNbChallenges > 1) { // Check today's activity on other challenges
                foreach($teamChallenges->linkedMonsters as $m) {
                  list($utGain, $inClassUtGain) = utGain($m, $player, date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"));
                  if ($utGain > 0) {
                    array_push($nbChallenge, $utGain);
                  }
                }
              }
              if (count($nbChallenge) == $totalNbChallenges) { // All challenges have been completed
                sort($nbChallenge);
                $todayChallenge = true;
              }
            }
            // test if training is possible
            if ($monster->isTrainable == 0 || $monster->spaced != 0) {
              // Record to log file
              $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result. ' - Training not allowed!';
              $log->save('underground-training', $logText);
            } else {
              $best = __('No');
              $historyPageId = updateScore($player, $task, true);
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
                $bestTrained = $player;
              } else {
                if ($monster->bestTrainedPlayerId != '') {
                  $bestTrained = $pages->get($monster->bestTrainedPlayerId);
                } else {
                  $bestTrained = false;
                }
              }
              // Validate solo-mission thanks to today's challenges
              if ($todayChallenge) {
                if ($nbChallenge[0] < 3) {
                  $task = $pages->get("template=task, name=micro-solo-v");
                } else if ($nbChallenge[0] < 5) {
                  $task = $pages->get("template=task, name=solo-v");
                } else {
                  $task = $pages->get("template=task, name=solo-vv");
                }
                if (!isset($task)) {
                  $error = __("No solo-mission set !");
                } else {
                  $task->comment = __("All challenges completed !");
                  $challengesList = $teamChallenges->linkedMonsters->implode(', ', '{title}');
                  $task->comment .= ' ('.$challengesList.')'; 
                  $task->refPage = null;
                  $task->eDate = date('m/d/Y H:i:s', time()+10);
                  $task->linkedId = $historyPageId;
                  updateScore($player, $task, true);
                }
              }
              
              // Record to log file
              $logText = $player->id.' ('.$player->title.' ['.$player->team->title.']),'.$monster->id.' ('.$monster->title.'),'.$result;
              if ($todayChallenge) {
                $logText .= ', Validated challenge ('.$challengesList.')';
              }
              $log->save('underground-training', $logText);

              // Notify teacher (or admin)
              $subject = _('Underground Training ').' : ';
              $subject .= $player->title. ' ['.$player->team->title.']';
              $subject .= ' - +'.$result.__("UT");
              $subject .= ' ['.$monster->title.']';
              $msg = __("Total training on this monster")." : ". $utGain."\r\n";
              if ($bestTrained) {
                $msg .= __("New best player")." :  ". $best." (".$bestTrained->title." [".$bestTrained->team->title."] : ".$monster->best.")\r\n";
              }
              $msg .= __("Global UT of player")." : ". $player->underground_training."\r\n";
              if ($teamChallenges->linkedMonsters->has($monster)) {
                $msg .= __("Belongs to challenges !")."\r\n";
              }
              if ($todayChallenge) {
                $msg .= __("All challenges are completed !")." (".$challengesList.")\r\n";
                if (!$error) {
                  $msg .= sprintf(__('A %s has been validated.'), $task->title);
                } else {
                  $msg .= __('No solo-mission are set in your actions !');
                }
              }

              if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                $message = $mail->new();
                $message->from($adminMail, "Planel Alert");
                if (isset($headTeacher) && $headTeacher->email != '') {
                  $message->to($headTeacher->email);
                } else {
                  $message->to($adminMail);
                }
                $message->subject($subject);
                $message->body($msg);
                $numSent = $message->send();
              }
            }
          }
        } else { // Monster fight or Speed Quiz
          if ($speedQuiz) {
            $playerTime = $input->post->playerTime;
            if ($monster->id && $player->id) {
              if ($monster->bestTime == 0 || ($monster->bestTime != 0 && $playerTime < $monster->bestTime)) { // New Master best time
                $result = __("New Master best time!").' ('.ms2string($playerTime).')';;
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
                  $result = __("New personal best time!").' ('.ms2string($playerTime).')';
                  $tmpPage->bestTime = $playerTime;
                  $tmpPage->of(false);
                  $tmpPage->save();
                  $task = $pages->get("template=task, name=best-time");
                  $task->comment = __('Best time on ').$monster->title.' : '.ms2string($monster->bestTime);
                  $task->refPage = $monster;
                  $task->linkedId = false;
                  updateScore($player, $task, true);
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
              $subject .= ' - '.$result;
              $subject .= ' ['.$monster->title.']';
              $msg = __("Player")." : ".$player->title." [".$player->team->title."]\r\n";
              $msg .= __("Monster")." : ".$monster->title."\r\n";
              $msg .= __("Result")." : ". $result;

              if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                $message = $mail->new();
                $message->from($adminMail, "Planel Alert");
                if (isset($headTeacher) && $headTeacher->email != '') {
                  $message->to($headTeacher->email);
                } else {
                  $message->to($adminMail);
                }
                $message->subject($subject);
                $message->body($msg);
                $numSent = $message->send();
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
                $subject .= ' - '.$result;
                $subject .= ' ['.$monster->title.']';
                $msg = __("Player")." : ".$player->title." [".$player->team->title."]\r\n";
                $msg .= __("Monster")." : ".$monster->title."\r\n";
                $msg .= __("Result")." : ". $result;
                $msg .= ' ['.__("Quality")." : ".$quality."]\r\n";
                $msg .= __("Global FP of player")." : ". $player->fighting_power."\r\n";

                if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
                  $message = $mail->new();
                  $message->from($adminMail, "Planel Alert");
                  if (isset($headTeacher) && $headTeacher->email != '') {
                    $message->to($headTeacher->email);
                  } else {
                    $message->to($adminMail);
                  }
                  $message->subject($subject);
                  $message->body($msg);
                  $numSent = $message->send();
                }
              }
            }
          }
        }
      }
    }
  }
?>
