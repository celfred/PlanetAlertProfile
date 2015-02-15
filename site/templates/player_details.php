<?php
  $playerPage = $pages->get("template=player,name=".$input->urlSegment2);
  //$categories = $pages->find("parent=/categories");
  $playersTotalNb = $pages->count("template=player,team=$playerPage->team");
  $playerPlacesNb = $playerPage->places->count();
  $allEvents = $playerPage->child("name=history")->find("template=event,sort=-created");

  $karma = $playerPage->karma;
  if (!$karma) $karma = 0;
  if ($karma > 0) { // Team Position
    // Number of players having a better karma than current player
    $playerPos = $pages->count("template=player,team=$playerPage->team,karma>$karma") + 1;
    if ($playerPos > 0 && $playerPos <= 3) {
      $rate = 5;
    } else if ($playerPos > 3 && $playerPos <= 7) {
      $rate = 4;
    } else if ($playerPos > 7 && $playerPos <= 12) {
      $rate = 3;
    } else if ($playerPos > 12 && $playerPos <= 17) {
      $rate = 2;
    } else if ($playerPos > 17 && $playerPos <= 21) {
      $rate = 1;
    } else {
      $rate = 0;
    }
  } else {
    $playerPos = 0;
    $rate = 0;
  }
?>

        <!-- <a href="players/<?php echo $input->urlSegment1; ?>">Back to team view</a> -->
<div ng-controller="playerCtrl" ng-init="init(<?php echo $rate; ?>)">
  <div class="row">
    <div class="col-sm-12">
      <div id="" class="col-sm-6 panel panel-success panel-player">
          <div class="panel-heading">
            <h1 class="panel-title">
              <span class=""><?php echo $playerPage->title; ?></span>
            </h1>
          </div>
          <div class="panel-body text-center">
            <img src="<?php if ($playerPage->avatar) echo $playerPage->avatar->url; ?>" alt="No avatar" />
          </div>
          <div class="col-sm-2 text-right">
            <span class="badge" tooltip="Points de santé"><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="Santé" /> <?php echo $playerPage->HP; ?>/50</span>
          </div>
          <div class="col-sm-10">
            <div class="progress progress-striped progress-lg" data-toggle="tooltip" title="Health points">
              <div class="progress-bar progress-bar-danger" role="progressbar" style="width:<?php echo 2*$playerPage->HP; ?>%">
              </div>
            </div>
          </div>
          <div class="col-sm-2 text-right">
            <span class="badge" data-toggle="tooltip" title="Experience (Level <?php echo $playerPage->level; ?>)"><img src="<?php  echo $config->urls->templates?>img/star.png" alt="Experience" /> <?php echo $playerPage->XP; ?>/<?php echo $playerPage->level*10+90; ?></span>
          </div>
          <div class="col-sm-10">
            <div class="progress progress-striped progress-lg" data-toggle="tooltip" title="Experience (Level <?php echo $playerPage->level; ?>)">
              <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo (100*$playerPage->XP)/($playerPage->level*10+90); ?>%">
              </div>
            </div>
          </div>
      </div>

      <div id="" class="col-sm-5 panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title">Equipment</h4>
        </div>
        <div class="panel-body text-center">
          <ul class="list-inline">
            <?php
              if ($playerPage->equipment->count > 0) {
                foreach ($playerPage->equipment as $equipment) {
                  if ($equipment->image) {
                    $thumb = $equipment->image->url;
                    echo "<li data-toggle='tooltip' data-html='true' title='{$equipment->title}<br />{$equipment->summary}'><img class='img-thumbnail' src='{$thumb}' /></li>";
                  } else {
                    echo "<li data-toggle='tooltip' data-html='true' title='{$equipment->title}<br />{$equipment->summary}'>{$equipment->title}</li>";
                  }
                }
              } else {
                echo "<p>Aucun équipement.</p>";
              }
            ?>
          </ul>
        </div>
      </div>

      <div id="" class="col-sm-2 panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title">Gold coins</h4>
        </div>
        <div class="panel-body text-center">
          <h4><img src="<?php  echo $config->urls->templates?>img/gold.png" alt="Or" width="100" /><span class="gc label label-default" data-toggle="tooltip" data-html="true" title="Gold Coins"><?php echo $playerPage->GC; ?></span></h4>
        </div>
      </div>

      <div id="" class="col-sm-3 panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title">Karma</h4>
        </div>
        <div class="panel-body text-center">
          <h4><span class="label label-default"><?php echo $karma; ?></span></h4>
        </div>
        <div class="panel-body text-center">
          <h4><span class="position">Team position : <?php echo $playerPos; ?>/<?php echo $playersTotalNb; ?></h4>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> Free places: <?php echo $playerPlacesNb; ?></span></h4>
        </div>
        <div class="panel-body">
            <ul class="playerPlaces list-inline">
            <?php
              foreach($playerPage->places as $place) {
                $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
                echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$place->title<br />$place->summary<br />[{$place->parent->title},{$place->parent->parent->title}]' /></a></li>";
              }
            ?>
            </ul>
        </div>
      </div>
    </div>
  </div>

  <?php
    if ($user->isSuperuser()) { // Admin front-end
  ?>
  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4>Historique</h4>
        </div>
        <div class="panel-body">
            <table id="historyTable" class="table table-condensed table-hover">
              <thead>
              <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Title</th>
                <th>Comment</th>
              </tr>
              </thead>
              <tbody>
              <?php
                foreach($allEvents as $event) {
                  echo "<tr><td data-order='{$event->created}'>";
                    echo date("d/m/Y D", $event->created);
                  echo "</td>";
                  echo "<td>";
                  echo "{$event->task->category->title}";
                  echo "</td>";
                  echo "<td>".$event->title."</td>";
                  echo "<td>".$event->summary."</td>";
                  echo"</tr>";
                }
              ?>
              </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>

  <?php } ?>
</div>
