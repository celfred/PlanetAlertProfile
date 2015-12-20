<?php
  include("./head.inc"); 

  // Test player login
  if ($player && $user->isLoggedin() || $user->isSuperuser()) {
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    $helmet = $player->equipment->get('memory-helmet');
    if ($helmet || $user->isSuperuser()) {
      $out = '<div>';
      if (!$input->get->id) { // Display training catalogue
        // Translate type only (for the moment)
        $allTranslate = $pages->find('template=exercise, type.name=translate');

        $out .= '<div class="row">';
          $out .= '<div class="col-sm-4">';
          $out .= '<h2><span class="label label-primary">Your current U.T. is : '.$player->underground_training.'</span></h2>';
          $out .= '</div>';
          $out .= '<div class="col-sm-8 text-center">';
          $out .= '<h2>Underground Training Zone</h2>';
          $out .= '</div>';
        $out .= '</div>';

        $out .= '<div class="well">';
        $out .= $page->summary;
        $out .= '<span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$page->frenchSummary.'"></span>';
        $out .= '</div>';

        $out .= '<div class="well">';
        $out .= '<img class="pull-right" src="'.$helmet->image->url.'" alt="Helmet" />';
        $out .= '<h3>Vocabulary revisions</h3>';
        $out .= '<ul>';
        foreach($allTranslate as $result) {
          // Get previous player's statistics
          // TODO : Lock page if UT > 5???
          // TODO : Avoid a player to work only on 4 seasons to get more UT
          // TODO : Depends on # of words in the exercise?
          $prevUt = $player->find('template=event,refPage='.$result->id)->count();
          $out .= '<li>';
          if ($prevUt) {
            $out .= '<span class="badge"><span class="glyphicon glyphicon-thumbs-up"></span>'.$prevUt.'</span> ';
          }
          $out .= $result->summary;
          $out .= ' <a role="button" class="" data-toggle="collapse" href="#collapseDiv'.$result->id.'" aria-expanded="false" aria-controls="collapseDiv">[French version]</a>';
          $out .= ' <a class="btn btn-sm btn-success" href="'.$page->url.'?id='.$result->id.'">Put the helmet on!</a>';
          $out .= '<div class="collapse" id="collapseDiv'.$result->id.'"><div class="well">';
          if ($result->frenchSummary != '') {
            $out .= $result->frenchSummary;
          } else {
            $out .= 'French version in preparation, sorry ;)';
          }
          $out .= '</div>';
          $out .= '</li>';
        }
        $out .= '</ul>';
      } else { // Training session
        $out .= '<div ng-app="exerciseApp">';

        $monster = $pages->get($input->get->id);
        $redirectUrl = $pages->get('name=underground-training')->url;
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
          $out .= '<button ng-click="stopSession()" class="btn btn-danger">Take the helmet off (Stop training session)</button>';
          $out .= '</div>';
          $out .= '</div>';
          $out .= '</div>';

          /* $exData = $monster->exData; */ 
          /* $allLines = preg_split('/$\R?^/m', $exData); */
          /* $pair = []; */
          /* foreach($allLines as $line) { */
          /*   list($left, $right) = preg_split('/,/', $line); */
          /*   // TODO : Doesn't work */ 
          /*   //$answer = preg_split('||', $answer); */
          /*   $pair[$left] = $right; */
          /*   $data[] = ['left'=>$left, 'right'=>$right]; */
          /* } */
          /* // TODO : */
          /* // trainingBoard dimensions */
          /* // shuffle tags inside trainingBoard */
          /* // draggable tags */
          /* // disappears when on top */
          /* // => TODO : Pb : difficult / Find an easier solution (and fun?) */
          /* $out .= '<div class="well trainingBoard">'; */
          /* /1* foreach($pair as $word=>$answer) { *1/ */
          /* /1*   $out .= '<span class="btn btn-success">'.$word.'</span>'; *1/ */
          /* /1*   $out .= '<span class="btn btn-danger">'.$answer.'</span>'; *1/ */
          /* /1* } *1/ */
    /* function str_shuffle_unicode($str) { */
        /* $tmp = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY); */
        /* shuffle($tmp); */
        /* return join("", $tmp); */
    /* } */
          /* shuffle($data); */
          /* $out .= '<div class="bubble-right">'; */
          /* $out .= '<h3 class="">Set the pairs in your mind.</h3>'; */
          /* $i = 0; */
          /* foreach($data as $d) { */
          /*   $shuffled = str_shuffle($data[$i]['left']); */
          /*   $out .= '<h4 class="">'; */
          /*   $out .= $data[$i]['right'].' ['.$shuffled.'] : '; */ 
          /*   $out .= '<input type="text" size="50" />'; */
          /*   $out .= '</h4>'; */
          /*   $i++; */
          /* } */
          /* $out .= '</div>'; */
          /* $out .= '</div>'; */
        } else {
          $out .= 'Sorry, but a problem occured. Please try again. If the problem still exists, contact the administrator.';
        }
      }

      echo $out;

      echo '</div>';
    }
  } else {
    echo '<p class="alert alert-warning">Sorry, but you don\'t have access to the Underground Training page. Contact the administrator if yoy think this is an error.</p> ';
  }

  include("./foot.inc"); 
?>
