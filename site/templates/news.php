<?php namespace ProcessWire;
  include("./head.inc"); 

  if ($user->isLoggedin()) {
    if ($session->allPlayers) {
      $allPlayers = $pages->find("id=$session->allPlayers");
    } else {
      $allPlayers = getAllPlayers($user, false);
      $session->allPlayers = (string) $allPlayers;
    }
  }

  $out = '';
  // Display team scores
  $out .= '<div class="row text-center">';
    $out .= getScoresSummaries($headTeacher);
  $out .= '</div>';

  $out .= '<div class="row">';
  if ($user->hasRole('teacher')) { // Teacher's Newsboard 
    $ajaxContentUrl = $pages->get("name=ajax-content")->url;
    $out .= '<div id="showInfo" data-href="'.$ajaxContentUrl.'"></div>';
    // Teacher's Admin board  (to prepare in-class papers to be given to players)
    $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">'.__("Teacher's work").'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
        $today = new \DateTime("today");
        $news = $pages->find("parent.name=history, publish=1, task.name!=penalty|buy-pdf|inactivity");
        $news->filter("has_parent=$allPlayers")->sort("-parent.parent.team.name, -date");
        $out .= '<div class="col-sm-6">';
        $out .= '<p class="label label-primary">'.__("Papers to be given").'</p>';
        if ($news->count() > 0) {
          $out .= '<ul class="list-unstyled">';
          $previousTeam = '';
          foreach($news as $n) {
            $currentPlayer = $n->parent('template=player');
            if ($currentPlayer->team->name == 'no-team') { 
              $team = '';
              $name = $currentPlayer->title.' '.$currentPlayer->lastName;
            } else { 
              $team = $currentPlayer->team->title;
              $name = $currentPlayer->title;
            }
            if ($team != $previousTeam) {
              $out .= '<p class="label label-danger">'.$team.'</p>';
            }
            $out .= '<li class="">';
            $out .=strftime("%d %b (%A)", $n->date).' : ';
            $out .= '<span>';
            switch ($n->task->name) {
              case 'free' : 
                if ($n->refPage->template == 'place') {
                  $out .= '<span class="">'.__("New place for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> : <a href="'.$n->refPage->url.'?pages2pdf=1&id='.$n->refPage->id.'">'.html_entity_decode($n->summary).'</a></span>';
                }
                if ($n->refPage->template == 'people') {
                  $out .= '<span class="">'.__("New people for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> : <a href="'.$n->refPage->url.'?pages2pdf=1&id='.$n->refPage->id.'">'.html_entity_decode($n->summary).'</a></span>';
                }
                break;
              case 'bought' :
              case 'buy' :
                $out .= '<span class="">'.__("New equipment for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'fight-vv' :
                $out .= '<span class="">'.__("Successful fight for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> : <a href="'.$pages->get("name=monsters")->url.'?id='.$n->refPage->id.'&thumbnail=1&pages2pdf=1">'.$sanitizer->entities($n->summary).'</a></span>';
                break;
              case 'death' :
                $out .= '<span class="">'.__("Death for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> <span class="glyphicon glyphicon-thumbs-down></span>"</span>';
                break;
              default : $out .= $n->task->name. ': '.__("todo");
            }
            $out .= '</span>';
            $out .= ' <label for="unpublish_'.$n->id.'" class="btn btn-danger btn-xs"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> '.__("Unpublish").'</label>';
            $out .= '</li>';
            $previousTeam = $team;
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("Nothing to do.").'</p>';
        }

        $out .= '<p class="label label-primary">'.__("Others").'</p>';
        $others = $pages->find("parent.name=history, publish=1, task.name=penalty|buy-pdf");
        $others->filter("has_parent=$allPlayers")->sort('-created');
        if ($others->count() > 0) {
          $out .= '<ul class="list-unstyled">';
          foreach($others as $n) {
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
            $others = new PageArray();
            switch ($n->task->name) {
              case 'penalty' :
                $out .= '<span class="">'.__("Penalty for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'inactivity' :
                $out .= '<span class="">'.__("Inactivity for").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              case 'buy-pdf':
                $out .= '<span class="">'.__("PDF bought by").' <a href="'.$currentPlayer->url.'">'.$name.'</a> '.$team.' : '.html_entity_decode($n->summary).'</span>';
                break;
              default : $out .= $n->task->name. ': '.__("todo");
            }
            $out .= '</span>';
            $out .= ' <label for="unpublish_'.$n->id.'" class="btn btn-danger btn-xs"><input type="checkbox" id="unpublish_'.$n->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&newsId='.$n->id.'" /> '.__("Unpublish").'</label>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("Nothing to check.").'</p>';
        }

        $out .= '</div>';
        $out .= '<div class="col-sm-6">';
        $unusedConcerned = $allPlayers->find("usabledItems.count>0")->sort("-team.name, name");
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
                    $out .= '<span><a href="'.$p->url.'">'.$p->title.'</a> ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)</span>';
                    $out .= ' <label for="unpublish_'.$historyPage->id.'" class="btn btn-danger btn-xs"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> '.__("used today").'</label>';
                  } else {
                    $successId = $pages->get("template=memory-text, id=$historyPage->linkedId")->task->id;
                    $failedId = $pages->get("name=solo-r")->id;
                    $out .= '<span><a href="'.$p->url.'">'.$p->title.'</a> ['.$p->team->title.'] : '.$historyPage->summary.' (bought '.$interval->days.' days ago)</span>';
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
                  $out .= '<span><a href="'.$p->url.'">'.$p->title.'</a> '.$p->lastName.' ['.$p->team->title.'] : '.$historyPage->refPage->title.' (bought '.$interval->days.' days ago)</span>';
                  $out .= ' <label for="unpublish_'.$historyPage->id.'" class="btn btn-danger btn-xs"><input type="checkbox" id="unpublish_'.$historyPage->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedItemHistoryPageId='.$historyPage->id.'" /> '.__("remove").'</label>';
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
        $pendings->filter("player=$allPlayers");
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
            $out .= '<span><a href="'.$p->player->url.'">'.$p->player->title.'</a> ['.$p->player->team->title.'] : '.$p->refPage->title.' (warning '.$interval->days.' days ago)</span>';
            $out .= ' <label for="unpublish_'.$p->id.'" class="btn btn-danger btn-xs"><input type="checkbox" id="unpublish_'.$p->id.'" class="ajaxUnpublish" value="'.$pages->get('name=submitforms')->url.'?form=unpublish&usedPending='.$p->id.'" /> validated today</label>';
            $out .= ' <a href="'.$pages->get('name=submitforms')->url.'?form=deleteNotification&pageId='.$p->id.'" class="del">'.__('[Delete]').'</a>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("No lessons to be validated.").'</p>';
        }
        $out .= '<hr />';
        $out .= '<p class="label label-primary">'.__("Fight requests").'</p>';
        $out .= ' <a href="#" class="ajaxBtn addRequest" data-type="addRequest">['.__("Add a request").']</a>';
        $fightRequests = $allPlayers->find("fight_request!=''");
        if (count($fightRequests) > 0) {
          $out .= '<ul id="fightRequests" class="list-unstyled">';
          foreach ($fightRequests as $p) {
            $out .= '<li>';
            $out .= '<a href="'.$p->url.'">'.$p->title.'</a> ['.$p->team->title.'] : <a href="'.$pages->get("name=monsters")->url.'?id='.$p->fight_request.'&pages2pdf=1">'.$pages->get($p->fight_request)->title.'</a>';
            $out .= ' <button class="ajaxBtn btn btn-xs btn-danger" data-type="fightRequest" data-result="rr" data-url="'.$pages->get('name=submit-fight')->url.'?form=fightRequest&playerId='.$p->id.'&result=RR&monsterId='.$p->fight_request.'">RR</button>';
            $out .= ' <button class="ajaxBtn btn btn-xs btn-danger" data-type="fightRequest" data-result="r" data-url="'.$pages->get('name=submit-fight')->url.'?form=fightRequest&playerId='.$p->id.'&result=R&monsterId='.$p->fight_request.'">R</button>';
            $out .= ' <button class="ajaxBtn btn btn-xs btn-success" data-type="fightRequest" data-result="v" data-url="'.$pages->get('name=submit-fight')->url.'?form=fightRequest&playerId='.$p->id.'&result=V&monsterId='.$p->fight_request.'">V</button>';
            $out .= ' <button class="ajaxBtn btn btn-xs btn-success" data-type="fightRequest" data-result="vv" data-url="'.$pages->get('name=submit-fight')->url.'?form=fightRequest&playerId='.$p->id.'&result=VV&monsterId='.$p->fight_request.'">VV</button>';
            $out .= ' <a href="'.$pages->get('name=submitforms')->url.'?form=deleteFightRequest&pageId='.$p->id.'" class="del">'.__('[Delete]').'</a>';
            $out .= '</li>';
          }
          $out .= '</ul>';
        } else {
          $out .= '<p>'.__("No requests.").'</p>';
        }
        $out .= '</div>';
      $out .= '</div>';
    $out .= '</div>';
  }
 
  if ($user->hasRole('player') || !$user->isLoggedin()) { // Scoreboards for players or guests (multi-columns)
    $out .= '<div class="col-sm-6">'; // Column 1
  }
      if ($user->hasRole('player')) {
        $playerName = $player->name;
        if ($player->team->is("name!=no-team")) {
          $team = '['.$player->team->title.']';
        } else {
          $team = '';
        }
        // Help messages ?
        if ($player->GC > 80) {
          $helpAlert = true;
          $helpTitle = sprintf(__("You have %dGC !"), $player->GC);
          $helpMessage = '<h4>'.__("Why not use them (free item, buy a potion, help a friend...) ?").'</h4>';
        }
        include("./helpAlert.inc.php"); 
      } else {
        $player = false;
        $playerName = '';
        $team = '';
      }

      if ($user->hasRole('player')) {
        // Get players' last 10 events
        $allEvents = $player->get("name=history")->children("sort=-created, limit=10");
        $out .= '<div id="" class="news panel panel-primary">';
          $out .= '<div class="panel-heading">';
            $out .= '<h4 class="panel-title">';
              if ($player->avatar) {
                $out .= '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="'.$player->title.'." />';
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
          $out .= '<a href="'.$player->url.'">'.__("My Profile page").'</a></p>';
          $out .= '</div>';
        $out .= '</div>';
      }
          
      if ($user->hasRole('player') || !$user->isLoggedin()) { // Not for teachers and superuser
        // Scoreboards
        $out .= '<div class="row">'; // Nested 2 columns
          $out .= '<div class="col-sm-6">'; // Subcolumn 1
            $boardsOnCol = ['yearlyKarma', 'reputation', 'underground_training'];
            foreach($boardsOnCol as $field) {
              switch($field) {
                case 'yearlyKarma' : $title = __("Most active players");
                  break;
                case 'reputation' : $title = __("Most influential players");
                  break;
                case 'underground_training' : $title = __("Most trained players");
                  break;
                default : $title = 'todo';
              }
              if ($user->isGuest()) { // Global Scoreboards
                $playerPos = false;
                $topPlayers = setGlobalScoreboard($field, 10);;
                $prevPlayers = false;
                $nextPlayer = false;
                $playerId = false;
              } else { // Team Scoreboards
                list($playerPos, $totalPlayersNb, $prevPlayers, $topPlayers, $nextPlayer) = setScoreboardNew($player, $field, 'team');
                $playerId = $player->id;
              }
              $out .= '<div class="board panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field='.$field.'"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title">';
              $out .= '<img src="'.$config->urls->templates.'img/star.png" alt="star." /> ';
              $out .= '<span class="label label-primary" data-toggle="tooltip" title="'.__("Your position in this scoreboard").'">'.$playerPos.'</span> '.$team.' '.$title.'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displaySmallScoreboard($topPlayers, $prevPlayers, $playerPos, $playerId, $field);
              $out .= '  </div>';
              $out .= '</div>';
            }
            if ($user->hasRole('player') && $player->team->name != 'no-team') {
              // Most active Groups
              $allGroups = setGroupScores($allPlayers);
              if (isset($allGroups)) { // Don't display if no groups are set
                $out .= '<div id="" class="panel panel-success">';
                $out .= '  <div class="panel-heading">';
                $out .= '  <h4 class="panel-title">'.$team.' '.__("Most Active Groups").'</h4>';
                $out .= '  </div>';
                $out .= '  <div class="panel-body">';
                  if ($player->group) { $groupId = $player->group->id; } else { $groupId = false; }
                  $out .= displayGroupScoreboard($allGroups, $groupId);
                $out .= '  </div>';
                $out .= '</div>';
              }
            }
          $out .= '</div>'; // /subcolumn 1

          $out .= '<div class="col-sm-6">'; // Subcolumn 2
            if ($user->hasRole('player') && $player->team->is("name!=no-team")) {
                // Help needed
                $out .= '<div id="" class="board panel panel-danger">';
                  $out .= '<div class="panel-heading">';
                    $dangerPlayers = $allPlayers->find("HP<=15")->sort("HP");
                    $comaPlayers = $allPlayers->find("coma=1");
                    $dangerPlayers->add($comaPlayers);
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
                          $out .= '<img class="" src="'.$p->avatar->getCrop("mini")->url.'" width="50" alt="'.$p->title.'." />';
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
            $boardsOnCol = ['places', 'people', 'fighting_power', 'donation'];
            foreach($boardsOnCol as $field) {
              switch($field) {
                case 'places' : $title = __("Greatest # of places");
                  break;
                case 'people' : $title = __("Greatest # of people");
                  break;
                case 'fighting_power' : $title = __("Best warriors");
                  break;
                case 'donation' : $title = __("Best donators");
                  break;
                default : $title = 'todo';
              }
              if ($user->isGuest()) { // Global Scoreboards
                $playerPos = false;
                $topPlayers = setGlobalScoreboard($field, 10);;
                $prevPlayers = false;
                $nextPlayer = false;
                $playerId = false;
              } else { // Team Scoreboards
                list($playerPos, $totalPlayersNb, $prevPlayers, $topPlayers, $nextPlayer) = setScoreboardNew($player, $field, 'team');
                $playerId = $player->id;
              }
              $out .= '<div class="board panel panel-success">';
              $out .= '  <div class="panel-heading">';
              $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field='.$field.'"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
              $out .= '  <h4 class="panel-title">';
              $out .= '<img src="'.$config->urls->templates.'img/star.png" alt="star." /> ';
              $out .= '<span class="label label-primary" data-toggle="tooltip" title="'.__("Your position in this scoreboard").'">'.$playerPos.'</span> '.$team.' '.$title.'</h4>';
              $out .= '  </div>';
              $out .= '  <div class="panel-body">';
              $out .= displaySmallScoreboard($topPlayers, $prevPlayers, $playerPos, $playerId, $field);
              $out .= '  </div>';
              $out .= '</div>';
            }
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
        $logo = $homepage->photo->eq(1)->size(40,40); 
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
              if ($player->avatar) { $out .= '<img src="'.$player->avatar->getCrop('mini')->url.'" alt="'.$player->title.'." />'; }
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
    
    // Recent public news (30 previous days)
    $out .= '<div id="" class="news panel panel-primary">';
      $out .= '<div class="panel-heading">';
        $out .= '<h4 class="panel-title">'.__("Recent public activity").'</h4>';
      $out .= '</div>';
      $out .= '<div class="panel-body">';
      $limitDate  = new \DateTime("-15 days");
      $limitDate = strtotime($limitDate->format('Y-m-d'));
      if ($user->isGuest()) { // All players (longest loading time)
        $recentEvents = $pages->find("has_parent!=$testPlayer, parent.name=history, template=event, date>$limitDate, public=1, limit=20, sort=-date");
      } else {
        $recentEvents = $pages->find("has_parent!=$testPlayer, parent.parent.team=$allTeams, parent.name=history, template=event, date>$limitDate, public=1, limit=20, sort=-date"); // Limit to headTeacher's teams
      }
      if ($recentEvents->count() > 0) {
        $out .= '<ul class="">';
        foreach($recentEvents as $n) {
          $currentPlayer = $n->parent('template=player');
          if ($currentPlayer->team->name == 'no-team') { $team = ''; } else { $team = '['.$currentPlayer->team->title.']'; }
          if ($currentPlayer->avatar) {
            $thumb = $currentPlayer->avatar->size(20,20);
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$currentPlayer->avatar->getCrop('thumbnail')->url."\" alt=\"".$currentPlayer->title.".\" />' src='".$thumb->url."' alt='".$currentPlayer->title.".' />";
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
            case 'best-time' :
              $out .= '<span class="">'.__("Best time for").' <a href="'.$currentPlayer->url.'">'.$currentPlayer->title.'</a> '.$team.'] : '.html_entity_decode($n->summary).'</span>';
              break;
            default : $out .= __("todo").' : '.$n->task->name;
          }
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
