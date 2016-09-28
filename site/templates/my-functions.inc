<?php

function getMultiDimensional($values, $prefix) {
  // Validate the arguments
  if(!is_array($values) and !($values instanceof Traversable))
    throw new Exception("Invalid values");
  $len = strlen($prefix);
  if(!$len)
    throw new Exception("Invalid prefix");
 
  $output = Array();

  foreach($values as $key=>$value)
  {
    // The key needs to match our prefix
    if(strcmp(substr($key,0,$len), $prefix) != 0)
      continue;

    // We expect the other part of the key to hold numeric IDs
    $id = intval(substr($key,$len));
    if(!$id)
      continue;

    $output[$id] = $value;
  }
  return $output;
}

function initPlayer($player) {
  $pages = wire('pages');
  // Get previous score from last archived page
  $prevScores = $player->find("template=archive, name!=history")->sort("date")->last();
  if ($prevScores->id) {
    $player->karma = $prevScores->karma;
    $player->HP = 50;
    $player->GC = $prevScores->GC;
    $player->XP = $prevScores->XP;
    $player->level = $prevScores->level;
    $player->places = $prevScores->places;
    $player->people = $prevScores->people;
    $player->donation = $prevScores->donation;
    $player->underground_training = $prevScores->underground_training;
    $player->fighting_power = $prevScores->fighting_power;
  } else {
    $player->karma = 0;
    $player->HP = 50;
    $player->GC = 0;
    $player->XP = 0;
    $player->level = 1;
    $player->donation = 0;
    $player->underground_training = 0;
    $player->fighting_power = 0;
    $player->equipment->removeAll();
    $player->places->removeAll();
    $player->people->removeAll();
  }
  return $player;
}

