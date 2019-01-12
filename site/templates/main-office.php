<?php namespace ProcessWire;
  include("./head.inc"); 

  $team = $pages->get("parent.name=teams, name=$input->urlSegment1");
  $rank = $team->rank->index;
  if ($team->name != 'no-team') {
    $allPlayers = $allPlayers->find("team=$team")->sort("-karma");
  } else {
    $allPlayers = $pages->find("team=$team")->sort("-karma");
  }

  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    include("./tabList.inc");
    $pickFromList = 'pickFromList';
  } else {
    $pickFromList = '';
  }

  $out = '';
  // Decisions menu (via ajax)
  $out .= '<div id="ajaxDecision" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="decision"></div>';
  $out .= '<div id="showInfo" data-href="'.$pages->get('name=ajax-content')->url.'"></div>';

  if (!($allTeams->count() == 1 && $allTeams->eq(0)->name == 'no-team')) { // Means Just no-team
    showScores($allTeams);
  }

  if ($allPlayers->count() > 0) {
    $dangerPlayers = $pages->find("parent.name=players, team=$team, (HP<=15), (coma=1)")->sort("coma, HP");
    $out .= '<div class="col-sm-4">';
      // Help needed
      $out .= '<div id="" class="board panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<p class="panel-title">'.__("Help needed !").'</p>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
        if ($dangerPlayers->count() != 0) {
          $healingPotion = $pages->get("name=health-potion");
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
              $out .= '<img class="" src="'.$p->avatar->getCrop("thumbnail")->url.'" width="50" alt="Avatar" />';
            } else {
              $out .= '<Avatar>';
            }

            $out .= '<caption class="text-center">';
            $out .= $p->title;
            $out .= ' <span class="badge">'.$label.'</span><br />';
            if ($user->isSuperuser() || $user->hasRole('teacher')) {
              $out .= '<span class="badge">'.$p->GC.'GC</span>';
              if ($p->GC >= $healingPotion->GC) {
                $out .= ' <a href="#" class="btn btn-xs btn-link buyBtn" data-type="heal" data-url="'.$pages->get('name=submitforms')->url.'?form=buyForm&playerId='.$p->id.'&itemId='.$healingPotion->id.'">→ '.__("Heal ?").'</a>';
              }
            }
            $out .= '</caption>';
            $out .= '</div>';
            $out .= '</li>';
          }
          $out .= '<ul>';
        } else {
          $out .= '<p>'.__("Congratulations ! No player with HP<15 !").'</p>';
        }
      $out .= '</div>';
      $out .= '<div class="panel-footer text-right">';
        if ($dangerPlayers->count() != 0 && $healingPotion->id) {
          $out .= sprintf(__('Healing potion costs %d GC'), $healingPotion->GC);
        }
      $out .= '</div>';
      $out .= '</div>';

      // Most influential players
      $top = $allPlayers->sort("-karma")->find("limit=3, karma>0");
      if ($top->count() != 0) {
        $topList = $top->implode(', ', '{id}');
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$topList.'">'.__("Pick 1!").'</a>';
        } else {
          $pickButton = '';
        }
        $out .= '<div id="" class="board panel panel-primary">';
        $out .= '<div class="panel-heading">';
        $out .= '<h4><span class="label label-primary">'.__("Most influential players !").'</span>'.$pickButton.'</h4>';
        $out .= '</div>';
        $out .= '<ul class="list list-unstyled list-inline text-center">';
        foreach ($top as $p) {
          $out .= '<li>';
          $out .= '<div class="thumbnail text-center">';
          if ($p->avatar) {
            $out .= '<img class="'.$pickFromList.'" data-list="'.$p->id.'" src="'.$p->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
          } else {
            $out .= '<Avatar>';
          }
          $out .= '<caption class="text-center">'.$p->title.' <span class="badge">'.$p->karma.' Reputation</span></caption>';
          $out .= '</div>';
          $out .= '</li>';
        }
        $out .= '</ul>';
        $out .= '<div class="panel-footer text-center">';
        $teamPlayersList = $allPlayers->implode(', ', '{id}');
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $out .= ' <a id="pickTeamPlayer" class="btn btn-danger btn-xs pickFromList" data-list="'.$teamPlayersList.'">'.__("Pick a player in the whole team !").'</a>';
        }
        $out .= '</div>';
        $out .= '</div>';
      }

      // Groups
      if ($allPlayers->count() > 0 && $team->name != 'no-team') {
        $allGroups = groupScores($team);
        if (isset($allGroups)) {
          $groupList = $allGroups->implode(', ', '{id}');
          if ($groupList && ($user->isSuperuser() || $user->hasRole('teacher'))) {
            $pickButton = ' <a class="btn btn-danger btn-sm pickFromList" data-list="'.$groupList.'" data-team="'.$team.'">Pick 1!</a>';
          } else {
            $pickButton = '';
          }
        }
        $out .= '<div id="" class="board panel panel-primary">';
        $out .= '<div class="panel-heading">';
        $out .= '<h4><span class="label label-primary">'.__("Most active groups").'</span>'.$pickButton.'</h4>';
        $out .= '</div>';
        $out .= '<div class="panel-body">';
        if ($groupList) {
          $out .= '<ol class="">';
          foreach($allGroups as $group) {
            $out .= '<li>';
            if ($user->isSuperuser() || $user->hasRole('teacher')) {
              $out .= '<p class="pickFromList" data-list="'.$group->id.'" data-toggle="tooltip" data-html="true" title="'.$group->members.'" onmouseenter="$(this).tooltip(\'show\');" data-team="'.$team.'">';
            } else {
              $out .= '<p data-toggle="tooltip" data-html="true" title="'.$group->members.'" onmouseenter="$(this).tooltip(\'show\');">';
            }
            if (isset($player) && $player->group == $group) {
              $focus = 'focus';
            } else {
              $focus = '';
            }
            $out .= '<span class="'.$focus.'">'.$group->title.'</span>';
            $out .= ' <span class="badge">'.$group->karma.' K</span>';
            // Display stars for bonus (filled star = 5 empty stars, 1 star = 1 free element for each group member)
            if ($group->nbBonus > 0) {
              $out .= '&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-star"></span>';
              $out .= '<span class="badge">'.$group->nbBonus.'</span>';
            }
            $out .= '</p>';
            $out .= '</li>';
          }
          $out .= '</ol>';
        } else {
          $out .= '<p>'.__("The groups aren't ready yet.").'</p>';
        }
        $out .= '</div>';
        $out .= '</div>';
      }
    $out .= '</div>';

    $out .= '<div class="col-sm-8">';
      // Team News (Free/Buy actions during last 5 days)
      $news = new PageArray();
      $today = new \DateTime("today");
      $interval = new \DateInterval('P5D');
      $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
      $teamRecentUtPlayers = new PageArray();
      $teamRecentFightPlayers = new PageArray();
      $news = $pages->find("has_parent=$allPlayers, parent.name=history, template=event, date>=$limitDate, task.name~=free|buy|ut-action|fight, refPage!='', inClass=0, sort=-date");
      if ($news->count() > 0) {
        $news->sort("-date");
        $teamRecentUt = $news->find("task.name~=ut-action");
        foreach ($teamRecentUt as $n) {
          $currentPlayer = $n->parent('template=player');
          $teamRecentUtPlayers->add($currentPlayer);
        }
        $utPlayersList = $teamRecentUtPlayers->implode(', ', '{title}');
        $teamRecentFight = $news->find("task.name~=fight");
      }
      $out .= '<div id="" class="board panel panel-primary">';
      $out .= '<div class="panel-heading">';
      $out .= '<h4 class=""><span class="label label-primary">'.__("Team News (Equipment, places, people, out of class activity during the last 5 days)").'</span></h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
      $out .= '<ul id="newsList" class="list list-unstyled list-inline text-center">';
      if ($news->count() != 0) {
        foreach ($news as $n) {
          if ($n->task->is("!name~=ut-action")) {
            $currentPlayer = $n->parent('template=player');
            $out .= '<li>';
            $out .= '<div class="thumbnail">';
            if ($n->task->is("name=buy-pdf")) {
              $out .= '<p><span class="label label-success">'.__('Buy PDF').'</span></p>';
              $out .= '<p><span class="label label-primary">'.$n->refPage->title.'</span></p>';
            }
            if ($n->task->is("name~=fight")) {
              $out .= '<span class="label label-primary"><i class="glyphicon glyphicon-flash"></i> '.$n->refPage->title.'</span>';
              // Show result
              if ($n->task->is("name=fight-vv")) { $out .= ' <span class="label label-success">VV</span>'; }
              if ($n->task->is("name=fight-v")) { $out .= ' <span class="label label-success">V</span>'; }
              if ($n->task->is("name=fight-r")) { $out .= ' <span class="label label-danger">R</span>'; }
              if ($n->task->is("name=fight-rr")) { $out .= ' <span class="label label-danger">RR</span>'; }
            }
            if ($n->refPage->photo) {
              $out .= '<img class="showInfo" data-id="'.$n->refPage->id.'" src="'.$n->refPage->photo->eq(0)->getCrop("thumbnail")->url.'" alt="'.$n->summary.'" />';
            }
            if ($n->refPage->image) {
              $out .= '<img class="showInfo" data-id="'.$n->refPage->id.'" src="'.$n->refPage->image->getCrop("thumbnail")->url.'" alt="'.$n->summary.'" />';
            }
            $out .= '<caption class="text-center">';
            $out .= ' <span>(On '.date('l, M. j', $n->date).')</span><br />';
            $out .= ' <span class="badge">'.$currentPlayer->title.'</span>';
            $out .= '</caption>';
            $out .= '</div>';
            $out .= '</li>';
          }
        }
      } else {
        $out .= '<p>'.__("No recent news :(").'</p>';
      }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '<div class="panel-footer text-center">';
      if (isset($teamRecentUt) && $teamRecentUt->count() > 0) {
        $out .= '<h3>';
          $out .= '<span class="label label-success"><i class="glyphicon glyphicon-headphones"></i> '.$teamRecentUt->count().' '.__('UT sessions').' → '.$utPlayersList.' <i class="glyphicon glyphicon-thumbs-up"></i></span>';
        $out .= '</h3>';
      } else {
        $out .= '<h3>'.__("No recent UT training :(").'</h3>';
      }
      $out .= '</div>';
      $out .= '</div>';

      // Ambassadors
      $ambassadors = $allPlayers->find("skills.name=ambassador");
      $ambassadorsList = $ambassadors->implode(', ', '{id}');
      if ($user->isSuperuser() || $user->hasRole('teacher')) {
        if ($ambassadors->count() == 0) { 
          $pickButton = '';
        } else {
          $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$ambassadorsList.'">'.__("Pick 1!").'</a>';
        }
      } else {
        $pickButton = '';
      }
      $out .= '<div id="" class="board panel panel-primary">';
      $out .= '<div class="panel-heading">';
      $out .= '<h4 class=""><span class="label label-primary">'.__("Ambassadors").'</span>'.$pickButton.'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
      $out .= '<ul class="list list-unstyled list-inline text-center">';
      foreach ($ambassadors as $p) {
        $out .= '<li>';
        $out .= '<div class="thumbnail text-center">';
        if ($p->avatar) {
          $out .= '<img class="'.$pickFromList.'" data-list="'.$p->id.'" src="'.$p->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />';
        } else {
          $out .= '<Avatar>';
        }
        $out .= '<caption class="text-center">'.$p->title.'</caption>';
        $out .= '</div>';
        $out .= '</li>';
      }
      if ($ambassadors->count() == 0) {
        $out .= '<p>'.__("No ambassadors today.").'</p>';
      }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '</div>';
      
      // Captains
      $captains = $allPlayers->find("skills.name=captain");
      $captainsList = $captains->implode(', ', '{id}');
      if ($user->isSuperuser() || $user->hasRole('teacher')) {
        if ($captains->count() == 0) { 
          $pickButton = '';
        } else {
          $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$captainsList.'">'.__("Pick 1!").'</a>';
        }
      } else {
        $pickButton = '';
      }
      $out .= '<div id="" class="board panel panel-primary">';
      $out .= '<div class="panel-heading">';
      $out .= '<h4 class=""><span class="label label-primary">'.__("Captains").'</span>'.$pickButton.'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
      $out .= '<ul class="list list-unstyled list-inline text-center">';
      foreach ($captains as $p) {
        $out .= '<li>';
        $out .= '<div class="thumbnail text-center">';
        if ($p->avatar) {
          $out .= '<img class="'.$pickFromList.'" data-list="'.$p->id.'" src="'.$p->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />';
        } else {
          $out .= '<Avatar>';
        }
        $out .= '<caption class="text-center">'.$p->title.'</caption>';
        $out .= '</div>';
        $out .= '</li>';
      }
      if ($captains->count() == 0) {
        $out .= '<p>'.__("No captains yet.").'</p>';
      }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '</div>';
      
      // Most active players
      $top = $allPlayers->sort('-yearlyKarma, karma')->find("limit=5, yearlyKarma>0");
      if ($top->count() != 0) {
        $topList = $top->implode(', ', '{id}');
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$topList.'">'.__("Pick 1!").'</a>';
        } else {
          $pickButton = '';
        }
        $out .= '<div id="" class="board panel panel-primary">';
        $out .= '<div class="panel-heading">';
        $out .= '<h4><span class="label label-primary">'.__("Most active players !").'</span>'.$pickButton.'</h4>';
        $out .= '</div>';
        $out .= '<ul class="list list-unstyled list-inline text-center">';
        foreach ($top as $p) {
          $out .= '<li>';
          $out .= '<div class="thumbnail text-center">';
          if ($p->avatar) {
            $out .= '<img class="'.$pickFromList.'" data-list="'.$p->id.'" src="'.$p->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
          } else {
            $out .= '<Avatar>';
          }
          $out .= '<caption class="text-center">'.$p->title.' <span class="badge">'.$p->yearlyKarma.'K</span></caption>';
          $out .= '</div>';
          $out .= '</li>';
        }
        $out .= '</ul>';
        $out .= '</div>';
      }

      // Top players
      $topPlayers = new PageArray();
      $fpPlayer = $allPlayers->sort('-fighting_power, karma')->first();
      if ($fpPlayer->fighting_power == 0) {
        unset($fpPlayer);
      } else {
        $topPlayers->add($fpPlayer);
      }
      $donPlayer = $allPlayers->sort('-donation, karma')->first();
      if ($donPlayer->donation == 0) {
        unset($donPlayer);
      } else {
        $topPlayers->add($donPlayer);
      }
      $utPlayer = $allPlayers->sort('-underground_training, karma')->first();
      if ($utPlayer->underground_training == 0) {
        unset($utPlayer);
      } else {
        $topPlayers->add($utPlayer);
      }
      $eqPlayer = $allPlayers->sort('-equipment.count, karma')->first();
      if (count($eqPlayer->equipment) == 0) {
        unset($eqPlayer);
      } else {
        $topPlayers->add($eqPlayer);
      }
      $plaPlayer = $allPlayers->sort('-places.count, karma')->first();
      if (count($plaPlayer->places) == 0) {
        unset($plaPlayer);
      } else {
        $topPlayers->add($plaPlayer);
      }
      if ($rank >= 8) {
        $peoPlayer = $allPlayers->sort('-people.count, karma')->first();
        if (count($peoPlayer->people) == 0) {
          unset($peoPlayer);
        } else {
          $topPlayers->add($peoPlayer);
        }
      }
      $topPlayersList = $topPlayers->implode(', ', '{id}');
      if ($user->isSuperuser() || $user->hasRole('teacher')) {
        if ($topPlayers->count() == 0) { 
          $pickButton = '';
        } else {
          $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$topPlayersList.'">'.__("Pick 1!").'</a>';
        }
      } else {
        $pickButton = '';
      }
      $out .= '<div id="" class="board panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<p class="panel-title">'.__("Top players !").$pickButton.'</p>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
      $out .= '<ul class="list list-unstyled list-inline text-center">';
        if (isset($fpPlayer)) {
          $out .= '<li>';
          $out .= '<div class="fame thumbnail">'; // Best warrior
            $out .= '<span class="badge">'.__("Best warrior !").'</span>';
            if ($fpPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$fpPlayer->id.'" src="'.$fpPlayer->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />'; }
            $out .= '<div class="caption text-center">'.$fpPlayer->title.' <span class="badge">'.$fpPlayer->fighting_power.__("FP").'</span></div>';
          $out .= '</div>';
          $out .= '</li>';
        }
        if (isset($donPlayer)) {
          $out .= '<li>';
          $out .= '<div class="fame thumbnail">'; // Best donator
            $out .= '<span class="badge">'.__("Best donator !").'</span>';
            if ($donPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$donPlayer->id.'" src="'.$donPlayer->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />'; }
            $out .= '<div class="caption text-center">'.$donPlayer->title.' <span class="badge">'.$donPlayer->donation.'Don.</span></div>';
          $out .= '</div>';
          $out .= '</li>';
        }
        if (isset($utPlayer)) {
          $out .= '<li>';
          $out .= '<div class="fame thumbnail">'; // Most trained
            $out .= '<span class="badge">'.__("Most trained !").'</span>';
            if ($utPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$utPlayer->id.'" src="'.$utPlayer->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />'; }
            $out .= '<div class="caption text-center">'.$utPlayer->title.' <span class="badge">'.$utPlayer->underground_training.__("UT").'</span></div>';
          $out .= '</div>';
          $out .= '</li>';
        }
        if (isset($eqPlayer)) {
          $out .= '<li>';
          $out .= '<div class="fame thumbnail">'; // Most equipped
            $out .= '<span class="badge">'.__("Most equipped !").'</span>';
            if ($eqPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$eqPlayer->id.'" src="'.$eqPlayer->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />'; }
            $out .= '<div class="caption text-center">'.$eqPlayer->title.' <span class="badge">'.$eqPlayer->equipment->count().'eq.</span></div>';
          $out .= '</div>';
          $out .= '</li>';
        }
        if (isset($plaPlayer)) {
          $out .= '<li>';
          $out .= '<div class="fame thumbnail">'; // Greatest # of Places
            $out .= '<span class="badge">'.__("Greatest # of Places !").'</span>';
            if ($plaPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$plaPlayer.'" src="'.$plaPlayer->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />'; }
            $out .= '<div class="caption text-center">'.$plaPlayer->title.' <span class="badge">'.$plaPlayer->places->count().__("pla.").'</span></div>';
          $out .= '</div>';
          $out .= '</li>';
        }
        if ($rank >= 8) {
          if (isset($peoPlayer)) {
            $out .= '<li>';
            $out .= '<div class="fame thumbnail">'; // Greatest # of people
              $out .= '<span class="badge">'.__("Greatest # of People !").'</span>';
              if ($peoPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$peoPlayer->id.'" src="'.$peoPlayer->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />'; }
              $out .= '<div class="caption text-center">'.$peoPlayer->title.' <span class="badge">'.$peoPlayer->people->count().'peo.</span></div>';
            $out .= '</div>';
            $out .= '</li>';
          }
        }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '</div>';
    $out .= '</div>';
  } else {
    $out .= '<h3 class="text-center">'.__("No players in the team !").'</h3>';
  }


  echo $out;

  include("./foot.inc");
?>
