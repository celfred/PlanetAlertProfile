<?php namespace ProcessWire;

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

function clearFileCache(...$pageNames) { // File cache for guests
  foreach($pageNames as $p) {
    $pageName = wire('pages')->get("name=$p");
    if ($pageName->id) {
      $cacheFile = wire('modules')->PageRender->getCacheFile($pageName);
      if($cacheFile->exists()) {
        $cacheFile->remove();
      }
    }
  }
}

function clearMarkupCache(...$cacheNames) { // Markup cache for logged in users
  foreach($cacheNames as $n) {
    wire('cache')->delete($n);
  }
}

function clearSessionCache(...$cacheNames) { // Session cache for logged in users
  foreach($cacheNames as $n) {
    wire('session')->remove($n);
  }
}

function getAllPlayers($user = false, $paginate = false) {
  $page = wire('page');
  $pages = wire('pages');
  $input = wire('input');
  if ($user) {
    if ($user->hasRole('player')) {
      $player = $pages->get("parent.name=players, template=player, login=$user->name");
      if ($input->urlSegment1 != 'no-team' && $player->team->is("name!=no-team")) {
        $allPlayers = $pages->find("parent.name=players, template=player, team=$player->team");
      } else {
        if ($paginate) {
          $allPlayers = $pages->find("template=player, team.name=no-team, limit=35, sort=-reputation");
        } else {
          $allPlayers = $pages->find("template=player, team.name=no-team, sort=-reputation");
        }
      }
    } else { // Teacher, Admin or Guest ?
      if ($input->urlSegment1 != '' && $input->urlSegment1 != 'no-team') {
        $teamName = $input->urlSegment1; 
        $allPlayers = $pages->find("parent.name=players, template=player, team.name=$teamName, sort=group"); // Limit to team players
      } else { // Order no-team list by reputation (and paginate)
        if ($user->hasRole("teacher")) {
          $allPlayers = $pages->find("parent.name=players, template=player, team.name!=test-team, team.teacher=$user");
        } else {
          $allPlayers = $pages->find("parent.name=players, template=player, team.name=no-team, limit=35, sort=-reputation");
          $pagination = $allPlayers->renderPager();
        }
      }
    }
  } else { // All Planet Alert players
    $allPlayers = $pages->find("parent.name=players, template=player");
  }
  return $allPlayers;
}

function initPlayer($player) {
  // Get previous score from last archived page
  $prevScores = $player->children()->find("template=archive, name~=history, name!=history")->sort("-date")->first();
  if ($prevScores->id) {
    $player->reputation = $prevScores->reputation;
    $player->yearlyKarma = 0;
    $player->HP = 50;
    $player->GC = $prevScores->GC;
    $player->XP = $prevScores->XP;
    $player->level = $prevScores->level;
    $player->places = $prevScores->places;
    $player->people = $prevScores->people;
    $player->equipment = $prevScores->equipment;
    $player->donation = $prevScores->donation;
    $player->underground_training = $prevScores->underground_training;
    $player->fighting_power = $prevScores->fighting_power;
    $player->hkcount = $prevScores->hkcount;
    $player->coma = 0;
    $player->streak = 0;
  } else {
    $player->reputation = 0;
    $player->yearlyKarma = 0;
    $player->HP = 50;
    $player->GC = 0;
    $player->XP = 0;
    $player->level = 1;
    $player->donation = 0;
    $player->underground_training = 0;
    $player->fighting_power = 0;
    $player->hkcount = 0;
    $player->equipment->removeAll();
    $player->places->removeAll();
    $player->people->removeAll();
    $player->coma = 0;
    $player->streak = 0;
  }
  createTmpCache($player);
  createHistoryPage($player);
  return $player;
}

function initPlayerFromCache($player, $cachedPage) {
  if ($cachedPage) {
    $player->yearlyKarma = $cachedPage->yearlyKarma;
    $player->GC = $cachedPage->GC;
    $player->level = $cachedPage->level;
    $player->reputation = $cachedPage->reputation;
    $player->HP = $cachedPage->HP;
    $player->XP = $cachedPage->XP;
    $player->places = $cachedPage->places;
    $player->people = $cachedPage->people;
    $player->equipment = $cachedPage->equipment;
    $player->donation = $cachedPage->donation;
    $player->underground_training = $cachedPage->underground_training;
    $player->fighting_power = $cachedPage->fighting_power;
    return $player;
  } else {
    return false;
  }
}

function resetPlayer($player, $previousLevel) {
  $player->HP = 50; // Full HP
  $player->GC = 0; // Lose all Gold Coins
  if ($player->level > 1) {
    $player->level = $player->level - 1; // Lose 1 level
    $threshold = getLevelThreshold($player->level);
    $player->XP = $threshold-5; // Set XP just below previous level
    $player->reputation = setReputation($player);
  } else { // Player is already on level 1
    $player->level = 1; // Stay on level 1
    if ($previousLevel == 1) { // 2nd death at level 1, set Coma state !
      $player->coma = 1;
      $player->XP = 0;
      $player->reputation = 0;
      $player->yearlyKarma = 0;
    } else {
      $player->XP = round($player->XP/2); // Cut previous XP
      $player->reputation = setReputation($player);
    }
  }
  // Lose all equipment but group-items
  foreach($player->equipment as $e) {
    if ($e->category->name != 'group-items') {
      $player->equipment->remove($e);
    }
  }
}

function deletePlayer($player) {
  $u = wire('users')->get("name=$player->login");
  wire('pages')->trash($player);
  if ($u && $u->hasRole('player')) {
    wire('users')->delete($u);
  }
}

function periodNotification($team, $teacher = false) {
  $out = '';
  $currentPeriod = $team->periods;
  if ($currentPeriod != false) {
    $dateStart = $currentPeriod->dateStart;
    $dateEnd = $currentPeriod->dateEnd;
    $now = time();
    if ($teacher) {
      $mod = $currentPeriod->periodOwner->get("singleTeacher=$teacher"); // Get personalized infos if needed
      if ($mod) {
        $mod->dateStart != '' ? $dateStart = $mod->dateStart : '';
        $mod->dateEnd != '' ? $dateEnd = $mod->dateEnd : '';
      }
    }
    if ($now < $dateStart || $now > $dateEnd) {
      $out .= '<div class="notification alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> '.__("Today's date is OUT OF the official period dates !").'</div>';
    } else {
      $dateStart = date("M. d", $dateStart);
      $dateEnd = date("M. d", $dateEnd);
      $out .= '<div class="notification alert alert-danger"><span class="">'.$currentPeriod->title.' ('.$dateStart.' → '.$dateEnd.')</span></div>';
    }
  } else {
    $out .= '<div class="notification alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> '.__("No official period set !").'</div>';
  }
  return $out;
}

function updateScore($player, $task, $real = true) {
  $task = checkModTask($task, false, $player); // Get personalized values according to logged in user
  if ($task->is("name!=death")) {
    // Task details to calculate new score
    $tXP = $task->XP;
    $tHP = $task->HP;
    $tGC = $task->GC;
    
    setStreak($player, $task);

    // Ponderate task's impact according to player's equipment
    setDelta($player, $task);

    // Calculate UT
    if ($task->is("name=ut-action-v|ut-action-vv")) {
      preg_match("/\[\+([\d]+)U\.?T\.?\]/", $task->comment, $matches);
      if ($matches[0]) {
        $player->underground_training = (int) $player->underground_training + $matches[1];
      } else {
        $player->underground_training = (int) $player->underground_training + 1;
      }
    }

    // Calculate player's new score
    $player->GC = (int) $player->GC + $tGC;
    if ($player->coma == false) {
      $player->HP = (int) $player->HP + $tHP + $player->deltaHP;
      $player->XP = (int) $player->XP + $tXP + $player->deltaXP;
    }

    taskExtraAction($player, $task, $real); // Before saving extra action required according to the task
    
    // Check GC, HP...
    if ($player->GC < 0) { $player->GC = 0; }
    if ($player->HP < 0) { $player->HP = 0; }
    if ($player->HP >= 50) { $player->HP = 50; }
    if ($player->fighting_power < 0) { $player->fighting_power = 0; }

    if ($player->coma == 0) {
      setLevel($player); // Check if new level
      $player->reputation = setReputation($player); // Set reputation (and store for cache)
      // Make a temporary event page to calculate new yearly Karma
      $tmpEvent = new Page();
      $tmpEvent->template = 'event';
      $tmpEvent->task = $task;
      if ($task->refPage) { $tmpEvent->refPage = $task->refPage; };
      // Set date for correct sorting
      setEventDate($task);
      if ($task->is("name=donation|ut-action-v|ut-action-vv")) {
        if (!once($tmpEvent, $player->id)) { // Check if event has already been recorded on the same day
          $player->yearlyKarma += eventKarma($tmpEvent);
          if ($player->yearlyKarma < 0) { $player->yearlyKarma = 0; }
        }
      } else {
        $player->yearlyKarma += eventKarma($tmpEvent);
        if ($player->yearlyKarma < 0) { $player->yearlyKarma = 0; }
      }
    }

    if ($real == true) { // Save player's new scores if necessary
      /* clearMarkupCache('cache__'.$player->team->name.'-*'); */
      clearFileCache("newsboard", "main-office", "players"); // For guests
      // Publish on Teacher's newsboard (requires teacher's work, in class)
      if ($task->is("name=inactivity|penalty|buy|free|fight-vv|bought|remove|buy-pdf")) {
        $newsBoard = 1;
      } else {
        $newsBoard = 0;
      }
      // Publish on Public newsBoard (everybody will see it)
      if ($task->is("name=buy|free|fight-v|fight-vv|ut-action-v|ut-action-vv|remove|buy-pdf|best-time")) {
        $public = 1;
        if ($player->skills->has("!name=fighter") && $task->is("name~=fight")) {
          if ($player->find("template=event, task.name~=fight")->count() == 10) {
            clearSessionCache('headMenu');
          }
        }
      } else {
        $public = 0;
      }
      if ($task->is("name=free")) {
        clearMarkupCache('cache__elements-'.$player->name);
        if ($player->places->count == 4 || $player->people->count == 4) {
          clearSessionCache('headMenu');
        }
      }
      if ($task->is("name=buy|bought") && $task->refPage->category->name == 'group-items') {
        clearSessionCache('headMenu');
      }
      // Save history
      $historyPage = saveHistory($player, $task, $newsBoard, $public);
      $player->of(false);
      $player->save();
    } else {
      $historyPage = '';
    }
  } else { // Player died
    if ($real == true) { // Save player's death if necessary
      if ($player->level == 1) { // Check if 2nd death in a row on level 1
        $prevDeath = $player->get("name=history")->get("template=event, sort=-date, task.name=death");
        if (isset($prevDeath)) {
          preg_match("/\d+/", $prevDeath->summary, $matches);
          $previousLevel = (int) $matches[0];
        } else {
          $previousLevel = 0;
        }
      } else {
        $previousLevel = $player->level;
      }
      resetPlayer($player, $previousLevel);
      $task->eDate = date('m/d/Y H:i:s', time()+60);
      if ($player->coma == 1) { $task->comment .= ' '.__("[Enter Coma state]"); }
      $historyPage = saveHistory($player, $task, 1, 0);
      $player->of(false);
      $player->save();
    } else {
      $historyPage = '';
    }
  }
  if ($historyPage != '') {
    extraActionAfterSave($player, $historyPage, $real);
    return $historyPage->id;
  }
}

function setEventDate($task) { // Set date for correct sorting
  if ($task->is("name!=penalty|team-death|group-death")) {
    if (!isset($task->eDate)) {
      $task->eDate = date('m/d/Y H:i:s', time());
    }
  } else {
    if (!isset($task->eDate)) {
      $task->eDate = date('m/d/Y H:i:s', time()+1);
    }
  }
}

function setDelta($player, $task) { // Ponderate task's impact according to player's equipment
  $player->deltaXP = 0;
  $player->deltaHP = 0;
  // Except for training and micro solo mission (core values are used)
  // TODO : Ignore fights ?
  if ($task->is("name!=ut-action-v|ut-action-vv|micro-solo-v")) {
    if ($player->equipment) {
      // Limit to the 2 best weapons
      $concerned_weapons = $player->equipment->find("category.name=weapons, sort=-XP, limit=2");
      foreach ($concerned_weapons as $item) {
        $player->deltaXP += $item->XP;
      }
      // Limit to 1 best protection
      $concerned_protection = $player->equipment->find("category.name=protections, sort=-HP, limit=1");
      foreach ($concerned_protection as $item) {
        $player->deltaHP += $item->HP;
      }
      if ($task->HP < 0) { // Negative task, loss of 1 minimum whatever the equipment
        if ($task->HP + $player->deltaHP >= 0 ) {
          $player->deltaHP = abs($task->HP) - 1;
        }
        // Get rid of weapons' bonus
        $player->deltaXP = 0;
      } else { // Positive task, get rid of protections' bonus
        $player->deltaHP = 0;
      }
      if ($task->XP == 0) { // Task provides no XP gain, disable weapons
        $player->deltaXP = 0;
      }
    }
  }
}