function updateScore($player, $task, $comment = '', $refPage = '', $linkedId ='', $real = true) {
  $pages = wire('pages');
  $player = $pages->get($player->id);
  $oldNbEl = $player->places->count()+$player->people->count();
  $task = $pages->get($task->id);
  if ($refPage) {
    $refPage = $pages->get($refPage->id);
  } else {
    $refPage = false;
  }
  if ($task->is("name!=death")) {
    // Task details to calculate new score
    $tXP = $task->XP;
    $tHP = $task->HP;
    $tGC = $task->GC;
    
    // Ponderate task's impact according to player's equipment
    // Except for training (core values are used)
    $deltaXP = 0;
    $deltaHP = 0;
    if ($task->is("name!=ut-action-v|ut-action-vv")) {
      if ($player->equipment) {
        // Limit to the 2 best weapons
        $concerned_weapons = $player->equipment->find("category.name=weapons, sort=-XP, limit=2");
        foreach ($concerned_weapons as $item) {
          $deltaXP = $deltaXP + $item->XP;
        }
        // Limit to 1 best protection
        $concerned_protection = $player->equipment->find("category.name=protections, sort=-HP, limit=1");
        foreach ($concerned_protection as $item) {
          $deltaHP = $deltaHP + $item->HP;
        }
        if ($tHP < 0) { // Negative task
          // Loss of 1 minimum whatever the equipment
          if ( $tHP + $deltaHP >= 0 ) {
            $deltaHP = abs($tHP) - 1;
          }
          // Get rid of weapons' bonus
          $deltaXP = 0;
        } else { // Positive task
          $deltaHP = 0;
        }
        if ($tXP == 0) { // Task provides no XP gain, disable weapons
          $deltaXP = 0;
        }
      }
    } else {
      preg_match("/\[\+([\d]+)U\.T\.\]/", $comment, $matches);
      if ($matches[0]) {
        $player->underground_training = (int) $player->underground_training + $matches[1];
      } else {
        $player->underground_training = (int) $player->underground_training + 1;
      }
    }

    // Calculate player's new score
    // Minus GC (0 if positive, costless, or needing extra action tasks, so no problem
    $player->GC = (int) $player->GC + $tGC;
    $player->oldHP = $player->HP; // Record previous HP for checkDeath function
    $player->HP = (int) $player->HP + $tHP + $deltaHP;
    $player->XP = (int) $player->XP + $tXP + $deltaXP;

    // Extra action?
    if ($task->is("name=penalty")) { // Half GC if penalty
      $player->GC = (int) round($player->GC/2);
    }
    if ($task->is("name=donation")) {
      preg_match("/\d+/", $comment, $matches);
      $player->GC = (int) $player->GC - $matches[0];
      $amount = $matches[0];
      $player->donation = (int) $player->donation + $amount;
    }
    if ($task->is("name=donated")) {
      preg_match("/\d+/", $comment, $matches);
      $player->GC = (int) $player->GC + $matches[0];
    }
    if ($task->is("name=fake-donator")) {
      $player->donation = 0; // Reset donation indicator
    }
    if ($task->is("name=bought")) {
      if ($refPage) {
        $player->equipment->add($refPage);
      }
    }
    if ($task->is("name=buy|free")) {
      if ($refPage) {
        $player->GC = (int) $player->GC-$refPage->GC;
        // Add item if needed
        if ($refPage->is("template=equipment|item")) {
          switch($refPage->parent->name) {
            case 'potions' : // instant use potions?
              $player->HP = $player->HP + $refPage->HP;
              if ($player->HP > 50) {
                $player->HP = 50;
              }
              break;
            case 'group-items' : // Make item available to each group member
              $player->equipment->add($refPage);
              $members = $pages->find("template=player, team=$player->team, group=$player->group, name!=$player->name");
              foreach ($members as $p) {
                $bought = $pages->get("name=bought");
                $comment = $refPage->title." [unlocked]";
                updateScore($p, $bought, $comment, $refPage, $linkedId, $real);
              }
              break;
            default:
              $player->equipment->add($refPage);
              break;
          }
        }
        // Add place if needed
        if ($refPage->is("template=place")) {
          $player->places->add($refPage);
        }
        // Add people if needed
        if ($refPage->is("template=people")) {
          $player->people->add($refPage);
        }
      }
    }
    if ($task->is("name=group-death|team-death")) { // No death because of others
      if ($player->oldHP > 0 && $player->HP<=0) { $player->HP = 1; }
    }
    // Set fighting power
    if ($player->fighting_power == '') {$player->fighting_power = 0; }
    if ($task->is("name=test-rr")) {
      $player->fighting_power = $player->fighting_power-2;
    }
    if ($task->is("name=test-r|wrong-invasion")) {
      $player->fighting_power = $player->fighting_power-1;
    }
    if ($task->is("name=test-v|right-invasion")) {
      $player->fighting_power = $player->fighting_power+1;
    }
    if ($task->is("name=test-vv")) {
      $player->fighting_power = $player->fighting_power+2;
    }
    if ($player->fighting_power < 0) { $player->fighting_power = 0; }

    if ($task->is("name=remove") && $refPage != '') { // Remove element
      if ($refPage->template == 'place') {
        $player->places->remove($refPage);
      }
      if ($refPage->template == 'people') {
        $player->people->remove($refPage);
      }
    }

    // Check GC, HP...
    if ($player->GC < 0) { $player->GC = 0; }
    if ($player->HP < 0) { $player->HP = 0; }
    if ($player->HP >= 50) { $player->HP = 50; }

    checkLevel($player);

    // Set karma
    $player->karma = getKarma($player);

    if ($real == true) { // Save player's new scores if necessary
      $player->of(false);
      $player->save();

      // Publish on Newsboard?
      if ($task->is("name=penalty|buy|free|ut-action-v|ut-action-vv|bought")) {
        $newsBoard = 1;
      } else {
        $newsBoard = 0;
      }

      // Set date for correct sorting
      if ($task->is("name!=penalty|team-death|group-death")) {
        $eDate = date('m/d/Y H:i:s', time());
      } else {
        $eDate = date('m/d/Y H:i:s', time()+1);
      }
      // Save history
      $newLinkedId = saveHistory($player, $task, $comment, $newsBoard, $refPage, $eDate, $linkedId);

      // Extra action?
      if (checkHk($player) >= 3) { // Record a Hk penalty
        $penalty = $pages->get("template=task, name=penalty"); 
        $comment = 'Automatic homework penalty';
        updateScore($player, $penalty, $comment, '', $newLinkedId, true);
      }

      if ($task->is("name=donation")) { // Donation, record the linked donated event
        $comment = $amount. ' GC received from '.$player->title.' ['.$player->team->title.']';
        $task = $pages->get("name=donated");
        updateScore($refPage, $task, $comment, '', $newLinkedId, true);
      }

      if ($task->is("name=wrong-invasion")) { // Remove element if 3rd wrong invasion
        $prevInvasions = $player->get("name=history")->find("template=event, task.name=wrong-invasion, refPage=$refPage, sort=-date");
        if ($prevInvasions->count() >= 2) {
          $remove = $pages->get("template=task, name=remove");
          $removeComment = '3rd wrong invasions on '.$refPage->title;
          updateScore($player, $remove, $removeComment, $refPage, '', $real);
        }
      }

      // Save new team scores (for cache) if necessary
      $newNbEl = $player->places->count()+$player->people->count();
      if ($newNbEl != $oldNbEl) {
        updateTeamScores($player->team);
      }

      // Set Ambassador skill if necessary
      $ambassador = $pages->get("name=ambassador");
      if (checkStreak($player) == 10) {
        $player->skills->add($ambassador);
      } else {
        $player->skills->remove($ambassador);
      }
      $player->save();
    }
  } else { // Player died
    if ($player->level > 1) { // Lose 1 level
      $player->level = $player->level - 1;
      $player->HP = 50;
      // Lose all Gold Coins
      $player->GC = 0;
    } else { // Init player scores for a new start
      $player->level = 1;
      $player->HP = 50;
      $player->GC = 0;
      $player->XP = 0;
    }
    // Lose all equipment but group-items
    foreach($player->equipment as $e) {
      if ($e->category->name != 'group-items') {
        $player->equipment->remove($e);
      }
    }
    if ($real == true) { // Save player's death if necessary
      $eDate = date('m/d/Y H:i:s', time()+60);
      $task = $pages->get("name=death");
      $newLinkedId = saveHistory($player, $task, $comment, '', $refPage, $eDate, '');
      $player->of(false);
      $player->save();
      // Team and group loss because of player's death
      $teamPlayers = $pages->find("template=player, team=$player->team")->not("$player->id");
      $groupMembers = $teamPlayers->find("group=$player->group")->not("$player->id");
      // Each group member suffers
      foreach($groupMembers as $p) {
        $groupDeath = $pages->get("name=group-death");
        $comment = 'Group member died! ['.$player->title.']';
        // Applies after the indicated date
        if (mktime(strftime("%H,%M,%S,%m,%d,%Y", $eDate)) > mktime(0,0,0,4,10,2016)) {
          updateScore($p, $groupDeath, $comment, '', $newLinkedId, true);
        }
      }
      // Each team member suffers
      $teamMembers = $teamPlayers->find("group!=$player->group");
      foreach($teamMembers as $p) {
        $teamDeath = $pages->get("name=team-death");
        $comment = 'Team member died! ['.$player->title.']';
        // Applies after the indicated date
        if (mktime(strftime("%H,%M,%S,%m,%d,%Y", $eDate)) > mktime(0,0,0,4,10,2016)) {
          updateScore($p, $teamDeath, $comment, '', $newLinkedId, true);
        }
      }
    }
  }
  return $newLinkedId;
}

function checkDeath($player, $real=true) {
  $pages = wire('pages');
  if ($player->HP <= 0) {
      $comment = 'Player died.';
      $task = $pages->get("name=death");
      updateScore($player, $task, $comment, '', '', true);
  }
}

