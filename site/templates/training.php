<?php namespace ProcessWire;
  include("./head.inc"); 

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser() || $user->hasRole('teacher')) {
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $user->hasRole('teacher') || $player->team->forceHelmet == 1) {
      $helmet = $pages->get("name=memory-helmet");
    } else {
      $helmet = $player->equipment->get('memory-helmet');
    }
    if ($helmet) {
      $out = '<div>';
      if (!$input->get->id) { // Display training catalogue
        // Set all available monsters
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $allMonsters = $pages->find('template=exercise, sort=name, include=all');
        } else {
          // Check if player has the Visualizer (or forced by admin)
          if ($player->equipment->has("name~=visualizer") || $player->team->forceVisualizer == 1) {
            $allMonsters = $pages->find("template=exercise, teacher=$headTeacher, sort=name");
          } else {
            $allMonsters = $pages->find("template=exercise, teacher=$headTeacher, special=0, sort=name");
            $hiddenMonstersNb = $pages->count("template=exercise, special=1");
          }
        }
        $out .= '<br />';
        $out .= '<div class="well">';
        $out .= '<h2 class="text-center">'.$page->title;
        if ($helmet->image) {
          $out .= '<img class="pull-right" src="'.$helmet->image->url.'" alt="Helmet" />';
        }
        $out .= '</h2>';
        $out .= '<p class="text-center">'.$page->summary.'</p>';

        $out .= '<h4 class="text-center">';
        $out .= sprintf(__("There are currently %d monsters detected."), $allMonsters->count());
        if (isset($hiddenMonstersNb)) {
          $link = '<a href="'.$pages->get("name=shop")->url.'/details/electronic-visualizer">Electronic Visualizer</a>';
          $out .= '<p>('.sprintf(__('%1$s monsters are absent because you don\'t have the %2$s.'), $hiddenMonstersNb, $link).')</p>';
        } else {
          $out .= '<p>('.__("All monsters are visible thanks to your Electronic Visualizer.").')</p>';
        }
        $out .= '</h4>';

        $allCategories = $pages->find("parent.name=topics, sort=name");
        $out .= '<div id="Filters" data-fcolindex="1" class="text-center">';
        $out .= '  <ul class="list-inline well">';
        foreach ($allCategories as $category) {
          if ($allMonsters->get("topic=$category")) {
            $out .= '<li><label for="'.$category->name.'" class="btn btn-primary btn-xs">'.$category->title.' <input type="checkbox" value="'.$category->title.'" class="categoryFilter" name="categoryFilter" id="'.$category->name.'"></label></li>';
          }
        }
        $out .= '</ul>';
        $out .= '</div>';
        $out .= '<table id="trainingTable" class="table table-condensed table-hover">';
          $out .= '<thead>';
          $out .= '<tr>';
          $out .= '<th>'.__("Name").'</th>';
          $out .= '<th>'.__("Topic").'</th>';
          $out .= '<th>'.__("Level").'</th>';
          $out .= '<th>'.__("Summary").'</th>';
          $out .= '<th>'.__("# of words").'</th>';
          $out .= '<th>'.__("U.T. gained").'</th>';
          $out .= '<th>'.__("Last training session").'</th>';
          $out .= '<th>'.__("Action").'</th>';
          $out .= '<th>'.__("Most trained player").'</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
        $today = new \DateTime("today");
        foreach($allMonsters as $m) {
          if (!$user->isSuperuser()) {
            // Prepare player's training possibilities
            setMonster($player, $m);
          } else { // Never trained (for admin)
            $m->isTrainable = 1;
            $m->lastTrainingInterval = -1;
            $m->waitForTrain = 0;
          }
          $out .= '<tr>';
          $out .= '<td>';
          $out .= $m->title;
          // Find # of days compared to today to set 'New' indicator
          $date2 = new \DateTime(date("Y-m-d", $m->published));
          $interval = $today->diff($date2);
          if ($interval->days < 7) {
            $out .= ' <span class="badge">'.__("New").'</span>';
          }
          if ($m->special) {
            $out .= ' <span class="badge">'.__("Detected").' !</span>';
          }
          $out .= '</td>';
          $out .= '<td>';
          $out .= '<span class="label label-default">'.$m->topic->implode(', ', '{title}').'</span>';
          $out .= '</td>';
          $out .= '<td>';
          $out .= $m->level;
          $out .= '</td>';
          $out .= '<td>';
          $out .= $m->summary;
          if ($user->language->name != 'french') {
            $m->of(false);
            if ($m->summary->getLanguageValue($french) != '') {
              $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$m->summary->getLanguageValue($french).'"></span>';
            }
          }
          $out .= '</td>';
          // Count # of words
          $exData = $m->exData;
          $allLines = preg_split('/$\r|\n/', $exData);
          /* Unused because triggers a bug with tooltip display */
          /* $out .= '<td data-sort="'.count($allLines).'">'; */
          $out .= '<td>';
          $listWords = prepareListWords($allLines, $m->type->name);
          switch ($m->type->name) {
            case 'translate' :
              $out .= count($allLines).' '.__("words");
              break;
            case 'quiz' :
              $out .= count($allLines).' '.__("questions");
              break;
            case 'image-map' :
              $out .= count($allLines).' '.__("words");
              break;
            case 'jumble' :
              $out .= count($allLines).' '.__("sentences");
              break;
            default : continue;
          }
          $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
          $out .= '</td>';
          $out .= '<td>';
          if ($user->hasRole('player')) {
            if ($m->utGain > 0) {
              $out .= '<span class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> +'.$m->utGain.'</span> ';
            } else {
              $out .= '<span class="label label-danger"><span class="glyphicon glyphicon-thumbs-down"></span> 0</span> ';
            }
          } else {
            if ($user->isSuperuser()) {
              $out .= '[Admin]';
            } else {
              $out .= '['.__("Teacher").']';
            }
          }
          $out .= '</td>';
          // Last training session date
          $out .= '<td>';
          if ($user->hasRole('player')) {
            if ($m->lastTrainingInterval != '-1') {
              $out .= $m->lastTrainingInterval;
            } else {
              $out .= '-';
            }
          } else {
            if ($user->isSuperuser()) {
              $out .= '[Admin]';
            } else {
              $out .= '['.__("Teacher").']';
            }
          }
          $out .= '</td>';
          $out .= '<td>';
          if ($m->isTrainable == 1) {
            $out .= ' <a class="btn btn-primary" href="'.$page->url.'?id='.$m->id.'"><i class="glyphicon glyphicon-headphones"></i> '.__("Put the helmet on !").'</a>';
          } else {
            if ($m->waitForTrain == 1) { // Trained today
              $out .= __('Come back tomorrow ;)');
            } else {
              $out .= sprintf(__("Come back in %d days ;)"), $m->waitForTrain);
            }
          }
          $out .= '</td>';
          // Find best trained player on this monster
          if ($m->mostTrained) {
            if (isset($player) && $m->mostTrained == $player) {
              $class = 'success';
            } else {
              $class = 'primary';
            }
          }
          $out .= '<td data-sort="'.$m->best.'">';
          if ($m->mostTrained) {
            $out .= '<span class="label label-'.$class.'">'.$m->best.' '.__("UT").' - '.$m->mostTrained->title.' ['.$m->mostTrained->team->title.']</span>';
          } else {
            $out .= '<span>No record yet.</span>';
          }
          $out .= '</td>';
          $out .= '</tr>';
        }
        $out .= '</tbody>';
        $out .= '</table>';
      } else { // Training session
        // Test if player is allowed to do the training session today
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
        } else {
        }
        $redirectUrl = $pages->get('name=underground-training')->url;
        if (!$user->isSuperuser() && !$user->hasRole('teacher')) {
          $monster = $pages->get($input->get->id);
          setMonster($player, $monster);
        } else { // Never trained (for admin)
          $monsterId = $input->get->id;
          $monster = $pages->get("id=$monsterId, include=all");
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
              $out .= '<div class=""><img src="'.$monster->imageMap->url.'" max-width="400" alt="Image" /></div>';
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
              $out .= '<span ng-class="{\'label\':true, \'label-primary\':selectedItems.indexOf($index) === -1, \'label-warning\':selectedItems.indexOf($index) !== -1}" ng-click="pickWord(w, $index)">{{w}}</span>';
              $out .= '</h2>';
              $out .= '</div>';
              $out .= ' <h3><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{showCorrection}} {{feedBack}}</span></h3> ';
              $out .= '<button class="btn btn-danger btn-xs" ng-click="clear()">'.__("Try again").'</button>';
              $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
              $out .= '<span id="clue" ng-show="showClue">{{mixedWord}}</span>';
              $out .= '<br /><br />';
              $out .= '<h3 id="" ng-bind="playerAnswer"></h3>';
            } else {
              $out .= '<div class="text-center">';
              $out .= '<h2 class="inline" ng-bind-html="word"></h2>   ';
              $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline" ng-show="showClue"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
              $out .= '<span id="clue" ng-show="showClue">{{mixedWord}}</span>';
              $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> <span ng-bind-html="showCorrection|underline"></span> {{feedBack}}</span></h3> ';
              $out .= '</div>';
              $out .= '<br />';
              $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="'.__("Type your answer").'" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
            }
            $out .= '<br />';
            $out .= '<button ng-click="attack()" class="btn btn-success">'.__("Stimulate !").'</button>';
            $out .= '&nbsp;&nbsp;';
            $out .= '<button ng-click="dodge()" class="btn btn-danger">'.__("I don't know").'</button>';
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
            $out .= '<button ng-click="stopSession()" class="btn btn-danger" ng-disabled="">'.__("Take the helmet off (Stop training session)").'</button>';
            $out .= '</div>';
            $out .= '</div>';
            $out .= '</div>';
          } else {
            $out .= __("Sorry, but a problem occured. Please try again. If the problem still exists, contact the administrator.");
          }
        }
      }

      echo $out;

      echo '</div>';
      echo '</div>';
    }
  } else {
    echo $noAuthMessage;
  }

  include("./foot.inc"); 
?>
