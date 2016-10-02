<?php

  include("./head.inc"); 

  $totalPlaces = $pages->find("template=place, name!=places");
  $allPlayers = $pages->find("template=player, name!=test");

  // Display team scores
  echo '<div class="row">';
    showScores($allTeams);
  echo '</div>';
  
  // Display Personal Analyzer if user is logged in
  if ($user->isLoggedin() && $user->isSuperuser()==false) {
    $player = $pages->get("login=$user->name");
    echo pma($player);
  }

?>

<div class="row">
  <div class="col-sm-4">
    <div id="" class="panel panel-success">
      <div class="panel-heading">
      <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
      <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most influential</h4>
      </div>
      <div id="karma" class="panel-body ajaxScore" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-ajax="karma">
        Loading...
      </div>
    </div>

    <div class="panel panel-success">
      <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> Greatest # of Places</h4>
      </div>
      <div id="places" class="panel-body ajaxScore" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-ajax="places">
        Loading...
      </div>
    </div>

    <div class="panel panel-success">
      <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> Greatest # of People</h4>
      </div>
      <div id="people" class="panel-body ajaxScore" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-ajax="people">
        Loading...
      </div>
    </div>

    <div class="panel panel-info">
      <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><span class="glyphicon glyphicon-flash"></span> Best warriors</h4>
      </div>
      <div id="fighting_power" class="panel-body ajaxScore" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-ajax="fighting_power">
        Loading...
      </div>
    </div>

    <div id="" class="panel panel-info">
      <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/heart.png" alt="" /> Best donators</h4>
      </div>
      <div id="donation" class="panel-body ajaxScore" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-ajax="donation">
        Loading...
      </div>
    </div>

    <div id="" class="panel panel-info">
      <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><span class="label label-primary">U.T.</span> Most trained</h4>
      </div>
      <div id="underground_training" class="panel-body ajaxScore" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-ajax="underground_training">
        Loading...
      </div>
    </div>

    <div id="" class="panel panel-success">
      <div class="panel-heading">
      <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=group"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
      <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most active groups</h4>
      </div>
      <div class="panel-body">
        <ol>
        <?php
            $groupScoreBoard = groupScoreBoard(10);
            echo $groupScoreBoard;
          ?>
        </ol>
      </div>
    </div>
  </div>

  <div class="col-sm-8">
    <?php
      // Admin is logged in, show stats
      if ($user->isSuperuser()) {
        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
          // Get current school year dates
          $period = $pages->get("template='period', name='school-year'");
          // Get today's unique logged players' names
          $query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp >= CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
          $query->execute();
          $todaysPlayers = $query->fetchAll();
          // Get yesterday's unique logged players' names
          $query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()");   
          $query->execute();
          $yesterdaysPlayers = $query->fetchAll();
          // Get total # of unique logged players during the last 7 days
          $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
          $query->execute();
          $totalNbUniqueVisitors7Days = $query->fetchColumn();
          // Get total # of logged players during the last 7 days
          $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
          $query->execute();
          $totalNbVisitors7Days = $query->fetchColumn();
          // Get total # of unique logged players during the current school year
          $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
          $query->execute();
          $totalNbUniqueVisitors = $query->fetchColumn();
          // Get total # of logged players during the current school year
          $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
          $query->execute();
          $totalNbVisitors = $query->fetchColumn();

          $stats = '<div id="stats" class="news panel panel-primary">';
          $stats .= '<div class="panel-heading">';
          $stats .= '<h5 class="panel-title">Planet Alert Statistics (started 17/09/2015)';
          $stats .= '<button type="button" class="close" data-id="#stats" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
          $stats .= '</h5>';
          $stats .= '</div>';
          $stats .= '<div class="panel-body">';
          $stats .= '<p>';
          $stats .= '&nbsp;&nbsp;&nbsp';
          $stats .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">Today : '.count($todaysPlayers).'</span>';
          $stats .= '&nbsp;&nbsp;&nbsp';
          $stats .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">Yesterday : '.count($yesterdaysPlayers).'</span>';
          $stats .= '&nbsp;&nbsp;&nbsp';
          $stats .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">Last 7 days : '.$totalNbUniqueVisitors7Days.'/'.$totalNbVisitors7Days.'</span>';
          $stats .= '&nbsp;&nbsp;&nbsp';
          $stats .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">School Year : '.$totalNbUniqueVisitors.'/'.$totalNbVisitors.'</span>';
          $stats .= '&nbsp;&nbsp;&nbsp';
          $stats .= '</p>';
          if ( count($todaysPlayers) > 0 ) {
            $stats .= '<ul class="list-inline list-unstyled">';
            $stats .= '<span>Today\'s players : </span>';
            foreach($todaysPlayers as $r) {
              // Get player's name
              $login = $r['username'];
              $player = $pages->get("template='player', login=$login");
              if ($player->team->name == 'no-team') { $team = ''; } else { $team = '['.$player->team->title.']'; }
              $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> '.$team.'</li>';
            }
            $stats .= '</ul>';
          }
          if ( count($yesterdaysPlayers) > 0 ) {
            $stats .= '<span>Yesterday\'s players : </span>';
            $stats .= '<ul class="list-inline list-unstyled">';
            foreach($yesterdaysPlayers as $r) {
              $login = $r['username'];
              $player = $pages->get("template='player', login=$login");
              if ($player->team->name == 'no-team') { $team = ''; } else { $team = '['.$player->team->title.']'; }
              $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> '.$team.'</li>';
            }
            $stats .= '</ul>';
          }
          // Link to Statistics page
          $stats .= '<p class="text-center"><a href='.$pages->get('name=statistics')->url.'>[See the complete Planet Alert statistics]</a></p>';
          $stats .= '</div>';
          $stats .= '</div>';
          echo $stats;
        } else {
          echo '<div>Localhost : No stats.</div>';
        }
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
        $news = $pages->find("template=event, sort=-created, publish=1, task=free|buy|penalty");
        if ($news->count() > 0) {
        ?>
          <div id="" class="news panel panel-primary">
            <div class="panel-heading">
              <h4 class="panel-title">
                Admin's work (papers to be given to players)
              </h4>
            </div>
            <div class="panel-body">
              <ul class="list-unstyled">
              <?php
              foreach($news as $n) {
                $currentPlayer = $n->parent('template=player');
                if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
                echo '<li class="">';
                echo date("F j (l)", $n->date).' : ';
                echo '<span>';

                switch ($n->task->category->name) {
                case 'place' :
                if ($n->refPage->template == 'place') {
                  echo '<span class="">New place for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  echo '<span class="">New people for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                  break;
                case 'shop' : echo '<span class="">New equipment for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                  break;
                case 'attitude' : echo '<span class="">Generous attitude from <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                  break;
                case 'homework' : echo '<span class="">Penalty for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                  break;
                default : echo 'todo : ';
                  break;
                }
                echo '</span>';
                echo ' <label for="unpublish_'.$n->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> Unpublish<span id="feedback"></span></label>';
                echo '</li>';
              }
              ?>
            </ul>
          </div>
        </div>
        <?php
        } else {
          echo '<span>Nothing to prepare.</span>';
        }
      }

      // User is logged in, show personal news
      if ($user->isLoggedin() && $user->isSuperuser() == false) {
        // Get current period statistics
        $officialPeriod = $pages->get("name=admin-actions")->periods;
        $allEvents = $player->child("name=history")->find("template=event, date>=$officialPeriod->dateStart, date<=$officialPeriod->dateEnd");
        ?>
        <div id="" class="news panel panel-primary">
          <div class="panel-heading">
            <h4 class="panel-title">
              <?php if ($player->avatar) { echo '<img src="'.$player->avatar->getThumb('mini').'" alt="avatar" />'; } ?>
              Work statistics on current period (<?php echo $officialPeriod->title; ?>) <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Suivi du travail sur la période (pour SACoche). Si la période n'est pas terminée, tu peux encore améliorer tes résultats !"></span>
            </h4>
          </div>
          <div class="panel-body">
            <?php
            // Participation
            $out = '';
            $allParticipation = $allEvents->find("task.category.name=participation");
            setParticipation($player, $allParticipation);
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
            setHomework($player);
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
            $late = $allEvents->find("task.name=late");
            $pb = $disobedience->count()+$ambush->count()+$late->count();
            echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Soucis avec l\'attitude"></span> Attitude problems :';
            echo ' <span class=""> ['.$disobedience->count()+$ambush->count().' problems - </span>';
            echo ' <span class="">'.$late->count().' slow moves]</span>';
            echo ' ⇒ ';
            if ($pb >= 3) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève." class="label label-danger">RR</span>';
            }
            if ($pb < 3 && $pb>1) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève." class="label label-danger">R</span>';
            }
            if ($pb > 0 && $pb<1) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève." class="label label-success">V</span>';
            }
            if ($pb == 0) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'adopte une attitude d\'élève." class="label label-success">VV</span>';
            }
            echo '</p>';
            ?>
          </div>
          <div class="panel-footer text-right">
          <p class="">End of period : <?php echo date("F j, Y", $officialPeriod->dateEnd) ?></p>
          </div>
        </div>

        <?php
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
            <ul class="list-unstyled">
            <?php
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
                  echo '<li class="'.$className.'">';
                  echo $signicon;
                  echo date("F j (l)", $event->date).' : ';
                  /* echo '<span data-toggle="tooltip" title="XP" class="badge badge-success">'.$sign.$event->task->XP.'</span><img src="'.$config->urls->templates.'img/star.png" alt="XP" /> '; */
                  /* echo '<span data-toggle="tooltip" title="GC" class="badge badge-default">'.$sign.$event->task->GC.'</span><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /> '; */
                  if ($className == 'negative') {
                    echo '<span data-toggle="tooltip" title="HP" class="badge badge-warning">'.$sign.$event->task->HP.'</span><img src="'.$config->urls->templates.'img/heart.png" alt="HP" /> ';
                  }
                  echo $event->task->title;
                  echo ' ['.$event->summary.']';
                  echo '</li>';
                };
              } else {
                echo 'No personal history yet...';
              }
            ?>
            </ul>
          </div>
          <div class="panel-footer text-right">
          <p>To see your complete history, go the the <a href="<?php echo $pages->get('/players')->url.$player->team->name.'/'.$player->name; ?>">'My Profile'</a> page.</p>
          </div>
        </div>

      <?php 
      }


      // Last 15 public news
      $excluded = $pages->find("name=test|admin");
      $included = $pages->find("name=history");
      // Find current school year date
      $schoolYear = $pages->get("template=period, name=school-year");
      $news = $pages->find("template=event, date>= $schoolYear->dateStart, sort=-date, limit=15, task=free|buy|ut-action-v|ut-action-vv, has_parent=$included, has_parent!=$excluded");
      if ($news->count() > 0) {
      ?>
        <div id="" class="news panel panel-primary">
          <div class="panel-heading">
            <h4 class="panel-title">
              Last 15 public events in Planet Alert
            </h4>
          </div>
          <div class="panel-body">
            <ul class="list-unstyled">
            <?php
            foreach($news as $n) {
              $currentPlayer = $n->parent('template=player');
              if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
              if ($currentPlayer->avatar) {
                $thumb = $currentPlayer->avatar->size(20,20);
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$thumb->url."' alt='avatar' />";
              } else {
                $mini = '';
              }
              echo '<li>';
              echo $mini;
              echo date("F j (l)", $n->date).' : ';
              echo '<span>';
              switch ($n->task->category->name) {
              case 'place' : 
                if ($n->refPage->template == 'place') {
                  echo '<span class="">New place for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  echo '<span class="">New people for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                }
                break;
              case 'shop' : echo '<span class="">New equipment for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'attitude' : echo '<span class="">Generous attitude from <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.'] : '.html_entity_decode($n->summary).'</span>';
              case 'individual-work' : echo '<span class="">Underground Training for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
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
      } else {
        echo '<h4 class="well">No public news... :(</h4>';
      }
    ?>
  </div>

</div>

<?php
  include("./foot.inc"); 
?>