function checkStreak($player) {
  // Set Ambassador skill if necessary
  $streak = 0;
  $lastEvents = $player->get("name=history")->find("template=event, sort=-date")->not("task.name=donated|donation|absent, limit=10");
  foreach ($lastEvents as $e) {
    if ($e->task->HP >= 0) {
      $streak++;
    } else {
      break;
    }
  }
  return $streak;
}

function checkLevel($player) {
  // Check new level
  $threshold = ($player->level*10)+90;
  if ($player->XP >= $threshold) {
    $player->level = $player->level + 1;
    $player->XP = $player->XP - $threshold;
    $player->HP = 50;
  }
}

function displayTrendScores($player, $old) {
  $out = '<ul class="list list-inline label label-default">';
  $trendKarma = $player->karma-$old->karma;
  if ($trendKarma > 0) { $out .= '<li>+'.$trendKarma.'K.→'.$player->karma.'</li>'; }
  if ($trendKarma < 0) { $out .= '<li>'.$trendKarma.'K.→'.$player->karma.'</li>'; }
  $trendGC = $player->GC-$old->GC;
  if ($trendGC > 0) { $out .= '<li>+'.$trendGC.'GC→'.$player->GC.'</li>'; }
  if ($trendGC < 0) { $out .= '<li>'.$trendGC.'GC→'.$player->GC.'</li>'; }
  $trendLevel = $player->level-$old->level;
  if ($trendLevel > 0) { $out .= '<li>+'.$trendLevel.'lvl→'.$player->level.'</li>'; }
  if ($trendLevel < 0) { $out .= '<li>'.$trendLevel.'lvl→'.$player->level.'</li>'; }
  $trendHP = $player->HP-$old->HP;
  if ($trendHP > 0) { $out .= '<li>+'.$trendHP.'HP→'.$player->HP.'</li>'; }
  if ($trendHP < 0) { $out .= '<li>'.$trendHP.'HP→'.$player->HP.'</li>'; }
  $trendXP = $player->XP-$old->XP;
  if ($trendXP > 0) { $out .= '<li>+'.$trendXP.'XP→'.$player->XP.'</li>'; }
  if ($trendXP < 0) { $out .= '<li>'.$trendXP.'XP→'.$player->XP.'</li>'; }
  $trendEquipment = $player->equipment->count-$old->equipment->count();
  if ($trendEquipment > 0) { $out .= '<li>+'.$trendEquipment.'Eq.→'.$player->equipment->count.'</li>'; }
  if ($trendEquipment < 0) { $out .= '<li>'.$trendEquipment.'Eq.→'.$player->equipment->count.'</li>'; }
  $trendPlaces = $player->places->count()-$old->places->count();
  if ($trendPlaces > 0) { $out .= '<li>+'.$trendPlaces.'Pl.→'.$player->places->count().'</li>'; }
  if ($trendPlaces < 0) { $out .= '<li>'.$trendPlaces.'Pl.→'.$player->places->count().'</li>'; }
  $trendPeople = $player->people->count()-$old->people->count();
  if ($trendPeople > 0) { $out .= '<li>+'.$trendPeople.'Peo.→'.$player->people->count().'</li>'; }
  if ($trendPeople < 0) { $out .= '<li>'.$trendPeople.'Peo.→'.$player->people->count().'</li>'; }
  $trendDonation = $player->donation-$old->donation;
  if ($trendDonation > 0) { $out .= '<li>+'.$trendDonation.'Don.→'.$player->donation.'</li>'; }
  if ($trendDonation < 0) { $out .= '<li>'.$trendDonation.'Don.→'.$player->donation.'</li>'; }
  $trendUT = $player->underground_training-$old->underground_training;
  if ($trendUT > 0) { $out .= '<li>+'.$trendUT.'UT→'.$player->underground_training.'</li>'; }
  if ($trendUT < 0) { $out .= '<li>'.$trendUT.'UT→'.$player->underground_training.'</li>'; }
  $trendFP = $player->fighting_power-$old->fighting_power;
  if ($trendFP > 0) { $out .= '<li>+'.$trendFP.'FP→'.$player->fighting_power.'</li>'; }
  if ($trendFP < 0) { $out .= '<li>'.$trendFP.'FP→'.$player->fighting_power.'</li>'; }
  $out .= '</ul>&nbsp;&nbsp;';

  return $out;
}

function displayPlayerScores($player, $type='new') {
  if ($type == 'new') {
    $out = '<ul class="list list-inline label label-default">New scores ⇒ ';
  } else {
    $out = '<ul class="list list-inline label label-warning"> Previous scores : ';
  }
  $out .= '<li>'.$player->karma.'K.</li>';
  $out .= '<li>'.$player->GC.'GC</li>';
  $out .= '<li>'.$player->level.'lvl</li>';
  $out .= '<li>'.$player->HP.'HP</li>';
  $out .= '<li>'.$player->XP.'XP</li>';
  $out .= '<li>'.$player->places->count.'Pl.</li>';
  $out .= '<li>'.$player->people->count.'Peo.</li>';
  $out .= '<li>'.$player->equipment->count.'Eq.</li>';
  $out .= '<li>'.$player->donation.'Don.</li>';
  $out .= '<li>'.$player->underground_training.'UT</li>';
  $out .= '<li>'.$player->fighting_power.'FP</li>';
  $out .= '</ul>';

  return $out;
}

function getKarma($player) {
  // Karma calculated from all values (except GC and Donations)
  if ($player->level > 1) {
    for ($i=1; $i<=$player->level; $i++) {
      $levelKarma = $levelKarma + (($i*10)+90);
    }
    $karma = $levelKarma + $player->XP + $player->places->count*20 + $player->people->count*20 + $player->equipment->count*10 - ((50-$player->HP)*5);
  } else {
    $karma = ($player->XP + $player->places->count*20 + $player->people->count*20 + $player->equipment->count*10) - ((50-$player->HP)*5);
  }
  if ($karma < 0) { $karma = 0; }
  //echo $player->title.':'.$karma.'-';
  return $karma;
}

