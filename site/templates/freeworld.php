<?php Namespace ProcessWire;
  include("./head.inc"); 

  $team = $pages->get("template=team, name=$input->urlSegment1");
  $allPlayers = $allPlayers->find("team=$team, sort=group"); // Limit to team players
  $rank = $allPlayers->first()->rank->name;
  if ($rank == '4emes' || $rank == '3emes') {
    $totalElements = $pages->find("template=place|people, name!=places|people, sort=level");
  } else {
    $totalElements = $pages->find("template=place, name!=places, sort=level");
  }
  
  // Set team stats
  $teamRate = round(($allPlayers->count()*20)/100);

  // Nav tabs
  if ($user->isSuperuser()) {
    include("./tabList.inc"); 
  }
  
  showScores($team);
  $allElements = teamFreeworld($team);
  $allCompleted = $allElements->find("completed=1");
  $notCompleted = $allElements->find("completed!=1");
?>
  <!-- <a class="pdfLink btn btn-info" href="<?php echo $page->url.$input->urlSegment1; ?>/places?pages2pdf=1">Get PDF</a> -->
  <h4 class="text-center">[Team rate : <?php echo $teamRate; ?>] <span data-toggle="tooltip" data-html="true" title="# of players required to complete a place and increase %." class="glyphicon glyphicon-question-sign"></span></h4>

  <?php
    $out = '';
    $allElements->sort("level, title");
    $out .= '<section class="freeWorld col-sm-6">';
    $out .= '<h2><span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i> '.$notCompleted->count().' incomplete elements</span></h2>';
    foreach($notCompleted as $el) {
      if ($el->photo) {
        $thumbImage = $el->photo->eq(0)->getCrop('thumbnail')->url;
      }
      $out .= '<div>';
      $title = '<h3>'.$el->title.'</h3>';
      $title .= '<h4>Level '.$el->level.', '.$el->GC.'GC</h4>';
      if ($el->teamOwners->count() > 0 && $el->teamOwners->count() < 10) {
        $ownerList = '['.$el->teamOwners->implode(', ', '{title}').']';
      } else {
        $ownerList = '';
      }
      $title .= '<h4>Freed by <span class=\'label label-success\'>'.$el->teamOwners->count().'</span> players '.$ownerList.'</h4>';
      if ($el->completed != 1) {
        $left = $teamRate - $el->teamOwners->count();
        $title .= '<br /><h4><span class=\'label label-primary\'>'.$left.' more needed !</span></h4>';
      }
      $out .= '<a href="'.$el->url.'" class=""><img class="'.$el->cssClass.'" src="'.$thumbImage.'" data-toggle="tooltip" data-html="true" title="'.$title.'" /></a>';
      $out .= "</div>";
    }
    $out .= '</section>';
    $out .= '<section class="freeWorld">';
    $out .= '<h2><span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i> '.$allCompleted->count().' Completed elements</span></h2>';
    foreach($allCompleted as $el) {
      if ($el->photo) {
        $thumbImage = $el->photo->eq(0)->getCrop('thumbnail')->url;
      }
      $out .= '<div>';
      $title = '<h3>'.$el->title.'</h3>';
      $title .= '<h4>Level '.$el->level.', '.$el->GC.'GC</h4>';
      if ($el->teamOwners->count() > 0 && $el->teamOwners->count() < 10) {
        $ownerList = '['.$el->teamOwners->implode(', ', '{title}').']';
      } else {
        $ownerList = '';
      }
      $title .= '<h4>Freed by <span class=\'label label-success\'>'.$el->teamOwners->count().'</span> players '.$ownerList.'</h4>';
      $out .= '<a href="'.$el->url.'" class=""><img class="'.$el->cssClass.'" src="'.$thumbImage.'" data-toggle="tooltip" data-html="true" title="'.$title.'" /></a>';
      $out .= "</div>";
    }
    $out .= '</section>';
    echo $out;

  include("./foot.inc"); 
?>
