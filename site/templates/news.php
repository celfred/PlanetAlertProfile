<?php namespace ProcessWire;
  include("./head.inc"); 

  $out = '';
  
  // Display team scores
  $out .= '<div class="row text-center">';
    if (!($allTeams->count() == 1 && $allTeams->eq(0)->name == 'no-team')) { // Means Just no-team
      showScores($allTeams);
    }
  $out .= '</div>';

  $out .= '<div class="row">';
  if ($user->hasRole('teacher')) { // Teacher's Newsboard 
    // Teacher's Admin board  (to prepare in-class papers to be given to players)
    $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">'.__("Teacher's work").'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body ajaxContent" data-priority="1" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="admin-work">';
      $out .= '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
      $out .= '</div>';
    $out .= '</div>';
  }
 
  if ($user->hasRole('player') || !$user->isLoggedin()) { // Scoreboards for players or guests (multi-columns)
    $out .= '<div class="col-sm-6">'; // Column 1
  }
      if ($user->hasRole('player')) {
        if ($player->team->is("name!=no-team")) {
          /* $teamPlayers = $allPlayers->filter("team=$player->team"); // Limit to logged player's team */
          $teamPlayers = $pages->find("parent.name=players, team=$player->team"); // Limit to logged player's team
          $team = '['.$player->team->title.']';
        } else {
          /* $teamPlayers = $pages->find("parent.name=players, template=player, team.name=no-team"); */
          $teamPlayers = $pages->findMany("parent.name=players, template=player");
          $team = '';
        }
      } else {
        $player = false;
        $teamPlayers = $pages->find("parent.name=players, template=player");
        $team = '';
      }

      if ($user->hasRole('player')) {
        // Get players' last 10 events
        $allEvents = $player->find("parent.name=history, sort=-created, limit=10");
        $out .= '<div id="" class="news panel panel-primary">';
          $out .= '<div class="panel-heading">';
            $out .= '<h4 class="panel-title">';
              if ($player->avatar) {
                $out .= '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="avatar" />';
              }
              $out .= __('Last 10 events in your personal history');
            $out .= '</h4>';
          $out .= '</div>';
          $out .= '<div class="panel-body ajaxContent" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="last10&playerId='.$player->id.'" data-priority="1">';
          $out .= '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
          $out .= '</div>';
          $out .= '<div class="panel-footer text-right">';
          $out .= '<p>'.__("To see your complete history, go to").' ';
          $out .= '<a href="'.$pages->get('/players')->url.$player->team->name.'/'.$player->name.'">'.__("My Profile page").'</a></p>';
          $out .= '</div>';
        $out .= '</div>';
        if ($player->team->is("name!=no-team")) {
          // Team News (Free/Buy actions during last 5 days)
          $news = new PageArray();
          $today = new \DateTime("today");
          $interval = new \DateInterval('P5D');
          $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
          $news = $teamPlayers->get("children.name=history")->find("date>=$limitDate,task.name~=free|buy|ut-action|fight, refPage!=NULL, inClass=0")->sort("-date");
          $out .= '<div id="" class="board panel panel-primary">';
            $out .= '<div class="panel-heading">';
            $out .= '<h4 class=""><span class="label label-primary">'.__('Team News (last 5 days)').'</span></h4>';
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
      }
          
      if ($user->hasRole('player') || !$user->isLoggedin()) { // Not for teachers and superuser
          // Scoreboards
          $out .= '<div class="row">'; // Nested 2 columns
            $out .= '<div class="col-sm-6">'; // Subcolumn 1
              // Most active
              $out .= '<div class="board panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=yearlyKarma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/star.png" alt="" /> '.$team.' '.__("Most Active Players").'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-yearlyKarma");
              $out .= '  </div>';
              $out .= '</div>';
              
              if ($user->hasRole('player') && $player->team->name != 'no-team') {
                // Most active Groups
                $out .= '<div id="" class="panel panel-success">';
                $out .= '  <div class="panel-heading">';
                $out .= '  <h4 class="panel-title">'.$team.' '.__("Most Active Groups").'</h4>';
                $out .= '  </div>';
                $out .= '  <div class="panel-body">';
                $groups = displayTeamScoreboard($teamPlayers, $player, "group");
                if (strlen($groups) == 9) { // Means empty <ol></ol>
                  $out .= '<p class="text-center">'.__("No groups are set").'</p>';
                } else {
                  $out .= $groups;
                }
                $out .= '  </div>';
                $out .= '</div>';
              }

              // Most influential
              $out .= '<div id="" class="panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/star.png" alt="" /> '.$team.' '.__("Most influential").'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-karma");
              $out .= '  </div>';
              $out .= '</div>';

              // Best UT
              $out .= '<div id="" class="panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title"><i class="glyphicon glyphicon-headphones"></i> '.$team.' '.__("Most Trained").'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-underground_training");
              $out .= '  </div>';
              $out .= '</div>';
            $out .= '</div>'; // /subcolumn 1

            $out .= '<div class="col-sm-6">'; // Subcolumn 2
              if ($user->hasRole('player') && $player->team->is("name!=no-team")) {
                // Help needed
                $out .= '<div id="" class="board panel panel-danger">';
                  $out .= '<div class="panel-heading">';
                    $dangerPlayers = $allPlayers->find('(coma=1), (HP<=15>)')->sort("coma, HP");
                    $out .= '<p class="panel-title">'.$team.' '.__("Help needed!").'</p>';
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
                      $out .= '<p class="text-center">'.__("Congratulations ! No player with HP<15 !").'</p>';
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
              $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> '.$team.' '.__("Greatest # of places").'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-places.count");
              $out .= '  </div>';
              $out .= '</div>';

              // Greatest # of people if needed
              if (($user->hasRole('player') && $player->rank && $player->rank->is("index>=8")) || !$user->isLoggedin()) {
                $out .= '<div id="" class="panel panel-success">';
                $out .= '  <div class="panel-heading">';
                $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
                $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/globe.png" alt="" /> '.$team.' '.__("Greatest # of people").'</h4>';
                $out .= '  </div>';
                $out .= '  <div class="panel-body">';
                $out .= displayTeamScoreboard($teamPlayers, $player, "-people.count");
                $out .= '  </div>';
                $out .= '</div>';
              }

              // Best warrior
              $out .= '<div id="" class="panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/flash.png" alt="" /> '.$team.' '.__("Best warriors").'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-fighting_power");
              $out .= '  </div>';
              $out .= '</div>';

              // Best donators
              $out .= '<div id="" class="panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title"><img src="'.$config->urls->templates.'img/heart.png" alt="" /> '.$team.' '.__("Best donators").'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displayTeamScoreboard($teamPlayers, $player, "-donation");
              $out .= '  </div>';
              $out .= '</div>';
            $out .= '</div>'; // /subcolumn 2
          $out .= '</div>'; // /row
        $out .= '</div>'; // /col-sm-6 /Column 1
        $out .= '<div class="col-sm-6">'; // Column 2
      }

    // Admin announcements
    if (!$user->isLoggedin()) { // Guests get public news only
      $newsAdmin = $pages->get("/newsboard")->children("publish=1, public=1")->sort("-date");
    } else {
      if ($user->hasRole('teacher') || $user->isSuperuser()) { // Teachers and Admin gets all published news
        $newsAdmin = $pages->get("/newsboard")->children("publish=1")->sort("-date");
      }
      if ($user->hasRole('player')) { // Player gets public and ranked news
        $newsAdmin = $pages->get("/newsboard")->children("publish=1, public=0|1, ranks=''|$player->rank")->sort("-date");
      }
    }
    if ($newsAdmin->count() > 0) {
      foreach($newsAdmin as $n) {
        $out .= '<div id="'.$n->id.'" class="news panel panel-success">';
        $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">';
        $logo = $homepage->photo->eq(0)->size(40,40); 
        $out .= '<img src="'.$logo->url.'" alt="" /> ';
        $out .= date("M. d, Y", $n->created);
        $out .= ' - ';
        $out .= $n->title;
        if ($n->public == 0) {
          if ($n->ranks) {
            foreach ($n->ranks as $r) {
              $out .= ' <span class="label label-default">'.$r->title.'</span>';
            }
          }
        } else {
          $out .= ' <span class="label label-default">Public News</span>';
        }
        $out .= '<button type="button" class="close" data-id="#'.$n->id.'" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $out .= '</h4>';
       $out .= '</div>';
       $out .= '<div class="panel-body">';
           $out .= $n->body;
           $out .= '<br />';
           if ($user->language->name == 'default') {
            $n->of(false);
            if ($n->body->getLanguageValue($french) != '') {
              $out .= '<a class="" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
              $out .= '<div class="collapse" id="collapseDiv">';
              $out .= '<div class="well">';
                $out .= nl2br($n->body->getLanguageValue($french));
              $out .= '</div>';
              $out .= '</div>';
            }
           }
       $out .= '</div>';
          if ($user->isSuperuser()) {
       $out .= '<div class="panel-footer text-right">';
        $out .= '<label for="unpublish_'.$n->id.'"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> Unpublish from Newsboard<span id="feedback"></span></label>';
       $out .= '</div>';
         }
      $out .= '</div>';
      }
    }

    // Work statistics for logged player if team and period
    if ($user->hasRole('player')) {
      if ($player->team->name != 'no-team') {
        $currentPeriod = $player->team->periods;
        if ($currentPeriod != false) {
          $dateStart = $currentPeriod->dateStart;
          $dateEnd = $currentPeriod->dateEnd;
          // Check headTeacher's customization
          $headTeacher = getHeadTeacher($player);
          $mod = $currentPeriod->periodOwner->get("singleTeacher=$headTeacher");
          if ($mod->id) {
            $mod->dateStart != '' ? $dateStart = $mod->dateStart : '';
            $mod->dateEnd != '' ? $dateEnd = $mod->dateEnd : '';
          }
          $out .= '<div id="" class="news panel panel-primary">';
            $out .= '<div class="panel-heading">';
            $out .= '<h4 class="panel-title">';
              if ($player->avatar) { $out .= '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="avatar" />'; }
              $out .= __('Work statistics on current period').' ('.$currentPeriod->title.') <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Suivi du travail sur la période (pour SACoche). Si la période n\'est pas terminée, tu peux encore améliorer tes résultats !"></span>';
            $out .= '</h4>';
            $out .= '</div>';
          $out .= '<div class="panel-body ajaxContent" data-href="'.$pages->get('name=ajax-content')->url.'" data-priority="1" data-id="work-statistics&playerId='.$player->id.'&periodId='.$currentPeriod->id.'">';
              $out .= '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
          $out .= '</div>';
          $out .= '<div class="panel-footer text-right">';
          $out .= '<p class=""><a href="'.$homepage->url.'report_generator/singlePlayer/'.$player->id.'/'.$currentPeriod->id.'/?sort=title">[ See my report <i class="glyphicon glyphicon-file"></i> ]</a>&nbsp;&nbsp;'.$currentPeriod->title.': from '.date("M. j, Y", $dateStart).' to '.date("M. j, Y", $dateEnd).'</p>';
            $out .= '</div>';
          $out .= '</div>';
        } else {
          $out .= '<div id="" class="news panel panel-primary">';
            $out .= '<div class="panel-heading">';
              $out .= '<h4 class="panel-title">No work statistics !</h4>';
            $out .= '</div>';
          $out .= '</div>';
        }
      }
    }
    
    // Recent public news
    $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">'.__("Recent public activity").'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body ajaxContent" data-href="'.$pages->get('name=ajax-content')->url.'" data-id="lastEvents">';
        $out .= '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
      $out .= '</div>';
    $out .= '</div>';

  if ($user->hasRole('player') || !$user->isLoggedin()) { // Not for teachers and superuser
    $out .= '</div>'; // /Column 2
  }

  $out .= '</div>'; // /div.row

  echo $out;

  include("./foot.inc"); 
?>
