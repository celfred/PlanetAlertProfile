<?php /* adminActions template */
  if (!$config->ajax) {
    include("./head.inc"); 

    if ($user->isSuperuser()) {
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
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="clean-history">Clean history</button>
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
    $allPlayers = $pages->find("template=player");
    $action = $input->urlSegment1;
    $playerId = $input->urlSegment2;

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
              $out .= $comment;
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
      case 'clean-history' :
        if ($selectedPlayer) {
          $allEvents = $selectedPlayer->find("template=event");
          /* $allEvents = $pages->find("template=event"); */
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
              $out .= '<span class="label label-success">OK</span></li>';
            }
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
            $out .= '<tr><td class="text-left">';
            $out .= '▶ '.strftime("%d/%m", $e->date).' - ';
            $out .= $e->title;
            if ($e->task) {
              /* $out .= '['.$e->task->name.' / '. $e->task->category->name.']'; */
              if ($e->task->name == 'donation') { // Player gave GC, increase his Donation
                $comment = $e->summary;
                preg_match("/\d+/", $comment, $matches);
                /* $out .= $e->summary.' - '.$matches[0]; */
                $out .= ' '.$comment;
                $selectedPlayer->donation = $selectedPlayer->donation + $matches[0];
              }
              if ($e->task->name == 'donated') { // Player received GC, increase his GC
                $comment = $e->summary;
                preg_match("/\d+/", $comment, $matches);
                /* $out .= $e->summary.' - '.$matches[0]; */
                $out .= ' '.$comment;
                $selectedPlayer->GC = $selectedPlayer->GC + $matches[0];
              }
              if ($e->task->name == 'ut-action-v' || $e->task->name == 'ut-action-vv') { // Underground trining, increase UT
                $comment = $e->summary;
                preg_match("/\+(\d+)/", $comment, $matches);
                /* $out .= $e->summary.' - '.$matches[1]; */
                $out .= ' '.$comment;
                $selectedPlayer->underground_training = $selectedPlayer->underground_training + $matches[0];
              }
              if ($e->task->name == 'buy' || $e->task->name == 'free') { // New equipment, place or potion, add it accordingly
                $out .= ' ['.$e->refPage->title.']';
                // Get item's data
                if ($e->refPage) {
                  $newItem = $pages->get("$e->refPage");
                  // Set new values
                  $selectedPlayer->GC = (int) $selectedPlayer->GC - $newItem->GC;
                  if ($newItem->template == 'equipment' || $newItem->template == 'item') {
                    switch($newItem->parent->name) {
                      case 'potions' : // instant use potions?
                        // If healing potion
                        $selectedPlayer->HP = $selectedPlayer->HP + $newItem->HP;
                        if ($selectedPlayer->HP > 50) {
                          $selectedPlayer->HP = 50;
                        }
                        break;
                      default:
                        $selectedPlayer->equipment->add($newItem);
                    }
                  }
                  if ($newItem->template == 'place') {
                    $selectedPlayer->places->add($newItem);
                  }
                }
              }
              updateScore($selectedPlayer, $e->task);
              $out .= '<br />';
              $out .= displayTrendScores($selectedPlayer, $oldPlayer);
              $out .= displayPlayerScores($selectedPlayer);
            }
            $out .='</td></tr>';
          }
          $out .= '</table>';
          $out .= displayPlayerScores($initialPlayer, 'previous');
          $out .= '<br />';
          $out .= displayPlayerScores($selectedPlayer);
          $out .= '<br /><br />';
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
      case 'trash' :
        $eventId = $pages->get($input->urlSegment2);
        $pages->trash($eventId);
        break;
      default :
        $out = 'Problem detected.';
    }

    $out .= '<script>';
    $out .= '$(".delete").click( function() { var eventId=$(this).attr("data-eventId"); var action=$(this).attr("data-action"); var href=$(this).attr("data-href") + action +"/"+ eventId; var that=$(this).parents("tr"); if (confirm("Delete event?")) {$.get(href, function(data) { that.hide(); }) };});';
    $out .= '$(".confirm").click( function() { var href=$(this).attr("data-href"); var that=$(this); if (confirm("Proceed?")) {$.get(href, function(data) { that.attr("disabled", true); that.html("Saved!"); }) };});';
    $out .= '</script>';

    echo $out;
  }
?>
