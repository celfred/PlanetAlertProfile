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
  $allConcernedPlayers = $allPlayers; // Already limited to teacher's students TODO : Check this for players !
  if ($user->hasRole('teacher')) { // Teacher's Newsboard 
    // Teacher's Admin board  (to prepare in-class papers to be given to players)
    $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">'.__("Teacher's work").'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
        $today = new \DateTime("today");
      t();
        $news = $pages->find("parent.name=history, publish=1");
      bd(t());
        $news->filter("has_parent=$allConcernedPlayers")->sort('-created');
        $out .= '<div class="col-sm-6">';
        if ($news->count() > 0) {
          $out .= '<p class="label label-primary">'.__("Papers to be given").'</p>';
          $out .= '<ul class="list-unstyled">';
          foreach($news as $n) {
            $currentPlayer = $n->parent('template=player');
            if ($currentPlayer->team->name == 'no-team') { 
              $team = '';
              $name = $currentPlayer->title.' '.$currentPlayer->lastName;
            } else { 
              $team = '['.$currentPlayer->team->title.']';
              $name = $currentPlayer->title;
            }
            $out .= '<li class="">';
            $out .=strftime("%d %b (%A)", $n->date).' : ';
            $out .= '<span>';
            switch ($n->task->name) {
              case 'free' : 
                if ($n->refPage->template == 'place') {
                  $out .= '<span class="">'.__("New place for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : <a href="'.$n->refPage->url.'?pages2pdf=1&id='.$n->refPage->id.'">'.html_entity_decode($n->summary).'</a></span>';
                }
                if ($n->refPage->template == 'people') {
                  $out .= '<span class="">'.__("New people for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : <a href="'.$n->refPage->url.'?pages2pdf=1&id='.$n->refPage->id.'">'.html_entity_decode($n->summary).'</a></span>';
                }
                break;
              case 'buy' :
                $out .= '<span class="">'.__("New equipment for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'penalty' :
                $out .= '<span class="">'.__("Penalty for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'fight-vv' :
                $out .= '<span class="">'.__("Successful fight for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : <a href="'.$pages->get("name=monsters")->url.'?id='.$n->refPage->id.'&thumbnail=1&pages2pdf=1">'.$sanitizer->entities($n->summary).'</a></span>';
                break;
              default : $out .= $n->task->name. ': '.__("todo");
            }
            $out .= '</span>';
            $out .= ' <label for="unpublish_'.$n->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> '.__("Unpublish").'</label>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("Nothing to do.").'</p>';
        }
        $out .= '</div>';
        $out .= '<div class="col-sm-6">';
        $unusedConcerned = $allConcernedPlayers->find("usabledItems.count>0")->sort("-team.name, name");
        $out .= '<p class="label label-primary">'.__("Potion Planner").'</p>';
        if ($unusedConcerned->count() > 0) {
          $out .= '<ul class="list-unstyled">';
          foreach ($unusedConcerned as $p) {
            foreach ($p->usabledItems as $item) {
                $historyPage = $p->get("name=history")->find("refPage=$item")->last();
                if ($historyPage) {
                  $out .= '<li>';
                  // Find # of days compared to today
                  $date2 = new \DateTime(date("Y-m-d", $historyPage->date));
                  $interval = $today->diff($date2);
                  if ($interval->days > 21) {
                    $out .= ' <span class="badge">!</span> ';
                  }
                  if ($historyPage->refPage->is("name!=memory-potion")) {
                    $out .= '<span>'.$p->title.' ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)</span>';
                    $out .= ' <label for="unpublish_'.$historyPage->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> '.__("used today").'</label>';
                  } else {
                    $successId = $pages->get("template=memory-text, id=$historyPage->linkedId")->task->id;
                    $failedId = $pages->get("name=solo-r")->id;
                    $out .= '<span>'.$p->title.' ['.$p->team->title.'] : '.$historyPage->summary.' (bought '.$interval->days.' days ago)</span>';
                    $out .= ' <button class="ajaxBtn btn btn-xs btn-success" data-type="memory" data-result="good" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask&type=memory&playerId='.$p->id.'&historyPageId='.$historyPage->id.'&taskId='.$successId.'"><i class="glyphicon glyphicon-thumbs-up"></i></button>';
                    $out .= ' <button class="ajaxBtn btn btn-xs btn-danger" data-type="memory" data-result="bad" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask&type=memory&playerId='.$p->id.'&historyPageId='.$historyPage->id.'&taskId='.$failedId.'"><i class="glyphicon glyphicon-thumbs-down"></i></button>';
                  }
                  $out .= '</li>';
                } else { // Old unused potions
                  $historyPage = $p->get("name~=history")->find("refPage=$item")->last();
                  // Find # of days compared to today
                  $date2 = new \DateTime(date("Y-m-d", $historyPage->date));
                  $interval = $today->diff($date2);
                  $out .= '<li>';
                  $out .= ' <span class="badge">!</span> ';
                  $out .= '<span>'.$p->title.' '.$p->lastName.' ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)</span>';
                  $out .= ' <label for="unpublish_'.$historyPage->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> '.__("remove").'</label>';
                  $out .= '</li>';
                }
            }
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("No Potion to be used.").'</p>';
        }
        $book = $pages->get("name=book-knowledge");
        $pendings = $book->pending;
        $pendings->filter("player=$allConcernedPlayers");
        $out .= '<hr />';
        $out .= '<p class="label label-primary">'.__("Copy work").'</p>';
        if (count($pendings) > 0) {
          $out .= '<ul class="list-unstyled">';
          foreach ($pendings as $p) {
            $out .= '<li class="">';
            // Find # of days compared to today
            $date2 = new \DateTime(date("Y-m-d", $p->date));
            $interval = $today->diff($date2);
            if ($interval->days > 21) {
              $out .= ' <span class="badge">!</span> ';
            }
            $out .= '<span>'.$p->player->title.' ['.$p->player->team->title.'] : '.$p->refPage->title.' (warning '.$interval->days.' days ago)</span>';
            $out .= ' <label for="unpublish_'.$p->id.'" class="label label-default"><input type="checkbox" id="unpublish_'.$p->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedPending='.$p->id.'" /> validated today</label>';
            $out .= ' <a href="'.$pages->get('name=submitforms')->url.'?form=deleteNotification&usedPending='.$p->id.'" class="del">[Delete]</a>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("No lessons to be validated.").'</p>';
        }
        $out .= '</div>';
      $out .= '</div>';
    $out .= '</div>';
  }
 
  if ($user->hasRole('player') || !$user->isLoggedin()) { // Scoreboards for players or guests (multi-columns)
    $out .= '<div class="col-sm-6">'; // Column 1
  }
      if ($user->hasRole('player')) {
        if ($player->team->is("name!=no-team")) {
          $teamPlayers = $allPlayers->find("team=$player->team"); // Limit to logged player's team
          $team = '['.$player->team->title.']';
        } else {
          $teamPlayers = $pages->find("parent.name=players, template=player, name!=test");
          $team = '';
        }
        if ($player->GC > 80) {
          $helpAlert = true;
          $helpTitle = sprintf(__("You have %dGC !"), $player->GC);
          $helpMessage = '<h4>'.__("Why not use them (free item, buy a potion, help a friend...) ?").'</h4>';
        }
        include("./helpAlert.inc.php"); 
      } else {
        $player = false;
        $teamPlayers = $pages->find("parent.name=players, template=player, name!=test");
        $team = '';
      }

      if ($user->hasRole('player')) {
        // Get players' last 10 events
        t('01');
        $allEvents = $player->find("parent.name=history, sort=-created, limit=10");
        bd(t('01'));
        $out .= '<div id="" class="news panel panel-primary">';
          $out .= '<div class="panel-heading">';
            $out .= '<h4 class="panel-title">';
              if ($player->avatar) {
                $out .= '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="avatar" />';
              }
              $out .= __('Last 10 events in your personal history');
            $out .= '</h4>';
          $out .= '</div>';
          $out .= '<div class="panel-body">';
          $out .= '<ul class="list-unstyled">';
            if ($allEvents->count() > 0) {
              foreach ($allEvents as $event) {
                $event->task = checkModTask($event->task, $headTeacher);
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
                $out .=strftime("%d %b (%A)", $event->date).' : ';
                if ($className == 'negative') {
                  $out .= '<span data-toggle="tooltip" title="HP" class="badge badge-warning">'.$sign.$event->task->HP.'HP</span> ';
                }
                $out .= $sanitizer->markupToText($event->task->title);
                if ($event->summary != '') {
                  $out .= ' ['.$sanitizer->markupToText($event->summary).']';
                }
                $out .= '</li>';
              };
            } else {
              $out .= __('No personal history yet...');
            }
          $out .= '</ul>';
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
          t('01');
          $news = $teamPlayers->get("children.name=history")->find("date>=$limitDate,task.name~=free|buy|ut-action|fight, refPage!=NULL, inClass=0")->sort("-date");
          bd(t('01'));
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
                $out .= '<p>'.__('No recent news.').'</p>';
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
                $allGroups = groupScores($player->team);
                if (isset($allGroups)) {
                  $out .= '<ol class="">';
                  foreach($allGroups as $group) {
                    if (isset($player) && $player->group == $group) {
                      $focus = 'focus';
                    } else {
                      $focus = '';
                    }
                    $out .= '<li>';
                    $out .= '<p data-toggle="tooltip" data-html="true" title="'.$group->members.'" onmouseenter="$(this).tooltip(\'show\');">';
                    $out .= '<span class="'.$focus.'">'.$group->title.'</span>';
                    $out .= ' <span class="badge">'.$group->karma.' K</span>';
                    // Display stars for bonus (filled star = 5 empty stars, 1 star = 1 free element for each group member)
                    if ($group->nbBonus > 0) {
                      $out .= '&nbsp;&nbsp;&nbsp;<span class="glyphicon glyphicon-star"></span>';
                      $out .= '<span class="badge">'.$group->nbBonus.'</span>';
                    }
                    $out .= '</p>';
                    $out .= '</li>';
                  }
                  $out .= '</ol>';
                } else {
                  $out .= '<p class="text-center">'.__("No groups are set").'</p>';
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
              $out .= displayTeamScoreboard($teamPlayers, $player, "-reputation");
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
                    $dangerPlayers = $allPlayers->find('(coma=1), (HP<=15)')->sort("coma, HP");
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

    // Work statistics for logged player if team, period and rank>6
    if ($user->hasRole('player')) {
      if ($player->team->name != 'no-team' && $player->team->rank->is("index>=6")) {
        $currentPeriod = $player->team->periods;
        if ($currentPeriod != false) {
          $dateStart = $currentPeriod->dateStart;
          $dateEnd = $currentPeriod->dateEnd;
          // Check headTeacher's customization
          $headTeacher = getHeadTeacher($player);
          $mod = $currentPeriod->periodOwner->get("singleTeacher=$headTeacher");
          if (isset($mod)) {
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
          $out .= '<p class=""><a href="'.$pages->get("name=reports")->url.'all/'.$player->id.'/'.$currentPeriod->id.'/?sort=title">[ See my report <i class="glyphicon glyphicon-file"></i> ]</a>&nbsp;&nbsp;'.$currentPeriod->title.': from '.date("M. j, Y", $dateStart).' to '.date("M. j, Y", $dateEnd).'</p>';
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
    
    $today = new \DateTime("today");
    $interval = new \DateInterval('P30D');
    $limitDate = strtotime($today->sub($interval)->format('Y-m-d'));
    // Planet Alert news
    $adminId = $users->get("name=admin")->id;
    if ($user->isGuest()) { // Guests get admin news only
      $newItems = $pages->find("template=exercise|equipment|item|lesson, created_users_id=$adminId, published>$limitDate, sort=-published, limit=10");
    } else {
      if ($user->isSuperuser()) { // Admin gets ALL news
        $newItems = $pages->find("template=exercise|equipment|item|lesson, published>$limitDate, sort=-published, limit=10");
      }
      if ($user->hasRole('teacher')) { // Teachers get admin news + personal news
        $guestId = $users->get("name=guest"); // To avoid undetectable updated monsters
        $newItems = $pages->find("template=exercise|equipment|item|lesson, (created_users_id=$adminId, published>$limitDate), (teacher=$user, modified>$limitDate, modified_users_id!=$guestId), sort=-modified, sort=-published, limit=10");
      }
      if ($user->hasRole('player')) { // Player gets headTeacher news
        $guestId = $users->get("name=guest"); // To avoid undetectable updated monsters
        $newItems = $pages->find("template=exercise|equipment|item|lesson, (template=equipment, published>$limitDate), (created_users_id=$headTeacher->id, published>$limitDate), (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1), (teacher=$headTeacher, modified>$limitDate, modified_users_id!=$guestId), sort=-modified, sort=-published, limit=10");
      }
    }
    $extra = $newItems->getTotal() - $newItems->getLimit();
    if ($extra > 0) { $limitReached = '<li>['.$extra.' '.__("more results").']</li>'; } else { $limitReached = ''; }
    // Recent public news (30 previous days)
    $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">'.__("Recent public activity").'</h4>';
      $out .= '</div>';
      // All public news
      $out .= '<div class="panel-body">';
      $news = $pages->find("parent.name=history, public=1, date>=$limitDate, sort=-date");
      if (!$user->isSuperuser()) { // Limit to teacher's players
        $news->filter("has_parent=$allConcernedPlayers, limit=20");
      } else {
        $news->filter("limit=20");
      }
      $out .= '<h4 class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> '.__("New public activity !").'</h4>';
      if ($news->count() > 0) {
        $out .= '<ul class="list-unstyled">';
        foreach($news as $n) {
          $currentPlayer = $n->parent('template=player');
          if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
          if ($currentPlayer->avatar) {
            $thumb = $currentPlayer->avatar->size(20,20);
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$thumb->url."' alt='avatar' />";
          } else {
            $mini = '';
          }
          $out .= '<li>';
          $out .=strftime("%d %b (%A)", $n->date).' : ';
          $out .= $mini;
          $out .= ' <span>';
          switch ($n->task->name) {
            case 'free' : 
              if ($n->refPage->template == 'place') {
                $out .= '<span class="">'.__("New place for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              }
              if ($n->refPage->template == 'people') {
                $out .= '<span class="">'.__("New people for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              }
              break;
            case 'buy-pdf' :
              $out .= '<span class="">'.__("New PDF for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              break;
            case 'buy' :
              $out .= '<span class="">'.__("New equipment for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              break;
            case 'fight-v' :
            case 'fight-vv' :
              $out .= '<span class="">'.__("Monster Fight for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              break;
            case 'ut-action-v' :
            case 'ut-action-vv' :
              $out .= '<span class="">'.__("Underground Training for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              break;
            case 'remove' :
              $out .= '<span class="">'.__("Lost item for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.' : '.$n->refPage->title.'</span>';
              break;
            case 'donation' :
              $out .= '<span class="">'.__("Generous attitude from").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.'] : '.html_entity_decode($n->summary).'</span>';
              break;
            default : $out .= __("todo");
          }
          //$out .= $n->task->title. ' : ' . $n->summary;
          $out .= '</span>';
          $out .= '</li>';
        }
        $out .= '</ul>';
      } else {
        $out .= '<h4>'.__("No public news within the last 30 days... :(").'</h4>';
      }
      $out .= '</div>';
    $out .= '</div>';

  if ($user->hasRole('player') || !$user->isLoggedin()) { // Not for teachers and superuser
    $out .= '</div>'; // /Column 2
  }

  $out .= '</div>'; // /div.row

  echo $out;

  include("./foot.inc"); 
?>
