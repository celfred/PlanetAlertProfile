<?php

  include("./head.inc"); 

  $allPlayers = $pages->find('template=player');
  $allTeams = $pages->get("/teams")->children->not("name=orphans")->sort('-name');
  $totalPlaces = $pages->find("template='place', name!='places'");

  echo '<div class="row">';
    display_scores($allPlayers, $allTeams, $totalPlaces);
  echo '</div>';

?>

<div class="row">
  <div class="col-sm-4">
    <div id="" class="panel panel-success">
      <div class="panel-heading">
      <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most influential</h4>
      </div>
      <div class="panel-body">
        <ol>
          <?php
            $players = $pages->find('template=player, sort=-karma, karma>0, limit=10');
            foreach($players as $player) {
              if ($player->avatar) {
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
              } else {
                $mini = '';
              }
              if ($player->login == $user->name) {
                $focus = "class='focus'";
              } else {
                $focus = "";
              }
              echo '<li><span '. $focus .'>'.$mini.' '.$player->title.' ['. $player->team->title.']</span> <span class="badge">'.$player->karma.' karma</span></li>';
            }
          ?>
        </ol>
      </div>
    </div>

    <div class="panel panel-success">
      <div class="panel-heading">
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> Greatest # of Free Places</h4>
      </div>
      <div class="panel-body">
        <ol>
          <?php
            $players = $pages->find('template=player, sort=-places.count, places.count>0, limit=10');
            foreach($players as $player) {
              if ($player->avatar) {
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
              } else {
                $mini = '';
              }
              if ($player->login == $user->name) {
                $focus = "class='focus'";
              } else {
                $focus = "";
              }
              echo '<li><span '.$focus.'>'.$mini.' '.$player->title.' ['. $player->team->title.']</span> <span class="badge">'.$player->places->count.' places</span></li>';
          }
        ?>
        </ol>
      </div>
    </div>

    <div class="panel panel-info">
      <div class="panel-heading">
        <h4 class="panel-title"><span class="glyphicon glyphicon-wrench"></span> Most equipped</h4>
      </div>
      <div class="panel-body">
        <ol>
          <?php
            $players = $pages->find('template=player, sort=-equipment.count, equipment.count>0, limit=10');
            foreach($players as $player) {
              if ($player->avatar) {
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
              } else {
                $mini = '';
              }
              if ($player->login == $user->name) {
                $focus = "class='focus'";
              } else {
                $focus = "";
              }
              echo '<li><span '. $focus .'>'.$mini.' '.$player->title.' ['. $player->team->title.']</span> <span class="badge">'.$player->equipment->count.' equipment</span></li>';
            }
          ?>
        </ol>
      </div>
    </div>

    <div id="" class="panel panel-info">
      <div class="panel-heading">
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/heart.png" alt="" /> Best donators</h4>
      </div>
      <div class="panel-body">
        <ol>
          <?php
            $players = $pages->find('template=player, sort=-donation, donation>0, limit=10');
            foreach($players as $player) {
              if ($player->avatar) {
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
              } else {
                $mini = '';
              }
              if ($player->login == $user->name) {
                $focus = "class='focus'";
              } else {
                $focus = "";
              }
              echo '<li><span '. $focus .'>'.$mini.' '.$player->title.' ['. $player->team->title.']</span> <span class="badge">'.$player->donation.' GC</span></li>';
            }
          ?>
        </ol>
      </div>
    </div>
  </div>

  <div class="col-sm-8">
    <?php
      // Admin news
      $newsAdmin = $pages->get('/newsboard')->children('publish=1')->sort('-created');
      if ($newsAdmin->count() > 0) {
        foreach($newsAdmin as $n) {
        ?>
        <div id="" class="news panel panel-success">
          <div class="panel-heading">
            <h4 class="panel-title">
             <?php
              $logo = $homepage->photo->eq(0)->size(40,40); 
              echo '<img src="'.$logo->url.'" alt="" /> ';
              echo date("F d, Y", $n->created);
              echo ' - ';
              echo 'Official Announcement : '.$n->title;
             ?>
           </h4>
         </div>
         <div class="panel-body">
           <?php
             echo $n->body;
           ?>
         </div>
         <?php
            if ($user->isSuperuser()) {
         ?>
         <div class="panel-footer text-right">
<form>
<label for="unpublish_<?php echo $n->id; ?>"><input type="checkbox" id="unpublish_<?php echo $n->id; ?>" class="ajaxUnpublish" value="<?php echo $pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id; ?>" /> Unpublish from Newsboard<span id="feedback"></span></label>
         </div>
         <?php
           }
         ?>
      </div>
      <?php
        }
      }

      // Automatic players' news (free place, shop)
      $news = $pages->find("template=event, publish=1, sort=-created");
      if ($news->count() > 0) {
        foreach($news as $n) {
          $currentPlayer = $n->parent('template=player');
          if ($currentPlayer->avatar) {
            $thumb = $currentPlayer->avatar->size(40,40);
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$thumb->url."' alt='avatar' />";
          } else {
            $mini = '';
          }
        ?>
        <div id="" class="news panel panel-primary">
          <div class="panel-heading">
            <h4 class="panel-title">
             <?php
              echo date("F j (l)", $n->date);
              echo ' - ';
              echo 'Congratulations to ';
              echo $currentPlayer->title.' ['.$currentPlayer->team->title.']  ';
              echo $mini.'  ';
             ?>
           </h4>
         </div>
         <div class="panel-body text-center">
           <?php
             echo '<p>';
             switch ($n->task->category->name) {
             case 'place' : echo '<span class="lead">New place : '.html_entity_decode($n->summary).'</span>';
               break;
             case 'shop' : echo '<span class="lead">New equipment : '.html_entity_decode($n->summary).'</span>';
               break;
             case 'attitude' : echo '<span class="lead">Generous attitude : '.html_entity_decode($n->summary).'</span>';
               break;
             default : echo 'todo : ';
               break;
             }
             //echo $n->task->title. ' : ' . $n->summary;
             echo '</p>';
           ?>
         </div>
         <?php
            if ($user->isSuperuser()) {
         ?>
         <div class="panel-footer text-right">
<form>
<label for="unpublish_<?php echo $n->id; ?>"><input type="checkbox" id="unpublish_<?php echo $n->id; ?>" class="ajaxUnpublish" value="<?php echo $pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id; ?>" /> Unpublish from Newsboard<span id="feedback"></span></label>
         </div>
         <?php
           }
         ?>
      </div>
    <?php
      }
      } else { // No news
        echo '<h4 class="well">No player\'s news... :(</h4>';
      }
    ?>
  </div>

</div>

<?php
  include("./foot.inc"); 
?>