function groupBonus($players) {
  $nbBonus = 0;
  // Sort players by nb of places
  $players->sort('places.count');
  // Get min/max nb of places in the group
  $min = $players->first()->places->count;
  $max = $players->last()->places->count;
  if ($min == 0) { // 1 player has 0 places, so NO bonus possible
    return 0; 
  } else { // No player has 0 places, let's check if they all have 1,2,3... places
    for ($i=1; $i<=$min; $i++) {
      $nbPlaces = $players->find("places.count>=$i")->count;
      if ($nbPlaces == $players->count) {
        $nbBonus++;
      }
    }
  }
  return $nbBonus;
  /*
  foreach( $players as $player) {
    array_push($nbPlaces, $player->places->count); 
  }
  // All players have the same number of places, then +30 bonus
  $same = array_count_values($nbPlaces);
  print_r($same);
  if ( count(array_unique($nbPlaces)) == 1) {
    return 30;
  }
  */
}

function saveHistory($player, $task, $comment, $newsBoard = 0, $refPage = '', $eDate = 'unset', $linkedId = '') {
  $p = new Page();
  $p->template = 'event';
  $history = $player->child("name=history");
  if (!$history->id) { // Creation of history page if doesn't exist
    $history = new Page();
    $history->parent = $player;
    $history->template = 'archive';
    $history->name = 'history';
    $history->title = 'history';
    $history->save();
  }
  $p->parent = $history;
  // Get today's date by default, changeable in backend if needed)
  if ($eDate == 'unset') {
    date_default_timezone_set('Paris/France');
    $eDate = date('m/d/Y H:i:s', time());
  }
  $p->date = $eDate;
  $p->title = str_replace('&#039;', '\'', $task->title);
  $p->task = $task;
  if ($refPage) { $p->refPage = $refPage; }
  $p->summary = $comment;
  $p->publish = $newsBoard;
  $p->linkedId = $linkedId;
  $p->save(); 

  return $p->id;
}

function pick_question($player) {
  $rank = $player->rank->name;
  if ( $rank == '4emes' || $rank == '3emes' ) {
    if ($player->people->count() > 0) {
      $randList = ['people', 'places'];
      $randList = $randList[array_rand($randList, 1)];
    } else {
      $randList = 'places';
    }
  } else {
    $randList = 'places';
  }
  switch ($randList) {
    case 'people' : 
      $randEl = $player->people->getRandom();
      break;
    default : 
      $randEl = $player->places->getRandom();
  }
  if ($randEl) {
    $type = ['photo'];
    if ($randList == 'places') {
      switch ($player->rank->name) {
        case '5emes':
          array_push($type, 'country', 'country', 'city', 'city');
          break;
        case '4emes':
          if ($randEl->quiz->count > 0) {
            array_push($type, 'country', 'country', 'city', 'city', 'quiz', 'quiz');
          } else {
            array_push($type, 'country', 'country', 'city', 'city');
          }
          break;
        case '3emes':
          if ($randEl->quiz->count > 0) {
            array_push($type, 'country', 'city', 'quiz', 'quiz');
          } else {
            array_push($type, 'country', 'country', 'city', 'city');
          }
          break;
        default:
      }
    }
    if ($randList == 'people') {
      switch ($player->rank->name) {
        case '4emes':
          if ($randEl->quiz->count > 0) {
            array_push($type, 'country', 'nationality', 'quiz');
          } else {
            array_push($type, 'country', 'nationality');
          }
          break;
        case '3emes':
          if ($randEl->quiz->count > 0) {
            array_push($type, 'country', 'nationality', 'quiz', 'quiz');
          } else {
            array_push($type, 'country', 'nationality');
          }
          break;
        default:
      }
    }
    $rand = array_rand($type, 1);
    $quiz['type'] = $type[$rand];
    if ($randList == 'places') {
      switch($quiz['type']) {
        case 'country' :
          $quiz['question'] = 'In which <strong>'.$type[$rand].'</strong> can you see <strong>'.$randEl->title.'</strong>?';
          $quiz['answer'] = $randEl->country->title;
          break;    
        case 'city' :
          $quiz['question'] = 'In which <strong>'.$type[$rand].'</strong> can you see <strong>'.$randEl->title.'</strong>?';
          $quiz['answer'] = $randEl->city->title;
          break;    
        case 'photo' :
          $quiz['question'] = 'What <strong>monument</strong> is it?';
          $quiz['answer'] = $randEl->title. ' in '. $randEl->city->title.', '.$randEl->country->title;
          break;    
        case 'quiz' :
          $randQuiz = $randEl->quiz->getRandom();
          $quiz['question'] = $randQuiz->question;
          $quiz['answer'] = $randQuiz->answer;
          if (  strpos($quiz['question'], 'on the map') == true) {
            $quiz['type'] = 'map';
          }
          break;    
        default : $quiz['answer'] = 'Question type error!'; break;
      }
    }
    if ($randList == 'people') {
      switch($quiz['type']) {
        case 'country' :
          $quiz['question'] = 'Where is '.$randEl->title.' from ? (<strong>'.$type[$rand].'</strong></strong>)';
          $quiz['answer'] = $randEl->country->title;
          break;    
        case 'nationality' :
          $quiz['question'] = 'What\'s '.$randEl->title.'\'s <strong>'.$type[$rand].'</strong> ?';
          $quiz['answer'] = $randEl->nationality;
          break;    
        case 'photo' :
          $quiz['question'] = '<strong>Who</strong> is this ?';
          $quiz['answer'] = $randEl->title. ', '. $randEl->country->title.' ('.$randEl->nationality.')';
          break;    
        case 'quiz' :
          $randQuiz = $randEl->quiz->getRandom();
          $quiz['question'] = $randQuiz->question;
          $quiz['answer'] = $randQuiz->answer;
          break;    
        default : $quiz['answer'] = 'Question type error!'; break;
      }
    }
  }
  $quiz['id'] = $randEl->id;
  return $quiz;
}

