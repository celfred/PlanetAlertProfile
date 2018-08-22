<?php namespace ProcessWire;
  if ($config->ajax) {
    $out = '';
    if (!$user->isSuperuser()) {
      $headTeacher = getHeadTeacher($user);
      $user->language = $headTeacher->language;
    }
    switch ($input->get('id')) {
      case 'lastEvents' : // Public activity
        // TODO : Limit to logged in players contextual news
        $adminId = $users->get("name=admin")->id;
        $lastMonsters = $pages->find("template=exercise, created_users_id=$adminId, sort=-published, limit=3");
        $lastLessons = $pages->find("template=lesson, created_users_id=$adminId, sort=-published, limit=3");
        $lastUpdatedLessons = $pages->find("template=lesson, modified_users_id=$adminId, sort=-modified, limit=3");
        // Last 3 published monsters
        $out .= '<ul class="list-inline">&nbsp;';
        $out .= '<li class="label label-success"><span class="glyphicon glyphicon-headphones"></span> '.__("New monsters !").'</li>';
        foreach($lastMonsters as $m) {
          if ($m->image) {
            $mini = "<img data-toggle='tooltip' src='".$m->image->getCrop('mini')->url."' alt='image' />";
          } else {
            $mini = '';
          }
          $out .= '  <li data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$m->summary.'">'.$mini.' '.$m->title.'</li>  ';
        }
        if ($user->isLoggedin() && $user->hasRole("player")) {
          $currentPlayer = $pages->get("template=player, login=$user->name");
          $helmet = $currentPlayer->equipment->get("name=memory-helmet");
          if (isset($helmet)) {
            $out .= '<li>→ <a href="'.$pages->get("name=underground-training")->url.'">'.__("Go to the Underground Training Zone !").'</a></li>';
          } else {
            $out .= '<li>→ '.__("You need to buy the Memory Helmet to fight monsters !").'</a></li>';
          }
        }
        $out .= '</ul>';
        // Last 3 published lessons
        $out .= '<ul class="list-inline">&nbsp;';
        $out .= '<li class="label label-success"><span class="glyphicon glyphicon-book"></span> '.__("New lessons !").'</li>';
        foreach($lastLessons as $l) {
          $out .= '  <li data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$l->summary.'">'.$l->title.'</li>  ';
        }
        // Last updated lessons
        foreach($lastUpdatedLessons as $l) {
          if ($lastLessons->has($l) == false) {
            $out .= '  <li data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$l->summary.'">'.$l->title.' <span class="badge">'.__("Updated !").'</span></li>  ';
          }
        }
        if ($user->isLoggedin() && $user->hasRole("player")) {
          $currentPlayer = $pages->get("template=player, login=$user->name");
          $book = $currentPlayer->equipment->get("name~=book-knowledge");
          if (isset($book)) {
            $out .= '<li>→ <a href="'.$pages->get("name=book-knowledge")->url.'">'.__("Read my Book of Knowledge").'</a></li>';
          } else {
            $out .= '<li>→ '.__("You need to buy the Book of Knowledge to see the lessons !").'</a></li>';
          }
        }
        $out .= '</ul>';
        // Last admin announcements
        if ($user->isGuest()) { // Guests get public news only
          $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=1, limit=5")->sort("-date");
        } else {
          if ($user->hasRole('teacher') || $user->isSuperuser()) { // Teachers and Admin gets all published news
            $newsAdmin = $pages->get("/newsboard")->children("publish=0, limit=5")->sort("-date");
          }
          if ($user->hasRole('player')) { // Player gets public and ranked news
            if ($currentPlayer->rank) {
              $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=0|1, ranks=''|$currentPlayer->rank, limit=5")->sort("-date");
            } else { // Public news only (no rank)
              $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=1, limit=5")->sort("-date");
            }
          }
        }
        $out .= '<p>';
        $out .= '<span class="label label-success"><span class="glyphicon glyphicon-hand-up"></span> '.__("Last official announcements !").'</span>';
        $out .= '<ul class="">';
        $blogUrl = $pages->get("name=blog")->url;
        foreach($newsAdmin as $n) {
          $out .= '<li>'.strftime("%d %b %Y", $n->created).' : <a href="'.$blogUrl.'">'.$n->title.'</a></li>';
        }
        $out .= '</ul>';
        $out .= '</p>';
        // Recent public news (30 previous days)
        $excluded = $pages->get("template=player, name=test");
        $today = new \DateTime("today");
        $interval = new \DateInterval('P30D');
        $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
        // Find last events
        $news = $pages->find("template=event, parent.name=history, date>=$limitDate, sort=-date, limit=20, task.name=free|buy|ut-action-v|ut-action-vv, has_parent!=$excluded");
        if ($news->count() > 0) {
          $out .= '<h4 class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> '.__("New public activity !").'</h4>';
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
            $out .= '<span>';
            switch ($n->task->name) {
              case 'free' : 
                if ($n->refPage->template == 'place') {
                  $out .= '<span class="">'.__("New place for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  $out .= '<span class="">'.__("New people for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                break;
              case 'buy' :
                $out .= '<span class="">'.__("New equipment for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'ut-action-v' :
              case 'ut-action-vv' :
                $out .= '<span class="">'.__("Underground Training for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'donation' :
                $out .= '<span class="">'.__("Generous attitude from").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.'] : '.html_entity_decode($n->summary).'</span>';
                break;
              default : $out .= __("todo");
            }
            //$out .= $n->task->title. ' : ' . $n->summary;
            $out .= '</span>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          echo '<h4 class="well">'.__("No public news within the last 30 days... :(").'</h4>';
        }
        break;
      case 'admin-work' :
        $allConcernedPlayers = $pages->find("parent.name=players, team.teacher=$user"); // Limit to teacher's students
        $news = $pages->find("parent.name=history, template=event, publish=1");
        $news->filter("has_parent=$allConcernedPlayers, task.name=free|buy|ambush")->sort('-created');
        $out .= '<div class="col-sm-6">';
        if ($news->count() > 0) {
          $out .= '<p class="label label-primary">'.__("Papers to be given").'</p>';
          $out .= '<ul class="list-unstyled">';
          foreach($news as $n) {
            $currentPlayer = $n->parent('template=player');
            if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
            $out .= '<li class="">';
            $out .=strftime("%d %b (%A)", $n->date).' : ';
            $out .= '<span>';
            switch ($n->task->name) {
              case 'free' : 
                if ($n->refPage->template == 'place') {
                  $out .= '<span class="">'.__("New place for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  $out .= '<span class="">'.__("New people for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                break;
              case 'buy' :
                $out .= '<span class="">'.__("New equipment for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'ambush' :
                $out .= '<span class="">'.__("Penalty for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              default : $out .= $n->task->name. ': '.__("todo");
            }
            $out .= '</span>';
            $out .= ' <label for="unpublish_'.$n->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> '.__("Unpublish").'</label>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("Nothing to do.").'</p>';
        }
        $out .= '</div>';
        $unusedConcerned = $allConcernedPlayers->find("usabledItems.count>0")->sort("-team.name, name");
        $out .= '<div class="col-sm-6">';
        if ($unusedConcerned->count > 0) {
          $date1 = new \DateTime("today");
          $out .= '<p class="label label-primary">'.__("Potion Planner").'</p>';
          $out .= '<ul class="list-unstyled">';
          foreach ($unusedConcerned as $p) {
            foreach ($p->usabledItems as $item) {
                $historyPage = $p->get("name=history")->find("refPage=$item")->last();
                if ($historyPage) {
                  $out .= '<li class="">';
                  // Find # of days compared to today
                  $date2 = new \DateTime(date("Y-m-d", $historyPage->date));
                  $interval = $date1->diff($date2);
                  if ($interval->days > 21) {
                    $out .= ' <span class="badge">!</span> ';
                  }
                  if ($historyPage->refPage->is("name!=memory-potion")) {
                    $out .= '<span>'.$p->title.' ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)</span>';
                    $out .= ' <label for="unpublish_'.$historyPage->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> '.__("used today").'</label>';
                  } else {
                    $successId = $pages->get("template=memory-text, id=$historyPage->linkedId")->task->id;
                    $failedId = $pages->get("name=solo-r")->id;
                    $out .= '<span>'.$p->title.' ['.$p->team->title.'] : '.$historyPage->summary.' (bought '.$interval->days.' days ago)</span>';
                    $out .= ' <button class="ajaxBtn btn btn-xs btn-success" data-type="memory" data-result="good" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask&type=memory&playerId='.$p->id.'&historyPageId='.$historyPage->id.'&taskId='.$successId.'"><i class="glyphicon glyphicon-thumbs-up"></i></button>';
                    $out .= ' <button class="ajaxBtn btn btn-xs btn-danger" data-type="memory" data-result="bad" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask&type=memory&playerId='.$p->id.'&historyPageId='.$historyPage->id.'&taskId='.$failedId.'"><i class="glyphicon glyphicon-thumbs-down"></i></button>';
                  }
                  $out .= '</li>';
                }
            }
          }
          $out .= '</ul>';
        } else {
          $out .= '<hr /><p class="">'.__("No Potion to be used.").'</p>';
        }
        $book = $pages->get("name=book-knowledge");
        $pendings = $book->pending;
        $pendings->filter("player=$allConcernedPlayers");
        if (count($pendings) > 0) {
          $date1 = new \DateTime("today");
          $out .= '<p class="label label-primary">Copy work</p>';
          $out .= '<ul class="list-unstyled">';
          foreach ($pendings as $p) {
            $out .= '<li class="">';
            // Find # of days compared to today
            $date2 = new \DateTime(date("Y-m-d", $p->date));
            $interval = $date1->diff($date2);
            if ($interval->days > 21) {
              $out .= ' <span class="badge">!</span> ';
            }
            $out .= '<span>'.$p->player->title.' ['.$p->player->team->title.'] : '.$p->refPage->title.' (warning '.$interval->days.' days ago)</span>';
            $out .= ' <label for="unpublish_'.$p->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$p->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedPending='.$p->id.'" /> validated today</label>';
            $out .= ' <a href="'.$pages->get('name=submitforms')->url.'?form=deleteNotification&usedPending='.$p->id.'" class="del">[Delete]</a>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<hr /><p class="">'.__("No lessons to be validated.").'</p>';
        }
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
            if ($p->team->rank && $p->team->rank->is("index>=8")) { // Add people ONLY for 4emes/3emes
              $possibleItems->add($possiblePeople);
            }
            $possibleItems->add($possibleEquipment);
            $possibleItems->add($possiblePotions);
          }
          $team = $p->team;
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
            if ($p->team->rank && $p->team->rank->is("index>=8")) {
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
          $teamId = $input->get('teamId');
          $team = $pages->get("id=$teamId");
          $groupPlayers = $pages->find("template=player, team=$team, group=$pageId");
          $out .= '<div class="row">';
            $out .= '<p class="text-center"><span class="label label-primary">'.$p->title.'</span></p>';
            $out .= '<ul class="list-unstyled list-inline text-left">';
            $donatorId = $groupPlayers->sort('-GC')->first()->id;
            $groupPlayers->sort('-karma');
            foreach($groupPlayers as $gp) {
              $nbFreeEl = $gp->places->count();
              if ($gp->team->rank && $gp->team->rank->is("index>=8")) {
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
            $out .= '<li><span><a href="'.$pages->get("name=shop")->url.$team->name.'">→ Go to the Marketplace.</a></span></li>';
          }
        }
        // Make a donation
        if ($p->GC > 5 || $p->is("parent.name=groups")) {
          $out .= '<li><span><a href="'.$pages->get("name=makedonation")->url.$team->name.'/'.$donatorId.'">→ Make a donation (help another player).</a></span></li>';
        }
        // Go to team's Freeworld
        $out .= '<li><span><a href="'.$pages->get("name=world")->url.$team->name.'">→ See team\'s Freeworld.</a></span></li>';
        // Go to team's scoring table
        $out .= '<li><span><a href="'.$pages->get("name=players")->url.$team->name.'">→ See team\'s scoring table.</a></span></li>';
        // Pick another player
        $out .= '<li><span><a href="#" onclick="swal.close(); $(\'#pickTeamPlayer\').click(); return false;">→ Pick a random player in the team.</a></span></li>';
        // Read about a random element
        $allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
        $allPeople = $pages->find("template=people, name!=people, sort=title");
        if (isset($allPlaces) && isset($allPeople)) {
          $allElements = clone($allPlaces);
          $allElements->add($allPeople);
          $randomId = $allElements->getRandom()->id;
          $out .= '<li><span><a href="#" class="ajaxBtn" data-type="showInfo" data-id="'.$randomId.'">→ Read about a random element.</a></span></li>';
        }
        // Visit the Hall of Fame
        $out .= '<li><span><a href="'.$pages->get("name=hall-of-fame")->url.'">→ Visit the Hall of Fame.</a></span></li>';
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
              // Find element's # of owners
              $out .= '<div class="alert alert-info">';
              $p = setOwners($p, $player);
              $out .= '<span class="">'.__("Free rate").' : ['.$p->owners->count().'/'.$p->teamRate.']</span> ';
              $out .= progressbar($p->owners->count(), $p->teamRate);
              if ($p->completed == 1) { $out .= '<span class="badge">'.__("Congratulations !").'</span>'; }
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
          $out .= '<span class="alert alert-info">'.__("This item costs");
          $out .= ' '.$p->GC.__('GC');
          $out .= ' ('.sprintf(__("You have %d GC"), $player->GC).')</span>';
        $out .= '</div>';
        if (!isset($player->group->id) && $p->is("template=item") && $p->category->name == 'group-items') {
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
          $allConcernedMonsters->sort("date('Ymd', lastTrainDate), date");
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
                echo '<a href="'.$pages->get("name=underground-training")->url.'?id='.$m->monster->id.'">'.$m->monster->title.'</a>';
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
            echo '<li class="label label-danger">'.sprintf(__("You have NEVER trained on %d monsters"), $tmpPage->index).'</li>';
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
                echo '<a href="'.$m->monster->url.'">'.$m->monster->title.'</a>';
              } else {
                echo $m->monster->title;
              }
              echo '</span> : ';
              echo '<span data-toggle="tooltip" title="Quality : '.$m->monster->quality.'" onmouseenter="$(this).tooltip(\'show\');" data-html="true"> '.averageLabel($m->monster->quality).'</span>';
              /* if ($m->monster->fightNb == 1) { $label = __('fight'); } else { $label = __('fights'); } */
              /* echo ' → <span>'.$m->monster->fightNb.' '.$label; */
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
        if ($allBattles->count() > 0) {
          echo '<p class="label label-primary">You have faced '.$allBattles->count().' monster attacks.</p>';
            echo '<ul class="utReport list-group list-unstyled">';
            foreach ($allBattles as $m) {
              echo '<li>'.$m->result.' : '.$m->summary.'';
              echo ' ['.strftime("%d/%m", $m->date).']';
              echo '</li>';
            }
            echo '</ul>';
        } else {
          echo "<p>".__("You haven't faced any monster attacks yet.")."</p>";
        }
        break;
      case 'history' :
        $playerId = $input->get('playerId');
        $playerPage = $pages->get($playerId);
        $headTeacher = getHeadTeacher($playerPage);
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
              $out .= $event->task->title;
              if ($event->summary != '') {
                $out .= ' ['.$event->summary.']';
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
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Compétence SACoche : Je participe en classe." onmouseenter="$(this).tooltip(\'show\');"></span> Communication ';
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
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Affaires oubliées<br />Compétence SACoche : J\'ai mon matériel."></span> Forgotten material : ';
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
        $out .= ' <span> ['.$player->extraHk->count().' extra - </span>';
        $out .= ' <span>'.$player->initiative->count().' initiatives - </span>';
        $out .= ' <span>'.$player->outClassActivity.' UT/FP session]</span>';
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
        $out .= '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="Soucis avec l\'attitude<br />Compétence SACoche : J\'adopte une attitude d\'élève."></span> Attitude problems :';
        $attPb = $disobedience->count()+$ambush->count()+$noisy->count();
        $out .= ' <span> ['.$attPb.' problems - </span>';
        $out .= ' <span>'.$late->count().' slow moves]</span>';
        $out .= ' ⇒ ';
        if ($pb->count() == 0) {
          $out .= '<span data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="<span class=\'label label-success\'>VV</span> 0 problems<br /><span class=\'label label-success\'>V</span> <span class=\'label label-danger\'>R</span> <span class=\'label label-danger\'>RR</span> Ask your teacher" class="label label-success">VV</span>';
        } else {
          $out .= '<span data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="">Ask your teacher.</span>';
        }
        $out .= '</p>';
        break;
      default :
        $out = __("todo");
    }
    echo $out;
  }
?>
