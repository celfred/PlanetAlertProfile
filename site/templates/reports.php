<?php // Team report template

include("./head.inc"); 

if ($user->isSuperuser()) {
  // List of teams (taken from fead.inc)
  echo '<span style="margin: 5px 20px; font-size: 20px;">';
  echo '<span>Team reports : </span>';
  foreach($uniqueResults as $player) {
    echo "<span class='btn btn-primary'><a class='ajax' href='{$pages->get('/report_generator')->url}{$sanitizer->pageName($player->team)}'>{$player->team}</a></span> ";
  }
  echo '</span>';
  echo '<span style="margin: 5px 20px; font-size: 20px;">';
  // List of players
  echo '<span>Player reports : </span>';
  echo '<select id="players_list">';
  foreach($players as $player) {
    echo "<option value='{$pages->get('/report_generator')->url}{$sanitizer->pageName($player->team)}/{$sanitizer->pageName($player->id)}'>{$player->title} ({$player->team})</a></option>";
  }
  echo '</select>';
  echo "<button id='report_button'>Generate</button>";
  echo '</span>';

  /*
  if ($input->urlSegment1) {
    $team = $input->urlSegment1;
    $allPlayers = $pages->find("team=$team, template=player, sort=title");

    if ($input->urlSegment2) {
      $catId = $input->urlSegment2;
      $category = $pages->get($catId);
      echo 'Team report : '.$team. ' - '. $category->title;
    } else {
      echo 'Team report : '.$team;
    }
  } else { // List 1 player's history
    $playerId = $input->get("playerId");
    $selectedPlayer = $pages->get("$playerId");

    echo 'Report : '.$selectedPlayer->title .' ('. $selectedPlayer->team.')';

    // List all recorded events for selected player
    $events = $selectedPlayer->find("template=event, sort=category");
  }


  echo '<ul>';
  if (!$selectedPlayer) {
    foreach($allPlayers as $player) {
      if ($category) {
        // List only players concerned with the category
        $events = $player->find("template=event, category=$category, sort=-modified");
      } else {
        // List all players' history
        $events = $player->find("template=event, sort=-modified");
      }

      if ($events->count() > 0) {
        echo '<li><a href="'. $page->url .'?playerId='. $player->id.'">'.$player->title.'</a></li>';
        echo '<ul>';
        foreach ($events as $event) {
          $task = $pages->get("$event->task");
          if ($task->HP < 0) {
            $className = 'negative';
          } else {
            $className = 'positive';
          }
          echo  '<li class="'. $className .'">['. strftime("%d/%m", $event->modified).'] ';
          if ($task.length > 0) { // Task is set only in new version for the moment
            echo $task->summary;
          } else {
            echo $event->title;
          }
          echo '</li>';
        }
        echo '</ul>';
      }
    }
  } else {
    // List all players' history
    $events = $selectedPlayer->find("template=event, sort=-modified");

    if ($events->count() > 0) {
      foreach ($events as $event) {
        $task = $pages->get("$event->task");
        if ($task->HP < 0) {
          $className = 'negative';
        } else {
          $className = 'positive';
        }
        echo  '<li class="'. $className .'">['. strftime("%d/%m", $event->modified).'] ';
        if ($task.length > 0) { // Task is set only in new version for the moment
          echo $task->summary;
        } else {
          echo $event->title;
        }
        echo '</li>';
      }
    }

  }
  echo '</ul>';

  $url = $pages->get('/report_generator')->url;
  echo '<a class="ajax" href="'.$url.'"> Ajax generator</a>';
   */

} else {
  echo '<p>Admin only ;)</p>';
}

echo '<div id="reportDiv">Select a report.</div>';

include("./foot.inc"); 
?>
