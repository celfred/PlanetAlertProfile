<?php namespace ProcessWire;
  if ($config->ajax) {
    $out = '';
    switch ($input->get('id')) {
      case 'lastEvents' :
        // Last 3 published monsters
        $out .= '<ul class="list-inline">&nbsp;';
        $out .= '<li class="label label-success"><span class="glyphicon glyphicon-headphones"></span> New monsters !</li>';
        $lastMonsters = $pages->find("template=exercise, sort=-published, limit=3");
        foreach($lastMonsters as $m) {
          if ($m->image) {
            $mini = "<img data-toggle='tooltip' src='".$m->image->getCrop('mini')->url."' alt='image' />";
          } else {
            $mini = '';
          }
          $out .= '  <li data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$m->summary.'">'.$mini.' '.$m->title.'</li>  ';
        }
        if ($user->isLoggedin()) {
          if ($user->isSuperuser() == false) {
            $currentPlayer = $pages->get("template=player, login=$user->name");
            $helmet = $currentPlayer->equipment->get("name=memory-helmet");
          }
          if (isset($helmet) || $user->isSuperuser()) {
            $out .= '<li>→ <a href="'.$pages->get("name=underground-training")->url.'">Go to the Underground Training Zone !</a></li>';
          } else {
            $out .= '<li>→ You need to buy the Memory Helmet to fight monsters !</a></li>';
          }
        }
        $out .= '</ul>';
        // Last admin announcements
        if ($user->isLoggedin()) {
          if ($user->isSuperuser()) {
            // Admin gets all news
            $newsAdmin = $pages->get("/newsboard")->children("publish=0, limit=5")->sort("-date");
          } else {
            // User gets public and ranked news
            $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=0|1, ranks=''|$currentPlayer->rank, limit=5")->sort("-date");
          }
        } else { // Guests get public news only
          $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=1, limit=5")->sort("-date");
        }
        $out .= '<p>';
        $out .= '<span class="label label-success"><span class="glyphicon glyphicon-hand-up"></span> Last official announcements !</span>';
        $out .= '<ul class="">';
        $blogUrl = $pages->get("name=blog")->url;
        foreach($newsAdmin as $n) {
          $out .= '<li>'.date("M. d, Y", $n->created).' : <a href="'.$blogUrl.'">'.$n->title.'</a></li>';
        }
        $out .= '</ul>';
        $out .= '</p>';
        // Last public news
        $excluded = $pages->find("template=player, name=test");
        // Find current school year date
        $schoolYear = $pages->get("template=period, name=school-year");
        // Find last events
        $news = $pages->find("template=event, date>=$schoolYear->dateStart, sort=-date, limit=20, task.name=free|buy|ut-action-v|ut-action-vv, has_parent!=$excluded");
        if ($news->count() > 0) {
          $out .= '<h4 class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> New public activity !</h4>';
          $out .= '<ul class="list-unstyled">';
          foreach($news as $n) {
            $currentPlayer = $n->parent('template=player');
            if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
            if ($currentPlayer->avatar) {
              $thumb = $currentPlayer->avatar->size(20,20);
              $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$thumb->url."' alt='avatar' />";
            } else {
              $mini = '';
            }
            $out .= '<li>';
            $out .= $mini;
            $out .= date("M. j (D)", $n->date).' : ';
            $out .= '<span>';
            switch ($n->task->category->name) {
            case 'place' : 
              if ($n->refPage->template == 'place') {
                $out .= '<span class="">New place for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
              }
              if ($n->refPage->template == 'people') {
                $out .= '<span class="">New people for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
              }
              break;
            case 'shop' : $out .= '<span class="">New equipment for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
              break;
            case 'attitude' : $out .= '<span class="">Generous attitude from <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.'] : '.html_entity_decode($n->summary).'</span>';
            case 'individual-work' : $out .= '<span class="">Underground Training for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
              break;
            default : $out .= 'todo : ';
              break;
            }
            //$out .= $n->task->title. ' : ' . $n->summary;
            $out .= '</span>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          echo '<h4 class="well">No public news... :(</h4>';
        }
        break;
      case 'admin-work' :
        $news = $pages->find("template=event, sort=-created, publish=1, task=free|buy|penalty");
        $out .= '<div class="col-sm-6">';
        if ($news->count() > 0) {
          $out .= '<p class="label label-primary">Papers to be given</p>';
          $out .= '<ul class="list-unstyled">';
          foreach($news as $n) {
            $currentPlayer = $n->parent('template=player');
            if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
            $out .= '<li class="">';
            $out .= date("M. j (D)", $n->date).' : ';
            $out .= '<span>';

            switch ($n->task->category->name) {
              case 'place' :
                if ($n->refPage->template == 'place') {
                  $out .= '<span class="">New place for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  $out .= '<span class="">New people for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                break;
              case 'shop' : $out .= '<span class="">New equipment for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'attitude' : $out .= '<span class="">Generous attitude from <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'homework' : $out .= '<span class="">Penalty for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              default : $out .= 'todo : ';
                break;
            }
            $out .= '</span>';
            $out .= ' <label for="unpublish_'.$n->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> Unpublish<span id="feedback"></span></label>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>Nothing to do.</p>';
        }
        $out .= '</div>';
        $unusedConcerned = $pages->find("template=player, usabledItems.count>0")->sort("-team.name, name");
        $out .= '<div class="col-sm-6">';
        if ($unusedConcerned->count > 0) {
          $date1 = new \DateTime("today");
          $out .= '<p class="label label-primary">Potion Planner</p>';
          $out .= '<ul class="list-unstyled">';
          foreach ($unusedConcerned as $p) {
            foreach ($p->usabledItems as $item) {
              $historyPage = $p->get("name=history")->find("refPage=$item, linkedId=0")->last();
              if ($historyPage) {
                $out .= '<li class="">';
                // Find # of days compared to today
                $date2 = new \DateTime(date("Y-m-d", $historyPage->date));
                $interval = $date1->diff($date2);
                if ($interval->days > 21) {
                  $out .= ' <span class="badge">!</span> ';
                }
                $out .= '<span>'.$p->title.' ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)</span>';
                $out .= ' <label for="unpublish_'.$historyPage->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> Used today<span id="feedback"></span></label>';
                $out .= '</li>';
              }
            }
          }
          $out .= '</ul>';
        } else {
          $out .= '<hr /><p class="">No Potion to be used.</p>';
        }
        $out .= '</div>';
        break;
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
            if ($p->team->rank && $p->team->rank->is('name=4emes|3emes')) { // Add people ONLY for 4emes/3emes
              $possibleItems->add($possiblePeople);
            }
            $possibleItems->add($possibleEquipment);
            $possibleItems->add($possiblePotions);
          }
          $donatorId = $p->id;
          if ($p->avatar) { $mini = '<img src="'.$p->avatar->getCrop('thumbnail')->url.'" alt="avatar" />'; }
          $out .= '<div class="row">';
          $out .= '<div class="col-sm-6 text-center">';
          $out .= '<h3 class="thumbnail">'.$mini.' <span class="caption">'.$p->title.'</span></h3>';
          $out .= '</div>';
          $out .= '<div class="col-sm-6 text-left">';
          $out .= '<ul class="list-unstyled">';
          if ($p->coma == 0) {
            $out .= '<li><span class="label label-success">Karma : '.$p->yearlyKarma.'</span></li>';
            $out .= '<li><span class="label label-success">Reputation : '.$p->karma.'</span></li>';
            $out .= '<li><span class="label label-default"><span class="glyphicon glyphicon-signal"></span> '.$p->level.'</span>';
            $threshold = getLevelThreshold($p->level);
            $out .= ' <span class="label label-default"><img src="'.$config->urls->templates.'img/star.png" alt="" /> '.$p->XP.'/'.$threshold.'</span></li>';
            $nbFreeEl = $p->places->count();
            if ($p->team->rank && $p->team->rank->is('name=4emes|3emes')) {
              $nbFreeEl += $p->people->count();
            }
            $out .= '<li><span class="label label-default"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> '.$nbFreeEl.'</span>';
            $out .= ' <span class="label label-default"><span class="glyphicon glyphicon-wrench"></span> '.$p->equipment->count().'</span></li>';
            $out .= '<li><span class="label label-default">'.$p->underground_training.' UT</span>';
            $out .= ' <span class="label label-default">'.$p->fighting_power.' FP</span>';
            $out .= '</li>';
            $out .= '<li><span class="label label-default"><img src="'.$config->urls->templates.'img/heart.png" alt="" /> '.$p->HP.'</span>';
            $out .= ' <span class="label label-default"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /> '.$p->GC.'</span></li>';
          } else {
            $out .= '<li class="label label-danger">Coma !</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
          $out .= '</div>';
        }
        if ($p->is("parent.name=groups")) {
          // Get group members
          $team = $input->get('teamId');
          $groupPlayers = $pages->find("template=player, team=$team, group=$pageId");
          $out .= '<div class="row">';
            $out .= '<p class="text-center"><span class="label label-primary">'.$p->title.'</span></p>';
            $out .= '<ul class="list-unstyled list-inline text-left">';
            $donatorId = $groupPlayers->sort('-GC')->first()->id;
            $groupPlayers->sort('-karma');
            foreach($groupPlayers as $gp) {
              $nbFreeEl = $gp->places->count();
              if ($gp->team->rank && $gp->team->rank->is('name=4emes|3emes')) {
                $nbFreeEl += $gp->people->count();
              }
              if ($gp->avatar) { $mini = '<img src="'.$gp->avatar->getCrop('thumbnail')->url.'" alt="avatar" width="50" />'; }
            $out .= '<li>';
            $out .= $mini;
            $out .= '<span>';
            $out .= $gp->title;
            if ($gp->coma == 0) {
              $out .= ' <span class="badge">'.$gp->karma.'K.</span>';
              $out .= ' <span class="badge"><span class="glyphicon glyphicon-wrench"></span>'.$gp->equipment->count().'</span>';
              $out .= ' <span class="badge"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> '.$nbFreeEl.'</span>';
              $out .= ' <span class="badge">'.$gp->HP.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
              $out .= ' <span class="badge">'.$gp->GC.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /></span>';
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
        $out .= '<ul class="text-left list-unstyled">';
        // Organize team defense
        $nbConcerned = possibleDefense($p->team);
        if ($nbConcerned > 0) {
          $out .= '<li><span><a href="'.$pages->get("name=quiz")->url.$p->team->name.'">→ Organize team defense ('.$nbConcerned.' players concerned).</a></span></li>';
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
        /* if ($p->team->rank && $p->team->rank->is('name=4emes|3emes')) { */
        /*   $task = $pages->get("name=personal-initiative"); */
        /*   $out .= '<li><span><a href="#" class="ajaxBtn" data-type="initiative" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask&playerId='.$p->id.'&taskId='.$task->id.'">→ Talk about [...] for 2 minutes.</a> [Personal initiative]</span></li>'; */
        /* } */
        // Special discount
        if ($p->is("parent.name!=groups")) {
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
              $out .= '<li>';
                $out .= ' <span><a href="#" class="buyBtn" data-url="'.$pages->get('name=submitforms')->url.'?form=buyForm&playerId='.$p->id.'&itemId='.$selectedItem->id.'&discount='.$discount->id.'" data-GC="'.$p->GC.'" data-item-price="'.$selectedItem->GC.'">→ Get <span class="label label-danger">'.$discount->title.'%</span> discount on <span class="label label-primary">'.$selectedItem->title.'</span> '.$details.' !</a></span>';
              $out .= '<div class="row">';
                $out .= '<div class="col-sm-6 text-right">';
                  $out .= '<h4><span class="strikeText">'.$selectedItem->GC.'GC</span> → <span class="label label-success">'.$newPrice.'</span></h4>';
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
              /* $out .= '<li><span class="strikeText">→ No special offer today...</span></li>'; */
            }
          } else {
            /* $out .= '<li><span class="strikeText">→ No possible item (not enough GC ?).</span></li>'; */
          }
        }
        // Make a donation
        if ($p->GC > 5 || $p->is("parent.name=groups")) {
          $out .= '<li><span><a href="'.$pages->get("name=makedonation")->url.$p->team->name.'/'.$donatorId.'">→ Make a donation (help another player).</a></span></li>';
        } else {
          /* $out .= '<li><span class="strikeText">→ Not enough GC to make a donation.</span></li>'; */
        }
        // Choose another player
        $out .= '<li><span><a href="#" onclick="swal.close();">→ Choose another player.</a></span></li>';
        $out .= '</ul>';
        $out .= '</div>';
        break;
      case 'ambassador' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->avatar) { $mini = '<img src="'.$p->avatar->getCrop('thumbnail')->url.'" alt="avatar" />'; }
        $out .= '<h3 class="thumbnail">'.$mini.' <span class="caption">'.$p->title.'</span></h3>';
        break;
      case 'showInfo' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->photo) { $mini = '<img src="'.$p->photo->eq(0)->getCrop('big')->url.'" alt="Photo" />'; }
        if ($p->image) { $mini = '<img src="'.$p->image->getCrop('thumbnail')->url.'" alt="Photo" />'; }
        $out .= '<h3><span class="label label-primary">'.$p->title.'</span>';
        if ($p->is("template=place")) {
          $out .= ' (in '.$p->city->title.', '.$p->country->title.')</h3>';
        } else if ($p->is("template=people")) {
          $out .= ' (from '.$p->country->title.')</h3>';
        } else {
          $out .= ' ('.$p->category->title.')</h3>';
        }
        $out .= '<div class="row">';
        $out .= '<div class="col-sm-4 text-center">';
          $out .= '<h3 class="thumbnail">';
          $out .= $mini;
          $out .= '</h3>';
        $out .= '</div>';
        $out .= '<div class="col-sm-8 text-justify">';
          $out .= '<br/>';
          $out .= '<p>'.$p->summary.'</p>';
        $out .= '</div>';
        $out .= '</div>';
        break;
      case 'buy' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        $player = $pages->get("template=player, login=$user->name");
        if ($p->photo) { $mini = '<img src="'.$p->photo->eq(0)->getCrop('big')->url.'" alt="Photo" />'; }
        if ($p->image) { $mini = '<img src="'.$p->image->getCrop('thumbnail')->url.'" alt="Photo" />'; }
        $out .= '<h3><span class="label label-primary">'.$p->title.'</span>';
        if ($p->is("template=place")) {
          $out .= ' (in '.$p->city->title.', '.$p->country->title.')</h3>';
        } else if ($p->is("template=people")) {
          $out .= ' (from '.$p->country->title.')</h3>';
        } else {
          $out .= ' ('.$p->category->title.')</h3>';
        }
        $out .= '<div class="row">';
        $out .= '<div class="col-sm-4 text-center">';
          $out .= '<div class="">';
            $out .= $mini;
            if ($p->is("template=place|people")) {
              // Find element's # of owners
              $out .= '<div class="alert alert-info">';
              $p = setOwners($p, $player);
              $out .= '<span class="">Free rate : ['.$p->owners->count().'/'.$p->teamRate.']</span> ';
              $out .= progressbar($p->owners->count(), $p->teamRate);
              if ($p->completed == 1) { $out .= '<span class="badge">Congratulations !</span>'; }
              $out .= '</div>';
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
          $out .= '<span class="alert alert-info">This item costs '.$p->GC.'GC. (You have '.$player->GC.'GC.)</span>';
        $out .= '</div>';
        if (!isset($player->group->id) && $p->is("template=item") && $p->category->name == 'group-items') {
          $out .= '<br /><br />';
          $out .= '<span class="alert alert-warning">No groups are set. This item will be individual !</span>';
        }
        break;
      case 'utreport' : // UT report
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        $utConcernedMonsters = utReport($playerPage);
        $never = $pages->count("template=exercise")-$utConcernedMonsters->count();
        if ($utConcernedMonsters->count() > 0) {
          echo '<p class="label label-primary">You have trained '.$utConcernedMonsters->first()->total.' times.</p>';
          if ($user->isSuperuser() || ($user->isLoggedin() && $user->name == $playerPage->login)) { // Admin is logged or user
            echo '<ul class="utReport list-group list-unstyled">';
            $trainingUrl = $pages->get("name=underground-training")->url.'?id=';
            foreach ($utConcernedMonsters as $m) {
              echo '<li>';
              if ($m->isTrainable == 0) { // Not allowed because of spaced repetition.
                if ($m->waitForTrain == 1) {
                  echo '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Available tomorrow !">'.$m->title.'</span> : <span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" data-html="true" title="'.$m->fightsCount.' training sessions">'.$m->utGain.'UT ';
                } else {
                  echo '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Available in '.$m->waitForTrain.' days">'.$m->title.'</span> : <span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" data-html="true" title="'.$m->fightsCount.' training sessions">'.$m->utGain.'UT ';
                }
              } else {
                echo '<a href="'.$trainingUrl.$m->id.'">'.$m->title.'</a> : <span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" data-html="true" title="'.$m->fightsCount.' training sessions">'.$m->utGain.'UT ';
              }
              echo ' [Last training : '.$m->lastFight.' days ago.]</span>';
              echo '</li>';
            }
            echo '<li class="label label-danger">You have NEVER trained on '.$never.' monsters.</li>';
            echo '</ul>';
          } else {
            echo '<p>Details are private.</p>';
          }
        } else {
          echo "<p>You have never used the Memory Helmet.</p>";
        }
        break;
      case 'fightreport' : // Fights report
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        // Fights report
        $playerConcernedMonsters = fightReport($playerPage);
        if ($playerConcernedMonsters->count() > 0) {
          echo '<p class="label label-primary">You have fought '.$playerConcernedMonsters->count().' monsters.</p>';
          if ($user->isSuperuser() || ($user->isLoggedin() && $user->name == $playerPage->login)) { // Admin is logged or user
            echo '<ul class="utReport list-group list-unstyled">';
            foreach ($playerConcernedMonsters as $m) {
              if ($m->isFightable == 0) {
                if ($m->lastTrainingInterval == 0) {
                  echo '<li><span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Available tomorrow !">'.$m->title.'</span> : '.$m->fightsCount.' fights ';
                } else {
                  echo '<li><span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Available in '.$m->waitForFight.' days">'.$m->title.'</span> : '.$m->fightsCount.' fights ';
                }
              } else {
                echo '<li><a href="'.$m->url.'">'.$m->title.'</a> : '.$m->fightsCount.' fights ';
              }
              echo '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Quality:'.$m->ratio.'<br /><span class=\'glyphicon glyphicon-thumbs-up\'></span>'.$m->positive.' / <span class=\'glyphicon glyphicon-thumbs-down\'></span>'.$m->negative.'">→ '.$m->average.'</span>';
              echo ' [Last fight : '.$m->lastFight.' days ago.]';
              echo '</li>';
            }
            echo '</ul>';
          } else {
            echo '<p>Details are private.</p>';
          }
        } else {
          echo "<p>You haven't fought any monsters yet.</p>";
        }
        break;
      case 'history' :
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        $allEvents = $playerPage->child("name=history")->find("template=event,sort=-date");
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
        $out .= '  </tr>';
        $out .= '  </thead>';
        $out .= '  <tbody>';
        foreach($allEvents as $event) {
          if ($event->task->XP > 0 || ($event->task->category->name === 'place' || $event->task->category->name === 'shop' || $event->task->name === 'positive-collective-alchemy') ) {
            $class = '+';
          } else {
            $class = '-';
          }
          $out .= '<tr>';
          $out .= '<td data-order='.$event->date.'>';
          if ($event->date != '') {
            $out .= date('d/m', $event->date);
          } else {
            $out .= 'Date error!';
          }
          $out .= '</td>';
          $out .= '<td>';
          $out .= $class;
          $out .= '</td>';
          $out .= '<td>';
          $out .= $event->task->category->title;
          if ($event->name == 'freeing') {
            if ($event->refPage->template == 'place') { $out .= 'Place'; }
            if ($event->refPage->template == 'people') { $out .= 'People'; }
          }
          $out .= '</td>';
          if ($user->isSuperuser()) {
            $out .= '<td>'.$event->title.'</td>';
          } else {
            $out .= '<td>'.$event->task->title.'</td>';
          }
          $out .= '<td>'.$event->summary.'</td>';
          $out .= '</tr>';
        }
        $out .= ' </tbody>';
        $out .= '</table>';
        break;
      case 'last10' :
        $playerId = $input->get('playerId');
        $playerPage = $pages->get("id=$playerId");
        $allEvents = $playerPage->child("name=history")->find("template=event,sort=-date, limit=10");
        $out = '';
        $out .= '<ul class="list-unstyled">';
          if ($allEvents->count() > 0) {
            foreach ($allEvents as $event) {
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
              $out .= date("M. j (D)", $event->date).' : ';
              if ($className == 'negative') {
                $out .= '<span data-toggle="tooltip" title="HP" class="badge badge-warning">'.$sign.$event->task->HP.'HP</span> ';
              }
              $out .= $event->task->title;
              $out .= ' ['.$event->summary.']';
              $out .= '</li>';
            };
          } else {
            $out .= 'No personal history yet...';
          }
        $out .= '</ul>';
        break;
      case 'work-statistics' :
        $playerId = $input->get('playerId');
        $player = $pages->get("id=$playerId");
        $allEvents = $player->child("name=history")->find("template=event,sort=-date");
        // Participation
        $out = '';
        setParticipation($player);
        $out .= '<p>';
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Participation en classe" onmouseenter="$(this).tooltip(\'show\');"></span> Communication ';
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
        $out .=  '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : Je participe en classe." class="label label-'.$class.'">'.$player->participation.'</span>';
        if ($player->partRatio != '-') {
          $out .= '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Participation positive">'.$player->partPositive.' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Participation négative">'.$player->partNegative.' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
        }
        // Homework stats
        setHomework($player, $officialPeriod->dateStart, $officialPeriod->dateEnd);
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Exercices non faits ou à moitié faits"></span> Training problems :';
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
        $out .=  '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : Je peux présenter mon travail fait à la maison." class="label label-'.$class.'">'.$player->homework.'</span> ';
        // Forgotten material
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Affaires oubliées"></span> Forgotten material : ';
        $out .= '<span>'.$player->noMaterial->count().'</span>';
        $out .= ' ⇒ ';
        if ($player->noMaterial->count() == 0) {
          $out .=  '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">VV</span>';
        }
        if ($player->noMaterial->count() == 1) {
          $out .=  '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">V</span>';
        }
        if ($player->noMaterial->count() == 2) {
          $out .=  '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">R</span>';
        }
        if ($player->noMaterial->count() > 2) {
          $out .=  '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">RR</span>';
        }
        $out .= '</p>';
        // Extra-hk
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Travail supplémentaire : extra-homework, personal initiative, underground training..."></span> Personal motivation :';
        $out .= ' <span> ['.$player->extraHk->count().' extra - </span>';
        $out .= ' <span>'.$player->initiative->count().' initiatives - </span>';
        $out .= ' <span class="">'.$player->ut->count().' UT session]</span>';
        $out .= ' ⇒ ';
        $out .= '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : Je prend une initiative particulière." class="label label-'.$class.'">'.$player->motivation.'</span> ';
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
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Soucis avec l\'attitude"></span> Attitude problems :';
        $attPb = $disobedience->count()+$ambush->count()+$noisy->count();
        $out .= ' <span> ['.$attPb.' problems - </span>';
        $out .= ' <span>'.$late->count().' slow moves]</span>';
        $out .= ' ⇒ ';
        if ($pb->count() == 0) {
          $out .= '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : J\'adopte une attitude d\'élève." class="label label-success">VV</span>';
        } else {
          $out .= '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="Compétence SACoche : J\'adopte une attitude d\'élève.">Ask your teacher.</span>';
        }
        $out .= '</p>';
        break;
      default :
        $out = 'Todo...';
    }
    echo $out;
  }
?>
