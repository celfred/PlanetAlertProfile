<?php 

if($config->ajax) {

  if ($input->urlSegment1 && $input->urlSegment2 == '') { // Class report
    $team = $input->urlSegment1;
    $allPlayers = $pages->find("team=$team, template=player, sort=title");

    /* TODO
    if ($input->urlSegment2) {
      $catId = $input->urlSegment2;
      $category = $pages->get($catId);
      echo '<h1>Team report : '.$team. ' - '. $category->title .'</h1>';
    } else {
      echo '<h1>Team report : '.$team.'</h1>';
    }
     */
  } else if ($input->urlSegment2 != '') { // 1 player report
    $playerId = $input->urlSegment2;
    $selectedPlayer = $pages->get($playerId);

    echo '<h1>Report : '.$selectedPlayer->title .' ('. $selectedPlayer->team.')</h1>';

    // List all recorded events for selected player
    $events = $selectedPlayer->find("template=event, sort=category");
  }

/*
  echo '<ul>';
  if (!$selectedPlayer) { // Class report
    foreach($allPlayers as $player) {
      if ($category) {
        // List only players concerned with the category
        $events = $player->find("template=event, category=$category, sort=-created");
      } else {
        // List all players' history
        $events = $player->find("template=event, sort=-created");
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
          echo  '<li class="'. $className .'">['. strftime("%d/%m", $event->created).'] ';
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
    // List all players' history // 1 player report
    $events = $selectedPlayer->find("template=event, sort=-created");

    if ($events->count() > 0) {
      foreach ($events as $event) {
        $task = $pages->get("$event->task");
        if ($task->HP < 0) {
          $className = 'negative';
        } else {
          $className = 'positive';
        }
        echo  '<li class="'. $className .'">['. strftime("%d/%m", $event->created).'] ';
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
 */

  echo '<table class="table table-condensed table-hover">';
  if (!$selectedPlayer) { // Class report
    echo '<tr>';
    echo '<td></td>';
    // Build all categories columns
    $categories = $pages->find("parent='/categories'")->not("name=shop|potions|protections|place|weapons|attitude");
    foreach ($categories as $category) {
      echo '<th>';
      switch ($category->name) {
        case 'participation' : echo '<i class="glyphicon glyphicon-comment" title="'. $category->title.'"></i> '; break;
        case 'homework' : echo '<i class="glyphicon glyphicon-pencil" title="'. $category->title.'"></i>'; break;
        case 'groupwork' : echo '<i class="glyphicon glyphicon-user" title="'. $category->title.'"></i><i class="glyphicon glyphicon-user" title="'. $category->title.'"></i>'; break;
        case 'travail-individuel' : echo '<i class="glyphicon glyphicon-user" title="'. $category->title.'"></i>'; break;
        case 'manual-cat' : echo '<i class="glyphicon glyphicon-cog" title="'. $category->title.'"></i>'; break;
        case 'test' : echo '<i class="glyphicon glyphicon-ok" title="'. $category->title.'"></i>'; break;
        case 'oublis' : echo '<i class="glyphicon glyphicon-cloud" title="'. $category->title.'"></i>'; break;
        default : echo $category->title;
      }
      echo '</th>';
    }
    echo '<td><i class="glyphicon glyphicon-picture" title="Lieux"></i></td>';
    echo '<td><i class="glyphicon glyphicon-th" title="Équipement"></i></td>';
    echo '</tr>';

    foreach($allPlayers as $player) {
      echo '<tr>';
      echo '<th>';
      echo $player->title;
      echo '</th>';
      foreach ($categories as $category) {
        echo '<td>';
        $events = $player->find("template=event, task.category=$category");
        //echo $events->count();
        if ($category->name == 'participation' || $category->name == 'homework') {
          $posPart = $events->count(); // Get positive participation
          $nbPart = $events->count(); // Max participation
          $nbAbsent = 0;
        }
        $list = '';
        $posItems = $events->count();
        $nbItems = $events->count();
        // Is $player concerned with this category?
        if ($events->count() > 0) {
          foreach ($events as $event) {
            $task = $pages->get("$event->task");
            if ($task->HP < 0) {
              $className = 'negative';
              // Participation stat
              if ($category->name == 'participation') {
                $posPart = $posPart-1;
              }
              $posItems = $posItems-1;
              $sign = '-';
            } else {
              if ($task->name != 'absent') {
                $className = 'positive';
              } else {
                $nbPart = $nbPart-1;
                $nbAbsent = $nbAbsent+1;
                $posPart = $posPart-1;
              }
              $sign = '+';
            }
            if (strlen(trim($event->summary)) > 0) {
              $list .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->summary."\r\n";
            } else {
              $list .= $sign.' ['.strftime("%d/%m", $event->created).'] '.$event->title."\r\n";
            }
            if ($category->name == 'homework') {
              switch ($task->name) {
                case 'no-homework' :
                  echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊝</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'homework' : // Don't display regular homework
                  //echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊙</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'homework-half-done' :
                  echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊘</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'entrainement-supplementaire-simple' :
                  echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊕</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                case 'awarded-homework' :
                  echo  '<p title="'. $event->summary.'"><span style="font-size: 18px;">⊕</span>&nbsp;<span style="font-size: 11px;" class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></p>';
                  break;
                default : break;
              }
            }
            if ($category->name == 'oublis') {
              switch ($task->name) {
                case 'forgotten-weapons' :
                  echo  '<span title="'. $list .'"><i class="glyphicon glyphicon-file"></i>&nbsp;<span class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></span> ';
                  break;
                case 'test-not-signed' :
                  echo  '<span title="'. $list .'"><i class="glyphicon glyphicon-pencil"></i>&nbsp;<span class="'. $className .'">['. strftime("%d/%m", $event->created).']</span></span> ';
                  break;
                default : break;
              }
            }
          }
          if ($category->name == 'participation') {
            $ratio = round(($posPart*100)/$nbPart);
            if ($nbAbsent != 0) {
              echo $nbAbsent. '&nbsp;-&nbsp;';
            }
            echo $ratio.'%';
            echo ' ('.$posPart.'/'.$nbPart.')';
          }
          if ($category->name != 'participation' && $category->name != 'homework' && $category->name != 'oublis') {
            $ratio = round(($posItems*100)/$nbItems);
            echo '<p title="'.$list.'">';
            echo $ratio.'%';
            echo ' ('.$posItems.'/'.$nbItems.')';
            echo '</p>';
          }
        } else {
          echo '-';
        }
        echo '</td>';
      }
      // Places and Equipment
      /*
      $items = $player->places;
      array_push($items, $player->equipment);
      if ($items) {
        echo '<td>';
        foreach ($items as $item) {
          echo '<p>';
          echo $item->title;
          echo '</p>';
        }
        echo '</td>';
      }
      */
      echo '<td>';
      if ($player->places->count > 0) {
        $list = '';
        foreach ($player->places as $place) {
          $list .= $place->title."\r\n";
        }
        echo '<p title="'.$list.'">';
        echo '<i class="glyphicon glyphicon-picture"></i> '.$player->places->count;
        echo '</p>';
      }
      echo '</td>';
      echo '<td>';
      if ($player->equipment->count > 0) {
        $list = '';
        foreach ($player->equipment as $item) {
          $list .= $item->title."\r\n";
        }
        echo '<p title="'.$list.'">';
        echo '<i class="glyphicon glyphicon-th"></i> '.$player->equipment->count;
        echo '</p>';
      }
      echo '</td>';
      echo '</tr>';
    }
  } else { // Player's report TODO
    // Build all categories columns
    $categories = $pages->find("parent='/categories'")->not("name=shop|potions|protections|place|weapons|attitude");
    if ($events->count() > 0) {
      foreach ($events as $event) {
        $task = $pages->get("$event->task");
        if ($task->HP < 0) {
          $className = 'negative';
        } else {
          $className = 'positive';
        }
        echo  '<tr class="'. $className .'">';
        echo '<td>';
        echo '['. strftime("%d/%m", $event->created).'] ';
        if ($task.length > 0) { // Task is set only in new version for the moment
          echo $task->summary;
        } else {
          echo $event->title;
        }
        echo '</td>';
        echo '</tr>';
      }
    }
  }
  echo '<tr>';
  echo '<td class="legend" colspan="'.($categories->count()+1).'">';
  foreach ($categories as $category) {
    echo '<small>';
    switch ($category->name) {
      case 'participation' : echo '<i class="glyphicon glyphicon-comment"></i> '. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      case 'homework' : echo '<i class="glyphicon glyphicon-pencil"></i> '. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      case 'groupwork' : echo '<i class="glyphicon glyphicon-user"></i><i class="glyphicon glyphicon-user"></i> '. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      case 'travail-individuel' : echo '<i class="glyphicon glyphicon-user"></i>'. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      case 'manual-cat' : echo '<i class="glyphicon glyphicon-cog"></i>'. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      case 'test' : echo '<i class="glyphicon glyphicon-ok"></i>'. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      case 'oublis' : echo '<i class="glyphicon glyphicon-cloud"></i>'. $category->title .'&nbsp;&nbsp;&nbsp;'; break;
      default : break;
    }
    echo '</small>';
  }
  echo '</td>';
  echo '</tr>';
  echo '</table>';
}

?>
