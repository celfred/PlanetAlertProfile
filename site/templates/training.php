<?php namespace ProcessWire;
if (!$config->ajax) {
  include("./head.inc"); 

  $limitDate  = new \DateTime("-1 year");
  $limitDate = strtotime($limitDate->format('Y-m-d'));

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser() || $user->hasRole('teacher')) {
    $getPlayerId = '';
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $user->hasRole('teacher') || $player->team->forceHelmet == 1) {
      if ($user->isSuperuser() || $user->hasRole('teacher')) { // Set player
        if ($input->get->playerId) {
          $playerId = $input->get->playerId;
          $player = $pages->get("id=$playerId");
          $getPlayerId = '?playerId='.$playerId;
        } else {
          $player = $pages->get("parent.name=players, name=test");
        }
        $headTeacher = getHeadTeacher($player);
        $helmet = $player->equipment->get("name=memory-helmet");
        $visualizer = $player->equipment->get("name~=visualizer");
      } else {
        $helmet = $pages->get("template=item, name=memory-helmet");
        $visualizer = $pages->get("template=item, name~=visualizer");
      }
    } else {
      $helmet = $player->equipment->get("name=memory-helmet");
      $visualizer = $pages->get("template=item, name~=visualizer");
    }
    if ($helmet) { // Display training catalogue
      $out = '<div>';
      // Set all available monsters
      // Check if player has the Visualizer (or forced by admin)
      if ($player->equipment->has("name~=visualizer") || $player->team->forceVisualizer == 1) {
        $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1")->sort("name");
        $allMonstersNb = $allMonsters->count();
        $visualizer = $pages->get("template=item, name~=visualizer");
      } else {
        $allMonsters = $pages->find("parent.name=monsters, template=exercise, exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1, special=0")->sort("name");
        $hiddenMonstersNb = $pages->find("parent.name=monsters, template=exercise, (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1), special=1")->count();
      }
      // Check if fightRequest
      if ($player->fight_request == 0) { $request = false; } else { $request = $player->fight_request; }
      // Load challenges
      if ($player->team->is("name!=no-team")) {
        $allChallenges = $pages->get("template=teacherProfile, name=$headTeacher->name")->teamChallenges->get("team=$player->team")->linkedMonsters;
      } else {
        $allChallenges = false;
      }
      // Prepare all monsters
      foreach($allMonsters as $m) {
        setMonster($player, $m);
      }
      $availableNb = $allMonsters->find("isTrainable=1")->count();
      // Store allMonsters in session cache on 1st page load
      $cache->save("monstersList_".$player->name, $allMonsters);
      $allMonstersNb = $allMonsters->count();
      $out .= '<div class="well">';
      $out .= '<h2 class="text-center">';
        $out .= '<span class="pull-left glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->summary.'"></span>';
        $out .= '<span class="blink">';
          $out .= __("Program your helmet !");
        $out .= "</span>";
        $out .= '<span class="avatarContainer pull-right">';
          if (isset($player) && $player->avatar) {
            $out .= '<img class="helmetAvatar" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="'.$player->title.'." />';
          }
          if ($helmet->image) {
            $out .= '<img class="helmet superpose" src="'.$helmet->image->getCrop("thumbnail")->url.'" alt="Helmet." />';
          }
          if ($player->equipment->has("name~=visualizer") || $player->team->forceVisualizer == 1) {
            $out .= '<img class="helmet superpose" src="'.$visualizer->image->getCrop("mini")->url.'" alt="Visualizer" />';
          }
        $out .= '</span>';
      $out .= '</h2>';
      if ($user->isSuperuser() || $user->hasRole('teacher')) {
        $out .= '<h3 class="text-center"><span class="label label-danger">';
        $out .= sprintf(__('Teacher\'s access for player %s'), $player->title);
        $out .= '</span></h3>';
      }
      $out .= '<p class="text-center">';
      $link = '<a href="'.$visualizer->url.'">'.$visualizer->title.'</a>';
      if (!isset($hiddenMonstersNb)) {
        $out .= sprintf(__('There are %1$s detected monsters thanks to your %2$s.'), $allMonstersNb, $link);
      } else {
        $out .= sprintf(__('There are only %d detected monsters.'), $allMonstersNb);
        /* $out .= ' '.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link); */
      }
      $out .= ' '.sprintf(__('[%d monsters are available today]'), $availableNb);
      $out .= '</p>';
      if (isset($hiddenMonstersNb)) { // Display helpAlert for Electronic visualizer
        $helpAlert = true;
        $helpTitle = __("Some monsters are absent !");
        $helpMessage = '<img src="'.$pages->get("name~=visualizer")->image->getCrop("small")->url.'" alt="image" /> ';
        $helpMessage .= '<h4>'.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link).'</h4>';
      }
      include("./helpAlert.inc.php"); 
      $out .= '<section class="configHelmet">';
      if ($input->urlSegment1 == '') { // No selection
          $out .= '<div class="frame">';
            $out .= __("Today's challenges !").' ';
            $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.__("Recommended training ! Validate a solo-mission by doing the complete challenges !").'"></span>';
            if ($player->team->name != 'no-team' && $allChallenges->count() > 0 && $player->team->classActivity == 0) {
              $out .= '<div class="row">';
                $nbChallenge = 0;
                foreach($allChallenges as $m) {
                  // Check if challenge done today !
                  list($utGain, $inClassUtGain) = utGain($m, $player, date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"));
                  $out .= '<div class="col-xs-12 col-md-4 text-center">';
                    $out .= '<p>';
                    if ($utGain > 0) {
                      $nbChallenge++;
                      $out .= '<span class="label label-success">';
                      $out .= '<span class="glyphicon glyphicon-thumbs-up"></span> '.__("Done !");
                      $out .= ' '.sprintf(__('%dUT'), $utGain);
                      $out .= '</span>';
                    } else {
                      $out .= '<span class="label label-danger">';
                      $out .= ' '.__("Recommended training !");
                      $out .= '</span>';
                    }
                    $out .= '</p>';
                      if ($utGain == 0) {
                        $out .= '<p>';
                          if ($m->image) { $out .= '<img class="squeeze" src="'.$m->image->getCrop("small")->url.'" alt="no-img" data-toggle="tooltip" title="'.$m->summary.'" />'; }
                          $out .= ' <a class="btn btn-primary" href="'.$m->url.'train"><i class="glyphicon glyphicon-headphones" data-toggle="tooltip" title="'.__("Activate helmet !").'" onmouseenter="$(this).tooltip(\'show\');"></i></a>';
                        $out .= '</p>';
                        $out .= '<p class="squeeze">';
                          $out .= '<span class="label label-danger">'.$m->title.'</span>';
                        $out .= '</p>';
                      } else {
                        $out .= '<p>';
                          if ($m->image) { $out .= '<img class="" src="'.$m->image->getCrop("mini")->url.'" alt="no-img" data-toggle="tooltip" data-html="true" title="'.$m->title.'<br />'.$m->summary.'" />'; }
                        $out .= '</p>';
                        /* $out .= '<p>'; */
                          /* $out .= '<small><span class="label label-success">'.$m->title.'</span></small>'; */
                        /* $out .= '</p>'; */
                      }
                  $out .= '</div>';
                }
                if ($nbChallenge == 3) {
                  $out .= '<h3><span class="glyphicon glyphicon-thumbs-up"></span> ';
                  $out .= __("Congratulations ! You have completed all challenges for today !");
                  $out .= '</h3>';
                }
              $out .= '</div>';
            }  else {
              if ($player->team->classActivity == 1) {
                $out .= ' → '.__("No challenge in class.");
              } else {
                $out .= ' → '.__("No challenge for today.");
              }
            }
          $out .= '</div>';
          $out .= '<div class="frame">';
            $out .= __("Quick selection ?");
            $out .= '<div class="btn-toolbar">';
              $out .= '<div class="btn-group btn-group-lg" role="group">';
                /* $out .= '<a href="'.$page->url.'random" class="btn btn-primary pgHelmet">'.__("Personal recommandation").'</a>&nbsp;'; // TODO */
                if ($availableNb > 0) {
                  $out .= '<a href="'.$page->url.'random/'.$getPlayerId.'" class="btn btn-primary pgHelmet">'.__("Random selection").'</a>&nbsp;';
                }
              $out .= '</div>';
              $out .= '<div class="btn-group btn-group-lg" role="group">';
                $out .= '<a href="'.$page->url.'never'.$getPlayerId.'" class="btn btn-primary pgHelmet">'.__("Never trained").'</a>';
                $out .= '<a href="'.$page->url.'available'.$getPlayerId.'" class="btn btn-primary pgHelmet">'.__("Available today").'</a>';
              $out .= '</div>';
              $out .= '<div class="btn-group btn-group-lg" role="group">';
                $out .= '<a href="'.$page->url.'level/1'.$getPlayerId.'" class="btn btn-primary pgHelmet">Level 1</a>';
                $out .= '<a href="'.$page->url.'level/2'.$getPlayerId.'" class="btn btn-primary pgHelmet">Level 2</a>';
                $out .= '<a href="'.$page->url.'level/3'.$getPlayerId.'" class="btn btn-primary pgHelmet">Level 3</a>';
              $out .= '</div>';
              $out .= '<div class="btn-group btn-group-lg" role="group">';
                $out .= '<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $out .= __("Name selection").' <span class="caret"></span>';
                $out .= '</button>';
                $out .= '<ul class="dropdown-menu monsterSelection">';
                  $allCat = [];
                  foreach($allMonsters as $m) {
                    $out .= '<li><a class="pgHelmet" href="'.$page->url.'monster/'.$m->id.'/'.$getPlayerId.'">'.$m->title.'</a></li>';
                    foreach($m->topic as $c){
                      if (!in_array($c->title, $allCat)) {
                        array_push($allCat, $c->title);
                      }
                    }
                    sort($allCat);
                  }
                $out .= '</ul>';
              $out .= '</div>';
              $out .= '<div class="btn-group btn-group-lg" role="group">';
                $out .= '<a href="'.$page->url.'notAvailable'.$getPlayerId.'" class="btn btn-primary pgHelmet">'.__("Not available today").'</a>';
              $out .= '</div>';
            $out .= '</div>';
          $out .= '</div>';
          $out .= '<div class="frame">';
            $out .= __("Topic selection ?");
            $out .= '<ul class="list list-unstyled col6">';
              foreach($allCat as $c) {
                $out .= '<li class="text-left"><a href="'.$page->url.$sanitizer->pageName($c).$getPlayerId.'" class="label label-danger pgHelmet topics">'.$c.'</a></li>';
              }
            $out .= '</ul>';
          $out .= '</div>';
      }
      $out .= '</section>';

      $out .= '<section id="trainingList" class="row" data-href="'.$page->url.'"></section>';

      echo $out;

      echo '</div>';
      echo '</div>';
    }
  } else {
    echo $noAuthMessage;
  }

  include("./foot.inc"); 
} else { // Load selected training possibilities
  if (!$user->isSuperuser()) {
    if ($user->hasRole('player')) { // Get logged in player
      $headTeacher = getHeadTeacher($user);
      $user->language = $headTeacher->language;
      $player = $pages->get("template=player, login=$user->name");
      $allCachedMonsters = $cache->get("monstersList_".$user->name); // Read from cache after 1st page load
    } else {
      if ($input->get->playerId) {
        $playerId = $input->get->playerId;
        $player = $pages->get("id=$playerId");
        $allCachedMonsters = $cache->get("monstersList_".$player->name); // Read from cache after 1st page load
      } else {
        $player = $pages->get("template=player, name=test");
        $allCachedMonsters = $cache->get("monstersList_".$player->name); // Read from cache after 1st page load
      }
      $headTeacher = getHeadTeacher($player);
    }
  } else {
    $player = $pages->get("template=player, name=test");
  }
  foreach($allCachedMonsters as $m) {
    setMonster($player, $m);
  }
  $availableNb = $allCachedMonsters->find("isTrainable=1")->count();
  bd($allCachedMonsters);
  switch($input->urlSegment1) {
    case 'random' :
      if ($availableNb > 3) { $nbSelected = 3; } else { $nbSelected = $availableNb; }
      $allMonsters = $allCachedMonsters->find("isTrainable=1")->findRandom($nbSelected)->sort("level");
      $title = '';
      break;
    case 'available' :
      $selector = 'isTrainable=1';
      $allMonsters = $allCachedMonsters->find($selector)->sort("name, level");
      $title = '<h3>'.sprintf(_n('%d result','%d results', $allMonsters->count()), $allMonsters->count()).'</h3>';
      break;
    case 'notAvailable' :
      $selector = 'isTrainable=0';
      $allMonsters = $allCachedMonsters->find($selector)->sort("waitForTrain, name, level");
      $title = '<h3>'.sprintf(_n('%d result','%d results', $allMonsters->count()), $allMonsters->count()).'</h3>';
      break;
    case 'level' :
      $level = $input->urlSegment2;
      $selector = 'level='.$level;
      $allMonsters = $allCachedMonsters->find($selector)->sort("title");
      $title = '<h3>'.sprintf(_n('%d result','%d results', $allMonsters->count()), $allMonsters->count()).'</h3>';
      break;
    case 'monster' :
      $monsterId = $input->urlSegment2;
      $selector = 'id='.$monsterId;
      $allMonsters = $allCachedMonsters->find($selector);
      $title = '';
      break;
    case 'never' :
      $tmpCache = $player->children()->get("name=tmp");
      $allTrainedIds = [];
      foreach($tmpCache->tmpMonstersActivity as $p) {
        array_push($allTrainedIds, $p->monster->id);
      }
      $allTrainedIds = implode('|', $allTrainedIds);
      $selector = 'id!='.$allTrainedIds;
      $allMonsters = $allCachedMonsters->find($selector)->sort("level");
      $title = '<h3>'.sprintf(_n('%d result','%d results', $allMonsters->count()), $allMonsters->count()).'</h3>';
      break;
    default :
      $selector = 'topic.name='.$input->urlSegment1;
      $allMonsters = $allCachedMonsters->find($selector)->sort("level");
      $title = '<h3>'.sprintf(_n('%d result','%d results', $allMonsters->count()), $allMonsters->count()).'</h3>';
  }

  $out = '';
  $out .= '<p class="text-center"><button id="configHelmetBtn" class="btn btn-danger"><span class="glyphicon glyphicon-cog"></span> '.__("Show/Hide programming options").'</button></p>';
  $limitDate  = new \DateTime("-1 year");
  $limitDate = strtotime($limitDate->format('Y-m-d'));
  // Check if fightRequest
  if ($player->fight_request == 0) { $request = false; } else { $request = $player->fight_request; }
  $notAvailable = new pageArray();
    $out .= '<div class="container">';
    $out .= $title;
    $out .= '<section class="row display-flex">';
      $today = new \DateTime("today");
      foreach($allMonsters as $m) {
        $topics = $m->topic->implode(', ', '{title}');
          if ($user->hasRole("teacher") || $user->isSuperuser()) { // Never trained (for admin or teachers)
            $m->isTrainable = 1;
            $m->lastTrainingInterval = -1;
            $m->waitForTrain = 0;
          }
          if ($m->bestTrainedPlayerId != 0) {
            $bestTrained = $pages->get($m->bestTrainedPlayerId);
            $m->bestTrainedTitle = $bestTrained->title;
            $m->bestTrainedTeam = $bestTrained->team->title;
            if ($m->bestTrainedPlayerId == $player->id) {
              $m->isBestTrained = true;
            } else {
              $m->isBestTrained = false;
            }
          }
          if ($m->bestTimePlayerId != 0) {
            $master = $pages->get($m->bestTimePlayerId);
            $m->bestTimePlayerTitle = $master->title;
            $m->bestTimeTeam = $master->team->title;
            if ($m->bestTrainedPlayerId == $player->id) {
              $m->isMaster = true;
            } else {
              $m->isMaster = false;
            }
          }
          switch($m->level) {
            case '1' : $class = 'success'; break;
            case '2' : $class = 'warning'; break;
            case '3' : $class = 'danger'; break;
            default : $class = 'primary';
          }
          if ($m->isTrainable == 1) {
            $out .= '<div class="col-xs-12 col-md-4">';
              $out .= '<div class="monsterPanel">';
              $out .= '<div class="actionBar lead colk-xs-12 col-md-12">';
              $out .= '<span class="badge pull-left">'.__("Level").' '.$m->level.'</span>';
              // Find # of days compared to today to set 'New' indicator
              $date2 = new \DateTime(date("Y-m-d", $m->published));
              $interval = $today->diff($date2);
              if ($interval->days < 7) {
                $out .= ' <span class="badge">'.__("New").'</span>';
              }
              if ($m->special) {
                $out .= ' <span class="badge">'.__("Detected").' !</span>';
              }
              $out .= ' <span>'.$m->title.'</span>';
              $out .= '<span class="pull-right">';
                $out .= ' <a class="btn btn-primary btn-lg" href="'.$m->url.'train"><i class="glyphicon glyphicon-headphones" data-toggle="tooltip" title="'.__("Activate helmet !").'" onmouseenter="$(this).tooltip(\'show\');"></i></a>';
                $formerRequest = $pages->get("has_parent=$player, template=event, task.name=fight-vv, inClass=1, refPage=$m, date>$limitDate");
                if ($formerRequest->id) {
                  $out .= ' <span class="glyphicon glyphicon-ok" data-toggle="tooltip" title="'.__('You have already defeated this monster in the previous year.').'" onmouseenter="$(this).tooltip(\'show\');"></span>';
                } else {
                  if ($request == 0) {
                    $msg = sprintf(__("Fight request for %s"), $m->title);
                    $out .= ' <span><a class="btn btn-danger btn-lg fightRequestConfirm" href="'.$page->url.'" data-href="'.$pages->get("name=submitforms")->url.'?form=fightRequest&monsterId='.$m->id.'&playerId='.$player->id.'" data-msg="'.$msg.'" data-reload="true"><i class="glyphicon glyphicon-education" data-toggle="tooltip" title="'.__("Ask teacher for an in-class Fight!").'" onmouseenter="$(this).tooltip(\'show\');"></i></a></span>';
                  } else if ($request == $m->id) {
                    $out .= ' <span class="glyphicon glyphicon-ok-circle" data-toggle="tooltip" title="'.__('Your teacher has already been warned about this request.').'" onmouseenter="$(this).tooltip(\'show\');"></span>';
                  }
                }
                $out .= '</span>';
              $out .= '</div>';
              $out .= '<div class="row">';
              $out .= '<div class="col-xs-12 col-md-4">';
                if ($m->image) { $out .= '<img class="img-thumbnail" src="'.$m->image->getCrop("thumbnail")->url.'" title="" alt="no-img" />'; }
              $out .= '</div>';
              $out .= '<div class="col-xs-12 col-md-8 monsterPanelBody">';
              $out .= '<p>';
                $out .= __("UT gained").' : ';
                if (isset($player)) {
                  if ($m->utGain > 0) {
                    $out .= '<span class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> +'.$m->utGain.'</span> ';
                  } else {
                    $out .= '<span class="label label-danger"><span class="glyphicon glyphicon-thumbs-down"></span> 0</span> ';
                  }
                }
              $out .= '</p>';
              $out .= '<p>';
                $out .= __("Last training session").' : ';
                if (isset($player)) {
                  if ($m->lastTrainingInterval != '-1') {
                    $out .= $m->lastTrainingInterval;
                  } else {
                    $out .= '-';
                  }
                }
              $out .= '</p>';
              $out .= '<hr />';
              $out .= '<p>';
                $out .= '<span>'.__("Most trained").' : ';
                if ($m->bestTrainedPlayerId != 0) {
                  if ($m->isBestTrained) { $class = 'success'; } else { $class = 'primary'; }
                  $out .= '<span class="label label-'.$class.'">'.$m->best.' '.__("UT").' - '.$m->bestTrainedTitle.' ['.$m->bestTrainedTeam.']</span>';
                } else {
                  $out .= __("Nobody yet.");
                }
                $out .= '</span>';
              $out .= '</p>';
              $out .= '<p>';
                $out .= '<span>'.__("Master time").' : ';
                if ($m->bestTimePlayerTitle) {
                  if ($m->isMaster) { $class = 'success'; } else { $class = 'primary'; }
                  $out .= '<span class="label label-'.$class.'">'.ms2string($m->masterTime).' '.__('by').' '.$m->bestTimePlayerTitle.' ['.$m->bestTimeTeam.']</span>';
                } else {
                  $out .= __("Nobody yet.");
                }
                $out .= '</span>';
              $out .= '</p>';
              $out .= '<hr />';
              $out .= '<p>';
                $out .= __("Exercise type").' → '.$m->type->title;
                if ($m->type->summary != '') {
                  $tooltip = $m->type->summary;
                } else {
                  if ($m->type->getLanguageValue($french, 'summary') != '') {
                    $tooltip = $m->type->getLanguageValue($french, 'summary');
                  }
                }
                $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$tooltip.'" onmouseenter="$(this).tooltip(\'show\');"></span>';
              $out .= '</p>';
              $out .= '</div>'; // col-md-9
              $out .= '</div>'; // row
              $out .= '<div class="footerBar">';
              if ($m->summary != '') {
                $out .= ' → <span>'.$m->summary.'</span> ';
              } else {
                if ($m->getLanguageValue($french, 'summary') != '') {
                  $out .= ' → <span>'.$m->getLanguageValue($french, 'summary').'</span> ';
                }
              }
              // Data preview
              $exData = $m->exData;
              $allLines = preg_split('/$\r|\n/', $sanitizer->entitiesMarkdown($exData));
              $listWords = prepareListWords($allLines, $m->type->name);
              $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'" onmouseenter="$(this).tooltip(\'show\');"></span>';
              $out .= '</div>';
              $out .= '</div>'; // monsterDiv
              $out .= '<div class="clearfix visible-md-block"></div>';
            $out .= '</div>'; // col-md-4
          } else {
            $notAvailable->add($m);
          }
        $out .= '<div class="clearfix visible-md-block"></div>';
      }
    $out .= '</section>'; // row
    if ($notAvailable->count() > 0) {
      $out .= '<section class="row">';
        $out .= '<h3>'.__("Not available").' ('.$notAvailable->count().') :</h3>';
        $out .= '<ul>';
          foreach($notAvailable as $m) {
            if ($m->waitForTrain == 1) {
              $out .= '<li>';
              if ($m->image) { $out .= '<img class="" src="'.$m->image->getCrop("small")->url.'" title="" alt="no-img" /> '; }
              $out .= $m->title;
              $out .= ' → <span class="label label-success">'.__("Available tomorrow !").'</span></li>';
            } else {
              $out .= '<li>';
              if ($m->image) { $out .= '<img class="" src="'.$m->image->getCrop("small")->url.'" title="" alt="no-img" /> '; }
              $out .= $m->title;
              $out .= ' → <span class="label label-danger">'.sprintf(__("Available in %d days"), $m->waitForTrain).'</span></li>';
            }
          }
        $out .= '</ul>';
      $out .= '</section>';
    }
  $out .= '</div>'; // container

  echo $out;
}
?>
