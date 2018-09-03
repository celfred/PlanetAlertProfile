<?php namespace ProcessWire;
 
  if (!$config->ajax) {
    include("./head.inc"); 
    $out = '';
    if ($user->isSuperuser() || $user->hasRole('teacher')) {
      $action = $input->urlSegment1;
      if ($user->hasRole('teacher')) {
        $allTeams = $pages->find("template=team, teacher=$user")->sort("title");
        $allPlayers = $pages->find("parent.name=players, template=player, team.teacher=$user");
      } else {
        $allTeams = $pages->find("template=team")->sort("title");
        $allPlayers = $pages->find("parent.name=players, template=player");
      }
      $allPlayers->sort("team.name, title");
      $out .= '<div class="alert alert-warning text-center">'.__("Admin Actions : Be VERY careful !").'</div>';
      switch ($action) {
        case 'ut' :
          if ($user->isSuperuser()) {
            $out .= '<p>⇒ Check if UT\'s hall of fame is correct.</p>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="ut">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'ut-stats' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<section class="well">';
            $out .= '<p>Select limiting dates if needed (start empty = 01/01/2000, end empty = today). <span class="label label-danger">English format dates!</span></p>';
            $out .= '<label for="startDate">From (mm/dd/yyyy) : </label>';
            $out .= '<input id="startDate" name="startDate" type="text" size="10" value="" />  ';
            $out .= '<label for="endDate">To (mm/dd/yyyy) : </label>';
            $out .= '<input id="endDate" name="endDate" type="text" size="10" value="" />';
            $out .= '</section>';
            $out .= '</section>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="ut-stats">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'fights-stats' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<section class="well">';
            $out .= '<p>Select limiting dates if needed (start empty = 01/01/2000, end empty = today). <span class="label label-danger">English format dates!</span></p>';
            $out .= '<label for="startDate">From (mm/dd/yyyy) : </label>';
            $out .= '<input id="startDate" name="startDate" type="text" size="10" value="" />  ';
            $out .= '<label for="endDate">To (mm/dd/yyyy) : </label>';
            $out .= '<input id="endDate" name="endDate" type="text" size="10" value="" />';
            $out .= '</section>';
            $out .= '</section>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="fights-stats">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'task-report' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '<span>Select a task : </span>';
            $out .= '<select id="taskId">';
            $out .= '<option value="-1">Select a task</option>';
            $allTasks = $pages->find("template=task, include=hidden, sort=title");
            foreach($allTasks as $t) {
              $out .= '<option value="'.$t.'">'.$t->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<section class="well">';
            $out .= '<p>Select limiting dates if needed (start empty = 01/01/2000, end empty = today). <span class="label label-danger">English format dates!</span></p>';
            $out .= '<label for="startDate">From (mm/dd/yyyy) : </label>';
            $out .= '<input id="startDate" name="startDate" type="text" size="10" value="" />  ';
            $out .= '<label for="endDate">To (mm/dd/yyyy) : </label>';
            $out .= '<input id="endDate" name="endDate" type="text" size="10" value="" />';
            $out .= '</section>';
            $out .= '</section>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="task-report">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'reports' :
          $out .= '<section class="well">';
          if ($user->isSuperuser()) { // Old version, ALL teams
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="reports">Generate</button>';
          } else { // Quick buttons for teacher's teams
            $out .= '<ul class="list list-inline">';
            foreach($allTeams as $p) {
              $out .= '<li>';
              $out .= '<a href="'.$page->url.'reports/'.$p->id.'?type=team" class="teamOption btn btn-success">'.$p->title.'</a>';
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          $out .= '</section>';
          $out .= '<section id="ajaxViewport" class="well"></section>';
          break;
        case 'recalculate' :
          if ($user->isSuperuser()) {
            $playerId = $input->urlSegment2;
            $selectedPlayer = $pages->get($playerId);
            $headTeacher = getHeadTeacher($selectedPlayer);
            if ($selectedPlayer) {
              $allEvents = $selectedPlayer->get("name=history")->children()->sort("date, created");
              $out = '<p class="alert alert-danger">ALL ACTIONS ARE RECALCULATED FROM LATEST VALUES (MAY CAUSE CHANGES IF VALUES HAVE BEEN MODIFIED)</p>';
              $out .= '<h3 class="text-center">'.$selectedPlayer->title.' ['.$selectedPlayer->team->title.'] → Recalculate scores from complete history ('. $allEvents->count.' events).</h3>';
              // Keep initial scores for comparison
              $initialPlayer = clone $selectedPlayer;
              // Init scores
              $selectedPlayer = initPlayer($selectedPlayer);
              $out .= displayPlayerScores($selectedPlayer, 'reset', 'success');
              $out .= '<table class="table table-condensed table-hover">';
              $out .= '<tbody>';
              foreach($allEvents as $e) {
                // Keep previous values to check evolution
                $oldPlayer = clone $selectedPlayer;
                if ($selectedPlayer->coma == 1) {
                  $comaClass = 'bg-warning';
                } else {
                  $comaClass = '';
                }
                $out .= '<tr><td class="text-left '.$comaClass.'">';
                if ($e->date != '') {
                  $out .= '▶ '.strftime("%d/%m", $e->date).' - ';
                } else {
                  $out .= '▶ <span class="label label-danger">Date error!</span> - ';
                  $dirty = true;
                }
                $out .= $e->title;
                $comment = trim($e->summary);
                if ($comment) {
                  $out .= ' [comment:'.$comment.']';
                }
                if ($e->refPage) {
                  $out .= ' [refPage:'.$e->refPage->title.']';
                }
                if ($e->linkedId) {
                  $out .= ' [linkedId:'.$e->linkedId.']';
                }
                $out .= $e->feel();
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
                  if ($e->task->is("name=wrong-invasion")) { // 3rd wrong invasion ?
                    $wrongInvasions = $allEvents->find("task.name=wrong-invasion, refPage=$e->refPage, date<=$e->date, sort=-date");
                    // Limit to events AFTER the last 'Free' action on the oarticular element
                    $lastFree = $allEvents->get("task.name=free, refPage=$e->refPage, sort=-date");
                    if ($lastFree->id) {
                      $wrongInvasions = $wrongInvasions->find("date>=$lastFree->date");
                    }
                    if ($wrongInvasions->count() >= 3) {
                      if ($allEvents->getNext($e) && $allEvents->getNext($e)->task->name == 'remove') {
                        $out .= '<span class="label label-success">Remove OK</span>';
                      } else {
                        $dirty = true;
                        $out .= '<span class="label label-danger">Remove Error (3rd wrong invasion)</span>';
                        // Button Add Remove action here
                        $out .= '<button class="death btn btn-danger" data-href="'.$page->url.'add-remove/'.$playerId.'/'.$e->id.'/'.$e->refPage->id.'">Add Remove action here?</button>';
                      }
                    } else {
                      if ($allEvents->getNext($e) && $allEvents->getNext($e)->task->name == 'remove') {
                        $dirty = true;
                        $out .= '<span class="label label-danger">Error : Remove found but not 3 wrong invasion(s) before ? ('.$wrongInvasions->count().'?)</span>';
                      }
                    }
                  }
                  if ($e->task->is("name=buy|free|bought")) { // New equipment, place, people or potion, add it accordingly
                    if ($e->refPage != false) {
                      // Get item's data
                      $newItem = $pages->get("$e->refPage");
                      if ($newItem->GC > $selectedPlayer->GC && $e->task->is("name!=bought")) {
                        $out .= ' <span class="label label-danger">Error : Not enough GC ('.$e->refPage->GC.' needed, '.$selectedPlayer->GC.' available).</span>';
                        $dirty = true;
                      }
                      if ($newItem->level > $selectedPlayer->level) { // Check for Buy/Free bug (if a Death occurred, for example)
                        $out .= ' <span class="label label-danger">Error : Wrong level.</span>';
                        $dirty = true;
                      }
                      if ($newItem->parent->is("name=group-items")) {
                        // Check if groups have been set
                        $out .= '<ul class="list-inline">';
                        $out .= '<li>Group item status → </li>';
                        if ($selectedPlayer->group != '') {
                          // Check if group members have [unlocked] item
                          $members = $allPlayers->find("team=$selectedPlayer->team, group=$selectedPlayer->group");
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
                          if (isset($matches[0]) && $e->task->is("name=buy")) {
                            $dirty = true;
                            $out .= ' <span class="label label-danger">Error : [unlocked] found, but task page set to "Buy" instead of "Bought".</span>';
                          }
                          if (!isset($matches[0]) && $e->task->is("name=bought")) {
                            $dirty = true;
                            $out .= ' <span class="label label-danger">Error : [unlocked] NOT found, but task page set to "Bought" instead of "Buy".</span>';
                          }
                        } else { // No groups, item shoudn't be there
                            $dirty = true;
                            $out .= ' <span class="label label-danger">Error : No groups set. Item shouldn\'t be there.</span>';
                        }
                        $out .= '</ul>';
                      }

                      if ($newItem->name == 'health-potion' && $selectedPlayer->coma == 1) {
                        $selectedPlayer->coma = 0;
                        $out .= '<li><span class="label label-success">Leaving COMA STATE !</span></li>';
                      }
                    }
                    if ($e->linkedId) {
                      $discount = $pages->get("$e->linkedId");
                      if ($discount->id && $discount->parent->is("name=specials")) {
                        $out .= ' ['.$discount->title.'% discount]';
                      }
                    }
                  }
                  if ($e->task->is("name=death")) {
                    $lastDeath = $e;
                    // Set previousLevel
                    preg_match("/\d+/", $e->summary, $matches);
                    $previousLevel = (int) $matches[0];
                    if ($previousLevel == 0) {
                      if ($selectedPlayer->level>1) {
                        $previousLevel = $selectedPlayer->level;
                      } else {
                        $previousLevel = 0;
                      }
                      $out .= ' <span class="label label-danger">Error : No former level, set to '.$previousLevel.'</span>';
                      $dirty = true;
                    }
                    // Death recorded but HP>0
                    if ($selectedPlayer->HP > 0) {
                      $out .= ' <span class="label label-danger">Error → HP>0 ?</span>';
                      $dirty = true;
                    }
                  }
                  if ($e->task->is("name=team-death|group-death|death")) {
                    if ($e->linkedId) {
                      $out .= ' ['.$e->linkedId.']';
                    }
                  }
                  if ($e->inClass == 1 && $e->task->is("name~=fight|ut-action")) {
                    $out .= ' [in class]';
                  }
                  if ($e->refPage != false && $e->refPage->is("name=memory-potion") && $e->linkedId == 0) {
                    $used = $allEvents->get("linkedId=$e->id");
                    if (isset($used->id)) {
                      $out .= ' [used on '.date('d/m', $used->date).' ?]';
                    } else {
                      $out .= ' <span class="label label-danger">Not used ?</span>';
                    }
                  }
                  $e->task->comment = $comment;
                  $e->task->refPage = $e->refPage;
                  $e->task->linkedId = $e->linkedId;
                  updateScore($selectedPlayer, $e->task, false);
                  if ($e->task->is("name=death")) {
                    $prevDeath = $allEvents->find('task.name=death')->getPrev($e);
                    if (isset($prevDeath)) {
                      // Set prevDeathLevel
                      preg_match("/\d+/", $prevDeath->summary, $matches);
                      $prevDeathLevel = (int) $matches[0];
                      $out .= 'PrevDeath:'.$prevDeathLevel;
                    }
                    resetPlayer($selectedPlayer, $prevDeathLevel);
                    if ($selectedPlayer->coma == 1) {
                      $out .= '<span class="label label-danger">Entering COMA STATE !</span>';
                    } 
                  }
                  $out .= '<br />';
                  // Show if teacher has customized task
                  if ($e->task->mod) { $out .= '<span class="label label-danger"><i class="glyphicon glyphicon-warning-sign"></i></span>'; }
                  $out .= displayTrendScores($selectedPlayer, $oldPlayer);
                  $out .= displayPlayerScores($selectedPlayer, 'new', 'success');
                  $out .= '  ';
                  // Test if player died
                  if ($selectedPlayer->HP == 0 && $e->task->is("name!=death")) {
                    if ($allEvents->getNext($e) && $allEvents->getNext($e)->task->name == 'death') {
                      $out .= '<span class="label label-success">Death OK</span>';
                    } else {
                      $dirty = true;
                      // Ask only for the first Death
                      if ($unique == true) {
                        $out .= '<span class="label label-danger">Error : No Death after?</span>  ';
                        // Button Add death here
                        $out .= '<button class="death btn btn-danger" data-href="'.$page->url.'add-death/'.$playerId.'/'.$e->id.'/'.$previousLevel.'">Add death here?</button>';
                        $unique = false;
                      } else {
                        $out .= '<span class="label label-danger">Previous Death?</span>';
                        $out .= '<button class="death btn btn-danger" data-href="'.$page->url.'add-death/'.$playerId.'/'.$e->id.'/'.$previousLevel.'">Add death here?</button>';
                      }
                    }
                  }
                }
                $out .='</td></tr>';
              }
              $out .= '</tbody>';
              $out .= '</table>';
              $out .= displayPlayerScores($initialPlayer, 'actual', 'danger');
              $out .= '<br />';
              $out .= '<h3>'.displayPlayerScores($selectedPlayer, 'Recalculated', 'primary').'</h3>';
              // Check changes
              $previousFingerprint = md5(displayPlayerScores($initialPlayer, 'actual', 'danger'));
              $recalculatedFingerprint = md5(displayPlayerScores($selectedPlayer, 'actual', 'danger'));
              if ($previousFingerprint === $recalculatedFingerprint) { 
                $out .= '<h4 class="text-center"><span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i> Scores are identical.</span></h4>';
              } else {
                $out .= '<h4 class="text-center"><span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i> Scores are different !</span></h4>';
              }
              $out .= '<br /><br />';
              if (isset($dirty)) {
                $out .= '<h4><span class="label label-danger">Error detected! You should check history before saving anything !</span></h4>';
              }
              if ($previousFingerprint !== $recalculatedFingerprint) { 
                $out .= '<button class="basicConfirm btn btn-block btn-primary" data-href="'.$page->url.'recalculate/'.$playerId.'/1" data-reload="true">Save recalculated scores</button>';
              }
              if ($input->urlSegment3 && $input->urlSegment3 == 1) {
                if ($selectedPlayer->team && $selectedPlayer->team->name != 'no-team') {
                  if ($selectedPlayer->team->periods) {
                    $currentPeriod = $selectedPlayer->team->periods;
                    $mod = $currentPeriod->periodOwner->get("singleTeacher=$headTeacher"); // Get personalized infos if needed
                    if ($mod->id) {
                      $mod->dateStart != '' ? $dateStart = $mod->dateStart : $dateStart = $currentPeriod->dateStart;
                      $mod->dateEnd != '' ? $dateEnd = $mod->dateEnd : $dateEnd = $currentPeriod->dateEnd;
                    }
                    $newCount = setHomework($selectedPlayer, $dateStart, $dateEnd);
                    $lastPenalty = $allEvents->find("task.category.name=homework, date>=$dateStart, date<=$dateEnd")->sort("-date")->first(); // Get last Hk pb
                    if ($lastPenalty && $lastPenalty->task->is("name=penalty")) {
                      $newCount = 0;
                    }
                    $selectedPlayer->hkcount = $newCount;
                  }
                }
                $selectedPlayer->of(false);
                $selectedPlayer->save();
              }
            } else {
              $out .= 'You need to select 1 player.';
            }
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'team-options' :
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('Team options');
          $out .= '</h3>';
          $out .= '<div>';
          if ($user->isSuperuser()) { // Old version, ALL teams
            $out .= '<span>'.__("Select a team").' : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">'.__("Select a team").'</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="team-options">'.__("Generate").'</button>';
          } else { // Quick buttons for teacher's teams
            $out .= '<ul class="list list-inline">';
            foreach($allTeams as $p) {
              $out .= '<li>';
              $out .= '<a href="'.$page->url.'team-options/'.$p->id.'?type=team" class="teamOption btn btn-success">'.$p->title.'</a>';
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          $out .= '</div>';
          if ($allTeams->count() > 0) {
            $out .= '<section id="ajaxViewport" class="well">'.__("Select a team.").'</section>';
          } else {
            $out .= '<p>'.__("You have no teams.").'</p>';
          }
          break;
        case 'manage-periods' :
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('Manage periods');
          $out .= '</h3>';
          if (!$user->isSuperuser()) {
            $out .= '<p class="text-center">'.__("Contact the administrator if you want to operate changes in this list.").'</p>';
          }
          $out .= '<div>';
          $allPeriods = $pages->get("name=periods")->children();
          $teacherPeriods = $allPeriods->find("periodOwner.singleTeacher=$user");
          $notTeacherPeriods = $allPeriods->find("periodOwner.singleTeacher!=$user");
          if (!$user->isSuperuser()) {
            $out .= '<h4><span>'.__("Your periods").'</span></h4>';
            $out .= '<ul id="teacherElements">';
            foreach($teacherPeriods as $p) {
              $out .= '<li>';
              $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
              $out .= '<span>'.$p->title.'</span> ';
              $mod = $p->periodOwner->get("singleTeacher=$user"); // Get personalized infos if needed
              $mod->dateStart != '' ? $out .= ' <span>('.__('From').' '.date("d/m/Y", $mod->dateStart).' ' : $out .= ' <span>('.__('From').' '.date("d/m/Y", $p->dateStart).' ';
              $mod->dateEnd != '' ? $out .= __('to').' '.date("d/m/Y", $mod->dateEnd).')</span>' : $out .= __('to').' '.date("d/m/Y", $p->dateEnd).')</span>';
              if ($user->isSuperuser()) {
                $out .= $p->feel(array(
                          "fields" => "title,dateStart,dateEnd"
                        ));
              } else {
                $out .= $mod->feel(array(
                          "text" => __('[Edit]'),
                          "fields" => "dateStart,dateEnd"
                        ));
              }
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          if ($user->isSuperuser()) {
            $out .= '<button class="confirm btn btn-primary btn-sm" data-href="'.$page->url.'bumpYear">'.__("+1 year to ALL periods").'</button> ';
            $out .= $pages->get("name=periods")->feel(array(
              'mode' => 'page-add',
              'text' => '[Add a new period]'
            ));
          }
          $out .= '<h4><span>'.__("Available periods").'</span></h4>';
          $out .= '<ul id="notTeacherElements">';
          foreach($notTeacherPeriods as $p) {
            $out .= '<li>';
            if (!$user->isSuperuser()) {
              $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
            }
            $out .= '<span>'.$p->title.'</span> ';
            $out .= ' <span>('.__('From').' '.date("d/m/Y", $p->dateStart).' ';
            $out .= __('to').' '.date("d/m/Y", $p->dateEnd).')</span>';
            if ($user->isSuperuser()) { $out .= $p->feel(); }
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          break;
        case 'manage-actions' :
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('Manage actions');
          $out .= '</h3>';
          $out .= '<div>';
          // Get actions
          $adminActions = $pages->find("name!=tasks, template=task, adminOnly=1")->sort("title");
          $notTeacherActions = $pages->find("name!=tasks, template=task, adminOnly=0")->not("owner.singleTeacher=$user")->sort("title");
          $teacherActions = $pages->find("name!=tasks, template=task, adminOnly=0, owner.singleTeacher=$user")->sort("title, owner.title");
          if (!$user->isSuperuser()) {
            $out .= '<h4><span>'.__("Your actions").'</span></h4>';
            $out .= '<ul id="teacherElements" class="list">';
            foreach($teacherActions as $p) {
              $out .= '<li>';
              $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
              $mod = $p->owner->get("singleTeacher=$user"); // Get personalized infos if needed
              $mod->title != '' ? $out .= '<span>'.$mod->title.'</span> ' : $out .= '<span>'.$p->title.'</span> ';
              $mod->teacherTitle != '' ? $out .= '<span>[= '.$mod->teacherTitle.']</span> → ' : '';
              if ($mod->teacherTitle == '' && $task->teacherTitle != '') { $out .= '<span>[= '.$p->teacherTitle.']</span> → '; }
              $mod->summary != '' ? $out .= '<span>'.$mod->summary.'</span> ' : $out .= '<span>'.$p->summary.'</span> ';
              $mod->HP == '' ? $HP = $p->HP : $HP = $mod->HP;
              $mod->XP == '' ? $XP = $p->XP : $XP = $mod->XP;
              $mod->GC == '' ? $GC = $p->GC : $GC = $mod->GC;
              $out .= '<span class="label label-danger">'.$HP.'HP</span> ';
              $out .= '<span class="label label-danger">'.$GC.'GC</span> ';
              $out .= '<span class="label label-danger">'.$XP.'XP</span> ';
              $out .= '<span class="label label-info">'.$p->category->title.'</span> ';
              $out .= $mod->feel(array(
                          "text" => __('[Edit]'),
                          "fields" => "title,teacherTitle,summary,HP,XP,GC",
                        ));
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          if ($user->isSuperuser()) {
            $out .= $pages->get("name=tasks")->feel(array(
              'mode' => 'page-add',
              'text' => '[Add a new action]'
            ));
          }
          $out .= '<h4><span>'.__("Available actions").'</span></h4>';
          $out .= '<ul id="notTeacherElements">';
          foreach($notTeacherActions as $p) {
            $out .= '<li>';
            if (!$user->isSuperuser()) {
              $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
            }
            $out .= '<span>'.$p->title.'</span> → ';
            $out .= '<span>'.$p->summary.'</span> ';
            $out .= '<span class="label label-danger">'.$p->HP.'HP</span> ';
            $out .= '<span class="label label-danger">'.$p->GC.'GC</span> ';
            $out .= '<span class="label label-danger">'.$p->XP.'XP</span> ';
            $out .= '<span class="label label-danger">'.$p->type.'</span> ';
            $out .= '<span class="label label-info">'.$p->category->title.'</span> ';
            if ($user->isSuperuser()) {
              $out .= $p->feel();
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '<hr />';
          $out .= '<h4><span>'.__("Planet Alert actions [for information]").'</span></h4>';
          $out .= '<ul id="adminActions">';
          foreach($adminActions as $p) {
            $out .= '<li>';
            $out .= '<span>'.$p->title.'</span> → ';
            $out .= '<span>'.$p->summary.'</span> ';
            $out .= '<span class="label label-danger">'.$p->HP.'HP</span> ';
            $out .= '<span class="label label-danger">'.$p->GC.'GC</span> ';
            $out .= '<span class="label label-danger">'.$p->XP.'XP</span> ';
            $out .= '<span class="label label-danger">'.$p->type.'</span> ';
            $out .= '<span class="label label-info">'.$p->category->title.'</span> ';
            if ($user->isSuperuser()) {
              $out .= $p->feel();
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          break;
        case 'manage-categories' :
          $allCategories = $pages->get("name=categories")->children("template=category")->sort('title');
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('View categories');
          $out .= '</h3>';
          if (!$user->isSuperuser()) {
            $out .= '<p class="text-center">'.__("Contact the administrator if you want to operate changes in this list.").'</p>';
          }
          $out .= '<div>';
          if ($user->isSuperuser()) {
            $out .= $pages->get("name=categories")->feel(array(
              'mode' => 'page-add',
              'text' => '[Add a new category]'
            ));
          }
          $out .= '<h4><span>'.__("All categories").'</span> '.__("[Categories are not editable]").'</h4>';
          $out .= '<ul>';
          foreach($allCategories as $p) {
            $out .= '<li>';
            $out .= '<span class="label label-primary">'.$p->title.'</span> → ';
            $out .= '<span>'.$p->summary.'</span>';
            if ($user->isSuperuser()) {
              $out .= $p->feel();
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          $allTopics = $pages->find("parent.name=topics, template=topic")->sort('title');
          $out .= '<h3 class="text-center">';
          $out .=   __('Manage topics');
          $out .= '</h3>';
          $out .= '<h4><span>'.__("All topics").'</span> ';
          $out .= $pages->get("name=topics")->feel(array(
            'mode' => 'page-add',
            'text' => '[Add a new topic]'
          ));
          $out .= '</h4>';
          $out .= '<ul>';
          foreach ($allTopics as $p) {
            $out .= '<li>'.$p->title;
            if ($p->summary != '') { $out .= ' → '.$p->summary; }
            $out .= $p->feel(); // Only if permission is set in backend
            $out .= '</li>';
          }
          $out .= '</ul>';
          break;
        case 'manage-lessons' :
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('Manage lessons');
          $out .= '</h3>';
          if (!$user->isSuperuser()) {
            $out .= '<p class="text-center">'.__("Contact the administrator if you want to delete items in this list.").'</p>';
          }
          $out .= $pages->get("name=book-knowledge")->feel(array(
            'mode' => 'page-add',
            'text' => __('[Add a new lesson]'),
            'class' => 'button'
          ));
          $out .= '<div>';
          if (!$user->isSuperuser()) {
            $notTeacherEl = $pages->find("template=lesson, teacher!=$user, created_users_id!=$user->id")->sort("title");
          } else {
            $notTeacherEl = $pages->find("template=lesson, include=all")->sort("title");
          }
          $teacherEl = $pages->find("template=lesson, (teacher=$user), (created_users_id=$user->id), include=all")->sort("title");
          if (!$user->isSuperuser()) {
            $out .= '<h4><span>'.__("Your lessons").'</span></h4>';
            $out .= '<ul id="teacherElements">';
            foreach($teacherEl as $p) {
              if ($p->created_users_id == $user->id || $user->name == 'flieutaud') { $userIsOwner = true; } else { $userIsOwner = false; }
              $out .= '<li>';
              if (!$userIsOwner) {
                $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
              }
              if ($p->isUnpublished()) {
                $out .= '<span class="strikeText">'.$p->title.'</span>';
                $out .= ' <a class="publishElement" href="'.$page->url.'publish-element/'.$user->id.'/'.$p->id.'?type=team">'.__('[Publish]').'</a>';
              } else {
                $out .= '<span>'.$p->title.'</span>';
              }
              if ($p->summary != '') {
                $out .= ' → <span>'.$p->summary.'</span> ';
              }
              if ($p->topic) {
                $out .= '<span class="label label-default">'.$p->topic->implode(', ', '{title}').'</span>';
              }
              if ($userIsOwner) {
                $out .= $p->feel(array(
                          "text" => __('[Edit]'),
                          "fields" => "title,topic,level,summary,body,images,task,linkedMonsters"
                        ));
              }
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          $out .= '<h4><span>'.__("Available lessons").'</span></h4>';
          $out .= '<ul id="notTeacherElements">';
          foreach($notTeacherEl as $p) {
            $out .= '<li>';
            if (!$user->isSuperuser()) {
              $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
            }
            $out .= '<span>'.$p->title.'</span> → ';
            $out .= '<span>'.$p->summary.'</span> ';
            if ($p->topic) {
              $out .= '<span class="label label-default">'.$p->topic->implode(', ', '{title}').'</span>';
            }
            $out .= ' <a href="'.$p->url.'" data-toggle="tooltip" title="'.__("See a preview").'" target="blank"><span class="glyphicon glyphicon-eye-open"></span></a> ';
            if ($user->isSuperuser() || $user->name == 'flieutaud') {
              $out .= $p->feel(array(
                        "text" => __('[Edit]'),
                        "fields" => "title,topic,level,summary,body,images,task,linkedMonsters"
                      ));
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          break;
        case 'manage-monsters' :
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('Manage monsters');
          $out .= '</h3>';
          if (!$user->isSuperuser()) {
            $out .= '<p class="text-center">'.__("Contact the administrator if you want to delete items in this list.").'</p>';
          }
          $out .= $pages->get("template=monsters")->feel(array(
            'mode' => 'page-add',
            'text' => __('[Add a new monster]'),
            'class' => 'button'
          ));
          $out .= '<div>';
          if (!$user->isSuperuser()) {
            $notTeacherEl = $pages->find("parent.name=monsters, template=exercise, teacher!=$user, created_users_id!=$user->id")->sort("title");
          } else {
            $notTeacherEl = $pages->find("parent.name=monsters, template=exercise, include=all")->sort("title");
          }
          $teacherEl = $pages->find("parent.name=monsters, template=exercise, (teacher=$user), (created_users_id=$user->id), include=all")->sort('title');
          if (!$user->isSuperuser()) {
            $out .= '<h4><span>'.__("Your monsters").'</span></h4>';
            $out .= '<ul id="teacherElements">';
            foreach($teacherEl as $p) {
              if ($p->created_users_id == $user->id) { $userIsOwner = true; } else { $userIsOwner = false; }
              $out .= '<li>';
              if (!$userIsOwner) {
                $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
              }
              if ($p->image) {
                $out .= '<img src="'.$p->image->getCrop("mini")->url.'" alt="Image" /> ';
              } else {
                $out .= '[-] ';
              }
              if ($p->isUnpublished()) {
                $out .= '<span class="strikeText">'.$p->title.'</span>';
                $out .= ' <a class="publishElement" href="'.$page->url.'publish-element/'.$user->id.'/'.$p->id.'?type=team">'.__('[Publish]').'</a>';
              } else {
                $out .= '<span>'.$p->title.'</span>';
              }
              if ($p->summary != '') {
                $out .= ' → <span>'.$p->summary.'</span> ';
              }
              if ($p->type && $p->type->name && $p->exData != '') {
                $allLines = preg_split('/$\r|\n/', $p->exData);
                $listWords = prepareListWords($allLines, $p->type->name);
                $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span> ';
                $out .= '<span class="label label-success">'.$p->type->title.'</span> ';
              }
              if ($p->topic->count() > 0) {
                $listTopics = $p->topic->implode(', ', '{title}');
                $out .= '<span class="label label-danger">'.$listTopics.'</span>';
              }
              if ($userIsOwner) {
                $out .= $p->feel(array(
                          "text" => __('[Edit]'),
                          "fields" => "title,image,summary,instructions,topic,level,type,imageMap,exData"
                        ));
                if ($p->teacher->count() == 0) { // Monster is not shared, owner can delete
                  $out .= '<a href="#" class="deleteFromId" data-href="'.$page->url.'deleteFromId/'.$user->id.'/'.$p->id.'?type=team">'.__("[Delete]").'</a>';
                }
              }
              // Possibility to test monster
              $out .= ' <a href="'.$pages->get("name=underground-training")->url.'?id='.$p->id.'" data-toggle="tooltip" title="'.__("Test training").'">[<i class="glyphicon glyphicon-headphones"></i>]</a>'; // Training link
              $out .= ' <a href="'.$p->url.'" data-toggle="tooltip" title="'.__("Test fight").'">[<i class="glyphicon glyphicon-flash"></i>]</a>'; // Fight link
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          $out .= '<h4><span>'.__("Available monsters").'</span></h4>';
          $out .= '<ul id="notTeacherElements">';
          foreach($notTeacherEl as $p) {
            $out .= '<li>';
              if (!$user->isSuperuser()) {
                $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
              }
              $out .= '<span>'.$p->title.'</span> → ';
              $out .= '<span>'.$p->summary.'</span> ';
              if ($p->type && $p->type->name && $p->exData != '') {
                $allLines = preg_split('/$\r|\n/', $p->exData);
                $listWords = prepareListWords($allLines, $p->type->name);
                $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span> ';
                $out .= '<span class="label label-success">'.$p->type->title.'</span> ';
              }
              if ($user->isSuperuser() || $user->name == 'flieutaud') {
                $out .= $p->feel();
              }
              // Possibility to test monster
              $out .= ' <a href="'.$pages->get("name=underground-training")->url.'?id='.$p->id.'" data-toggle="tooltip" title="'.__("Test training").'">[<i class="glyphicon glyphicon-headphones"></i>]</a>'; // Training link
              $out .= ' <a href="'.$p->url.'" data-toggle="tooltip" title="'.__("Test fight").'">[<i class="glyphicon glyphicon-flash"></i>]</a>'; // Fight link
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          $out .= '</section>';
          break;
        case 'manage-shop' :
          $out .= '<section class="well">';
          $out .= '<h3 class="text-center">';
          $out .=   __('Manage marketplace');
          $out .= '</h3>';
          if (!$user->isSuperuser()) {
            $out .= '<p class="text-center">'.__("Contact the administrator if you want to operate changes in this list.").'</p>';
            $notTeacherEl = $pages->find("template=item, teacher!=$user, created_users_id!=$user->id")->sort('title');
          } else {
            $notTeacherEl = $pages->find("template=item, include=all")->sort('title');
          }
          $out .= '<div>';
          if (!$user->isSuperuser()) {
            $teacherEl = $pages->find("template=item, (teacher=$user), (created_users_id=$user->id), include=all")->sort('title');
            $out .= '<h4><span>'.__("Your items").'</span></h4>';
            $out .= '<ul id="teacherElements">';
            foreach($teacherEl as $p) {
              if ($p->created_users_id == $user->id) { $userIsOwner = true; } else { $userIsOwner = false; } // Useless for the moment, teacher can't create items
              $out .= '<li>';
              if (!$userIsOwner) {
                $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
              }
              if ($p->isUnpublished()) { // Useless for the moment, teacher can't create items
                $out .= '<span class="strikeText">'.$p->title.'</span>';
                $out .= ' <a class="publishElement" href="'.$page->url.'publish-element/'.$user->id.'/'.$p->id.'?type=team">'.__('[Publish]').'</a>';
              } else {
                $out .= '<span><a href="'.$shop->url.'details/'.$p->name.'" target="blank">'.$p->title.'</a></span>';
              }
              if ($p->summary != '') {
                $out .= ' → <span>'.$p->summary.'</span> ';
              }
              if ($p->category) {
                $out .= ' <span class="label label-danger">'.$p->category->title.'</span>';
              }
              if ($p->created_users_id == $user->id || $user->name == 'flieutaud') { // Useless for the moment : teacher can't create items
                $out .= $p->feel(array(
                          "text" => __('[Edit]'),
                        ));
              }
              $out .= '</li>';
            }
            $out .= '</ul>';
          }
          $out .= '<h4><span>'.__("Available items").'</span></h4>';
          $out .= '<ul id="notTeacherElements">';
          foreach($notTeacherEl as $p) {
            $out .= '<li>';
            if (!$user->isSuperuser()) {
              $out .= '<a href="'.$page->url.'select-element/'.$user->id.'/'.$p->id.'?type=team" class="selectElement btn btn-xs btn-primary"><i class="glyphicon glyphicon-sort"></i></a> ';
            }
            $out .= '<span><a href="'.$shop->url.'details/'.$p->name.'" target="blank">'.$p->title.'</a></span> → ';
            $out .= '<span>'.$p->summary.'</span> ';
            if ($p->category) {
              $out .= ' <span class="label label-danger">'.$p->category->title.'</span>';
            }
            if ($user->isSuperuser() || $user->name == 'flieutaud') {
              $out .= $p->feel(array(
                        "text" => __('[Edit]'),
                      ));
            }
            $out .= '</li>';
          }
          $out .= '</ul>';
          $out .= '<h4><span>'.__("Planet Alert equipment [for information]").'</span></h4>';
          $allEquipments = $pages->find("template=equipment")->sort("category.title, title");
          foreach($allEquipments as $p) {
            $out .= '<li>';
            $out .= '<span><a href="'.$shop->url.'details/'.$p->name.'" target="blank">'.$p->title.'</a></span> → ';
            $out .= '<span>'.$p->summary.'</span> ';
            $out .= '<span class="label label-danger">'.$p->category->title.'</span> ';
            if ($user->isSuperuser() || $user->name == 'flieutaud') {
              $out .= $p->feel(array(
                        "text" => __('[Edit]'),
                      ));
            }
            $out .= '</li>';
          }
          $out .= '<ul>';
          $out .= '</ul>';
          $out .= '</div>';
          $out .= '</section>';
          break;
        case 'setCaptains' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<h3 class="text-center">';
            $out .=   'Set Captains';
            $out .= '</h3>';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="setCaptains">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'setKarma' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<h3 class="text-center">';
            $out .=   'Set karma';
            $out .= '</h3>';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="setKarma">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'setYearlyKarma' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<h3 class="text-center">';
            $out .=   'Set yearly karma';
            $out .= '</h3>';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="setYearlyKarma">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'setScores' :
          if ($user->isSuperuser()) {
            $out .= '<section class="well">';
            $out .= '<h3 class="text-center">';
            $out .=   'Set scores';
            $out .= '</h3>';
            $out .= '<div>';
            $out .= '<span>Select a team : </span>';
            $out .= '<select id="teamId">';
            $out .= '<option value="-1">Select a team</option>';
            foreach($allTeams as $p) {
              $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
            }
            $out .= '</select>';
            $out .= '</div>';
            $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="setScores">Generate</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        case 'users' :
          if ($user->isSuperuser()) {
            $allPlayers = $pages->find("parent.name=players, template=player")->sort("title");
            $allTeachers = $pages->find("parent.name=teachers, template=teacherProfile")->sort("title");
            $out .= '<section class="well">';
            $out .= '<p><span class="glyphicon glyphicon-alert"></span> 1 player / line → Name [,lastName] [,rank = 4=CM1,5=CM2,6=6emes,7=5emes,8=4emes...)] [,team] [,teacher\'s login]</p>';
            $out .= '<textarea id="newPlayers" name="newPlayers" rows="5" cols="200"></textarea>';
            $out .= '<button class="addUsers btn btn-primary btn-block" data-href="'.$page->url.'" data-action="addUsers">Add new players</button>';
            $out .= '<section id="ajaxViewport" class="well"></section>';
            if ($user->isSuperuser()) {
              $allTeachers = $users->find("roles=teacher");
              $out .= '<p>There are currently '.$allTeachers->count().' teachers. (Go to backend to add a new teacher, i.e. new user with teacher role)</p>';
              $out .= '<table class="table table-condensed table-hover">';
              $out .= '<th>Teacher (user page)</th>';
              $out .= '<th>Email</th>';
              $out .= '<th>Teacher profile page</th>';
              $out .= '<th>Associated teams</th>';
              foreach ($allTeachers as $p) {
                $out .= '<tr>';
                $out .= '<td>'.$p->name.$p->feel().'</td>';
                $out .= '<td>'.$p->email.' '.$p->feel(array('fields'=>'email')).'</td>';
                $profilePage = $pages->get("name=teachers")->get("singleTeacher=$p");
                if ($profilePage->id) {
                  $out .= '<td><span class="label label-success">OK</span> '.$profilePage->feel().'</td>';
                } else {
                  $out .= '<td><span class="label label-danger">No page !</span>'.$pages->get("name=teachers")->feel(array('text'=>'[Create profile page]', 'mode' => 'page-add')).'</td>';
                }
                $teacherTeams = $pages->find("template=team, teacher=$p")->implode(', ', '{title}');
                $out .= '<td>'.$teacherTeams.'</td>';
                $out .= '</tr>';
              }
              $out .= '</table>';
              $out .= '<hr />';
            }
            $out .= '<p>There are currently '.$allPlayers->count().' players.</p>';
            $out .= '<table id="usersTable" class="table table-condensed table-hover">';
            $out .= '<th>Player</th>';
            $out .= '<th>Team</th>';
            $out .= '<th>User name / Login</th>';
            $out .= '<th>Head teacher</th>';
            $out .= '<th>History</th>';
            $out .= '<th>Archive</th>';
            $out .= '<th>Delete</th>';
            foreach ($allPlayers as $p) {
              $u = $users->get("name=$p->login");
              $out .= '<tr>';
              $out .= '<td>'.$p->title.' '.$p->lastName.$p->feel().'</td>';
              $out .= '<td>'.$p->team->title.$p->feel(array('text'=>'[Change]', 'fields'=>'team')).'</td>';
              $out .= '<td>'.$u->name.' / '.$p->login.'</td>';
              $headTeacher = getHeadTeacher($p);
              if ($headTeacher) {
                $out .= '<td>'.$headTeacher->name.'</td>';
              } else {
                $out .= '<td>-</td>';
              }
              /* $out .= '<td><a class="btn btn-xs btn-success" href="'.$config->urls->admin.'page/edit/?id='.$p->id.'">Edit page in backend</a></td>'; */
              $out .= '<td><a target="blank" class="btn btn-xs btn-danger" href="'.$adminActions->url.'recalculate/'.$p->id.'">Check history</a></td>';
              $history = $p->child("name=history");
              if ($history->id && $history->children()->count() > 0) {
                $out .= '<td><button class="confirm btn btn-xs btn-danger" data-href="'.$page->url.'archivePlayer/'.$p->id.'/1">Archive Player</button></td>';
              } else {
                $out .= '<td>Nothing to archive.</td>';
              }
              $out .= '<td><button class="removeUser btn btn-xs btn-danger" data-href="'.$page->url.'" data-action="removeUser" data-playerId="'.$p->id.'">Delete Player/User</button></td>';
              $out .= '</tr>';
            }
            $out .= '</table>';
            $out .= '<div>';
          } else {
            $out .= $noAuthMessage;
          }
          break;
        default :
          $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="script">Generate</button>';
          $out .= '<section id="ajaxViewport" class="well"></section>';
          $out .= '<div>';
      }
    } else { // End if admin/teacher
      $out .= $noAuthMessage;
    }
    $out .= '</div>';
    echo $out;
    include("./foot.inc"); 
    echo '<script>';
    echo '$(".addUsers").click( function() { var myData = $(\'#newPlayers\').val(); var action=$(this).attr("data-action"); var href=$(this).attr("data-href")+action; var that=$(this); if (confirm("Proceed?")) {$.post(href, {newPlayers:myData}, function(data) { $("#ajaxViewport").html(data); }) };});';
    echo '$(".removeUser").click( function() {  var playerId=$(this).attr("data-playerId"); var action = $(this).attr("data-action"); var href=$(this).attr("data-href")+action+"/"+playerId+"/1"; var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); $("#ajaxViewport").html(data);that.html("User deleted. Please reload!"); })}});';
    echo '$(".confirm").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Saved!"); }) };});';
    echo '</script>';
  } else { // Ajax call, display requested information
    $out = '';
    $allPlayers = $pages->find("parent.name=players, template=player")->sort("team.name, title");
    $action = $input->urlSegment1;
    $playerId = $input->urlSegment2;
    $confirm = $input->urlSegment3;
    $type = $input->get["type"];
    $unique = true;
    $startDate = $input->get["startDate"]; 
    $endDate = $input->get["endDate"]; 
    if ($startDate == '') {
      $startDate = date('2000-01-01 00:00:00');
    } else {
      $startDate = $startDate.' 00:00:00';
    };
    if ($endDate == '') {
      $endDate = date('Y-m-d 23:59:59');
    } else {
      $endDate = $endDate.' 23:59:59';
    }

    $teamActions = ['toggle-lock', 'savePeriod', 'archive', 'forceHelmet', 'forceVisualizer', 'forceKnowledge', 'classActivity', 'reset-streaks', 'ut-stats', 'recalculate-tmp'];
    if (in_array($action, $teamActions)) {
      $type = 'team';
    }

    if ($type == 'team') {
      if ($playerId != '-1') {
        $selectedTeam = $pages->get("id=$playerId");
      } else {
        $selectedTeam = '-1';
      }
      $selectedPlayer = false;
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
        /* $allPlayers = $pages->find("parent.name=players, template=player"); */
        /* $ambassador = $pages->get("name=ambassador"); */
        /* foreach($allPlayers as $p) { */
        /*   $p->streak = 0; */
        /*   $allEvents = $p->get("name=history")->children("template=event, task.name!=donation|donated|absent, sort=-date, limit=10")->sort('date'); */
        /*   foreach($allEvents as $e) { */
        /*     setStreak($p, $e->task); */
        /*   } */
        /*   $p->of(false); */
        /*   $p->save(); */
        /* } */
        
        /* // Add player role to all users (but admin and teachers */
        /* foreach($users as $u) { */
        /*   if ($u->is('roles!=teacher') && $u->is('roles!=superuser') && $u->name!='guest') { */
        /*     $u->of(false); */
        /*     $u->addRole('player'); */
        /*     $u->save(); */
        /*   } */
        /* } */
        break;
      case 'reports' :
        $out .='<script type="text/javascript" src="'.$config->urls->templates.'scripts/main.js"></script>';
        if ($selectedTeam && $selectedTeam != '-1') {
          $allPeriods = $pages->get("name=periods")->children();
          /* $officialPeriod = $pages->get("name=admin-actions")->periods; */
          $officialPeriod = $selectedTeam->periods;
          $allPlayers = $allPlayers->find("team.name=$selectedTeam->name");
          $out .= '<section class="well">';
          $out .= '<div>';
          $out .= '<span>Report category : </span>';
          $out .= '<label for="allCat"><input type="radio" value="all" id="allCat" name="reportCat" checked="checked" class="reportCat"> All</input></label> &nbsp;&nbsp;';
          $out .= '<label for="participation"><input type="radio" value="participation" id="participation" name="reportCat" class="reportCat"> Participation</input></label> &nbsp;&nbsp;';
          $out .= '<label for="planetAlert"><input type="radio" value="planetAlert" id="planetAlert" name="reportCat" class="reportCat"> Planet Alert</input></label> &nbsp;&nbsp;';
          $out .= '</div>';
          $out .= '<div>';
          $out .= '<span>Ordering by : </span>';
          $out .= '<label for="firstName"><input type="radio" class="reportSort" id="firstName" name="order" checked="checked" value="title"> First name</input></label> &nbsp;&nbsp;';
          $out .= '<label for="lastName"><input type="radio" class="reportSort" id="lastName" name="order" value="lastName"> Last name</input></label>';
          $out .= '</div>';
          $out .= '<div>';
          $out .= '<span>Period : </span>';
          $out .= '<select id="periodId">';
          foreach($allPeriods as $period) {
            if ($period->id == $officialPeriod->id) {$selected = 'selected="selected"';} else {$selected = '';};
            $out .= '<option value="'.$period->id.'" '.$selected.'>'.$period->title.'</option>';
          }
          $out .= '</select>';
          $out .= '</div>';
          $out .= '<div>';
          $out .= '<span>Select a player : </span>';
          $out .= '<select id="reportPlayer">';
          $out .= '<option value="'.$selectedTeam->id.'">The whole team</option>';
          foreach($allPlayers as $player) {
            $out .= '<option value="'.$player->id.'">'.$player->title.'</option>';
          }
          $out .= '</select>';
          // reportUrl is based on url segments : all|category/team|player/periodId?sort=title|lastName
          $out .= '<p class="text-center"><a id="reportUrl_button" class="btn btn-primary" href="'. $pages->get('/report_generator')->url .'" data-reportUrl="'. $pages->get('/report_generator')->url .'" target="_blank">Generate report</a></p>';
          $out .= '</div>';
          $out .= '</section>';
        } else {
          $out .= 'You need to select a team.';
        }
        break;
      case 'refPage' :
        $out .= 'Total # of players : '.$allPlayers->count();
        $out .= '<ul>';
        foreach($allPlayers as $p) {
          $p->of(false);
          $out .= '<li>'.$p->title.' ['.$p->team->title.']</li>';
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
            $out .= '<span class="label label-success">Good</span> No empty refPage found. Recalculation of score for <strong>'. $p->title.' ['.$p->team->title.']</strong> should be possible.';
          }
        }
        $out .= '</ul>';
        break;
      case 'add-remove' :
        if ($selectedPlayer) {
          $eventId = $confirm; // urlSegment3 used for eventId
          $elementId = $input->urlSegment4; // urlSegment4 used for element id
          $allEvents = $selectedPlayer->get("name=history")->children()->sort("date");
          $e = $pages->get("id=$eventId");
          // Move all day events a few seconds later
          $dayEvents = $allEvents->find("date=$e->date, id!=$e->id");
          $seconds = 5;
          foreach($dayEvents as $d) {
            $d->date = date($e->date + $seconds); 
            $seconds = $seconds + 1;
            $d->of(false);
            $d->save();
          }
          $task = $pages->get("name=remove");
          $element = $pages->get("id=$elementId");
          $task->comment = '3rd wrong invasions on '.$element->title;
          $task->eDate = date($e->date+1);
          $task->linkedId = false;
          $task->refPage = $element;
          $historyPage = saveHistory($selectedPlayer, $task, 1);
          $linkedId = $historyPage->id;
          // DO NOT use updateScore(...,true), it would touch the equipment for real !!!
        }
        break;
      case 'reset-streaks' :
        $allPlayers = $pages->find("parent.name=players, template=player, team=$selectedTeam");
        $role = $pages->get("name=ambassador");
        foreach($allPlayers as $p) {
          $streak = checkStreak($p);
          $p->streak = $streak;
          if ($streak >= 10) {
            $p->skills->add($role);
          } else {
            $p->skills->remove($role);
          }
          $p->of(false);
          $p->save();
        }
        break;
      case 'bumpYear' :
        $allPeriods = $pages->find("template=period");
        foreach($allPeriods as $p) {
          $p->of(false);
          $p->dateStart = strtotime('+1 year', $p->dateStart);
          $p->dateEnd = strtotime('+1 year', $p->dateEnd);
          $p->save();
        }
        break;
      case 'recalculate-tmp' :
        $allPlayers = $pages->find("parent.name=players, template=player, team=$selectedTeam");
        foreach($allPlayers as $p) {
          $tmpPage = $p->child("name=tmp");
          if ($tmpPage->id) { // Is tmpCache available ?
          } else { // Create it
            $tmpPage = createTmpCache($p);
          }
          // Check date
          $today = new \DateTime("today");
          $modified = new \DateTime(date("Y-m-d", $tmpPage->modified));
          if ($today->diff($modified)->days != 0) {
            initTmpMonstersActivity($p);
          }
        }
        break;
      case 'add-death' :
        if ($selectedPlayer) {
          $allPlayers = $pages->find("parent.name=players, template=player, team=$selectedPlayer->team");
          $eventId = $confirm; // urlSegment3 used for eventId
          $currentLevel = $input->urlSegment4;
          $allEvents = $selectedPlayer->get("name=history")->children()->sort("date");
          $e = $pages->get("id=$eventId");
          // Move all day events a few seconds later
          $dayEvents = $allEvents->find("date=$e->date, id!=$e->id");
          $seconds = 5;
          foreach($dayEvents as $d) {
            $d->date = date($e->date + $seconds); 
            $seconds = $seconds + 1;
            $d->of(false);
            $d->save();
          }
          $task = $pages->get("name=death");
          $task->comment = 'Player died. [former level:'.$currentLevel.']';
          $task->eDate = date($e->date+1);
          $task->linkedId = false;
          $historyPage = saveHistory($selectedPlayer, $task, 1);
          $linkedId = $historyPage->id;
          // DO NOT use updateScore(...,true), it would touch the equipment for real !!!
          // Find previous death, check former level and act accordingly
          $prevDeath = $allEvents->sort("-date")->get("task.name=death, limit=1");
          preg_match("/\d+/", $prevDeath->summary, $matches);
          $previousLevel = (int) $matches[0];
          if ($previousLevel == 1 && $currentLevel == 1) { // 2nd death in a row on Level 1 > Enter coma state
            // Disabled on Edit history but no effect on other players
            /* $selectedPlayer->coma = 1; */
          } else {
            // Each team member suffers from player's death
            // Disable to avoid recalculation nightmare ???
            $teamDeath = $pages->get("name=team-death");
            $teamDeath->comment = 'Team member died! ['.$selectedPlayer->title.']';
            $teamDeath->eDate = $task->eDate;
            $teamDeath->refPage = $selectedPlayer;
            $teamDeath->linkedId = $linkedId;
            $teamPlayers = $allPlayers->find("group!=$selectedPlayer->group");
            foreach($teamPlayers as $p) {
              saveHistory($p, $teamDeath, 0);
            }
            // Each group member suffers from player's death
            $groupMembers = $allPlayers->find("group=$selectedPlayer->group, id!=$selectedPlayer->id");
            $groupDeath = $pages->get("name=group-death");
            $groupDeath->comment = 'Group member died! ['.$selectedPlayer->title.']';
            $teamDeath->refPage = $selectedPlayer;
            $groupDeath->eDate = $task->eDate;
            $groupDeath->linkedId = $linkedId;
            foreach($groupMembers as $p) {
              saveHistory($p, $groupDeath, 0);
            }
          }
          // Reset streak for all players
          foreach ($allPlayers as $p) {
            $p->of(false);
            $p->streak = 0;
            $p->save();
          }
        }
        break;
      case 'helmet' :
        $helmet = $pages->get("name=memory-helmet");
        $out .= 'Total # of players : '.$allPlayers->count();
        $out .= '<ul>';
        foreach($allPlayers as $p) {
          $p->of(false);
          $out .= '<li>'.$p->title.' ['.$p->team->title.']</li>';
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
              $members = $allPlayers->find("team=$p->team, group=$p->group");
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
                $members = $allPlayers->find("team=$p->team, group=$p->group");
                foreach($members as $m) {
                  $boughtHelmet = $m->get("name=history")->child("template=event, task.name=buy, refPage.name=memory-helmet");
                  if ($boughtHelmet->id) {
                    $source = clone $boughtHelmet;
                  }
                }
                // Copy the event on the same date
                if ($source->id) {
                  $task = $pages->get("template=task, name=buy");
                  $task->comment = "Memory helmet [unlocked]";
                  $task->refPage = $helmet;
                  $task->linkedId = false;
                  $task->eDate = $source->date;
                  foreach($members as $m) {
                    $boughtHelmet = $m->get("name=history")->child("template=event, task.name=buy, refPage.name=memory-helmet");
                    if ($boughtHelmet->id == '') {
                      saveHistory($m, $task, 0);
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
        $allMonsters = $pages->find("template=exercise")->sort("title, level");
        $out .= '<h3>Best players among '.$allPlayers->count().' players.</h3>';
        $out .= '<ul>';
        // Get all ut-actions
        $testPlayer = $allPlayers->get("name=test");
        $allUt = $pages->findMany("task.name~=ut-action, has_parent!=$testPlayer");
        $concernedMonster = [];
        // Build monsters stats
        foreach($allUt as $e) {
          $utGain = 1;
          $pId = $e->parent("template=player")->id;
          $mId = $e->refPage->id;
          if (!isset($concernedMonster[$mId][$pId])) {
            $concernedMonster[$mId][$pId] = 0;
          }
          preg_match("/\[\+([\d]+)U\.T\.\]/", $e->summary, $matches);
          if ($matches) {
            $utGain = $matches[1];
          }
          $concernedMonster[$mId][$pId] += $utGain;
        }
        // Check best players
        foreach($allMonsters as $m) {
          $bestUt = $m->best;
          if ($m->best && $m->mostTrained) {
            $out .= '<li>'.$m->title.' [Current best : '.$m->mostTrained->title.' ['.$m->mostTrained->team->title.'] : '.$bestUt.'UT]';
          } else {
            $out .= '<li>'.$m->title.' [Current best : Nobody.]';
          }
          if (isset($concernedMonster[$m->id])) {
            $newBestUt = max($concernedMonster[$m->id]);
            $newBestId = array_search(max($concernedMonster[$m->id]),$concernedMonster[$m->id]);
          } else {
            $newBestUt = $bestUt;
          }
          if ($newBestUt != $bestUt) {
            $dirty = true;
            $newBestPlayer = $allPlayers->get("id=$newBestId");
            $out .= ' <span class="label label-danger">Error</span>';
            $out .= ' - New best : '.$newBestPlayer->title.' ['.$newBestPlayer->team->title.'] ⇒'.$newBestUt.'UT';
            if ($confirm == 1) { // Save new best players
              $m->of(false);
              $m->mostTrained = $newBestPlayer;
              $m->best = $newBestUt;
              $m->save();
            }
          } else {
            $out .= ' <span class="label label-success">OK</span>';
          }
        }
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
      case 'remove-equipment' :
        $item = $pages->get($input->urlSegment3);
        $player = $pages->get($playerId);
        $player->of(false);
        $player->equipment->remove($item);
        $player->save();
        break;
      case 'save-options':
        $allPlayers = $pages->find("parent.name=players, template=player, team.name!=no-team");
        $id = $input->urlSegment2;
        if ($id != '-1') {
          $officialPeriod = $pages->get("id=$id");
          $page->periods = $officialPeriod;
        } else {
          $page->periods = false;
        }
        $page->of(false);
        $page->save();
        // Feedback
        echo '<div class="notification alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> ';
        echo $allPlayers->count();
        echo '</div>';
        // Set hkCount for newly selected period
        // Might be too long to recalculate hkcount over a long period with many events...
        if ($id != -1) { 
          $now = time();
          if ($now < $officialPeriod->dateStart || $now > $officialPeriod->dateEnd) {
            echo '<div class="notification alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Today\'s date is OUT OF the official period dates !</div>';
          }
          foreach($allPlayers as $p) {
            $newCount = setHomework($p, $officialPeriod->dateStart, $officialPeriod->dateEnd);
            if ($newCount != $p->hkcount) {
              $p->hkcount = $newCount;
              $p->of(false);
              $p->save();
            }
          }
        } else {
          foreach($allPlayers as $p) {
            $newCount = 0;
            if ($newCount != $p->hkcount) {
              $p->hkcount = $newCount;
              $p->of(false);
              $p->save();
            }
          }
        }
        break;
      case 'setCaptains':
        if ($selectedTeam && $selectedTeam != '-1') {
          $oldCaptains = $allPlayers->find("team=$selectedTeam, skills.count>0, skills.name=captain")->implode(', ', '{title}');
          $out .= '</div>';
          $out .= '<h4 class="text-center">';
          $out .=   'Set Captains for '.$selectedTeam->title;
          $out .= '</h4>';
          $out .= '<section>';
          $out .= '<p> Old captains : '.$oldCaptains.'</p>';
          setCaptains($selectedTeam, false);
          $newCaptains = $allPlayers->find("team=$selectedTeam, skills.count>0, skills.name=captain")->implode(', ', '{title}');
          $out .= '<p> New captains : '.$newCaptains.'</p>';
          $out .= '</section>';
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'saveCaptains/'.$selectedTeam->id.'/1">Save new captains</button>';
        }
        break;
      case 'setKarma':
        if ($selectedTeam && $selectedTeam != '-1') {
          $allPlayers = $allPlayers->find("team=$selectedTeam");
          $out .= '</div>';
          $out .= '<h4 class="text-center">';
          $out .=   'Set Karma for '.$selectedTeam->title;
          $out .= '</h4>';
          $out .= '<section>';
          $out .= '<ul><span class="label label-default">Actual karmas</span>';
          foreach($allPlayers as $p) {
            $newKarma = setKarma($p);
            $out .= '<li>'.$p->title.' : '.$p->karma.' → '.$newKarma.'</li>';
          }
          $out .= '</ul>';
          $out .= '</section>';
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'saveKarma/'.$selectedTeam->id.'/1">Save new karmas</button>';
        }
        break;
      case 'setYearlyKarma':
        if ($selectedTeam && $selectedTeam != '-1') {
          $allPlayers = $allPlayers->find("team=$selectedTeam");
          $out .= '</div>';
          $out .= '<h4 class="text-center">';
          $out .=   'Set yearly Karma for '.$selectedTeam->title;
          $out .= '</h4>';
          $out .= '<section>';
          $out .= '<ul><span class="label label-default">Actual yearly karmas</span>';
          foreach($allPlayers as $p) {
            $newKarma = setYearlyKarma($p);
            $out .= '<li>'.$p->title.' : '.$p->yearlyKarma.' → '.$newKarma.'</li>';
          }
          $out .= '</ul>';
          $out .= '</section>';
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'saveYearlyKarma/'.$selectedTeam->id.'/1">Save new yearly karmas</button>';
        }
        break;
      case 'setScores':
        $out = '';
        if ($selectedTeam && $selectedTeam != '-1') {
          $out .= '</div>';
          $out .= '<h4 class="text-center">';
          $out .=   'School year Team scores for '.$selectedTeam->title;
          $out .= '</h4>';
          $out .= '<section>';
          $out .= '<ul><span class="label label-default">Actual scores</span>';
          $out .= '<li>'.$selectedTeam->freeworld.'%</li>';
          $out .= '<li>'.$selectedTeam->freeActs.' free acts</li>';
          $out .= '</section>';
          $out .= '<section>';
          $out .= '<ul><span class="label label-danger">New scores</span>';
          $free = nbFreedomActs($selectedTeam);
          $allElements = teamFreeworld($selectedTeam);
          $completed = $allElements->find("completed=1");
          $percent = round((100*$completed->count())/$allElements->count());
          $out .= '<li>'.$percent.'%</li>';
          $out .= '<li>'.$free.' free acts</li>';
          $out .= '</section>';
          $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'saveScores/'.$selectedTeam->id.'/1">Save new scores</button>';
        }
        break;
      case 'saveCaptains':
        $selectedTeam = $pages->get("$input->urlSegment2");
        $allPlayers = $allPlayers->find("team=$selectedTeam");
        setCaptains($selectedTeam, true);
        break;
      case 'saveKarma':
        $selectedTeam = $pages->get("$input->urlSegment2");
        $allPlayers = $allPlayers->find("team=$selectedTeam");
        foreach($allPlayers as $p) {
          $p->karma = setKarma($p);
          $p->of(false);
          $p->save();
        }
        break;
      case 'saveYearlyKarma':
        $selectedTeam = $pages->get("$input->urlSegment2");
        $allPlayers = $allPlayers->find("team=$selectedTeam");
        foreach($allPlayers as $p) {
          $p->yearlyKarma = setYearlyKarma($p);
          $p->of(false);
          $p->save();
        }
        break;
      case 'saveScores':
        $selectedTeam = $pages->get("$input->urlSegment2");
        updateTeamScores($selectedTeam);
        break;
      case 'toggle-lock':
        $team = $pages->get("$selectedTeam");
        $team->of(false);
        if ($team->lockFights == 1) {
          // Remove lock
          $team->lockFights = 0;
        } else {
          $team->lockFights = 1;
        }
        $team->save();
        break;
      case 'savePeriod':
        $team = $pages->get("$selectedTeam");
        if ($confirm != -1) {
          $period = $pages->get($confirm); // urlSegment3 used for periodId
        } else {
          $period = false;
        }
        $team->of(false);
        if ($period && $period->is("template=period")) {
          $team->periods = $period;
        } else {
          $team->periods = false;
        }
        $team->save();
        break;
      case 'select-element':
        $teacher = $users->get("$selectedTeam"); // urlSegment2 used for teacherId
        $element = $pages->get($confirm); // urlSegment3 used for elementId
        if ($element->id) { // Add/Remove page in correct repeater field or simple teacher field
          switch($element->template) {
            case 'task' : 
              $already = $element->owner->get("singleTeacher=$user");
              if ($already->id) {
                $element->owner->remove($already);
              } else {
                $new = $element->owner->getNew();
                $new->singleTeacher = $user;
                $element->owner->add($new);
              }
              break;
            case 'period' : 
              $already = $element->periodOwner->get("singleTeacher=$user");
              if ($already->id) {
                $element->periodOwner->remove($already);
              } else {
                $new = $element->periodOwner->getNew();
                $new->singleTeacher = $user;
                $element->periodOwner->add($new);
              }
              break;
            default : // Add/Remove teacher in teacher field
              if ($element->teacher->has($user)) {
                $element->teacher->remove($user);
              } else {
                $element->teacher->add($user);
              }
          }
        }
        $element->of(false);
        $element->save();
        break;
      case 'publish-element':
        $teacher = $users->get("$selectedTeam"); // urlSegment2 used for teacherId
        $element = $pages->get($confirm); // urlSegment3 used for elementId
        if ($element->id && ($element->created_users_id == $teacher->id || $user->isSuperuser() || $user->name == 'flieutaud')) {
          $element->of(false);
          $element->status([]);
          $element->save();
        }
        break;
      case 'forceHelmet':
        $team = $pages->get("$selectedTeam");
        $team->of(false);
        if ($team->forceHelmet == 1) {
          // Remove lock
          $team->forceHelmet = 0;
        } else {
          $team->forceHelmet = 1;
        }
        $team->save();
        break;
      case 'forceVisualizer':
        $team = $pages->get("$selectedTeam");
        $team->of(false);
        if ($team->forceVisualizer == 1) {
          // Remove lock
          $team->forceVisualizer = 0;
        } else {
          $team->forceVisualizer = 1;
        }
        $team->save();
        break;
      case 'forceKnowledge':
        $team = $pages->get("$selectedTeam");
        $team->of(false);
        if ($team->forceKnowledge == 1) {
          // Remove lock
          $team->forceKnowledge = 0;
        } else {
          $team->forceKnowledge = 1;
        }
        $team->save();
        break;
      case 'classActivity':
        $team = $pages->get("$selectedTeam");
        $team->of(false);
        if ($team->classActivity == 1) {
          // Remove lock
          $team->classActivity = 0;
        } else {
          $team->classActivity = 1;
        }
        $team->save();
        break;
      case 'archivePlayer':
        $p = $selectedPlayer;
        $noteam = $pages->get("template=team, name=no-team");
        $currentHistory = $p->children()->get("name=history");
        $counter = $p->children()->count();
        if ($counter > 0 && $currentHistory) {
          $currentHistory->of(false);
          // Save scores
          $currentHistory->name = 'history-'.$counter;
          $currentHistory->title = 'history-'.$counter;
          $currentHistory->team = $p->team;
          $currentHistory->rank = $p->rank;
          $currentHistory->karma = $p->karma;
          $currentHistory->level = $p->level;
          $currentHistory->HP = $p->HP;
          $currentHistory->XP = $p->XP;
          $currentHistory->GC = $p->GC;
          $currentHistory->underground_training = $p->underground_training;
          $currentHistory->fighting_power = $p->fighting_power;
          $currentHistory->donation = $p->donation;
          $currentHistory->equipment = $p->equipment;
          $currentHistory->places = $p->places;
          $currentHistory->coma = $p->coma;
          $currentHistory->save();
        }
        // 'Init' player
        $p->of(false);
        $p->HP = 50;
        $p->coma = 0;
        $p->team = $noteam;
        $p->group = '';
        $nextRank = $p->rank->index+1;
        if ($nextRank > 11) { $nextRank = 11; }
        $p->rank = $pages->get("name=ranks")->child("index=$nextRank");
        $p->save();
        break;
      case 'archive':
        $allPlayers = $pages->find("template=player, parent.name=players, team=$selectedTeam");
        $noteam = $pages->get("template=team, name=no-team");
        $captain = $pages->get("name=captain");
        // Archive player's history
        foreach($allPlayers as $p) {
          $currentHistory = $p->children()->get("name=history");
          $counter = $p->children("name~=history")->count();
          if ($counter > 0 && $currentHistory) {
            $currentHistory->of(false);
            // Save scores
            $currentHistory->name = 'history-'.$counter;
            $currentHistory->title = 'history-'.$counter;
            $currentHistory->team = $p->team;
            $currentHistory->rank = $p->rank;
            $currentHistory->karma = $p->karma;
            $currentHistory->yearlyKarma = $p->yearlyKarma;
            $currentHistory->level = $p->level;
            $currentHistory->HP = $p->HP;
            $currentHistory->XP = $p->XP;
            $currentHistory->GC = $p->GC;
            $currentHistory->streak = $p->streak;
            $currentHistory->underground_training = $p->underground_training;
            $currentHistory->fighting_power = $p->fighting_power;
            $currentHistory->donation = $p->donation;
            $currentHistory->equipment = $p->equipment;
            $currentHistory->usabledItems = $p->usabledItems;
            $currentHistory->places = $p->places;
            $currentHistory->people = $p->people;
            $currentHistory->skills = $p->skills;
            $currentHistory->coma = $p->coma;
            $currentHistory->save();
          }
          // 'Init' player
          $p->of(false);
          $p->HP = 50;
          $p->coma = 0;
          $p->hkcount = 0;
          $p->team = $noteam;
          $p->group = '';
          $p->skills->remove($captain);
          $nextRank = $p->rank->index+1;
          if ($nextRank > 11) { $nextRank = 11; }
          $p->rank = $pages->get("name=ranks")->child("index=$nextRank");
          $p->save();
          // Prepare a new history page
          $history = new Page();
          $history->parent = $p;
          $history->template = 'archive';
          $history->name = 'history';
          $history->title = 'history';
          $history->save();
        }
        // Archive team scores and delete team
        $teamScores = $pages->get("name=teams");
        $teamScores->of(false);
        $newScore = $teamScores->teamScore->getNew();
        $newScore->title = $selectedTeam->title;
        $newScore->freeActs = $selectedTeam->freeActs;
        $newScore->freeworld = $selectedTeam->freeworld;
        $newScore->summary = date("Y", strtotime("-1 year")).'/'.date("Y");
        $teamScores->save();
        $pages->trash($selectedTeam);
        break;
      case 'trash' :
        $event = $pages->get($confirm); // urlSegment3 used for eventId
        // Delete team and group damage if needed (death)
        if ($event->task->is("name=death")) {
          $linkedDeath = $pages->find("template=event, linkedId=$event->id");
          foreach($linkedDeath as $p) {
            $pages->trash($p);
          }
        }
        if ($event->task->is("name=buy|free") && $event->refPage != false) { // Remove from player's equipment/places/people/usabledItems
          $playerPage = $pages->get("id=$playerId");
          if ($event->refPage->is("template=equipment")) {
            $playerPage->equipment->remove($event->refPage);
          }
          if ($event->refPage->is("template=place")) {
            $playerPage->places->remove($event->refPage);
          }
          if ($event->refPage->is("template=people")) {
            $playerPage->people->remove($event->refPage);
          }
          if ($event->refPage->is("name~=potion") && $event->refPage->is("name!=health-potion")) {
            $playerPage->usabledItems->remove($event->refPage);
          }
          $playerPage->of(false);
          $playerPage->save();
        }
        if ($event->task->is("name!=buy") && $event->refPage != false && $event->refPage->is("name~=potion") && $event->refPage->is("name!=health-potion")) { // Set back usabled item (potion) if needed
          $playerPage = $pages->get("id=$playerId");
          $playerPage->usabledItems->add($event->refPage);
          $playerPage->of(false);
          $playerPage->save();
        }
        $pages->trash($event);
        break;
      case 'removeUser' :
        $playerPage = $pages->get("id=$playerId");
        $u = $users->get("name=$playerPage->login");
        $users->delete($u);
        $pages->trash($playerPage);
        break;
      case 'ut-stats' :
        if ($selectedPlayer) { // TODO : Unused for the moment ?
          $out .= '<h3>';
          $out .= 'UT Stats for '.$selectedPlayer->title.' ['.$selectedPlayer->team->title.']   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          $allMonsters = $pages->find("template=exercise")->sort("level, title");
          foreach($allMonsters as $m) {
            list($playerUt, $inClassUtGain) = calculatedUt($m, $selectedPlayer, $startDate, $endDate);
            if ($playerUt > 0) {
              $out .= $m->title.' [Level '.$m->level.'] → ';
              $out .= $playerUt.' UT';
              $out .= '<br />';
            } else if ($inClassUtGain > 0) {
              $out .= $m->title.' [Level '.$m->level.'] → ';
              $out .= $playerUt.' UT [in class]';
              $out .= '<br />';
            }
          }
        } else if ($selectedTeam && $selectedTeam != '-1') {
          $out .= '<h3 class="text-center">';
          $out .= 'UT Stats for '.$selectedTeam->title .'   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          $allMonstersIds = $pages->findIds("parent.name=monsters");
          $allPlayers = $pages->find("parent.name=players, team=$selectedTeam")->sort("title");
          $teamUt = 0;
          foreach($allPlayers as $p) {
            $playersTrainings = $pages->find("has_parent=$p, template=event, task.name~=ut-action, refPage!='', date>=$startDate, date<=$endDate");
            $outUt = 0; // Out of class UT
            $inUt= 0; // in class UT
            $out .= '<ul>';
            $out .= '<li>'.$p->title.' : '.$playersTrainings->count().' sessions.</li>';
            foreach($allMonstersIds as $mId) {
              $mTrainings  = $playersTrainings->find("refPage.id=$mId")->sort("refPage->title");
              if ($mTrainings->count() > 0) {
                $out .= '<ul>';
                foreach($mTrainings as $mT) {
                  preg_match("/\[\+([\d]+)U\.T\.\]/", $mT->summary, $matches);
                  if (!$matches) {
                    if ($mT->inClass == 0) {
                      $outUt++;
                    } else {
                      $inUt++;
                    }
                  } else {
                    if ($mT->inClass == 0) {
                      $outUt = $outUt+$matches[1];
                    } else {
                      $inUt = $inUt+$matches[1];
                    }
                  }
                }
                $out .= '<li>'.$mT->refPage->title.' → '.($outUt+$inUt).' UT ['.$inUt.' in class] - '.$mTrainings->count().' session·s</li>';
                $out .= '</ul>';
              }
            }
            $teamUt += ($inUt+$outUt);
            $out .= '</ul>';
          }
          $out .= '<p class="label label-success">Total : +'.$teamUt.' UT for the team</p>';
        } else {
          $out .= 'You need to select 1 player or 1 team.';
        }
        break;
      case 'fights-stats' :
        if ($selectedTeam && $selectedTeam != '-1') {
          $out .= '<h3 class="text-center">';
          $out .= 'Fights Stats for '.$selectedTeam->title .'   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          $allPlayers = $allPlayers->find("team=$selectedTeam");
          $out .= '<ul>';
          foreach($allPlayers as $p) {
            $allTests = $p->find("template=event, task.name~=fight, refPage!='', date>=$startDate, date<=$endDate, sort=refPage, sort=date");
            $inClassAllTestsCount = $allTests->find("inClass=1")->count();
            if ($allTests->count() > 0) {
              $out_03 = '<ul>';
              $prevDate = '';
              $prevName = '';
              foreach($allTests as $t) {
                switch ($t->task->name) {
                  case 'fight-vv' : $class="success"; $result="VV";
                    break;
                  case 'fight-v' : $class="success"; $result="V";
                    break;
                  case 'fight-r' : $class="danger"; $result="R";
                    break;
                  case 'fight-rr' : $class="danger"; $result="RR";
                    break;
                  default: $class = ""; $result = "";
                }
                if ($prevDate == date('Y-m-d', $t->date) && $prevName == $t->refPage->name) {
                  $error = 'Error detected ?';
                } else {
                  $error = '';
                }
                $out_03 .= '<li>';
                $out_03 .= date('d/m', $t->date).' → '.$t->refPage->title.' [lvl '.$t->refPage->level.'] <span class="label label-'.$class.'">'.$result.'</span> <span class="label label-danger">'.$error.'</span>';
                if ($t->inClass == 1) {
                  $out_03 .= ' [in class]';
                } else {
                  $out_03 .= ' [not in class]';
                }
                $out_03 .='</li>';
                $prevDate = date('Y-m-d', $t->date);
                $prevName = $t->refPage->name;
              }
              $out_03 .= '</ul>';
              $out_02 = '<li><strong>'.$p->title.'</strong> → <span class="label label-success">'.$allTests->count().' fights</span> ['.$inClassAllTestsCount.' in class]</li>';
              $out .= $out_02.$out_03;
            }
          }
          $out .= '</ul>';
        } else {
          $out .= 'You need to select 1 team.';
        }
        break;
      case 'task-report' :
        $taskId = $input->get['taskId'];
        $task = $pages->get("id=$taskId");
        $taskCount = 0;
        if ($selectedTeam && $selectedTeam != '-1' && $taskId!= -1) {
          $out .= '<h3 class="text-center">';
          $out .= '['.$task->title.'] report for '.$selectedTeam->title .'   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          # TODO : Select a period
          $allPlayers = $allPlayers->find("team=$selectedTeam");
          $out .= '<ul>';
          foreach($allPlayers as $p) {
            $prevTask = $p->find("template=event,task=$task, date>$startDate, date<$endDate, sort=-date");
            if ($prevTask->count() > 0) {
              $taskCount += $prevTask->count();
              $out .= '<li>'.$p->title. ': Task found '.$prevTask->count().' time(s).</li>';
              $out .= '<ul>';
              foreach($prevTask as $t) {
                $tDate = strftime("%d/%m/%y", $t->date).' - ';
                $out .= '<li>';
                $out .= $tDate. $t->summary;
                if ($t->inClass == 1) {
                  $out .= ' [in class]';
                }
                $out .= '</li>';
              }
              $out .= '</ul>';
            }
          }
          $out .= '</ul>';
          $out .= '<p class="label label-primary">Total count : '.$taskCount.'</p>';
        } else {
          $out .= 'You need to select a team and a task.';
        }
        break;
      case 'team-options' :
        $out = '';
        if ($selectedTeam && $selectedTeam != '-1') {
          $out .= '</div>';
          $out .= '<h4 class="text-center">';
          $out .= sprintf(__('Team options for %1$s'), $selectedTeam->title);
          $out .= '</h4>';
          $out .= '<ul>';
            // Team Official Period
            if ($user->hasRole('teacher')) {
              $allPeriods = $pages->find("template=period, periodOwner.singleTeacher=$user");
            } else {
              $allPeriods = $pages->find("template=period");
            }
            if ($selectedTeam->periods != false) {
              $officialPeriod = $selectedTeam->periods;
            } else {
              $officialPeriod = false;
            }
            $out .= '<li>';
            $out .=   '<label for="periodId">'.__("Official period").' :&nbsp;</label>';
            $out .=   '<select id="periodId">';
            $out .=     '<option value="-1">'.__("No official period (holidays ?)").'</option>';
            foreach($allPeriods as $p) {
              if ($officialPeriod != false) {
                if ($p->id == $officialPeriod->id) {
                  $status = 'selected="selected"';
                } else {
                  $status = '';
                }
              } else {
                $status = '';
              }
              $out .=   '<option value="'.$p->id.'" '.$status.'>'.$p->title.'</option>';
            }
            $out .= ' </select>';
            $out .= '&nbsp;';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'savePeriod/'.$selectedTeam.'">'.__("Save").'</button>';
            $out .= '</li>';
            // Force Memory Helmet
            $lock = $pages->get("$selectedTeam")->forceHelmet;
            if ($lock == 1) {
              $status = 'checked="checked"';
            } else {
              $status = '';
            }
            $out .= '<li><label for="forceHelmet"><input type="checkbox" id="forceHelmet" '.$status.'> '.__("Force Memory Helmet").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'forceHelmet/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
            // Force Visualizer
            $lock = $pages->get("$selectedTeam")->forceVisualizer;
            if ($lock == 1) {
              $status = 'checked="checked"';
            } else {
              $status = '';
            }
            $out .= '<li><label for="forceVisualizer"><input type="checkbox" id="forceVisualizer" '.$status.'> '.__("Force Visualizer").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'forceVisualizer/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
            // Force Book of Knowledge
            $lock = $pages->get("$selectedTeam")->forceKnowledge;
            if ($lock == 1) {
              $status = 'checked="checked"';
            } else {
              $status = '';
            }
            $out .= '<li><label for="forceKnowledge"><input type="checkbox" id="forceKnowledge" '.$status.'> '.__("Force the Book of Knowledge").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'forceKnowledge/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
            // Lock Fights (Training but no 'tests')
            $lock = $pages->get("$selectedTeam")->lockFights;
            if ($lock == 1) {
              $status = 'checked="checked"';
            } else {
              $status = '';
            }
            $out .= '<li><label for="lockFights"><input type="checkbox" id="lockFights" '.$status.'> '.__("Lock fights").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'toggle-lock/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
            // 'Class activity' tag (ignored for 'motivation' statistic)
            $lock = $pages->get("$selectedTeam")->classActivity;
            if ($lock == 1) {
              $status = 'checked="checked"';
            } else {
              $status = '';
            }
            $out .= '<li><label for="classActivity"><input type="checkbox" id="classActivity" '.$status.'> '.__("Class activity tag").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'classActivity/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
            if ($user->isSuperuser()) {
              // Archive team
              $out .= '<li><label for="archiveTeam"><input type="checkbox" id="archiveTeam"> '.__("Archive").'</label> ';
              $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'archive/'.$selectedTeam.'/1">'.__("Save").'</button>';
              $out .= '</li>';
            };
            // Reset streak
            $out .= '<li><label for="resetStreaks"><input type="checkbox" id="reset-streaks"> '.__("Reset streaks").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'reset-streaks/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
            // Recalculate tmp page
            $out .= '<li><label for="recalculateTmpPage"><input type="checkbox" id="recalculate-tmp"> '.__("Recalculate tmpPage").'</label> ';
            $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'recalculate-tmp/'.$selectedTeam.'/1">'.__("Save").'</button>';
            $out .= '</li>';
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("You need to select a team for more options.").'</p>';
        }
        break;
      case 'addUsers' :
        $newPlayers = $input->post->newPlayers;
        $newUserLines = preg_split("/[\r\n]+/", $newPlayers, -1, PREG_SPLIT_NO_EMPTY);
        $parentPage =  $pages->get('name=players');
        $out = '';
        foreach($newUserLines as $l) {
          $newUser = array_map('trim', explode(',', $l));
          list($title, $lastName, $rank, $team, $teacher) = $newUser;
          if ($title && $title != '') {
            // Generate a random password
            $pass = '';
            $chars = 'abcdefghjkmnopqrstuvwxyz23456789'; // add more as you see fit
            $length = mt_rand(8,8); // 9,12 = password between 9 and 12 characters
            for($n = 0; $n < $length; $n++) $pass .= $chars[mt_rand(0, strlen($chars)-1)];
            // Create player
            $p = new Page();
            $p->template = 'player';
            $p->parent = $parentPage;
            $p->title = $title;
            $p->lastName = $lastName;
            if ($rank && $rank != '') {
              if ($rank == 0 || $rank > 11) { $rank = 11; }
              $r = $pages->get("parent.name=ranks, index=$rank");
              if ($r->id) {
                $p->rank = $r;
              }
            }
            if ($team && $team != '') {
              $newTeam = $pages->get("template=team, name=$team");
              if ($newTeam->id) {
                $p->team = $newTeam;
                if ($teacher && $teacher != '') { // Add teacher
                  $t = $users->get("name=$teacher");
                  if ($t->id) {
                    $newTeam->of(false);
                    $newTeam->teacher->add($t);
                    $newTeam->save();
                  }
                }
              } else { // Create new team
                $newTeam = new Page();
                $newTeam->template = 'team';
                $newTeam->parent = $pages->get("name=teams");
                $newTeam->title = strtoupper($team);
                if ($p->rank) { $newTeam->rank = $p->rank; }
                if ($teacher && $teacher != '') { // Add teacher
                  $t = $users->get("name=$teacher");
                  if ($t->id) {
                    $newTeam->teacher->add($t);
                  }
                }
                $newTeam->save();
                $p->team= $newTeam;
              }
            } else {
              $t = $pages->get("template=team, name=no-team");
              $p->team = $t;
            }
            $p->save();
            initPlayer($p);
            $p->login = $p->name;
            $p->save();
            // Create user (if he doesn't exit)
            $u = $users->get($p->login);
            if ($u == '') { // User does not exist
              $u = $wire->users->add($p->login); // Add new user
              $u->pass = $pass;
              $u->addRole('player');
              $u->save();
            } else {
              $out .= $title.' : <span class="label label-danger">Error (user already exists ?)</span>';
            }
          }
          // Display login/passwords pairs (for admin recup)
          $out .= '<p>Planet Alert login for <b>'.$p->title.'</b> ['.$p->team->title.'] :</p>';
          $out .= 'Username : '.$p->login.'</p>';
          $out .= 'Password : '. $pass.'</p>';
          $out .= '<br />';
        }
        break;
      case 'deleteFromId' :
        $id = $pages->get($confirm); // urlSegment3 used for element's id
        $pages->trash($id);
        break;
      default :
        $out = __('Problem detected.');
    }

    $out .= '<script>';
    $out .= '$(".delete").click( function() { var eventId=$(this).attr("data-eventId"); var action=$(this).attr("data-action"); var playerId=$(this).attr("data-playerId"); var href=$(this).attr("data-href") + action +"/"+ playerId +"/"+ eventId; var that=$(this).parents("tr"); if (confirm("Delete event?")) {$.get(href, function(data) { that.hide(); $("button[data-action=recalculate]").click(); }) };});';
    $out .= '$(".remove").click( function() { var itemId=$(this).attr("data-itemId"); var action = $(this).attr("data-action"); var playerId=$(this).attr("data-playerId"); var href=$(this).attr("data-href") + action +"/"+ playerId +"/"+ itemId; var that=$(this).parents("li"); if (confirm("Remove item?")) {$.get(href, function(data) { that.hide(); }) };});';
    $out .= '$(".confirm").click( function() { var href=$(this).attr("data-href"); var that=$(this); var $urlSeg3 = $("#periodId").val(); if ($urlSeg3) { href = href+"/"+$urlSeg3; }; if (confirm("Proceed?" + href)) {$.get(href, function(data) { that.attr("disabled", true); that.html("Saved!"); $("button[data-action=recalculate]").click(); }) };});';
    $out .= '$(".death").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Please reload!"); $("button[data-action=recalculate]").click();}) };});';
    $out .= '</script>';

    echo $out;
  }
?>
