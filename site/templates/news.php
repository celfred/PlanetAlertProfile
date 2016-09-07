<?php

  include("./head.inc"); 

  $totalPlaces = $pages->find("template=place, name!=places");
  $allPlayers = $pages->find("template=player, name!=test");

  // Display Personal Mission Analyzer if user is logged in
  if ($user->isLoggedin() && $user->isSuperuser()==false) {
    $helmet = $player->equipment->get("name=memory-helmet");
    if ($helmet->id) {
      echo pma($pages->get("login=$user->name"));
    }
  }

  // Display team scores
  echo '<div class="row">';
    displayScores($allTeams);
  echo '</div>';
  
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
          $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
          $query->execute();
          $totalNbUniqueVisitors = $query->fetchColumn();
          // Get total # of logged players during the current school year
          $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
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
              $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> ['.$player->playerTeam.']</li>';
            }
            $stats .= '</ul>';
          }
          if ( count($yesterdaysPlayers) > 0 ) {
            $stats .= '<span>Yesterday\'s players : </span>';
            $stats .= '<ul class="list-inline list-unstyled">';
            foreach($yesterdaysPlayers as $r) {
              // Get player's name
              $login = $r['username'];
              $player = $pages->get("template='player', login=$login");
              $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> ['.$player->playerTeam.']</li>';
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
      $newsAdmin = $pages->get('/newsboard')->children('publish=1')->sort('-date');
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
              echo 'Official Announcement : '.$n->title;
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
                echo '<li class="">';
                echo date("F j (l)", $n->date).' : ';
                echo '<span>';

                switch ($n->task->category->name) {
                case 'place' :
                if ($n->refPage->template == 'place') {
                  echo '<span class="">New place for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  echo '<span class="">New people for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                }
                  break;
                case 'shop' : echo '<span class="">New equipment for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                  break;
                case 'attitude' : echo '<span class="">Generous attitude from <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                  break;
                case 'homework' : echo '<span class="">Penalty for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
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
        // Get player's indicators
        $player = $pages->get("template=player, login=$user->name");
        //echo '<h2><img src="'.$player->avatar->getThumb('thumbnail').'" alt="avatar" /> '.$player->title.' ['.$player->playerTeam.']</h2>';
        echo '<div class="well">';
          echo '<span class="label label-success">Your Karma : '.$player->karma.'</span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-default" data-toggle="tooltip" title="Level">'.$player->level.'<span class="glyphicon glyphicon-signal"></span></span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-default" data-toggle="tooltip" title="XP">'.$player->XP.'<img src="'.$config->urls->templates.'img/star.png" alt="" /></span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-default" data-toggle="tooltip" title="HP">'.$player->HP.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-default" data-toggle="tooltip" title="GC">'.$player->GC.'<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" /></span>';
          echo '&nbsp;&nbsp;';
          $freeElements = $player->places->count()+$player->people->count();
          echo '<span class="label label-info" data-toggle="tooltip" title="Free places/people">'.$freeElements.'<img src="'.$config->urls->templates.'img/globe.png" alt="" /></span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-info" data-toggle="tooltip" title="Equipment">'.$player->equipment->count().'<span class="glyphicon glyphicon-wrench"></span></span>';
          echo '&nbsp;&nbsp;';
          if ($player->donation == false) {$player->donation = 0; }
          echo '<span class="label label-default" data-toggle="tooltip" title="Donated">'.$player->donation.'<img src="'.$config->urls->templates.'img/heart.png" alt="" /></span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-primary" data-toggle="tooltip" title="Underground Training">'.$player->underground_training.' UT</span>';
          echo '&nbsp;&nbsp;';
          echo '<span class="label label-primary" data-toggle="tooltip" title="Fighting Power">'.$player->fighting_power.' FP</span>';
        echo ' </div>';

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
            $vv = 0;
            $v = 0;
            $r = 0;
            $rr = 0;
            $abs = 0;
            $out = '';
            $allParticipation = $allEvents->find("task.category.name=participation");
            $nbPart = $allParticipation->count();
            $rr = taskCount('communication-rr', $allEvents);
            echo '<p>';
            echo '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Participation en classe"></span> Communication : ';
            foreach($allParticipation as $index=>$e) {
              switch ($e->task->name) {
                case 'communication-rr' :
                  $out .= '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $e->date).'">RR</span>';
                  $rr += 1;
                  break;
                case 'communication-r' :
                  $out .= '<span class="participation label label-danger" data-toggle="tooltip" title="'.strftime("%d/%m", $e->date).'">R</span>';
                  $r += 1;
                  break;
                case 'communication-v' : 
                  $out .= '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $e->date).'">V</span>';
                  $v += 1;
                  break;
                case 'communication-vv' :
                  $out .= '<span class="participation label label-success" data-toggle="tooltip" title="'.strftime("%d/%m", $e->date).'">VV</span>';
                  $vv += 1;
                  break;
                case 'abs' : 
                  $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $e->date).'">-</span>';
                  $abs += 1;
                  $nbPart -= 1;
                  $listAbsent .= '- '.strftime("%d/%m", $e->date).'<br />';
                  break;
                case 'absent' : 
                  $out .= '<span class="participation label label-info" data-toggle="tooltip" title="'.strftime("%d/%m", $e->date).'">-</span>';
                  $abs += 1;
                  $nbPart -= 1;
                  $listAbsent .= '- '.strftime("%d/%m", $e->date).'<br />';
                  break;
                default: break;
              }
              if (in_array($index, [10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200])) $out .= '<br />';
            }
            // Player's average and stats
            $percentPresent = (int) round((100*$nbPart)/$allParticipation->count());
            if ($percentPresent >= 30) {
              // Participation quality formula
              $ratio = (int) round(((($vv*2)+($v*1.6)-$rr)*100)/($nbPart*2));
              if ( $ratio < 0) { $ratio = 0; }
            } else {
              $ratio = 'absent';
            }
            echo $out;
            echo ' ⇒ ';
          if (is_int($ratio)) {
            if ($ratio >= 80) {
              echo '<span data-toggle="tooltip" title="Moyenne sur la période" class="label label-success">VV</span>';
            }
            if ($ratio < 80 && $ratio >= 55) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je participe régulièrement." class="label label-success">V</span>';
            }
            if ($ratio < 55 && $ratio >= 35) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je participe régulièrement." class="label label-danger">R</span>';
            }
            if ($ratio < 35 && $ratio >= 0) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je participe régulièrement." class="label label-danger">RR</span>';
            }
          } else {
            if ($ratio === 'absent') {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je participe régulièrement." class="label label-default">NN</span>';
            }
          }
          echo ' [<span data-toggle="tooltip" title="Participation positive">'.($v+$vv).' <i class="glyphicon glyphicon-thumbs-up"></i></span> <span data-toggle="tooltip" title="Participation négative">'.($r+$rr).' <i class="glyphicon glyphicon-thumbs-down"></i></span>]';
            // Homework stats
            $noHomework = $allEvents->find("task.name=no-homework, sort=-date");
            $halfHomework = $allEvents->find("task.name=homework-half-done, sort=-date");
            $noSignature = $allEvents->find("task.name=signature, sort=-date");
            $pb = $noHomework->count()+(($halfHomework->count+$noSignature->count())*0.5);
            if ($noHomework->count()>0) {
              $out = '<ul>';
              foreach($noHomework as $index=>$e) {
                $out .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->summary.'</li>';
              }
              $out .= '</ul>';
            } else { $out='';}
            if ($halfHomework->count()>0) {
              $out02 = '<ul>';
              foreach($halfHomework as $index=>$e) {
                $out02 .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->summary.'</li>';
              }
              $out02 .= '</ul>';
            } else { $out02='';}
            if ($noSignature->count()>0) {
              $out03 = '<ul>';
              foreach($noSignature as $index=>$e) {
                $out03 .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->summary.'</li>';
              }
              $out03 .= '</ul>';
            } else { $out03='';}
            echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Exercices non faits ou à moitié faits"></span> Training problems :';
            echo ' <span class="">'.$pb.'</span>';
            echo ' [<span data-toggle="tooltip" data-html="true" title="'.$out.'">'.$noHomework->count().' Hk</span> - <span data-toggle="tooltip" data-html="true" title="'.$out02.'">'.$halfHomework->count().' HalfHk</span> - <span data-toggle="tooltip" data-html="true" title="'.$out03.'">'.$noSignature->count().' notSigned</span>]';
            echo ' ⇒ ';
            if ($pb >= 3) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail fait à la maison." class="label label-danger">RR</span>';
            }
            if ($pb < 3 && $pb>1) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail fait à la maison." class="label label-danger">R</span>';
            }
            if ($pb > 0 && $pb<1) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail fait à la maison." class="label label-success">V</span>';
            }
            if ($pb == 0) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je peux présenter mon travail fait à la maison." class="label label-success">VV</span>';
            }
            echo '</p>';
            // Forgotten material
            $noMaterial = $allEvents->find("task.name=material, sort=-date");
            if ($noMaterial->count()>0) {
              $out = '<ul>';
              foreach($noMaterial as $index=>$e) {
                $out .= '<li>'.strftime("%d/%m", $e->date).'</li>';
              }
              $out .= '</ul>';
            } else { $out='';}
            echo '<p>';
            echo '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Affaires oubliées"></span> Forgotten material : ';
            echo '<span data-toggle="tooltip" data-html="true" title="'.$out.'">'.$noMaterial->count().'</span>';
            echo ' ⇒ ';
            if ($pb >= 3) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-danger">RR</span>';
            }
            if ($pb < 3 && $pb>1) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-danger">R</span>';
            }
            if ($pb > 0 && $pb<1) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">V</span>';
            }
            if ($pb == 0) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : J\'ai mon matériel." class="label label-success">VV</span>';
            }
            echo '</p>';

            // Extra-hk
            $extra = $allEvents->find("task.name=extra-homework|very-extra-homework|personal-initiative");
            $initiative = $allEvents->find("task.name=personal-initiative");
            $ut = $allEvents->find("task.name=ut-action-v|ut-action-vv");
            $all = $extra->count()+$initiative->count()+$ut->count();
            if ($extra->count()>0) {
              $out = '<ul>';
              foreach($extra as $index=>$e) {
                $out .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->summary.'</li>';
              }
              $out .= '</ul>';
            } else {
              $out = '';
            }
            if ($initiative->count()>0) {
              $out02 = '<ul>';
              foreach($initiative as $index=>$e) {
                $out02 .= '<li>'.strftime("%d/%m", $e->date).' : '.$e->summary.'</li>';
              }
              $out02 .= '</ul>';
            } else {
              $out02 = '';
            }
            echo '<p><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Travail supplémentaire : extra-homework, personal initiative, underground training..."></span> Personal motivation :';
            echo ' <span data-toggle="tooltip" data-html="true" title="'.$out.'"> ['.$extra->count().' extra - </span>';
            echo ' <span data-toggle="tooltip" data-html="true" title="'.$out02.'">'.$initiative->count().' initiative - </span>';
            echo ' <span class="">'.$ut->count().' UT session(s)]</span>';
            echo ' ⇒ ';
            if ($all-$ut->count()>=8 || $ut->count()>=50) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je prends une initiative particulière." class="label label-success">VV</span>';
            } if ($all-$ut->count()>=3 || $ut->count()>=20 && $ut->count()<50) {
              echo '<span data-toggle="tooltip" title="Compétence SACoche : Je prends une initiative particulière." class="label label-success">V</span>';
            } if ($all-$ut->count()<3 && $ut->count()<20) {
              echo '<soan data-toggle="tooltip" title="Compétence SACoche : Je prends une initiative particulière.">No bonus for the moment.</span>';
            }
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
          <p>To see your complete history, go the the <a href="<?php echo $pages->get('/players')->url.$player->playerTeam.'/'.$player->name; ?>">'My Profile'</a> page.</p>
          </div>
        </div>

      <?php 
      }


      // Last 15 public news
      $excluded = $pages->find('name=test|admin');
      $news = $pages->find("template=event, sort=-date, limit=15, task=free|buy|ut-action-v|ut-action-vv, has_parent!=$excluded");
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
                  echo '<span class="">New place for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                }
                if ($n->refPage->template == 'people') {
                  echo '<span class="">New people for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                }
                break;
              case 'shop' : echo '<span class="">New equipment for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'attitude' : echo '<span class="">Generous attitude from <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
              case 'individual-work' : echo '<span class="">Underground Training for <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> ['.$currentPlayer->playerTeam.'] : '.html_entity_decode($n->summary).'</span>';
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
