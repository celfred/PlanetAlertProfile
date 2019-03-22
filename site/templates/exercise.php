<?php namespace ProcessWire;
  if (!$config->ajax) { // Fight or train on monster
    include("./head.inc"); 
    
    // check for login before outputting markup
    if($input->post->username && $input->post->pass) {
      $userName = $sanitizer->pageName($input->post->username);
      $pass = $input->post->pass; 
      if($session->login($userName, $pass)) {
        $session->redirect($page->url); // Redirect logged user to page to set up all variable
      }
    }

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) { // IE detected
      echo $wrongBrowserMessage;
    } else {
      if (!$user->isLoggedin()) {
        echo '<div class="row">';
          echo '<p class="well text-center">'.__("You MUST log in to access this page !").'</p>';
            echo '<div class="col-md-10 text-center">';
            if($input->post->username || $input->post->pass) echo "<h3><span class='label label-danger'>Login failed... (check user name or password)</span></h3>";
            echo '<form class="form-horizontal loginForm" action="'.$page->url.'" method="post">';
              echo '<div class="form-group">';
                echo '<label for="username" class="col-sm-4 control-label">User :</label>';
                echo '<div class="col-sm-6">';
                  echo '<input class="form-control" type="text" name="username" id="username" placeholder="Username" />';
                echo '</div>';
              echo '</div>';
              echo '<div class="form-group">';
                echo '<label for="pass" class="col-sm-4 control-label">Password :</label>';
                echo '<div class="col-sm-6">';
                  echo '<input class="form-control" type="password" name="pass" id="pass" placeholder="Password" /></label></p>';
                echo '</div>';
              echo '</div>';
            echo '<input type="submit" class="btn btn-info" name="submit" value="Connect" />';
            echo '</form>';
          echo '</div>';
          echo '</div>';
      } else {
        // Check for publish state from exerciseOwner or created_users_id
        if ($user->isSuperuser() || $user->hasRole('teacher') || ($user->hasRole('player') && $page->exerciseOwner->get("singleTeacher=$headTeacher") != NULL && $page->exerciseOwner->get("singleTeacher=$headTeacher")->publish == 1) || $player->team->is("name=test-team")) {
          // Test if player has unlocked Memory helmet (only training equipment for the moment)
          // or if admin has forced it in Team options
          if ($user->isSuperuser() || $user->hasRole('teacher')) {
            $player = $pages->get("parent.name=players, name=test");
            $helmet = $pages->get("name=memory-helmet");
            $teacherView = true;
          } else {
            $teacherView = false;
            if ($player->team->forceHelmet == 1) {
              $helmet = $pages->get("name=memory-helmet");
            } else {
              $helmet = $player->equipment->get('memory-helmet');
            }
          }
          $action = $input->urlSegment1;
          if ($action == '' || $action == 'train') { // Training session
            $out = '';
            $redirectUrl = $pages->get('name=underground-training')->url;
            $monster = $page;
            if (!$user->isSuperuser() && !$user->hasRole('teacher') || isset($player) && $player->team->is("name!=test-team")) {
              setMonster($player, $monster);
            } else { // Never trained (for admin)
              $monster->isTrainable = 1;
              $monster->lastTrainingInterval = -1;
            }
            if ($monster->isTrainable == 0) { // Not allowed because of spaced repetition.
              $session->redirect($redirectUrl); // Redirect to training page
            } else { // Ok, let's start the training session !
              if ($teacherView) { $out .= '<h3 class="text-center"><span class="label label-danger text-uppercase"><i class="glyphicon glyphicon-warning-sign"></i> '.__("Teacher access !").'</span></h3>'; }
              $out .= '<div ng-app="exerciseApp">';
                $out .= '<div class="row" ng-controller="TrainingCtrl" ng-init="init(\''.$pages->get("name=service-pages")->url.'\', \''.$monster.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$pages->get("name=submit-fight")->url.'\')">';
                if ($monster->id) { // Training session starts
                  $out .= '<div class="col-sm-12 text-center">';
                  $out .= '<h3>';
                  /* $out .= __("Memory helmet programmed").' : <span class="label label-danger">'.__("Training for").' '.$monster->title.' : </span>'; */
                  $out .= __("Memory helmet programmed").' : '.__("Training for").' <span class="label label-danger">'.$monster->title.' : ';
                  $out .= $monster->summary;
                  $out .= '</span>';
                  if ($user->language->name != 'french') {
                    $monster->of(false);
                    if ($monster->summary->getLanguageValue($french) != '') {
                      $out .= ' <img class="img-rounded" src="'.$urls->templates.'img/flag_fr.png" data-toggle="tooltip" data-html="true" title="'.$monster->summary->getLanguageValue($french).'" />';
                    }
                  }
                  $out .= '</h3>';
                  $out .= '<h2>';
                  $out .= '<span ng-class="{label:true, \'label-success\':true}">'.__("Training session");
                  $out .= ' <span class="blink">'.__("started").'</span></span> ';
                  $out .= ' → ';
                  $out .= ' <span class="label label-primary">+{{result}}'.__("UT").'</span>';
                  $out .= ' → ';
                  $out .= ' <span ng-class="{label:true, \'label-default\':true, \'blink\':correct}">'.__("Current counter").': {{counter}}</span>';
                  $out .= '</h2>';
                  $out .= '<span class="glyphicon glyphicon-info-sign"></span> '.__("10 words/sentences = +1UT");

                  $out .= '<div class="well trainingBoard" ng-show="waitForStart">'.__("Please wait while loading data...").'</div>';
                  $out .= '<div class="well trainingBoard" ng-hide="waitForStart">';
                  if ($monster->type->name == 'image-map') {
                    $out .= '<div><img src="'.$monster->imageMap->first()->url.'" max-width="800" alt="Image" /></div>';
                  }
                  if ($monster->instructions != '') {
                    $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$monster->instructions.'"></span>';
                  }
                  $out .= '<div class="bubble-right">';
                    if ($monster->type->name == 'jumble') {
                      $out .= '<div class="text-center">';
                      $out .= '<h2 class="jumbleW inline" ng-repeat="w in word track by $index">';
                      $out .= '<span ng-class="{\'label\':true, \'label-primary\':selectedItems.indexOf($index) === -1, \'label-warning\':selectedItems.indexOf($index) !== -1}" ng-click="pickWord(w, $index)" ng-bind-html="w|paTags"></span>';
                      $out .= '</h2>';
                      $out .= '</div>';
                      $out .= ' <h3><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{showCorrection}} {{feedback|paTags}}</span></h3> ';
                      $out .= '<button class="btn btn-danger btn-xs" ng-click="clear()">'.__("Try again").'</button> ';
                      $out .= '<span class="lead pull-right" data-toggle="tooltip" data-html="true" title="{{mixedWord}}" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses"></span></span>';
                      $out .= '<br /><br />';
                      $out .= '<h3 id="" ng-bind="playerAnswer"></h3>';
                      $out .= '<p class="text-right">';
                        $out .= '<button ng-click="attack()" class="actionBtn btn btn-success">'.__("Stimulate !").'</button>';
                        $out .= '<button ng-click="dodge()" class="actionBtn btn btn-danger">'.__("I don't know").'</button>';
                      $out .= '</p>';
                    } else if ($monster->type->name == 'categorize') {
                      $out .= '<div class="text-center">';
                      $out .= '<h2 class="inline" ng-bind-html="word|paTags"></h2>   ';
                      $out .= '<span class="lead pull-right" data-toggle="tooltip" data-html="true" title="{{mixedWord}}" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses"></span></span>';
                      $out .= ' <h3><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{showCorrection}}</span></h3> ';
                      $out .= '</div>';
                      $out .= '<br />';
                      $out .= '<h2 class="category inline" ng-repeat="c in categories">';
                      $out .= '<span ng-click="pickCategory(c)" ng-bind-html="c|paTags"></span>';
                      $out .= '</h2>';
                    } else {
                      $out .= '<div class="text-center">';
                      $out .= '<h2 class="inline" ng-bind-html="word|paTags"></h2>   ';
                      $out .= '<span class="lead pull-right" data-toggle="tooltip" data-html="true" title="{{mixedWord}}" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses"></span></span>';
                      $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> <span ng-bind-html="showCorrection|underline"></span> {{feedback}}</span></h3> ';
                      $out .= '</div>';
                      $out .= '<br />';
                      $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="'.__("Type your answer").'" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
                      $out .= '<p class="text-right">';
                        $out .= '<button ng-click="attack()" class="actionBtn btn btn-success">'.__("Stimulate !").'</button>';
                        $out .= '<button ng-click="dodge()" class="actionBtn btn btn-danger">'.__("I don't know").'</button>';
                      $out .= '</p>';
                    }
                  $out .= '</div>';
                  $out .= '<span class="avatarContainer">';
                    if (isset($player) && $player->avatar) {
                      $out .= '<img class="" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
                    }
                    if ($helmet->image) {
                      $out .= '<img class="helmet superpose squeeze" src="'.$helmet->image->url.'" alt="image" />';
                    }
                  $out .= '</span>';
                  $out .= '<h4>';
                  $out .= '<span class="glyphicon glyphicon-education"></span> '.__("Current record").' → ';
                  if ($monster->bestTrainedPlayerId != 0) {
                    $bestTrained = $pages->get($monster->bestTrainedPlayerId);
                    $out .= '<span class="label label-primary">'.$monster->best.__('UT by ').$bestTrained->title.' ['.$bestTrained->team->title.']</span>';
                  } else {
                    $out .= __("No record yet.");
                  }
                  if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
                    list($utGain, $inClassUtGain) = utGain($monster, $player);
                    $out .= ' ('.__("Your global UT for this monster").': '.($utGain+$inClassUtGain).')';
                  }
                  $out .= '</h4>';
                  $out .= '</div>';
                  $out .= '<button ng-click="stopSession()" class="btn btn-block btn-danger" ng-disabled="">'.__("Quit").'</button>';
                  $out .= '</div>';
                  $out .= '</div>';
                  /* $out .= '</div>'; */
                } else {
                  $out .= __("Sorry, but a problem occured. Please try again. If the problem still exists, contact the administrator.");
                }
              $out .= '</div>';
            }
            echo $out;
          } else if ($action == 'fight') { // Fighting session
            // Test if minimum UT has been reached to fight the monster
            if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
              $utGain = utGain($page, $player)[0];
            } else {
              $utGain = 100;
            }
            if ($utGain >= 20) {
              if ($teacherView) { echo '<h3 class="text-center"><span class="label label-danger text-uppercase"><i class="glyphicon glyphicon-warning-sign"></i> '.__("Teacher access !").'</span></h3>'; }
              echo '<div ng-app="exerciseApp">';
              // Get player's equipment to set scores alternatives
              $weaponRatio = 0;
              $protectionRatio = 0;
              $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
              $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
              if ($bestWeapon != false) { $weaponRatio = $bestWeapon->XP; } else { $weaponRatio = 0; }
              if ($bestProtection != false) { $protectionRatio = $bestProtection->HP; } else { $protectionRatio = 0; }
              // Get exercise type
              include('./exTemplates/'.$page->type->name.'.php');
              echo '</div>';
            } else {
              echo '<p class="alert alert-danger">'.__("You need to get 20UT on this monster before being able to fight it !").'</p>';
            }
          } else {
            echo __("A problem has occurred. Please tell the administrator.");
          }
        } else {
          echo $noAuthMessage;
        }
      }
    }
    include("./foot.inc"); 
  } else { // Ajax monster infos
    $out = '';
    $out .= '<div class="row">';
    $out .= '<div class="col-sm-4 text-center">';
    $out .= '<h3><span class="label label-primary">'.$page->title.'</span></h3>';
    $out .= '<p>Level '.$page->level.'</p>';
    $out .= '<small>Type : '.$page->type->title.' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$page->type->summary.'"></span></small>';
      $out .= '<h3 class="thumbnail">';
      if ($page->image) { $mini = '<img src="'.$page->image->getCrop('big')->url.'" alt="Photo" />'; }
      $out .= $mini;
      $out .= '</h3>';
    $out .= '</div>';
    $out .= '<div class="col-sm-8 text-left">';
      $page->of(false);
      $out .= '<p class="text-center"><h3>'.$page->summary.' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" onmouseenter="$(this).tooltip(\'show\');" title="'.$page->summary->getLanguageValue($french).'"></i></h3></p>';
      // Get player's stats
      if ($user->isLoggedin()) {
        $player = $pages->get("template='player', login=$user->name");
        if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
          $page = setMonster($player, $page);
          if ($page->fightNb > 0) {
          } else {
            $page->fightNb = 0;
          }
        } else { // Never trained (for admin)
          $page->isTrainable = 1;
          $page->isFightable = 1;
          $page->lastTrainingInterval = -1;
          $page->waitForTrain = 0;
        }
        $out .= "<br /><br />";
        $out .= '<p>Your activity :</p>';
        $out .= '<ul>';
        $out .= '<li><i class="glyphicon glyphicon-headphones"></i> <span class="label label-primary">'.$page->utGain.' UT</span>';
        if ($page->isTrainable == 1) {
          $helmet = $pages->get("name=memory-helmet");
          $out .= '→ <a class="btn btn-primary" href="'.$page->url.'train"><img src="'.$helmet->image->getCrop("mini")->url.'" alt="Use the Memory Helmet" /> Use the Memory Helmet !</a>';
          if ($page->lastTrainingInterval != -1) {
            $out .= '<p>Last training session : '.$page->lastTrainingInterval.'</p>';
          } else {
            $out .= '<p>You have never trained on this monster.</p>';
          }
        } else {
          if ($page->lastTrainingInterval == 0) {
            $out .= '<p>Last training session : Today !</p>';
          } else {
            $out .= '<p>Last training session : '.$page->lastTrainingInterval.'</p>';
          }
          if ($page->waitForTrain == 1) {
            $out .= '<p>You have to wait for tomorrow before training again on this monster.</p>';
          } else {
            $out .= '<p>You have to wait '.$page->waitForTrain.' days before training again on this monster.</p>';
          }
        }
        $out .= '</li>';
        $out .= '<li><i class="glyphicon glyphicon-flash"></i> <span class="label label-primary">'.$page->fightNb.' fight·s</span>';
        if ($page->isFightable == 1) {
          $out .= '→ <a class="btn btn-primary" href="'.$page->url.'fight"><i class="glyphicon glyphicon-flash"></i> Fight  the monster !</a>';
          if ($page->lastFightInterval != -1) {
            $out .= '<p>Last fight : '.$page->lastFightInterval.'</p>';
          } else {
            $out .= '<p>You have never fought this monster.</p>';
          }
        } else {
          if ($page->lastFightInterval == -1) {
            $out .= '<p>You must have 20UT to fight this monster.</p>';
          } else {
            if ($page->lastTrainingInterval != 0) {
              $out .= '<p>You have to wait '.$page->waitForFight.' days to fight this monster.</p>';
            } else {
              $out .= '<p>You can\'t fight this monster. You have used the Memory Helmet today so '.$page->title.' walked away.</p>';
            }
          }
        }
        // Show last result
        if (isset($page->quality) && $page->fightNb > 0) {
          $out .= '<p>Average result : '.averageLabel($page->quality).'</p>';
        }
        $out .= '</li>';
        // Is speedQuiz available ?
        $out .= '<li>';
        if ($user->isSuperuser() || $user->hasRole('teacher') || isset($player->skills) && $player->skills->has("name=fighter") && $player->find("template=event, task.name=fight-vv, refPage=$page")->count() >= 1) {
          $out .= '<a class="btn btn-primary" href="'.$pages->get("name=speed-quiz")->url.$page->id.'"><i class="glyphicon glyphicon-time"></i> '.__("Start a Speed Quiz !").'</a>';
        } else {
          $out .= '<i class="glyphicon glyphicon-time"></i> '.__("You need to have at least 1 VV fight to do a speed quiz.");
        }
        $out .= '</ul>';
      }
    $out .= '</div>';
    $out .= '<div class="col-sm-8 text-left">';
      $out .= "<br /><br />";
      if ($page->bestTrainedPlayerId != 0) {
        $bestTrained = $pages->get($page->bestTrainedPlayerId);
        if ($bestTrained->team->name != "no-team" ) { $team = ' ['.$bestTrained->team->title.']'; } else { $team = ''; }
      }
      $out .= '<p><i class="glyphicon glyphicon-thumbs-up"></i> Most trained player : ';
      if ($page->bestTrainedPlayerId) {
        $out .='<span class="label label-success">'.$bestTrained->title.$team.' → '.$page->best.'UT</span>';
      } else {
        $out .= __('Nobody !');
      }
      $out .= '</p>';
      $out .= '<p><i class="glyphicon glyphicon-thumbs-up"></i> Master time : ';
      if ($page->masterTime != 0) {
        $master = $pages->get($page->bestTimePlayerId);
        $out .= '<span class="label label-success">'.ms2string($page->masterTime).' '.__('by').' '.$master->title.' ['.$master->team->title.']</span>';
      } else {
        $out .= __('Nobody !');
      }
      $out .= '</td>';
    $out .= '</div>';
    $out .= '</div>';

    echo $out;
  }
?>