function nbFreedomActs($team, $startDate='', $endDate='') {
  $pages = wire('pages');
  // Find current school year date
  $schoolYear = $pages->get("template=period, name=school-year");
  if ($startDate == '') { $startDate = $schoolYear->dateStart; }
  if ($endDate == '') { $endDate = $schoolYear->dateEnd; }
  $players = $pages->find("template=player, team=$team");
  $free = $pages->find("has_parent=$players, template=event, task.name=free, date>=$startDate, date<=$endDate")->count();
  return $free;
}

function teamFreeworld($team) {
  $pages = wire('pages');
  $team = $pages->get("$team");
  $allPlayers = $pages->find("template=player, team=$team->name");
  $teamRate = round(($allPlayers->count()*20)/100); // 20% of players
  // Get all possible elements
  $allElements = new pageArray();
  $allPlaces = $pages->find("template=place, name!=places");
  $allPeople = $pages->find("template=people, name!=people");
  $rank = $team->rank->name;
  if ($rank == '4emes' || $rank == '3emes') {
    $allElements->add($allPlaces);
    $allElements->add($allPeople);
  } else {
    $allElements->add($allPlaces);
  }
  // Test if teamRate is reached
  foreach( $allElements as $el) {
    if ($el->template == 'place') {
      $teamOwners = $allPlayers->find("places=$el");
    }
    if ($el->template == 'people') {
      $teamOwners = $allPlayers->find("people=$el");
    }
    if ($teamOwners->count() >= round($teamRate/2)) { $el->completed = 0.5; $el->cssClass = 'almost'; }
    if ($teamOwners->count() >= $teamRate) { $el->completed = 1; $el->cssClass = 'completed'; }
    if ($teamOwners->count() <= round($teamRate/2)) { $el->completed = 0; $el->cssClass = ''; }
    $el->teamOwners = $teamOwners;
  }
  $allElements->sort("-completed, level, title");
  return $allElements;
}

function updateTeamScores($team) {
  $pages = wire('pages');
  $team = $pages->get("$team");
  $team->freeActs = nbFreedomActs($team);
  if ($team->name != 'no-name') {
    $team->freeelements->removeAll();
    $allElements = teamFreeworld($team);
    $completed = $allElements->find("completed=1");
    $percent = round((100*$completed->count())/$allElements->count());
    foreach ($completed as $el) {
      $team->freeelements->add($el);
    }
    $team->freeworld = $percent;
    $team->of(false);
    $team->save();
  }
}

function showScores($allTeams) {
  echo '<h4 class="text-center scores">';
  echo '<ul class="list-inline">';
  if ($allTeams->count() > 1) {
    foreach($allTeams as $t) {
      if ($t->name != 'no-team') {
        echo '<li class="label label-default" data-toggle="tooltip" data-html="true" title="School year Team Activity"><h4><strong>'.$t->title.':'.$t->freeActs.'</strong> ('.$t->freeworld.'%)</strong></h4></li>';
      }
    }
  } else {
    if ($allTeams->name != 'no-team') {
      echo '<li class="label label-default" data-toggle="tooltip" data-html="true" title="School year Team Activity"><h4><strong>'.$allTeams->title.':'.$allTeams->freeActs.'</strong> ('.$allTeams->freeworld.'%)</strong></h4></li>';
    }
  }
  echo '</ul>';
  echo '</h4>';
}

function calculate_average($arr) {
  $total = 0;
  $count = count($arr); //total numbers in array
  foreach ($arr as $value) {
      $total = $total + $value; // total value of array numbers
  }
  $average = round($total/$count); // get average value
  return $average;
}

function taskCount($taskName, $allEvents) {
  $all = $allEvents->find("task.name=$taskName");
  return $all->count();
}

function checkHk($player) {
  $pages = wire('pages');
  // Get official period
  $period = $pages->get("name=admin-actions")->periods;
  // Select homework tasks over the period
  $allHk = $player->find("template=event, task.category.name=homework, date>=$period->dateStart, date<=$period->dateEnd");
  $count = 0;
  // Check last penalty's date
  $penalty = $allHk->find("task.name=penalty");
  if ($penalty->count() != 0) {
    // Count all negative homework tasks since then
    $lastDate = $penalty->last()->date;
    $negHk = $allHk->find("task.HP<0, task.name!=penalty, date>=$lastDate");
    if ($negHk->count() > 0) {
      foreach ($negHk as $event) {
        if (in_array($event->task->name, ['signature', 'homework-half-done'])) {
          $count = $count + 0.5;
        } else {
          $count++;
        }
      }
    }
  } else { // No previous penalty
    // Count all negative homework tasks
    $negHk = $allHk->find("task.name!=penalty, task.HP<0");
    if ($negHk->count() > 0) {
      foreach ($negHk as $event) {
        if (in_array($event->task->name, ['signature', 'homework-half-done'])) {
          $count = $count + 0.5;
        } else {
          $count++;
        }
      }
    }
  }
  return $count;
}

