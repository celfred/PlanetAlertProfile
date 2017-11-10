<?php namespace ProcessWire;
  include("./head.inc"); 

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser()) { // Test player login
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $player->team->forceHelmet == 1) {
      $helmet = $pages->get("name=memory-helmet");
    } else {
      $helmet = $player->equipment->get('memory-helmet');
    }
    if ($helmet) {
      $out = '<div>';
      if (!$input->get->id) { // Display training catalogue
        // Set all available monsters
        if ($user->isSuperuser()) {
          $allMonsters = $pages->find('template=exercise, sort=name, include=all');
        } else {
          // Check if player has the Visualizer (or forced by admin)
          if ($player->equipment->has('name=visualizer') || $player->team->forceVisualizer == 1) {
            $allMonsters = $pages->find('template=exercise, sort=name');
          } else {
            $allMonsters = $pages->find('template=exercise, special=0, sort=name');
          }
        }
        $out .= '<br />';
        $out .= '<div class="well">';
        $out .= '<h2 class="text-center">Underground Training Zone';
        if ($helmet->image) {
          $out .= '<img class="pull-right" src="'.$helmet->image->url.'" alt="Helmet" />';
        }
        $out .= '</h2>';
        $out .= '<p class="text-center">'.$page->summary;
        $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->frenchSummary.'"></span>';
        $out .= '</p>';

        $out .= '<h4 class="text-center">';
        $out .= 'There are currently '.$allMonsters->count().' monsters in the list.';
        $out .= '</h4>';

        $allCategories = $pages->find("parent.name=topics, sort=name");
        $out .= '<div id="Filters" data-fcolindex="1" class="text-center">';
        $out .= '  <ul class="list-inline well">';
        foreach ($allCategories as $category) {
          $out .= '<li><label for="'.$category->name.'" class="btn btn-primary btn-xs">'.$category->title.' <input type="checkbox" value="'.$category->title.'" class="categoryFilter" name="categoryFilter" id="'.$category->name.'"></label></li>';
        }
        $out .= '</ul>';
        $out .= '</div>';
        $out .= '<table id="trainingTable" class="table table-condensed table-hover">';
          $out .= '<thead>';
          $out .= '<tr>';
          $out .= '<th>Name</th>';
          $out .= '<th>Topic</th>';
          $out .= '<th>Level</th>';
          $out .= '<th>Summary</th>';
          $out .= '<th># of words</th>';
          $out .= '<th>U.T. gained</th>';
          $out .= '<th>Last training session</th>';
          $out .= '<th>Action</th>';
          $out .= '<th>Most trained player</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
        $today = new \DateTime("today");
        foreach($allMonsters as $m) {
          if (!$user->isSuperuser()) {
            $m = setMonstersActivity($player, $m);
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
            $out .= ' <span class="badge">New</span>';
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
          if ($m->frenchSummary != '') {
            $fr = $m->frenchSummary;
          } else {
            $fr = 'French version in preparation, sorry ;)';
          }
          $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$fr.'"></span>';
          $out .= '</td>';
          // Count # of words
          $exData = $m->exData;
          $allLines = preg_split('/$\r|\n/', $exData);
          /* Unused because triggers a bug with tooltip display */
          /* $out .= '<td data-sort="'.count($allLines).'">'; */
          $out .= '<td>';
          // Prepare list of French words
          switch ($m->type->name) {
            case 'translate' :
              $out .= count($allLines).' words';
              if (count($allLines)>15) {
                $listWords = '<strong>15 first words :</strong><br />';
                for($i=0; $i<15; $i++) {
                  list($left, $right) = preg_split('/,/', $allLines[$i]);
                  $listWords .= $right.'<br />';
                }
                $listWords .= '[...]';
              } else {
                $listWords = '';
                foreach($allLines as $line) {
                  list($left, $right) = preg_split('/,/', $line);
                  $listWords .= $right.'<br />';
                }
              }
              break;
            case 'quiz' :
              $out .= count($allLines).' questions';
              if (count($allLines)>15) {
                $listWords = '<strong>15 first questions :</strong><br />';
                for($i=0; $i<15; $i++) {
                  list($left, $right) = preg_split('/::/', $allLines[$i]);
                  $listWords .= '- '.$left.'<br />';
                }
                $listWords .= '[...]';
              } else {
                $listWords = '';
                foreach($allLines as $line) {
                  list($left, $right) = preg_split('/::/', $line);
                  $listWords .= '- '.$left.'<br />';
                }
              }
              break;
            case 'image-map' :
              $out .= count($allLines).' words';
              if (count($allLines)>15) {
                $listWords = '<strong>15 first questions :</strong><br />';
                for($i=0; $i<15; $i++) {
                  list($left, $right) = preg_split('/::/', $allLines[$i]);
                  $listWords .= '- '.$right.'<br />';
                }
                $listWords .= '[...]';
              } else {
                $listWords = '';
                foreach($allLines as $line) {
                  list($left, $right) = preg_split('/::/', $line);
                  $listWords .= '- '.$right.'<br />';
                }
              }
              break;
            case 'jumble' :
              $out .= count($allLines).' sentences';
              if (count($allLines)>15) {
                $listWords = '<strong>15 first sentences :</strong><br />';
                for($i=0; $i<15; $i++) {
                  $pattern = '/\$.*?\$/';
                  preg_match($pattern, $allLines[$i], $matches);
                  if ($matches) {
                    $help = preg_replace('/\$/', '', $matches[0]);
                  }
                  $listWords .= '- '.$help.'<br />';
                }
                $listWords .= '[...]';
              } else {
                $listWords = '';
                foreach($allLines as $line) {
                  $pattern = '/\$.*?\$/';
                  preg_match($pattern, $line, $matches);
                  $help = preg_replace('/\$/', '', $matches[0]);
                  $listWords .= '- '.$help.'<br />';
                }
              }
              break;
            default :
              $listWords = '';
          }
          $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
          $out .= '</td>';
          $out .= '<td>';
          if ($m->utGain > 0) {
            $out .= '<span class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> +'.$m->utGain.'</span> ';
          } else {
            $out .= '<span class="label label-danger"><span class="glyphicon glyphicon-thumbs-down"></span> 0</span> ';
          }
          $out .= '</td>';
          // Last training session date
          $out .= '<td>';
          switch ($m->lastTrainingInterval) {
            case 0 :
              $out .= "Today !";
              break;
            case 1 : 
              $out .= "1 day ago.";
              break;
            case '-1' :
              $out .= "Not trained yet.";
              break;
            default:
              $out .= $m->lastTrainingInterval . " days ago.";
              break;
          }
          $out .= '</td>';
          $out .= '<td>';
          if ($m->isTrainable == 1) {
            $out .= ' <a class="btn btn-primary" href="'.$page->url.'?id='.$m->id.'"><i class="glyphicon glyphicon-headphones"></i> Put the helmet on!</a>';
          } else {
            if ($m->waitForTrain == 1) { // Trained today
              $out .= 'Come back tomorrow ;)';
            } else {
              $out .= 'Come back in '.$m->waitForTrain.' days ;)';
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
            $out .= '<span class="label label-'.$class.'">'.$m->best.' UT - '.$m->mostTrained->title.' ['.$m->mostTrained->team->title.']</span>';
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
        $monster = $pages->get($input->get->id);
        $redirectUrl = $pages->get('name=underground-training')->url;

        if (!$user->isSuperuser()) {
          $monster = setMonstersActivity($player, $monster);
        } else { // Never trained (for admin)
          $monster->isTrainable = 1;
          $monster->lastTrainingInterval = -1;
          $monster->spaced = 0;
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
            $out .= '<h3>Memory helmet programmed : '. $monster->summary.'</h3> ';

            $out .= '<div class="col-sm-3">';
            $out .= '<h3><span ng-class="{label:true, \'label-primary\':true}">Training session <span class="blink">started</span></span></h3>';
            $out .= '<br />';
            $out .= '<h4><span ng-class="{label:true, \'label-primary\':true}">Current counter: {{counter}}</span> → <span class="label label-primary">+{{result}}UT</span></h4>';
            $out .= '<span class="glyphicon glyphicon-info-sign"></span> 10 words/sentences = +1UT';
            $out .= '<br /><br />';
            $out .= '<div class="panel panel-success">';
            $out .= '<div class="panel-heading">';
            $out .= '<h4 class="panel-title"><span class="glyphicon glyphicon-education"></span> Current record</h4>';
            $out .= '</div>';
            $out .= '<div class="panel-body">';
            if ($monster->mostTrained->id) {
              $out .= '<h4 class="text-center">'.$monster->best.'UT by '.$monster->mostTrained->title.' ['.$monster->mostTrained->team->title.']</h4>';
            } else {
              $out .= '<h4 class="text-center">No record yet.</h4>';
            }
            $out .= '</div>';
            $out .= '<div class="panel-footer">';
            if (!$user->isSuperuser()) {
              $out .= '<p>Your global UT for this monster: '.utGain($monster, $player).'</p>';
            }
            $out .= '</div>';
            $out .= '</div>';
            /* $out .= '<h1><span class="glyphicon glyphicon-warning-sign"></span> Don\'t forget to save your result!</h1>'; */
            $out .= '</div>';

            $out .= '<div class="col-sm-9 text-center">';
            $out .= '<div class="well trainingBoard" ng-show="waitForStart">Please wait while loading data...';
            $out .= '</div>';
            $out .= '<div class="well trainingBoard" ng-hide="waitForStart">';
            if ($monster->type->name == 'image-map') {
              $out .= '<div class=""><img src="'.$monster->imageMap->url.'" max-width="400" alt="Image" /></div>';
            }
            if ($monster->type->name == 'jumble') {
              $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="Click on the words to build a correct sentence. If you make a mistake, use the \'Try again\' button. If you\'re wrong, the correct answer will be shown and you just have to copy the correction.<br />See documentation for more information."></span>';
            } else {
              $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="Type your answer. If you don\'t know, just hover on the glasses to see the mixed letters. If you\'re wrong, the correct answer will be shown and you just have to copy the correction.<br />See documentation for more information."></span>';
            }
            $out .= '<div class="bubble-right">';
            if ($monster->type->name == 'jumble') {
              $out .= '<div class="text-center">';
              $out .= '<h2 class="jumbleW inline" ng-repeat="w in word track by $index">';
              $out .= '<span ng-class="{\'label\':true, \'label-primary\':selectedItems.indexOf($index) === -1, \'label-warning\':selectedItems.indexOf($index) !== -1}" ng-click="pickWord(w, $index)">{{w}}</span>';
              $out .= '</h2>';
              $out .= '</div>';
              $out .= ' <h3><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{showCorrection}} {{feedBack}}</span></h3> ';
              $out .= '<button class="btn btn-danger btn-xs" ng-click="clear()">Try again</button>';
              $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
              $out .= '<span id="clue">{{mixedWord}}</span>';
              $out .= '<br /><br />';
              $out .= '<h3 id="" ng-bind="playerAnswer"></h3>';
            } else {
              $out .= '<div class="text-center">';
              $out .= '<h2 class="inline" ng-bind-html="word"></h2>   ';
              $out .= '&nbsp;&nbsp;&nbsp;<h2 class="inline"><span class="glyphicon glyphicon-sunglasses" onmouseenter="$(\'#clue\').show();" onmouseleave="$(\'#clue\').hide();"></span></h2>';
              $out .= '<span id="clue">{{mixedWord}}</span>';
              $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> <span ng-bind-html="showCorrection|underline"></span> {{feedBack}}</span></h3> ';
              $out .= '</div>';
              $out .= '<br />';
              $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="Type your answer" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
            }
            $out .= '<br />';
            $out .= '<button ng-click="attack()" class="btn btn-success">Stimulate!</button>';
            $out .= '&nbsp;&nbsp;';
            $out .= '<button ng-click="dodge()" class="btn btn-danger">I don\'t know</button>';
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
            $out .= '<button ng-click="stopSession()" class="btn btn-danger" ng-disabled="">Take the helmet off (Stop training session)</button>';
            $out .= '</div>';
            $out .= '</div>';
            $out .= '</div>';

          } else {
            $out .= 'Sorry, but a problem occured. Please try again. If the problem still exists, contact the administrator.';
          }
        }
      }

      echo $out;

      echo '</div>';
      echo '</div>';
    }
  } else {
    echo '<p class="alert alert-warning">Sorry, but you don\'t have access to the Underground Training page. Contact the administrator if yoy think this is an error.</p> ';
  }

  include("./foot.inc"); 
?>
