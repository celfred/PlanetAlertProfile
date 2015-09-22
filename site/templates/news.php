<?php

  include("./head.inc"); 

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
              if ($player->playerTeam == '') {$team = '';} else {$team = ' ['.$player->playerTeam.']';}
              echo '<li><span '. $focus .'>'.$mini.' '.$player->title.$team.'</span> <span class="badge">'.$player->karma.' karma</span></li>';
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
              if ($player->playerTeam == '') {$team = '';} else {$team = ' ['.$player->playerTeam.']';}
              if ($player->places->count > 1) {
                echo '<li><span '.$focus.'>'.$mini.' '.$player->title.$team.'</span> <span class="badge">'.$player->places->count.' places</span></li>';
              } else {
                echo '<li><span '.$focus.'>'.$mini.' '.$player->title.$team.'</span> <span class="badge">'.$player->places->count.' place</span></li>';
              }
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
              if ($player->playerTeam == '') {$team = '';} else {$team = ' ['.$player->playerTeam.']';}
              echo '<li><span '. $focus .'>'.$mini.' '.$player->title.$team.'</span> <span class="badge">'.$player->equipment->count.' equipment</span></li>';
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
              if ($player->playerTeam == '') {$team = '';} else {$team = ' ['.$player->playerTeam.']';}
              echo '<li><span '. $focus .'>'.$mini.' '.$player->title.$team.'</span> <span class="badge">'.$player->donation.' GC</span></li>';
            }
          ?>
        </ol>
      </div>
    </div>
  </div>

  <div class="col-sm-8">
    <?php
      // Admin is logged in, show stats
      if ($user->isSuperuser()) {
        // Get current school year dates
        $period = $pages->get("template='period', name='school-year'");
        // Get today's unique logged players' names
        $query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND login_was_successful=1 AND date(login_timestamp) = current_date()");   
        $query->execute();
        $todaysPlayers = $query->fetchAll();
        // Get yesterday's unique logged players' names
        $query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND login_was_successful=1 AND date(login_timestamp) = current_date()-1");   
        $query->execute();
        $yesterdaysPlayers = $query->fetchAll();
        // Get total # of unique logged players during the last 7 days
        $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND login_was_successful=1 AND login_timestamp BETWEEN date(now())-7 AND now()");   
        $query->execute();
        $totalNbUniqueVisitors7Days = $query->fetchColumn();
        // Get total # of logged players during the last 7 days
        $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND login_was_successful=1 AND login_timestamp BETWEEN date(now())-7 AND now()");   
        $query->execute();
        $totalNbVisitors7Days = $query->fetchColumn();
        // Get total # of unique logged players during the current school year
        $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND now()");   
        $query->execute();
        $totalNbUniqueVisitors = $query->fetchColumn();
        // Get total # of logged players during the current school year
        $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND now()");   
        $query->execute();
        $totalNbVisitors = $query->fetchColumn();

        $stats = '<div id="" class="news panel panel-primary">';
        $stats .= '<div class="panel-heading">';
        $stats .= '<h4 class="panel-title">Planet Alert Statistics (started 17/09/2015)</h4>';
        $stats .= '</div>';
        $stats .= '<div class="panel-body">';
        $stats .= '<p class="lead">';
        $stats .= '&nbsp;&nbsp;&nbsp';
        $stats .= '<span class="label label-success">Today : '.count($todaysPlayers).'</span>';
        $stats .= '&nbsp;&nbsp;&nbsp';
        $stats .= '<span class="label label-success">Yesterday : '.count($yesterdaysPlayers).'</span>';
        $stats .= '&nbsp;&nbsp;&nbsp';
        $stats .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">Last 7 days : '.$totalNbUniqueVisitors7Days.'/'.$totalNbVisitors7Days.'</span>';
        $stats .= '&nbsp;&nbsp;&nbsp';
        $stats .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">School Year : '.$totalNbUniqueVisitors.'/'.$totalNbVisitors7Days.'</span>';
        $stats .= '&nbsp;&nbsp;&nbsp';
        $stats .= '</p>';
        if ( count($todaysPlayers) > 0 ) {
          $stats .= '<p>Today\'s players : </p>';
          $stats .= '<ul>';
          foreach($todaysPlayers as $r) {
            // Get player's name
            $login = $r['username'];
            $player = $pages->get("template='player', login=$login");
            $stats .= '<li>'.$player->title.' ['.$player->playerTeam.']</li>';
          }
          $stats .= '</ul>';
        }
        if ( count($yesterdaysPlayers) > 0 ) {
          $stats .= '<p>Yesterday\'s players : ';
          $stats .= '<ul>';
          foreach($yesterdaysPlayers as $r) {
            // Get player's name
            $login = $r['username'];
            $player = $pages->get("template='player', login=$login");
            $stats .= '<li>'.$player->title.' ['.$player->playerTeam.']</li>';
          }
          $stats .= '</ul>';
        }
        $stats .= '</div>';
        $stats .= '</div>';
        echo $stats;
      }

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

      // User is logged in, show personal news
      if ($user->isLoggedin() && $user->isSuperuser() == false) {
        // Get player's indicators
        $player = $pages->get("template=player, login=$user->name");
        //echo '<h2><img src="'.$player->avatar->getThumb('thumbnail').'" alt="avatar" /> '.$player->title.' ['.$player->playerTeam.']</h2>';
        echo '<div class="">';
        echo '<h3 class="text-center">'.$player->title.' ['.$player->playerTeam.']</h3>';
        echo '<h3 class="">';
        echo '<span class="label label-success">Your Karma : '.$player->karma.'</span>';
        echo '&nbsp;&nbsp;';
        echo '<span class="label label-default" data-toggle="tooltip" title="Level">'.$player->level.'<span class="glyphicon glyphicon-signal"></span></span>';
        echo '&nbsp;&nbsp;';
        echo '<span class="label label-default" data-toggle="tooltip" title="XP">'.$player->XP.'<img src="'.$config->urls->templates.'img/star.png" alt="" /></span>';
        echo '&nbsp;&nbsp;';
        echo '<span class="label label-default" data-toggle="tooltip" title="HP">'.$player->XP.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
        echo '&nbsp;&nbsp;';
        echo '<span class="label label-default" data-toggle="tooltip" title="GC">'.$player->XP.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" /></span>';
        echo '&nbsp;&nbsp;';
        echo '<span class="label label-info" data-toggle="tooltip" title="Free places">'.$player->places->count().'<img src="'.$config->urls->templates.'img/globe.png" alt="" /></span>';
        echo '&nbsp;&nbsp;';
        echo '<span class="label label-info" data-toggle="tooltip" title="Free places">'.$player->equipment->count().'<span class="glyphicon glyphicon-wrench"></span></span>';
        echo '&nbsp;&nbsp;';
        if ($player->donation == false) {$player->donation = 0; }
        echo '<span class="label label-default" data-toggle="tooltip" title="Donated">'.$player->donation.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
        echo ' </h3>';
        echo '</div>';

        // Get last 10 players's events
        $allEvents = $player->child("name=history")->find("template=event,sort=-created,limit=10");
        ?>
        <div id="" class="news panel panel-primary">
          <div class="panel-heading">
            <h4 class="panel-title">
              <?php if ($player->avatar) { echo '<img src="'.$player->avatar->getThumb('mini').'" alt="avatar" />'; } ?>
              Last 10 events in your personal history
            </h4>
          </div>
          <div class="panel-body">
            <ul class="double list-unstyled">
            <?php
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
                echo '<li class="'.$className.'">';
                echo $signicon;
                echo date("F j (l)", $event->date).' : ';
                echo '<span data-toggle="tooltip" title="XP" class="badge badge-success">'.$sign.$event->task->XP.'</span><img src="'.$config->urls->templates.'img/star.png" alt="XP" /> ';
                echo '<span data-toggle="tooltip" title="GC" class="badge badge-default">'.$sign.$event->task->GC.'</span><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /> ';
                if ($className == 'negative') {
                  echo '<span data-toggle="tooltip" title="HP" class="badge badge-warning">'.$sign.$event->task->HP.'</span><img src="'.$config->urls->templates.'img/heart.png" alt="HP" /> ';
                }
                echo $event->task->title;
                echo ' ['.$event->summary.']';
                echo '</li>';
              };
            ?>
            </ul>
          </div>
          <div class="panel-footer text-right">
          <p>To see your complete history, go the the <a href="<?php echo $pages->get('/players')->url.$player->playerTeam.'/'.$player->name; ?>">'My Profile'</a> page.</p>
          </div>
        </div>
      <?php 
      }

      // Public news
      $news = $pages->find("template=event, sort=-created, limit=10, task=free|buy");
      if ($news->count() > 0) {
      ?>
        <div id="" class="news panel panel-primary">
          <div class="panel-heading">
            <h4 class="panel-title">
              Last 10 public events in Planet Alert
            </h4>
          </div>
          <div class="panel-body">
            <ul class="list-unstyled">
            <?php
            foreach($news as $n) {
              $currentPlayer = $n->parent('template=player');
              if ($currentPlayer->avatar) {
                $thumb = $currentPlayer->avatar->size(40,40);
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$thumb->url."' alt='avatar' />";
              } else {
                $mini = '';
              }
              echo '<li>';
              echo $mini;
              echo date("F j (l)", $n->date).' : ';
              echo '<span>';
              switch ($n->task->category->name) {
              case 'place' : echo '<span class="">New place for '.$currentPlayer->title.' ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'shop' : echo '<span class="">New equipment for '.$currentPlayer->title.' ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'attitude' : echo '<span class="">Generous attitude from '.$currentPlayer->title.' ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                break;
              default : echo 'todo : ';
                break;
              }
              //echo $n->task->title. ' : ' . $n->summary;
              echo '</span>';
              echo '</li>';
            }
            ?>
          </ul>
        </div>
      </div>
      <?php
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
              echo $currentPlayer->title.' ['.$currentPlayer->playerTeam.']  ';
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
