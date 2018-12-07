<?php namespace ProcessWire;
  if (!$config->ajax) { // Fight or train on monster
    include("./head.inc"); 
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) { // IE detected
      echo $wrongBrowserMessage;
    } else {
      if (!$user->isLoggedin()) {
        echo $noAuthMessage;
      } else {
        // Test if player has unlocked Memory helmet (only training equipment for the moment)
        // or if admin has forced it in Team options
        if ($user->isSuperuser() || $user->hasRole('teacher') || $player->team->forceHelmet == 1) {
          $helmet = $pages->get("name=memory-helmet");
        } else {
          $helmet = $player->equipment->get('memory-helmet');
        }
        $action = $input->urlSegment1;
        if ($action == '' || $action == 'train') {
          $out = '';
          $redirectUrl = $pages->get('name=underground-training')->url;
          if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
            $monster = $page;
            setMonster($player, $monster);
          } else { // Never trained (for admin)
            /* $monsterId = $input->get->id; */
            /* $monster = $pages->get("id=$monsterId, include=all"); */
            $monster = $page;
            $monster->isTrainable = 1;
            $monster->lastTrainingInterval = -1;
          }
          if ($monster->isTrainable == 0) { // Not allowed because of spaced repetition.
            // Redirect to training page
            $session->redirect($redirectUrl);
          } else { // Ok, let's start the training session !
            $out .= '<div ng-app="exerciseApp">';
            if (isset($player)) {
              $out .= '<div class="row" ng-controller="TrainingCtrl" ng-init="init(\''.$pages->get("name=service-pages")->url.'\', \''.$monster.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$pages->get("name=submit-fight")->url.'\')">';
            } else {
              $out .= '<div class="row" ng-controller="TrainingCtrl" ng-init="init(\''.$pages->get("name=service-pages")->url.'\', \''.$monster.'\', \''.$redirectUrl.'\', \'0\', \''.$pages->get("name=submit-fight")->url.'\')">';
            }
            if ($monster->id) { // Training session starts
              $out .= '<h3>';
              $out .= __("Memory helmet programmed").' : ';
              $out .= $monster->summary;
              if ($user->language->name != 'french') {
                $monster->of(false);
                if ($monster->summary->getLanguageValue($french) != '') {
                  $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$monster->summary->getLanguageValue($french).'"></span>';
                }
              }
              $out .= '</h3> ';
              $out .= '<div class="col-sm-3">';
              $out .= '<h3><span ng-class="{label:true, \'label-primary\':true}">'.__("Training session");
              $out .= ' <span class="blink">'.__("started").'</span></span></h3>';
              $out .= '<br />';
              $out .= '<h4><span ng-class="{label:true, \'label-primary\':true}">'.__("Current counter").': {{counter}}</span> → ';
              $out .= '<span class="label label-primary">+{{result}}'.__("UT").'</span></h4>';
              $out .= '<span class="glyphicon glyphicon-info-sign"></span> '.__("10 words/sentences = +1UT");
              $out .= '<br /><br />';
              $out .= '<div class="panel panel-success">';
              $out .= '<div class="panel-heading">';
              $out .= '<h4 class="panel-title"><span class="glyphicon glyphicon-education"></span> '.__("Current record").'</h4>';
              $out .= '</div>';
              $out .= '<div class="panel-body">';
              if ($monster->mostTrained && $monster->mostTrained->id) {
                $out .= '<h4 class="text-center">'.$monster->best.__('UT by ').$monster->mostTrained->title.' ['.$monster->mostTrained->team->title.']</h4>';
              } else {
                $out .= '<h4 class="text-center">'.__("No record yet.").'</h4>';
              }
              $out .= '</div>';
              $out .= '<div class="panel-footer">';
              if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
                list($utGain, $inClassUtGain) = utGain($monster, $player);
                $out .= '<p>'.__("Your global UT for this monster").': '.($utGain+$inClassUtGain).'</p>';
              }
              $out .= '</div>';
              $out .= '</div>';
              $out .= '</div>';

              $out .= '<div class="col-sm-9 text-center">';
                if ($monster->instructions != '') {
                  $out .= '<h3 class="text-center">'.$monster->instructions.'</h3>';
                }
                $out .= '<div class="well trainingBoard" ng-show="waitForStart">Please wait while loading data...';
              $out .= '</div>';
              $out .= '<div class="well trainingBoard" ng-hide="waitForStart">';
              if ($monster->type->name == 'image-map') {
                $out .= '<div class=""><img src="'.$monster->imageMap->first()->url.'" max-width="800" alt="Image" /></div>';
              }
              if ($monster->type->name == 'jumble') {
                $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.__("Click on the words to build a correct sentence. If you make a mistake, use the 'Try again' button. If you're wrong, the correct answer will be shown and you just have to copy the correction.<br />See documentation for more information.").'"></span>';
              } else {
                $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.__("Type your answer. If you don't know, just hover on the glasses to see the mixed letters. If you're wrong, the correct answer will be shown and you just have to copy the correction.<br />See documentation for more information.").'"></span>';
              }
              $out .= '<div class="bubble-right">';
              if ($monster->type->name == 'jumble') {
                $out .= '<div class="text-center">';
                $out .= '<h2 class="jumbleW inline" ng-repeat="w in word track by $index">';
                $out .= '<span ng-class="{\'label\':true, \'label-primary\':selectedItems.indexOf($index) === -1, \'label-warning\':selectedItems.indexOf($index) !== -1}" ng-click="pickWord(w, $index)" ng-bind-html="w|paTags"></span>';
                $out .= '</h2>';
                $out .= '</div>';
                $out .= ' <h3><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{showCorrection}} {{feedback|paTags}}</span></h3> ';
                $out .= '<button class="btn btn-danger btn-xs" ng-click="clear()">'.__("Try again").'</button>';
                $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
                $out .= '<span id="clue" ng-show="showClue" ng-bind-html="mixedWord|paTags"></span>';
                $out .= '<br /><br />';
                $out .= '<h3 id="" ng-bind="playerAnswer"></h3>';
                $out .= '<br />';
                $out .= '<button ng-click="attack()" class="btn btn-success">'.__("Stimulate !").'</button>';
                $out .= '&nbsp;&nbsp;';
                $out .= '<button ng-click="dodge()" class="btn btn-danger">'.__("I don't know").'</button>';
              } else if ($monster->type->name == 'categorize') {
                $out .= '<div class="text-center">';
                $out .= '<h2 class="inline" ng-bind-html="word|paTags"></h2>   ';
                $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
                $out .= '<span id="clue" ng-show="showClue">{{mixedWord}}</span>';
                $out .= '</div>';
                $out .= '<br />';
                $out .= '<h2 class="category inline" ng-repeat="c in categories">';
                $out .= '<span ng-click="pickCategory(c)" ng-bind-html="c|paTags"></span>';
                $out .= '</h2>';
              } else {
                $out .= '<div class="text-center">';
                $out .= '<h2 class="inline" ng-bind-html="word|paTags"></h2>   ';
                $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
                $out .= '<span id="clue" ng-show="showClue">{{mixedWord}}</span>';
                $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> <span ng-bind-html="showCorrection|underline"></span> {{feedback}}</span></h3> ';
                $out .= '</div>';
                $out .= '<br />';
                $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="'.__("Type your answer").'" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
                $out .= '<br />';
                $out .= '<button ng-click="attack()" class="btn btn-success">'.__("Stimulate !").'</button>';
                $out .= '&nbsp;&nbsp;';
                $out .= '<button ng-click="dodge()" class="btn btn-danger">'.__("I don't know").'</button>';
              }
              $out .= '<span class="pull-right">';
              $out .= '<span class="avatarContainer">';
              if (isset($player) && $player->avatar) {
                $out .= '<img class="" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
              }
              if ($helmet->image) {
                $out .= '<img class="helmet superpose squeeze" src="'.$helmet->image->url.'" alt="image" />';
              }
              $out .= '</span>';
              $out .= '</span>';
              $out .= '</div>';
              $out .= '<p><button ng-click="stopSession()" class="btn btn-danger" ng-disabled="">'.__("Take the helmet off (Stop training session)").'</button></p>';
              $out .= '</div>';
              $out .= '</div>';
              $out .= '</div>';
            } else {
              $out .= __("Sorry, but a problem occured. Please try again. If the problem still exists, contact the administrator.");
            }
          }
          $out .= '</div>';
          echo $out;
        } else if ($action == 'fight') {
          // Test if minimum UT has been reached to fight the monster
          if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
            $utGain = utGain($page, $player)[0];
          } else {
            $utGain = 100;
          }
          if ($utGain >= 20) {
            echo '<div ng-app="exerciseApp">';
            // Get player's equipment to set scores alternatives
            $weaponRatio = 0;
            $protectionRatio = 0;
            if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
              $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
              $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
            }
            if ($bestWeapon->id) { $weaponRatio = $bestWeapon->XP; }
            if ($bestProtection->id) { $protectionRatio = $bestProtection->HP; }
            // Get exercise type
            include('./exTemplates/'.$page->type->name.'.php');
            echo '</div>';
          } else {
            echo '<p class="alert alert-danger">'.__("You need to get 20UT on this monster before being able to fight it !").'</p>';
          }
        } else {
          echo __("A problem has occurred. Please tell the administrator.");
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
        $out .= '</ul>';
      }
    $out .= '</div>';
    $out .= '<div class="col-sm-8 text-left">';
      $out .= "<br /><br />";
      if ($page->mostTrained && $page->mostTrained->team->name != "no-team" ) { $team = ' ['.$page->mostTrained->team->title.']'; } else { $team = ''; }
      $out .= '<p><i class="glyphicon glyphicon-thumbs-up"></i> Most trained player : ';
      if ($page->mostTrained) {
        $out .='<span class="label label-success">'.$page->mostTrained->title.$team.' → '.$page->best.'UT</span>';
      } else {
        $out .='Nobody !';
      }
      $out .= '</p>';
    $out .= '</div>';
    $out .= '</div>';

    echo $out;
  }
?>
