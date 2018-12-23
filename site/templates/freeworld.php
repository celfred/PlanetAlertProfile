<?php namespace ProcessWire;
  include("./head.inc"); 

  $team = $pages->get("template=team, name=$input->urlSegment1");
  $rank = $team->rank->index;
  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    include("./tabList.inc");
  }
  $out = '';
  if ($team->name != 'no-team') {
    $allPlayers = $allPlayers->find("team=$team"); // Limit to team players
    // Set team stats
    $teamRate = round(($allPlayers->count()*20)/100);
    $teamRate == 0 ? $teamRate = 1 : '';
    $valid = true;
    $individual = false;
  } else { // Individual free world for no-team players
    if ($user->isSuperuser() || $user->hasRole('teacher') || $pages->get("login=$user->name")->team->name != 'no-team') {
      $out .= '<h3>'.__("No team players have indivual free world stats.").'</h3>';
      $valid = false;
      $individual = false;
    } else { // A player is looking at no-team free world
      $teamRate = 1;
      $valid = true;
      $individual = true;
      $playerPage = $pages->get("template=player, login=$user->name");
    }
  }

  if ($valid) {
    if (!($allTeams->count() == 1 && $allTeams->eq(0)->name == 'no-team')) { // Means Just no-team
      showScores($allTeams);
    }
    if ($individual) { 
      $allElements = new pageArray();
      $rank = $team->rank->index;
      if ($rank >= 8) {
        $allPlaces = $pages->find("template=place, name!=places");
        $allElements->add($allPlaces);
        $allPeople = $pages->find("template=people, name!=people");
        $allElements->add($allPeople);
      } else {
        $allPlaces = $pages->find("template=place, name!=places");
        $allElements->add($allPlaces);
      }
      $allPlayersElements = $playerPage->places->count()+$playerPage->people->count();
      $percent = round(($allPlayersElements*100)/$allElements->count());
      $out .= '<h3 class="text-center">'.__("Individual free world").' : '.$percent.'%</h3>';
      foreach($allElements as $el) {
        if ($el->is("template=place") && $playerPage->places->has($el)) {
          $el->completed = 1;
          $el->cssClass = 'completed';
        } else {
          if ($el->is("template=people") && $playerPage->people->has($el)) {
            $el->completed = 1;
            $el->cssClass = 'completed';
          } else {
            $el->completed = 0;
            $el->cssClass = 'far';
          }
        }
      }
    } else {
      $allElements = teamFreeworld($team);
      $out .= '<h4 class="text-center">['.__("Team rate").' : '.$teamRate.']';
      $out .= ' <span data-toggle="tooltip" data-html="true" title="'.__("# of players required to complete a place and increase %.").'" class="glyphicon glyphicon-question-sign"></span></h4>';
    }

    $allCompleted = $allElements->find("completed=1");
    $notCompleted = $allElements->find("completed!=1");
    $allElements->sort("level, title");
    $out .= '<section class="freeWorld col-sm-6">';
    if ($notCompleted->count() != 0) {
      $out .= '<h2><span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i> '.sprintf(_n("%d incomplete element", "%d incomplete elements", $notCompleted->count()), $notCompleted->count()).'</span></h2>';
    } else {
      $out .= '<h2><span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i> '.__("No  more incomplete elements.").'</span></h2>';
    }
    foreach($notCompleted as $el) {
      if ($el->photo) {
        $thumbImage = $el->photo->eq(0)->getCrop('thumbnail')->url;
      }
      $out .= '<div>';
      $title = '<h3>'.$el->title.'</h3>';
      $title .= '<h4>'.__("Level").' '.$el->level;
      $title .= ', '.$el->GC.__("GC").'</h4>';
      if ($team->name != 'no-team') {
        if ($el->teamOwners->count() > 0 && $el->teamOwners->count() < 10) {
          $ownerList = '['.$el->teamOwners->implode(', ', '{title}').']';
        } else {
          $ownerList = '';
        }
        if ($el->teamOwners->count() > 0) {
          $title .= '<h4>'.__("Freed by").' <span class=\'label label-success\'>'.$el->teamOwners->count().'</span> ';
          $title.= __('player·s').' '.$ownerList.'</h4>';
        } else {
          $title = '<h4>'.__("Freed by nobody.").'</h4>';
        }
        if ($el->completed != 1) {
          $left = $teamRate - $el->teamOwners->count();
          $title .= '<br /><h4><span class=\'label label-primary\'>'.$left.' '.__('more needed !').'</span></h4>';
        }
      }
      $out .= '<a href="'.$el->url.'" class=""><img class="'.$el->cssClass.'" src="'.$thumbImage.'" data-toggle="tooltip" data-html="true" title="'.$title.'" /></a>';
      $out .= "</div>";
    }
    $out .= '</section>';
    $out .= '<section class="freeWorld">';
    if ($allCompleted->count() != 0) {
      $out .= '<h2><span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i> '.sprintf(_n("%d complete element", "%d complete elements", $allCompleted->count()), $allCompleted->count()).'</span></h2>';
    } else {
      $out .= '<h2><span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i> '.__("No free elements.").'</span></h2>';
    }
    foreach($allCompleted as $el) {
      if ($el->photo) {
        $thumbImage = $el->photo->eq(0)->getCrop('thumbnail')->url;
      }
      $out .= '<div>';
      if ($team->name != 'no-team') {
        $title = '<h3>'.$el->title.'</h3>';
        $title .= '<h4>'.__("Level").' '.$el->level;
        $title .= ', '.$el->GC.__("GC").'</h4>';
        if ($el->teamOwners->count() > 0 && $el->teamOwners->count() < 10) {
          $ownerList = '['.$el->teamOwners->implode(', ', '{title}').']';
        } else {
          $ownerList = '';
        }
        $title .= '<h4>'.__("Freed by").' <span class=\'label label-success\'>'.$el->teamOwners->count().'</span> players '.$ownerList.'</h4>';
      }
      $out .= '<a href="'.$el->url.'" class=""><img class="'.$el->cssClass.'" src="'.$thumbImage.'" data-toggle="tooltip" data-html="true" title="'.$title.'" /></a>';
      $out .= "</div>";
    }
    $out .= '</section>';
  }

  echo $out;

  include("./foot.inc"); 
?>
