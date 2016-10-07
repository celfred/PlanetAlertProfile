<?php
  include("./head.inc"); 

  // Test player login
  if ($player && $user->isLoggedin() || $user->isSuperuser()) {
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
        // Display Personal Mission Analyzer
        if (!$user->isSuperuser()) {
          echo pma($player);
        }

        // Translate / Quiz / Image-map types only (for the moment)
        if ($user->isSuperuser()) {
          $allMonsters = $pages->find('template=exercise, type.name=translate|quiz|image-map, sort=name, include=all');
        } else {
          $allMonsters = $pages->find('template=exercise, type.name=translate|quiz|image-map, sort=name');
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
        foreach($allMonsters as $m) {
          $m = isTrainingAllowed($player, $m);
          // Get previous player's statistics
          $prevUt = $player->find('template=event,refPage='.$result->id.', sort=-date');
          $out .= '<tr>';
          $out .= '<td>';
          $out .= $m->title;
          // Find # of days compared to today to set 'New' indicator
          $date1 = new DateTime("today");
          $date2 = new DateTime(date("Y-m-d", $m->published));
          $interval = $date1->diff($date2);
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
                  list($left, $right) = preg_split('/\?/', $allLines[$i]);
                  $listWords .= '- '.$left.'<br />';
                }
                $listWords .= '[...]';
              } else {
                $listWords = '';
                foreach($allLines as $line) {
                  list($left, $right) = preg_split('/\?/', $line);
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
            default :
              $listWords = '';
              break;
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
          if ($m->lastTrainingDate != 0) {
            switch ($m->interval) {
              case 0 :
                $out .= "Today !";
                break;
              case 1 : 
                $out .= "1 day ago.";
                break;
              default:
                $out .= $m->interval . " days ago.";
                break;
            }
          } else {
            $out .= "Not trained yet.";
          }
          $out .= '</td>';
          $out .= '<td>';
          if ($m->isTrainable != 0 && $m->spaced == 0) {
            $out .= ' <a class="label label-sm label-primary" href="'.$page->url.'?id='.$m->id.'">Put the helmet on!</a>';
          } else {
            if ($m->spaced == 1) {
              $out .= 'Come back tomorrow ;)';
            } else {
              $out .= 'Come back in '.$m->spaced.' days ;)';
            }
          }
          // Admin access
          if ($user->isSuperuser()) {
            $out .= ' <a class="label label-sm label-success" href="'.$page->url.'?id='.$m->id.'">Put the helmet on!</a>';
          }
          $out .= '</td>';
          // Find best trained player on this monster
          if ($m->mostTrained) {
            if ($m->mostTrained == $player) {
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

        $monster = isTrainingAllowed($player, $monster);
        if ($monster->isTrainable == 0) { // Not allowed because of spaced repetition.
          // Redirect to training page
          $session->redirect($redirectUrl);
        } else { // Ok, let's start the training session !
          $out .= '<div ng-app="exerciseApp">';
          $out .= '<div class="row" ng-controller="TrainingCtrl" ng-init="init(\''.$monster.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$pages->get("name=submit-fight")->url.'\')">';
          if ($monster->id) { // Training session starts
            $out .= '<h3>Memory helmet programmed : '. $monster->summary.'</h3> ';

            $out .= '<div class="col-sm-3">';
            $out .= '<h3><span ng-class="{label:true, \'label-primary\':true, blink:correct}">Training session <span class="blink">started</span></span></h3>';
            $out .= '<h2><span ng-class="{label:true, \'label-primary\':true, blink:correct}">Word count: {{counter}}</span></h2>';
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
            $out .= '<p>Your global UT for this monster: '.utGain($monster, $player).'</p>';
            $out .= '</div>';
            $out .= '</div>';
            /* $out .= '<h1 ng-class="{zoom:utPoint}"><span class="label label-primary">+{{result}} U.T.</span></h1>'; */
            /* $out .= '<h1><span class="glyphicon glyphicon-warning-sign"></span> Don\'t forget to save your result!</h1>'; */
            $out .= '</div>';

            $out .= '<div class="col-sm-9 text-center">';
            $out .= '<div class="well trainingBoard" ng-show="waitForStart">Please wait while loading data...';
            $out .= '</div>';
            $out .= '<div class="well trainingBoard" ng-hide="waitForStart">';
            if ($monster->type->name == 'image-map') {
              $out .= '<div class=""><img src="'.$monster->imageMap->url.'" width="400" alt="Image" /></div>';
            }
            $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="Type your answer. If you don\'t know, just hover on the glasses to see the mixed letters. If you\'re wrong, the correct answer will be shown and you just have to copy the correction.<br />See documentation for more information."></span>';
            $out .= '<div class="bubble-right">';
            $out .= '<div class="text-center">';
            $out .= '<h2 class="inline" ng-bind-html="word"></h2>   ';
            $out .= ' <h3 class="inline"><span class="glyphicon glyphicon-sunglasses" data-toggle="tooltip" data-html="true" title="{{mixedWord}}"></span></h3> ';
            $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{showCorrection}} {{feedBack}}</span></h3> ';
            $out .= '</div>';
            $out .= '<br />';
            $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="Type your answer" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
            $out .= '<br />';
            $out .= '<button ng-click="attack()" class="btn btn-success">Stimulate!</button>';
            $out .= '<span class="pull-right">';
            $out .= '<span class="avatarContainer">';
            if ($player->avatar) {
              $out .= '<img class="" src="'.$player->avatar->getThumb("thumbnail").'" alt="Avatar" />';
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
