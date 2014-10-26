<?php
  $playerPage = $pages->get("template=player,name=".$input->urlSegment2);
  //$categories = $pages->find("parent=/categories");
  $playersTotalNb = $pages->count("template=player,team=$playerPage->team");
  $playerPlacesNb = $playerPage->places->count();
  $recentEvents = $playerPage->child("name=history")->find("template=event,sort=-created,limit=10");
  $history = $playerPage->find("parent.name=history");
  $categories = array();
  foreach($history as $p) {
    if (!in_array($p->category, $categories)) {
      $categories[$p->category->name] = $p->category->title;
    }
  }
  /*
  if ($playerPlacesNb != 0) { // TODO : Position works???
    // Number of players having more freed places than current player
    $playerPos = $pages->count("template=player,team=$playerPage->team,places.count>$playerPlacesNb") + 1;
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
   */

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
            <!-- <progress class="progress-striped progress-lg progress-hp" percent="<?php echo 2*$playerPage->HP; ?>" role="progressbar"></progress> -->
            <div class="progress progress-striped progress-lg" tooltip="Points de santé">
              <div class="progress-bar progress-bar-danger" role="progressbar" style="width:<?php echo 2*$playerPage->HP; ?>%">
              </div>
            </div>
          </div>
          <div class="col-sm-2 text-right">
            <span class="badge" tooltip="Expérience (Niveau <?php echo $playerPage->level; ?>)"><img src="<?php  echo $config->urls->templates?>img/star.png" alt="Expérience" /> <?php echo $playerPage->XP; ?>/<?php echo $playerPage->level*10+90; ?></span>
          </div>
          <div class="col-sm-10">
            <!-- <progress class="progress-striped progress-lg progress-xp" percent="<?php echo (100*$playerPage->XP)/($playerPage->level*10+90); ?>" role="progressbar"></progress> -->
            <div class="progress progress-striped progress-lg" tooltip="Expérience (Niveau <?php echo $playerPage->level; ?>)">
              <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo (100*$playerPage->XP)/($playerPage->level*10+90); ?>%">
              </div>
            </div>
          </div>
      </div>

      <div id="" class="col-sm-5 panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title">Équipement</h4>
        </div>
        <div class="panel-body text-center">
          <ul class="list-inline">
            <?php
              if ($playerPage->equipment->count > 0) {
                foreach ($playerPage->equipment as $equipment) {
                  if ($equipment->image) {
                    $thumb = $equipment->image->url;
                    echo "<li tooltip-html-unsafe='{$equipment->title}<br />{$equipment->summary}'><img class='img-thumbnail' src='{$thumb}' /></li>";
                  } else {
                    echo "<li tooltip-html-unsafe='{$equipment->title}<br />{$equipment->summary}'>{$equipment->title}</li>";
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
          <h4 class="panel-title">Pièces d'or</h4>
        </div>
        <div class="panel-body text-center">
          <h4><img src="<?php  echo $config->urls->templates?>img/gold.png" alt="Or" width="100" /><span class="gc label label-default" tooltip="Gold Coins"><?php echo $playerPage->GC; ?></span></h4>
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
          <h4><rating ng-model="rate" value="<?php echo $rate; ?>" readonly="true"></rating></span></h4>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> Lieux libérés: <?php echo $playerPlacesNb; ?></span></h4>
        </div>
        <div class="panel-body">
            <ul class="playerPlaces list-inline">
            <?php
              foreach($playerPage->places as $place) {
                $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
                echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' tooltip-html-unsafe='$place->title<br />$place->summary<br />[{$place->parent->title},{$place->parent->parent->title}]' /></a></li>";
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
          <h4>Historique <span><a href="" ng-click="loadedHistory = !loadedHistory" ng-class="{selected : loadedHistory == false}">[Les 10 dernières actions]</a> <a href="" ng-click="loadHistory(<?php echo $playerPage->child("name=history")->id; ?>)" ng-class="{selected : loadedHistory == true}">[Toutes les actions]</a></span>
          </h4>
        </div>
        <div class="panel-body">
          <ul ng-hide="loadedHistory">
            <table class="table table-condensed table-hover">
              <tr>
                <th>Date</th>
                <th>Catégorie</th>
                <th>Intitulé</th>
                <th>Commentaire</th>
              </tr>
              <?php
                foreach($recentEvents as $event) {
                  echo "<tr><td>";
                    echo date("d/m/Y D", $event->created);
                  echo "</td>";
                  echo "<td>";
                  echo "{$event->category->title}";
                  echo "</td>";
                  echo "<td>".$event->title."</td>";
                  echo "<td>".$event->summary."</td>";
                  echo"</tr>";
                }
              ?>
            </table>
          </ul>

          <div id="playerHistory" ng-init="search.category = ''" ng-show="loadedHistory">
            <ul class="list-inline">
              <?php
                echo "<li><span ng-class='{\"btn btn-success\": search.category == \"\", \"btn btn-info\": search.category != \"\"}' ng-click='search.category = \"\"'>All</span></li>";
                foreach ($categories as $catName=>$catTitle) {
                  echo "<li><span ng-class='{\"btn btn-success\": search.category == \"{$catName}\", \"btn btn-info\": search.category != \"{$catName}\"}' ng-click='search.category = \"{$catName}\"'>{$catTitle}</span></li>";
                }
              ?>
            </ul>
            <table class="table table-condensed table-hover">
              <tr>
                <th ng-click="predicate = 'created'; reverse=!reverse">Date</th>
                <th ng-click="predicate = 'category'; reverse=!reverse">Catégorie</th>
                <th ng-click="predicate = 'title'; reverse=!reverse">Intitulé</th>
                <th>Commentaire</th>
              </tr>
              <tr ng-repeat="event in history | orderBy:predicate:reverse | filter: search">
                <td>{{event.created*1000 | date:'dd/MM/yyyy EEE'}}</td>
                <td>{{event.category.title | filterHtmlChars}}</td>
                <td>{{event.title | filterHtmlChars}}</td>
                <td>{{event.summary | filterHtmlChars}}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
</div>
