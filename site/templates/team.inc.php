<?php namespace ProcessWire; ?>
<div>
  <?php /* list-all template */

  $reportLink = $pages->get("/reports")->url;
  $reportGeneratorLink = $pages->get("/report_generator")->url;
  $team = $allTeams->get("name=$input->urlSegment1");
  $rank = $team->rank->index;
  if ($input->urlSegment1 != 'no-team') {
    $allPlayers = $allPlayers->find("team=$team, sort=group"); // Limit to team players
    // Build allGroups
    $allGroups = new PageArray();
    foreach($allPlayers as $p) {
      $nbEl = 0;
      if (!in_array($p->group, $allGroups->getArray())) {
        $allGroups->add($p->group);
      }
      if ($rank >= 8) {
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
      foreach($players as $player) {
        // Karma is divided by number of players in the group to be fair with smaller groups
        $groupKarma = round($player->yearlyKarma/$players->count);
        (int) $group->karma += $groupKarma;
        $group->details .= '- '.$player->title.' ('.$groupKarma.'k - '.$player->nbEl.'el)<br />';
      }
      $index++;
    }

    // Prepare group display
    $allGroups->sort('-karma');
    $outGroups .= '<ul class="list-inline">';
    foreach($allGroups as $group) {
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
    // TODO : Order no-team list by karma (i.e. Reputation)
    // Unexpected behavior because of JS table sorting
    $allPlayers = $pages->find("template=player, team.name=no-team, limit=35, sort=-yearlyKarma");
    $pagination = $allPlayers->renderPager();
  }

  // Nav tabs
  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    include("./tabList.inc"); 
  }

  if ($input->urlSegment1 != 'no-team') {
    $captains = $allPlayers->find("skills.count>0, skills.name=captain")->implode(', ', '{title}');
    if (strlen($captains) == 0) { 
      $captains = __('Nobody.');
    }
    echo '<p class="text-center">';
      // Any new serious injuries ? (personal workflow)
      if ($user->name == 'flieutaud' || $user->isSuperuser()) {
        $penalty = $pages->find("has_parent=$allPlayers, template=event, publish=1, task.name=penalty, sort=-date");
        if (count($penalty) > 0) {
          $players = $penalty->implode(', ', '{parent.parent.title}');
          echo '<span class="pull-left label label-danger">';
          echo '<span class="glyphicon glyphicon-warning-sign"></span> ';
          echo sprintf(_n("Serious injury", "Serious injuries", count($penalty)), count($penalty)).' : ';
          echo $players;
          echo '</span>';
          $helpAlert = true;
          $helpTitle =  '<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;';
          $helpTitle .= sprintf(_n("Serious injury", "Serious injuries", count($penalty)), count($penalty)).' !';
          $helpMessage = '<h4>'.$players.'</h4>';
        }
      }

      echo '<span class="label label-primary"><span class="glyphicon glyphicon-star"></span> '.__("Group Captains").'</span> → '.$captains;
    echo '</p>';

  }

  // echo $outGroups;

  // Players table
  $allPlayers->sort('-yearlyKarma, -level, -XP');
  $out = '';

  if (isset($pagination)) { $out .= $pagination;}
  $out .= '<table id="teamTable" class="table table-hover table-condensed teamView">';
  $out .= '<thead>';
  $out .= '<tr>';
  $out .= '<th data-toggle="tooltip" title="'.__("Group").'"><span class="glyphicon glyphicon-user"></span><span class="glyphicon glyphicon-user"></span></th>';
  $out .= '<td></td>';
  $out .= '<th data-toggle="tooltip" title="'.__("Player").'"><span class="glyphicon glyphicon-user"></span></th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Karma").'">K&nbsp;&nbsp;</th>';
  if ($user->isLoggedin()) {
    $out .= '<th data-toggle="tooltip" title="'.__("What happened on the last date?").'"><span class="glyphicon glyphicon-th-list"></span></th>';
  }
  $out .= '<th data-toggle="tooltip" title="'.__("Special skills").'">S.</th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Gold coins").'"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /></th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Level").'"><span class="glyphicon glyphicon-signal"></span></th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Reputation").'">R&nbsp;&nbsp;</th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Health Points").'"><img src="'.$config->urls->templates.'img/heart.png" alt="" /> ';
  $out .= __("HP").'</th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Experience").'"><img src="'.$config->urls->templates.'img/star.png" alt="" /> ';
  $out .= __("XP").'</th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Places").'"><img src="'.$config->urls->templates.'img/globe.png" alt="" /></th>';
  if ($team->rank && $team->rank->is("index>=8")) {
    $out .= '<th data-toggle="tooltip" title="'.__("People").'"><span class="glyphicon glyphicon-user"></span></th>';
  }
  $out .= '<th data-toggle="tooltip" title="'.__("Equipment").'"><span class="glyphicon glyphicon-wrench"></span></th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Donation").'"><img src="'.$config->urls->templates.'img/heart.png" alt="" /></th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Underground training").'">';
  $out .= __("U.T.").'</th>';
  $out .= '<th data-toggle="tooltip" title="'.__("Fighting Power").'">';
  $out .= __("F.P.").'</th>';
  $out .= '</tr>';
  $out .= '</thead>';
  $out .= '<tbody>';
  foreach($allPlayers as $player) {
    if ($player->login == $user->name) {
      $class = ' class="selected"';
    } else {
      $class = '';
    }
    if ($user->isLoggedin()) {
      // Get last recorded events
      // Get last event date
      $lastEvent = $player->child("name=history")->child("sort=-date");
      $prevDay = date("m/d/Y", $lastEvent->date); // Get all events on same day
      $prevDate = $prevDay.' 0:0:0'; // Select events for the whole day
      $prevEvents = $player->child("name=history")->children("template=event, date>=$prevDate");
      $trend = '';
      foreach ($prevEvents as $event) {
        $event->task = checkModTask($event->task, $headTeacher, $player);
        if (($user->hasRole('teacher') || $user->isSuperuser()) && $event->task->is("name=penalty|death")) { $class = 'selected'; }
        $HP = $event->task->HP;
        $title = $event->task->title;
        $HP < 0 || $event->task->is("name=inactivity") ? $trendClass = 'negativeTrend' : $trendClass = 'positiveTrend';
        $event->summary !== '' ? $summary = ' ('.$event->summary.')' : $summary = '';
        $trend .= '<span class="'.$trendClass.'" data-toggle="tooltip" data-html="true" title="'.strftime("%d/%m", $event->date).': '.$title.$summary.'">&nbsp;</span>';
      }
      if ($team->is("name!=no-team|cm1")) {
        // Set hk counter
        if ($user->hasRole('teacher') || $user->isSuperuser() || ($user->isLoggedin() && $user->name == $player->login)) { // Admin is logged or user
          if ($player->hkcount > 0) {
            $hkCount = '&nbsp;<span class="label label-danger">'.$player->hkcount.'</span>';
          } else {
            $hkCount = '';
          }
        }
      } else {
        $hkCount = '';
      }
    } else {
      $hkCount = '';
    }
    // Set HP progressbar
    $HPwidth = round(150*$player->HP/50);
    // Set XP progressbar
    if ($player->level <= 4) {
      $delta = 40+($player->level*10);
    } else {
      $delta = 90;
    }
    $threshold = ($player->level*10)+$delta;
    $XPwidth = round(150*$player->XP/($threshold));
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
    if ($team->rank && $team->rank->is("index>=8")) {
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
      $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img class=\"tipList-light\" src=\"".$player->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$player->avatar->getCrop('mini')->url."' alt='avatar' />";
    } else {
      $mini = '';
    }
    $out .= '<tr '. $class.'>';
    $out .= '<td>';
    if ($player->group) { 
      $out .= $player->group->title;
    } else {
      $out .= '-';
    };
    $out .= '</td>';
    $out .= '<td>'. $mini .'</td>';
    $out .= '<td>';
    $out .='<a href="'.$page->url.$input->urlSegment1.'/'.$player->name.'">'. $player->title .'</a>'.$hkCount;
    $out .= '</td>';
    $out .= '<td>'. $player->yearlyKarma .'</td>';
    if ($user->isLoggedin()) {
      $out .= '<td><span class="trend">'.$trend.'</span></td>';
    }
    if ($player->skills->has("name=captain")) {
      $showSkills = '<span class="label label-primary" data-toggle="tooltip" title="'.__("Captain").'">C</span>';
    } else {
      $showSkills = '';
    }
    if ($player->skills->has("name=ambassador")) {
      $showSkills .= '<span class="label label-success" data-toggle="tooltip" title="'.__("Ambassador").'">A</span>';
    } else {
      $showSkills .= '<span class="label label-info">'.$player->streak.'</span>';
    }
    $out .= '<td>'.$showSkills.'</td>';
    $out .= '<td>'. $player->GC .'</td>';
    $out .= '<td>'. $player->level .'</td>';
    $out .= '<td>'. $player->reputation .'</td>';
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
    if ($team->rank && $team->rank->is("index>=8")) {
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
  if (isset($pagination)) { $out .= $pagination;}

  // helpAlert
  if ($user->hasRole('player')) {
    $dangerPlayers = $allPlayers->find('(coma=1), (HP<=15)')->sort("coma, HP");
    if ($dangerPlayers->count() > 0) {
      $helpAlert = true;
      $helpTitle = __("Some team mates need help !");
      $helpMessage = __("Low HP !");
    }
  }

  include("./helpAlert.inc.php");

  echo $out;
?>

</div>
