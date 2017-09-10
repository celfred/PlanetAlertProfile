<?php namespace ProcessWire; /* main-office template */
  include("./head.inc"); 

  $out = '';
  $team = $pages->get("name=$input->urlSegment1");
  $rank = $team->rank->name;
  $allPlayers = $allPlayers->find("team=$team")->sort("-karma");
  include("./tabList.inc");

  // Decisions menu (via ajax)
  $out .= '<div id="ajaxDecision" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="decision"></div>';

  $out .= '<div class="col-sm-4">';
    // Groups
    if ($user->isSuperuser()) {
      $pickFromList = 'pickFromList';
      $out .= displayGroups($allPlayers, 1);
    } else {
      $pickFromList = '';
      $out .= displayGroups($allPlayers, 0);
    }

    // Help needed
    $out .= '<div id="" class="board panel panel-primary">';
    $out .= '<div class="panel-heading">';
      $dangerPlayers = $allPlayers->find('coma=1');
      $dangerPlayers->add($allPlayers->find("HP<=10"))->sort("coma, HP");
      $out .= '<p class="panel-title">Help needed!</p>';
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
            $out .= '<img class="" src="'.$p->avatar->getThumb("thumbnail").'" width="50" alt="Avatar" />';
          } else {
            $out .= '<Avatar>';
          }

          $out .= '<caption class="text-center">';
          $out .= $p->title;
          $out .= ' <span class="badge">'.$label.'</span><br />';
          if ($user->isSuperuser()) {
            $out .= '<span class="badge">'.$p->GC.'GC</span>';
            if ($p->GC >= $healingPotion->GC) {
              $out .= ' <a href="#" class="btn btn-xs btn-link buyBtn" data-type="heal" data-url="'.$pages->get('name=submitforms')->url.'?form=buyForm&playerId='.$p->id.'&itemId='.$healingPotion->id.'">→ Heal?</a>';
            }
          }
          $out .= '</caption>';
          $out .= '</div>';
          $out .= '</li>';
        }
        $out .= '<ul>';
      } else {
        $out .= '<p>Congratulations ! No player with HP<10 !</p>';
      }
    $out .= '</div>';
    $out .= '<div class="panel-footer text-right">';
      if ($dangerPlayers->count() != 0 && $healingPotion->id) {
        $out .= 'Healing potion costs '.$healingPotion->GC.'GC';
      }
    $out .= '</div>';
    $out .= '</div>';
  $out .= '</div>';

  $out .= '<div class="col-sm-8">';
    // Ambassadors
    $ambassadors = $allPlayers->find("skills.name=ambassador");
    $ambassadorsList = $ambassadors->implode(', ', '{id}');
    if ($user->isSuperuser()) {
      if ($ambassadors->count() == 0) { 
        $pickButton = '';
      } else {
        $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$ambassadorsList.'">Pick 1!</a>';
      }
    } else {
      $pickButton = '';
    }
    $out .= '<div id="" class="board panel panel-primary">';
    $out .= '<div class="panel-heading">';
    $out .= '<h4 class=""><span class="label label-primary">Ambassadors</span>'.$pickButton.'</h4>';
    $out .= '</div>';
    $out .= '<div class="panel-body">';
    $out .= '<ul class="list list-unstyled list-inline text-center">';
    foreach ($ambassadors as $p) {
      $out .= '<li>';
      $out .= '<div class="thumbnail text-center">';
      if ($p->avatar) {
        $out .= '<img class="'.$pickFromList.'" data-list="'.$p->id.'" src="'.$p->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />';
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
    
    // Most influential players
    $top = $allPlayers->find("limit=5, karma>0");
    if ($top->count() != 0) {
      $topList = $top->implode(', ', '{id}');
      if ($user->isSuperuser()) {
        $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$topList.'">Pick 1!</a>';
      } else {
        $pickButton = '';
      }
      $out .= '<div id="" class="board panel panel-primary">';
      $out .= '<div class="panel-heading">';
      $out .= '<h4><span class="label label-primary">Most influential players !</span>'.$pickButton.'</h4>';
      $out .= '</div>';
      $out .= '<ul class="list list-unstyled list-inline text-center">';
      foreach ($top as $p) {
        $out .= '<li>';
        $out .= '<div class="thumbnail text-center">';
        if ($p->avatar) {
          $out .= '<img class="'.$pickFromList.'" data-list="'.$p->id.'" src="'.$p->avatar->getThumb("thumbnail").'" alt="Avatar" />';
        } else {
          $out .= '<Avatar>';
        }
        $out .= '<caption class="text-center">'.$p->title.' <span class="badge">'.$p->karma.'K</span></caption>';
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
    if ($eqPlayer->equipment->count() == 0) {
      unset($eqPlayer);
    } else {
      $topPlayers->add($eqPlayer);
    }
    $plaPlayer = $allPlayers->sort('-places.count, karma')->first();
    if ($plaPlayer->places->count() == 0) {
      unset($plaPlayer);
    } else {
      $topPlayers->add($plaPlayer);
    }
    if ($rank == '4emes' || $rank == '3emes') {
      $peoPlayer = $allPlayers->sort('-people.count, karma')->first();
      if ($peoPlayer->people->count() == 0) {
        unset($peoPlayer);
      } else {
        $topPlayers->add($peoPlayer);
      }
    }
    $topPlayersList = $topPlayers->implode(', ', '{id}');
    if ($user->isSuperuser()) {
      if ($topPlayers->count() == 0) { 
        $pickButton = '';
      } else {
        $pickButton = ' <a class="btn btn-danger btn-xs '.$pickFromList.' pull-right" data-list="'.$topPlayersList.'">Pick 1!</a>';
      }
    } else {
      $pickButton = '';
    }
    $out .= '<div id="" class="board panel panel-primary">';
    $out .= '<div class="panel-heading">';
      $out .= '<p class="panel-title">Top players !'.$pickButton.'</p>';
    $out .= '</div>';
    $out .= '<div class="panel-body">';
      if (isset($fpPlayer)) {
        $out .= '<div class="fame thumbnail">'; // Best warrior
          $out .= '<span class="badge">Best warrior !</span>';
          if ($fpPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$fpPlayer->id.'" src="'.$fpPlayer->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />'; }
          $out .= '<div class="caption text-center">'.$fpPlayer->title.' <span class="badge">'.$fpPlayer->fighting_power.'FP</span></div>';
        $out .= '</div>';
      }
      if (isset($donPlayer)) {
        $out .= '<div class="fame thumbnail">'; // Best donator
          $out .= '<span class="badge">Best donator !</span>';
          if ($donPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$donPlayer->id.'" src="'.$donPlayer->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />'; }
          $out .= '<div class="caption text-center">'.$donPlayer->title.' <span class="badge">'.$donPlayer->donation.'Don.</span></div>';
        $out .= '</div>';
      }
      if (isset($utPlayer)) {
        $out .= '<div class="fame thumbnail">'; // Most trained
          $out .= '<span class="badge">Most trained !</span>';
          if ($utPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$utPlayer->id.'" src="'.$utPlayer->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />'; }
          $out .= '<div class="caption text-center">'.$utPlayer->title.' <span class="badge">'.$utPlayer->underground_training.'UT</span></div>';
        $out .= '</div>';
      }
      if (isset($eqPlayer)) {
        $out .= '<div class="fame thumbnail">'; // Most equipped
          $out .= '<span class="badge">Most equipped !</span>';
          if ($eqPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$eqPlayer->id.'" src="'.$eqPlayer->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />'; }
          $out .= '<div class="caption text-center">'.$eqPlayer->title.' <span class="badge">'.$eqPlayer->equipment->count().'eq.</span></div>';
        $out .= '</div>';
      }
      if (isset($plaPlayer)) {
        $out .= '<div class="fame thumbnail">'; // Greatest # of Places
          $out .= '<span class="badge">Greatest # of Places !</span>';
          if ($plaPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$plaPlayer.'" src="'.$plaPlayer->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />'; }
          $out .= '<div class="caption text-center">'.$plaPlayer->title.' <span class="badge">'.$plaPlayer->places->count().'pla.</span></div>';
        $out .= '</div>';
      }
      if ($rank == '4emes' || $rank == '3emes') {
        if (isset($peoPlayer)) {
          $out .= '<div class="fame thumbnail">'; // Greatest # of people
            $out .= '<span class="badge">Greatest # of People !</span>';
            if ($peoPlayer->avatar) { $out .= '<img class="'.$pickFromList.'" data-list="'.$peoPlayer->id.'" src="'.$peoPlayer->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />'; }
            $out .= '<div class="caption text-center">'.$peoPlayer->title.' <span class="badge">'.$peoPlayer->people->count().'peo.</span></div>';
          $out .= '</div>';
        }
      }
    $out .= '</div>';
    $out .= '</div>';
  $out .= '</div>';

  echo $out;

  include("./foot.inc");
?>
