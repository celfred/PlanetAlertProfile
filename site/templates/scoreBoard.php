<?php

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
      $players = $pages->find("template=player, sort=-places.count, places.count>0");
      break;
    case 'equipment' :
      $title = 'Most equipped';
      $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
      $players = $pages->find('template=player, sort=-equipment.count, equipment.count>0');
      break;
    case 'donation' :
      $title = 'Best donators';
      $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
      $players = $pages->find('template=player, sort=-donation, donation>0');
      break;
    case 'underground_training' :
      $title = 'Most trained';
      $img = '<img src="'.$config->urls->templates .'img/star.png" alt="" />';
      $players = $pages->find('template=player, sort=-underground_training, underground_training>0');
      break;
    default : 
      $title = 'Error';
  }
?>

<div class="row">
  <div class="panel panel-success">
    <div class="panel-heading">
    <h4 class="panel-title"><?php echo $img .' '. $title; ?></h4>
    </div>
    <div class="panel-body">
      <ol>
        <?php
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
        ?>
      </ol>
      <div class="panel-footer">
        <p class="text-center"><span class="label label-success">Total # of players : <?php echo $pages->find("template=player, name!=test")->count; ?></span></p>
        <p class="text-center">If you have a 0 indicator in the selected scoreboard, then you are absent of the list :(</p>
      </div>
    </div>
  </div>
</div>

<?php
  include("./foot.inc"); 
?>
