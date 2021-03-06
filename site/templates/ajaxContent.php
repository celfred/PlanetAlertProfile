<?php namespace ProcessWire;
  if ($config->ajax) {
    $out = '';
    if (!$user->isSuperuser()) {
      $headTeacher = getHeadTeacher($user);
      $user->language = $headTeacher->language;
      if ($user->hasRole('player')) {
        $currentPlayer = $pages->get("parent.name=players, template=player, login=$user->name");
      }
    }
    switch ($input->get('id')) {
      case 'decision' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->is("template=player")) {
          // Possible places
          if ($p->coma == false) {
            $allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
            $allPeople = $pages->find("template=people, name!=people, sort=title");
            $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
            $possiblePlaces = $allPlaces->find("GC<=$p->GC, level<=$p->level, id!=$p->places,sort=name");
            // Possible people
            $possiblePeople = $allPeople->find("GC<=$p->GC, level<=$p->level, id!=$p->people,sort=name");
            // Possible equipments
            $nbEl = $p->places->count()+$p->people->count();
            $possibleEquipment = $allEquipments->find("GC<=$p->GC, level<=$p->level, freeActs<=$nbEl, id!=$p->equipment, parent.name!=potions, sort=-parent.name, sort=name");
            // Get rid of potions bought within the last 15 days
            $today = new \DateTime("today");
            $interval = new \DateInterval('P15D');
            $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
            $boughtPotions = $p->find("template=event, date>=$limitDate, refPage.name~=potion, refPage.name!=health-potion");
            $possiblePotions = $allEquipments->find("GC<=$p->GC, level<=$p->level, freeActs<=$nbEl, parent.name=potions, sort=name")->not($boughtPotions);
            $healthPotion = $allEquipments->get("name=health-potion");
            if ($p->HP == 50) { $possiblePotions->remove($healthPotion); }
            $possibleItems = new pageArray();
            $possibleItems->add($possiblePlaces);
            if ($p->team->rank && $p->team->rank->is("index>=8")) { // Add people ONLY for 4emes/3emes
              $possibleItems->add($possiblePeople);
            }
            $possibleItems->add($possibleEquipment);
            $possibleItems->add($possiblePotions);
          }
          $team = $p->team;
          $donatorId = $p->id;
          if ($p->avatar) { $mini = '<img src="'.$p->avatar->getCrop('thumbnail')->url.'" alt="'.$p->title.'." />'; }
          $out .= '<div class="row">';
          $out .= '<div class="col-sm-6 text-center">';
          $out .= '<h3 class="thumbnail">'.$mini.' <span class="caption">'.$p->title.'</span></h3>';
          $out .= '</div>';
          $out .= '<div class="col-sm-6 text-left">';
          $out .= '<ul class="list-unstyled">';
          if ($p->coma == 0) {
            $out .= '<li><span class="label label-success">Karma : '.$p->yearlyKarma.'</span></li>';
            $out .= '<li><span class="label label-success">Reputation : '.$p->reputation.'</span></li>';
            $out .= '<li><span class="label label-default"><span class="glyphicon glyphicon-signal"></span> '.$p->level.'</span>';
            $threshold = getLevelThreshold($p->level);
            $out .= ' <span class="label label-default"><img src="'.$config->urls->templates.'img/star.png" alt="star." /> '.$p->XP.'/'.$threshold.'</span></li>';
            $nbFreeEl = $p->places->count();
            if ($p->team->rank && $p->team->rank->is("index>=8")) {
              $nbFreeEl += $p->people->count();
            }
            $out .= '<li><span class="label label-default"><img src="'.$config->urls->templates.'img/globe.png" alt="globe." /> '.$nbFreeEl.'</span>';
            $out .= ' <span class="label label-default"><span class="glyphicon glyphicon-wrench"></span> '.$p->equipment->count().'</span></li>';
            $out .= '<li><span class="label label-default">'.$p->underground_training.' UT</span>';
            $out .= ' <span class="label label-default">'.$p->fighting_power.' FP</span>';
            $out .= '</li>';
            $out .= '<li><span class="label label-default"><img src="'.$config->urls->templates.'img/heart.png" alt="heart." /> '.$p->HP.'</span>';
            $out .= ' <span class="label label-default"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="gold coins." /> '.$p->GC.'</span></li>';
          } else {
            $out .= '<li class="label label-danger">Coma !</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          $out .= '</div>';
        }
        if ($p->is("parent.name=groups")) {
          // Get group members
          $teamId = $input->get('teamId');
          $team = $pages->get("id=$teamId");
          $groupPlayers = $pages->find("template=player, team=$team, group=$pageId");
          $out .= '<div class="row">';
            $out .= '<p class="text-center"><span class="label label-primary">'.$p->title.'</span></p>';
            $out .= '<ul class="list-unstyled list-inline text-left">';
            $donatorId = $groupPlayers->sort('-GC')->first()->id;
            $groupPlayers->sort('-reputation');
            foreach($groupPlayers as $gp) {
              $nbFreeEl = $gp->places->count();
              if ($gp->team->rank && $gp->team->rank->is("index>=8")) {
                $nbFreeEl += $gp->people->count();
              }
              if ($gp->avatar) { $mini = '<img src="'.$gp->avatar->getCrop('thumbnail')->url.'" alt="'.$gp->title.'." width="50" />'; }
            $out .= '<li>';
            $out .= $mini;
            $out .= '<span>';
            $out .= $gp->title;
            if ($gp->coma == 0) {
              $out .= ' <span class="badge">'.$gp->reputation.'K.</span>';
              $out .= ' <span class="badge"><span class="glyphicon glyphicon-wrench"></span>'.$gp->equipment->count().'</span>';
              $out .= ' <span class="badge"><img src="'.$config->urls->templates.'img/globe.png" alt="globe." /> '.$nbFreeEl.'</span>';
              $out .= ' <span class="badge">'.$gp->HP.'<img src="'.$config->urls->templates.'img/heart.png" alt="heart." /></span>';
              $out .= ' <span class="badge">'.$gp->GC.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="gold coins." /></span>';
            } else {
              $out .= '<span class="label label-danger">Coma !</span>';
            }
            $out .= '</span>';
            $out .= '</li>';
            }
            $out .= '</ul>';
          $out .= '</div>';
        }
        $out .= '<div class="contrast">';
        $out .= '<ul class="text-left list-unstyled">[I want to...]';
        // Organize team defense
        $nbConcerned = possibleDefense($team);
        if ($nbConcerned > 0) {
          $out .= '<li><span><a href="'.$pages->get("name=quiz")->url.$team->name.'">→ Organize team defense ('.$nbConcerned.' players concerned).</a></span></li>';
        } else {
          /* $out .= '<li><span class="strikeText">→ No team defense.</span></li>'; */
        }
        // Read Team news
        /* if ($input->get->news && $input->get->news>0) { */
          /* $out .= '<li><span><a href="#" data-type="teamNews" class="ajaxBtn">→ Read about Team News.</a></span></li>'; */
        /* } else { */
          /* $out .= '<li><span class="strikeText">→ No team news today...</span></li>'; */
        /* } */
        // Personal initiative Talk (for 4emes/3emes)
        /* if ($p->team->rank && $p->team->rank->is("index>=8")) { */
        /*   $task = $pages->get("name=personal-initiative"); */
        /*   $out .= '<li><span><a href="#" class="ajaxBtn" data-type="initiative" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask&playerId='.$p->id.'&taskId='.$task->id.'">→ Talk about [...] for 2 minutes.</a> [Personal initiative]</span></li>'; */
        /* } */
        if ($p->is("parent.name!=groups")) { // Not for groups
          // Special discount
          if ($possibleItems->count() > 0) {
            if (rand(0,1)) { // Random special discount
              // Pick a random item
              $selectedItem = $possibleItems->getRandom();
              if ($selectedItem->is("has_parent.name=places")) {
                $details = ' in '.$selectedItem->city->title.' ('.$selectedItem->country->title.')';
              } else if ($selectedItem->is("has_parent.name=people")) {
                $details = ' from '.$selectedItem->country->title;
              } else {
                $details = ' ('.$selectedItem->category->title.')';
              }
              // Pick a random discount
              $discount = $pages->find("parent=/specials")->getRandom();
              $newPrice = round($selectedItem->GC-($selectedItem->GC*($discount->name/100))).'GC';
              if ($newPrice == 0) { $newPrice = 'Free'; }
              $out .= '<li id="discount">';
                $out .= ' <h3><a href="#" class="buyBtn" data-url="'.$pages->get('name=submitforms')->url.'?form=buyForm&playerId='.$p->id.'&itemId='.$selectedItem->id.'&discount='.$discount->id.'" data-GC="'.$p->GC.'" data-item-price="'.$selectedItem->GC.'">→ Get <span class="label label-danger">'.$discount->title.'%</span> discount on <span class="label label-primary">'.$selectedItem->title.'</span> '.$details.' !</a></h3>';
              $out .= '<div class="row">';
                $out .= '<div class="col-sm-6 text-right">';
                  $out .= '<h3><span class="strikeText">'.$selectedItem->GC.'GC</span> → <span class="label label-success">'.$newPrice.'</span></h3>';
                $out .= '</div>';
                $out .= '<div class="col-sm-6 text-left">';
                if ($selectedItem->photo) {
                  $out .= '<img class="img-thumbnail" src="'.$selectedItem->photo->eq(0)->getCrop('thumbnail')->url.'" /> ';
                }
                if ($selectedItem->image) {
                  $out .= '<img class="img-thumbnail" src="'.$selectedItem->image->getCrop('thumbnail')->url.'" /> ';
                }
                $out .= '</div>';
              $out .= '</div>';
              $out .= '</li>';
            } else {
              $out .= '<li id="discount"><h3>Sorry, no discount available...</h3></li>';
            }
            // Play for a discount
            $out .= '<li><span><a href="#" class="ajaxBtn" data-type="discount">→ Play for a random discount (cheaper place, equipment...)</a></span></li>';
          }
          // Go to the Marketplace
          if ($possibleItems->count() > 0) {
            $out .= '<li><span><a href="'.$pages->get("name=shop")->url.$team->name.'?playerId='.$p->id.'">→ Go to my marketplace.</a></span></li>';
          }
        }
        // Make a donation
        if ($p->GC > 5 || $p->is("parent.name=groups")) {
          $out .= '<li><span><a href="'.$pages->get("name=makedonation")->url.$team->name.'/'.$donatorId.'">→ Make a donation.</a></span></li>';
        }
        // Go to team's Freeworld
        /* $out .= '<li><span><a href="'.$pages->get("name=world")->url.$team->name.'">→ See team\'s Freeworld.</a></span></li>'; */
        // Go to team's scoring table
        /* $out .= '<li><span><a href="'.$pages->get("name=players")->url.$team->name.'">→ See team\'s scoring table.</a></span></li>'; */
        // Read about a random element
        /* $allPlaces = $pages->get("/places/")->find("template='place', sort='title'"); */
        $allPeople = $pages->find("template=people, name!=people, sort=title");
        if (isset($allPlaces) && isset($allPeople)) {
          $allElements = clone($allPlaces);
          $allElements->add($allPeople);
          $randomId = $allElements->getRandom()->id;
          $out .= '<li><span><a href="#" class="ajaxBtn" data-type="showInfo" data-id="'.$randomId.'">→ Read about a random element.</a></span></li>';
        }
        $out .= '</ul>';
        $out .= '</div>';
        break;
      case 'ambassador' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->avatar) { $mini = '<img src="'.$p->avatar->getCrop('thumbnail')->url.'" alt="'.$p->title.'." />'; }
        $out .= '<h3 class="thumbnail">'.$mini.' <span class="caption">'.$p->title.'</span></h3>';
        break;
      case 'help' :
        $playerId = $input->get('playerId');
        $player = $pages->get($playerId);
        $healingPotion = $pages->get("name=health-potion");
        $neededGC = $healingPotion->GC - $player->GC;
        $allHelpers = $pages->find("parent.name=players, team=$player->team, GC>=$neededGC, sort=name");
        $out .= '<h2>'.sprintf(__('%d GC needed !'), $neededGC).'</h2>';
        if ($allHelpers->count() > 0) {
          $out .= '<p>'.__('Does a player want to help by making a donation ?').'</p>';
          $out .= '<ul class="col4 text-left list-unstyled">';
          foreach($allHelpers as $h) {
            $leftGC = $h->GC-$neededGC;
            $out .= '<li>';
            $out .= '<a href="'.$pages->get("name=main-office")->url.$player->team->name.'" data-href="'.$pages->get("name=submitforms")->url.'?form=quickDonation&receiver='.$player->id.'&donator='.$h->id.'&amount='.$neededGC.'" class="basicConfirm" data-reload="true" data-msg="['.$h->title.'→'.sprintf(__('%d GC left'), $leftGC).']">';
            $out .= $h->title.' ('.$h->GC.__("GC").')</a>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__('No player has enough in the team. Team needs to get organized and collaborate to help the player !').'</p>';
        }
        break;
      case 'addRequest' :
        $allPlayers = $pages->find("id=$session->allPlayers, fight_request=''")->sort("title"); // Avoid pending fight requests
        if ($user->hasRole('teacher')) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, (created_users_id=$user->id),(exerciseOwner.singleTeacher=$user,exerciseOwner.publish=1)")->sort("name");
        } else if ($user->isSuperuser()) {
          $allMonsters = $pages->find("parent.name=monsters, template=exercise, sort=name");
        }
        $out .= '<h1>'.__("Add a fight request ?").'</h1>';
        $out .= '<p>'.__("Select a player").' : ';
        $out .= '<select id="playerId">';
        foreach ($allPlayers as $p) {
          $out .= '<option value="'.$p->id.'">'.$p->title.' ['.$p->team->title.']';
        }
        $out .= '</select>';
        $out .= '</p>';
        $out .= '<p>'.__("Select a monster").' : ';
        $out .= '<select id="monsterId">';
        foreach ($allMonsters as $m) {
          $out .= '<option value="'.$m->id.'">'.$m->title.'</option>';
        }
        $out .= '</select>';
        $out .= '</p>';
        $out .= '<input type="hidden" id="submitFormUrl" value="'.$pages->get('name=submitforms')->url.'" />';
        break;
      case 'showInfo' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->photo) { $mini = '<img src="'.$p->photo->eq(0)->getCrop('big')->url.'" alt="'.$p->title.'." />'; }
        if ($p->image) { $mini = '<img src="'.$p->image->getCrop('thumbnail')->url.'" alt="'.$p->title.'." />'; }
        $out .= '<h3><span class="label label-primary">'.$p->title.'</span>';
        if ($p->is("template=place")) {
          $out .= ' (in '.$p->city->title.', '.$p->country->title.')</h3>';
        } else if ($p->is("template=people")) {
          $out .= ' (from '.$p->country->title.')</h3>';
        } else if ($p->is("template=exercise")) {
          $topics = $p->topic->implode(', ', '{title}');
          $out .= ' ('.$topics.')</h3>';
        } else if ($p->is("template=equipment|item")) {
          $out .= ' ('.$p->category->title.')';
        } else {
          $out .= 'TODO';
        }
        $out .= '<div class="row">';
        $out .= '<div class="col-sm-4 text-center">';
          $out .= '<h3 class="thumbnail">';
          $out .= $mini;
          $out .= '</h3>';
        $out .= '</div>';
        $out .= '<div class="col-sm-8 text-justify">';
          $out .= '<br/>';
          $out .= '<p class="lead">'.$p->summary.'</p>';
          if ($p->is("template=place")) {
            $out .= '<p><a href="'.$p->url.'" target="_blank">'.__("[See details and map]").'</a></p>';
          }
          if ($p->is("template=exercise")) {
            $out .= '<p>';
            $out .= __('Most trained player').' → ';
            if ($p->bestTrainedPlayerId != 0) {
              $bestTrained = $pages->get($p->bestTrainedPlayerId);
              bd($p->bestTrainedPlayerId);
              $out .= '<span class="label label-primary">'.$p->best.' '.__('UT').' - '.$bestTrained->title.' ['.$bestTrained->team->title.']</span>';
            } else {
              $out .= '-';
            }
            $out .= '</p>';
            $out .= '<p>';
            $out .= __('Master time').' → ';
            $out .= '<span class="label label-primary">';
            if ($p->bestTime) {
              $master = $pages->get($p->bestTimePlayerId);
              $out .= ms2string($p->bestTime).' '.__('by').' '.$master->title.' ['.$master->team->title.']';
            } else {
              $out .= '-';
            }
            $out .= '</span>';
            $out .= '</p>';
          }
        $out .= '</div>';
        $out .= '</div>';
        break;
      case 'buy' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->photo) { $mini = '<img src="'.$p->photo->eq(0)->getCrop('big')->url.'" alt="'.$p->title.'." />'; }
        if ($p->image) { $mini = '<img src="'.$p->image->getCrop('thumbnail')->url.'" alt="'.$p->title.'." />'; }
        $out .= '<h3><span class="label label-primary">'.$p->title.'</span>';
        if ($p->is("template=place")) {
          $out .= ' ('.__("in").' '.$p->city->title.', '.$p->country->title.')</h3>';
        } else if ($p->is("template=people")) {
          $out .= ' ('.__("from").' '.$p->country->title.')</h3>';
        } else {
          $out .= ' ('.$p->category->title.')</h3>';
        }
        $out .= '<div class="row">';
        $out .= '<div class="col-sm-4 text-center">';
          $out .= '<div class="">';
            $out .= $mini;
            if ($p->is("template=place|people")) {
              if ($currentPlayer->team->is("name!=no-team")) {
                // Find element's # of owners
                $out .= '<div class="alert alert-info">';
                $p = setOwners($p, $currentPlayer);
                $out .= '<span class="">'.__("Free rate").' : ['.$p->owners->count().'/'.$p->teamRate.']</span> ';
                $out .= progressBar($p->owners->count(), $p->teamRate);
                if ($p->completed == 1) { $out .= '<span class="badge">'.__("Congratulations !").'</span>'; }
                $out .= '</div>';
              }
            }
          $out .= '</div>';
        $out .= '</div>';
        $out .= '<div class="col-sm-8 text-justify">';
          $out .= '<br/>';
          $out .= '<p>'.$p->summary.'</p>';
        $out .= '</div>';
        $out .= '</div>';
        $out .= '<br />';
        $out .= '<div class="row">';
          $out .= '<span class="alert alert-info">'.__("This item costs");
          $out .= ' '.$p->GC.__('GC');
          $out .= ' ('.sprintf(__("You have %d GC"), $currentPlayer->GC).')</span>';
        $out .= '</div>';
        if (!isset($currentPlayer->group->id) && $p->is("template=item") && $p->category->name == 'group-items') {
          $out .= '<br /><br />';
          $out .= '<span class="alert alert-warning">'.__("No groups are set. This item will be individual !").'</span>';
        }
        break;
      case 'utreport' : // UT report
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        // From tmpCache if available
        $today = new \DateTime("today");
        $tmpPage = $playerPage->child("name=tmp");
        if ($tmpPage->id && $tmpPage->tmpMonstersActivity) {
          $allConcernedMonsters = $tmpPage->tmpMonstersActivity->find("trainNb>0");
          $allConcernedMonsters->sort("lastTrainDate, date");
          if ($allConcernedMonsters->count() > 0) {
            echo '<p class="label label-success"> '.sprintf(__("You have trained on %d different monsters"), $allConcernedMonsters->count()).'</p>';
            echo '<ul class="utReport list-group list-unstyled">';
            foreach($allConcernedMonsters as $m) {
              setMonster($playerPage, $m->monster);
              // Find # of days compared to today
              $date2 = new \DateTime(date("Y-m-d", $m->lastTrainDate));
              $interval = $today->diff($date2);
              $m->lastTrainingInterval = $interval->days;
              $date3 = new \DateTime(date("Y-m-d", $m->date));
              $availableInterval = $today->diff($date3);
              echo '<li>';
              echo '<span data-toggle="tooltip" title="'.$m->monster->summary.'" onmouseenter="$(this).tooltip(\'show\');" data-html="true">';
              if ($m->monster->isTrainable == 1) {
                echo '<a href="'.$m->monster->url.'train">'.$m->monster->title.'</a>';
              } else {
                echo $m->monster->title;
              }
              echo '</span> : ';
              echo '<span>'.$m->monster->utGain.'UT</span>';
              if ($m->monster->isTrainable == 1) {
                echo ' <span>[Last training : '.$m->monster->lastTrainingInterval.']</span>';
              } else {
                if ($m->monster->waitForTrain == 1) { $label = __('Available tomorrow !'); } else { $label = sprintf(__("Available in %d days"), $m->monster->waitForTrain); }
                echo ' <span data-toggle="tooltip" title="'.$label.'" onmouseenter="$(this).tooltip(\'show\');">[Last training : '.$m->monster->lastTrainingInterval.']</span>';
              }
              echo '</li>';
            }
            if ($tmpPage->index > 0) {
              echo '<li class="label label-danger">'.sprintf(__("You have NEVER trained on %d monsters"), $tmpPage->index).'</li>';
            } else {
              echo '<li class="label label-success">'.__("You have trained on ALL monsters !").'</li>';
            }
            echo '</ul>';
          } else {
            echo "<p>".__("You have never used the Memory Helmet.")."</p>";
          }
        }
        break;
      case 'fightreport' : // Fights report
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        $tmpPage = $playerPage->child("name=tmp");
        if ($tmpPage->id && $tmpPage->tmpMonstersActivity) {
          $allConcernedMonsters = $tmpPage->tmpMonstersActivity->find("fightNb>0")->sort("lastFightDate");
          if ($allConcernedMonsters->count() > 0) {
            echo '<p class="label label-success"> '.sprintf(__("You have fougth %d different monsters"), $allConcernedMonsters->count()).'</p>';
            echo '<ul class="utReport list-group list-unstyled">';
            foreach($allConcernedMonsters as $m) {
              setMonster($playerPage, $m->monster);
              echo '<li>';
              echo '<span data-toggle="tooltip" title="'.$m->monster->summary.'" onmouseenter="$(this).tooltip(\'show\');" data-html="true">';
              if ($m->monster->isFightable == 1) {
                echo '<a href="'.$m->monster->url.'fight">'.$m->monster->title.'</a>';
              } else {
                echo $m->monster->title;
              }
              echo '</span> : ';
              echo '<span data-toggle="tooltip" title="Quality : '.$m->monster->quality.'" onmouseenter="$(this).tooltip(\'show\');" data-html="true"> '.averageLabel($m->monster->quality).'</span>';
              echo ' → <span>'.sprintf(_n("%d fight", "%d fights", $m->monster->fightNb), $m->monster->fightNb);
              echo '  ['.__("Last fight").' : '.$m->monster->lastFightInterval.']</span>';
              echo '</li>';
            }
            echo '</ul>';
          } else {
            echo "<p>".__("You have never fought any monsters. You need +20UT on a monster to see it in the Fighting Zone.")."</p>";
          }
        }
        break;
      case 'battlereport' : // Battle report
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        $allBattles = battleReport($playerPage);
        $attacks = array();
        foreach ($allBattles as $p) { // Group by linkedId
          if ($p->linkedId != '') {
            $attacks["$p->linkedId"][] = $p;
          } else { // Make a unique id
            $uniqueId = mt_rand(100000, 999999); // Unique test number
            $attacks["$uniqueId"][] = $p;
          }
        }
        if (count($attacks) > 0) {
          echo '<p class="label label-primary">'.sprintf(_n('You have faced %d monster attack during the current school year.', 'You have faced %d monster attacks during the current school year.', count($attacks)), count($attacks)).'</p>';
          echo '<ul class="utReport list-group list-unstyled">';
          foreach($attacks as $key => $gr){
            $count = count($gr);
            if($count > 1){
              $pos = 0;
              $neg = 0;
              $vv = 0;
              $v = 0;
              $r = 0;
              $rr = 0;
              echo '<li>';
              foreach($gr as $key => $m){
                switch($m->task->name) {
                  case 'battle-vv': $vv++; $pos++; break;
                  case 'battle-v': $v++; $pos++; break;
                  case 'battle-r': $r++; $neg++; break;
                  case 'battle-rr': $rr++; $neg++; break;
                  default: break;
                }
              }
              if ($vv != 0) echo '<span class="label label-success">'.$vv.'VV</span>';
              if ($v != 0) echo ' <span class="label label-success">'.$v.'V</span>';
              if ($r != 0) echo ' <span class="label label-danger">'.$r.'R</span>';
              if ($rr != 0) echo ' <span class="label label-danger">'.$rr.'RR</span>';
              $testTitle = $gr[0]->summary;
              preg_match('/\[(.*?)\]/', $gr[0]->summary, $matches);
              echo ' : '.$matches[1].' ['.strftime("%d/%m", $gr[0]->date).']';
              echo "</li>";
            } else if ($count) {
              foreach($gr as $key => $m){
                echo '<li>';
                echo $m->result.' : '.$m->summary;
                echo ' ['.strftime("%d/%m", $m->date).']';
                echo "</li>";
              }
            }
            echo "</li>";
          }
          echo '</ul>';
        } else {
          echo "<p>".__("You haven't faced any monster attacks during the current school year.")."</p>";
        }
        break;
      case 'history' :
        $playerId = $input->get('playerId');
        $playerPage = $pages->get($playerId);
        $headTeacher = getHeadTeacher($playerPage);
        if ($input->get->limit == 30) {
          $limitDate  = new \DateTime("-30 days");
          $limitDate = strtotime($limitDate->format('Y-m-d'));
          $allEvents = $playerPage->child("name=history")->find("template=event, date>$limitDate, sort=-date");
        } else {
          $allEvents = $playerPage->child("name=history")->find("template=event, sort=-date");
        }
        $allCategories = new PageArray();
        foreach ($allEvents as $e) {
          if (isset($e->task->category)) {
            $allCategories->add($e->task->category);
            $allCategories->sort("title");
          }
        }
        $out .= '<div id="Filters" data-fcolindex="2" class="text-center">';
        $out .= ' <ul class="list-inline well">';
        foreach ($allCategories as $c) {
          $out .= '<li><label for="'.$c->name.'" class="btn btn-primary btn-xs">'.$c->title.' <input type="checkbox" value="'.$c->title.'" class="categoryFilter" name="categoryFilter" id="'.$c->name.'"></label></li>';
        }
        $out .= '</ul>';
        $out .= '</div>';
        $out .= ' <table id="historyTable" class="table table-condensed table-hover">';
        $out .= '  <thead>';
        $out .= '    <tr>';
        $out .= '    <th>Date</th>';
        $out .= '    <th>+/-</th>';
        $out .= '    <th>Category</th>';
        $out .= '    <th>Title</th>';
        $out .= '    <th>Comment</th>';
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $out .= '    <th></th>';
        }
        $out .= '  </tr>';
        $out .= '  </thead>';
        $out .= '  <tbody>';
        foreach($allEvents as $event) {
          $event->task = checkModTask($event->task, $headTeacher);
          if ($event->task->XP > 0 || ($event->task->is("name=free|buy|positive-collective-alchemy"))) {
            $class = '+';
          } else {
            $class = '-';
          }
          $out .= '<tr>';
          $out .= '<td data-order='.$event->date.'>';
          if ($event->date != '') {
            $out .= strftime("%d/%m", $event->date);
          } else {
            $out .= 'Date error!';
          }
          $out .= '</td>';
          $out .= '<td>';
          $out .= $class;
          $out .= '</td>';
          $out .= '<td>';
          $out .= $event->task->category->title;
          $out .= '</td>';
          $out .= '<td>';
            if ($user->isSuperuser() || $user->hasRole('teacher')) {
              $out .= $event->task->title; // Depending on teacher's variation
              $out .= ' ['.$event->title; // Event title
              if ($event->task->teacherTitle != '') {
                $out .= ' = '.$event->task->teacherTitle.']';
              } else {
                $out .= ']';
              }
            } else {
              $out .= $event->task->title;
              /* $out .= ' ['.$event->title.']'; // Event title */
            }
            if ($event->inClass == 1 && $event->task->is("name~=fight|ut-action")) {
              $out .= ' [in class]';
            }
          $out .= '</td>';
          $event->summary == '' ? $event->summary = '-' : '';
          $out .= '<td>'.$event->summary.'</td>';
          if ($user->isSuperuser()) {
            $out .= '<td>'.$event->feel().'</td>';
          }
          if ($user->hasRole('teacher')) {
            $out .= '<td><a href="'.$config->urls->admin.'page/edit/?id='.$event->id.'" target="blank">'.__("[Edit]").'</a></td>';
          }
          $out .= '</tr>';
        }
        $out .= ' </tbody>';
        $out .= '</table>';
        break;
      case 'last10' :
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        $headTeacher = getHeadTeacher($playerPage);
        $allEvents = $playerPage->child("name=history")->find("template=event,sort=-date, limit=10");
        $out = '';
        $out .= '<ul class="list-unstyled">';
          if ($allEvents->count() > 0) {
            foreach ($allEvents as $event) {
              $event->task = checkModTask($event->task, $headTeacher);
              if ($event->task->HP < 0) {
                $className = 'negative';
                $sign = '';
                $signicon = '<span class="glyphicon glyphicon-minus-sign"></span> ';
              } else {
                $className = 'positive';
                //$className = '';
                $sign = '+';
                $signicon = '<span class="glyphicon glyphicon-plus-sign"></span> ';
              }
              $out .= '<li class="'.$className.'">';
              $out .= $signicon;
              $out .=strftime("%d %b (%A)", $event->date).' : ';
              if ($className == 'negative') {
                $out .= '<span data-toggle="tooltip" title="HP" class="badge badge-warning">'.$sign.$event->task->HP.'HP</span> ';
              }
              $out .= $sanitizer->markupToText($event->task->title);
              if ($event->summary != '') {
                $out .= ' ['.$sanitizer->markupToText($event->summary).']';
              }
              $out .= '</li>';
            };
          } else {
            $out .= __('No personal history yet...');
          }
        $out .= '</ul>';
        break;
      case 'work-statistics' :
        $playerId = $input->get("playerId");
        $player = $pages->get("id=$playerId");
        $periodId = $input->get("periodId");
        $officialPeriod= $pages->get("id=$periodId");
        // Limit to official period dates
        $dateStart = $officialPeriod->dateStart;
        $dateEnd = $officialPeriod->dateEnd;
        $allEvents = $player->child("name=history")->find("template=event, date>=$dateStart, date<=$dateEnd, sort=-date");
        // Participation
        $out = '';
        setParticipation($player);
        $out .= '<p>';
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Compétence SACoche : Je participe en classe." onmouseenter="$(this).tooltip(\'show\');"></span> '.__('Communication').' ';
        $out .= ' ⇒ ';
        switch ($player->participation) {
          case 'NN' : $class='primary';
            break;
          case 'VV' : $class='success';
            break;
          case 'V' : $class='success';
            break;
          case 'R' : $class='danger';
            break;
          case 'RR' : $class='danger';
            break;
          default: $class = '';
        }
        $out .=  '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="<span class=\'label label-success\'>VV</span> <span class=\'label label-success\'>V</span> <span class=\'label label-danger\'>R</span> <span class=\'label label-danger\'>RR</span> See report below." class="label label-'.$class.'">'.$player->participation.'</span>';
        if ($player->partRatio != '-') {
          $out .= ' <span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Participation positive">'.$player->partPositive.' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Participation négative">'.$player->partNegative.' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
        }
        // Homework stats
        setHomework($player, $officialPeriod->dateStart, $officialPeriod->dateEnd);
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Exercices non faits ou à moitié faits<br />Compétence SACoche : Je peux présenter mon travail fait à la maison."></span> Training problems :';
        $out .= ' <span class="">'.$player->hkPb.'</span>';
        $out .= ' [<span>'.$player->noHk->count().' Hk</span> - <span>'.$player->halfHk->count().' HalfHk</span> - <span>'.$player->notSigned->count().' notSigned</span>]';
        $out .= ' ⇒ ';
        switch ($player->homework) {
          case 'NN' : $class='primary'; break;
          case 'VV' : $class='success'; break;
          case 'V' : $class='success'; break;
          case 'R' : $class='danger'; break;
          case 'RR' : $class='danger'; break;
          default: $class = 'primary';
        }
        $out .=  '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="<span class=\'label label-success\'>VV</span> 0/0.5 problems<br /><span class=\'label label-success\'>V</span> 1/1.5 problems<br /><span class=\'label label-danger\'>R</span> 2/2.5 problems<br /><span class=\'label label-danger\'>RR</span> 3/+ problems" class="label label-'.$class.'">'.$player->homework.'</span> ';
        // Forgotten material
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Affaires oubliées<br />Compétence SACoche : J\'ai mon matériel."></span> '.__('Forgotten material').' : ';
        $out .= '<span>'.$player->noMaterial->count().'</span>';
        $out .= ' ⇒ ';
        switch ($player->materialLabel) {
          case 'NN' : $class='primary'; break;
          case 'VV' : $class='success'; break;
          case 'V' : $class='success'; break;
          case 'R' : $class='danger'; break;
          case 'RR' : $class='danger'; break;
          default: $class = 'primary';
        }
        $out .=  '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="<span class=\'label label-success\'>VV</span> 0 oublis<br /><span class=\'label label-success\'>V</span> 1 oubli<br /><span class=\'label label-danger\'>R</span> 2 oublis<br /><span class=\'label label-danger\'>RR</span> 3 oublis (ou +)" class="label label-'.$class.'">'.$player->materialLabel.'</span>';
        $out .= '</p>';
        // Extra-hk
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Travail supplémentaire HORS-CLASSE : extra-homework, personal initiative, underground training...<br />Compétence SACoche : Je prend une initiative particulière."></span> Personal motivation :';
        $out .= ' <span> ['.sprintf(__('%d extra'), $player->extraHk->count()).' - </span>';
        $out .= ' <span>'.sprintf(__('%d initiative'), $player->initiative->count()).' - </span>';
        $out .= ' <span>'.sprintf(__('%d UT/FP sessions'), $player->outClassActivity).']</span>';
        $out .= ' ⇒ ';
        $out .= '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="<span class=\'label label-success\'>VV</span> 9xtHk/init. AND 47→49 UT/FP<br /><span class=\'label label-success\'>VV</span> 10xtHk/init. OR 50→+ UT/FP<br /><span class=\'label label-success\'>V</span> 4xtHk/init. AND 18→19 UT/FP<br /><span class=\'label label-success\'>V</span> 5xtHK/init. OR 20→49 UT/FP" class="label label-'.$class.'">'.$player->motivation.'</span> ';
        $out .= '</p>';
        
        // Attitude
        $disobedience = $allEvents->find("task.name=civil-disobedience");
        $ambush = $allEvents->find("task.name=ambush");
        $noisy = $allEvents->find("task.name=noisy-mission");
        $late = $allEvents->find("task.name=late");
        $pb = new PageArray();
        $pb->add($disobedience);
        $pb->add($ambush);
        $pb->add($noisy);
        $pb->add($late);
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Soucis avec l\'attitude<br />Compétences SACoche : respect / attention en classe..."></span> '.__("Attitude problems").' :';
        $attPb = $disobedience->count()+$ambush->count()+$noisy->count();
        $out .= ' <span>['.sprintf(__('%d problems'), $attPb).' - </span>';
        $out .= ' <span>'.sprintf(__('%d slow moves'), $late->count()).']</span>';
        $out .= ' ⇒ ';
        if ($pb->count() == 0) {
          $out .= '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="<span class=\'label label-success\'>VV</span> 0 problems<br /><span class=\'label label-success\'>V</span> <span class=\'label label-danger\'>R</span> <span class=\'label label-danger\'>RR</span> Ask your teacher" class="label label-success">VV</span>';
        } else {
          $out .= '<span>'.__("Ask your teacher.").'</span>';
        }
        $out .= '</p>';
        break;
      case 'unPublish' : // Unpublish announcement
        $announcementId = $input->get("announcementId");
        $playerId = $input->get("playerId");
        if ($announcementId != '' && $playerId != '') {
          $announcement = $pages->get($announcementId);
          $player = $pages->get($playerId);
          if ($announcement->selectPlayers == 1) { // Untick player
            $announcement->playersList->remove($player);
            if ($announcement->playersList->count() == 0) { // No more ticked players, unpublish
              $announcement->publish = 0;
            }
          } else { // Team announcement, make it individual
            $announcement->selectPlayers = 1;
            $teamPlayers = $pages->find("template=player, team=$player->team")->not($player);
            foreach ($teamPlayers as $p) {
              $announcement->playersList->add($p);
            }
          }
          $announcement->of(false);
          $announcement->save();
        }
        break;
      case 'checkHighscore' :
        $monsterId = $input->get("monsterId");
        $confirm = $input->get("confirm");
        $m = $pages->get($monsterId);
        $testPlayer = $pages->get("template=player, name=test");
        $allUt = $pages->findMany("template=event, task.name~=ut-action, refPage=$m, has_parent!=$testPlayer");
        $newBest = [];
        foreach($allUt as $e) {
          $utGain = 1;
          $pId = $e->parent("template=player")->id;
          if (!isset($newBest[$pId])) {
            $newBest[$pId] = 0;
          }
          // Test also french field
          if ($e->summary != '') {
            preg_match("/\[\+([\d]+)U\.T\.\]/", $e->summary, $matches);
          } else {
            $e->of(false);
            preg_match("/\[\+([\d]+)U\.T\.\]/", $e->summary->getLanguageValue($french), $matches);
          }
          if ($matches) {
            $utGain = $matches[1];
          }
          $newBest[$pId] += $utGain;
        }
        if (count($newBest) > 0) {
          $newBestUt = max($newBest);
          $newBestId = array_search(max($newBest),$newBest);
          $newBestPlayer = $pages->get($newBestId);
        } else {
          $newBestPlayer = false;
          $newBestId = 0;
        }
        if (!$confirm) {
          if (($newBestId == $m->bestTimePlayerId && $newBestUt == $m->best) || ($m->bestTrainedPlayerId == 0 && $newBestId != 0)) {
            $out = '&nbsp;<span class="label label-success">OK</span>';
          } else {
            $out = '&nbsp;<span class="label label-danger">Error</span> : '.$newBestPlayer->title.' → '.$newBestUt.'UT';
            $options = [
              'data' => [
                'id' => 'checkHighscore',
                'monsterId' => $monsterId,
                'confirm' => 'true'
              ]
            ];
            $out .= ' <button class="simpleAjax btn btn-xs btn-danger" data-href="'.$page->url($options).'" data-disable="true">Save ?</button> <span class="glyphicon glyphicon-alert"></span> ';
          }
        } else { // Saving new highscore
          $m->of(false);
          $m->bestTrainedPlayerId = $newBestId;
          $m->best = $newBestUt;
          $m->save();
          $out = '<span class="label label-success">✓ Saved !</span>';
          $out .= 'New highscore : '.$newBestUt.'UT for '.$newBestPlayer->title;
        }
        break;
      default :
        $out = __("todo");
    }
    echo $out;
  }
?>
