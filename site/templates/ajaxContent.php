<?php
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
        $news = $pages->find("template=event, sort=-created, publish=1, task=free|buy|penalty|remove");
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
      default :
        $out = 'Todo...';
    }
    echo $out;
  }
?>
