<div>
  <?php 
  /* list-all template */

  $reportLink = $pages->get("/reports")->url;
  $reportGeneratorLink = $pages->get("/report_generator")->url;
  $team = $allTeams->get("name=$input->urlSegment1");
  $rank = $team->rank->name;
  if ($input->urlSegment1 != 'no-team') {
    $allPlayers = $allPlayers->find("team=$team, sort=group"); // Limit to team players
    
    // Build allGroups
    $allGroups = new PageArray();
    foreach($allPlayers as $p) {
      $nbEl = 0;
      if (!in_array($p->group, $allGroups->getArray())) {
        $allGroups->add($p->group);
      }
      if ( $rank == '4emes' || $rank == '3emes' ) {
        $nbEl = $p->places->count()+$p->people->count();
      } else {
        $nbEl = $p->places->count();
      }
      $p->nbEl = $nbEl;
    }
    $outGroups = '';

    // Calculate groups Karma & Set Captain
    $index = 0;
    foreach($allGroups as $group) {
      $group->karma = 0;
      $group->nbBonus = 0;
      
      // Find selected players
      $players = $allPlayers->find("group=$group");
      
      // Check for group bonus
      $group->nbBonus = groupBonus($players);
      $group->karma = $group->nbBonus*30;

      // Add individual karmas
      foreach( $players as $player) {
        // Karma is divided by number of players in the group to be fair with smaller groups
        $groupKarma = round($player->karma/$players->count);
        (int) $group->karma += $groupKarma;
        $group->details .= '- '.$player->title.' ('.$groupKarma.'k - '.$player->nbEl.'el)<br />';
      }
      $index++;
    }

    // Prepare group display
    $allGroups->sort('-karma');
    $outGroups .= '<ul class="list-inline">';
    foreach( $allGroups as $group) {
      $outGroups .= '<li>';
      $outGroups .= '<p class="label label-default" data-toggle="tooltip" data-html="true" title="'.$group->details.'">';
      $outGroups .= $group->title.' <span class="bg-primary">'.$group->karma.'</span>';
      // Display stars for bonus (filled star = 5 empty stars, 1 star = 1 free element for each group member)
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

  } else {
    $allPlayers = $pages->find("template=player, team.name=no-team");
  }

  // Nav tabs
  $team = $pages->get("template=team, name=$input->urlSegment1");;
  include("./tabList.inc"); 

  if ($input->urlSegment1 != 'no-team') {
    if ($user->isLoggedin() && !$user->isSuperuser()) {
      showScores($team);
    }

    $captains = $allPlayers->find("skills.count>0, skills.name=captain")->implode(', ', '{title}');
    if ( strlen($captains) == 0 ) { 
      $captains = 'Nobody.';
    } else {
    }
    echo '<p class="text-center"><span class="label label-primary"><span class="glyphicon glyphicon-star"></span> Group Captains</span> '.$captains.'</p>';
  }

  // echo $outGroups;

  // Players table
  $allPlayers->sort('-yearlyKarma, -level, -XP');
  $out = '<table id="teamTable" class="table table-hover table-condensed teamView">';
  $out .= '<thead>';
  $out .= '<tr>';
  $out .= '<th data-toggle="tooltip" title="Group"><span class="glyphicon glyphicon-user"></span><span class="glyphicon glyphicon-user"></span></th>';
  $out .= '<td></td>';
  $out .= '<th data-toggle="tooltip" title="Player"><span class="glyphicon glyphicon-user"></span></th>';
  $out .= '<td data-toggle="tooltip" title="Karma">K&nbsp;&nbsp;</td>';
  $out .= '<td data-toggle="tooltip" title="What happened on the last date?"><span class="glyphicon glyphicon-th-list"></span></td>';
  $out .= '<th data-toggle="tooltip" title="Special skills">S.</th>';
  $out .= '<th data-toggle="tooltip" title="Gold coins"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /></th>';
  $out .= '<th data-toggle="tooltip" title="Level"><span class="glyphicon glyphicon-signal"></span></th>';
  $out .= '<td data-toggle="tooltip" title="Reputation">R&nbsp;&nbsp;</td>';
  $out .= '<th><img src="'.$config->urls->templates.'img/heart.png" alt="" /> HP</th>';
  $out .= '<th><img src="'.$config->urls->templates.'img/star.png" alt="" /> XP</th>';
  $out .= '<th data-toggle="tooltip" title="Places"><img src="'.$config->urls->templates.'img/globe.png" alt="" /></th>';
  if ($team->rank && $team->rank->is("name!=6emes|5emes")) {
    $out .= '<th data-toggle="tooltip" title="People"><span class="glyphicon glyphicon-user"></span></th>';
  }
  $out .= '<th data-toggle="tooltip" title="Equipment"><span class="glyphicon glyphicon-wrench"></span></th>';
  $out .= '<td data-toggle="tooltip" title="Donation"><img src="'.$config->urls->templates.'img/heart.png" alt="" /></td>';
  $out .= '<th data-toggle="tooltip" title="Underground training">U.T.</th>';
  $out .= '<th data-toggle="tooltip" title="Fighting Power">F.P.</th>';
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
    $lastEvent = $player->child("name=history")->child("sort=-date");
    $prevDay = date("m/d/Y", $lastEvent->date); // Get all events on same day
    $prevDate = $prevDay.' 0:0:0'; // Select events for the whole day
    $prevEvents = $player->child("name='history'")->children("date>=$prevDate");
    $trend = '';
    foreach ($prevEvents as $event) {
      $HP = $event->task->HP;
      $title = $event->task->title;
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
    // Set hk counter
    if ($user->isSuperuser() || ($user->isLoggedin() && $user->name == $player->login)) { // Admin is logged or user
      if ($player->hkcount > 0) {
        $hkCount = '&nbsp;<span class="label label-danger">'.$player->hkcount.'</span>';
      } else {
        $hkCount = '';
      }
    } else {
      $hkCount = '';
    }
    // Set HP progressbar
    $HPwidth = 150*$player->HP/50;
    // Set XP progressbar
    if ($player->level <= 4) {
      $delta = 40+($player->level*10);
    } else {
      $delta = 90;
    }
    $threshold = ($player->level*10)+$delta;
    $XPwidth = 150*$player->XP/($threshold);
    // Places list
    $tooltipPlaces = '';
    if ($player->places->count() > 0) {
      $listPlaces = '<ul>';
      foreach ($player->places as $place) {
        $listPlaces .= '<li>'.$place->title.'</li>';
      }
      $listPlaces .= '</ul>';
      $tooltipPlaces =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listPlaces.'"';
    } else {
      $tooltipPlaces = '';
    }
    if ($team->rank && $team->rank->is("name!=6emes|5emes")) {
      // People list
      $tooltipPeople = '';
      if ($player->people->count() > 0) {
        $listPeople = '<ul>';
        foreach ($player->people as $people) {
          $listPeople .= '<li>'.$people->title.'</li>';
        }
        $listPeople .= '</ul>';
        $tooltipPeople =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listPeople.'"';
      } else {
        $tooltipPeople = '';
      }
    }
    // Equipment list
    $tooltipEquipment = '';
    if ($player->equipment->count() > 0) {
      $listEquipment = '<ul>';
      foreach ($player->equipment as $equipment) {
        $listEquipment .= '<li>'.$equipment->title.'</li>';
      }
      $listEquipment .= '</ul>';
      $tooltipEquipment =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listEquipment.'"';
    } else {
      $tooltipEquipment = '';
    }

    if ($player->avatar) {
      $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$player->avatar->getCrop('mini')->url."' alt='avatar' />";
    } else {
      $mini = '';
    }
    $out .= '<tr '. $class.'>';
    $out .= '<td>';
    if ($player->group) { $out .= $player->group->title; };
    $out .= '</td>';
    $out .= '<td>'. $mini .'</td>';
    $out .= '<td>';
    $out .='<a href="'.$page->url.$input->urlSegment1.'/'.$player->name.'">'. $player->title .'</a>'.$hkCount.'</td>';
    $out .= '<td>'. $player->yearlyKarma .'</td>';
    $out .= '<td><span class="trend">'.$trend.'</span></td>';
    if ($player->skills->has("name=captain")) {
      $showSkills = '<span class="label label-primary" data-toggle="tooltip" title="Captain">C</span>';
    } else {
      $showSkills = '';
    }
    if ($player->skills->has("name=ambassador")) {
      $showSkills .= '<span class="label label-success" data-toggle="tooltip" title="Ambassador">A</span>';
    } else {
      $showSkills .= '<span class="label label-info">'.$player->streak.'</span>';
    }
    $out .= '<td>'.$showSkills.'</td>';
    $out .= '<td>'. $player->GC .'</td>';
    $out .= '<td>'. $player->level .'</td>';
    $out .= '<td>'. $player->karma .'</td>';
    if ($player->coma == true) { $player->HP = 0; }
    $out .= '<td data-order="'.$player->HP.'" data-toggle="tooltip" title="'.$player->HP.'/50" data-placement="top">';
    if ($player->coma == false) {
      $out .= '<div class="progress progress-striped progress-mini">';
      $out .= '<div class="progress-bar progress-bar-danger" role="progressbar" style="width: '.$HPwidth.'px;"></div>';
      $out .= '</div>';
    } else {
      $out .= '<span class="badge badge-danger">Coma !</span>';
    }
    $out .= '</td>';
    $out .= '<td data-order="'.$player->XP.'" data-toggle="tooltip" title="'.$player->XP.'/'.($threshold).'" data-placement="top">';
    if ($player->coma == false) {
      $out .= '<div class="progress progress-striped progress-mini">';
      $out .= '<div class="progress-bar progress-bar-success" role="progressbar" style="width: '.$XPwidth.'px;"></div>';
      $out .= '</div>';
    } else {
      $out .= '<span class="badge badge-danger">Coma !</span>';
    }
    $out .= '</td>';
    $out .= '<td '.$tooltipPlaces.'>'. $player->places->count() .'</td>';
    if ($team->rank && $team->rank->is("name!=6emes|5emes")) {
      $out .= '<td '.$tooltipPeople.'>'. $player->people->count() .'</td>';
    }
    $out .= '<td '.$tooltipEquipment.'>'. $player->equipment->count() .'</td>';
    $out .= '<td>'. $player->donation .'</td>';
    $out .= '<td>'. $player->underground_training .'</td>';
    $out .= '<td>'. $player->fighting_power .'</td>';
    $out .= '</tr>';
  }
  $out .= '</tbody>';
  $out .= '</table>';

  echo $out;
?>

</div>
