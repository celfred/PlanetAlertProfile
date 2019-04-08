<?php namespace ProcessWire;
  include("./head.inc"); 

  if (isset($player) && $player->team->is("name=test-team")) {
    $selectedTeam = $pages->get("name=test-team");
  }
  $rank = $selectedTeam->rank->index;

  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    include("./tabList.inc");
  }

  $out = '';
  if ($selectedTeam->name != 'no-team') {
    $teamRate = setTeamRate($selectedTeam->id); // Set team stats
    $valid = true;
    $individual = false;
  } else { // Individual free world for no-team players
    if ($user->isSuperuser() || $user->hasRole('teacher') || $pages->get("login=$user->name")->team->name != 'no-team') {
      $out .= '<h3>'.__("No team players have individual free world stats.").'</h3>';
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
    $out .= getScoresSummaries($headTeacher);
    if ($individual) { 
      $allElements = new pageArray();
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
      $allElements = teamFreeworld($selectedTeam);
      $out .= '<h4 class="text-center">['.__("Team rate").' : '.$teamRate.']';
      $out .= ' <span data-toggle="tooltip" data-html="true" title="'.__("# of players required to complete a place and increase %.").'" class="glyphicon glyphicon-question-sign"></span></h4>';
    }

    $freeworld = $cache->get("cache__freeworld-".$selectedTeam->name, 86400, function() use($allElements, $selectedTeam, $teamRate) {
      $out = '';
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
          $thumbImage = $el->photo->eq(0)->getCrop('mini')->url;
        }
        $out .= '<div>';
        $title = '<h3>'.$el->title.'</h3>';
        $title .= '<h4>'.__("Level").' '.$el->level;
        $title .= ', '.$el->GC.__("GC").'</h4>';
        if ($selectedTeam->name != 'no-team') {
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
          $left = $teamRate - $el->teamOwners->count();
          $title .= '<br /><h4><span class=\'label label-primary\'>'.$left.' '.__('more needed !').'</span></h4>';
        }
        $out .= '<a href="'.$el->url.'" class=""><img class="'.$el->cssClass.'" src="'.$thumbImage.'" data-toggle="tooltip" data-html="true" title="'.$title.'" alt="'.$el->title.'."/></a>';
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
        if ($selectedTeam->name != 'no-team') {
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
        $out .= '<a href="'.$el->url.'" class=""><img class="'.$el->cssClass.'" src="'.$thumbImage.'" data-toggle="tooltip" data-html="true" title="'.$title.'" alt="'.$el->title.'." /></a>';
        $out .= "</div>";
      }
      $out .= '</section>';
      return $out;
    });
    $out .= $freeworld;
  }

  echo $out;

  include("./foot.inc"); 
?>
