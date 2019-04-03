<?php namespace ProcessWire;
  include("./head.inc"); 

echo '<div>';

  $reportLink = $pages->get("/reports")->url;
  $reportGeneratorLink = $pages->get("/report_generator")->url;
  if (isset($player) && $player->team->is("name=test-team")) {
    $selectedTeam = $pages->get("name=test-team");
    $allPlayers = $pages->find("parent.name=players, team.name=test-team");
  } else {
    $rank = $selectedTeam->rank->index;
    $allPlayers = getAllPlayers($user, true);
  }

  // Nav tabs
  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    include("./tabList.inc"); 
    $loggedId = '';
  }

  if ($user->isLoggedin()) {
    if (isset($player)) {
      $loggedId = $player->id;
    } else {
      $loggedId = '';
    }
  }

  if ($input->urlSegment1 != 'no-team') {
    $captains = $allPlayers->find("skills.name=captain")->implode(', ', '{title}');
    if (strlen($captains) == 0) { 
      $captains = __('Nobody.');
    }
    echo '<p class="text-center">';
      if ($user->name == 'flieutaud' || $user->isSuperuser()) { // Any new serious injuries ? (personal workflow)
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
  } else {
    $pagination = $allPlayers->renderPager();
  }

  // Players table
  $out = '';

  if (isset($pagination)) { $out .= $pagination;}
  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    $cacheName = 'cache__players-teacher-'.$input->urlSegment1.'-'.$user->language->name;
  }
  if ($user->hasRole('player') || $user->isGuest()) {
    $cacheName = 'cache__players-player-'.$input->urlSegment1.'-'.$headTeacher->language->name;
  }
  $cachedTable = $cache->get($cacheName, 86400, function($user, $pages, $config) use($selectedTeam, $allPlayers) {
    $out = '';
    $out .= '<table id="teamTable" class="table table-hover table-condensed teamView" data-highlight="{$loggedId}">';
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
    $out .= '<th data-toggle="tooltip" title="'.__("Reputation").'">R.</th>';
    $out .= '<th data-toggle="tooltip" title="'.__("Health Points").'"><img src="'.$config->urls->templates.'img/heart.png" alt="" /> ';
    $out .= __("HP").'</th>';
    $out .= '<th data-toggle="tooltip" title="'.__("Experience").'"><img src="'.$config->urls->templates.'img/star.png" alt="" /> ';
    $out .= __("XP").'</th>';
    $out .= '<th data-toggle="tooltip" title="'.__("Places").'"><img src="'.$config->urls->templates.'img/globe.png" alt="" /></th>';
    if ($selectedTeam->rank && $selectedTeam->rank->is("index>=8")) {
      $out .= '<th data-toggle="tooltip" title="'.__("People").'"><span class="glyphicon glyphicon-user"></span></th>';
    }
    $out .= '<th data-toggle="tooltip" title="'.__("Equipment").'"><span class="glyphicon glyphicon-wrench"></span></th>';
    $out .= '<th data-toggle="tooltip" title="'.__("Donation").'"><img src="'.$config->urls->templates.'img/heart.png" alt="" /></th>';
    $out .= '<th data-toggle="tooltip" title="'.__("Underground training").'">';
    $out .= __("UT").'</th>';
    $out .= '<th data-toggle="tooltip" title="'.__("Fighting Power").'">';
    $out .= __("FP").'</th>';
    $out .= '</tr>';
    $out .= '</thead>';
    $out .= '<tbody>';
    foreach($allPlayers as $player) {
      $historyPage = $player->get("name=history");
      /* $player->login == $user->name ? $class = ' class="selected"' : $class = ''; */
      if ($user->isLoggedin()) { // Get last recorded events
        $lastEvent = $historyPage->child("sort=-date");
        $prevDate = date("m/d/Y", $lastEvent->date)." 0:0:0"; // Get all events on same day
        $prevEvents = $historyPage->children("template=event, date>=$prevDate");
        $trend = '';
        foreach ($prevEvents as $event) {
          if (isset($headTeacher)) { // Get custom info if needed
            $mod = $event->task->owner->get("singleTeacher=$headTeacher"); // Get personalized infos if needed
            if ($mod) {
              if ($mod->title != '') { $event->task = $mod->title; }
              if ($mod->HP != '') { $event->task->HP = $mod->HP; }
            }
          }
          /* if (($user->hasRole('teacher') || $user->isSuperuser()) && $event->task->is("name=penalty|death")) { $class = 'selected'; } */
          $HP = $event->task->HP;
          $title = $event->task->title;
          $HP < 0 || $event->task->is("name=inactivity") ? $trendClass = 'negativeTrend' : $trendClass = 'positiveTrend';
          $event->summary !== '' ? $summary = ' ('.$event->summary.')' : $summary = '';
          $trend .= '<span class="'.$trendClass.'" data-toggle="tooltip" data-html="true" title="'.strftime("%d/%m", $event->date).': '.$title.$summary.'">&nbsp;</span>';
        }
        if ($selectedTeam->is("name!=no-team|cm1")) { // Set hk counter (Personal workflow)
          if ($user->hasRole('teacher') || $user->isSuperuser() || $user->name == $player->login) { // Admin is logged or user
            if ($player->hkcount > 0) {
              $hkCount = '&nbsp;<span class="label label-danger">'.$player->hkcount.'</span>';
            } else {
              $hkCount = '';
            }
          } else {
            $hkCount = '';
          }
        } else {
          $hkCount = '';
        }
      } else {
        $hkCount = '';
      }
      $HPwidth = round(150*$player->HP/50); // Set HP progressbar
      // Set XP progressbar
      $threshold = getLevelThreshold($player->level);
      $XPwidth = round(150*$player->XP/($threshold));
      // Places list
      if ($player->places->count() > 0) {
        $listPlaces = '<ul>';
        $listPlaces .= $player->places->each('<li>{title}</li>');
        $listPlaces .= '</ul>';
        $tooltipPlaces =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listPlaces.'"';
      } else {
        $tooltipPlaces = '';
      }
      if ($selectedTeam->rank && $selectedTeam->rank->is("index>=8")) {
        // People list
        if ($player->people->count() > 0) {
          $listPeople = '<ul>';
          $listPeople .= $player->people->each('<li>{title}</li>');
          $listPeople .= '</ul>';
          $tooltipPeople =  'data-toggle="tooltip" data-html="true" data-placement="top" title="'.$listPeople.'"';
        } else {
          $tooltipPeople = '';
        }
      }
      // Equipment list
      if ($player->equipment->count() > 0) {
        $listEquipment = '<ul>';
        $listEquipment .= $player->equipment->each('<li>{title}</li>');
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
      $out .= '<tr class="'.$player->id.'">';
      $out .= '<td>';
      if ($player->group) { 
        $out .= $player->group->title;
      } else {
        $out .= '-';
      };
      $out .= '</td>';
      $out .= '<td>'. $mini .'</td>';
      $out .= '<td>';
      $out .='<a href="'.$player->url.'">'. $player->title .'</a>'.$hkCount;
      $out .= '</td>';
      $out .= '<td>'. $player->yearlyKarma .'</td>';
      if ($user->isLoggedin()) {
        $out .= '<td><span class="trend">'.$trend.'</span></td>';
      }
      $showSkills = '';
      foreach($player->skills as $s) {
        $showSkills .= '<span class="label label-success" data-toggle="tooltip" title="'.$s->title.'">'.$s->title[0].'</span>';
      }
      if (!$player->skills->has("name=ambassador")) {
        $showSkills .= ' <span class="label label-primary">'.$player->streak.'</span>';
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
      if ($selectedTeam->rank && $selectedTeam->rank->is("index>=8")) {
        $out .= '<td '.$tooltipPeople.'>'. $player->people->count() .'</td>';
      }
      $out .= '<td '.$tooltipEquipment.'>'. $player->equipment->count() .'</td>';
      $out .= '<td>'. $player->donation .'</td>';
      $out .= '<td>'. $player->underground_training .'</td>';
      $out .= '<td>'. $player->fighting_power .'</td>';
      $out .= '</tr>';
    }
    $pages->unCacheAll();
    $out .= '</tbody>';
    $out .= '</table>';
    return $out;
  });

  $out .= $cachedTable;
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

  echo '</div>';

  include("./foot.inc");
?>
