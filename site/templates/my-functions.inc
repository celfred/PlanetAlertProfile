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
  // Karma calculated from all values (except GC)
  if ($player->level > 1) {
    $karma = $player->level*100 + $player->XP + $player->places->count*20 + $player->equipment->count*10 - ((50-$player->HP)*5) + $player->donation;
  } else {
    $karma = ($player->XP + $player->places->count*20 + $player->equipment->count*10) - ((50-$player->HP)*5) + $player->donation;
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

function saveHistory($player, $task, $taskComment, $newsBoard = 0) {
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
  $today = date('m/d/Y', time());
  $p->date = $today;
  // Get new values
  $newValues = ' ['.$player->level.'lvl, '.$player->HP.'HP, '.$player->XP.'XP, '.$player->GC.'GC, '.$player->places->count.'P, '.$player->equipment->count.'E]';
  $p->title = str_replace('&#039;', '\'', $task->title).$newValues;
  // Save task
  $p->task = $task;
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

?>
