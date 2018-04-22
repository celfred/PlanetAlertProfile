<?php namespace ProcessWire;

  include("./head.inc"); 

  // Display team scores
  echo '<div class="row">';
    showScores($allTeams);
  echo '</div>';
  
  // Display Personal Analyzer if user is logged in
  if ($user->isLoggedin() && $user->isSuperuser()==false) {
    $player = $pages->get("login=$user->name");
    echo pma($player);
  }

  $out = '';
  $out .= '<div class="row">';
      if ($user->isLoggedin() && !$user->isSuperuser()) {
      $out .= '<div class="col-sm-6">';
        if ($player->team->is("name!=no-team")) {
          $teamPlayers = $allPlayers->filter("team=$player->team"); // Limit to logged player's team
        } else {
          $teamPlayers = $pages->find("template=player, team.name=no-team");
        }
        
        // Get players' last 10 events
        $allEvents = $player->child("name=history")->find("template=event,sort=-created,limit=10");
        $out .= '<div id="" class="news panel panel-primary">';
          $out .= '<div class="panel-heading">';
            $out .= '<h4 class="panel-title">';
              if ($player->avatar) {
                $out .= '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="avatar" />';
              }
              $out .= 'Last 10 events in your personal history';
            $out .= '</h4>';
          $out .= '</div>';
          $out .= '<div class="panel-body ajaxContent" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="last10&playerId='.$player->id.'" data-priority="1">';
          $out .= '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
          $out .= '</div>';
          $out .= '<div class="panel-footer text-right">';
          $out .= '<p>To see your complete history, go the the <a href="'.$pages->get('/players')->url.$player->team->name.'/'.$player->name.'">My Profile</a> page.</p>';
          $out .= '</div>';
        $out .= '</div>';

        if ($player->team->is("name!=no-team")) {
          // Team News (Free/Buy actions during last 5 days)
          $news = new PageArray();
          $today = new \DateTime("today");
          $interval = new \DateInterval('P5D');
          $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
          foreach($allPlayers as $p) {
            $last = $p->find("parent.name=history, date>=$limitDate,task.name=free|buy");
            if ($last->count() > 0) {
              $news->add($last);
              $news->sort('-date');
            }
          }
          $out .= '<div id="" class="board panel panel-primary">';
            $out .= '<div class="panel-heading">';
            $out .= '<h4 class=""><span class="label label-primary">Team News (last 5 days)</span></h4>';
            $out .= '</div>';
            $out .= '<div class="panel-body">';
              $out .= '<ul id="newsList" class="list list-unstyled list-inline text-center">';
              $counter = 1;
              foreach ($news as $n) {
                $currentPlayer = $n->parent('template=player');
                $out .= '<li>';
                $out .= '<div class="thumbnail">';
                $out .= '<span class="badge">'.$counter.'</span>';
                $counter++;
                if ($n->refPage->photo) {
                  $out .= '<img class="showInfo" data-id="'.$n->refPage->id.'" src="'.$n->refPage->photo->eq(0)->getCrop("thumbnail")->url.'" alt="'.$n->summary.'" />';
                }
                if ($n->refPage->image) {
                  $out .= '<img class="showInfo" data-id="'.$n->refPage->id.'" src="'.$n->refPage->image->getCrop("thumbnail")->url.'" alt="'.$n->summary.'" />';
                }
                $out .= '<caption class="text-center">';
                $out .= ' <span>(On '.date('D, M. j', $n->date).')</span><br />';
                $out .= ' <span class="badge">'.$currentPlayer->title.'</span>';
                $out .= '</caption>';
                $out .= '</div>';
                $out .= '</li>';
              }
              if ($news->count() == 0) {
                $out .= '<p>No recent news.</p>';
              }
              $out .= '</ul>';
            $out .= '</div>';
          $out .= '</div>';
        }
            
        $out .= '<div class="row">'; // Nested 2 columns
          $out .= '<div class="col-sm-6">'; // Subcolumn 1
            // Most active
            $out .= '<div class="board panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=yearlyKarma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
            $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/star.png" alt="" /> Team Most Active Players</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "-yearlyKarma");
            $out .= '  </div>';
            $out .= '</div>';
            
            // Groups
            $out .= '<div id="" class="panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <h4 class="panel-title">Team Most Active Groups</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "group");
            $out .= '  </div>';
            $out .= '</div>';

            // Most influential
            $out .= '<div id="" class="panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
            $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/star.png" alt="" /> Team Most influential</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "-karma");
            $out .= '  </div>';
            $out .= '</div>';

            // Best UT
            $out .= '<div id="" class="panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
            $out .= '  <h4 class="panel-title"><i class="glyphicon glyphicon-headphones"></i> Team Most Trained</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "-underground_training");
            $out .= '  </div>';
            $out .= '</div>';

          $out .= '</div>'; // /subcolumn 1

          $out .= '<div class="col-sm-6">'; // Subcolumn 2
            if ($player->team->is("name!=no-team")) {
              // Help needed
              $out .= '<div id="" class="board panel panel-danger">';
                $out .= '<div class="panel-heading">';
                  $dangerPlayers = $allPlayers->find('coma=1');
                  $dangerPlayers->add($allPlayers->find("HP<=10"))->sort("coma, HP");
                  $out .= '<p class="panel-title">Help needed!</p>';
                $out .= '</div>';
                $out .= '<div class="panel-body">';
                  if ($dangerPlayers->count() != 0) {
                    $out .= '<ul class="list list-unstyled list-inline text-center">';
                    foreach($dangerPlayers as $p) {
                      if ($p->coma == 1) {
                        $label = 'Coma';
                      } else {
                        $label = $p->HP.'HP';
                      }
                      $out .= '<li>';
                      if ($p->avatar) {
                        $out .= '<img class="" src="'.$p->avatar->getCrop("mini")->url.'" width="50" alt="Avatar" />';
                      } else {
                        $out .= '<Avatar>';
                      }

                      $out .= $p->title;
                      $out .= ' <span class="badge">'.$label.'</span><br />';
                      $out .= '</li>';
                    }
                    $out .= '<ul>';
                  } else {
                    $out .= '<p>Congratulations ! No player with HP<10 !</p>';
                  }
                $out .= '</div>';
                $out .= '<div class="panel-footer text-right">';
                $out .= '</div>';
              $out .= '</div>';
            }
           
            // Greatest # of places
            $out .= '<div id="" class="panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
            $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> Team Greatest # of places</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "places");
            $out .= '  </div>';
            $out .= '</div>';

            // Greatest # of people if needed
            if ($player->rank->is("name=4emes|3emes")) {
              $out .= '<div id="" class="panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> Team Greatest # of people</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-people.count");
              $out .= '  </div>';
              $out .= '</div>';
            }

            // Best donators
            $out .= '<div id="" class="panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
            $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/heart.png" alt="" /> Team Best donators</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "-donation");
            $out .= '  </div>';
            $out .= '</div>';

            // Best warrior
            $out .= '<div id="" class="panel panel-success">';
            $out .= '  <div class="panel-heading">';
            $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
            $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/flash.png" alt="" /> Team Best warriors</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "-fighting_power");
            $out .= '  </div>';
            $out .= '</div>';

          $out .= '</div>'; // /subcolumn 2

    $out .= '</div>'; // /col-sm-6

    echo $out;
    } else {
      if (!$user->isSuperuser()) {
      echo '<div class="col-sm-6">';
        echo '<div class="row">'; // Nested 2 columns
          echo '<div class="col-sm-6">'; // Subcolumn 1
    ?>
      <div id="" class="panel panel-success">
        <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most influential</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="karma">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div class="panel panel-success">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> Greatest # of Places</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="places">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div class="panel panel-success">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> Greatest # of People</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="people">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div id="" class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/heart.png" alt="" /> Best donators</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="donation">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <?php
        echo '</div>';
        echo '<div class="col-sm-6">';
      ?>

      <div id="" class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><i class="glyphicon glyphicon-headphones"></i> Most trained</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="underground_training">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><span class="glyphicon glyphicon-flash"></span> Best warriors</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="fighting_power">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div id="" class="panel panel-success">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=group"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most active groups</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="group">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>
      <?php
        echo '</div>';
        echo '</div>';
      ?>
    <?php } ?>
    <?php } // End isSuperuser() ?>
  </div>

    <?php
      if (!$user->isSuperuser()) { // Scoreboards take place !
        echo '<div class="col-sm-6">';
      } else {
        echo '<div class="col-sm-12">'; // No scoreboards
      }
    
      // Admin news
      if ($user->isLoggedin()) {
        if ($user->isSuperuser()) {
          // Admin gets all published news
          $newsAdmin = $pages->get("/newsboard")->children("publish=1")->sort("-date");
        } else {
          // User gets public and ranked news
          $newsAdmin = $pages->get("/newsboard")->children("publish=1, public=0|1, ranks=''|$player->rank")->sort("-date");
        }
      } else { // Guests get public news only
        $newsAdmin = $pages->get("/newsboard")->children("publish=1, public=1")->sort("-date");
      }
      if ($newsAdmin->count() > 0) {
        foreach($newsAdmin as $n) {
        ?>
          <div id="<?php echo $n->id; ?>" class="news panel panel-success">
          <div class="panel-heading">
            <h4 class="panel-title">
             <?php
              $logo = $homepage->photo->eq(0)->size(40,40); 
              echo '<img src="'.$logo->url.'" alt="" /> ';
              echo date("M. d, Y", $n->created);
              echo ' - ';
              echo $n->title;
              if ($n->public == 0) {
                if ($n->ranks) {
                  foreach ($n->ranks as $r) {
                    echo ' <span class="label label-default">'.$r->title.'</span>';
                  }
                }
              } else {
                echo ' <span class="label label-default">Public News</span>';
              }
             ?>
               <button type="button" class="close" data-id="<?php echo '#'.$n->id; ?>" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           </h4>
         </div>
         <div class="panel-body">
           <?php
             echo $n->body;
             echo '<br />';
             echo '<a role="button" class="" data-toggle="collapse" href="#collapseDiv'.$n->id.'" aria-expanded="false" aria-controls="collapseDiv">[French version]</a>';
             echo '<div class="collapse" id="collapseDiv'.$n->id.'"><div class="well">';
             if ($n->frenchSummary != '') {
               echo $n->frenchSummary;
             } else {
               echo 'French version in preparation, sorry ;)';
             }
             echo '</div>';
             echo '</div>';
           ?>
         </div>
         <?php
            if ($user->isSuperuser()) {
         ?>
         <div class="panel-footer text-right">
          <label for="unpublish_<?php echo $n->id; ?>"><input type="checkbox" id="unpublish_<?php echo $n->id; ?>" class="ajaxUnpublish" value="<?php echo $pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id; ?>" /> Unpublish from Newsboard<span id="feedback"></span></label>
         </div>
         <?php
           }
         ?>
      </div>
      <?php
        }
      }

      // Admin NewsBoard (to prepare in-class papers to be given to the students)
      if ($user->isSuperuser()) {
      ?>
      <div id="" class="news panel panel-primary">
        <div class="panel-heading">
          <h4 class="panel-title">Admin's work</h4>
        </div>
        <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="admin-work">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>
      <?php
      }

      // User is logged in and in a team, load work statistics
      if ($user->isLoggedin() && $user->isSuperuser() == false) {
        if ($player->team->name != 'no-team') { ?>
          <div id="" class="news panel panel-primary">
            <div class="panel-heading">
              <h4 class="panel-title">
                <?php if ($player->avatar) { echo '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="avatar" />'; } ?>
                Work statistics on current period (<?php echo $currentPeriod->title; ?>) <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Suivi du travail sur la période (pour SACoche). Si la période n'est pas terminée, tu peux encore améliorer tes résultats !"></span>
              </h4>
            </div>
            <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-priority="1" data-id="work-statistics&playerId=<?php echo $player->id; ?>&periodId=<?php echo $currentPeriod->id; ?>">
              <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
            </div>
            <div class="panel-footer text-right">
            <p class=""><?php echo '<a href="'.$homepage->url.'report_generator/singlePlayer/'.$player->id.'/'.$currentPeriod->id.'/?sort=title">[ See my report <i class="glyphicon glyphicon-file"></i> ]</a>&nbsp;&nbsp;'.$currentPeriod->title; ?>  : from <?php echo date("M. j, Y", $currentPeriod->dateStart) ?> to <?php echo date("M. j, Y", $currentPeriod->dateEnd) ?></p>
            </div>
          </div>
      <?php 
        }
      }

      // Last public news
      ?>
        <div id="" class="news panel panel-primary">
          <div class="panel-heading">
            <h4 class="panel-title">Recent public activity</h4>
          </div>
          <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="lastEvents">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
          </div>
        </div>
  </div>

</div>

<?php
  include("./foot.inc"); 
?>
