<?php /* adminActions template */
  if (!$config->ajax) {
    include("./head.inc"); 

    $out = '';
    if ($user->isSuperuser()) {
      $action = $input->urlSegment1;
      $allPlayers->sort("playerTeam, title");
      $out .= '<div class="alert alert-warning text-center">Admin Actions : Be careful !</div>';
      switch ($action) {
      case 'ut' :
        $out .= '<p>⇒ Check if UT\'s hall of fame is correct.</p>';
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="ut">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      case 'ut-stats' :
        $out .= '<section class="well">';
        $out .= '<div>';
        $out .= '<span>Select a team : </span>';
        $out .= '<select id="teamId">';
        $out .= '<option value="-1">Select a team</option>';
        foreach($allTeams as $p) {
          $out .= '<option value="'.$p.'">'.$p.'</option>';
        }
        $out .= '</select>';
        $out .= '</div>';
        $out .= '<section class="well">';
        $out .= '<p>Select limiting dates if needed (start empty = 01/01/20000, end empty = today). <span class="label label-danger">English format dates!</span></p>';
        $out .= '<label for="startDate">From (mm/dd/yyyy) : </label>';
        $out .= '<input id="startDate" name="startDate" type="text" size="10" value="" />  ';
        $out .= '<label for="endDate">To (mm/dd/yyyy) : </label>';
        $out .= '<input id="endDate" name="endDate" type="text" size="10" value="" />';
        $out .= '</section>';
        $out .= '</section>';
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="ut-stats">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      case 'task-report' :
        $out .= '<section class="well">';
        $out .= '<div>';
        $out .= '<span>Select a team : </span>';
        $out .= '<select id="teamId">';
        $out .= '<option value="-1">Select a team</option>';
        foreach($allTeams as $p) {
          $out .= '<option value="'.$p.'">'.$p.'</option>';
        }
        $out .= '</select>';
        $out .= '<span>Select a task : </span>';
        $out .= '<select id="taskId">';
        $out .= '<option value="-1">Select a task</option>';
        $allTasks = $pages->find("template=task, sort=title");
        foreach($allTasks as $t) {
          $out .= '<option value="'.$t.'">'.$t->title.'</option>';
        }
        $out .= '</select>';
        $out .= '</div>';
        $out .= '<section class="well">';
        $out .= '<p>Select limiting dates if needed (start empty = 01/01/20000, end empty = today). <span class="label label-danger">English format dates!</span></p>';
        $out .= '<label for="startDate">From (mm/dd/yyyy) : </label>';
        $out .= '<input id="startDate" name="startDate" type="text" size="10" value="" />  ';
        $out .= '<label for="endDate">To (mm/dd/yyyy) : </label>';
        $out .= '<input id="endDate" name="endDate" type="text" size="10" value="" />';
        $out .= '</section>';
        $out .= '</section>';
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="task-report">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      case 'recalculate' :
        $out .= '<section class="well">';
        $out .= '<div>';
        $out .= '<span>Select a player : </span>';
        $out .= '<select id="playerId">';
        $out .= '<option value="-1">Select a player</option>';
        $out .= '<option value="all">All players</option>';
        foreach($allPlayers as $p) {
          $out .= '<option value="'.$p->id.'">'.$p->title.' ['.$p->playerTeam.']</option>';
        }
        $out .= '</select>';
        $out .= '</div>';
        $out .= '</section>';
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="recalculate">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      default :
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="script">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        /* $out .= '<section class="well">'; */
        /* $out .= '<div>'; */
        /* $out .= '<span>Select a player : </span>'; */
        /* $out .= '<select id="playerId">'; */
        /* $out .= '<option value="-1">Select a player</option>'; */
        /* $out .= '<option value="all">All players</option>'; */
        /* foreach($allPlayers as $p) { */
        /*   $out .= '<option value="'.$p->id.'">'.$p->title.' ['.$p->playerTeam.']</option>'; */
        /* } */
        /* $out .= '</select>'; */
        /* $out .= '</div>'; */
        /* $out .= '<div>'; */
        /* $out .= '<span>Select a team : </span>'; */
        /* $out .= '<select id="teamId">'; */
        /* $out .= '<option value="-1">Select a team</option>'; */
        /* foreach($allTeams as $p) { */
        /*   $out .= '<option value="'.$p.'">'.$p.'</option>'; */
        /* } */
        /* $out .= '</select>'; */
        /* $out .= '</div>'; */
        /* $out .= '</section>'; */
?>
  <div>
  <!-- 
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="refPage">Set refPage</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="helmet">Check Memory helmet</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="ut">Check UT scoreboard</button>
  -->
  <!-- <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="clean-history">Clean history</button> -->
  <!-- <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="recalculate">Recalculate scores</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="ut-stats">UT Stats</button> -->
  <!--
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="script">Script</button>
  -->
<?php
    }
    } else {
      $out .= 'Admin only.';
    }
    echo $out;
    include("./foot.inc"); 
  } else { // Ajax call, display requested information
    include("./my-functions.inc"); 
    $allPlayers = $pages->find("template=player")->sort("playerTeam, title");
    $action = $input->urlSegment1;
    $playerId = $input->urlSegment2;
    $confirm = $input->urlSegment3;
    $unique = true;
    $type = $input->get["type"];
    $startDate = $input->get["startDate"]; 
    $endDate = $input->get["endDate"]; 
    if ($startDate == '') {
      $startDate = date('2000-01-01 00:00:00');
    } else {
      $startDate = $startDate.' 00:00:00';
    }
    if ($endDate == '') {
      $endDate = date('Y-m-d 23:59:59');
    } else {
      $endDate = $endDate.' 23:59:59';
    }

    if ($type == 'team') {
      $selectedTeam = $playerId;
    } else {
      switch($playerId) {
        case 'all' : 
          $selectedPlayer = false;
          $selectedAll = true;
          break;
        case '-1' :
          $selectedPlayer = false;
          $selectedAll= false;
          break;
        default :
          $selectedPlayer = $pages->get($playerId);
          $selectedAll= false;
      }
    }

    switch ($action) {
      case 'script' :
        /* $allEvents = $pages->find("template=event, summary~='team died'"); */
        /* $out .= $allEvents->count(); */
        /* $out .= '<br />'; */
        /* $title = 'Team Death'; */
        /* foreach($allEvents as $e) { */
        /*   $out .= $e->id.': '.$e->title. ' → '.$title.'<br />'; */
        /*   $e->of(false); */
        /*   $e->title = $title; */
        /*   $e->save(); */
        /* } */
        break;
      case 'refPage' :
        $out .= 'Total # of players : '.$allPlayers->count();
        $out .= '<ul>';
        foreach($allPlayers as $p) {
          $p->of(false);
          $out .= '<li>'.$p->title.' ['.$p->playerTeam.']</li>';
          $allEvents = $p->get("name=history")->children("task.name=buy|free, refPage=''");
          if ($allEvents->count() > 0) {
            foreach($allEvents as $e) {
              $e->of(false);
              $out .= '<li>';
              $out .= strftime("%d/%m", $e->date).' - ';
              $out .= $e->title;
              $comment = trim($e->summary);
              if ($comment) {
                $out .= ' ['.$comment.']';
              }
              if ($e->refPage == false) {
                // Compare summary to equipment or place title
                $refPage = $pages->get("title=$comment");
                if ($refPage && $refPage->id != 0) {
                  $out .= ' → OK ('.$refPage->id.')';
                  $e->refPage = $refPage;
                } else {
                  $out .= ' → Page not found!';
                }
                $e->save();
              }
              $out .='</li>';
            }
          } else {
            $out .= '<span class="label label-success">Good</span> No empty refPage found. Recalculation of score for <strong>'. $p->title.' ['.$p->playerTeam.']</strong> should be possible.';
          }
        }
        $out .= '</ul>';
        break;
      case 'add-death' :
        if ($selectedPlayer) {
          $allEvents = $selectedPlayer->get("name=history")->children()->sort("date");
          $death = $pages->get("name=death");
          $comment = 'Player died.';
          $eventId = $confirm; // urlSegment3 used for eventId
          $e = $pages->get("id=$eventId");
          $deathDate = date($e->date+1);
          $linkedId = saveHistory($selectedPlayer, $death, $comment, 0, '', $deathDate, '');
          // Move all day events a few seconds later
          $dayEvents = $allEvents->find("date=$e->date")->not($e);
          $seconds = 5;
          foreach($dayEvents as $d) {
            $d->date = date($e->date + $seconds); 
            $seconds = $seconds + 1;
            $d->of(false);
            $d->save();
          }
          // Each team member suffers from player's death
          if ($deathDate > mktime(date('04/10/2016 00:00:00'))) {
            $teamDeath = $pages->get("name=team-death");
            $teamPlayers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam")->not("group=$selectedPlayer->group");
            foreach($teamPlayers as $p) {
              $comment = 'Team member died!';
              saveHistory($p, $teamDeath, $comment, 0, '', $deathDate, $linkedId);
            }
            // Each group member suffers from player's death
            $groupMembers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam, group=$selectedPlayer->group")->not("$selectedPlayer");
            $groupDeath = $pages->get("name=group-death");
            foreach($groupMembers as $p) {
              $comment = 'Group member died!';
              saveHistory($p, $groupDeath, $comment, 0, '', $deathDate, $linkedId);
            }
          }
        }
        break;
      case 'helmet' :
        $helmet = $pages->get("name=memory-helmet");
        $out .= 'Total # of players : '.$allPlayers->count();
        $out .= '<ul>';
        foreach($allPlayers as $p) {
          $p->of(false);
          $out .= '<li>'.$p->title.' ['.$p->playerTeam.']</li>';
          $out .= '<li>';
          if ($p->equipment->has("name=memory-helmet")) { // Player has Memory helmet in equipment
            // Check if event exists in History
            $event = $p->get("name=history")->children("task.name=buy|bought, refPage.name=memory-helmet")->sort("date")->last();
            if ($event->id) {
              // Search if player died after buying the helmet
              $eDate = $event->date;
              $death = $p->get("name=history")->child("task.name=death, date>=$eDate");
              if ($death->id) {
                $out .= '<span class="label label-danger">Error</span> (Last bought on ';
                $out .= strftime("%d/%m", $event->date);
                $out .= ') (Last death on '.strftime("%d/%m", $death->date).')';
                $out .= '  <button class="remove btn btn-xs btn-warning" data-href="'.$page->url.'" data-itemId="'.$helmet->id.'" data-playerId="'.$p->id.'" data-action="remove-equipment">Remove from equipment</button>';
              } else {
                $out .= '<span class="label label-success">OK</span> (Last bought on ';
                $out .= strftime("%d/%m", $event->date);
                $out .= ') (No death after this date)';
              }
            } else { // No Memory helmet in equipment
              $out .= '<span class="label label-danger">Error</span> Memory helmet in equipment, but not present in History!';
              $dirty = true;
              // Find if Helmet event exists in the group
              $members = $allPlayers->find("playerTeam=$p->playerTeam, group=$p->group");
              foreach($members as $m) {
                $boughtHelmet = $m->get("name=history")->child("template=event, task.name=buy, refPage.name=memory-helmet");
                if ($boughtHelmet->id) {
                  $source = clone $boughtHelmet;
                }
              }
              // Copy the event on the same date
              if ($source->id) {
                $out .= ' Copy from '.strftime("%d/%m", $source->date).'?';
              }
              if ($confirm == 1) { // Create new event in History;
                // Find if Helmet event exists in the group
                $members = $allPlayers->find("playerTeam=$p->playerTeam, group=$p->group");
                foreach($members as $m) {
                  $boughtHelmet = $m->get("name=history")->child("template=event, task.name=buy, refPage.name=memory-helmet");
                  if ($boughtHelmet->id) {
                    $source = clone $boughtHelmet;
                  }
                }
                // Copy the event on the same date
                if ($source->id) {
                  $buy = $pages->get("template=task, name=buy");
                  $eDate = $source->date;
                  $comment = "Memory helmet [unlocked]";
                  foreach($members as $m) {
                    $boughtHelmet = $m->get("name=history")->child("template=event, task.name=buy, refPage.name=memory-helmet");
                    if ($boughtHelmet->id == '') {
                      saveHistory($m, $buy, $comment, 0, $helmet, $eDate, '');
                    }

                  }
                }
              }
            }
          } else { // Memory helmet not in equipment
            // Let's check History
            $event = $p->get("name=history")->children("task.name=buy, refPage.name=memory-helmet")->sort("date")->last();
            if ($event->id) {
              // Search if player died after buying the helmet
              $eDate = $event->date;
              $death = $p->get("name=history")->child("task.name=death, date>=$eDate");
              if ($death->id) {
                $out .= '<span class="label label-success">OK</span>';
                $out .= ' (Last bought on '.strftime("%d/%m", $event->date).') (Last death on '.strftime("%d/%m", $death->date).')';
              } else {
                $out .= '<span class="label label-danger">Error</span>';
                $out .= '(Bought on '.strftime("%d/%m", $event->date).') (No death after this date)';
                $dirty = true;
              }
              if ($confirm == 1) {
                // Add to equipment;
                //$p->of(false);
                //$p->equipment->add($helmet);
                //$p->save();
              }
            } else {
              $out .= '<span class="label label-success">OK</span> (no Memory Helmet at all)';
            }
          }
          $out .= '</li>';
        }
        $out .= '</ul>';
        if ($dirty && !$input->urlSegment3 && $input->urlSegment3 != 1) {
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'helmet/all/1">Clean now!</button>';
        } else {
          $out .= '<p>Memory helmets seem to be clean.</p>';
        }
        break;
      case 'ut' :
        $dirty = false;
        $allMonsters = $pages->find("template=exercise")->sort("level, title");
        $out .= '<h3>Best players among '.$allPlayers->count().' players.</h3>';
        $out .= '<ul>';
        foreach($allMonsters as $m) {
          $bestUt = $m->best;
          $out .= '<li>'.$m->title.' [Current best : '.$m->mostTrained->title.' ['.$m->mostTrained->playerTeam.'] : '.$bestUt.']';
          foreach($allPlayers as $p) {
            $playerUt = utGain($m, $p);
            $p->ut = $playerUt;
          }
          $allPlayers->sort("-ut");
          if ($allPlayers->first()->ut != $m->best) {
            $out .= ' <span class="label label-danger">Error</span>';
            $out .= ' - New best : '.$allPlayers->first()->title.' ['.$allPlayers->first()->playerTeam.'] ⇒'.$allPlayers->first()->ut;
            if ($confirm == 1) { // Save new best players
              $m->of(false);
              $m->mostTrained = $allPlayers->first();
              $m->best = $allPlayers->first()->ut;
              $m->save();
            }
          } else {
            $dirty = true;
            $out .= ' <span class="label label-success">OK</span>';
          }
          $out .= '</li>';
        }
        $out .= '</ul>';
        if ($dirty) {
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'ut/all/1">Save now!</button>';
        }
        break;
      case 'clean-history' :
        if ($selectedPlayer) {
          $allEvents = $selectedPlayer->get("name=history")->children()->sort(date);
        }
        if ($selectedAll) {
          $allEvents = $pages->find("template=event")->sort('-date');
        }
        $out = 'Clean '.$allEvents->count.' events.';
        $out .= '<ul>';
        foreach($allEvents as $e) {
          $out .= '<li>';
          $out .= $e->title.'­→ ';
          preg_match("/\s.?\[.*\]/", $e->title, $matches);
          if ($matches[0]) {
            $dirty = true;
            $title = preg_replace("/(.*)(\s.?\[.*\])/", "$1", $e->title);
            $out .= $title;
            if ($input->urlSegment3 && $input->urlSegment3 == 1) {
              $e->of(false);
              $e->title = $title;
              $e->save();
            }
          } else {
            $out .= '<span class="label label-success">OK</span>';
          }
          // Direct link to manually edit page
          $out .= ' <a class="btn btn-xs btn-primary" href="'.$config->urls->admin.'page/edit/?id='.$e->id.'" target="_blank">Edit page in Backend</a>';
          $out .= '</li>';
        }
        $out .= '</ul>';
        $out .= '<br /><br />';
        if ($dirty && !$input->urlSegment3 && $input->urlSegment3 != 1) {
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'clean-history/'.$playerId.'/1">Clean now!</button>';
        } else {
          $out .= '<p>Titles seem to be clean.</p>';
        }
        break;
      case 'recalculate' :
        if ($selectedPlayer) {
          $allEvents = $selectedPlayer->get("name=history")->children()->sort("date, created");
          $out = '<h3>'.$selectedPlayer->title.' ['.$selectedPlayer->playerTeam.'] → Recalculate scores from complete history ('. $allEvents->count.' events).</h3>';
          // Keep initial scores for comparison
          $initialPlayer = clone $selectedPlayer;
          // Init scores
          $selectedPlayer = initPlayer($selectedPlayer);
          $out .= displayPlayerScores($selectedPlayer);
          $out .= '<table class="table table-condensed table-hover">';
          foreach($allEvents as $e) {
            // Keep previous values to check evolution
            $oldPlayer = clone $selectedPlayer;
            $out .= '<tbody>';
            $out .= '<tr><td class="text-left">';
            $out .= '▶ '.strftime("%d/%m", $e->date).' - ';
            $out .= $e->title;
            $comment = trim($e->summary);
            if ($comment) {
              $out .= ' ['.$comment.']';
            }
            if ($e->refPage) {
              $out .= ' ['.$e->refPage->title.']';
            }
            if ($e->task) {
              if ($e->task->is("name=donation")) { // Player gave GC, increase his Donation
                preg_match("/\d+/", $comment, $matches);
                $diff = $selectedPlayer->GC - $matches[0];
                if ($diff < 0) { // Check for Donation bug
                  $out .= ' <span class="label label-danger">Error';
                  $out .= ' ⇒ Amount replaced : '.$selectedPlayer->GC.' [Edit and reload]';
                  $out .= '</span>';
                  $comment = preg_replace("/\d+/", $selectedPlayer->GC, $comment, 1);
                  $dirty = true;
                  /* // Change summary if saved */
                  /* if ($input->urlSegment3 && $input->urlSegment3 == 1) { */
                  /*   $e->summary = $comment; */
                  /*   $e->of(false); */
                  /*   $e->save(); */
                  /* } */
                }
              }
              if ($e->task->is("name=buy|free|bought")) { // New equipment, place or potion, add it accordingly
                if ($e->refPage->GC > $selectedPlayer->GC && $e->task->is("name!=bought")) {
                  $out .= ' <span class="label label-danger">Error : Not enough GC ('.$e->refPage->GC.' needed, '.$selectedPlayer->GC.' available).</span>';
                  $dirty = true;
                }
                if ($e->refPage->level > $selectedPlayer->level) { // Check for Buy/Free bug (if a Death occurred, for example)
                  $out .= ' <span class="label label-danger">Error : Wrong level.</span>';
                  $dirty = true;
                }
                // Get item's data
                if ($e->refPage) {
                  $newItem = $pages->get("$e->refPage");
                  if ($newItem->parent->is("name=group-items")) {
                      // Check if group members have [unlocked] item
                      $members = $allPlayers->find("playerTeam=$selectedPlayer->playerTeam, group=$selectedPlayer->group");
                      $out .= '<ul class="list-inline">';
                      $out .= '<li>Group item status → </li>';
                      $boughtNb = 0;
                      foreach ($members as $p) {
                        $bought = $p->get("name=history")->get("task.name=bought, refPage=$newItem, summary*=[unlocked]");
                        if ($bought->id) {
                          $boughtNb++;
                          $out .= '<li><span class="label label-success">'.$p->title.' : [unlocked]</span></li>';
                        } else {
                          $out .= '<li><span class="label label-danger">'.$p->title.' : [buy]</span></li>';
                        }
                      }
                      if ($boughtNb != $members->count-1) {
                        $dirty = true;
                        $out .= '<li><span class="label label-danger"> ⇒ Error : Check [unlocked] status in the group</span></li>';
                      }
                      // [unlocked] or [bought] ?
                      // task page should be set accordingly but prevention here for backward compatibility
                      preg_match("/\[unlocked\]/", $comment, $matches);
                      if ($matches[0] && $e->task->is("name=buy")) {
                        $dirty = true;
                        $out .= ' <span class="label label-danger">Error : [unlocked] found, but task page set to "Buy" instead of "Bought".</span>';
                      }
                      if (!$matches[0] && $e->task->is("name=bought")) {
                        $dirty = true;
                        $out .= ' <span class="label label-danger">Error : [unlocked] NOT found, but task page set to "Bought" instead of "Buy".</span>';
                      }
                      $out .= '</ul>';
                  }
                }
              }
              if ($e->task->is("name=team-death|group-death")) {
                // Find who died
                $teamPlayers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam");
                foreach($teamPlayers as $p) {
                  $dead = $p->get("name=history")->get("template=event, task.name=death,date=$e->date");
                  if ($dead->id) {
                    $deadPlayer = $dead->parent("template=player");
                    $out .= ' ['.$deadPlayer->title.']';
                  }
                }
              }
              updateScore($selectedPlayer, $e->task, $comment, $e->refPage, '', false);
              // Test if player died
              if ($selectedPlayer->HP == 0) {
                $died = true;
                if ($allEvents->getNext($e)->task->name == 'death') {
                  $out .= '<span class="label label-success">Death OK</span>';
                } else {
                  $dirty = true;
                  // Ask only for the first Death
                  if ($unique == true) {
                    $out .= '<span class="label label-danger">Error : Death</span>  ';
                    // Button Add death here
                    $out .= '<button class="death btn btn-danger" data-href="'.$page->url.'add-death/'.$playerId.'/'.$e->id.'">Add death here?</button>';
                    $unique = false;
                  } else {
                    $out .= '<span class="label label-danger">Previous Death?</span>';
                  }
                }
              }
              $out .= '<br />';
              $out .= displayTrendScores($selectedPlayer, $oldPlayer);
              $out .= displayPlayerScores($selectedPlayer);
              $out .= '  ';
              // Direct link to manually edit page
              $out .= ' <a class="btn btn-xs btn-primary" href="'.$config->urls->admin.'page/edit/?id='.$e->id.'" target="_blank">Edit page in Backend</a>';
              // Delete event link
              $out .= '  <button class="delete btn btn-xs btn-danger" data-href="'.$page->url.'" data-playerId="'.$selectedPlayer->id.'" data-eventId="'.$e->id.'" data-action="trash">Delete</button>';
            }
            $out .='</td></tr>';
          }
          $out .= '</tbody>';
          $out .= '</table>';
          $out .= displayPlayerScores($initialPlayer, 'previous');
          $out .= '<br />';
          $out .= displayPlayerScores($selectedPlayer);
          $out .= '<br /><br />';
          if ($dirty) {
            $out .= '<h4><span class="label label-danger">Error detected! You should check history before saving anything !</span></h4>';
          }
          if ($input->urlSegment3 && $input->urlSegment3 == 1) {
            $selectedPlayer->of(false);
            $selectedPlayer->save();
            /* $out .= '<div class="well">New scores saved !</div>'; */
          } else {
            $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'recalculate/'.$playerId.'/1">Recalculate scores</button>';
          }
        } else {
          $out .= 'You need to select 1 player.';
        }
        break;
      case 'remove-equipment' :
        $item = $pages->get($input->urlSegment3);
        $player = $pages->get($playerId);
        $player->of(false);
        $player->equipment->remove($item);
        $player->save();
        break;
      case 'trash' :
        $event = $pages->get($confirm); // urlSegment3 used for eventId
        $pages->trash($event);
        // Delete team and group damage if needed (death)
        if ($event->task->is("name=death")) {
          $linkedDeath = $pages->find("template=event, linkedId=$event->id");
          foreach($linkedDeath as $p) {
            $pages->trash($p);
          }
          /* $teamPlayers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam"); */
          /* foreach($teamPlayers as $p) { */
          /*   $linkedDeath = $p->get("name=history")->get("template=event, task.name=group-death|team-death, date=$event->date"); */
          /*   if ($linkedDeath->id) { */
          /*     $pages->trash($linkedDeath); */
          /*   } */
          /* } */
        }
        break;
      case 'ut-stats' :
        if ($selectedPlayer) {
          $out .= '<h3>';
          $out .= 'UT Stats for '.$selectedPlayer->title.' ['.$selectedPlayer->playerTeam.']   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          /* $allEvents = $selectedPlayer->get("name=history")->find("task.name=ut-action-v|ut-action-vv,date>$start,date<$end")->sort("date"); */
          $allMonsters = $pages->find("template=exercise")->sort("level, title");
          foreach($allMonsters as $m) {
            $playerUt = utGain($m, $selectedPlayer, $startDate, $endDate);
            if ($playerUt > 0) {
              $out .= $m->title.' [Level '.$m->level.'] → ';
              $out .= $playerUt.' UT';
              $out .= '<br />';
            }
          }
          /* if ($allEvents->count() > 0) { */
          /*   foreach($allEvents as $e) { */
          /*     $out .= strftime("%d/%m", $e->date); */
          /*     $out .= ' : '; */
          /*     $out .= $e->summary.'<br />'; */
          /*   } */
          /* } else { */
          /*   $out .= '<p>No training yet.</p>'; */
          /* } */
        } else if ($selectedTeam && $selectedTeam != '-1') {
          $out .= '<h3 class="text-center">';
          $out .= 'UT Stats for '.$selectedTeam .'   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          $allMonsters = $pages->find("template=exercise")->sort("level, title");
          $allPlayers = $allPlayers->find("playerTeam=$selectedTeam");
          $out .= '<ul>';
          foreach($allPlayers as $p) {
            $activity = 0;
            $out_03 = '<ul>';
            foreach($allMonsters as $m) {
              $playerUt = utGain($m, $p, $startDate, $endDate);
              if ($playerUt > 0) { 
                $activity += $playerUt;
                $out_03 .= '<li>'.$m->title. ' [level '.$m->level.']: +'.$playerUt.'UT</li>';
              }
            }
            $out_03 .= '</ul>';
            $out_02 = '<li><strong>'.$p->title.'</strong> → <span class="label label-success">+'.$activity.'UT</span></li>';
            if ($activity != 0) {
              $out .= $out_02.$out_03;
            }
          }
          $out .= '</ul>';
        } else {
          $out .= 'You need to select 1 player or 1 team.';
        }
        break;
      case 'task-report' :
        $taskId = $input->get['taskId'];
        $task = $pages->get("id=$taskId");
        $taskCount = 0;
        if ($selectedTeam && $selectedTeam != '-1' && $taskId!= -1) {
          $out .= '<h3 class="text-center">';
          $out .= '['.$task->title.'] report for '.$selectedTeam .'   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          $allPlayers = $allPlayers->find("playerTeam=$selectedTeam");
          $out .= '<ul>';
          foreach($allPlayers as $p) {
            $prevTask = $p->find("template=event,task=$task, date>$startDate, date<$endDate, sort=-date");
            if ($prevTask->count() > 0) {
              $taskCount += $prevTask->count();
              $out .= '<li>'.$p->title. ': Task found '.$prevTask->count().' time(s).</li>';
            }
          }
          $out .= '</ul>';
          $out .= '<p class="label label-primary">Total count : '.$taskCount.'</p>';
        } else {
          $out .= 'You need to select a team and a task.';
        }
        break;
      default :
        $out = 'Problem detected.';
    }

    $out .= '<script>';
    $out .= '$(".delete").click( function() { var eventId=$(this).attr("data-eventId"); var action=$(this).attr("data-action"); var playerId=$(this).attr("data-playerId"); var href=$(this).attr("data-href") + action +"/"+ playerId +"/"+ eventId; var that=$(this).parents("tr"); if (confirm("Delete event?")) {$.get(href, function(data) { that.hide(); $("button[data-action=recalculate]").click(); }) };});';
    $out .= '$(".remove").click( function() { var itemId=$(this).attr("data-itemId"); var action = $(this).attr("data-action"); var playerId=$(this).attr("data-playerId"); var href=$(this).attr("data-href") + action +"/"+ playerId +"/"+ itemId; var that=$(this).parents("li"); if (confirm("Remove item?")) {$.get(href, function(data) { that.hide(); }) };});';
    $out .= '$(".confirm").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Saved!"); $("button[data-action=recalculate]").click(); }) };});';
    $out .= '$(".death").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Please reload!"); $("button[data-action=recalculate]").click();}) };});';
    $out .= '</script>';

    echo $out;
  }
?>
