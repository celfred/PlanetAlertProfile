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

function updateScore($player, $task) {
  // Task details to calculate new score
  $tXP = $task->XP;
  $tHP = $task->HP;
  $tGC = $task->GC;
  
  // Ponderate task's impact according to player's equipment
  $deltaXP = 0;
  $deltaHP = 0;
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
  }

  // Calculate player's new score
  $player->HP = $player->HP + $tHP + $deltaHP;
  $player->XP = $player->XP + $tXP + $deltaXP;
  $player->GC = $player->GC + $tGC;
  // Check GC
  if ($player->GC < 0) { $player->GC = 0; }
  // Check death
  if ($player->HP <= 0) {
    // Loose 1 level
    if ($player->level > 1) {
      $player->level = $player->level - 1;
      $player->HP = 50;
      // Loose all Gold Coins
      $player->GC = 0;
      // Loose all equipment
      foreach ($player->equipment as $equipment) {
        $equipment->delete();
      }
    } else {
      // TODO : Make an important team loss? (all players get HP loss? Extra free spots on all places?)
      // For the moment : init player scores for a new start
      $player->level = 1;
      $player->HP = 50;
      $player->GC = 0;
      $player->XP = 0;
    }
  } else {
    if ($player->HP >= 50) {
      $player->HP = 50;
    }
  }

  checkLevel($player);

  // Set karma
  $player->karma = getKarma($player);
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

function getKarma($player) {
  // Karma calculated from all values (except GC and Donations)
  if ($player->level > 1) {
    $karma = $player->level*100 + $player->XP + $player->places->count*20 + $player->equipment->count*10 - ((50-$player->HP)*5);
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

function saveHistory($player, $task, $taskComment, $newsBoard = 0, $refPage = '') {
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
  date_default_timezone_set('Paris/France');
  $today = date('m/d/Y H:i:s', time());
  $p->date = $today;
  // Get new values
  $newValues = ' ['.$player->level.'lvl, '.$player->HP.'HP, '.$player->XP.'XP, '.$player->GC.'GC, '.$player->places->count.'P, '.$player->equipment->count.'E]';
  $p->title = str_replace('&#039;', '\'', $task->title).$newValues;
  // Save task
  $p->task = $task;
  // Save refPage
  if ($refPage) {
    $p->refPage = $refPage;
  }
  // Save comment
  $p->summary = $taskComment;
  // Save publish state
  $p->publish = $newsBoard;
  $p->save(); 
}

function pick_question($player) {
  $randomPlace = $player->places->getRandom();
    if ($randomPlace) {
      if ($randomPlace->quiz->count > 0) {
        $type = ['country', 'city', 'photo', 'quiz'];
      } else {
        $type = ['country', 'city', 'photo'];
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

  return 0;

}

?>
