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
          /* echo "<option value='all'>All players</option>"; */
          foreach($allPlayers as $p) {
            echo "<option value='{$p->id}'>{$p->title} [{$p->playerTeam}]</option>";
          }
        ?>
      </select>
  </div>
  <div>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="refPage">Set refPage</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="helmet">Check Memory helmet</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="ut">Check UT scoreboard</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="clean-history">Clean history</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="add-death">Add death</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="view-history">View history</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="recalculate">Recalculate scores</button>
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
        break;
      case '-1' :
        $selectedPlayer = false;
        break;
      default :
        $selectedPlayer = $pages->get($playerId);
    }

    switch ($action) {
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
          $allEvents = $selectedPlayer->get("name=history")->children()->sort(date);
          $death = $pages->get('name=death');
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
          // Team and group loss because of player's death
          $teamPlayers = $pages->find("template=player, playerTeam=$selectedPlayer->playerTeam")->not("group=$selectedPlayer->group");
          $groupMembers = $teamPlayers->find("group=$selectedPlayer->group")->not("$selectedPlayer->id");
          // Each group member suffers
          foreach($groupMembers as $p) {
            $groupDeath = $pages->get("name=group-death");
            $comment = 'Group member died!';
            saveHistory($p, $groupDeath, $comment, 0, '', $deathDate);
          }
          // Each team member suffers
          foreach($teamPlayers as $p) {
            $teamDeath = $pages->get("name=team-death");
            $comment = 'Team member died!';
            saveHistory($p, $teamDeath, $comment, 0, '', $deathDate);
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
          /* $allEvents = $selectedPlayer->find("template=event")->sort('-date'); */
          $allEvents = $selectedPlayer->get("name=history")->children()->sort(date);
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
        } else {
          $out .= 'You need to select 1 player.';
        }
        break;
      case 'view-history' :
        if ($selectedPlayer) {
          $allEvents = $selectedPlayer->get("name=history")->children()->sort('-date');
          $out = 'History of '.$selectedPlayer->title.' ['.$allEvents->count.' events].';
          $out .= '<table class="table table-condensed table-hover">';
          foreach($allEvents as $e) {
            $out .= '<tr><td class="text-left">';
            $out .= strftime("%d/%m", $e->date).' - ';
            $out .= $e->title;
            $comment = trim($e->summary);
            if ($comment) {
              $out .= ' ['.$comment.']';
            }
            $out .= '  <button class="delete btn btn-xs btn-warning" data-href="'.$page->url.'" data-eventId="'.$e->id.'" data-action="trash">Delete</button>';
            // Direct link to manually edit page
            $out .= ' <a class="btn btn-xs btn-primary" href="'.$config->urls->admin.'page/edit/?id='.$e->id.'" target="_blank">Edit page in Backend</a>';
            $out .= '</td></tr>';
          }
          $out .= '</table>';
        } else {
          $out .= 'You need to select 1 player.';
        }
        break;
      case 'recalculate' :
        if ($selectedPlayer) {
          $allEvents = $selectedPlayer->get("name=history")->children()->sort(date);
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
                if ($diff <= 0) { // Check for Donation bug
                  $out .= ' <span class="label label-danger">Error';
                  $out .= ' ⇒ Amount replaced : '.$selectedPlayer->GC;
                  $out .= '</span>';
                  $comment = preg_replace("/\d+/", $selectedPlayer->GC, $comment);
                  $dirty = true;
                }
              }
              if ($e->task->is("name=buy|free")) { // New equipment, place or potion, add it accordingly
                if ($e->refPage->GC > $selectedPlayer->GC) {
                  $out .= ' <span class="label label-danger">Error : Not enough GC.</span>';
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
                    // [unlocked] or [bought] ?
                    // refPage should be set accordingly but prevention here for backward compatibility
                    preg_match("/\[unlocked\]/", $comment, $matches);
                    if ($matches[0]) {
                      $dirty = true;
                      $out .= ' <span class="label label-danger">[unlocked] found, but refPage set to "Buy" instead of "Bought".</span>';
                    } else {
                      // Check if group members have [unlocked] item
                      $members = $allPlayers->find("playerTeam=$selectedPlayer->playerTeam, group=$selectedPlayer->group")->not("$selectedPlayer->id");
                      foreach ($members as $p) {
                        $bought = $p->get("name=history")->get("task.name=bought, refPage=$newItem, summary*=[unlocked]");
                        if ($bought->id) {
                          $out .= '1';
                        } else {
                          $dirty = true;
                          $out.= '0';
                        }
                      }
                      if ($dirty) {
                        $out .= ' <span class="label label-danger"> Make sure [unlocked] exists in the group and set refPage to "Bought".</span>';
                      }
                    }
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
                    $out .= '<span class="label label-danger">Death here?</span>';
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
              $out .= '  <button class="delete btn btn-xs btn-danger" data-href="'.$page->url.'" data-eventId="'.$e->id.'" data-action="trash">Delete</button>';
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
        $eventId = $pages->get($input->urlSegment2);
        $pages->trash($eventId);
        break;
      default :
        $out = 'Problem detected.';
    }

    $out .= '<script>';
    $out .= '$(".delete").click( function() { var eventId=$(this).attr("data-eventId"); var action=$(this).attr("data-action"); var href=$(this).attr("data-href") + action +"/"+ eventId; var that=$(this).parents("tr"); if (confirm("Delete event?")) {$.get(href, function(data) { that.hide(); $("button[data-action=recalculate]").click(); }) };});';
    $out .= '$(".remove").click( function() { var itemId=$(this).attr("data-itemId"); var action = $(this).attr("data-action"); var playerId=$(this).attr("data-playerId"); var href=$(this).attr("data-href") + action +"/"+ playerId +"/"+ itemId; var that=$(this).parents("li"); if (confirm("Remove item?")) {$.get(href, function(data) { that.hide(); }) };});';
    $out .= '$(".confirm").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Saved!"); }) };});';
    $out .= '$(".death").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Please reload!"); $("button[data-action=recalculate]").click();}) };});';
    $out .= '</script>';

    echo $out;
  }
?>
