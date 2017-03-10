<?php /* adminActions template */
  $out = '';
  if (!$config->ajax) {
    include("./head.inc"); 
    $allTeams = $pages->find("template=team")->sort("title");
    if ($user->isSuperuser()) {
      $action = $input->urlSegment1;
      $allPlayers->sort("team.name, title");
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
        break;
      case 'fights-stats' :
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
        break;
      case 'task-report' :
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
        break;
      case 'reports' :
        $out .= '<section class="well">';
        $out .= '<span>Select a team : </span>';
        $out .= '<select id="teamId">';
        $out .= '<option value="-1">Select a team</option>';
        foreach($allTeams as $p) {
          $out .= '<option value="'.$p->id.'">'.$p->title.'</option>';
        }
        $out .= '</select>';
        $out .= '</section>';
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="reports">Generate</button>';
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
          $out .= '<option value="'.$p->id.'">'.$p->title.' ['.$p->team->title.']</option>';
        }
        $out .= '</select>';
        $out .= '</div>';
        $out .= '<div class="text-right">';
        $out .= '<a id="backendEditable" class="btn btn-success" href="'.$config->urls->admin.'page/edit/?id=" data-id="-1">Edit player in backend</a>';
        $out .= '</div>';
        $out .= '</section>';
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="recalculate">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      case 'team-options' :
        $out .= '<section class="well">';
        $out .= '<h3 class="text-center">';
        $out .=   'Global options';
        $out .= '</h3>';
        $out .= '<div>';
        $allPeriods = $pages->find("template=period");
        $officialPeriod = $page->periods;
        $out .=   '<span>Official period : </span>';
        $out .=   '<select id="periodId">';
        $out .=     '<option value="-1">Select a period</option>';
        foreach($allPeriods as $p) {
          if ($p->id == $officialPeriod->id) {
            $status = 'selected="selected"';
          } else {
            $status = '';
          }
          $out .=   '<option value="'.$p->id.'" '.$status.'>'.$p->title.'</option>';
        }
        $out .= ' </select>';
        $out .= '<button class="proceed btn btn-block btn-primary" data-href="'.$page->url.'" data-action="save-options">Save</button>';
        $out .= '<div class="proceedFeedback"></div>';
        $out .= '</div>';
        $out .= '</section>';
        $out .= '<section class="well">';
        $out .= '<h3 class="text-center">';
        $out .=   'Team options';
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
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="team-options">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      case 'setKarma' :
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
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="setKarma">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        break;
      case 'setScores' :
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
        break;
      case 'users' :
        $allPlayers = $pages->find("template=player")->sort("title");
        $out .= '<section class="well">';
        $out .= '<p><span class="glyphicon glyphicon-alert"></span> 1 player / line → Name [,lastName] [,rank =6emes,5emes,4emes,3emes)] [,team]</p>';
        $out .= '<textarea id="newPlayers" name="newPlayers" rows="5" cols="50"></textarea>';
        $out .= '<button class="addUsers btn btn-primary btn-block" data-href="'.$page->url.'" data-action="addUsers">Add new players</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
        $out .= '<p>There are currently '.$allPlayers->count().' players.</p>';
        $out .= '<table class="table table-condensed table-hover">';
        $out .= '<th>Player</th>';
        $out .= '<th>Team</th>';
        $out .= '<th>User</th>';
        $out .= '<th>Edit</th>';
        $out .= '<th>Delete</th>';
        $allUsers = $users->find("name!=admin|guest");
        foreach ($allPlayers as $p) {
          $u = $users->get("name=$p->login");
          $out .= '<tr>';
          $out .= '<td>'.$p->title.'</td>';
          $out .= '<td>'.$p->team->title.'</td>';
          $out .= '<td>'.$u->name.'</td>';
          $out .= '<td><a class="btn btn-xs btn-success" href="'.$config->urls->admin.'page/edit/?id='.$p->id.'">Edit page in backend</td>';
          $out .= '<td><button class="removeUser btn btn-xs btn-danger" data-href="'.$page->url.'" data-action="removeUser" data-playerId="'.$p->id.'">Delete Player/User</button></td>';
          $out .= '</tr>';
        }
        $out .= '</table>';
        $out .= '<div>';
        break;
      default :
        $out .= '<button class="adminAction btn btn-primary btn-block" data-href="'.$page->url.'" data-action="script">Generate</button>';
        $out .= '<section id="ajaxViewport" class="well"></section>';
?>
  <div>
<?php
    }
  } else { // End if admin
    $out .= 'Admin only.';
  }
  $out .= '</div>';
  echo $out;
  include("./foot.inc"); 
  echo '<script>';
  echo '$(".addUsers").click( function() { var myData = $(\'#newPlayers\').val(); var action=$(this).attr("data-action"); var href=$(this).attr("data-href")+action; var that=$(this); if (confirm("Proceed?")) {$.post(href, {newPlayers:myData}, function(data) { $("#ajaxViewport").html(data); }) };});';
  echo '$(".removeUser").click( function() {  var playerId=$(this).attr("data-playerId"); var action = $(this).attr("data-action"); var href=$(this).attr("data-href")+action+"/"+playerId+"/1"; var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); $("#ajaxViewport").html(data);that.html("User deleted. Please reload!"); })}});';
  echo '</script>';
  } else { // Ajax call, display requested information
    include("./my-functions.inc"); 
    $allPlayers = $pages->find("template=player")->sort("team.name, title");
    $action = $input->urlSegment1;
    $playerId = $input->urlSegment2;
    $confirm = $input->urlSegment3;
    $type = $input->get["type"];
    $unique = true;
    $startDate = $input->get["startDate"]; 
    $endDate = $input->get["endDate"]; 
    if ($startDate == '') {
      $startDate = date('2000-01-01 00:00:00');
    }
    if ($endDate == '') {
      $endDate = date('Y-m-d 23:59:59');
    }
    if ($action == 'toggle-lock' || $action == 'archive' || $action == 'forceHelmet') {
      $type = 'team';
    }

    if ($type == 'team') {
      if ($playerId != '-1') {
        $selectedTeam = $pages->get("id=$playerId");
      } else {
        $selectedTeam = '-1';
      }
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
        $allPlayers = $pages->find("template=player");
        $ambassador = $pages->get("name=ambassador");
        foreach($allPlayers as $p) {
          $p->streak = 0;
          $allEvents = $p->get("name=history")->children("template=event, task.name!=donation|donated|absent, sort=-date, limit=10")->sort('date');
          foreach($allEvents as $e) {
            setStreak($p, $e->task);
          }
          $p->of(false);
          $p->save();
        }
        break;
      case 'reports' :
        $out .='<script type="text/javascript" src="'.$config->urls->templates.'scripts/main.js"></script>';
        if ($selectedTeam && $selectedTeam != '-1') {
          $allPeriods = $pages->get("name=periods")->children();
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
            $out .= '<option value="'.$period->id.'">'.$period->title.'</option>';
          }
          $out .= '</select>';
          $out .= '</div>';
          $out .= '<div>';
          $out .= '<span>Select a player : </span>';
          $out .= '<select id="reportPlayer">';
          $out .= '<option value="'.$selectedTeam->name.'">The whole team</option>';
          foreach($allPlayers as $player) {
            $out .= '<option value="'.$player->name.'">'.$player->title.'</option>';
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
      case 'add-death' :
        if ($selectedPlayer) {
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
            $teamPlayers = $pages->find("template=player, team=$selectedPlayer->team, group!=$selectedPlayer->group");
            foreach($teamPlayers as $p) {
              saveHistory($p, $teamDeath, 0);
            }
            // Each group member suffers from player's death
            $groupMembers = $pages->find("template=player, team=$selectedPlayer->team, group=$selectedPlayer->group, id!=$selectedPlayer->id");
            $groupDeath = $pages->get("name=group-death");
            $groupDeath->comment = 'Group member died! ['.$selectedPlayer->title.']';
            $teamDeath->refPage = $selectedPlayer;
            $groupDeath->eDate = $task->eDate;
            $groupDeath->linkedId = $linkedId;
            foreach($groupMembers as $p) {
              saveHistory($p, $groupDeath, 0);
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
        $allMonsters = $pages->find("template=exercise")->sort("level, title");
        $out .= '<h3>Best players among '.$allPlayers->count().' players.</h3>';
        $out .= '<ul>';
        foreach($allMonsters as $m) {
          $bestUt = $m->best;
          $out .= '<li>'.$m->title.' [Current best : '.$m->mostTrained->title.' ['.$m->mostTrained->team->title.'] : '.$bestUt.']';
          foreach($allPlayers as $p) {
            $playerUt = utGain($m, $p);
            $p->ut = $playerUt;
          }
          $allPlayers->sort("-ut");
          if ($allPlayers->first()->ut != $m->best) {
            $out .= ' <span class="label label-danger">Error</span>';
            $out .= ' - New best : '.$allPlayers->first()->title.' ['.$allPlayers->first()->team->title.'] ⇒'.$allPlayers->first()->ut;
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
          $out = '<h3>'.$selectedPlayer->title.' ['.$selectedPlayer->team->title.'] → Recalculate scores from complete history ('. $allEvents->count.' events).</h3>';
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
                    // Check if group members have [unlocked] item
                    $members = $allPlayers->find("team=$selectedPlayer->team, group=$selectedPlayer->group");
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
                    if (isset($matches[0]) && $e->task->is("name=buy")) {
                      $dirty = true;
                      $out .= ' <span class="label label-danger">Error : [unlocked] found, but task page set to "Buy" instead of "Bought".</span>';
                    }
                    if (!isset($matches[0]) && $e->task->is("name=bought")) {
                      $dirty = true;
                      $out .= ' <span class="label label-danger">Error : [unlocked] NOT found, but task page set to "Bought" instead of "Buy".</span>';
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
                  $out .= ' ['.$discount->title.'% discount]';
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
              $out .= displayTrendScores($selectedPlayer, $oldPlayer);
              $out .= displayPlayerScores($selectedPlayer);
              $out .= '  ';
              // Direct link to manually edit page
              $out .= ' <a class="btn btn-xs btn-primary" href="'.$config->urls->admin.'page/edit/?id='.$e->id.'" target="_blank">Edit page in Backend</a>';
              // Delete event link
              $out .= '  <button class="delete btn btn-xs btn-danger" data-href="'.$page->url.'" data-playerId="'.$selectedPlayer->id.'" data-eventId="'.$e->id.'" data-action="trash">Delete</button>';
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
          $out .= displayPlayerScores($initialPlayer, 'previous');
          $out .= '<br />';
          $out .= displayPlayerScores($selectedPlayer);
          $out .= '<br /><br />';
          if ($dirty) {
            $out .= '<h4><span class="label label-danger">Error detected! You should check history before saving anything !</span></h4>';
          }
          if ($input->urlSegment3 && $input->urlSegment3 == 1) {
            $officialPeriod = $pages->get("name=admin-actions")->periods;
            $newCount = setHomework($selectedPlayer, $officialPeriod->dateStart, $officialPeriod->dateEnd);
            $lastPenalty = $allEvents->find("task.category.name=homework, date>=$officialPeriod->dateStart, date<=$officialPeriod->dateEnd")->sort("-date")->first(); // Get last Hk pb
            if ($lastPenalty->task->is("name=penalty")) {
              $newCount = 0;
            }
            $selectedPlayer->hkcount = $newCount;
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
      case 'save-options':
        $allPlayers = $pages->find("template=player");
        $id = $input->urlSegment2;
        $officialPeriod = $pages->get("id=$id");
        $page->of(false);
        $page->periods = $officialPeriod;
        $page->save();
        $session->officialPeriod = $officialPeriod;
        $now = time();
        if ($now < $session->officialPeriod->dateStart || $now > $session->officialPeriod->dateEnd) {
          echo '<div class="notification alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Today\'s date is OUT OF the official period dates !</div>';
        }
        // TODO : Might be too long to recalculate hkcount over a long period with many events...
        foreach($allPlayers as $p) {
          $newCount = setHomework($p, $officialPeriod->dateStart, $officialPeriod->dateEnd);
          if ($newCount != $p->hkcount) {
            $p->hkcount = $newCount;
            $p->of(false);
            $p->save();
          }
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
      case 'saveKarma':
        $selectedTeam = $pages->get("$input->urlSegment2");
        $allPlayers = $allPlayers->find("team=$selectedTeam");
        foreach($allPlayers as $p) {
          $p->karma = setKarma($p);
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
      case 'archive':
        $allPlayers = $pages->find("template=player, team=$selectedTeam");
        foreach($allPlayers as $p) {
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
            $currentHistory->save();
          }
          // 'Init' player
          $p->of(false);
          $p->HP = 50;
          $p->team = '';
          $p->group = '';
          $p->rank = '';
          $p->save();
        }
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
        }
        break;
      case 'removeUser' :
        $playerPage = $pages->get("id=$playerId");
        $u = $users->get("name=$playerPage->login");
        $users->delete($u);
        $pages->trash($playerPage);
        break;
      case 'ut-stats' :
        if ($selectedPlayer) {
          $out .= '<h3>';
          $out .= 'UT Stats for '.$selectedPlayer->title.' ['.$selectedPlayer->team->title.']   ';
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
          $out .= 'UT Stats for '.$selectedTeam->title .'   ';
          $out .= 'from '.$startDate.' ';
          $out .= 'to '.$endDate;
          $out .= '</h3>';
          $allMonsters = $pages->find("template=exercise")->sort("level, title");
          $allPlayers = $allPlayers->find("team=$selectedTeam");
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
            $allTests = $p->find("template=event, task.name=test-vv|test-v|test-r|test-rr, refPage!='', date>=$startDate, date<=$endDate, sort=refPage, sort=date");
            if ($allTests->count() > 0) {
              $out_03 = '<ul>';
              $prevDate = '';
              $prevName = '';
              foreach($allTests as $t) {
                switch ($t->task->name) {
                  case 'test-vv' : $class="success"; $result="VV";
                    break;
                  case 'test-v' : $class="success"; $result="V";
                    break;
                  case 'test-r' : $class="danger"; $result="R";
                    break;
                  case 'test-rr' : $class="danger"; $result="RR";
                    break;
                  default: $class = ""; $result = "";
                }
                if ( $prevDate == date('Y-m-d', $t->date) && $prevName == $t->refPage->name) {
                  $error = 'Error detected ?';
                } else {
                  $error = '';
                }
                $out_03 .= '<li>'.date('d/m', $t->date).' → '.$t->refPage->title.' [lvl '.$t->refPage->level.'] <span class="label label-'.$class.'">'.$result.'</span> <span class="label label-danger">'.$error.'</span></li>';
                $prevDate = date('Y-m-d', $t->date);
                $prevName = $t->refPage->name;
              }
              $out_03 .= '</ul>';
              $out_02 = '<li><strong>'.$p->title.'</strong> → <span class="label label-success">'.$allTests->count().' fights</span></li>';
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
          $allPlayers = $allPlayers->find("team=$selectedTeam");
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
      case 'team-options' :
        $out = '';
        if ($selectedTeam && $selectedTeam != '-1') {
          $out .= '</div>';
          $out .= '<h4 class="text-center">';
          $out .=   'Team options for '.$selectedTeam->title;
          $out .= '</h4>';
          $out .= '<ul>';
          $lock = $pages->get("$selectedTeam")->forceHelmet;
          if ($lock == 1) {
            $status = 'checked="checked"';
          } else {
            $status = '';
          }
          $out .= '<li><label for="forceHelmet"><input type="checkbox" id="forceHelmet" '.$status.'> Force Memory Helmet</label> ';
          $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'forceHelmet/'.$selectedTeam.'/1">Save</button>';
          $out .= '</li>';
          $lock = $pages->get("$selectedTeam")->lockFights;
          if ($lock == 1) {
            $status = 'checked="checked"';
          } else {
            $status = '';
          }
          $out .= '<li><label for="lockFights"><input type="checkbox" id="lockFights" '.$status.'> Lock fights</label> ';
          $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'toggle-lock/'.$selectedTeam.'/1">Save</button>';
          $out .= '</li>';

          $out .= '<li><label for="archiveTeam"><input type="checkbox" id="archiveTeam"> Archive</label> ';
          $out .= '<button class="confirm btn btn-primary" data-href="'.$page->url.'archive/'.$selectedTeam.'/1">Save</button>';
          $out .= '</li>';
          $out .= '</ul>';
        } else {
          $out .= '<p>You need to select a team for more options.</p>';
        }
        break;
      case 'addUsers' :
        $newPlayers = $input->post->newPlayers;
        $newUserLines = preg_split("/[r\n]+/", $newPlayers, -1, PREG_SPLIT_NO_EMPTY);
        $out = '';
        foreach($newUserLines as $l) {
          $newUser = array_map('trim', explode(',', $l));
          list($title, $lastName, $rank, $team) = $newUser;
          if ($title && $title != '') {
            // Generate a random password
            $pass = '';
            $chars = 'abcdefghjkmnopqrstuvwxyz23456789'; // add more as you see fit
            $length = mt_rand(8,8); // 9,12 = password between 9 and 12 characters
            for($n = 0; $n < $length; $n++) $pass .= $chars[mt_rand(0, strlen($chars)-1)];
            // Create player
            $p = new Page();
            $p->template = 'player';
            $p->parent = $pages->get('name=players');
            $p->title = $title;
            $p->lastName = $lastName;
            if ($rank && $rank != '') {
              $r = $pages->get("parent.name=ranks, name=$rank");
              if ($r->id) {
                $p->rank = $r;
              }
            }
            if ($team && $team != '') {
              $t = $pages->get("template=team, name=$team");
              if ($t->id) {
                $p->team = $t;
              } else { // Create new team
                $newTeam = new Page();
                $newTeam->template = team;
                $newTeam->parent = $pages->get("name=teams");
                $newTeam->title = strtoupper($team);
                if ($p->rank) { $newTeam->rank = $p->rank; }
                $newTeam->save();
                $p->team= $newTeam;
              }
            } else {
              $t = $pages->get("template=team, name=no-team");
              $p->team = $t;
            }
            initPlayer($p);
            $p->save();
            $p->login = $p->name;
            $p->save(login);
            // Create user (if he doesn't exit)
            $u = $users->get($p->login);
            if ($u == '') { // User does not exist
              $u = $wire->users->add($p->login); // Add new user
              $u->pass = $pass;
              $u->addRole('guest');
              $u->save();
            } else {
              $out .= $title.' : <span class="label label-danger">Error</span>';
            }
          }
          // Display login/passwords pairs (for admin recup)
          $out .= '<p>Planet Alert login for <b>'.$p->title.'</b> ['.$p->team->title.'] :</p>';
          $out .= 'Username : '.$p->login.'</p>';
          $out .= 'Password : '. $pass.'</p>';
          $out .= '<br />';
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