function getPosition($player, $field) {
  $pages = wire('pages');
  $pos = 1;
  switch ($field) {
    case 'karma' :
      $allPlayers = $pages->find("template=player, sort=karma, karma>0");
      break;
    case 'places' :
      $allPlayers = $pages->find("template=player, sort=-places.count, sort=-karma, places.count>0");
      break;
    case 'people' :
      $allPlayers = $pages->find("template=player, sort=-people.count, sort=-karma, people.count>0");
      break;
    case 'fighting_power' :
      $allPlayers = $pages->find('template=player, sort=-fighting_power, sort=-karma, fighting_power>0');
      break;
    case 'equipment' :
      $allPlayers = $pages->find('template=player, sort=-equipment.count, sort=-karma, equipment.count>0');
      break;
    case 'donation' :
      $allPlayers = $pages->find('template=player, sort=-donation, sort=-karma, donation>0');
      break;
    case 'underground_training' :
      $allPlayers = $pages->find('template=player, sort=-underground_training, sort=-karma, underground_training>0');
      break;
    case 'group' :
      $allPlayers = $pages->find('template=player');
      $allGroups = $pages->get('/groups')->children;
      $index = 0;
      foreach( $allGroups as $group) {
        // Find selected players
        $players = $allPlayers->find("group=$group");
        // Get rid of unused groups
        if ($players->count == 0) {
          unset($allGroups[$index]);
        }
        $index++;
      }
      $allPlayers = $allGroups;
      break;
    default : 
      $allPlayers = '';
  }
  foreach($allPlayers as $p) {
    if ($field != 'group') {
      if ($p->id === $player->id) {
        $playerPos = $pos;
      }
    } else {
      if ($p->id === $player->group->id) {
        $playerPos = $pos;
      }
    }
    $pos++;
  }
  if ($playerPos) {
    return array($playerPos, $allPlayers->count);
  } else {
    return false;
  }
}

function groupScoreBoard($limit) {
  $pages = wire('pages');
  $allPlayers = $pages->find("template=player");
  $allGroups = $pages->get("/groups")->children('sort=title');
  $outGroups = '';
  if (wire('user')->isLoggedin()) {
    $login = wire('user')->name;
    $loggedGroup = $pages->get("template=player, login=$login")->group->name;
  } else {
    $loggedGroup = "";
  }
  // Calculate groups Karma
  $index = 0;
  foreach( $allGroups as $group) {
    $group->karma = 0;
    $group->nbBonus = 0;
    
    // Find selected players
    $players = $allPlayers->find("group=$group");
    
    // Get rid of unused groups
    if ($players->count == 0) {
      unset($allGroups[$index]);
    }
    // Check for group bonus
    $group->nbBonus = groupBonus($players);
    $group->karma = $group->nbBonus*30;

    // Add individual karmas
    foreach( $players as $player) {
      $karma = getKarma($player);
      $player->karma = $karma;
      // Karma is divided by number of players in the group to be fair with smaller groups
      $groupKarma = round($karma/$players->count);
      (int) $group->karma = $group->karma + $groupKarma;
      $group->details .= $player->title.'<br />';
      $group->team = $player->team;
    }
    $index++;
  }

  // Prepare group display
  $allGroups->sort('-karma');
  if (!isset($limit) || $limit === 0) {
    $limit = $allGroups->count();
  }
  $index = 0;
  if ($allGroups->count() > 0) {
    foreach( $allGroups as $group) {
      if ($index < $limit) {
        if ($loggedGroup == $group->name) {
          $focus = "class='focus'";
        } else {
          $focus = "";
        }
        $outGroups .= '<li>';
        $outGroups .= '<span '.$focus.' data-toggle="tooltip" data-html="true" title="'.$group->details.'">';
        $outGroups .= $group->title.' ['.$group->team->title.']</span> <span class="badge">'.$group->karma.'</span>';
        // Display stars for bonus (filled star = 5 empty stars, 1 star = 1 place for each group member)
        $starsGroups = floor($group->nbBonus/5);
        if ( $starsGroups < 1) {
          for ($i=0; $i<$group->nbBonus; $i++) {
            $outGroups .= ' <span class="glyphicon glyphicon-star-empty"></span>';
          }
        } else {
          for ($i=0; $i<$starsGroups; $i++) {
            $outGroups .= ' <span class="glyphicon glyphicon-star"></span>';
          }
          $group->nbBonus = $group->nbBonus - $starsGroups*5;
          for ($i=0; $i<$group->nbBonus; $i++) {
            $outGroups .= ' <span class="glyphicon glyphicon-star-empty"></span>';
          }
        }
        $outGroups .= '</p>';
        $outGroups .= '</li>';
        $index++;
      } else {
        return $outGroups;
      }
    }
  } else {
    $outGroups .= '<li>No groups yet.</li>';
  }

  return $outGroups;
}

function isTrainingAllowed($player, $monster) {
  $utGain = utGain($monster->id, $player);
  $prevTrainings = $player->find("template=event,task.name=ut-action-v|ut-action-vv, refPage=$monster->id, sort=-date");
  if ($utGain > 0) { // Already trained
    $lastTraining = $prevTrainings->first();
    // Find # of days compared to today
    $date1 = new DateTime("today");
    $date2 = new DateTime(date("Y-m-d", $lastTraining->date));
    $interval = $date1->diff($date2);
    // Set spaced repetition according to monster's level?
    if ($monster->level >= 1 ) { // For the moment, all levels
      // Limit to 1 training session a day if prevTrainings<5
      // Limit to 1 training session a week if prevTrainings<10
      // Limit to 1 training session a month if prevTrainings>15
      if ($prevTrainings->count() > 0 && $prevTrainings->count() <= 5) {
        $spaced = 1;
      }
      if ($prevTrainings->count() > 5 && $prevTrainings->count() <= 10) {
        $spaced = 7;
      }
      if ($prevTrainings->count() > 10) {
        $spaced = 30;
      }
    }
    $monster->lastTrainingDate = $lastTraining->date;
    $monster->interval = $interval->days;
    $monster->utGain = $utGain;
    if ($interval->days < $spaced && $interval->days!= 0) {
      $monster->isTrainable = 0;
      $nbDays = $spaced-$interval->days;
      $monster->spaced = $nbDays;
    } else if ($interval->days == 0) { // Today's training
      $monster->isTrainable = 2;
      $monster->spaced = $spaced;
    } else {
      $monster->isTrainable = 1;
      $monster->spaced = 0;
    }
  } else { // Never trained
    $monster->isTrainable = 1;
    $monster->lastTrainingDate = 0;
    $monster->interval = -1;
    $monster->spaced = 0;
  }
  return $monster;
}

