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
  $player->karma = 0;
  $player->HP = 50;
  $player->GC = 0;
  $player->XP = 0;
  $player->level = 1;
  $player->donation = 0;
  $player->underground_training = 0;
  $player->equipment->removeAll();
  $player->places->removeAll();
  return $player;
}
function updateScore($player, $task, $comment = '', $refPage = '', $real = true) {
  $pages = wire('pages');
  $player = $pages->get($player->id);
  $task = $pages->get($task->id);
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
          if ( $tHP + $deltaHP > 0 ) {
            $deltaHP = $tHP-1;
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
      $player->underground_training = $player->underground_training + $matches[1];
    }

    // Calculate player's new score
    if ($task->is("name!=penalty")) {
      $player->GC = (int) $player->GC + $tGC;
    } else { // Half GC if penalty
      $player->GC = (int) round($player->GC/2); // Penalty = Half GC taken away
    }
    if ($task->is("name=donation")) {
      preg_match("/\d+/", $comment, $matches);
      $player->GC = (int) $player->GC - $matches[0];
    }
    if ($task->is("name=donated")) {
      preg_match("/\d+/", $comment, $matches);
      $player->GC = (int) $player->GC + $matches[0];
    }
    $player->HP = (int) $player->HP + $tHP + $deltaHP;
    $player->XP = (int) $player->XP + $tXP + $deltaXP;
   
    // Extra action?
    if ($task->name == 'fake-donator') {
      $player->donation = 0; // Reset donation indicator
    }

    // Add items if needed
    if ($task->name == 'buy') {
      $player->GC = (int) $player->GC-$refPage->GC;
      if ($refPage->template == 'equipment' || $refPage->template == 'item') {
        switch($refPage->parent->name) {
          case 'potions' : // instant use potions?
            $player->HP = $player->HP + $refPage->HP;
            if ($player->HP > 50) {
              $player->HP = 50;
            }
            /* $player->equipment->add($refPage); */
            break;
          case 'group-items' : // Make item available to each group member
            $members = $pages->find("template=player, playerTeam=$player->playerTeam, group=$player->group")->not("$player->id");
            foreach ($members as $p) {
              $bought = $pages->get("name=bought");
              $comment = $refPage->title." [unlocked]";
              updateScore($p, $bought, $comment, $refPage, true);
            }
            break;
          default:
            $player->equipment->add($refPage);
            break;
        }
      }
    }
    // Add place if needed
    if ($task->name == 'free') {
      $player->GC = (int) $player->GC-$refPage->GC;
      if ($refPage->template == 'place') {
        $player->places->add($refPage);
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
      if ($task->is("name=buy|free|ut-action-v|ut-action-vv")) {
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
      saveHistory($player, $task, $comment, $newsBoard, $refPage, $eDate);

      // Extra action?
      if (checkHk($player) >= 3) { // Record a Hk penalty
        $penalty = $pages->get("template=task, name=penalty"); 
        $comment = 'Automatic homework penalty';
        updateScore($player, $penalty, $comment, '', true);
      }

      if ($player->HP == 0) { // Player died
        $comment = 'Player died.';
        $task = $pages->get("name=death");
        updateScore($player, $task, $comment, '', true);
      }
    }
  } else { // Player died
    if ($player->level > 1) { // Loose 1 level
      $player->level = $player->level - 1;
      $player->HP = 50;
      // Loose all Gold Coins
      $player->GC = 0;
      // Loose all equipment
      $player->equipment->removeAll();
    } else { // Init player scores for a new start
      $player->level = 1;
      $player->HP = 50;
      $player->GC = 0;
      $player->XP = 0;
      $player->equipment->removeAll();
      /* $player->places->removeAll(); */
    }
    if ($real == true) { // Save player's death if necessary
      $eDate = date('m/d/Y H:i:s', time()+1);
      $task = $pages->get("name=death");
      saveHistory($player, $task, $comment, 0, '', $eDate);
      $player->of(false);
      $player->save();
      // Team and group loss because of player's death
      $teamPlayers = $pages->find("template=player, playerTeam=$player->playerTeam")->not("$player->id");
      $groupMembers = $teamPlayers->find("group=$player->group")->not("$player->id");
      // Each group member suffers
      foreach($groupMembers as $p) {
        $groupDeath = $pages->get("name=group-death");
        $comment = 'Group member died!';
        updateScore($p, $groupDeath, $comment, '', true);
      }
      // Each team member suffers
      foreach($teamMembers as $p) {
        $teamDeath = $pages->get("name=team-death");
        $comment = 'Team member died!';
        if ($p->group != $player->group) {
          updateScore($p, $teamDeath, $comment, '', true);
        }
      }
    }
  }
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
  $trendPlaces = $player->places->count-$old->places->count();
  if ($trendPlaces > 0) { $out .= '<li>+'.$trendPlaces.'Pl.→'.$player->places->count.'</li>'; }
  if ($trendPlaces < 0) { $out .= '<li>'.$trendPlaces.'Pl.→'.$player->places->count.'</li>'; }
  $trendDonation = $player->donation-$old->donation;
  if ($trendDonationHP > 0) { $out .= '<li>+'.$trendDonationHP.'Don.→'.$player->donation.'</li>'; }
  if ($trendDonationHP < 0) { $out .= '<li>'.$trendDonationHP.'Don.→'.$player->donation.'</li>'; }
  $trendUT = $player->underground_training-$old->underground_training;
  if ($trendUT > 0) { $out .= '<li>+'.$trendUT.'UT→'.$player->underground_training.'</li>'; }
  if ($trendUT < 0) { $out .= '<li>'.$trendUT.'UT→'.$player->underground_training.'</li>'; }
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
  $out .= '<li>'.$player->equipment->count.'Eq.</li>';
  $out .= '<li>'.$player->donation.'Don.</li>';
  $out .= '<li>'.$player->underground_training.'UT</li>';
  $out .= '</ul>';

  return $out;
}

function getKarma($player) {
  // Karma calculated from all values (except GC and Donations)
  if ($player->level > 1) {
    for ($i=1; $i<=$player->level; $i++) {
      $levelKarma = $levelKarma + (($i*10)+90);
    }
    $karma = $levelKarma + $player->XP + $player->places->count*20 + $player->equipment->count*10 - ((50-$player->HP)*5);
  } else {
    $karma = ($player->XP + $player->places->count*20 + $player->equipment->count*10) - ((50-$player->HP)*5);
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

function saveHistory($player, $task, $comment, $newsBoard = 0, $refPage = '', $eDate = 'unset') {
  $p = new Page();
  $p->template = 'event';
  $history = $player->child("name=history");
  if (!$history->id) { // Creation of history page if doesn't exist
    $history = new Page();
    $history->parent = $player;
    $history->template = 'basic-page';
    $history->name = 'history';
    $history->title = 'History';
    $history->save();
  }
  $p->parent = $history;
  // Save title
  // Get today's date by default, changeable in backend if needed)
  if ($eDate == 'unset') {
    date_default_timezone_set('Paris/France');
    $eDate = date('m/d/Y H:i:s', time());
  }
  $p->date = $eDate;
  // Save title
  $p->title = str_replace('&#039;', '\'', $task->title);
  // Save task
  $p->task = $task;
  // Save refPage
  if ($refPage) {
    $p->refPage = $refPage;
  }
  // Save comment
  $p->summary = $comment;
  // Save publish state
  $p->publish = $newsBoard;
  $p->save(); 
}

function pick_question($player) {
  $randomPlace = $player->places->getRandom();
    if ($randomPlace) {
      $type = ['photo'];
      if ($randomPlace->quiz->count > 0) {
        switch ($player->rank->name) {
          case '5emes':
            array_push($type, 'country', 'country', 'city', 'city');
            break;
          case '4emes':
            array_push($type, 'country', 'country', 'city', 'city', 'quiz', 'quiz');
            break;
          case '3emes':
            array_push($type, 'country', 'city', 'quiz', 'quiz');
            break;
          default:
        }
      }
      $rand = array_rand($type, 1);
      $quiz['type'] = $type[$rand];
      switch($quiz['type']) {
        case 'country' :
          $quiz['question'] = 'In which <strong>'.$type[$rand].'</strong> can you see <strong>'.$randomPlace->title.'</strong>?';
          $quiz['answer'] = $randomPlace->country->title;
          break;    
        case 'city' :
          $quiz['question'] = 'In which <strong>'.$type[$rand].'</strong> can you see <strong>'.$randomPlace->title.'</strong>?';
          $quiz['answer'] = $randomPlace->city->title;
          break;    
        case 'photo' :
          $quiz['question'] = 'What <strong>monument</strong> is it?';
          $quiz['answer'] = $randomPlace->title. ' in '. $randomPlace->city->title.', '.$randomPlace->country->title;
          break;    
        case 'quiz' :
          $randQuiz = $randomPlace->quiz->getRandom();
          $quiz['question'] = $randQuiz->question;
          $quiz['answer'] = $randQuiz->answer;
          if (  strpos($quiz['question'], 'on the map') == true) {
            $quiz['type'] = 'map';
          }
          break;    
        default : $quiz['answer'] = 'Question type error!'; break;
      }
    }

  $quiz['id'] = $randomPlace->id;
  return $quiz;
}

function globalScore($players, $totalPlaces) {
  $totalOwners = 0;
  foreach($totalPlaces as $place) {
    $totalOwners = $totalOwners + $place->maxOwners;
  }
  $teamOwners = 0;
  foreach( $players as $player) {
    $teamOwners = $teamOwners + $player->places->count();
  }
  $globalScore = round((100*$teamOwners)/$totalOwners);

  return array($globalScore, $teamOwners, $totalOwners);
}

function placeFreedomRate($place, $allPlayers) {
  $maxOwners = $place->maxOwners;
  $placeId = $place->id;
  $teamOwners = $allPlayers->find("places=$placeId")->count();
  $rate = (100*$teamOwners)/$maxOwners;

  return $rate;
}

function display_scores($allPlayers, $allTeams, $totalPlaces) {
  // Calculate each team scores and display it
  echo '<h3 class="text-center ">';
  echo '<ul class="scores list-inline">Free world : ';
  foreach($allTeams as $team) {
    if ($team != 'No team') {
      $teamPlayers = $allPlayers->find("playerTeam=$team");
      $globalScore = globalScore($teamPlayers, $totalPlaces); $teamScore = $globalScore[0];
      $teamOwners = $globalScore[1];
      $totalOwners = $globalScore[2];

      echo '<li class="label label-default"><strong title="'.$teamOwners.'/'.$totalOwners.'">'.$team.' : '.$teamScore.'%</strong></li>';
    }
  }
  echo '</ul>';
  echo '</h3>';
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

function checkHk($player) {
  $count = 0;
  // Check last penalty's date
  $penalty = $player->find("template=event, task.name=penalty");
  if ($penalty->count() != 0) {
    // Count all negative homework tasks since then
    $lastDate = $penalty->last()->date;
    $allHk = $player->find("template=event, task.category=homework, task.HP<0, task.name!=penalty, date>$lastDate");
    if ($allHk->count() > 0) {
      foreach ($allHk as $event) {
        if (in_array($event->task->name, ['signature', 'homework-half-done'])) {
          $count = $count + 0.5;
        } else {
          $count++;
        }
      }
    }
  } else { // No previous penalty
    // Count all negative homework tasks
    $allHk = $player->find("template=event, task.category=homework, task.name!=penalty, task.HP<0");
    if ($allHk->count() > 0) {
      foreach ($allHk as $event) {
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
      $group->team = $player->playerTeam;
    }
    $index++;
  }

  // Prepare group display
  $allGroups->sort('-karma');
  if (!isset($limit) || $limit === 0) {
    $limit = $allGroups->count();
  }
  $index = 0;
  foreach( $allGroups as $group) {
    if ($index < $limit) {
      if ($loggedGroup == $group->name) {
        $focus = "class='focus'";
      } else {
        $focus = "";
      }
      $outGroups .= '<li>';
      $outGroups .= '<span '.$focus.' data-toggle="tooltip" data-html="true" title="'.$group->details.'">';
      $outGroups .= $group->title.' ['.$group->team.']</span> <span class="badge">'.$group->karma.'</span>';
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

  return $outGroups;
}

function isTrainingAllowed($player, $monster) {
  $prevUt = $player->find("template=event,refPage=$monster->id, sort=-date");

  if ($prevUt->count > 0) {
    // Find # of days compared to today
    $date1 = new DateTime("today");
    $date2 = new DateTime(date("Y-m-d", $prevUt->first->date));
    $interval = $date1->diff($date2);
  }

  // Set spaced repetition according to monster's level?
  if ($monster->level >= 1 ) {
    // Limit to 1 training session a day if prevUt<5
    // Limit to 1 training session a week if prevUt<10
    // Limit to 1 training session a month if prevUt>15
    if ($prevUt->count > 0 && $prevUt->count < 5) {
      $spaced = 1;
    }
    if ($prevUt->count > 5 && $prevUt->count < 10) {
      $spaced = 7;
    }
    if ($prevUt->count > 10) {
      $spaced = 30;
    }
  }
  if ($monster->level === 1) {
    // Limit to 1 training session a day if prevUt<5
    // Limit to 1 training session a week if prevUt<10
    // Limit to 1 training session a month if prevUt>15
    if ($prevUt->count > 0 && $prevUt->count <= 5) {
      $spaced = 1;
    }
    if ($prevUt->count > 5 && $prevUt->count <= 10) {
      $spaced = 7;
    }
    if ($prevUt->count > 10) {
      $spaced = 30;
    }
  }

  if ($interval->days < $spaced && $prevUt->count > 0) {
    $nbDays = $spaced - $interval->days;
    if ($nbDays == $spaced) {
      return $spaced;
    } else {
      if ($nbDays > 1) {
        return $nbDays;
      }
    }
  } else {
    return 0;
  }
}

function utGain($monsterId, $player) {
  $pages = wire('pages');
  $player = $pages->get($player->id);
  $prevUt = $player->find('template=event,refPage='.$monsterId.', sort=-date');
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

function setBestPlayer($monster, $player) {
  $playerUt = utGain($monster, $player);
  $bestUt = utGain($monster, $monster->mostTrained);
  if ($playerUt > $bestUt) {
    $monster->of(false);
    $monster->mostTrained = $player->id;
    $monster->save();
  }
}

?>