function taskExtraAction($player, $task, $real) {
  $pages = wire('pages');
  if ($task->is("name=no-homework")) {
    // If a penalty is still not signed, don't add to hkcount
    $penalty = $pages->get("has_parent=$player, template=event, publish=1, task.name=penalty, sort=-date");
    if (!($penalty->id)) {
      $player->hkcount++;
    }
    return;
  }
  if ($task->is("name=homework-half-done|signature")) {
    // If a penalty is still not signed, don't add to hkcount
    $penalty = $pages->get("has_parent=$player, template=event, publish=1, task.name=penalty, sort=-date");
    if (!($penalty->id)) {
      $player->hkcount += 0.5;
    }
    return;
  }
  if ($task->is("name=battle-rr|fight-rr")) {
    $player->fighting_power = $player->fighting_power-2;
    if ($task->is("name=battle-rr")) { // No death because of battles
      if ($player->HP<=0) { $player->HP = 1; }
    }
  }
  if ($task->is("name=corrupted-identity")) {
    // No death because of corrupted identity
    if ($player->HP<=0) { $player->HP = 1; }
  }
  if ($task->is("name=battle-r|fight-r|wrong-invasion")) {
    $player->fighting_power = $player->fighting_power-1;
    if ($task->is("name=battle-r")) { // No death because of battles
      if ($player->HP<=0) { $player->HP = 1; }
    }
  }
  if ($task->is("name=battle-v|fight-v|right-invasion|best-time")) {
    $player->fighting_power = $player->fighting_power+1;
  }
  if ($task->is("name=battle-vv|fight-vv")) {
    $player->fighting_power = $player->fighting_power+2;
  }
  if ($player->skills->get("name=fighter") == NULL && $task->is("name~=fight")) {
    if ($player->find("template=event, task.name~=fight")->count() > 9) {
      setFighter($player);
    }
    return;
  }
  if ($task->is("name~=best-time")) { // Set Master skill if needed
    checkMaster($player);
    return;
  }
  if ($task->is("name=penalty")) {
    $player->GC = (int) round($player->GC/2); // Half GC if penalty
    $player->hkcount = 0; // Reset current hkcount
    return;
  }
  if ($task->is("name=group-death|team-death")) { // No death because of others
    if ($player->HP<=0) { $player->HP = 1; }
    return;
  }
  if ($task->is("name=inactivity")) { // Lose all GC
    $player->GC = 0;
    return;
  }
  if ($task->is("name=donation")) {
    preg_match("/\d+/", $task->comment, $matches);
    $player->GC = (int) $player->GC - $matches[0];
    $player->donation = (int) $player->donation + $matches[0];
    return;
  }
  if ($task->is("name=donated")) {
    preg_match("/\d+/", $task->comment, $matches);
    $player->GC = (int) $player->GC + $matches[0];
    return;
  }
  if ($task->is("name=fake-donator")) { // Reset donation indicator
    $player->donation = 0;
  }
  if ($task->is("name=bought")) { // Add equipment
    if ($task->refPage) {
      $player->equipment->add($task->refPage);
    }
    return;
  }
  if ($task->is("name=remove")) { // Remove element
    if ($task->refPage != false && $task->refPage->template == 'place') {
      $player->places->remove($task->refPage);
    }
    if ($task->refPage != false && $task->refPage->template == 'people') {
      $player->people->remove($task->refPage);
    }
    return;
  }
  if ($task->is("name=free")) { // Add element
    if ($task->refPage) {
      // Check for a discount
      if ($task->linkedId) {
        $discount = $pages->get($task->linkedId)->name;
        $newPrice = round($task->refPage->GC-($task->refPage->GC*($discount/100)));
      } else {
        $newPrice = $task->refPage->GC;
      }
      $player->GC = (int) $player->GC-$newPrice;
      // Add place if needed
      if ($task->refPage->is("template=place")) {
        $player->places->add($task->refPage);
      }
      // Add people if needed
      if ($task->refPage->is("template=people")) {
        $player->people->add($task->refPage);
      }
    }
    return;
  } 
  if ($task->is("name=buy")) { // Add equipment
    if ($task->refPage) {
      // Check for a discount
      if ($task->linkedId) {
        $discount = $pages->get($task->linkedId)->name;
        $newPrice = round($task->refPage->GC-($task->refPage->GC*($discount/100)));
      } else {
        $newPrice = $task->refPage->GC;
      }
      $player->GC = (int) $player->GC-$newPrice;
      // Add item if needed
      if ($task->refPage->is("template=equipment|item")) {
        switch($task->refPage->parent->name) {
          case 'potions' : // instant use potions?
            $player->HP = $player->HP + $task->refPage->HP;
            if ($player->HP > 50) {
              $player->HP = 50;
            }
            if ($task->refPage->is("name=health-potion") && $player->coma == 1) {
              $player->coma = 0;
            }
            if ($task->refPage->is("name!=health-potion")) {
              if ($task->refPage->is("name=memory-potion")) {
                // Adapt comment to show index
                $index = $player->get("name=history")->find("template=event, refPage.name=memory-potion")->count();
                $newIndex = $index++;
                $headTeacher = getHeadTeacher($player);
                $profilePage = getProfile($headTeacher);
                $newText = $profilePage->memoryPotionTexts->eq($newIndex);
                if ($newText) {
                  $task->linkedId = $newText->id;
                  $task->comment = __("Memory potion").' ('.$newText->title.')';
                  // TODO : Check if new text has not already been memorized in case order was modified ?
                } else { // Last text ?
                  $task->linkedId = 0;
                  $task->comment = __("Memory potion : No more texts.");
                }
                // TODO : Manage text index through repeater depending on teacher ???
                /* $task->linkedId = $newIndex; */
                /* $task->comment = 'Memory potion (text '.$newIndex.')'; */
              } else {
                $task->linkedId = 0;
                if ($task->refPage->is("name=recovering-potion")) {
                  $player->equipment->add($task->refPage);
                }
              }
            }
            break;
          default:
            $player->equipment->add($task->refPage);
            break;
        }
      }
    }
    return;
  }
}

function addUsable($player, $historyPage, $real) {
  $player->of(false);
  if ($real == true) {
    $player->usabledItems->add($historyPage->refPage->id);
    $player->save();
  }
}

function savePendingLesson($player, $task) {
  $pages = wire('pages');
  setEventDate($task);
  $book = $pages->get("name=book-knowledge");
  $book->of(false);
  $pending = $book->pending->getNew();
  $pending->player = $player;
  $pending->task = $task;
  $pending->refPage = $task->refPage;
  $pending->date = $task->eDate;
  $pending->save();
  $book->pending->add($pending);
  $book->save();
}

function addGroupItem($player, $historyPage, $real) {
  $pages = wire('pages');
  $player->equipment->add($historyPage->refPage);
  $members = $pages->find("team=$player->team, group=$player->group, id!=$player->id");
  foreach ($members as $m) {
    $task = $pages->get("name=bought");
    $task->comment = $historyPage->refPage->title." [unlocked]";
    $task->refPage = $historyPage->refPage;
    $task->linkedId = $historyPage->id;
    updateScore($m, $task, $real);
  }
}

function extraActionAfterSave($player, $historyPage, $real) { // Watch out : recorded with today's date (but trigeering event's linkedId
  $pages = wire('pages');
  if ($player->hkcount >= 3 && $historyPage->task->is("name=no-homework|homework-half-done|signature|material")) {  // Record a Hk penalty
    $task = $pages->get("template=task, name=penalty"); 
    $task->comment = 'Automatic homework penalty';
    $task->refPage = false;
    $task->linkedId = $historyPage->id;
    updateScore($player, $task, $real);
  }
  if ($historyPage->task->is("name=donation")) {  // Record the linked 'Donated' event
    $task = $pages->get("name=donated");
    preg_match("/\d+/", $historyPage->summary, $matches);
    $amount = $matches[0];
    $task->comment = $amount. ' GC received from '.$player->title.' ['.$player->team->title.']';
    $task->refPage = $player;
    $receiverPlayer = $historyPage->refPage;
    $task->linkedId = $historyPage->id;
    updateScore($receiverPlayer, $task, $real);
  }
  if ($historyPage->task->is("name=wrong-invasion")) { // Remove element if 3rd wrong invasion
    $wrongInvasions = $player->get("name=history")->find("template=event, task.name=wrong-invasion, refPage=$historyPage->refPage, sort=-date"); // Limited to current school year
    // Limit to events AFTER the last 'Free' action on the oarticular element (in case element has been freed again)
    $lastFree = $player->get("name=history")->get("task.name=free, refPage=$historyPage->refPage, sort=-date");
    if ($lastFree->id) {
      $wrongInvasions = $wrongInvasions->find("date>=$lastFree->date");
    }
    if ($wrongInvasions->count() >= 3) {
      $task = $pages->get("template=task, name=remove");
      $task->comment = __('3rd wrong invasions on ').$historyPage->refPage->title;
      $task->refPage = $historyPage->refPage;
      $task->linkedId = $historyPage->id;
      updateScore($player, $task, $real);
    }
  }
  if ($historyPage->task->is("name=buy")) {
    if ($historyPage->refPage->parent->is("name=group-items") && isset($player->group->id)) { // Save group items for all group members 
      addGroupItem($player, $historyPage, $real);
    }
    if ($historyPage->refPage->parent->is("name=potions") && $historyPage->refPage->is("name!=health-potion|recovering-potion")) {
      // Add item to player's usabledItems
      addUsable($player, $historyPage, $real);
    }
  }
  // Save new team scores (for cache) if necessary
  if ($real == true) {
    if ($historyPage->task->is("name~=fight|ut-action")) { // Update tmpCache
      updateCache($player, $historyPage);
    }
    if ($historyPage->task->is("name=free|remove")) { // Team score has changed, save it
      updateTeamScores($player->team, $historyPage->refPage);
    }
    if (checkNewPageCache($player)) {
      setCache($player->id, false);
    }
  }
}

function checkDeath($player, $real=true) {
  $pages = wire('pages');
  if ($player->HP <= 0) {
    $task = $pages->get("name=death");
    $task->comment = sprintf(__('Player died. [former level:%d]'), $player->level);
    $task->refPage = false;
    $task->linkedId = false;
    $historyPageId = updateScore($player, $task, $real);
    if ($real == true) {
      if ($player->coma == 0 && $historyPageId != 0) { // Player died and not in coma
        updateTeamDeath($player, $historyPageId); // Record death for others
      }
    }
  }
}

function updateTeamDeath($player, $linkedId) {
  $pages = wire('pages');
  $teamPlayers = $pages->find("template=player, team=$player->team, id!=$player->id");
  $teamDeath = $pages->get("name=team-death");
  $teamDeath->comment = sprintf(__('Team member died! [%s]'), $player->title);
  $teamDeath->refPage = $player;
  $teamDeath->linkedId = $linkedId;
  if ($player->group->id) {
    $groupMembers = $teamPlayers->find("group=$player->group");
    $groupDeath = $pages->get("name=group-death");
    $groupDeath->comment = sprintf(__('Group member died! [%s]'), $player->title);
    $groupDeath->refPage = $player;
    $groupDeath->linkedId = $linkedId;
    // Each group member suffers
    foreach($groupMembers as $p) {
      updateScore($p, $groupDeath, true);
      // No need to checkDeath because dying because of others is impossible
    }
    // Each team member suffers
    $teamMembers = $teamPlayers->find("group!=$player->group");
  } else {
    // All team members suffer
    $teamMembers = $teamPlayers;
  }
  foreach($teamMembers as $p) {
    updateScore($p, $teamDeath, true);
    // No need to checkDeath because dying because of others is impossible
  }
}
function getLevelThreshold($level) {
  if ($level <= 4) {
    $delta = 40+($level*10);
  } else {
    $delta = 90;
  }
  $threshold = ($level*10)+$delta;
  return $threshold;
}

function setStreak($player, $task) {
  $pages = wire('pages');
  if ($task->is("name!=donation|donated|absent")) {
    if ($task->HP>=0 && $task->is("name!=inactivity")) {
      $player->streak++;
    } else {
      $player->streak = 0;
    }
  }
  if ($player->coma == 0) {
    // Set Ambassador skill if necessary
    $role = $pages->get("name=ambassador");
    if ($player->streak >= 10) {
      $player->skills->add($role);
    } else {
      $player->skills->remove($role);
    }
  }
}

function checkStreak($player) {
  $streak = 0;
  $lastEvents = $player->get("name=history")->find("template=event, sort=-date, limit=10, task.name!=donated|donation|absent");
  foreach ($lastEvents as $e) {
    if ($e->task->HP >= 0 && $e->task->is("name!=inactivity")) {
      $streak++;
    } else {
      break;
    }
  }
  return $streak;
}

function clearAdminTableCache(HookEvent $e) {
  $p = wire('pages')->get("name=admintable");
  $cacheFile = wire('modules')->PageRender->getCacheFile($p);
  if($cacheFile->exists()) {
    $cacheFile->remove();
  }
}

function randomSpecial(HookEvent $e) { // Random monsters are hidden if player doesn't have the Visualizer
  $pages = wire('pages');
  $allMonsters = $pages->find("template=exercise");
  foreach ($allMonsters as $m) { // Reset all specials
    if ($m->special === 1) { 
      $m->of(false);
      $m->special = 0;
      $m->save();
    }
  }
  $nb = round($allMonsters->count()/2); // 50% of monsters
  $specialMonsters = $allMonsters->findRandom($nb);
  foreach($specialMonsters as $m) {
    if ($m->alwaysDetected == 0) {
      $m->of(false);
      $m->special = 1;
      $m->save();
    }
  }
}

function cleanTest(HookEvent $e) { // Empty /tmp subtree
  $pages = wire('pages');
  $testPlayers = $pages->find("parent.name=players, team.name=test-team");
  foreach ($testPlayers as $p) {
    $p->of(false);
    if ($p->fight_request != 0) {
      $p->fight_request = 0;
      $p->save();
    }
  }
}
function emptyTmp(HookEvent $e) { // Empty /tmp subtree
  $pages = wire('pages');
  $allTmp = $pages->get("name=tmp")->children();
  foreach ($allTmp as $p) {
    $p->of(false);
    $pages->trash($p);
  }
}

function checkActivity(HookEvent $e) {
  $pages = wire('pages');
  $refPage = $pages->get("name=inactivity");
  $allPlayers = $pages->findMany("parent.name=players, template=player, team.name=no-team, name!=test"); // Limit to no-team players, avoid 'test'
  foreach ($allPlayers as $player) {
    $lastEvent = lastEvent($player);
    if ($lastEvent) {
      $lastActivityCount = daysFromToday($lastEvent);
    } else {
      $lastActivityCount = -1;
    }
    if ($lastActivityCount > 30) {
      if ($lastEvent->name != 'inactivity') {
        $task = $refPage;
        $task->comment = sprintf(__('%d days of inactivity.'), $lastActivityCount);
        $task->refPage = false;
        $task->linkedId = false;
        updateScore($player, $task, true);
        // No need to checkDeath, inactivity can't cause death
      }
      if ($lastActivityCount > 365 ) { // 1 year of inactivity, delete player/user
        if ($player->rank->index > 9) { // Delete only out-of-middle-school players
          deletePlayer($player);
        }
      }
    }
  }
}

function lastEvent($player) {
  $historyPage = false;
  $lastEvent = false;
  $historyPages = $player->children("name~=history, sort=date");
  if ($historyPages->count() == 1 && $historyPages->hasChildren()) {
    $historyPage = $historyPages->last();
  }
  if ($historyPage) {
    $lastEvent = $historyPage->children()->last();
    if (!$lastEvent) { // No events in historyPage, go back one history page
      if ($historyPages->getPrev($historyPage)->hasChildren()) {
        $lastEvent = $historyPages->getPrev($historyPage)->children()->last();
      } else {
        $lastEvent = false;
      }
    }
  }
  if ($lastEvent) {
    return $lastEvent;
  } else {
    return false;
  }
}

function daysFromToday($event) {
  if ($event) {
    // Find # of days compared to today
    $date1 = new \DateTime("today");
    $date2 = new \DateTime(date("Y-m-d", $event->created));
    $interval = $date1->diff($date2);
    return $interval->days;
  } else {
    return -1;
  }
}

function setLevel($player) {
  // Check new level
  $threshold = getLevelThreshold($player->level);
  if ($player->XP >= $threshold) {
    $player->level = $player->level + 1;
    $player->XP = $player->XP - $threshold;
    $player->HP = 50;
  }
}

