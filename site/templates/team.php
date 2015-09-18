<div>
  <?php 
  /* list-all template */

  $reportLink = $pages->get("/reports")->url;
  $reportGeneratorLink = $pages->get("/report_generator")->url;
  if ($input->urlSegment1 != 'no-team') {
    $allPlayers = $pages->find("template='player', playerTeam=$input->urlSegment1, sort='group'");
    $team = $allPlayers->first->playerTeam;
  } else {
    $allPlayers = $pages->find("template='player', playerTeam='', sort='group'");
    $team = 'No team';
  }
  $allGroups = $pages->get("/groups")->children('sort=title');
  $outGroups = '';
  $totalPlaces = $pages->find("template='place', name!='places'");
  $globalScore = globalScore($allPlayers, $totalPlaces);
  $teamScore = $globalScore[0];
  $teamOwners = $globalScore[1];
  $totalOwners = $globalScore[2];

  // Calculate groups Karma
  $index = 0;
  foreach( $allGroups as $group) {
    $group->karma = 0;
    $group->nbBonus = 0;
    
    // Find selected players
    $players = $allPlayers->find("group=$group");
    
    // Get rid of unused groups
    if ($players->count == 0) {
      unset($allGroups[$index]);
    }
    // Check for group bonus
    $group->nbBonus = groupBonus($players);
    $group->karma = $group->nbBonus*30;

    // Add individual karmas
    foreach( $players as $player) {
      $karma = getKarma($player);
      $player->karma = $karma;
      // Karma is divided by number of players in the group to be fair with smaller groups
      $groupKarma = round($karma/$players->count);
      (int) $group->karma = $group->karma + $groupKarma;
      $group->details .= $player->title." (".$karma.' ('.$groupKarma.') - '.$player->places->count.') ';
    }
    $index++;
  }

  // Prepare group display
  $allGroups->sort('-karma');
  $outGroups .= '<ul class="list-inline lead">';
  foreach( $allGroups as $group) {
    $outGroups .= '<li>';
    $outGroups .= '<p class="label label-default" title="'.$group->details.'">';
    $outGroups .= $group->title.' <span class="bg-primary">'.$group->karma.'</span>';
    // Display stars for bonus (filled star = 5 empty stars, 1 star = 1 place for each group member)
    $starsGroups = floor($group->nbBonus/5);
    if ( $starsGroups < 1) {
      for ($i=0; $i<$group->nbBonus; $i++) {
        $outGroups .= ' <span class="glyphicon glyphicon-star-empty"></span>';
      }
    } else {
      for ($i=0; $i<$starsGroups; $i++) {
        $outGroups .= ' <span class="glyphicon glyphicon-star"></span>';
      }
      $group->nbBonus = $group->nbBonus - $starsGroups*5;
      for ($i=0; $i<$group->nbBonus; $i++) {
        $outGroups .= ' <span class="glyphicon glyphicon-star-empty"></span>';
      }
    }
    $outGroups .= '</p>';
    $outGroups .= '</li>';
  }
  $outGroups .= '</ul>';

  // Nav tabs
  include("./tabList.inc"); 

  echo '<p class="text-center lead well"><strong title="'.$teamOwners.'/'.$totalOwners.'">'.$team.' - Free world : '.$teamScore.'%</strong></p>';

  echo $outGroups;

  // New PHP table
  $out = '<table id="teamTable" class="table table-hover table-condensed teamView">';
  $allPlayers->sort('-karma');
  $out .= '<thead>';
  $out .= '<tr>';
  $out .= '<th data-toggle="tooltip" title="Group"><span class="glyphicon glyphicon-user"></span><span class="glyphicon glyphicon-user"></span></th>';
  $out .= '<td></td>';
  $out .= '<th data-toggle="tooltip" title="Player"><span class="glyphicon glyphicon-user"></span></th>';
  $out .= '<td>Karma</td>';
  $out .= '<td data-toggle="tooltip" title="What happened on the last date?"><span class="glyphicon glyphicon-th-list"></span></td>';
  $out .= '<th data-toggle="tooltip" title="Gold coins"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="" /></th>';
  $out .= '<th data-toggle="tooltip" title="Level"><span class="glyphicon glyphicon-signal"></span></th>';
  $out .= '<th><img src="'.$config->urls->templates.'img/heart.png" alt="" /> HP</th>';
  $out .= '<th><img src="'.$config->urls->templates.'img/star.png" alt="" /> XP</th>';
  $out .= '<th data-toggle="tooltip" title="Places"><img src="'.$config->urls->templates.'img/globe.png" alt="" /></th>';
  $out .= '<th data-toggle="tooltip" title="Equipment"><span class="glyphicon glyphicon-wrench"></span></th>';
  $out .= '<td data-toggle="tooltip" title="Donation"><img src="'.$config->urls->templates.'img/heart.png" alt="" /></td>';
  $out .= '</tr>';
  $out .= '</thead>';
  $out .= '<tbody>';
  foreach( $allPlayers as $player) {
    if ($player->login == $user->name) {
      $class = ' class="selected"';
    } else {
      $class = '';
    }
    // Get last recorded events
    // Get last event date
    $lastEvent = $player->child("name='history'")->children("sort=-date")->first();
    $prevDate = $lastEvent->date;
    // Get all events on same date
    $prevEvents = $player->child("name='history'")->children("date=$prevDate");
    $trend = '';
    foreach ($prevEvents as $event) {
      $HP = $event->task->HP;
      $title = $event->task->title;
      if ($event->summary) {
        $comment = '<br />'.strftime("%d/%m", $event->date).'<br />'.$event->summary;
      } else {
        $comment = '<br />'.strftime("%d/%m", $event->date);
      }
      if ($HP < 0) {
        $trendClass = 'negativeTrend';
      } else {
        $trendClass = 'positiveTrend';
      }
      if ($event->summary !== '') {
        $summary = ' ('.$event->summary.')';
      } else {
        $summary = '';
      } 
      $trend .= '<span class="'.$trendClass.'" data-toggle="tooltip" data-html="true" title="'.strftime("%d/%m", $event->date).': '.$title.$summary.'">&nbsp;</span>';
    }
    // Set HP progressbar
    $HPwidth = 150*$player->HP/50;
    // Set XP progressbar
    $XPwidth = 150*$player->XP/($player->level*10+90);
    // Places list
    $tooltipPlaces = '';
    $listPlaces = '<ul>';
    foreach ($player->places as $place) {
      $listPlaces .= '<li>'.$place->title.'</li>';
    }
    $listPlaces .= '</ul>';
    if ($player->places->count() > 0) {
      $tooltipPlaces =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listPlaces.'"';
    } else {
      $tooltipPlaces = '';
    }
    // Equipment list
    $tooltipEquipment = '';
    $listEquipment = '<ul>';
    foreach ($player->equipment as $equipment) {
      $listEquipment .= '<li>'.$equipment->title.'</li>';
    }
    $listEquipment .= '</ul>';
    if ($player->equipment->count() > 0) {
      $tooltipEquipment =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listEquipment.'"';
    } else {
      $tooltipEquipment = '';
    }

    if ($player->avatar) {
      $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
    } else {
      $mini = '';
    }
    $out .= '<tr '. $class.'>';
    $out .= '<td>'. $player->group->title .'</td>';
    $out .= '<td>'. $mini .'</td>';
    $out .= '<td><a href="'.$page->url.$input->urlSegment1.'/'.$player->name.'">'. $player->title .'</a></td>';
    $out .= '<td>'. $player->karma .'</td>';
    $out .= '<td><span class="trend">'.$trend.'</span></td>';
    $out .= '<td>'. $player->GC .'</td>';
    $out .= '<td>'. $player->level .'</td>';
    $out .= '<td data-order="'.$player->HP.'" data-toggle="tooltip" title="'.$player->HP.'/50" data-placement="top">';
    $out .= '<div class="progress progress-striped progress-mini">';
    $out .= '<div class="progress-bar progress-bar-danger" role="progressbar" style="width: '.$HPwidth.'px;"></div>';
    $out .= '</div>';
    $out .= '</td>';
    $out .= '<td data-order="'.$player->XP.'" data-toggle="tooltip" title="'.$player->XP.'/'.($player->level*10+90).'" data-placement="top">';
    $out .= '<div class="progress progress-striped progress-mini">';
    $out .= '<div class="progress-bar progress-bar-success" role="progressbar" style="width: '.$XPwidth.'px;"></div>';
    $out .= '</div>';
    $out .= '</td>';
    $out .= '<td '.$tooltipPlaces.'>'. $player->places->count() .'</td>';
    $out .= '<td '.$tooltipEquipment.'>'. $player->equipment->count() .'</td>';
    $out .= '<td>'. $player->donation .'</td>';
    $out .= '</tr>';
  }
  $out .= '</tbody>';
  $out .= '</table>';

  echo $out;
?>

</div> <!-- /teamCtrl -->