function isFightAllowed($player, $monster) {
  $minUt = 20; // Previous training required to be able to fight
  $spaced = 15; // 15 days between fights
  $utGain = utGain($monster->id, $player);
  $monster->utGain = $utGain;
  if ($utGain >= $minUt) { // Player has trained enough
    // Check last fight interval
    $allFights = $player->find("template=event, task.name=test-vv|test-v|test-r|test-rr, refPage=$monster, sort=-date");
    $lastFight = $allFights->first();
    if ($lastFight->id) {
      $monster->allFightsNb = $allFights->count();
      // Find # of days compared to today
      $date1 = new DateTime("today");
      $date2 = new DateTime(date("Y-m-d", $lastFight->date));
      $interval = $date1->diff($date2);
      $monster->lastFightDate = $lastFight->date;
      $monster->interval = $interval->days;
      if ($interval->days < $spaced) {
        $monster->isFightable = 0;
      } else {
        $monster->isFightable = 1;
      }
      $monster->spaced = $spaced-$interval->days;
    } else { // Never fought
      $monster->isFightable = 1;
      $monster->lastFightDate = 0;
      $monster->interval = -1;
    }
  } else {
    $monster->isFightable = 0;
    $monster->interval = -1;
  }
  return $monster;
}

function utGain($monsterId, $player, $startDate='', $endDate='') {
  if ($startDate == '') { $startDate = date('2000-01-01 00:00:00'); }
  if ($endDate == '') { $endDate = date('Y-m-d 23:59:59'); }
  $pages = wire('pages');
  $player = $pages->get($player->id);
  $prevUt = $player->find("template=event,refPage=$monsterId, date>$startDate, date<$endDate, sort=-date");
  if ($prevUt->count() > 0) {
    foreach($prevUt as $p) {
      preg_match("/\[\+([\d]+)U\.T\.\]/", $p->summary, $matches);

      if (!$matches) {
        $utGain = $utGain+1;
      } else {
        $utGain = $utGain+$matches[1];
      }
    }
  } else {
    $utGain = 0;
  }
  return $utGain;
}

function setBestPlayer($monster, $player, $utGain) {
  $pages = wire('pages');
  $player = $pages->get($player->id);
  $monster = $pages->get($monster->id);
  $monster->of(false);
  $monster->mostTrained = $player;
  $monster->best = $utGain;
  $monster->save();
}

