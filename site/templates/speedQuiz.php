<?php namespace ProcessWire;
  include("./head.inc"); 
  
  $out = '';
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) { // IE detected
    $out .= $wrongBrowserMessage;
  } else {
    if (!$user->isLoggedin()) {
      $out .= '<p class="well text-center">'.__("You MUST log in to access this page !").'</p>';
    } else {
      if ($user->hasRole("player") && $player->skills->has("name=fighter") || $user->hasRole("teacher") || $user->isSuperuser()) {
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $player = $pages->get("template=player, login=test");
        }
        $monster = $pages->get("id=$input->urlSegment1");
        $redirectUrl = $playground->url.$player->name;
        // Ok, let's start the Speed Quiz !
        $out .= '<div id="speedQuiz" ng-app="exerciseApp">';
        if (isset($player)) {
          $lastVV = $player->get("template=event, task.name=fight-vv, refPage=$monster");
          if (isset($lastVV)) {
            $requirements = true;
          } else {
            $requirements = false;
          }
        } else {
          $requirements = false;
        }
        if ($requirements) {
          if ($user->isSuperuser() || $user->hasRole('teacher')) {
            $out .= '<h2 class="text-center"><span class="label label-danger">'.__("TEACHER quiz !").'</span></h2>';
          }
          $out .= '<div class="row" ng-controller="TrainingCtrl" ng-init="init(\''.$pages->get("name=service-pages")->url.'\', \''.$monster->id.'\', \''.$redirectUrl.'\', \''.$player->id.'\', \''.$pages->get("name=submit-fight")->url.'\')">';
          if ($monster->id) { // Training session starts
            $out .= '<div class="col-sm-12 text-center">';
              $out .= '<h2>';
              $out .= '<span class="label label-success">'.__("Speed Quiz");
              $out .= ' : ';
              $out .= $monster->title.'</span>';
              $out .= ' → ';
              $out .= ' <span class="label label-primary">'.__('Your time').' : <span id="playerTime">{{playerTime}}</span></span>';
              $out .= ' → ';
              $out .= '<span ng-class="{label:true, \'label-default\':true, \'blink\':correct}">'.__("Correct answers").': {{counter}}</span>';
              $out .= '</h2>';
              $out .= '<span class="glyphicon glyphicon-info-sign"></span> '.__("20 correct answers required to stop the timer");
              $out .= '<div class="well trainingBoard" ng-show="waitForStart">Please wait while loading data...';
            $out .= '</div>';
            $out .= '<div class="well trainingBoard" ng-hide="waitForStart">';
            if ($monster->type->name == 'image-map') {
              $out .= '<div class=""><img src="'.$monster->imageMap->first()->url.'" max-width="800" alt="Image" /></div>';
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
              $out .= '<br /><br />';
              $out .= '<h3 id="" ng-bind="playerAnswer"></h3>';
              $out .= '<br />';
              $out .= '<button ng-click="attack()" class="btn btn-success">'.__("Check !").'</button>';
              $out .= '&nbsp;&nbsp;';
              $out .= '<button ng-click="dodge()" class="btn btn-danger">'.__("I don't know").'</button>';
            } else if ($monster->type->name == 'categorize') {
              $out .= '<div class="text-center">';
              $out .= '<h2 class="inline" ng-bind-html="word|paTags"></h2>   ';
              $out .= '</div>';
              $out .= '<br />';
              $out .= '<h2 class="category inline" ng-repeat="c in categories">';
              $out .= '<span ng-click="pickCategory(c)" ng-bind-html="c|paTags"></span>';
              $out .= '</h2>';
            } else {
              $out .= '<div class="text-center">';
              $out .= '<h2 class="inline" ng-bind-html="word|paTags"></h2>   ';
              $out .= ' <h3 class="inline"><span ng-show="wrong"><span class="glyphicon glyphicon-arrow-right" ng-show="wrong"></span> <span ng-bind-html="showCorrection|underline"></span> {{feedback}}</span></h3> ';
              $out .= '</div>';
              $out .= '<br />';
              $out .= '<input type="text" class="input-lg" ng-model="playerAnswer" size="70" placeholder="'.__("Type your answer").'" autocomplete="off" my-enter="attack()" sync-focus-with="isFocused" />';
              $out .= '<br />';
              $out .= '<button ng-click="attack()" class="btn btn-success">'.__("Check !").'</button>';
              $out .= '&nbsp;&nbsp;';
              $out .= '<button ng-click="dodge()" class="btn btn-danger">'.__("I don't know").'</button>';
            }
            $out .= '</div>';
            $out .= '<h4>';
            $out .= '<span class="glyphicon glyphicon-education"></span> '.__("Current record").' : ';
            if (isset($monster->bestTime) && $monster->bestTimePlayerId != 0) {
              $monsterBestTime = $monster->bestTime;
            } else {
              $monsterBestTime = 0;
            }
            if ($monsterBestTime != 0) {
              if ($monster->bestTimePlayerId == $player->id) {
                $out .= '<span id="monsterBestTime" data-ms="'.$monsterBestTime.'">'.ms2string($monsterBestTime).'</span> '.__('by').' <span class="label label-success">YOU !</span>';
              } else {
                $master = $pages->get($monster->bestTimePlayerId);
                $out .= '<span id="monsterBestTime" data-ms="'.$monsterBestTime.'">'.ms2string($monsterBestTime).'</span> '.__('by').' '.$master->title.' ['.$master->team->title.']';
              }
            } else {
              $out .= '<span id="monsterBestTime" data-ms="'.$monsterBestTime.'">'.__("No record yet.");
            }
            $tmpPage = $player->child("name=tmp")->tmpMonstersActivity->get("monster=$monster");
            if (isset($tmpPage) && $tmpPage->bestTime != '') {
              $bestTime = $tmpPage->bestTime;
            } else {
              $bestTime = 0;
            }
            $out .= '   <span id="playerBestTime" data-ms="'.$bestTime.'">('.__("Your best time").': '.ms2string($bestTime).')';
            $out .= '</h4>';
            $out .= '</div>';
            $out .= '<a href="'.$playground->url.$player->name.'" class="btn btn-block btn-danger simpleConfirm">'.__("Quit").'</a>';
            $out .= '</div>';
            $out .= '</div>';
          } else {
            $out .= __("Sorry, but a problem occured. Please try again. If the problem still exists, contact the administrator.");
          }
          $out .= '</div>';
        } else {
          $out .= $noAuthMessage;
        }
      } else {
        $out .= $noAuthMessage;
      }
    }
  }
  echo $out;

  include("./foot.inc"); 
?>
