<?php /* adminActions template */
  if (!$config->ajax) {
    include("./head.inc"); 
?>

<div class="alert alert-warning text-center">Admin Actions : Be careful !</div>
<section class="well">
  <div>
    <span>Select a player : </span>
      <select id="playerId">
        <?php
          foreach($allPlayers as $p) {
            echo "<option value='{$p->id}'>{$p->title} [{$p->playerTeam}]</option>";
          }
        ?>
      </select>
  </div>
  <div>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="view-profile">View profile</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="view-history">View history</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="recalculate">Recalculate scores</button>
  <button class="adminAction btn btn-default" data-href="<?php echo $page->url; ?>" data-action="refPage">RefPage</button>
  </div>
</section>
<section id="ajaxViewport" class="well"></section>
<?php
    include("./foot.inc"); 
  } else { // Ajax call, display requested information
    include("./my-functions.inc"); 
    $allPlayers = $pages->find("template=player");
    $playerId = $input->urlSegment1;
    $action = $input->urlSegment2;

    $selectedPlayer = $pages->get($playerId);
    $selectedPlayer->of(false);

    switch ($action) {
      case 'view-profile' :
        $out = 'Profile of '.$selectedPlayer->title;
        break;
      case 'view-history' :
        $allEvents = $selectedPlayer->get("name=history")->children();
        $out = 'History of '.$selectedPlayer->title;
        $out .= '<ul>';
        foreach($allEvents as $e) {
          $out .= '<li>'.$e->title.'</li>';
        }
        $out .= '</ul>';
        break;
      case 'recalculate' :
        $allEvents = $selectedPlayer->get("name=history")->children()->sort(date);
        $out = 'Recalculate scores from complete History ('. $allEvents->count.' events).';
        $out .= '<ul>';
        // Init scores
        $selectedPlayer = initPlayer($selectedPlayer);
        foreach($allEvents as $e) {
          $out .= '<li>';
          $out .= strftime("%d/%m", $e->date).' - ';
          $out .= $e->title;
          if ($e->task) {
            $out .= '['.$e->task->name.' / '. $e->task->category->name.']';
            if ($e->task->name == 'donation') { // Player gave GC, increase his Donation
              $comment = $e->summary;
              preg_match("/\d+/", $comment, $matches);
              $out .= $e->summary.' - '.$matches[0];
              $selectedPlayer->donation = $selectedPlayer->donation + $matches[0];
            }
            if ($e->task->name == 'donated') { // Player received GC, increase his GC
              $comment = $e->summary;
              preg_match("/\d+/", $comment, $matches);
              $out .= $e->summary.' - '.$matches[0];
              $selectedPlayer->GC = $selectedPlayer->GC + $matches[0];
            }
            if ($e->task->name == 'buy' || $e->task->name == 'free') { // New equipment, place or potion, add it accordingly
              $comment = $e->summary;
              $out .= $comment;
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
            $out .= displayPlayerScores($selectedPlayer);
          }
          $out .='</li>';
        }
        $out .= '</ul>';
        $out .= displayPlayerScores($selectedPlayer);
        break;
      case 'refPage' :
        echo $allPlayers;
        $out .= '<ul>';
        foreach($allPlayers as $p) {
          $p->of(false);
          $allEvents = $p->get("name=history")->children("task.name=buy|free");
          foreach($allEvents as $e) {
            $e->of(false);
            $out .= '<li>';
            $out .= strftime("%d/%m", $e->date).' - ';
            $out .= $e->title;
            $comment = $e->summary;
            $out .= $comment;
            if ($e->refPage == false) {
              // Compare summary to equipment or place title
              $refPage = $pages->get("title=$comment");
              if ($refPage && $refPage->id != 0) {
                $out .= $refPage->id;
                $e->refPage = $refPage;
              } else {
                $out .= ' Page not found.';
              }
              $e->save();
            }
            $out .='</li>';
          }
        }
        $out .= '</ul>';
        break;
      default :
        $out = 'Problem detected.';
    }

    echo $out;
?>
<?php
  }
?>

