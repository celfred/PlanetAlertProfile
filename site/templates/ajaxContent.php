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
        break;
      case 'decision' :
        $pageId = $input->get('pageId');
        $p = $pages->get("id=$pageId");
        if ($p->is("template=player")) {
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
            foreach($groupPlayers as $p) {
              $nbFreeEl = $p->places->count();
              if ($p->team->rank->is('name=4emes|3emes')) {
                $nbFreeEl += $p->people->count();
              }
              if ($p->avatar) { $mini = '<img src="'.$p->avatar->getThumb('thumbnail').'" alt="avatar" width="50" />'; }
            $out .= '<li>';
            $out .= $mini;
            $out .= '<span>';
            $out .= $p->title;
            if ($p->coma == 0) {
              $out .= ' <span class="badge">'.$p->karma.'K.</span>';
              $out .= ' <span class="badge"><span class="glyphicon glyphicon-wrench"></span>'.$p->equipment->count().'</span>';
              $out .= ' <span class="badge"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> '.$nbFreeEl.'</span>';
              $out .= ' <span class="badge">'.$p->HP.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
              $out .= ' <span class="badge">'.$p->GC.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /></span>';
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
        $out .= '<h4>What do you want to do ? [I want to...]</h4>';
        $out .= '<ul class="text-left list-unstyled">';
        $out .= '<li><span class="toggleStrike label label-danger">✓/✗</span> <span class="strikeText"><a href="'.$pages->get("name=shop")->url.$p->team->name.'">Go to the Marketplace.</a>.</span></li>';
        $out .= '<li><span class="toggleStrike label label-danger">✓/✗</span> <span class="strikeText"><a href="'.$pages->get("name=makedonation")->url.$p->team->name.'/'.$donatorId.'">Make a donation (help another player).</a></span></li>';
        $out .= '<li><span class="toggleStrike label label-danger">✓/✗</span> <span class="strikeText"><a href="'.$pages->get("name=quiz")->url.$p->team->name.'">Repell a monster invasion.</a></span></li>';
        $out .= '<li><span class="toggleStrike label label-danger">✓/✗</span> <span class="strikeText">Pick another group/player/ambassador...</span></li>';
        $out .= '<li><span class="toggleStrike label label-danger">✓/✗</span> <span class="strikeText">Pick a random mission.</span></li>';
        $out .= '</ul>';
        // TODO : Check available potions/places/people (with a discount)
        // TODO : Pick 1 random ?
        // TODO : Random discount ?
        // Pb : How to record in player's history ? When recalculating...
        // > use $refPage->linkedId ? Have discount pages in backend ?
        // Possible places
        $allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
        $allPeople = $pages->find("template=people, name!=people, sort=title");
        $possiblePlaces = $allPlaces->find("GC<=$p->GC, level<=$p->level, id!=$p->places,sort=name");
        // Possible people
        $possiblePeople = $allPeople->find("GC<=$p->GC, level<=$p->level, id!=$p->people,sort=name");
        if ($possiblePlaces->count() > 0 || $possiblePeople->count() > 0 ) {
          $out .= '<ul>';
            $out .= '<span class="badge">Special offers : 50% discount !</span>';
            $out .= '<li><span class="toggleStrike label label-danger">✓/✗</span> <span class="strikeText"><a href="'.$pages->get("name=shop")->url.$p->team->name.'">Go to the Marketplace.</a>.</span></li>';
          $out .= '</ul>';
        }
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