function pma($player) {
  $out = '';
  $pages = wire('pages');
  $config = wire('config');
  $player = $pages->get($player->id);
  switch ($player->rank->name) {
    case '6emes':
      /* $levels = '1|2'; */
      $levels = '1|2|3|4|5';
      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name");
      break;
    case '5emes':
      /* $levels = '1|2'; */
      $levels = '1|2|3|4|5';
      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name");
      break;
    case '4emes':
      $levels = '1|2|3|4|5';
      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=-level, sort=name");
    case '3emes':
      $levels = '1|2|3|4|5';
      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=-level, sort=name");
    default:
      $levels = '1|2|3|4|5';
      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name");
    default:
      $levels = '1|2|3|4|5';
      $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name");
  }
  foreach($allMonsters as $m) {
    $m = isTrainingAllowed($player, $m);
    $m = isFightAllowed($player, $m);
  }
  $suggestedFights = $allMonsters->find("isFightable=0, interval=0, sort=random, limit=3"); // Pick today's fights first to check for today's mission
  if ($suggestedFights->count() < 3) { // Complete with never fought
    $n = 3-$suggestedFights->count();
    $suggestedFights->add($allMonsters->find("isFightable=1, lastFightDate=0, level=$levels, sort=random, limit=$n"));
    if ($suggestedFights->count() < 3) { // Complete with oldest
      $n = 3-$suggestedFights->count();
      $suggestedFights->add($allMonsters->find("isFightable=1, lastFightDate!=0, level=$levels, sort=lastFightDate, limit=$n"));
    }
  }
  $suggestedTrainings = $allMonsters->find("isTrainable=2, sort=name, limit=3");
  if ($suggestedTrainings->count() < 3) {
    $n = 3-$suggestedTrainings->count();
    $suggestedTrainings->add($allMonsters->find("isTrainable=1, lastTrainingDate=0, level=$levels, sort=random, limit=$n"));
    if ($suggestedTrainings->count() < 3) {
      $n = 3-$suggestedTrainings->count();
      $suggestedTrainings->add($allMonsters->find("isTrainable=1, lastTrainingDate!=0, level=$levels, sort=lastTrainingDate, limit=$n"));
    }
  }

  $out .= '<div class="row">';
  $out .= '<div class="col-sm-12">';
  $out .= '<div class="pma panel panel-primary">';
  $out .= '<div class="panel-heading">';
  $out .= '<h4><span class="glyphicon glyphicon-calendar"></span> Personal Analyzer';
  $out .= '<span class="pull-right">';
  $out .= '<span class="label label-success">Your Karma : '.$player->karma.'</span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-default" data-toggle="tooltip" title="Level">'.$player->level.'<span class="glyphicon glyphicon-signal"></span></span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-default" data-toggle="tooltip" title="XP">'.$player->XP.'<img src="'.$config->urls->templates.'img/star.png" alt="" /></span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-default" data-toggle="tooltip" title="HP">'.$player->HP.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-default" data-toggle="tooltip" title="GC">'.$player->GC.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" /></span>';
  $out .= '&nbsp;&nbsp;';
  $freeElements = $player->places->count()+$player->people->count();
  $out .= '<span class="label label-info" data-toggle="tooltip" title="Free places/people">'.$freeElements.'<img src="'.$config->urls->templates.'img/globe.png" alt="" /></span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-info" data-toggle="tooltip" title="Equipment">'.$player->equipment->count().'<span class="glyphicon glyphicon-wrench"></span></span>';
  $out .= '&nbsp;&nbsp;';
  if ($player->donation == false) {$player->donation = 0; }
  $out .= '<span class="label label-default" data-toggle="tooltip" title="Donated">'.$player->donation.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-primary" data-toggle="tooltip" title="Underground Training">'.$player->underground_training.' UT</span>';
  $out .= '&nbsp;&nbsp;';
  $out .= '<span class="label label-primary" data-toggle="tooltip" title="Fighting Power">'.$player->fighting_power.' FP</span>';
  $out .= '</span>';
  $out .= '</h4>';
  $out .= '</div>';
  $out .= '<div class="panel-body">';
  $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" title="This analyser is here to help you organize."></span>';
  $out .= '<span class="label label-primary">';
  $helmet = $pages->get("name=memory-helmet");
  if ($helmet->image) {
    $out .= '<img width="20" src="'.$helmet->image->url.'" alt="Helmet" />';
  }
  $out .= ' Underground Training</span> ⇒ ';
  if ($suggestedTrainings->count() > 0) {
    foreach($suggestedTrainings as $m) {
      if ($m->image) {
        $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getThumb('thumbnail')."\" alt=\"image\" />' src='".$m->image->getThumb('mini')."' alt='image' />";
      } else {
        $mini = '';
      }
      if ($m->isTrainable == 2) { $tag = '<span class="glyphicon glyphicon-ok-sign" title="Fought today"></span>'; $style = ' disabled="disabled" style="text-decoration: line-through;" title="Done today!"'; } else { $tag = ''; $style = 'title="'.$m->summary.'"'; }
      $out .= '<a href="'.$pages->get("name=underground-training")->url.'?id='.$m->id.'" class="btn btn-sm btn-info" '.$style.' data-toggle="tooltip" data-html="true">'.$m->title.' '.$tag.'</a> ';
    }
  } else {
    $out .= 'You have no possible training today.';


























































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































































  }
  $out .= '&nbsp;&nbsp;&nbsp;&nbsp;';
  $out .= '<span class="label label-primary">Monster Fight(s)</span> ⇒ ';
  // Test if fights have been disabled by Admin
  $lock = $pages->get("$player->team")->lockFights;
  if ($lock == 1) {
    $out .= 'The administrator has disabled this feature.';
  } else {
    if ($suggestedFights->count() > 0) {
      foreach($suggestedFights as $m) {
        if ($m->image) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getThumb('thumbnail')."\" alt=\"image\" />' src='".$m->image->getThumb('mini')."' alt='image' />";
        } else {
          $mini = '';
        }
        if ($m->interval == 0) { $tag = '<span class="glyphicon glyphicon-ok-sign" title="Fought today"></span>'; $style = ' disabled="disabled" style="text-decoration: line-through;" title="Done today!"'; } else { $tag = ''; $style = 'title="'.$m->summary.'"'; }
        $out .= '<a href="'.$m->url.'" class="btn btn-sm btn-danger" '.$style.' data-toggle="tooltip" data-html="true">'.$mini.' '.$m->title.' '.$tag.'</a> ';
      }
    } else {
      $out .= 'You have no possible fights today.';
    }
  }
  $out .= '</p>';
  $out .= '</div>';
  // Personal advice
  $out .= '<div class="panel-footer">';
  $lowTeamMembers = $pages->find("template=player, team=$player->team, HP<10");
  if ($lowTeamMembers->count() > 0) {
    $out .= '<p><span class="label label-danger">Low HP in your team <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Maybe you can help them (donation, encouraging in class...)?"></span>: </span>';
    foreach($lowTeamMembers as $p) {
      $out .= ''.$p->title.' ';
    }
    $out .= '</p>';
  }
  $helmet = $player->equipment->get("name=memory-helmet");
  if (!$helmet->id) {
    $out .= '<p><span class="glyphicon glyphicon-hand-up"></span> Think as a team : 1 member of your group should buy the Memory Helmet to give access to the group to the Underground Training Zone ! <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Agissez en équipe : 1 membre du groupe devrait débloquer le Memory Helmet pour donner l\'accès au groupe à la Underground Training Zone !"></span></p>';
  }
  if ($player->GC > 100) {
    $out .= '<p><span class="glyphicon glyphicon-hand-up"></span> You have '.$player->GC.' GC ! You should use it to increase your karma  (buy equipment, free elements, donate...) :) <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Tu as beaucoup de pièces d\'or ! Tu devrais les utiliser pour augmenter ton Karma (acheter de l\'équipement, libérer quelque chose, faire un don...)"></span></p>';
  }
  $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
  $possibleEquipment = $allEquipments->find("GC<=$player->GC, level<=$player->level, id!=$player->equipment, parent.name!=potions, sort=-parent.name, sort=name");
  $allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
  // Possible places
  $possiblePlaces = $allPlaces->find("GC<=$player->GC, level<=$player->level, id!=$player->places,sort=name");
  $possibleElements = $possiblePlaces->count();
  if ($player->team->rank->name == '4emes' || $player->team->rank->name == '3emes') {
    $allPeople = $pages->find("template=people, name!=people, sort=title");
    // Possible people
    $possiblePeople = $allPeople->find("GC<=$player->GC, level<=$player->level, id!=$player->people,sort=name");
    $possibleElements += $possiblePeople->count();
  }
  if ($possibleEquipment->count() > 0 || $possibleElements > 0) {
    $marketPlace = $pages->get("name=shop_generator")->url.$player->id;
    $out .= '<p><span class="glyphicon glyphicon-hand-up"></span> New elements are available for you at <a href="'.$marketPlace.'">the Marketplace</a> ! <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="De nouveaux éléments sont accessibles pour toi dans la Marketplace !"></span></p>';
  }
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '</div>';

  return $out;
}
?>
