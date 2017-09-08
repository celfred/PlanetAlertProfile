<?php
  if ($config->ajax) {
    include("./my-functions.inc");
    $out = '';
    switch ($input->get('id')) {
      case 'lastEvents' :
        // Last 3 published monsters
        $out .= '<ul class="list-inline">&nbsp;';
        $out .= '<li class="label label-success"><span class="glyphicon glyphicon-headphones"></span> New monsters !</li>';
        $lastMonsters = $pages->find("template=exercise, sort=-published, limit=3");
        foreach($lastMonsters as $m) {
          if ($m->image) {
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getThumb('thumbnail')."\" alt=\"image\" />' src='".$m->image->getThumb('mini')."' alt='image' />";
          } else {
            $mini = '';
          }
          $out .= '  <li data-toggle="tooltip" title="'.$m->summary.'">'.$mini.' '.$m->title.'</li>  ';
        }
        if ($user->isLoggedin()) {
          if ($user->isSuperuser() == false) {
            $currentPlayer = $pages->get("template=player, login=$user->name");
            $helmet = $currentPlayer->equipment->get("name=memory-helmet");
          }
          if ($helmet || $user->isSuperuser()) {
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
            $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=0|1, ranks=''|$player->rank, limit=5")->sort("-date");
          }
        } else { // Guests get public news only
          $newsAdmin = $pages->get("/newsboard")->children("publish=0, public=1, limit=5")->sort("-date");
        }
        $out .= '<p>';
        $out .= '<span class="label label-success"><span class="glyphicon glyphicon-hand-up"></span> Last official announcements !</span>';
        $out .= '<ul class="">';
        $blogUrl = $pages->get("name=blog")->url;
        foreach($newsAdmin as $n) {
          $out .= '<li>'.date("F d, Y", $n->created).' : <a href="'.$blogUrl.'">'.$n->title.'</a></li>';
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
              $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$thumb->url."' alt='avatar' />";
            } else {
              $mini = '';
            }
            $out .= '<li>';
            $out .= $mini;
            $out .= date("F j (l)", $n->date).' : ';
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
        if ($news->count() > 0) {
          $out .= '<ul class="list-unstyled">';
          foreach($news as $n) {
            $currentPlayer = $n->parent('template=player');
            if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
            $out .= '<li class="">';
            $out .= date("F j (l)", $n->date).' : ';
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
        $unusedConcerned = $pages->find("template=player, usabledItems.count>0");
        if ($unusedConcerned->count > 0) {
          $out .= '<p class="label label-primary">Potion Planner</p>';
          foreach ($unusedConcerned as $p) {
            $out .= '<ul class="list-unstyled">';
            foreach ($p->usabledItems as $item) {
              $historyPage = $p->get("name=history")->get("refPage=$item, linkedId=0");
              if ($historyPage->id) {
                $out .= '<li class="">';
                // Find # of days compared to today to set 'New' indicator
                $date1 = new DateTime("today");
                $date2 = new DateTime(date("Y-m-d", $historyPage->date));
                $interval = $date1->diff($date2);
                if ($interval->days > 21) {
                  $out .= ' <span class="badge">!</span> ';
                }
                $out .= $p->title.' ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)';
                $out .= ' <label for="unpublish_'.$historyPage->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> Used today<span id="feedback"></span></label>';
                $out .= '</li>';
              }
            }
            $out .= '</ul>';
          }
        } else {
          $out .= '<hr /><p class="">No Potion to be used.</p>';
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
            $today = mktime("23:59:59 Y-m-d");
            $limitDate = time()-15*3600*24;
            $boughtPotions = $p->find("template=event, date>=$limitDate, refPage.name~=potion, refPage.name!=health-potion");
            $possiblePotions = $allEquipments->find("GC<=$p->GC, level<=$p->level, freeActs<=$nbEl, parent.name=potions, sort=name")->not($boughtPotions);
            if ($p->HP == 50) { $possiblePotions->remove("name=health-potion"); }
            $possibleItems = new pageArray();
            $possibleItems->add($possiblePlaces);
            if ($p->team->rank->is('name=4emes|3emes')) { // Add people ONLY for 4emes/3emes
              $possibleItems->add($possiblePeople);
            }
            $possibleItems->add($possibleEquipment);
            $possibleItems->add($possiblePotions);
          }
          $donatorId = $p->id;
          if ($p->avatar) { $mini = '<img src="'.$p->avatar->getThumb('thumbnail').'" alt="avatar" />'; }
          $out .= '<div class="row">';
          $out .= '<div class="col-sm-6 text-center">';
          $out .= '<h3 class="thumbnail">'.$mini.' <span class="caption">'.$p->title.'</span></h3>';
          $out .= '</div>';
          $out .= '<div class="col-sm-6 text-left">';
          $out .= '<ul class="list-unstyled">';
          if ($p->coma == 0) {
            $out .= '<li><span class="label label-success">Karma : '.$p->karma.'</span></li>';
            $out .= '<li><span class="label label-default"><span class="glyphicon glyphicon-signal"></span> '.$p->level.'</span>';
            $threshold = getLevelThreshold($p->level);
            $out .= ' <span class="label label-default"><img src="'.$config->urls->templates.'img/star.png" alt="" /> '.$p->XP.'/'.$threshold.'</span></li>';
            $nbFreeEl = $p->places->count();
            if ($p->team->rank->is('name=4emes|3emes')) {
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
          $groupPlayers = $pages->find("template=player, group=$pageId");
          $out .= '<div class="row">';
            $out .= '<p class="text-center"><span class="label label-primary">'.$p->title.'</span></p>';
            $out .= '<ul class="list-unstyled list-inline text-left">';
            $donatorId = $groupPlayers->sort('-GC')->first()->id;
            $groupPlayers->sort('-karma');
            foreach($groupPlayers as $gp) {
              $nbFreeEl = $gp->places->count();
              if ($gp->team->rank->is('name=4emes|3emes')) {
                $nbFreeEl += $gp->people->count();
              }
              if ($gp->avatar) { $mini = '<img src="'.$gp->avatar->getThumb('thumbnail').'" alt="avatar" width="50" />'; }
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
        if ($p->GC > 5 || $p->is("parent.name=groups")) {
          $out .= '<li><span><a href="'.$pages->get("name=makedonation")->url.$p->team->name.'/'.$donatorId.'">→ Make a donation (help another player).</a></span></li>';
        }
        $out .= '<li><span><a href="'.$pages->get("name=quiz")->url.$p->team->name.'">→ Organize team defense.</a></span></li>';
        if ($p->is("parent.name!=groups")) {
          if (rand(0,1)) { // Random special discount
            if ($possibleItems->count() > 0 ) {
              // Pick a random item
              $selectedItem = $possibleItems->getRandom();
              $details = ' ('.$selectedItem->category->title.')';
              if ($selectedItem->is("has_parent.name=places|people")) { $details = ' in '.$selectedItem->city->title.' ('.$selectedItem->country->title.')'; }
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
                  $out .= '<img class="img-thumbnail" src="'.$selectedItem->photo->eq(0)->getThumb('thumbnail').'" /> ';
                }
                if ($selectedItem->image) {
                  $out .= '<img class="img-thumbnail" src="'.$selectedItem->image->getThumb('thumbnail').'" /> ';
                }
                $out .= '</div>';
              $out .= '</div>';
              $out .= '</li>';
            }
          } else {
            $out .= '<li><span class="strikeText">No special offer today...</span></li>';
          }
        }
        $out .= '</ul>';
        $out .= '</div>';
        break;
      case 'ambassador' :
        $pageId = $input->get('pageId');
        $p = $pages->get("title=$pageId");
        if ($p->avatar) { $mini = '<img src="'.$p->avatar->getThumb('thumbnail').'" alt="avatar" />'; }
        $out .= '<h3 class="thumbnail">'.$mini.' <span class="caption">'.$p->title.'</span></h3>';
        break;
      default :
        $out = 'Todo...';
    }
    echo $out;
  }
?>
