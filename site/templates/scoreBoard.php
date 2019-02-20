<?php namespace ProcessWire;

  if (!$config->ajax) { // Complete scoreboard
    include("./head.inc"); 
    $field = $input->get('field');
    if ($field == 'places' || $field == 'people') { $selector = '-'.$field.'.count'; } else { $selector = '-'.$field; }
    if (isset($player)) { // Get player's positions
      $playerId = $player->id;
    } else {
      $player = false;
      $playerId = false;
    }
    if ($user->isGuest() || $input->urlSegment1 == 'global') {
      $global = true;
    } else {
      $global = false;
    }
    if ($global) {
      $allPlayers = setGlobalScoreboard($field, 35);
      $input->whitelist("field", $field); // Get parameter for pagination
      $pagination = $allPlayers->renderPager();
      if ($player) {
        list($playerPos, $totalPlayersNb) = setScoreboardNew($player, $field, 'global', true);
        $posTitle = sprintf(__('You are %1$s/%2$s in this scoreboard.'), $playerPos, $totalPlayersNb);
      } else {
        $posTitle = '';
      }
    } else {
      $allPlayers = setTeamScoreboard($player->team->name, $field);
      $pagination = false;
      if ($player) {
        list($playerPos, $totalPlayersNb) = setScoreboardNew($player, $field, 'team', true);
        $posTitle = sprintf(__('You are %1$s/%2$s in this scoreboard.'), $playerPos, $totalPlayersNb);
      } else {
        $posTitle = '';
      }
    }
    switch ($field) {
      case 'yearlyKarma' :
        $title = __('Most active');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'reputation' :
        $title = __('Most influential');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'places' :
        $title = __('Greatest # of places');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'people' :
        $title = __('Greatest # of people');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'fighting_power' :
        $title = __('Best warriors');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'donation' :
        $title = __('Best donators');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'underground_training' :
        $title = __('Most trained');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'group' :
        $title = __('Most active groups');
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $playerPos = false;
        break;
      default : 
        $title = 'Error';
        $subTitle = '';
        $playerPos = false;
    }
    $subTitle = ' ['.sprintf(__('Out of %d players'), $allPlayers->getTotal()).']';
  ?>

  <div class="row">
    <?php 
      if (!$global) {
        echo '<a href="'.$page->url.'global?field='.$field.'" class="btn btn-primary pull-right">'.__("See global scoreboard").'</a>';
      } else {
        echo '<a href="'.$page->url.'?field='.$field.'" class="btn btn-primary pull-right">'.__("See team scoreboard").'</a>';
      }
      if ($user->isLoggedin()) {
        echo '<h3 class="text-center">';
        echo '<span class="label label-primary">'.$posTitle.'</span>';
        echo '</h3>';
      }
      if ($pagination) { echo $pagination; }
    ?>
    <div class="panel panel-success">
      <div class="panel-heading">
      <h4 class="panel-title"><?php echo $img .' '. $title.$subTitle; ?></h4>
      </div>
      <div class="panel-body">
        <ol class="col3">
          <?php
            echo displayCompleteScoreboard($allPlayers, $playerId, $field, $input->pageNum);
          ?>
        </ol>
      </div>
    </div>
    <?php if ($pagination) { echo $pagination; } ?>
  </div>

  <?php
    include("./foot.inc"); 

  } else { // Ajax loaded
    $out = '';
    $field = $input->get('id');
    switch($field) {
      case 'yearlyKarma' : $title = __("Most active players");
        break;
      case 'reputation' : $title = __("Most influential players");
        break;
      case 'underground_training' : $title = __("Most trained players");
        break;
      case 'places' : $title = __("Greatest # of places");
        break;
      case 'people' : $title = __("Greatest # of people");
        break;
      case 'fighting_power' : $title = __("Best warriors");
        break;
      case 'donation' : $title = __("Best donators");
        break;
      default : $title = 'todo';
    }
    if ($user->isGuest()) { // Global Scoreboards
      $playerPos = false;
      $topPlayers = setGlobalScoreboard($field, 10);;
      $prevPlayers = false;
      $nextPlayer = false;
      $playerId = false;
      $team = false;
    } else { // Team Scoreboards
      $player = $pages->get("login=$user->name");
      $playerId = $player->id;
      if ($input->get->type && $input->get->type == 'global') {
        list($playerPos, $totalPlayersNb, $prevPlayers, $topPlayers, $nextPlayer) = setScoreboardNew($player, $field, 'global');
        $team = false;
      } else {
        list($playerPos, $totalPlayersNb, $prevPlayers, $topPlayers, $nextPlayer) = setScoreboardNew($player, $field, 'team');
        $team = '['.$player->team->title.']';
      }
    }
    $out .= '<div class="board panel panel-success">';
    $out .= '  <div class="panel-heading">';
    $out .= '  <a class="pull-right" href="'.$pages->get('name=scoreboard')->url.'?field='.$field.'"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>';
    $out .= '  <h4 class="panel-title">';
    $out .= '<img src="'.$config->urls->templates.'img/star.png" alt="" /> ';
    $out .= '<span class="label label-primary" data-toggle="tooltip" title="'.__("Your position in this scoreboard").'">'.$playerPos.'</span> '.$team.' '.$title.'</h4>';
    $out .= '  </div>';
    $out .= '  <div class="panel-body">';
    $out .= displaySmallScoreboard($topPlayers, $prevPlayers, $playerPos, $playerId, $field);
    $out .= '  </div>';
    $out .= '</div>';
    echo $out;
  }
?>
