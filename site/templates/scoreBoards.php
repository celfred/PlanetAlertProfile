<?php namespace ProcessWire;

  include("./head.inc"); 

  echo '<div class="row">';
    if ($user->isGuest() || $input->urlSegment1 == 'global') {
      $scoreBoardLink = $pages->get("name=scoreboard")->url.'?type=global';
      $switchTitle = __('View team scoreboards');
      $switchBoardLink = $pages->get("name=the-scoreboards")->url;
    } else {
      $scoreBoardLink = $pages->get("name=scoreboard")->url;
      $switchTitle = __('View global scoreboards');
      $switchBoardLink = $pages->get("name=the-scoreboards")->url.'global';
    }
    if (!$user->isGuest()) {
      echo '<a class="pull-right btn btn-small btn-primary" href="'.$switchBoardLink.'">'.$switchTitle.'</a>';
    }
  echo '</div>';

  echo '<div class="row">';
    echo '<div class="col-sm-4">';
      $boardsOnCol = ['yearlyKarma', 'reputation'];
      foreach($boardsOnCol as $field) {
        echo '<div class="ajaxContent" data-href="'.$scoreBoardLink.'" data-id="'.$field.'">';
          echo '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
        echo '</div>';
      }
    echo '</div>';
    echo '<div class="col-sm-4">';
      $boardsOnCol = ['places', 'people', 'donation'];
      foreach($boardsOnCol as $field) {
        echo '<div class="ajaxContent" data-href="'.$scoreBoardLink.'" data-id="'.$field.'">';
          echo '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
        echo '</div>';
      }
    echo '</div>';
    echo '<div class="col-sm-4">';
      $boardsOnCol = ['underground_training', 'fighting_power'];
      foreach($boardsOnCol as $field) {
        echo '<div class="ajaxContent" data-href="'.$scoreBoardLink.'" data-id="'.$field.'">';
          echo '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
        echo '</div>';
      }
    echo '</div>';
  echo '</div>';

  include("./foot.inc"); 
?>
