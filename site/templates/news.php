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
    $out .= '<div class="col-sm-6">';
      if ($user->isLoggedin() && !$user->isSuperuser()) {
        $teamPlayers = $allPlayers->filter("team=$player->team"); // Limit to logged player's team
        
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
          $out .= '<div class="panel-body">';
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
                  $out .= date("F j (l)", $event->date).' : ';
                  /* echo '<span data-toggle="tooltip" title="XP" class="badge badge-success">'.$sign.$event->task->XP.'</span><img src="'.$config->urls->templates.'img/star.png" alt="XP" /> '; */
                  /* echo '<span data-toggle="tooltip" title="GC" class="badge badge-default">'.$sign.$event->task->GC.'</span><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /> '; */
                  if ($className == 'negative') {
                    $out .= '<span data-toggle="tooltip" title="HP" class="badge badge-warning">'.$sign.$event->task->HP.'</span><img src="'.$config->urls->templates.'img/heart.png" alt="HP" /> ';
                  }
                  $out .= $event->task->title;
                  $out .= ' ['.$event->summary.']';
                  $out .= '</li>';
                };
              } else {
                $out .= 'No personal history yet...';
              }
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<div class="panel-footer text-right">';
          $out .= '<p>To see your complete history, go the the <a href="'.$pages->get('/players')->url.$player->team->name.'/'.$player->name.'">My Profile</a> page.</p>';
          $out .= '</div>';
        $out .= '</div>';

        // Team News (Free/Buy actions during last 7 days)
        $news = new PageArray();
        $today = new \DateTime("today");
        $interval = new \DateInterval('P5D');
        $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
        foreach($allPlayers as $p) {
          $last = $p->get("name=history")->children("sort=-date")->find("date>=$limitDate,task.name=free|buy");
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
              $out .= ' <span>(On '.date('l, F j', $n->date).')</span><br />';
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
            $out .= '  <h4 class="panel-title"><span class="label label-primary">UT</span> Team Most Trained</h4>';
            $out .= '  </div>';
            $out .= '  <div class="panel-body">';
            $out .= displayTeamScoreboard($teamPlayers, $player, "-underground_training");
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

          $out .= '</div>'; // /subcolumn 1

          $out .= '<div class="col-sm-6">'; // Subcolumn 2
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
            if ($player->team->rank->is("name=4emes|3emes")) {
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

    $out .= '</div>'; // /row

    echo $out;
    } else { ?>
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

      <div class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><span class="glyphicon glyphicon-flash"></span> Best warriors</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="fighting_power">
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

      <div id="" class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><span class="label label-primary">U.T.</span> Most trained</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="underground_training">
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
    <?php } ?>
  </div>

  <div class="col-sm-6">
    <?php // Admin news
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
              echo date("F d, Y", $n->created);
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
          <h4 class="panel-title">Admin's work (papers to be given to players)</h4>
        </div>
        <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="admin-work">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>
      <?php
      }

      // User is logged in, show personal history
      if ($user->isLoggedin() && $user->isSuperuser() == false) {
        if ($player->team->name != 'no-team') {
          // Get current period statistics
          $officialPeriod = $pages->get("name=admin-actions")->periods;
          $allEvents = $player->child("name=history")->find("template=event, date>=$officialPeriod->dateStart, date<=$officialPeriod->dateEnd"); ?>
          <div id="" class="news panel panel-primary">
            <div class="panel-heading">
              <h4 class="panel-title">
                <?php if ($player->avatar) { echo '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="avatar" />'; } ?>
                Work statistics on current period (<?php echo $officialPeriod->title; ?>) <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Suivi du travail sur la période (pour SACoche). Si la période n'est pas terminée, tu peux encore améliorer tes résultats !"></span>
              </h4>
            </div>
            <div class="panel-body">
              <?php
              // Participation
              $out = '';
              setParticipation($player);
              echo '<p>';
              echo '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Participation en classe"></span> Communication ';
              echo ' ⇒ ';
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
              echo  '<span data-toggle="tooltip" title="Compétence SACoche : Je participe en classe." class="label label-'.$class.'">'.$player->participation.'</span>';
              if ($player->partRatio != '-') {
                echo '<span data-toggle="tooltip" title="Participation positive">'.$player->partPositive.' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Participation négative">'.$player->partNegative.' <i class="glyphicon glyphicon-thumbs-down"></i></span>';
              }
              // Homework stats
              setHomework($player, $officialPeriod->dateStart, $officialPeriod->dateEnd);
              if ($player->noHk->count() > 0) {
                $out = '';
                foreach($player->noHk as $index=>$e) {
                  $out .= '- '.strftime("%d/%m", $e->date).' : '.$e->summary.'<br />';
                }
              } else { $out='';}
              if ($player->halfHk->count()>0) {
                $out02 = '';
                foreach($player->halfHk as $index=>$e) {
                  $out02 .= '- '.strftime("%d/%m", $e->date).' : '.$e->summary.'<br />';
                }
              } else { $out02='';}
              if ($player->notSigned->count()>0) {
                $out03 = '';
                foreach($player->notSigned as $index=>$e) {
                  $out03 .= '- '.strftime("%d/%m", $e->date).' : '.$e->summary.'<br />';
                }
              } else { $out03 = '';}
              echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Exercices non faits ou à moitié faits"></span> Training problems :';
              echo ' <span class="">'.$player->hkPb.'</span>';
              echo ' [<span data-toggle="tooltip" data-html="true" title="'.$out.'">'.$player->noHk->count().' Hk</span> - <span data-toggle="tooltip" data-html="true" title="'.$out02.'">'.$player->halfHk->count().' HalfHk</span> - <span data-toggle="tooltip" data-html="true" title="'.$out03.'">'.$player->notSigned->count().' notSigned</span>]';
              echo ' ⇒ ';
              switch ($player->homework) {
                case 'NN' : $class='primary'; break;
                case 'VV' : $class='success'; break;
                case 'V' : $class='success'; break;
                case 'R' : $class='danger'; break;
                case 'RR' : $class='danger'; break;
                default: $class = '';
              }
              echo  '<span data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail fait à la maison." class="label label-'.$class.'">'.$player->homework.'</span> ';
              // Forgotten material
              if ($player->noMaterial->count() > 0) {
                $out04 = '';
                foreach($player->noMaterial as $index=>$e) {
                  $out04 .= '- '.strftime("%d/%m", $e->date).'<br />';
                }
              } else { $out04 = '';}
              echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Affaires oubliées"></span> Forgotten material : ';
              echo '<span data-toggle="tooltip" data-html="true" title="'.$out04.'">'.$player->noMaterial->count().'</span>';
              echo ' ⇒ ';
              if ($player->noMaterial->count() == 0) {
                echo  '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">VV</span>';
              }
              if ($player->noMaterial->count() == 1) {
                echo  '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">V</span>';
              }
              if ($player->noMaterial->count() == 2) {
                echo  '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">R</span>';
              }
              if ($player->noMaterial->count() > 2) {
                echo  '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">RR</span>';
              }
              echo '</p>';
              // Extra-hk
              if ($player->extraHk->count()>0) {
                $out = '';
                foreach($player->extraHk as $index=>$e) {
                  $out .= '- '.strftime("%d/%m", $e->date).' : '.$e->summary.'<br />';
                }
              } else {
                $out = '';
              }
              if ($player->initiative->count()>0) {
                $out02 = '';
                foreach($player->initiative as $index=>$e) {
                  $out02 .= '- '.strftime("%d/%m", $e->date).' : '.$e->summary.'<br />';
                }
              } else {
                $out02 = '';
              }
              echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Travail supplémentaire : extra-homework, personal initiative, underground training..."></span> Personal motivation :';
              echo ' <span data-toggle="tooltip" data-html="true" title="'.$out.'"> ['.$player->extraHk->count().' extra - </span>';
              echo ' <span data-toggle="tooltip" data-html="true" title="'.$out02.'">'.$player->initiative->count().' initiatives - </span>';
              echo ' <span class="">'.$player->ut->count().' UT session]</span>';
              echo ' ⇒ ';
              echo  '<span data-toggle="tooltip" title="Compétence SACoche : Je prend une initiative particulière." class="label label-'.$class.'">'.$player->motivation.'</span> ';
              echo '</p>';
              
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
              echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Soucis avec l\'attitude"></span> Attitude problems :';
              $attPb = $disobedience->count()+$ambush->count()+$noisy->count();
              echo ' <span> ['.$attPb.' problems - </span>';
              echo ' <span>'.$late->count().' slow moves]</span>';
              echo ' ⇒ ';
              if ($pb->count() == 0) {
                echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève." class="label label-success">VV</span>';
              } else {
                echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève.">Ask your teacher.</span>';
              }
              echo '</p>';
              ?>
            </div>
            <div class="panel-footer text-right">
            <p class=""><?php echo '<a href="'.$homepage->url.'report_generator/singlePlayer/'.$player->id.'/'.$currentPeriod->id.'/?sort=title">[ See my report <i class="glyphicon glyphicon-file"></i> ]</a>&nbsp;&nbsp;'.$officialPeriod->title; ?>  : from <?php echo date("F j, Y", $officialPeriod->dateStart) ?> to <?php echo date("F j, Y", $officialPeriod->dateEnd) ?></p>
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
          <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="lastEvents">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
          </div>
        </div>
  </div>

</div>

<?php
  include("./foot.inc"); 
?>
