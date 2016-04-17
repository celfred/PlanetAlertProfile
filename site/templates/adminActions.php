<?php /* adminActions template */
  if (!$config->ajax) {
    include("./head.inc"); 

    if ($user->isSuperuser()) {
      $allPlayers->sort("playerTeam, title");
?>

<div class="alert alert-warning text-center">Admin Actions : Be careful !</div>
<section class="well">
  <div>
    <span>Select a player : </span>
      <select id="playerId">
        <?php
          echo "<option value='-1'>Select a player</option>";
          echo "<option value='all'>All players</option>";
          foreach($allPlayers as $p) {
            echo "<option value='{$p->id}'>{$p->title} [{$p->playerTeam}]</option>";
          }
        ?>
      </select>
  </div>
  <div>
  <!-- 
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="refPage">Set refPage</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="helmet">Check Memory helmet</button>
  -->
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="ut">Check UT scoreboard</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="clean-history">Clean history</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="recalculate">Recalculate scores</button>
  <!--
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="script">Script</button>
  -->
  </div>
</section>
<section id="ajaxViewport" class="well"></section>
<?php
    } else {
      echo 'Admin only.';
    }
    include("./foot.inc"); 
  } else { // Ajax call, display requested information
    include("./my-functions.inc"); 
    $allPlayers = $pages->find("template=player")->sort("playerTeam, title");
    $action = $input->urlSegment1;
    $playerId = $input->urlSegment2;
    $confirm = $input->urlSegment3;
    $unique = true;

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

    switch ($action) {
      case 'script' :
        $allEvents = $pages->find("template=event, summary~='team died'");
        $out .= $allEvents->count();
        $out .= '<br />';
        $title = 'Team Death';
        foreach($allEvents as $e) {
          $out .= $e->id.': '.$e->title. ' → '.$title.'<br />';
          $e->of(false);
          $e->title = $title;
          $e->save();
        }
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
          saveHistory($selectedPlayer, $death, $comment, 0, '', $deathDate);
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
              saveHistory($p, $teamDeath, $comment, 0, '', $deathDate);
            }
            // Each group member suffers from player's death
            $groupMembers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam, group=$selectedPlayer->group")->not("$selectedPlayer->id");
            $groupDeath = $pages->get("name=group-death");
            foreach($groupMembers as $p) {
              $comment = 'Group member died!';
              saveHistory($p, $groupDeath, $comment, 0, '', $deathDate);
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
                      saveHistory($m, $buy, $comment, 0, $helmet, $eDate);
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
        $allMonsters = $pages->find("template=exercise");

        $out .= 'Total # of players : '.$allPlayers->count();
        $out .= '<ul>';
        foreach($allMonsters as $m) {
          $bestUt = utGain($m, $m->mostTrained);
          $out .= '<li>'.$m->title.' ['.$m->mostTrained->title.' ['.$m->mostTrained->playerTeam.'] : '.$bestUt.']';
          foreach($allPlayers as $p) {
            $playerUt = utGain($m, $p);
            $p->ut = $playerUt;
          }
          $allPlayers->sort("-ut");
          if ($allPlayers->first()->id != $m->mostTrained->id) {
            $out .= ' <span class="label label-danger">Error</span>';
            if ($confirm == 1) { // Save new best players
              $m->of(false);
              $m->mostTrained = $allPlayers->first();
              $m->save();
            }
          } else {
            $out .= ' <span class="label label-success">OK</span>';
          }
          $out .= ' : '.$allPlayers->first()->title.' ['.$allPlayers->first()->playerTeam.'] ⇒'.$allPlayers->first()->ut;
          $out .= '</li>';
        }
        $out .= '</ul>';
        $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'ut/all/1">Save now!</button>';
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
          $out = 'Recalculate scores from complete history ('. $allEvents->count.' events). &nbsp;&nbsp;';
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
              updateScore($selectedPlayer, $e->task, $comment, $e->refPage, false);
              // Test if player died
              if ($selectedPlayer->HP == 0) {
                $died = true;
                if ($allEvents->getNext($e)->task->name == 'death') {
                  $out .= '<span class="label label-success">Death OK</span>';
                } else {
                  $dirty = true;
                  // Ask only for the first Death
                  if ($unique == true) {
                    $out .= '<span class="label label-danger">Error : Death here?</span>';
                    // Button Add death here
                    $out .= '<button class="death" data-href="'.$page->url.'add-death/'.$playerId.'/'.$e->id.'">Add death</button>';
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
            $out .= '<button class="confirm btn btn-block btn-primary" data-href="'.$page->url.'recalculate/'.$playerId.'/1">Recalculate now!</button>';
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
          $teamPlayers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam");
          foreach($teamPlayers as $p) {
            $linkedDeath = $p->get("name=history")->get("template=event, task.name=group-death|team-death, date=$event->date");
            if ($linkedDeath->id) {
              $pages->trash($linkedDeath);
            }
          }
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
