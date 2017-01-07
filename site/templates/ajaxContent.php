<?php
  if ($config->ajax) {
    $out = '';
    switch ($input->get('id')) {
      case 'lastEvents' :
        // Last public news
        $excluded = $pages->find("template=player, name=test");
        // Find current school year date
        $schoolYear = $pages->get("template=period, name=school-year");
        // Find last events
        $news = $pages->find("template=event, date>=$schoolYear->dateStart, sort=-date, limit=20, task.name=free|buy|ut-action-v|ut-action-vv, has_parent!=$excluded");
        if ($news->count() > 0) {
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
