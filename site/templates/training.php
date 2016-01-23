<?php
  include("./head.inc"); 

  // Test player login
  if ($player && $user->isLoggedin() || $user->isSuperuser()) {
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    if ($user->isSuperuser()) {
      $helmet = $pages->get("name=memory-helmet");
    } else {
      $helmet = $player->equipment->get('memory-helmet');
    }
    if ($helmet) {
      $out = '<div>';
      if (!$input->get->id) { // Display training catalogue
        // Translate / Quiz types only (for the moment)
        if ($user->isSuperuser()) {
          $allTranslate = $pages->find('template=exercise, type.name=translate|quiz, sort=name, include=hidden');
        } else {
          $allTranslate = $pages->find('template=exercise, type.name=translate|quiz, sort=name');
        }

        $out .= '<div class="row">';
          $out .= '<div class="col-sm-12 text-center">';
          $out .= '<h2><span class="label label-default">Underground Training Zone</span></h2>';
          $out .= '</div>';
        $out .= '</div>';

        $out .= '<div class="well">';
        $out .= $page->summary;
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->frenchSummary.'"></span>';
        $out .= '</div>';

        $out .= '<div class="well">';
        $out .= '<h3 class="text-center">';
        $out .= '<img width="50" src="'.$helmet->image->url.'" alt="Helmet" />';
        $out .= ' Memory Helmet <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="Vocabulary Revisions"></span>';
        $out .= '</h3>';
        $out .= '<table id="trainingTable" class="table table-condensed table-hover">';
          $out .= '<thead>';
          $out .= '<tr>';
          $out .= '<th>Name</th>';
          $out .= '<th>Topic</th>';
          $out .= '<th>Level</th>';
          $out .= '<th>Summary</th>';
          $out .= '<th># of words</th>';
          $out .= '<th>Already trained?</th>';
          $out .= '<th>Last training session</th>';
          $out .= '<th>Action</th>';
          $out .= '</tr>';
          $out .= '</thead>';
          $out .= '<tbody>';
        foreach($allTranslate as $result) {
          // Get previous player's statistics
          $prevUt = $player->find('template=event,refPage='.$result->id.', sort=-date');
          $out .= '<tr>';
          $out .= '<td>';
          $out .= $result->title;
          // Find # of days compared to today to set 'New' indicator
          $date1 = new DateTime("today");
          $date2 = new DateTime(date("Y-m-d", $result->published));
          $interval = $date1->diff($date2);
          if ($interval->days < 7) {
            $out .= ' <span class="badge">New</span>';
          }
          $out .= '</td>';
          $out .= '<td>';
          foreach ($result->topic as $t) {
            $out .= '<span class="label label-primary">'.$t->title.'</span> ';
          }
          $out .= '</td>';
          $out .= '<td>';
          $out .= $result->level;
          $out .= '</td>';
          $out .= '<td>';
          $out .= $result->summary;
          if ($result->frenchSummary != '') {
            $fr = $result->frenchSummary;
          } else {
            $fr = 'French version in preparation, sorry ;)';
          }
          $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$fr.'"></span>';
          $out .= '</td>';
          // Count # of words
          $exData = $result->exData;
          $allLines = preg_split('/$\r|\n/', $exData);
          /* $out .= '<td data-sort="'.count($allLines).'">'; */
          $out .= '<td>';
          // Prepare list of French words
          switch ($result->type->name) {
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
            default :
              $listWords = '';
              break;
          }
          $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
          $out .= '</td>';
          $out .= '<td>';
          if ($prevUt->count > 0) {
            $out .= '<span class="label label-success"><span class="glyphicon glyphicon-thumbs-up"></span> '.$prevUt->count.'</span> ';
          } else {
            $out .= '<span class="label label-danger"><span class="glyphicon glyphicon-thumbs-down"></span></span> ';
          }
          $out .= '</td>';
          // Last training session date
          $out .= '<td>';
          if ($prevUt->count > 0) {
            // Find # of days compared to today
            $date1 = new DateTime("today");
            $date2 = new DateTime(date("Y-m-d", $prevUt->first->date));
            $interval = $date1->diff($date2);
            switch ($interval->days) {
              case 1 : 
                $out .= "1 day ago.";
                break;
              case 0 :
                $out .= "Today !";
                break;
              default:
                $out .= $interval->days . " days ago.";
                break;
            }
          }
          $out .= '</td>';
          $out .= '<td>';
          $spaced = isTrainingAllowed($player, $result);
          switch ($spaced) {
            case 1 : 
              $out .= 'Come back tomorrow ;)';
              break;
            case 0 :
              $out .= ' <a class="label label-sm label-primary" href="'.$page->url.'?id='.$result->id.'">Put the helmet on!</a>';
              break;
            default:
              $out .= 'Come back in '.$spaced.' days ;)';
              break;
          }
          // Admin access
          if ($user->isSuperuser()) {
            $out .= ' <a class="label label-sm label-success" href="'.$page->url.'?id='.$result->id.'">Put the helmet on!</a>';
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

        $isAllowed = isTrainingAllowed($player, $monster);
        if ($isAllowed !== 0) { // Not allowed because of spaced repetition.
          // Redirect to training page
          $session->redirect($redirectUrl);
        } else { // Ok, let's start the training session !
          $out .= '<div ng-app="exerciseApp">';
          $out .= '<div class="row" ng-controller="TrainingCtrl" ng-init="init(\''.$monster.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$pages->get("name=submit-fight")->url.'\')">';
          if ($monster->id) { // Training session starts
            $out .= '<h1>Memory helmet programmed : '. $monster->summary.'</h1> ';

            $out .= '<div class="col-sm-3">';
            $out .= '<h3><span ng-class="{label:true, \'label-primary\':true, blink:correct}">Training session <span class="blink">started</span></span></h3>';
            $out .= '<h1><span ng-class="{label:true, \'label-primary\':true, blink:correct}">Word count: {{counter}}</span></h1>';
            /* $out .= '<h1 ng-class="{zoom:utPoint}"><span class="label label-primary">+{{result}} U.T.</span></h1>'; */
            /* $out .= '<h1><span class="glyphicon glyphicon-warning-sign"></span> Don\'t forget to save your result!</h1>'; */
            $out .= '</div>';

            $out .= '<div class="col-sm-9 text-center">';
            $out .= '<div class="well trainingBoard" ng-show="waitForStart">Please wait while loading data...';
            $out .= '</div>';
            $out .= '<div class="well trainingBoard" ng-hide="waitForStart">';
            $out .= '<span class="pull-right glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="Type your answer. If you don\'t know, just hover on the glasses to see the mixed letters. If you\'re wrong, the correct answer will be shown and you just have to copy the correction.<br />See documentation for more information."></span>';
            $out .= '<div class="bubble-right">';
            $out .= '<div class="text-center">';
            $out .= '<h2 class="inline" ng-bind-html="word"></h2>   ';
            $out .= ' <h3 class="inline"><span class="glyphicon glyphicon-sunglasses" data-toggle="tooltip" data-html="true" title="{{mixedWord}}"></span></h3> ';
            $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> {{allCorrections[0]}}</span></h3> ';
            $out .= '</div>';
            $out .= '<br />';
            $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="50" placeholder="Type your answer" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
            $out .= '<br />';
            $out .= '<button ng-click="attack()" class="btn btn-success">Stimulate!</button>';
            $out .= '<span class="pull-right">';
            $out .= '<span class="avatarContainer">';
            if ($player->avatar) {
              $out .= '<img class="" src="'.$player->avatar->getThumb("thumbnail").'" alt="Avatar" />';
            } else {
              $out .= '<img src="'.$page->image->url.'" alt="Avatar" />';
            }
            $out .= '<img class="helmet superpose squeeze" src="'.$helmet->image->url.'" alt="image" />';
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
