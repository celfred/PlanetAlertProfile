<?php

  if (!$config->ajax) {
    include("./head.inc"); 

    $field = $input->get('field');
    switch ($field) {
      case 'karma' :
        $title = 'Most influential';
        $players = $pages->find("template=player, sort=-karma, karma>0");
        break;
      case 'places' :
        $title = 'Greatest # of places';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $players = $pages->find("template=player, sort=-places.count, sort=-karma, places.count>0");
        break;
      case 'people' :
        $title = 'Greatest # of people';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $players = $pages->find("template=player, sort=-people.count, sort=-karma, people.count>0");
        break;
      case 'fighting_power' :
        $title = 'Best warriors';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $players = $pages->find('template=player, sort=-fighting_power, sort=-karma, fighting_power>0');
        break;
      case 'equipment' :
        $title = 'Most equipped';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $players = $pages->find('template=player, sort=-equipment.count, sort=-karma, equipment.count>0');
        break;
      case 'donation' :
        $title = 'Best donators';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $players = $pages->find('template=player, sort=-donation, sort=-karma, donation>0');
        break;
      case 'underground_training' :
        $title = 'Most trained';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        $players = $pages->find('template=player, sort=-underground_training, sort=-karma, underground_training>0');
        break;
      case 'group' :
        $title = 'Most active groups';
        $players = $pages->get("/groups")->children;
        break;
      default : 
        $title = 'Error';
    }

    if ($user->isLoggedin()) {
      // Get player's position
      list($playerPos, $totalPlayers) = getPosition($player, $field);
      if ($playerPos) {
        $pos = '<h3 class="text-center"><span class="label label-success">You are '.$playerPos.'/'.$totalPlayers.' in the \''.$title.'\' score board.</span></h3>';
      } else {
        $pos = '<h3 class="text-center"><span class="label label-success">You are not on this score board. Sorry :(</span></h3>';
      }
    }
  ?>

  <div class="row">
    <?php echo $pos; ?>
    <div class="panel panel-success">
      <div class="panel-heading">
      <h4 class="panel-title"><?php echo $img .' '. $title; ?></h4>
      </div>
      <div class="panel-body">
        <ol>
          <?php
            if ($field != 'group') {
              foreach($players as $player) {
                switch ($field) {
                  case 'karma' :
                    $indicator = $player->karma;
                    $tag = 'karma';
                    break;
                  case 'places' :
                    $indicator = $player->places->count;
                    $tag = 'places';
                    break;
                  case 'people' :
                    $indicator = $player->people->count;
                    $tag = 'people';
                    break;
                  case 'fighting_power' :
                    $indicator = $player->fighting_power;
                    $tag = 'FP';
                    break;
                  case 'equipment' :
                    $indicator = $player->equipment->count;
                    $tag = 'equipment';
                    break;
                  case 'donation' :
                    $indicator = $player->donation;
                    $tag = 'GC';
                    break;
                  case 'underground_training' :
                    $indicator = $player->underground_training;
                    $tag = 'U.T.';
                    break;
                  default : 
                    $title = 'Error';
                }
                if ($player->avatar) {
                  $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
                } else {
                  $mini = '';
                }
                if ($player->login == $user->name) {
                  $focus = "class='focus'";
                } else {
                  $focus = "";
                }
                if ($player->playerTeam == '') {$team = '';} else {$team = ' ['.$player->playerTeam.']';}
                echo '<li><span '. $focus .'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$indicator.' '.$tag.' </span></li>';
              }
            } else {
            echo groupScoreBoard();
            }
          ?>
        </ol>
        <div class="panel-footer">
          <?php if ($field != 'group') { ?>
            <p class="text-center"><span class="label label-success">Total # of players : <?php echo $pages->find("template=player, name!=test")->count; ?></span> If you have a 0 indicator in the selected scoreboard, then you are absent of this list :(</p>
          <?php } else { ?>
            <!-- TODO Get active groups only (ie having members) -->
            <p class="text-center"><span class="label label-success">Total # of groups : <?php echo $totalPlayers; ?></span> If you have a 0 indicator in the selected scoreboard, then you are absent of this list :(</p>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <?php
    include("./foot.inc"); 

  } else {
    $type = $input->get('type');

    switch($type) {
      case 'karma':
        $players = $pages->find("template=player, sort=-karma, karma>0, limit=10");
        break;
      case 'places':
        $players = $pages->find("template=player, sort=-places.count, sort=-karma, places.count>0, limit=10");
        break;
      case 'people':
        $players = $pages->find("template=player, sort=-people.count, sort=-karma, people.count>0, limit=10");
        break;
      case 'fighting_power':
        $players = $pages->find("template=player, sort=-fighting_power, sort=-karma, fighting_power>0, limit=10");
        break;
      case 'donation' :
        $players = $pages->find("template=player, sort=-donation, sort=-karma, donation>0, limit=10");
        break;
      case 'underground_training' :
        $players = $pages->find("template=player, sort=-underground_training, sort=-karma, underground_training>0, limit=10");
        break;
      default:
        echo 'TODO';
    }

    $out = '<ol>';

    if ($players->count() > 0) {
      foreach ($players as $player) {
        if ($player->avatar) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getThumb('thumbnail')."\" alt=\"avatar\" />' src='".$player->avatar->getThumb('mini')."' alt='avatar' />";
        } else {
          $mini = '';
        }
        if ($player->login == $user->name) {
          $focus = "class='focus'";
        } else {
          $focus = "";
        }
        if ($player->playerTeam == '') {$team = '';} else {$team = ' ['.$player->playerTeam.']';}
        switch($type) {
          case 'karma':
            $out .= '<li><span '. $focus .'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->karma.' karma</span></li>';
            break;
          case 'places':
            if ($player->places->count > 1) {
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->places->count.' places</span></li>';
            } else {
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->places->count.' place</span></li>';
            }
            break;
          case 'people':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->people->count.' people</span></li>';
            break;
          case 'fighting_power':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->fighting_power.' F.P.</span></li>';
            break;
          case 'donation':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->donation.' GC</span></li>';
            break;
          case 'underground_training':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$player->underground_training.' UT</span></li>';
            break;
          default: $out .= 'Error.';
        }
      }
    } else {
      $out .= '<li>No player in this score board yet.</li>';
    }
    $out .= '</ol>';

    echo $out;
  }
  ?>
