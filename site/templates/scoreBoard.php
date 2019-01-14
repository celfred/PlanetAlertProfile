<?php namespace ProcessWire;

  if (!$config->ajax) {
    include("./head.inc"); 

    $field = $input->get('field');
    if (!isset($player)) { $player = ''; }
    list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($player, $field, -1);
    switch ($field) {
      case 'yearlyKarma' :
        $title = 'Most influential';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'reputation' :
        $title = 'Most influential';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'places' :
        $title = 'Greatest # of places';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'people' :
        $title = 'Greatest # of people';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'fighting_power' :
        $title = 'Best warriors';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'equipment' :
        $title = 'Most equipped';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'donation' :
        $title = 'Best donators';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'underground_training' :
        $title = 'Most trained';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      case 'group' :
        $title = 'Most active groups';
        $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
        break;
      default : 
        $title = 'Error';
    }

    if ($user->isLoggedin()) {
      // Get player's position
      if ($playerPos) {
        $pos = '<h3 class="text-center"><span class="label label-success">You are '.$playerPos.'/'.$totalPlayers.' in the \''.$title.'\' score board.</span></h3>';
      } else {
        $pos = '<h3 class="text-center"><span class="label label-success">You are not on this score board. Sorry :(</span></h3>';
      }
    }
  ?>

  <div class="row">
    <?php if (isset($pos)) { echo $pos; } ?>
    <div class="panel panel-success">
      <div class="panel-heading">
      <h4 class="panel-title"><?php echo $img .' '. $title; ?></h4>
      </div>
      <div class="panel-body">
        <ol>
          <?php
            foreach($topPlayers as $player) {
              switch ($field) {
                case 'yearlyKarma' :
                  $indicator = $player->yearlyKarma;
                  $tag = 'K.';
                  break;
                case 'reputation' :
                  $indicator = $player->reputation;
                  $tag = 'rep.';
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
                case 'group' :
                  $indicator = $player->reputation;
                  $tag = 'rep.';
                  break;
                default : 
                  $title = 'Error';
              }
              if ($player->avatar) {
                $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$player->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$player->avatar->getCrop('mini')->url."' alt='avatar' />";
              } else {
                $mini = '';
              }
              if ($player->login == $user->name || ($field == 'group' && $player->focus == 1)) {
                $focus = "class='focus'";
              } else {
                $focus = "";
              }
              if ($player->team->name == 'no-team') { $team = ''; } else {$team = ' ['.$player->team->title.']';}
              echo '<li><span '. $focus .'>'.$mini.' <a href="'.$player->url.'">'.$player->title.'</a>'.$team.'</span> <span class="badge">'.$indicator.' '.$tag.' </span></li>';
            }
          ?>
        </ol>
        <div class="panel-footer">
          <p class="text-center"><span class="label label-success">Total # of players : <?php echo $totalPlayers; ?></span> If you have a 0 indicator in the selected scoreboard, then you are absent of this list :(</p>
        </div>
      </div>
    </div>
  </div>

  <?php
    include("./foot.inc"); 

  } else { // Ajax loaded
    $out = '';
    $field = $input->get('id');
    $player = $pages->get("login=$user->name");
    $limit = 5;
    if ($user->hasRole('player')) {
      list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($player, $field, $limit, false);
    } else {
      $player = '';
      list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($player, $field, $limit, true);
    }
    if ($prevPlayers != false) { // Player is 'surrounded'
      $out .= '<ol>';
      if ($topPlayers->count() > 0) {
        foreach ($topPlayers as $p) {
          if ($p->avatar) {
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$p->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$p->avatar->getCrop('mini')->url."' alt='avatar' />";
          } else {
            $mini = '';
          }
          if ($p->login == $user->name) {
            $focus = "class='focus'";
          } else {
            $focus = "";
          }
          if ($p->team->name == 'no-team') { $team = ''; } else {$team = ' ['.$p->team->title.']';}
          switch($field) {
            case 'reputation':
              $out .= '<li><span '. $focus .'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->reputation.' '.__('rep.').'</span></li>';
              break;
            case 'places':
              if ($p->places->count > 1) {
                $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->places->count.' '.__('places').'</span></li>';
              } else {
                $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->places->count.' '.__('place').'</span></li>';
              }
              break;
            case 'people':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->people->count.' '.__('people').'</span></li>';
              break;
            case 'fighting_power':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->fighting_power.' '.__('FP').'</span></li>';
              break;
            case 'donation':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->donation.' '.__('GC').'</span></li>';
              break;
            case 'underground_training':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->underground_training.' '.__('UT').'</span></li>';
              break;
            case 'group':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->yearlyKarma.' K</span></li>';
              break;
            default: $out .= 'Error.';
          }
        }
      }
      $out .= '</ol>';
      $out .= '<hr />';
      $startIndex = (int) $playerPos-round($limit/2)+1; 
      $out .= '<ol start="'.$startIndex.'">';
        if ($prevPlayers->count() > 0) {
        foreach ($prevPlayers as $p) {
          if ($p->avatar) {
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$p->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$p->avatar->getCrop('mini')->url."' alt='avatar' />";
          } else {
            $mini = '';
          }
          if ($p->login == $user->name) {
            $focus = "class='focus'";
          } else {
            $focus = "";
          }
          if ($p->team->name == 'no-team') { $team = ''; } else {$team = ' ['.$p->team->title.']';}
          switch($field) {
            case 'reputation':
              $out .= '<li><span '. $focus .'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->reputation.' '.__('rep.').'</span></li>';
              break;
            case 'places':
              if ($p->places->count > 1) {
                $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->places->count.' '.__('places').'</span></li>';
              } else {
                $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->places->count.' '.__('place').'</span></li>';
              }
              break;
            case 'people':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->people->count.' '.__('people').'</span></li>';
              break;
            case 'fighting_power':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->fighting_power.' '.__('FP').'</span></li>';
              break;
            case 'donation':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->donation.' '.__('GC').'</span></li>';
              break;
            case 'underground_training':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->underground_training.' '.__('UT').'</span></li>';
              break;
            case 'group':
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->yearlyKarma.' K</span></li>';
              break;
            default: $out .= 'Error.';
          }
        }
        }
      $out .= '</ol>';
    } else { // No ranking or Top 10
      $out .= '<ol>';
      foreach ($topPlayers as $p) {
        if ($p->avatar) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$p->avatar->getCrop('thumbnail')->url."\" alt=\"avatar\" />' src='".$p->avatar->getCrop('mini')->url."' alt='avatar' />";
        } else {
          $mini = '';
        }
        if ($p->login == $user->name) {
          $focus = "class='focus'";
        } else {
          $focus = "";
        }
        if ($p->team->name == 'no-team') { $team = ''; } else {$team = ' ['.$p->team->title.']';}
        switch($field) {
          case 'reputation':
            $out .= '<li><span '. $focus .'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->reputation.' '.__('rep.').'</span></li>';
            break;
          case 'places':
            if ($p->places->count > 1) {
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->places->count.' '.__('places').'</span></li>';
            } else {
              $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->places->count.' '.__('place').'</span></li>';
            }
            break;
          case 'people':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->people->count.' '.__('people').'</span></li>';
            break;
          case 'fighting_power':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->fighting_power.' '.__('FP').'</span></li>';
            break;
          case 'donation':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->donation.' '.__('GC').'</span></li>';
            break;
          case 'underground_training':
            $out .= '<li><span '.$focus.'>'.$mini.' <a href="'.$p->url.'">'.$p->title.'</a>'.$team.'</span> <span class="badge">'.$p->underground_training.' '.__('UT').'</span></li>';
            break;
          case 'group':
            if (isset($player->group) && $p == $player->group) {
              $focus = "class='focus'";
            } else {
              $focus = "";
            }
            $out .= '<li>';
            $out .= '<span '.$focus.' data-toggle="tooltip" data-html="true" onmouseenter="$(this).tooltip(\'show\');" title="'.$p->members.'">';
            $out .= $p->title.' ['.$p->team->title.']</span> <span class="badge">'.$p->karma.' K</span>';
            // Display stars for bonus
            if ($p->nbBonus > 0) {
              $out .= '&nbsp;&nbsp;<span class="glyphicon glyphicon-star"></span>';
              $out .= '<span class="badge">'.$p->nbBonus.'</span>';
            }
            $out .= '</p>';
            $out .= '</li>';
            break;
          default: $out .= 'Error.';
        }
      }
      $out .= '</ol>';
    }

    echo $out;
  }
  ?>