function displayTrendScores($player, $old) {
  $out = '<ul class="list list-inline label label-default">';
  $trendYearlyKarma = $player->yearlyKarma-$old->yearlyKarma;
  if ($trendYearlyKarma > 0) { $out .= '<li>+'.$trendYearlyKarma.'K.→'.$player->yearlyKarma.'</li>'; }
  if ($trendYearlyKarma < 0) { $out .= '<li>'.$trendYearlyKarma.'K.→'.$player->yearlyKarma.'</li>'; }
  $trendGC = $player->GC-$old->GC;
  if ($trendGC > 0) { $out .= '<li>+'.$trendGC.'GC→'.$player->GC.'</li>'; }
  if ($trendGC < 0) { $out .= '<li>'.$trendGC.'GC→'.$player->GC.'</li>'; }
  $trendLevel = $player->level-$old->level;
  if ($trendLevel > 0) { $out .= '<li>+'.$trendLevel.'lvl→'.$player->level.'</li>'; }
  if ($trendLevel < 0) { $out .= '<li>'.$trendLevel.'lvl→'.$player->level.'</li>'; }
  $trendKarma = $player->reputation-$old->karma;
  if ($trendKarma > 0) { $out .= '<li>+'.$trendKarma.'R.→'.$player->reputation.'</li>'; }
  if ($trendKarma < 0) { $out .= '<li>'.$trendKarma.'R.→'.$player->reputation.'</li>'; }
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

function displayPlayerScores($cachePage) {
  $out = '<li>'.$cachePage->yearlyKarma.'K.</li>';
  $out .= '<li>'.$cachePage->GC.'GC</li>';
  $out .= '<li>'.$cachePage->level.'lvl</li>';
  $out .= '<li>'.$cachePage->reputation.'R.</li>';
  $out .= '<li>'.$cachePage->HP.'HP</li>';
  $out .= '<li>'.$cachePage->XP.'XP</li>';
  $out .= '<li>'.$cachePage->places->count.'Pl.</li>';
  $out .= '<li>'.$cachePage->people->count.'Peo.</li>';
  $out .= '<li>'.$cachePage->equipment->count.'Eq.</li>';
  $out .= '<li>'.$cachePage->donation.'Don.</li>';
  $out .= '<li>'.$cachePage->underground_training.'UT</li>';
  $out .= '<li>'.$cachePage->fighting_power.'FP</li>';
  return $out;
}

function setGroupCaptain($playerId, $real = true) {
  $pages = wire('pages');
  $player = $pages->get($playerId);
  if (isset($player->group) && $player->group != '') {
    $players = $pages->find("parent.name=players, template=player, team=$player->team, group=$player->group");
    $captain = $players->sort('-yearlyKarma')->first();
    if (!$captain->skills->has("skills.name=captain")) { // Else captain has not changed
      $role = $pages->get("parent.name=skills, name=captain");
      // Remove old captain
      $oldCaptain = $players->get("skills.name=captain");
      if ($oldCaptain->id) {
        $oldCaptain->skills->remove($role);
        if ($real == true) {
          $oldCaptain->of(false);
          $oldCaptain->save();
        }
      }
      // Set new captain
      $captain->skills->add($role);
      if ($real == true) {
        $captain->of(false);
        $captain->save();
      }
    }
  }
}
function setTeamCaptains($team, $real = true) {
  if ($team->is("name!=no-team")) { // Skip no-team players
    $pages = wire('pages');
    $players = $pages->find("template=player, team=$team");
    // Remove old captains
    $role = $pages->get("parent.name=skills, name=captain");
    $groups = new PageArray();
    foreach($players as $p) {
      $p->of(false);
      $p->skills->remove($role);
      $groups->add($p->group);
      if ($real == true) {
        $p->save();
      }
    }
    foreach($groups as $group) {
      // Find group captain
      $captain = $players->sort("-yearlyKarma")->findOne("group=$group");
      if (isset($captain->id)) {
        $captain->skills->add($role);
        if ($real == true) {
          $captain->save();
        }
      }
    }
  }
}

function checkMaster($player) {
  $pages = wire('pages');
  $role = $pages->get("name=master");
  $nbBestTimes = $pages->count("template=exercise, bestTimePlayerId=$player->id");
  if ($nbBestTimes > 0) {
    $player->skills->add($role);
  } else {
    $player->skills->remove($role);
  }
}

function setMaster($player) {
  $pages = wire('pages');
  $role = $pages->get("parent.name=skills, name=master");
  $player->of(false);
  $player->skills->add($role);
  $player->save();
}

function setFighters($team, $real = true) {
  $pages = wire('pages');
  $players = $pages->find("template=player, team=$team");
  $role = $pages->get("parent.name=skills, name=fighter");
  foreach($players as $p) {
    $p->of(false);
    $p->skills->remove($role);
    $nbFights = $p->find("template=event, task.name~=fight")->count();
    if ($nbFights >= 10) {
      $p->skills->add($role);
    }
    if ($real == true) {
      $p->save();
    }
  }
}
function setFighter($player) {
  $pages = wire('pages');
  $role = $pages->get("parent.name=skills, name=fighter");
  $player->skills->add($role);
}

function setReputation($player) { // Reputation calculated from all values (except GC, Donations and HP)
  $free = 20;
  $equipment = 10;
  $reputation = (($player->places->count+$player->people->count)*$free) + ($player->equipment->count*$equipment) + $player->XP;
  $levelReputation = 0;
  if ($player->level > 1) { // Add completed levels XP
    for ($i=1; $i<$player->level; $i++) {
      $levelReputation = $levelReputation + getLevelThreshold($i);
    }
    $reputation += $levelReputation;
  }
  if ($reputation < 0 || $player->coma == 1) { $reputation = 0; }
  return $reputation;
}

function setYearlyKarma($player) {
  // Yearly Karma calculated from current history
  $history = $player->child("name=history");
  $karma = 0;
  foreach ($history->children() as $e) {
    $karma += eventKarma($e);
    if ($karma < 0) { $karma = 0; }
  }
  if ($karma < 0 || $player->coma == 1) { $karma = 0; }
  return $karma;
}

function once($e, $playerId) {
  $pages = wire('pages');
  if ($e->task->name == 'donation') {
    $eDate = date("d m Y", strtotime($e->task->eDate));
    // Check if task is already recorded on the same day
    $already = $pages->get("id=$playerId")->get("name=history")->get("template=event,task=$e->task, sort=-date");
    if ($already->id) {
      $lastDate = date("d m Y", $already->date);
      if ($eDate == $lastDate) {
        return true;
      }
    }
  }
  return false;
}

function eventKarma($e) { // Calculate karma evolution according to recorded event
  $karma = 0;
  if ($e->task) {
    if ($e->task->is("name=free")) { // Free action
      $karma += 8;
      $karma += $e->refPage->level;
    } else if ($e->task->is("name=buy")) { // Buy action
      $karma += 4;
      $karma += $e->refPage->level;
    } else if ($e->task->is("name=donation|bought")) { // 'Special' positive actions
      $karma += 1;
    } else if ($e->task->is("name=ut-action-v|donated|buy-pdf")) { // 'Neutralized' actions
      $karma += 0;
    } else if ($e->task->is("name=remove|death")) { // 'Special' negative actions
      $karma -= 5;
    } else if ($e->task->HP>0 || $e->task->GC>0 || $e->task->XP>0) { // Positive action
      $karma += 2;
      if ($e->task->HP>=5 || $e->task->GC>=5 || $e->task->XP>=5) {
        $karma += 2;
      } else {
        $karma += 0;
      }
    } else if ($e->task->HP<0 || $e->task->GC<0 || $e->task->XP<0) { // Negative action
      $karma -= 2;
      if ($e->task->HP<=-5 || $e->task->GC<=-5 || $e->task->XP<=-5) {
        $karma -= 2;
      } else {
        $karma -= 0;
      }
    }
  }
  return $karma;
}

function groupBonus($players) {
  $nbBonus = 0;
  // Sort players by nb of places
  $players->sort('nbEl');
  // Get min/max nb of places in the group
  $min = $players->first()->nbEl;
  $max = $players->last()->nbEl;
  if ($min == 0) { // 1 player has 0 places, so NO bonus possible
    return 0; 
  } else { // No player has 0 places, let's check if they all have 1,2,3... places
    for ($i=1; $i<=$min; $i++) {
      $nbEl = $players->find("nbEl>=$i")->count;
      if ($nbEl == $players->count) {
        $nbBonus++;
      }
    }
  }
  return $nbBonus;
}

function saveHistory($player, $task, $newsBoard = 0, $public = 0) {
  createHistoryPage($player);
  $history = $player->child("name=history");
  $p = new Page();
  $p->of(false);
  $p->template = 'event';
  $p->parent = $history;
  $p->date = $task->eDate;
  $p->title = str_replace('&#039;', '\'', $task->title);
  $p->task = $task;
  if ($task->refPage) { $p->refPage = $task->refPage; }
  // Tick inClass checkbox if classActivity Team option is on
  // For UT and FP actions only
  if (($player->team->classActivity == 1 && $task->is("name~=fight|ut-action")) || $task->inClass == 1) {
    $p->inClass = 1;
  } else {
    $p->inClass = 0;
  }
  $p->summary = $task->comment;
  $p->publish = $newsBoard;
  $p->public = $public;
  $p->linkedId = $task->linkedId;
  $p->save(); 
  // Clear session, markup or file caches
  /* if ($p->task->is("name=free")) { */
  /* } */
  return $p;
}

function checkNewPageCache($player) {
  $limit=50;
  $nbEvents = $player->get("name=history")->numChildren();
  $toCache = ceil($nbEvents/$limit)-1; // Skip last page
  if ($toCache > 0) {
    $nbCached = $player->get("name=history")->tmpScores->count();
    if ($toCache != $nbCached) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function setCache($playerId, $force = false) {
  $pages = wire('pages');
  $player = $pages->get($playerId);
  $allEvents = $player->get("name=history")->children()->sort("date");
  $limit = 50;
  $curPage = 1;
  $nbEvents = 0;
  $nbAllEvents = $allEvents->count();
  $toCache = ceil($nbAllEvents/$limit)-1;
  if ($toCache > 0) {
    if ($force) {
      $player = initPlayer($player);
      $curPage = 1;
    } else { // Only add new cache page (if possible)
      $allCached = $player->get("name=history")->tmpScores;
      $nbCached = $allCached->count();
      if ($toCache == ($nbCached+1) && $nbCached > 0) {
        $player = initPlayerFromCache($player, $allCached->last());
        $nbPrevious = $nbCached*$limit;
        $curPage = $toCache;
        $allEvents->filter("start=$nbPrevious, limit=$limit"); // Limit to 50 events starting after previous cached events
      } else { // Error detected, force cache regeneration
        setCache($playerId, true);
        return false;
      }
    }
    foreach($allEvents as $e) {
      $e->task->comment = trim($e->summary);
      $e->task->refPage = $e->refPage;
      $e->task->linkedId = $e->linkedId;
      updateScore($player, $e->task, false);
      // Save new cache for each page
      $nbEvents++;
      if ($nbEvents == $limit) {
        saveScoresForCache($player, $curPage);
        $curPage++;
        $nbEvents = 0;
      }
    }
  }
}

function pick_question($player) {
  $sanitizer = wire('sanitizer');
  if ($player->team && $player->team->rank) {
    $rank = $player->team->rank->index;
  } else if ($player->rank) {
    $rank = $player->rank->index;
  } else {
    $rank = 6; // 6emes by default
  }
  if ($rank >= 8) {
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
    if ($randList == 'places') {
      switch ($rank) {
        case '6':
          $type = ['photo'];
          break;
        case '7':
          $type = ['photo', 'map'];
          break;
        case '8':
          if ($randEl->quiz->count > 0) { // Add questions if they exists in backend
            $type = ['photo', 'map', 'quiz'];
          } else {
            $type = ['photo', 'map'];
          }
          break;
        case '9':
          if ($randEl->quiz->count > 0) { // Add questions if they exists in backend
            $type = ['photo', 'map', 'map', 'quiz', 'quiz'];
          } else {
            $type = ['photo', 'map', 'map'];
          }
          break;
        default:
          $type = ['photo'];
      }
    }
    if ($randList == 'people') {
      switch ($rank) {
        case '8':
          if ($randEl->quiz->count > 0) { // Add questions if they exists in backend
            $type = ['country', 'nationality', 'quiz'];
          } else {
            $type = ['country', 'nationality'];
          }
          break;
        case '9':
          if ($randEl->quiz->count > 0) { // Add questions if they exists in backend
            $type = ['country', 'nationality', 'quiz', 'quiz'];
          } else {
            $type = ['country', 'nationality'];
          }
          break;
        default:
          $type = ['country', 'nationality'];
      }
    }
    $rand = array_rand($type, 1);
    $quiz['type'] = $type[$rand];
    if ($randList == 'places') {
      switch($quiz['type']) {
        /* case 'country' : */
        /*   $quiz['question'] = $sanitizer->entitiesMarkdown(sprintf(__('In which **country** can you see **%s** ?'), $randEl->title)); */
        /*   $quiz['answer'] = sprintf(__('In %1$s (in the city of %2$s)'), $randEl->country->title, $randEl->city->title); */
        /*   break; */
        /* case 'city' : */
        /*   $quiz['question'] = $sanitizer->entitiesMarkdown(sprintf(__('In which **city** can you see **%s** ?'), $randEl->title)); */
        /*   $quiz['answer'] = sprintf(__('In %1$s (in %2$s)'), $randEl->city->title, $randEl->country->title); */
        /*   break; */
        case 'photo' :
          $quiz['question'] = $sanitizer->entitiesMarkdown(__('What **monument** is it ? **Where** is it ?'));
          if ($randEl->city->id) {
            $quiz['answer'] = sprintf(__('%1$s (in %2$s, in %3$s)'), $randEl->title, $randEl->city->title, $randEl->country->title);
          } else {
            $quiz['answer'] = sprintf(__('%1$s (in %3$s)'), $randEl->title, $randEl->country->title);
          }
          break;
        case 'quiz' :
          $randQuiz = $randEl->quiz->getRandom();
          $quiz['question'] = $randQuiz->question;
          $quiz['answer'] = $randQuiz->answer;
          if (strpos($quiz['question'], 'on the map') == true || strpos($quiz['question'], 'sur la carte') == true) {
            $quiz['type'] = 'map';
          }
          break;
        case 'map' :
          $elTitle = $randEl->title;
          $quiz['question'] = $sanitizer->entitiesMarkdown(__('What **monument** is it ?'));
          /* $quiz['question'] .= ' ['.sprintf(__('Starts with %1$s, in %2$s word·s'), ucfirst(mb_substr($elTitle, 0, 1)), str_word_count($elTitle)).']'; // To make it unique */
          $quiz['question'] .= ' [ '.preg_replace("/[^A-Z|\s]/", "-", $elTitle).' ]'; // To make it unique
          $quiz['question'] .= '<br />'.$sanitizer->entitiesMarkdown(__('**Where** is it ?'));
          $quiz['answer'] = sprintf(__('%1$s (in %2$s, in %3$s)'), $randEl->title, $randEl->city->title, $randEl->country->title);
          break;
        default : $quiz['answer'] = __("Question type error !");
      }
    }
    if ($randList == 'people') {
      switch($quiz['type']) {
        case 'country' :
          $quiz['question'] = $sanitizer->entitiesMarkdown(sprintf(__('Where is %s from ? (**country**)'), $randEl->title));
          $quiz['answer'] = sprintf(__('From %s'), $randEl->country->title);
          break;
        case 'nationality' :
          $quiz['question'] = $sanitizer->entitiesMarkdown(sprintf(__('What is the **nationality** of %s ?'), $randEl->title));
          $quiz['answer'] = sprintf(__('%1$s (from %2$s)'), $randEl->nationality, $randEl->country->title);
          break;
        case 'photo' :
          $quiz['question'] = $sanitizer->entitiesMarkdown(__('**Who** is this ?'));
          $quiz['answer'] = sprintf(__('%1$s, from %2$s (%3$s)'), $randEl->title, $randEl->country->title, $randEl->nationality);
          break;
        case 'quiz' :
          $randQuiz = $randEl->quiz->getRandom();
          $quiz['question'] = $randQuiz->question;
          $quiz['answer'] = $randQuiz->answer;
          break;
        default : $quiz['answer'] = __('Question type error !');
      }
    }
  }
  $quiz['id'] = $randEl->id;
  // Get player's stats on quizzed element (over current school year)
  $quiz['stats'] = quizStat($randEl, $player);
  return $quiz;
}

function quizStat($el, $player, $startDate='', $endDate='') { // Get player's stats on quizzed element
  $pages = wire('pages');
  $right = 0;
  $wrong = 0;
  // Find current school year date
  $schoolYear = $pages->get("template=period, name=school-year");
  if ($startDate == '') { $startDate = $schoolYear->dateStart; }
  if ($endDate == '') { $endDate = $schoolYear->dateEnd; }
  $invasions = $player->get("name=history")->find("template=event, task.name~=invasion, refPage=$el, date>=$startDate, date<=$endDate");
  if ($invasions->count() > 0) {
    $right = $invasions->find("task.name=right-invasion")->count();
    $wrong = $invasions->find("task.name=wrong-invasion")->count();
  }
  return array($right, $wrong);
}

function nbFreedomActs($team, $startDate='', $endDate='') {
  $pages = wire('pages');
  // Find current school year date
  $schoolYear = $pages->get("template=period, name=school-year");
  if ($startDate == '') { $startDate = $schoolYear->dateStart; }
  if ($endDate == '') { $endDate = $schoolYear->dateEnd; }
  $players = $pages->find("template=player, team=$team");
  $nbFree = $pages->find("has_parent=$players, template=event, task.name=free, date>=$startDate, date<=$endDate")->count();
  $nbRemove = $pages->find("has_parent=$players, template=event, task.name=remove, date>=$startDate, date<=$endDate")->count();
  return $nbFree-$nbRemove;
}

function progressBar($index, $total) {
  if ($index >= $total) {
    $width = 100;
  } else if ($index == 0) {
    $width = 0;
  } else {
    $width = (100*$index)/$total;
  }
  $out = '<div class="progress progress-striped progress-mini">';
  $out .= '<div class="progress-bar progress-bar-danger" role="progressbar" style="width: '.$width.'%;"></div>';
  $out .= '</div>';
  return $out;
}

function setTeamRate($teamId) {
  $pages = wire('pages');
  $team = $pages->get($teamId);
  $nbPlayers = $pages->count("parent.name=players, team=$team");
  if ($team->rank->index >= 8) { // Lower team rate for teams having a world composed of both places and people
    $teamRate = round(($nbPlayers*15)/100);
  } else {
    $teamRate = round(($nbPlayers*20)/100);
  }
  $teamRate == 0 ? $teamRate = 1 : '';
  return $teamRate;
}

function setOwners($el, $player) {
  $pages = wire('pages');
  $allPlayers = $pages->find("template=player, team=$player->team");
  $teamRate = setTeamRate($player->team->id); // Set team stats
  if ($el->template == 'place') {
    $teamOwners = $allPlayers->find("places=$el");
  }
  if ($el->template == 'people') {
    $teamOwners = $allPlayers->find("people=$el");
  }
  $el->owners = $teamOwners;
  // Set element free rate
  if ($teamOwners->count() >= round($teamRate/2)) { $el->completed = 0.5; $el->cssClass = 'label label-info'; }
  if ($teamOwners->count() >= $teamRate) { $el->completed = 1; $el->cssClass = 'label label-success'; }
  if ($teamOwners->count() <= round($teamRate/2)) { $el->completed = 0; $el->cssClass = 'label label-danger'; }
  $el->teamRate = $teamRate;
  return $el;
}

function setElement($el, $team) {
  $pages = wire('pages');
  $allPlayers = $pages->find("parent.name=players, template=player, team=$team");
  $teamRate = setTeamRate($team->id); // Set team stats
  if ($el->template == 'place') {
    $teamOwners = $allPlayers->find("places=$el")->count();
  }
  if ($el->template == 'people') {
    $teamOwners = $allPlayers->find("people=$el")->count();
  }
  $el->owners = $teamOwners;
  // Set element free rate
  if ($teamOwners >= $teamRate) { 
    $el->completed = 1; 
    $el->cssClass = 'label label-success';
  } else if ($teamOwners >= round($teamRate/2)) { 
    $el->completed = 0.5;
    $el->cssClass = 'label label-primary';
  } else {
    $el->completed = 0;
    $el->cssClass = 'label label-danger';
  }
  $el->teamRate = $teamRate;
  return $el;
}

function teamFreeworld($team) {
  $pages = wire('pages');
  // Get all possible elements
  $rank = $team->rank->index;
  if ($rank >= 8) {
    $allElements = $pages->find("template=place|people, name!=places|people");
  } else {
    $allElements = $pages->find("template=place, name!=places");
  }
  if ($team->name != 'no-team') {
    $allPlayers = $pages->find("parent.name=players, template=player, team=$team");
    $teamRate = setTeamRate($team->id); // Set team stats
    // Test if teamRate is reached
    foreach($allElements as $el) {
      if ($el->template == 'place') {
        $teamOwners = $allPlayers->find("places=$el");
      }
      if ($el->template == 'people') {
        $teamOwners = $allPlayers->find("people=$el");
      }
      if ($teamOwners->count() >= round($teamRate/2)) { $el->completed = 0.5; $el->cssClass = 'almost'; }
      if ($teamOwners->count() >= $teamRate) { $el->completed = 1; $el->cssClass = 'completed'; }
      if ($teamOwners->count() < round($teamRate/2)) { $el->completed = 0; $el->cssClass = 'far'; }
      $el->teamOwners = $teamOwners;
    }
  }
  $allCompleted = $allElements->find("completed=1");
  $allElements->sort("-completed, level, title");
  return $allElements;
}

function saveScoresForCache($player, $index) {
  // Check if previous cached exist
  $historyPage = $player->get("name=history");
  $tmpPage = $historyPage->tmpScores->eq($index-1);
  if (!$tmpPage) { // Create cache page
    $tmpPage = $historyPage->tmpScores->getNew();
  }
  $tmpPage->of(false);
  if ($tmpPage) {
    $tmpPage->yearlyKarma = $player->yearlyKarma;
    $tmpPage->reputation = $player->reputation;
    $tmpPage->level = $player->level;
    $tmpPage->HP = $player->HP;
    $tmpPage->XP = $player->XP;
    $tmpPage->GC = $player->GC;
    $tmpPage->underground_training = $player->underground_training;
    $tmpPage->fighting_power = $player->fighting_power;
    $tmpPage->donation = $player->donation;
    $tmpPage->equipment = $player->equipment;
    $tmpPage->places = $player->places;
    $tmpPage->people = $player->people;
    $tmpPage->coma = $player->coma;
    $tmpPage->save();
  }
  $historyPage->of(false);
  $historyPage->save();
}

function updateTeamScores($team, $element = false) {
  $pages = wire('pages');
  if ($team->name != 'no-team') {
    if ($element != false) { // Test only new element
      $team->freeActs += 1;
      if (!$team->freeelements->has($element)) { // Element is not already freed
        $element = setElement($element, $team);
        if ($element->completed == 1) { 
          if ($team->rank->index >= 8) {
            $nbAllElements = $pages->count("template=place|people");
          } else {
            $nbAllElements = $pages->count("template=place");
          }
          $team->freeelements->add($element); 
          $percent = round((100*$team->freeelements->count())/$nbAllElements);
          $team->freeworld = $percent;
        }
      }
    } else {
      $team->freeActs = nbFreedomActs($team);
      $team->freeelements->removeAll();
      $allElements = teamFreeworld($team);
      $completed = $allElements->find("completed=1");
      $percent = round((100*$completed->count())/$allElements->count());
      foreach ($completed as $el) {
        $team->freeelements->add($el);
      }
      $team->freeworld = $percent;
    }
    $team->of(false);
    $team->save();
    clearMarkupCache('cache__freeworld-'.$team->name , 'cache__scores-'.$team->teacher->eq(0)->name);
  }
}

function getScoresSummaries($headTeacher = false) {
  $cachedScores = wire('cache')->get('cache__scores-'.$headTeacher->name, 86400, function() use($headTeacher) {
    if (!$headTeacher->isSuperuser()) {
      $allTeams = wire('pages')->find("template=team, teacher=$headTeacher, name!=test-team");
    } else {
      $allTeams = wire('pages')->find("template=team");
    }
    $out =  '<h4 class="text-center scores">';
    $out .=  '<ul class="list-inline">';
    $allTeams->sort("-title");
    foreach($allTeams as $t) {
      if ($t->name != 'no-team') {
        if ($t->freeActs == '') { $t->freeActs = 0; }
        if ($t->freeworld == '') { $t->freeworld = 0; }
        $out .=  '<li class="label label-default" data-toggle="tooltip" data-html="true" title="'.__("School year Team Activity").'"><h4><strong>'.$t->title.':'.$t->freeActs.'</strong> ('.$t->freeworld.'%)</strong></h4></li>';
      }
    }
    $out .=  '</ul>';
    $out .=  '</h4>';
    return $out;
  });
  return $cachedScores;
}

function possibleDefense($team) {
  $pages = wire('pages');
  $nbConcerned = 0;
  $allPlayers = $pages->find("template=player, team=$team");
  if ($team->rank->index >= 8) {
    foreach($allPlayers as $p) { // Find players having at least 3 free elements
      $nbEl = $p->places->count()+$p->people->count();
      if ( $nbEl >= 3) {
        $nbConcerned++;
      }
    }
  } else {
    $nbConcerned = $pages->count("template=player, team=$team, places.count>=3"); // Find players having at least 3 places
  }
  return $nbConcerned;
}

function calculate_average($arr) {
  $total = 0;
  if (count($arr) > 0) {
    $count = count($arr); //total numbers in array
    foreach ($arr as $value) {
        $total = $total + $value; // total value of array numbers
    }
    $average = round($total/$count, 1); // get average value
    return $average;
  } else {
    return '-';
  }
}

function taskCount($taskName, $allEvents) {
  $all = $allEvents->find("task.name=$taskName");
  return $all->count();
}

function setHomework($player, $dateStart='', $dateEnd='') {
  $pages = wire('pages');
  // Get official period
  $period = $player->team->periods;
  if ($dateStart == '') { $dateStart = $period->dateStart; }
  if ($dateEnd == '') { $dateEnd = $period->dateEnd; }
  // Select homework tasks over the period
  $allHk = $player->find("template=event, task.category.name=homework, date>=$dateStart, date<=$dateEnd");
  $player->initiative = $player->find("task.name=personal-initiative, date>=$dateStart, date<=$dateEnd");
  $player->ut = $player->find("task.name=ut-action-v|ut-action-vv, date>=$dateStart, date<=$dateEnd");
  $player->fp = $player->find("task.name=best-time|battle-v|battle-vv|fight-v|fight-vv, date>=$dateStart, date<=$dateEnd");
  $player->inClassUtCount = $player->ut->find("inClass=1")->count();
  $player->outClassUtCount = $player->ut->find("inClass=0")->count();
  $player->inClassFpCount = $player->fp->find("inClass=1")->count();
  $player->outClassFpCount = $player->fp->find("inClass=0, task.name!=battle-v|battle-vv")->count();
  $player->inClassActivity = $player->inClassUtCount + $player->inoutClassFpCount;
  $player->outClassActivity = $player->outClassUtCount + $player->outClassFpCount;
  $player->nbHk = $allHk->count();
  $player->noHk = $allHk->find("task.name=no-homework");
  $player->halfHk = $allHk->find("task.name=homework-half-done");
  $player->extraHk = $allHk->find("task.name=extra-homework");
  $player->veryExtraHk = $allHk->find("task.name=very-extra-homework");
  $player->notSigned = $allHk->find("task.name=signature");
  $player->noMaterial = $allHk->find("task.name=material");
  if ($player->noMaterial->count() == 0) { $player->materialLabel = 'VV'; }
  if ($player->noMaterial->count() == 1) { $player->materialLabel = 'V'; }
  if ($player->noMaterial->count() == 2) { $player->materialLabel = 'R'; }
  if ($player->noMaterial->count() > 2) { $player->materialLabel = 'RR'; }
  $player->penalty = $allHk->find("task.name=penalty");
  // Stats over the period
  $pb = ((($player->halfHk->count()+$player->notSigned->count())*0.5) + $player->noHk->count())-($player->penalty->count()*3);
  if ($pb >= 0 && $pb < 1) { $player->homework = 'VV'; }
  if ($pb >= 1 && $pb < 2) { $player->homework = 'V'; }
  if ($pb >= 2 && $pb < 3) { $player->homework = 'R'; }
  if ($pb >= 3 || $player->penalty->count() >= 1) { $player->homework = 'RR'; }
  $player->hkPb = $pb;
  $nbBonus = $player->initiative->count() + $player->extraHk->count() + $player->veryExtraHk->count();
  if ($nbBonus >= 10 || ($player->outClassActivity) >= 50 || (($player->outClassActivity) >= 47 && $nbBonus == 9)) {
    $player->motivation = 'VV';
  } else if (($nbBonus >= 5 && $nbBonus < 10) || (($player->outClassActivity) >= 20 && ($player->outClassActivity) < 50) || ((($player->outClassActivity) >= 18 && ($player->outClassActivity) < 50) && $nbBonus == 4)) {
    $player->motivation = 'V';
  } else {
    $player->motivation = 'No bonus for the moment';
  }
  return $pb;
}

function setAttitude($player, $dateStart='', $dateEnd='') {
  $pages = wire('pages');
  // Get official period
  $period = $pages->get("name=admin-actions")->periods;
  if ($dateStart == '') { $dateStart = $period->dateStart; }
  if ($dateEnd == '') { $dateEnd = $period->dateEnd; }
  // Select events over the period
  $allEvents = $player->find("template=event, task.category.name=attitude, date>=$dateStart, date<=$dateEnd");
  $player->negAttitude = $allEvents->find("task.HP<0");
  $player->posAttitude = $allEvents->find("task.name=civil-achievement|personal-initiative");
  $player->nbAmbush = $allEvents->find("task.name=ambush")->count();
}

function setTeamScoreboard($teamName, $field, $limit = false) {
  $pages = wire('pages');
  if ($field == 'places' || $field == 'people') { $term1 = $field.'.count'; } else { $term1 = $field; }
  if ($limit) {
    $allPlayers = $pages->find("parent.name=players, team.name=$teamName, sort=-$term1, limit=$limit");
  } else {
    $allPlayers = $pages->find("parent.name=players, team.name=$teamName, name!=test, sort=-$term1");
  }
  return $allPlayers;
}

function setGlobalScoreboard($field, $limit = false) {
  $pages = wire('pages');
  if ($field == 'places' || $field == 'people') { $term1 = $field.'.count'; } else { $term1 = $field; }
  if ($limit) {
    $allPlayers = $pages->find("parent.name=players, template=player, name!=test, sort=-$term1, limit=$limit");
  } else {
    $allPlayers = $pages->find("parent.name=players, template=player, name!=test, sort=-$term1");
  }
  return $allPlayers;
}

function setScoreboardNew($player, $field, $scope = 'all', $posOnly = false) {
  $pages = wire('pages');
  if ($field == 'places' || $field == 'people') { 
    $term1 = $field.'.count'; 
    if ($player != false) {
      $term2 = $player->$field->count();
    }
  } else { 
    $term1 = $field; 
    if ($player != false) {
      $term2 = $player->$field;
    }
  }
  if ($scope == 'all' || $scope == 'global') { // Global scoreboard
    if ($player != false) {
      $playerGlobalPos = $pages->count("parent.name=players, name!=test, $term1>$term2")+1;
      $totalGlobalPlayersNb = $pages->count("parent.name=players, name!=test");
      if ($posOnly == false) {
        if ($playerGlobalPos <= 6) { // Good position, get top 6
          $topGlobalPlayers =  $pages->find("parent.name=players, name!=test, $term1>$term2, limit=6, sort=-$term1");
          $topGlobalPlayers->add($player);
          $prevGlobalPlayers = false;
          $nextGlobalPlayer = false;
          if ($playerGlobalPos < 6) {
            $nextLimit = 6-$playerGlobalPos;
            $nextGlobalPlayers = $pages->find("parent.name=players, id!=$player->id, name!=test, $term1<=$term2, sort=-$term1, limit=$nextLimit");
            $topGlobalPlayers->add($nextGlobalPlayers);
          }
        } else {
          $topGlobalPlayers =  $pages->find("parent.name=players, name!=test, $term1>$term2, limit=3, sort=-$term1");
          $prevGlobalPlayers = $pages->find("parent.name=players, name!=test, $term1>$term2, limit=2, sort=$term1");
          $prevGlobalPlayers->add($player);
          $nextGlobalPlayer = $pages->get("parent.name=players, name!=test, id!=$player->id, $term1<$term2, sort=-$term1");
          $prevGlobalPlayers->add($nextGlobalPlayer);
        }
      }
      if ($scope == 'global') {
        if ($posOnly == false) {
          return array($playerGlobalPos, $totalGlobalPlayersNb, $prevGlobalPlayers, $topGlobalPlayers, $nextGlobalPlayer);
        } else {
          return array($playerGlobalPos, $totalGlobalPlayersNb);
        }
      }
    } else {
      $totalGlobalPlayersNb = $pages->count("parent.name=players, name!=test");
      $topGlobalPlayers =  $pages->find("parent.name=players, name!=test, limit=6, sort=-$term1");
      return array($topGlobalPlayers, $totalGlobalPlayersNb);
    }
  }
  if ($scope == 'all' || $scope == 'team') { // Team scoreboard
    if ($player != false) {
      $playerTeamPos = $pages->count("parent.name=players, team=$player->team, $term1>$term2")+1;
      $totalTeamPlayersNb = $pages->count("parent.name=players, team=$player->team");
      if ($posOnly == false) {
        if ($playerTeamPos <= 6) { // Good position, get top 6
          $topTeamPlayers = $pages->find("parent.name=players, team=$player->team, $term1>$term2, limit=6, sort=-$term1");
          $topTeamPlayers->add($player);
          $prevTeamPlayers = false;
          $nextTeamPlayer = false;
          if ($playerTeamPos < 6) {
            $nextLimit = 6-$playerTeamPos;
            $nextTeamPlayers = $pages->find("parent.name=players, id!=$player->id, team=$player->team, $term1<=$term2, sort=-$term1, limit=$nextLimit");
            $topTeamPlayers->add($nextTeamPlayers);
          }
        } else {
          $topTeamPlayers = $pages->find("parent.name=players, team=$player->team, $term1>$term2, limit=3, sort=-$term1");
          $prevTeamPlayers = $pages->find("parent.name=players, team=$player->team, $term1>$term2, limit=2, sort=$term1");
          $prevTeamPlayers->add($player);
          $nextTeamPlayer = $pages->get("parent.name=players, team=$player->team, id!=$player->id, $term1<=$term2, sort=-$term1");
          if ($nextTeamPlayer->count() > 0) {
            $prevTeamPlayers->add($nextTeamPlayer);
          }
        }
      }
      if ($scope == 'team') {
        if ($posOnly == false) {
          return array($playerTeamPos, $totalTeamPlayersNb, $prevTeamPlayers, $topTeamPlayers, $nextTeamPlayer);
        } else {
          return array($playerTeamPos, $totalTeamPlayersNb);
        }
      }
    } else {
      $totalTeamPlayersNb = $pages->count("parent.name=players, team=$player->team");
      // TODO : Manage team name ?
    }
  }
  if ($posOnly == false) {
    return array($playerGlobalPos, $totalGlobalPlayersNb, $playerTeamPos, $totalTeamPlayersNb, $prevGlobalPlayers, $prevTeamPlayers, $topGlobalPlayers, $topTeamPlayers, $nextGlobalPlayer, $nextTeamPlayer);
  } else {
    return array($playerGlobalPos, $totalGlobalPlayersNb, $playerTeamPos, $totalTeamPlayersNb);
  }
}

function setGroupScores($allPlayers) {
  // Build all groups
  $allGroups = new PageArray();
  foreach($allPlayers as $p) {
    $nbEl = 0;
    if (!in_array($p->group, $allGroups->getArray())) {
      $allGroups->add($p->group);
    }
    $p->nbEl = $p->places->count()+$p->people->count();
  }
  if ($allGroups->count() > 0) {
    // Calculate groups Karma
    foreach($allGroups as $group) {
      $group->karma = 0;
      $group->nbBonus = 0;
      // Find selected players
      $players = $allPlayers->find("group=$group");
      // Check for group bonus
      $group->nbBonus = groupBonus($players);
      $group->karma = $group->nbBonus*30;
      // Add individual karmas
      foreach($players as $player) {
        // Karma is divided by number of players in the group to be fair with smaller groups
        $groupKarma = round($player->yearlyKarma/$players->count);
        (int) $group->karma += $groupKarma;
        $group->members = $players->implode(' - ', '{title} {yearlyKarma}K {nbEl}el.');
        $group->team = $player->team;
      }
    }
    $allGroups->sort('-karma');
  } else {
    $allGroups = NULL;
  }
  return $allGroups;
}

function displaySmallScoreboard($topPlayers, $prevPlayers, $playerPos, $playerId, $field) {
  $out = '';
  if ($prevPlayers != false) { // Player is 'surrounded'
    $out .= '<ol>';
    if ($topPlayers->count() > 0) {
      foreach ($topPlayers as $p) {
        $p->id == $playerId ? $focus = "class='focus'" : $focus = "";
        $out .= scoreboardLine($field, $p, $focus);
      }
    }
    $out .= '</ol>';
    $out .= '<p class="text-center">[...]</p>';
    $startIndex = (int) $playerPos-2; 
    $out .= '<ol start="'.$startIndex.'">';
      if ($prevPlayers->count() > 0) {
        foreach ($prevPlayers as $p) {
          $p->id == $playerId ? $focus = "class='focus'" : $focus = "";
          $out .= scoreboardLine($field, $p, $focus);
        }
      }
    $out .= '</ol>';
  } else { // No ranking or Top 10
    $out .= '<ol>';
    foreach ($topPlayers as $p) {
      $p->id == $playerId ? $focus = "class='focus'" : $focus = "";
      $out .= scoreboardLine($field, $p, $focus);
    }
    $out .= '</ol>';
  }
  return $out;
}

function displayCompleteScoreboard($allPlayers, $playerId, $field, $pageNum) {
  if ($field == 'places' || $field == 'people') { $selector = '-'.$field.'.count'; } else { $selector = '-'.$field; }
  $allPlayers->sort($selector);
  $out = '';
  $startIndex = ($pageNum*35)-34;
  $out .= '<ol start="'.$startIndex.'">';
  foreach ($allPlayers as $p) {
    $p->id == $playerId ? $focus = 'class="focus"' : $focus = '';
    if ($p->avatar) {
      $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$p->avatar->getCrop('thumbnail')->url."\" alt=\"".$p->title.".\" />' src='".$p->avatar->getCrop('mini')->url."' alt='".$p->title.".' />";
    } else {
      $mini = '';
    }
    $out .= scoreboardLine($field, $p, $focus);
  }
  $out .= '</ol>';
  return $out;
}

function displayGroupScoreboard($allGroups, $playerGroupId) {
  $out = '';
  $out .= '<ol class="">';
  foreach($allGroups as $group) {
    if ($playerGroupId == $group->id) {
      $focus = 'focus';
    } else {
      $focus = '';
    }
    $out .= '<li>';
    $out .= '<p data-toggle="tooltip" data-html="true" title="'.$group->members.'" onmouseenter="$(this).tooltip(\'show\');">';
    $out .= '<span class="'.$focus.'">'.$group->title.'</span>';
    $out .= ' <span class="badge">'.$group->karma.' K</span>';
    // Display stars for bonus (filled star = 5 empty stars, 1 star = 1 free element for each group member)
    if ($group->nbBonus > 0) {
      $out .= '&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-star"></span>';
      $out .= '<span class="badge">'.$group->nbBonus.'</span>';
    }
    $out .= '</p>';
    $out .= '</li>';
  }
  $out .= '</ol>';
  return $out;
}

function scoreboardLine($field, $player, $focus) {
  $out = '';
  $p = $player;
  if ($p->avatar) {
    $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$p->avatar->getCrop('thumbnail')->url."\" alt=\"".$p->title.".\" />' src='".$p->avatar->getCrop('mini')->url."' alt='".$p->title.".' />";
  } else {
    $mini = '';
  }
  switch($field) {
    case 'yearlyKarma': $label = __('K.'); break;
    case 'reputation': $label = __('rep.'); break;
    case 'places': $label = __('pl.'); break;
    case 'people': $label = __('peo.'); break;
    case 'fighting_power': $label = __('FP'); break;
    case 'donation': $label = __('GC'); break;
    case 'underground_training': $label = __('UT'); break;
    default: $out .= 'Error.';
  }
  switch($field) {
    case 'yearlyKarma':
      $out .= '<li><span '. $focus .'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$p->yearlyKarma.' '.$label.'</span></li>';
      break;
    case 'reputation':
      $out .= '<li><span '. $focus .'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$p->reputation.' '.$label.'</span></li>';
      break;
    case 'places':
      if (!isset($p->places)) { $placesNb = 0; } else { $placesNb = $p->places->count(); }
      $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$placesNb.' '.$label.'</span></li>';
      break;
    case 'people':
      if (!isset($p->people)) { $peopleNb = 0; } else { $peopleNb = $p->people->count(); }
      $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$peopleNb.' '.$label.'</span></li>';
      break;
    case 'fighting_power':
      $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$p->fighting_power.' '.$label.'</span></li>';
      break;
    case 'donation':
      $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$p->donation.' '.$label.'</span></li>';
      break;
    case 'underground_training':
      $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a></span> <span class="badge">'.$p->underground_training.' '.$label.'</span></li>';
      break;
    default: $out .= 'Error.';
  }
  return $out;
}

function displayPlayerPos($scoreboardLink, $title, $playerPos, $playerTeamPos, $totalPlayers, $totalTeamPlayers) {
  $out = '';
  $out .= '<p>';
  $out .= '<a href="'.$scoreboardLink.'">';
  $out .= '<span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
  $out .= $title.' → ';
  $out .= '<span class="label label-primary">'.__("In Planet Alert").'</span> ';
  if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
  $out .= $playerPos.'/'.$totalPlayers.' '.$star.' ';
  if ($playerTeamPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
  $out .= '<span class="label label-primary">'.__("In your team").'</span> ';
  $out .= $playerTeamPos.'/'.$totalTeamPlayers.' '.$star;
  $out .= '</p>';
  return $out;
}

function setSpacedRepetition($level, $trainNb) { // Set spaced repetition according to monster's level?
  if ($level >= 1 ) { // For the moment, all levels
    // Limit to 1 training session a day if prevTrainings<=5
    // Limit to 1 training session a week if prevTrainings<=10
    // Limit to 1 training session a month if prevTrainings>=15
    if ($trainNb >= 0 && $trainNb <= 5) {
      $spacedTrainings = 1;
    }
    if ($trainNb > 5 && $trainNb <= 10) {
      $spacedTrainings = 7;
    }
    if ($trainNb > 10) {
      $spacedTrainings = 30;
    }
  }
  return $spacedTrainings;
}

function averageLabel($quality) {
  if ($quality >= 1.5) { $label = '<span class="label label-success">VV</span>'; }
  if ($quality < 1.5 && $quality > 0.3) { $label = '<span class="label label-success">V</span>'; }
  if ($quality <= 0.3 && $quality > -0.4) { $label = '<span class="label label-danger">R</span>'; }
  if ($quality <= -0.4) { $label = '<span class="label label-danger">RR</span>'; }
  return $label;
}

function setFightsQuality($player, $monster) {
  $fights = $player->find("template=event, task.name~=fight, refPage=$monster")->sort("date");
  $fightNb = $fights->count();
  if ($fightNb > 0) {
    $lastFightDate = $fights->last()->date;
  } else {
    $lastFightDate = false;
  }
  // Calculate average
  $VV = $fights->find("task.name=fight-vv")->count();
  $V = $fights->find("task.name=fight-v")->count();
  $R = $fights->find("task.name=fight-r")->count();
  $RR = $fights->find("task.name=fight-rr")->count();
  $posNb = $V + $VV;
  $negNb = $R + $RR;
  if ($fightNb > 0) {
    $quality = round(($VV*2+$V-$R-$RR*2)/$fightNb, 1);
    $averageLabel = averageLabel($quality);
  } else {
    $quality = false;
    $averageLabel = false;
  }
  return array($fightNb, $posNb, $negNb, $quality, $averageLabel, $lastFightDate);
}

function setMonsterFromCache ($tmpCache, $monster) {
  $minUt = 20; // Previous training required to be able to fight
  $spacedFights = 15; // 15 days between fights
  $fightInterval = new \DateInterval('P15D');
  // Sets inUt, outUt, lastFightDate.... for $monster
  $monster->masterTime = $monster->bestTime; // Keep faster time before overriding it below
  foreach ($tmpCache->fields as $f) {
    if ($f->name != 'monster') {
      $monster->$f = $tmpCache->$f;
    }
  }
  // Sets training possibilities
  $monster->utGain = ($tmpCache->inUt+$tmpCache->outUt);
  $monster->lastTrainingInterval = wire('datetime')->relativeTimeStr($tmpCache->lastTrainDate);
  if ($tmpCache->date) {
    $availableTrainDate = new \DateTime(date("Y-m-d", $tmpCache->date));
    $today = new \DateTime("today");
    $monster->waitForTrain = $today->diff($availableTrainDate)->format("%r%a");
  } else {
    $monster->waitForTrain = 0;
  }
  if ($monster->waitForTrain <= 0) {
    $monster->isTrainable = 1;
  } else {
    $monster->isTrainable = 0;
    $monster->waitForTrain = abs($monster->waitForTrain);
  }
  // Sets fighting possibilities
  $monster->lastFightInterval = wire('datetime')->relativeTimeStr($tmpCache->lastFightDate);
  if ($tmpCache->lastFightDate != '') {
    $today = new \DateTime("today");
    $availableFightDate = new \DateTime(date("Y-m-d", $tmpCache->lastFightDate));
    $monster->waitForFight = $spacedFights-$today->diff($availableFightDate)->format("%a");
  } else {
    if ($monster->utGain >= 20) {
      $monster->waitForFight = -1; 
    } else {
      $monster->waitForFight = 100; 
    }
    $monster->lastFightInterval = -1;
  }
  if ($monster->waitForFight <= 0) {
    // TODO : Check visualizer ?
    $monster->isFightable = 0;
  } else {
    $monster->isFightable = 0;
  }
  return $monster;
}

function setMonster($player, $monster) {
  $pages = wire('pages');
  $minUt = 20; // Previous training required to be able to fight
  $spacedFights = 15; // 15 days between fights
  $fightInterval = new \DateInterval('P15D');
  // Prepare monster from player's Cache page
  $tmpCache = $player->child("name=tmp")->tmpMonstersActivity->get("monster=$monster");
  if ($tmpCache) {
    $tmpCache->of(false);
    // Sets inUt, outUt, lastFightDate.... for $monster
    $monster->masterTime = $monster->bestTime; // Keep faster time before overriding it below
    foreach ($tmpCache->fields as $f) {
      if ($f->name != 'monster') {
        $monster->$f = $tmpCache->$f;
      }
    }
    // Sets training possibilities
    $monster->utGain = ($tmpCache->inUt+$tmpCache->outUt);
    $monster->lastTrainingInterval = wire('datetime')->relativeTimeStr($tmpCache->lastTrainDate);
    if ($tmpCache->date) {
      $availableTrainDate = new \DateTime(date("Y-m-d", $tmpCache->date));
      $today = new \DateTime("today");
      $monster->waitForTrain = $today->diff($availableTrainDate)->format("%r%a");
    } else {
      $monster->waitForTrain = 0;
    }
    if ($monster->waitForTrain <= 0) {
      $monster->isTrainable = 1;
    } else {
      $monster->isTrainable = 0;
      $monster->waitForTrain = abs($monster->waitForTrain);
    }
    // Sets fighting possibilities
    if ($tmpCache->lastFightDate != '') {
      $monster->lastFightInterval = wire('datetime')->relativeTimeStr($tmpCache->lastFightDate);
      if ($monster->utGain >= $minUt) {
        $today = new \DateTime("today");
        $availableFightDate = new \DateTime(date("Y-m-d", $tmpCache->lastFightDate));
        $monster->waitForFight = $spacedFights-$today->diff($availableFightDate)->format("%a");
      } else { // Solve previous bugs when fights wrongly got available
        $monster->waitForFight = 1000;
      }
    } else {
      if ($monster->utGain >= $minUt) {
        $monster->waitForFight = -1;
      } else {
        $monster->waitForFight = 100;
      }
      $monster->lastFightInterval = -1;
    }
    if ($monster->waitForFight <= 0) {
      if ($player->equipment->has("name~=visualizer") || $monster->special == 0 || $player->team->forceVisualizer == 1) {
        $monster->isFightable = 1;
      } else {
        $monster->isFightable = 0;
      }
    } else {
      $monster->isFightable = 0;
    }
  } else { // No previous monster activity
    $monster->utGain = 0;
    $monster->isTrainable = 1;
    $monster->lastTrainingInterval = '-1';
    $monster->waitForTrain = 0;
    $monster->isFightable = 0;
    $monster->lastFightInterval = -1;
    $monster->waitForFight = 0;
  }
  if ($monster->bestTrainedPlayerId != 0) {
    $bestTrained = $pages->get($monster->bestTrainedPlayerId);
    $monster->bestTrainedTitle = $bestTrained->title;
    $monster->bestTrainedTeam = $bestTrained->team->title;
    if ($monster->bestTrainedPlayerId == $player->id) {
      $monster->isBestTrained = true;
    } else {
      $monster->isBestTrained = false;
    }
  }
  if ($monster->bestTimePlayerId != 0) {
    $master = $pages->get($monster->bestTimePlayerId);
    $monster->bestTimePlayerTitle = $master->title;
    $monster->bestTimeTeam = $master->team->title;
    if ($master->id == $player->id) {
      $monster->isMaster = true;
    } else {
      $monster->isMaster = false;
    }
  } else {
    $monster->bestTimePlayerTitle = false;
    $monster->bestTimeTeam = false;
    $monster->isMaster = false;
  }
  return $monster;
}

function prepareListWords($allLines, $type) {
  switch ($type) {
    case 'translate' :
      if (count($allLines)>15) {
        $listWords = '<strong>'.__("10 first words").' :</strong><br />';
        for($i=0; $i<10; $i++) {
          list($left, $right) = preg_split('/,/', $allLines[$i]);
          $listWords .= $right.'<br />';
        }
        $listWords .= '[...]';
      } else {
        $listWords = '';
        foreach($allLines as $line) {
          list($left, $right) = preg_split('/,/', $line);
          $listWords .= $right.'<br />';
        }
      }
      break;
    case 'quiz' :
      if (count($allLines)>10) {
        $listWords = '<strong>'.__("10 first questions").' :</strong><br />';
        for($i=0; $i<10; $i++) {
          if (strpos($allLines[$i], "::") !== false) {
            list($left, $right) = preg_split('/::/', $allLines[$i]);
            $listWords .= '- '.$left.'<br />';
          }
        }
        $listWords .= '[...]';
      } else {
        $listWords = '';
        foreach($allLines as $line) {
          list($left, $right) = preg_split('/::/', $line);
          $listWords .= '- '.$left.'<br />';
        }
      }
      break;
    case 'image-map' :
      if (count($allLines)>10) {
        $listWords = '<strong>'.__("10 first words").' :</strong><br />';
        for($i=0; $i<10; $i++) {
          list($left, $right) = preg_split('/::/', $allLines[$i]);
          $listWords .= '- '.$right.'<br />';
        }
        $listWords .= '[...]';
      } else {
        $listWords = '';
        foreach($allLines as $line) {
          list($left, $right) = preg_split('/::/', $line);
          $listWords .= '- '.$right.'<br />';
        }
      }
      break;
    case 'jumble' :
      if (count($allLines)>10) {
        $listWords = '<strong>'.__("10 first sentences").' :</strong><br />';
        for($i=0; $i<10; $i++) {
          $pattern = '/\$.*?\$/';
          preg_match($pattern, $allLines[$i], $matches);
          if ($matches) {
            $help = preg_replace('/\$/', '', $matches[0]);
          }
          $listWords .= '- '.$help.'<br />';
        }
        $listWords .= '[...]';
      } else {
        $listWords = '';
        foreach($allLines as $line) {
          $pattern = '/\$.*?\$/';
          preg_match($pattern, $line, $matches);
          if (isset($matches[0])) {
            $help = preg_replace('/\$/', '', $matches[0]);
            $listWords .= '- '.$help.'<br />';
          }
        }
      }
      break;
    default :
      $listWords = '';
  }
  return $listWords;
}

/* function utReport($player, $monster = false) { */
/*   if (!$monster) { // All monsters */
/*     $allEvents = $player->find("template=event, task~=ut-action")->sort("refPage.name, date"); */
/*     $allConcernedMonsters = new PageArray(); */
/*     $lastMonster = ''; */
/*     if ($allEvents->count() > 0) { */
/*       foreach($allEvents as $e) { // Create monsters list */
/*         $allConcernedMonsters->add($e->refPage); */
/*       } */
/*       foreach ($allConcernedMonsters as $m) { */
/*         // Set monster result */
/*         $fights = $allEvents->find("refPage=$m")->sort("-date"); */
/*         $m->fightsCount = $fights->count(); */
/*         $m->total = $allEvents->count(); */
/*         // Set monster status */
/*         $m = setMonster($player, $m); */
/*       } */
/*       $allConcernedMonsters->sort("lastTrainDate, -utGain"); */
/*     } */
/*     return $allConcernedMonsters; */
/*   } else { // TODO ??? Single monster stats */
/*   } */
/* } */

/* function fightReport($player, $monster = false) { */
/*   if (!$monster) { // All monsters */
/*     $today = new \DateTime("today"); */
/*     $allFights = $player->find("template=event, task~=fight, refPage!=NULL")->sort("refPage.name, date"); */
/*     $allConcernedMonsters = new PageArray(); */
/*     $lastMonster = ''; */
/*     if ($allFights->count() > 0) { */
/*       foreach($allFights as $f) { // Create monsters list */
/*         if ($f->refPage) { */
/*           if ($f->refPage->name != $lastMonster) { */
/*             $allConcernedMonsters->add($f->refPage); */
/*           } */
/*           $lastMonster = $f->refPage->name; */
/*         } */
/*       } */
/*       foreach ($allConcernedMonsters as $m) { */
/*         // Set monster result */
/*         $fights = $allFights->find("refPage=$m")->sort("-date"); */
/*         $m->fightsCount = $fights->count(); */
/*         // Calculate average */
/*         $VV = $fights->find("task.name=fight-vv")->count(); */
/*         $V = $fights->find("task.name=fight-v")->count(); */
/*         $R = $fights->find("task.name=fight-r")->count(); */
/*         $RR = $fights->find("task.name=fight-rr")->count(); */
/*         $m->positive = $V + $VV; */
/*         $m->negative = $R + $RR; */
/*         if ($m->fightsCount > 0) { */
/*           $m->ratio = round(($VV*2+$V-$R-$RR*2)/$m->fightsCount, 1); */
/*           if ($m->ratio >= 1.5) { $m->average = '<span class="label label-success">VV</span>'; } */
/*           if ($m->ratio < 1.5 && $m->ratio > 0.3) { $m->average = '<span class="label label-success">V</span>'; } */
/*           if ($m->ratio <= 0.3 && $m->ratio > -0.4) { $m->average = '<span class="label label-danger">R</span>'; } */
/*           if ($m->ratio <= -0.4) { $m->average = '<span class="label label-danger">RR</span>'; } */
/*         } */
/*         // Set monster status */
/*         $m = setMonster($player, $m); */
/*       } */
/*       $allConcernedMonsters->sort("lastFight, -fightsCount"); */
/*     } */
/*     return $allConcernedMonsters; */
/*   } else { // Single monster stats */
/*     list($monster->fightsCount, $monster->positive, $monster->negative, $monster->quality, $monster->average, $monster->lastFightDate) = setFightsQuality($player, $monster); */
/*     $monster->ratio = $monster->quality; */
/*     return $monster; */
/*   } */
/* } */

function battleReport($player, $monster = false) {
  if (!$monster) { // All monsters
    $allBattles = $player->find("template=event, task~=battle")->sort("-date");
    if ($allBattles->count() > 0) {
      foreach($allBattles as $f) { // Create monsters list
        // Set monster result
        if ($f->task->name == "battle-vv") { $f->result = "<span class='label label-success'>VV</span>"; }
        if ($f->task->name == "battle-v") { $f->result = "<span class='label label-success'>V</span>"; }
        if ($f->task->name == "battle-r") { $f->result = "<span class='label label-danger'>R</span>"; }
        if ($f->task->name == "battle-rr") { $f->result = "<span class='label label-danger'>RR</span>"; }
      }
    }
    return $allBattles;
  } else { // TODO ??? Single monster stats
  }
}

function calculatedUt($monster, $player, $startDate='', $endDate='') {
  $utGain = 0;
  $inClassUtGain = 0;
  if ($startDate == '') { $startDate = date('2000-01-01 00:00:00'); }
  if ($endDate == '') { $endDate = date('Y-m-d 23:59:59'); }
  $prevUt = $player->find("template=event,refPage=$monster, task.name~=ut-action, date>$startDate, date<$endDate, sort=-date");
  if ($prevUt->count() > 0) {
    foreach($prevUt as $p) {
      preg_match("/\[\+([\d]+)U\.T\.\]/", $p->summary, $matches);
      if (!$matches) {
        if ($p->inClass == 0) {
          $utGain++;
        } else {
          $inClassUtGain++;
        }
      } else {
        if ($p->inClass == 0) {
          $utGain = $utGain+$matches[1];
        } else {
          $inClassUtGain = $inClassUtGain+$matches[1];
        }
      }
    }
  } else {
    $utGain = 0;
  }
  return array($utGain, $inClassUtGain);
}

function utGain($monster, $player, $startDate='', $endDate='') {
  if ($startDate == '' && $endDate == '') { // No restricting dates (hence global UT), get from tmpCache if possible
    $tmpPage = $player->child("name=tmp");
    if ($tmpPage->id) { // Is tmpCache available ?
      $tmp = $player->get("name=tmp")->tmpMonstersActivity->get("monster=$monster");
      if ($tmp) {
        $inUt = $tmp->inUt;
        $outUt = $tmp->outUt;
        return array($outUt, $inUt);
      } else {
        return calculatedUt($monster, $player, $startDate, $endDate);
      }
    } else { // Create tmp page for next call
      $tmpPage = createTmpCache($player);
      return calculatedUt($monster, $player, $startDate, $endDate);
    }
  } else { // Restricting dates means recalculate from scratch
    return calculatedUt($monster, $player, $startDate, $endDate);
  }
}

function createTmpCache($player) {
  $tmpPage = $player->child("name=tmp");
  if (!$tmpPage->id) {
    $tmpPage = new Page();
    $tmpPage->parent = $player;
    $tmpPage->template = 'tmp';
    $tmpPage->name = 'tmp';
    $tmpPage->title = 'tmp';
    $tmpPage->save();
    /* initTmpMonstersActivity($player); */
  }
}

function createHistoryPage($player) {
  $history = $player->child("name=history");
  if (!$history->id) { // Creation of history page if it doesn't exist
    $history = new Page();
    $history->parent = $player;
    $history->template = 'archive';
    $history->name = 'history';
    $history->title = 'history';
    $history->save();
  }
}

function updateCache($player, $historyPage) {
  $tmpPage = $player->child("name=tmp");
  if (!$tmpPage->id) { createTmpCache($player); }
  if ($historyPage->refPage) {
    $monster = $historyPage->refPage;
    $tmpCache = $tmpPage->tmpMonstersActivity->get("monster=$monster");
    if (!$tmpCache) { // Monster is new in cache
      $tmpCache = $tmpPage->tmpMonstersActivity->getNew();
      $tmpCache->monster = $monster;
    } else {
      $tmpCache = $tmpPage->tmpMonstersActivity->get("monster=$monster");
    }
    if ($historyPage->task->is("name~=ut-action")) { // Update tmpCache
      preg_match("/\[\+([\d]+)U\.?T\.?\]/", $historyPage->summary, $matches);
      if ($matches[0]) {
        $delta = $matches[1];
      } else {
        $delta = 1;
      }
      if ($tmpCache) { // Monster already exists in tmpMonstersActivity
        if ($historyPage->inClass) {
          $tmpCache->inUt += $delta;
        } else {
          $tmpCache->outUt += $delta;
        }
        $tmpCache->trainNb++;
        $tmpCache->lastTrainDate = $historyPage->date;
        // Set spacedRepetition date
        $spacedRepetition = setSpacedRepetition($monster->level, $tmpCache->trainNb); 
        $tmpCache->date = strtotime("+".$spacedRepetition."days",$tmpCache->lastTrainDate);
      }
      $tmpCache->of(false);
      $tmpCache->save();
      return;
    }
    if ($historyPage->task->is("name~=fight")) { // Update tmpCache
      if ($tmpCache) { // Monster already exists in tmpMonstersActivity
        $tmpCache->lastFightDate = $historyPage->date;
        list($tmpCache->fightNb, $posNb, $negNb, $tmpCache->quality, $average, $tmpCache->lastFightDate) = setFightsQuality($player, $monster);
      }
      $tmpCache->of(false);
      $tmpCache->save();
      return;
    }
  }
}

function initTmpMonstersActivity($player) {
  $pages = wire('pages');
  $tmpPage = $player->child("name=tmp");
  // Delete previous tmpMonstersActivity
  if ($tmpPage->tmpMonstersActivity) {
    foreach($tmpPage->tmpMonstersActivity as $p) {
      $tmpPage->tmpMonstersActivity->remove($p);
    }
    $tmpPage->of(false);
    $tmpPage->save();
  }
  $allMonsters = $pages->find("parent.name=monsters");
  $tmpPage->index = $allMonsters->count();
  foreach($allMonsters as $m) {
    // Sets training activity
    $allTrainings = $player->find("template=event, task.name~=ut-action, refPage=$m")->sort("date");
    if ($allTrainings->count() > 0) {
      $tmpCache = $tmpPage->tmpMonstersActivity->getNew();
      $tmpCache->monster = $m;
      foreach($allTrainings as $e) {
        preg_match("/\[\+([\d]+)U\.T\.\]/", $e->summary, $matches);
        if (isset($matches[0])) {
          $delta = $matches[1];
        } else {
          $delta = 1;
        }
        if ($e->inClass) {
          $tmpCache->inUt += $delta;
        } else {
          $tmpCache->outUt += $delta;
        }
        $tmpCache->trainNb++;
      }
      $tmpCache->lastTrainDate = $allTrainings->last()->date;
      // Set spacedRepetition date
      $spacedRepetition = setSpacedRepetition($m->level, $tmpCache->trainNb); 
      $tmpCache->date = strtotime("+".$spacedRepetition."days",$allTrainings->last()->date);
    }
    // Sets fighting activity
    list($fightNb, $posNb, $negNb, $quality, $average, $lastFightDate) = setFightsQuality($player, $m);
    if (($allTrainings->count() + $fightNb) > 0) {
      $tmpCache->fightNb = $fightNb;
      $tmpCache->quality = $quality;
      $tmpCache->lastFightDate = $lastFightDate;
      $tmpCache->save();
      $tmpPage->tmpMonstersActivity->add($tmpCache);
      $tmpPage->index--;
      $tmpPage->of(false);
      $tmpPage->save();
    }
  }
  $tmpPage->of(false);
  $tmpPage->save();
}

function setBestPlayer($monster, $player, $utGain) {
  $pages = wire('pages');
  $monster = $pages->get($monster->id);
  $monster->of(false);
  $monster->bestTrainedPlayerId = $player->id;
  $monster->best = $utGain;
  $monster->save();
}

/* function pma($player) { */
/*   $out = ''; */
/*   $pages = wire('pages'); */
/*   $config = wire('config'); */
/*   switch ($player->rank->index) { */
/*     case '6': // 6emes */
/*       /1* $levels = '1|2'; *1/ */
/*       $levels = '1|2|3|4|5'; */
/*       $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name"); */
/*       break; */
/*     case '7': // 5emes */
/*       /1* $levels = '1|2'; *1/ */
/*       $levels = '1|2|3|4|5'; */
/*       $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name"); */
/*       break; */
/*     case '8': // 4emes */
/*       $levels = '1|2|3|4|5'; */
/*       $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=-level, sort=name"); */
/*     case '9': // 3emes */
/*       $levels = '1|2|3|4|5'; */
/*       $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=-level, sort=name"); */
/*     default: */
/*       $levels = '1|2|3|4|5'; */
/*       $allMonsters = $pages->find("template=exercise, type.name=translate|quiz, sort=level, sort=name"); */
/*   } */
/*   if ($player->equipment->has('name=memory-helmet')) { */
/*     foreach($allMonsters as $m) { */
/*       $m = setMonster($player, $m); */
/*     } */
/*   } */
/*   $suggestedFights = $allMonsters->find("isFightable=1, sort=lastFightDate, sort=random, limit=3"); // Pick less-recently trained fightable monsters */
/*   $suggestedTrainings = $allMonsters->find("isTrainable=2, sort=name, limit=3"); */
/*   if ($suggestedTrainings->count() < 3) { */
/*     $n = 3-$suggestedTrainings->count(); */
/*     $suggestedTrainings->add($allMonsters->find("isTrainable=1, lastTrainingInterval=-1, level=$levels, sort=random, limit=$n")); */
/*     if ($suggestedTrainings->count() < 3) { */
/*       $n = 3-$suggestedTrainings->count(); */
/*       $suggestedTrainings->add($allMonsters->find("isTrainable=1, lastTrainingInterval!=-1, level=$levels, sort=-lastTrainingInterval, limit=$n")); */
/*     } */
/*   } */

/*   $out .= '<div class="row">'; */
/*   $out .= '<div class="col-sm-12">'; */
/*   $out .= '<div class="pma panel panel-primary">'; */
/*   $out .= '<div class="panel-heading">'; */
/*   $out .= '<h4><span class="glyphicon glyphicon-calendar"></span> Personal Analyzer'; */
/*   $out .= '<span class="pull-right">'; */
/*   $out .= '<span class="label label-success">Karma : '.$player->yearlyKarma.'</span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   $out .= '<span class="label label-success">Reputation : '.$player->reputation.'</span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   if ($player->coma == 0) { */
/*     $out .= '<span class="label label-default" data-toggle="tooltip" title="Level">'.$player->level.'<span class="glyphicon glyphicon-signal"></span></span>'; */
/*     $out .= '&nbsp;&nbsp;'; */
/*     $out .= '<span class="label label-default" data-toggle="tooltip" title="XP">'.$player->XP.'<img src="'.$config->urls->templates.'img/star.png" alt="" /></span>'; */
/*     $out .= '&nbsp;&nbsp;'; */
/*     $out .= '<span class="label label-default" data-toggle="tooltip" title="HP">'.$player->HP.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>'; */
/*   } else { */
/*     $out .= '<span class="label label-danger">Coma !</span>'; */
/*   } */
/*   $out .= '&nbsp;&nbsp;'; */
/*   $out .= '<span class="label label-default" data-toggle="tooltip" title="GC">'.$player->GC.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" /></span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   $freeElements = $player->places->count()+$player->people->count(); */
/*   $out .= '<span class="label label-info" data-toggle="tooltip" title="Free places/people">'.$freeElements.'<img src="'.$config->urls->templates.'img/globe.png" alt="" /></span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   $out .= '<span class="label label-info" data-toggle="tooltip" title="Equipment">'.$player->equipment->count().'<span class="glyphicon glyphicon-wrench"></span></span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   if ($player->donation == false) {$player->donation = 0; } */
/*   $out .= '<span class="label label-default" data-toggle="tooltip" title="Donated">'.$player->donation.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   $out .= '<span class="label label-primary" data-toggle="tooltip" title="Underground Training">'.$player->underground_training.' UT</span>'; */
/*   $out .= '&nbsp;&nbsp;'; */
/*   $out .= '<span class="label label-primary" data-toggle="tooltip" title="Fighting Power">'.$player->fighting_power.' FP</span>'; */
/*   $out .= '</span>'; */
/*   $out .= '</h4>'; */
/*   $out .= '</div>'; */
/*   $out .= '<div class="panel-body">'; */
/*   $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" title="This analyser is here to help you organize."></span>'; */
/*   $out .= '<span class="label label-primary">'; */
/*   $helmet = $pages->get("name=memory-helmet"); */
/*   if ($helmet->image) { */
/*     $out .= '<img width="20" src="'.$helmet->image->url.'" alt="Helmet" />'; */
/*   } */
/*   $out .= ' Underground Training</span> ⇒ '; */
/*   if ($suggestedTrainings->count() > 0) { */
/*     foreach($suggestedTrainings as $m) { */
/*       if ($m->image) { */
/*         $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getCrop('thumbnail')->url."\" alt=\"image\" />' src='".$m->image->getCrop('mini')->url."' alt='image' />"; */
/*       } else { */
/*         $mini = ''; */
/*       } */
/*       if ($m->isTrainable == 2) { $tag = '<span class="glyphicon glyphicon-ok-sign" title="Fought today"></span>'; $style = ' disabled="disabled" style="text-decoration: line-through;" title="Done today!"'; } else { $tag = ''; $style = 'title="'.$m->summary.'"'; } */
/*       $out .= '<a href="'.$pages->get("name=underground-training")->url.'?id='.$m->id.'" class="btn btn-sm btn-info" '.$style.' data-toggle="tooltip" data-html="true">'.$m->title.' '.$tag.'</a> '; */
/*     } */
/*   } else { */
/*     if ($player->team->forceHelmet == 1) { */
/*       $out .= __('Special access to the Underground Training Zone !'); */
/*     } else { */
/*       $out .= __('You have no possible training today.'); */
/*     } */
/*   } */
/*   $out .= '&nbsp;&nbsp;&nbsp;&nbsp;'; */
/*   $out .= '<span class="label label-primary"><span class="glyphicon glyphicon-flash"></span> Monster Fight(s)</span> ⇒ '; */
/*   // Test if fights have been disabled by Admin */
/*   $lock = $pages->get("$player->team")->lockFights; */
/*   if ($lock == 1) { */
/*     $out .= 'The administrator has disabled this feature.'; */
/*   } else { */
/*     if ($suggestedFights->count() > 0) { */
/*       foreach($suggestedFights as $m) { */
/*         if ($m->image) { */
/*           $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getCrop('thumbnail')->url."\" alt=\"image\" />' src='".$m->image->getCrop('mini')->url."' alt='image' />"; */
/*         } else { */
/*           $mini = ''; */
/*         } */
/*         if ($m->lastTrainingInterval == 0) { */
/*           $tag = '<span class="glyphicon glyphicon-ok-sign" title="Fought/Trained today"></span>'; */
/*           $style = ' disabled="disabled" style="text-decoration: line-through;" title="Done today!"'; */
/*         } else { */
/*           $tag = ''; */
/*           $style = 'title="'.$m->summary.'"'; */
/*         } */
/*         $out .= '<a href="'.$m->url.'" class="btn btn-sm btn-danger" '.$style.' data-toggle="tooltip" data-html="true">'.$mini.' '.$m->title.' '.$tag.'</a> '; */
/*       } */
/*     } else { */
/*       if ($player->team->forceHelmet == 1) { */
/*         $out .= __('Special access to the Fighting Zone !'); */
/*       } else { */
/*         $out .= __('You have no possible fights today.'); */
/*       } */
/*     } */
/*   } */
/*   $out .= '</p>'; */
/*   $out .= '</div>'; */
/*   // Personal advice */
/*   $out .= '<div class="panel-footer">'; */
/*   $lowTeamMembers = $pages->find("template=player, team=$player->team, HP<10"); */
/*   if ($lowTeamMembers->count() > 0) { */
/*     $out .= '<p><span class="label label-danger">Low HP in your team <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Maybe you can help them (donation, encouraging in class...)?"></span>: </span>'; */
/*     foreach($lowTeamMembers as $p) { */
/*       $out .= ''.$p->title.' '; */
/*     } */
/*     $out .= '</p>'; */
/*   } */
/*   $helmet = $player->equipment->get("name=memory-helmet"); */
/*   if (!$helmet->id) { */
/*     $out .= '<p><span class="glyphicon glyphicon-hand-up"></span> Think as a team : 1 member of your group should buy the Memory Helmet to give access to the group to the Underground Training Zone ! <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Agissez en équipe : 1 membre du groupe devrait débloquer le Memory Helmet pour donner l\'accès au groupe à la Underground Training Zone !"></span></p>'; */
/*   } */
/*   if ($player->GC > 100) { */
/*     $out .= '<p><span class="glyphicon glyphicon-hand-up"></span> You have '.$player->GC.' GC ! You should use it to increase your karma  (buy equipment, free elements, donate...) :) <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Tu as beaucoup de pièces d\'or ! Tu devrais les utiliser pour augmenter ton Karma (acheter de l\'équipement, libérer quelque chose, faire un don...)"></span></p>'; */
/*   } */
/*   $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'"); */
/*   $possibleEquipment = $allEquipments->find("GC<=$player->GC, level<=$player->level, id!=$player->equipment, parent.name!=potions, sort=-parent.name, sort=name"); */
/*   $allPlaces = $pages->get("/places/")->find("template='place', sort='title'"); */
/*   // Possible places */
/*   $possiblePlaces = $allPlaces->find("GC<=$player->GC, level<=$player->level, id!=$player->places,sort=name"); */
/*   $possibleElements = $possiblePlaces->count(); */
/*   if ($player->rank->is("index>=8") || ($player->team->is("name!=no-team") && $player->team->is("rank.index>=8"))) { */
/*     $allPeople = $pages->find("template=people, name!=people, sort=title"); */
/*     // Possible people */
/*     $possiblePeople = $allPeople->find("GC<=$player->GC, level<=$player->level, id!=$player->people,sort=name"); */
/*     $possibleElements += $possiblePeople->count(); */
/*   } */
/*   if ($possibleEquipment->count() > 0 || $possibleElements > 0) { */
/*     //Limit to 3 items a day */
/*     $today = new \DateTime("today"); */
/*     $limitDate = strtotime($today->format('Y-m-d')); */
/*     $todayItemsCount = $player->get("name=history")->find("date>=$limitDate")->count(); */
/*     if ($todayItemsCount >= 3) { */
/*       $out .= "<p class='text-center alert alert-warning'>You have reached the 3 items limit for today ! Come back tomorrow  to go to the Marketplace !</p>"; */
/*     } else { */
/*       $marketPlace = $pages->get("name=shop_generator")->url.$player->id; */
/*       $out .= '<p><span class="glyphicon glyphicon-hand-up"></span> New elements are available for you at <a href="'.$marketPlace.'">the Marketplace</a> ! <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="De nouveaux éléments sont accessibles pour toi dans la Marketplace !"></span></p>'; */
/*     } */
/*   } */
/*   $out .= '</div>'; */
/*   $out .= '</div>'; */
/*   $out .= '</div>'; */
/*   $out .= '</div>'; */

/*   return $out; */
/* } */

function possibleElements($player) {
  $pages = wire('pages');
  $already = new PageArray();
  $already->add($player->equipment);
  $already->add($player->places);
  $already->add($player->usabledItems); // Items waiting to be used (potions)
  // Get rid of potions bought within the last 15 days
  $today = new \DateTime("today");
  $interval = new \DateInterval('P15D');
  $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
  $boughtEvents = $player->find("template=event, date>=$limitDate, refPage.name~=potion, refPage.name!=health-potion");
  $items['locked'] = new PageArray();
  foreach($boughtEvents as $b) {
    $already->add($b->refPage);
    $date2 = new \DateTime(date("Y-m-d", $b->date));
    $interval = $today->diff($date2)->format("%a");
    $b->refPage->locked = $interval+1;
    $items['locked']->add($b->refPage);
  }
  // Lock visualizer if Memory helmet isn't in player's equipment
  if ($player->equipment->has('name=memory-helmet') == false) {
    $visualizer = $pages->get("name~=visualizer");
    $already->add($visualizer);
  }
  // Manage teacher's restrictions (only items for the moment)
  $headTeacher = getHeadTeacher($player);
  $teachersRestrictions = $pages->find("template=item, teacher!=$headTeacher");
  $already->add($teachersRestrictions);
  // Remove useless potions for no-team players
  if ($player->team->name == 'no-team') {
    $noteamRestrictions = $pages->find("template=item, name~=potion, name!=health-potion|recovering-potion");
    $already->add($noteamRestrictions);
  }
  if (($player->rank && $player->rank->is("index>=8")) || ($player->team && $player->team->is("rank.index>=8"))) {
    $already->add($player->people);
    $nbEl = ($player->places->count() + $player->people->count())+1;
    $items['unlocked'] = $pages->find("template=place|people|item|equipment, name!=people, GC<=$player->GC, level<=$player->level, freeActs<$nbEl, id!=$already")->sort("template, name");
  } else {
    $nbEl = $player->places->count()+1;
    $items['unlocked'] = $pages->find("template=place|item|equipment, GC<=$player->GC, level<=$player->level, freeActs<$nbEl, id!=$already")->sort("template, name");
  }
  return $items;
}

function possibleElement($player, $item) {
  $pages = wire('pages');
  //Limit to 3 items a day
  $today = new \DateTime("today");
  $limitDate = strtotime($today->format('Y-m-d'));
  $todayItemsCount = $player->get("name=history")->find("date>=$limitDate")->count();
  $already = new PageArray();
  $already->add($player->equipment);
  $already->add($player->places);
  $already->add($player->people);
  $already->add($player->usabledItems);
  if ($already->has($item)) {
    $item->pb = 'already';
  } else {
    $nbEl = $player->places->count()+$player->people->count()+1;
    $possibleItems = $pages->find("template=place|people|item|equipment, name!=places|people, GC<=$player->GC, level<=$player->level, freeActs<=$nbEl, id!=$already");
    if ($possibleItems->has($item) && $todayItemsCount < 3) {
      $item->pb = 'possible';
      // Lock visualizer if Memory helmet isn't in player's equipment
      if ($item->is("name~=visualizer") && $player->equipment->has("name=memory-helmet") == false) {
        $item->pb = 'helmet';
      }
    } else {
      if ($item->level > $player->level) { $item->pb = 'level'; }
      if ($item->GC > $player->GC) { $item->pb = 'GC'; }
      if ($item->freeActs > $nbEl) { $item->pb = 'freeActs'; }
      if ($todayItemsCount >= 3) { $item->pb = 'maxToday'; }
    }
  }
  return $item;
}

function setParticipation($player, $dateStart='', $dateEnd='') {
  $pages = wire('pages');
  // Get official period
  $period = $player->team->periods;
  if ($dateStart == '') { $dateStart = $period->dateStart; }
  if ($dateEnd == '') { $dateEnd = $period->dateEnd; }
  $pages = wire('pages');
  $allEvents = $player->find("template=event, task.category.name=participation, date>=$dateStart, date<=$dateEnd, sort=-date")->reverse();
  $player->nbPart = $allEvents->find("task.name!=abs|absent")->count();
  $absent = $allEvents->find("task.name=abs|absent");
  $vvEvents = $allEvents->find("task.name=communication-vv");
  $vEvents = $allEvents->find("task.name=communication-v");
  $rEvents = $allEvents->find("task.name=communication-r");
  $rrEvents = $allEvents->find("task.name=communication-rr");
  $player->absent = $absent;
  if ($player->nbPart > 0) {
    $ratio = round(($vvEvents->count()*2+$vEvents->count()-$rEvents->count()-$rrEvents->count()*2)/$player->nbPart, 1);
    if ($ratio >= 1.5) { $player->participation = 'VV'; }
    if ($ratio < 1.5 && $ratio > 0.3) { $player->participation = 'V'; }
    if ($ratio <= 0.3 && $ratio > -0.4) { $player->participation = 'R'; }
    if ($ratio <= -0.4) { $player->participation = 'RR'; }
    $player->partRatio = $ratio;
    $player->partPositive = $vEvents->count() + $vvEvents->count();
    $player->partNegative = $rEvents->count() + $rrEvents->count();
  } else {
    $player->participation = 'NN';
    $ratio = 'abs';
    $player->partRatio = '-';
    $player->partPositive = '-';
    $player->partNegative = '-';
  }
}

function customTask($task, $field, $headTeacher = false, $player = false) {
  $modTask = clone $task;
}

function checkModTask($task, $headTeacher = false, $player = false) {
  $modTask = clone $task;
  $user = wire('user');
  if (!$headTeacher) {
    if ($user->hasRole('teacher')) {
      $headTeacher = $user;
    } else {
      if ($player) {
        $headTeacher = getHeadTeacher($player);
      }
    }
  }
  if ($headTeacher) {
    $mod = $task->owner->get("singleTeacher=$headTeacher"); // Get personalized infos if needed
    if ($mod) {
      $task->mod = true;
      $mod->title != '' ? $modTask->title = $mod->title : $modTask->title = $task->title;
      $mod->teacherTitle != '' ? $modTask->teacherTitle = $mod->teacherTitle : $modTask->teacherTitle = $task->teacherTitle;
      $mod->summary != '' ? $modTask->summary = $mod->summary : $modTask->summary = $task->summary;
      $mod->HP != '' ? $modTask->HP = $mod->HP : $modTask->HP = $task->HP;
      $mod->XP != '' ? $modTask->XP = $mod->XP : $modTask->XP = $task->XP;
      $mod->GC != '' ? $modTask->GC = $mod->GC : $modTask->GC = $task->GC;
    }
    return $modTask;
  } else {
    return $task;
  }
}

function getHeadTeacher($player) {
  $users = wire('users');
  $pages = wire('pages');
  $userPlayer = $users->get($player->id);
  if ($userPlayer->id) { // Test if $player is a $user
    if ($userPlayer->hasRole('teacher')) {
      $headTeacher = $userPlayer;
      $playerPage = false;
    }
    if ($userPlayer->hasRole('player')) {
      $playerPage = $pages->get("parent.name=players, login=$userPlayer->name"); 
    }
    if ($userPlayer->isGuest()) {
      $playerPage = false;
      $headTeacher = $pages->get("template=team, name=no-team")->teacher->first();
    }
  } else {
    if ($player->is("template=player")) {
      $playerPage = $player;
    } else {
      $playerPage = false;
    }
  }
  if ($playerPage != false) {
    if ($playerPage->team && $playerPage->team->teacher->first()) {
      $headTeacher = $playerPage->team->teacher->first();
    } else {
      $headTeacher = false;
    }
  }
  return $headTeacher;
}

function getProfile($teacher) {
  $pages = wire('pages');
  $profilePage = $pages->get("parent.name=teachers, singleTeacher=$teacher");
  return $profilePage;
}

function miniProfile($player, $type = '') {
  $config = wire('config');
  $pages = wire('pages');
  $out = '<ul class="list-unstyled list-inline">';
  if ($type == 'profile') {
    $out .= '<span>'.__("Your scores").' : </span>';
  }
  if ($player->coma == 0) {
    // Display scores
    $out .= '<li><span class="label label-success">'.__("Karma").' : '.$player->yearlyKarma.'</span></li>';
    $out .= '<li><span class="label label-success">'.__("Reputation").' : '.$player->reputation.'</span></li>';
    $out .= '<li><span class="label label-default" data-toggle="tooltip" title="'.__('Level').'"><span class="glyphicon glyphicon-signal"></span> '.$player->level.'</span>';
    $threshold = getLevelThreshold($player->level);
    $out .= ' <span class="label label-default" data-toggle="tooltip" title="XP"><img src="'.$config->urls->templates.'img/star.png" alt="star." /> '.$player->XP.'/'.$threshold.'</span></li>';
    if ($player->places) {
      $nbFreeEl = $player->places->count();
    }
    if ($player->team->rank && $player->team->rank->is('index>=8') && $player->people) {
      $nbFreeEl += $player->people->count();
    }
    $out .= '<li><span class="label label-default" data-toggle="tooltip" title="'.__('Free Elements').'"><img src="'.$config->urls->templates.'img/globe.png" alt="globe." /> '.$nbFreeEl.'</span>';
    $out .= ' <span class="label label-default" data-toggle="tooltip" title="'.__('Equipment').'"><span class="glyphicon glyphicon-wrench"></span> '.$player->equipment->count().'</span></li>';
    $out .= '<li><span class="label label-default" data-toggle="tooltip" title="'.__('Underground Training').'">'.$player->underground_training.' '.__('UT').'</span>';
    $out .= ' <span class="label label-default" data-toggle="tooltip" title="'.__('Fighting Power').'">'.$player->fighting_power.' '.__('FP').'</span>';
    $out .= '</li>';
    $out .= '<li><span class="label label-default" data-toggle="tooltip" title="'.__('Health Points').'"><img src="'.$config->urls->templates.'img/heart.png" alt="heart." /> '.$player->HP.'</span>';
    $out .= ' <span id="playerGC" class="label label-default" data-toggle="tooltip" title="'.__('Gold Coins').'"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="gold coins." /> '.$player->GC.'</span></li>';

    // Display according to $type
    switch ($type) {
      case 'equipment' :
        if ($player->equipment && $player->equipment->count() > 0) {
          $shop = $pages->get("name=shop");
          $out .= '<ul class="list-inline">';
          $out .= '<li>'.__("Your equipment").' ('.$player->equipment->count().') → </li>';
          foreach($player->equipment as $p) {
            $out .= '<li><a href="'.$p->url.'"><img class="" src="'.$p->image->getCrop('small')->url.'" data-toggle="tooltip" title="'.$p->title.'" /></a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("You don't have any equipment for the moment.").'</p>';
        }
        break;
      case 'places' :
        if ($player->places && $player->places->count() > 0) {
          $out .= '<ul class="list-inline">';
          $out .= '<li>'.__("Your places").' ('.$player->places->count().') → </li>';
          foreach($player->places as $p) {
            $out .= '<li><a href="'.$p->url.'"><img class="" src="'.$p->photo->eq(0)->getCrop('mini')->url.'" data-toggle="tooltip" title="'.$p->title.'" /></a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("You don't have any places for the moment.").'</p>';
        }
        break;
      case 'people' :
        if ($player->people && $player->people->count() > 0) {
          $out .= '<ul class="list-inline">';
          $out .= '<li>'.__("Your people").' ('.$player->people->count().') → </li>';
          foreach($player->people as $p) {
            $out .= '<li><a href="'.$p->url.'"><img class="" src="'.$p->photo->eq(0)->getCrop('mini')->url.'" data-toggle="tooltip" title="'.$p->title.'" /></a></li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("You don't have any people for the moment.").'</p>';
        }
        break;
      default: break;
    }
  } else {
    $out .= '<li class="label label-danger">Coma !</li>';
  }
  $out .= '</ul>';
  return $out;
}

function groupScores($team = NULL) {
  $pages = wire('pages');
  if (isset($team)) {
    $allPlayers = $pages->find("parent.name=players, template=player, team=$team");
  } else {
    $allPlayers = $pages->find("parent.name=players, template=player, name!=test");
  }
  // Build all groups
  $allGroups = new PageArray();
  foreach($allPlayers as $p) {
    $nbEl = 0;
    if (!in_array($p->group, $allGroups->getArray())) {
      $allGroups->add($p->group);
    }
    $p->nbEl = $p->places->count()+$p->people->count();
  }
  if ($allGroups->count() > 0) {
    // Calculate groups Karma
    foreach($allGroups as $group) {
      $group->karma = 0;
      $group->nbBonus = 0;
      // Find selected players
      $players = $allPlayers->find("group=$group");
      // Check for group bonus
      $group->nbBonus = groupBonus($players);
      $group->karma = $group->nbBonus*30;
      // Add individual karmas
      foreach($players as $player) {
        // Karma is divided by number of players in the group to be fair with smaller groups
        $groupKarma = round($player->yearlyKarma/$players->count);
        (int) $group->karma += $groupKarma;
        $group->members = $players->implode(' - ', '{title} {yearlyKarma}K {nbEl}el.');
        $group->team = $player->team;
      }
    }
    $allGroups->sort('-karma');
  } else {
    $allGroups = NULL;
  }
  return $allGroups;
}

function pluralize($count, $singular, $plural = false) {
  if (!$plural) $plural = $singular . 's';
  return ($count == 1 || $count == 0 ? $singular : $plural) ;
}

function ms2string($ms) {
  $oneSecond = 100;
  $oneMinute = $oneSecond * 60;
  $oneHour = $oneMinute * 60;
  $oneDay = $oneHour * 24;
  $seconds = floor(($ms % $oneMinute) / $oneSecond);
  $minutes = floor(($ms % $oneHour) / $oneMinute);
  $hours = floor(($ms % $oneDay) / $oneHour);
  $days = floor($ms / $oneDay);
  $timeString = '';
  $millLeft = $ms;
  if ($days != 0) {
    $millLeft = $ms-($days*$oneDay);
    $timeString .= $days.'d ';
  }
  if ($hours != 0) {
    $millLeft = $ms-($hours*$oneHour);
    $timeString .= $hours.'h ';
  }
  if ($minutes != 0) {
    $millLeft = $ms-($minutes*$oneMinute);
    $timeString .= $minutes.'m ';
  }
  if ($seconds != 0) {
    $millLeft = $ms-($seconds*$oneSecond);
    if ($millLeft>=0 && $millLeft <= 9) { $millLeft = '0'+$millLeft; }
    $timeString .= $seconds.'s ';
  }
  if ($millLeft < 1000) {
    $timeString .= $millLeft;
  }
  return $timeString;
}

?>
