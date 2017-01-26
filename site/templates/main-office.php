<?php /* main-office template */
  include("./head.inc"); 

  if ($user->isSuperuser()) { // Main office for admin only
    $out = '';
    $team = $page->parent;
    $rank = $team->rank->name;
    $allPlayers = $allPlayers->find("team=$team")->sort("-karma");

    include("./tabList.inc");

    /* $out .= '<div class="row">'; */
    /* $out .= '<h3 class="well text-center">'; */
    $out .= '<p id="honored" class="label label-danger pull-right"></p>';
    /* $out .= '<p>'.$team->title.' Main Office</p>'; */
    /* $out .= '</h3>'; */
    /* $out .= '</div>'; */

    // TODO : Make items clickable so admin can select a group/player/ambassador...
    
    // Decisions menu
    $out .= '<div id="decisions" class="well">';
    // TODO : Display player(s) GC, nbEl... ?
    $out .= '<h3>What\'s your decision? (I want to...)</h3>';
    $out .= '<ul>';
    $out .= '<li>Go to the Marketplace</li>';
    $out .= '<li>Repell Monster Invasions</li>';
    $out .= '<li>Pick a random mission</li>';
    $out .= '<li>Pick another group/player/ambassador</li>';
    $out .= '<li>Help another player</li>';
    $out .= '</ul>';
    $out .= '</div>';

    $out .= '<div class="col-sm-10">';
      // Groups
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

      // Calculate groups Karma
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
      $groupList = $allGroups->implode(', ', '{title}');
      $pickButton = ' <a class="btn btn-danger btn-sm pickFromList pull-right" data-list="'.$groupList.'">Pick a group</a>';
      $outGroups .= '<div id="" class="news panel panel-primary">';
      $outGroups .= '<div class="panel-heading">';
      $outGroups .= '<h4><span class="label label-primary">Most influential groups</span>'.$pickButton.'</h4>';
      $outGroups .= '</div>';
      $outGroups .= '<ul class="list-inline text-center">';
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
      $outGroups .= '</div>';
      $out .= $outGroups;

      // Top players
      $top = $allPlayers->find("name*=a,limit=5");
      $topList = $top->implode(', ', '{title}');
      $pickButton = ' <a class="btn btn-danger btn-sm pickFromList pull-right" data-list="'.$topList.'">Pick a player</a>';
      $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
      $out .= '<h4><span class="label label-primary">Most influential players</span>'.$pickButton.'</h4>';
      $out .= '</div>';
      $out .= '<ul class="list list-unstyled list-inline text-center">';
      foreach ($top as $p) {
        $out .= '<li>';
        $out .= '<div class="thumbnail text-center">';
        if ($p->avatar) {
          $out .= '<img class="" src="'.$p->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />';
        } else {
          $out .= '<Avatar>';
        }
        $out .= '<caption class="text-center">'.$p->title.' <span class="badge">'.$p->karma.'</span></caption>';
        $out .= '</div>';
        $out .= '</li>';
      }
      $out .= '</ul>';
      $out .= '</div>';

      // Ambassadors
      $ambassadors = $allPlayers->find("skills.name=ambassador");
      $ambassadorsList = $ambassadors->implode(', ', '{title}');
      if ($ambassadors->count() == 0) { 
        $pickButton = '';
      } else {
        $pickButton = ' <a class="btn btn-danger btn-sm pickFromList pull-right" data-list="'.$ambassadorsList.'">Pick a player</a>';
      }
      $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
      $out .= '<h4 class=""><span class="label label-primary">Ambassadors</span>'.$pickButton.'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
      $out .= '<ul class="list list-unstyled list-inline text-center">';
      foreach ($ambassadors as $p) {
        $out .= '<li>';
        $out .= '<div class="thumbnail text-center">';
        if ($p->avatar) {
          $out .= '<img class="" src="'.$p->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />';
        } else {
          $out .= '<Avatar>';
        }
        $out .= '<caption class="text-center">'.$p->title.'</caption>';
        $out .= '</div>';
        $out .= '</li>';
      }
      if ($ambassadors->count() == 0) {
        $out .= '<p>No ambassadors today.</p>';
      }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '</div>';
      
      // TODO Best evolution?
      // TODO Low HP players
    $out .= '</div>';

    $out .= '<div class="col-sm-2">';
      $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $dangerPlayers = $allPlayers->find('coma=1');
        $dangerPlayers->add($allPlayers->find("HP<=10"))->sort("coma, HP");
        $out .= '<p class="panel-title">Help needed!</span>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
        $out .= '<ul class="list list-unstyled list-inline text-center">';
        foreach($dangerPlayers as $p) {
          if ($p->coma == 1) {
            $label = 'Coma';
          } else {
            $label = $p->HP.'HP';
          }
          $out .= '<li>';
          $out .= '<div class="thumbnail text-center">';
          if ($p->avatar) {
            $out .= '<img class="" src="'.$p->avatar->getThumb("thumbnail").'" width="50" alt="Avatar" />';
          } else {
            $out .= '<Avatar>';
          }
          $out .= '<caption class="text-center">'.$p->title.' <span class="badge">'.$label.'</span></caption>';
          $out .= '</div>';
          $out .= '</li>';
        }
        $out .= '<ul>';
      $out .= '</div>';
      $out .= '</div>';
    $out .= '</div>';
  } else {
    $out .= 'Admin only.';
  }

  echo $out;

  include("./foot.inc");
?>
