<?php
  $playerPage = $pages->get("template=player,name=".$input->urlSegment2);
  $playersTotalNb = $pages->count("template=player,team=$playerPage->team");
  if ($playerPage->places) {
    $playerPlacesNb = $playerPage->places->count();
  } else {
    $playerPlacesNb = 0;
  }
  if ($playerPage->people) {
    $playerPeopleNb = $playerPage->people->count();
  } else {
    $playerPeopleNb = 0;
  }
  $allEvents = $playerPage->child("name=history")->find("template=event,sort=-date");
  $rightInvasions = $allEvents->find("task.name=right-invasion")->count();
  $wrongInvasions = $allEvents->find("task.name=wrong-invasion")->count();
  $allCategories = new PageArray();
  foreach ($allEvents as $e) {
    if (isset($e->task->category)) {
      $allCategories->add($e->task->category);
      $allCategories->sort("title");
    }
  }

  $karma = $playerPage->yearlyKarma;
  if (!$karma) $karma = 0;
  if ($karma > 0 && $playerPage->team->name != 'no-team') { // Team Position
    // Number of players having a better karma than current player
    $playerPos = $pages->count("template=player,team=$playerPage->team,yearlyKarma>$karma") + 1;
  } else {
    $playerPos = $playersTotalNb;
  }
  // Set no hk counter
  if ($user->isSuperuser() || ($user->isLoggedin() && $user->name == $playerPage->login)) { // Admin is logged or user
    $hkCount = '<span class="label label-danger" data-toggle="tooltip" title="No hk counter">'.$playerPage->hkcount.'</span>';
  } else {
    $hkCount = '<span class="label label-danger">Private!</span>';
  }
  // Get last activity # of days
  $lastActivityCount = lastActivity($playerPage);
?>

<?php
  if ($user->isSuperuser()) {
    echo '<div class="row">';
    echo '<a class="pdfLink btn btn-info" href="'.$playerPage->url.'?pages2pdf=1">Get PDF</a>';
    echo '</div>';
  }
?>
<div>
  <div class="row">
    <div class="col-sm-12">
      <div id="" class="col-sm-6 panel panel-success panel-player">
          <div class="panel-heading">
            <h1 class="panel-title">
              <span class=""><?php echo $playerPage->title; ?></span>
              <?php 
                if ($playerPage->skills->has("name=captain")) {
                  $showSkills = '<span class="label label-primary">Captain</span>';
                } else {
                  $showSkills = '';
                }
                if ($playerPage->skills->has("name=ambassador")) {
                  $showSkills .= '<span class="label label-success">Ambassador</span>';
                }
                echo $showSkills;
              ?>
            </h1>
          </div>
          <div class="panel-body row">
          <div class="text-center col-sm-6">
            <img src="<?php if ($playerPage->avatar) echo $playerPage->avatar->url; ?>" alt="No avatar" />
          </div>
          <div class="col-sm-6">
            <ul class="player-details">
            <li>Karma : <span class="label label-default"><?php echo $karma; ?></span> <?php if ($playerPage->team->name != 'no-team') {?><span data-toggle="tooltip" title="Team position">(<?php echo $playerPos; ?>/<?php echo $playersTotalNb; ?>)</span><?php } ?></li>
            <li>Reputation : <span class="label label-default"><?php echo $playerPage->karma; ?></span></li>
            <li>Level : <?php echo $playerPage->level; ?></li>
            <li><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="GC" /> : <span class="label label-default" data-toggle="tooltip" data-html="true" title="Gold Coins"><?php echo $playerPage->GC; ?> GC</span></li>
            <li><span class="glyphicon glyphicon-exclamation-sign"></span> Hk count : <?php echo $hkCount; ?></li>
            <?php
              if ($lastActivityCount >= 0 && $lastActivityCount < 30) { // Active players
                echo '<li><span class="glyphicon glyphicon-thumbs-up"></span> Active player !</li>';
              } else { // 30 days of inactivity > lose all GC
                if ($lastActivityCount >= 20 && $lastActivityCount <= 30) { // Warning 10 days before losing GC
                  $delay = 31-$lastActivityCount;
                  echo '<li><span class="glyphicon glyphicon-exclamation-sign"></span> '.$lastActivityCount.' days of inactivity. ('.$delay.' day(s) before losing all GC !)</li>';
                } else {
                  echo '<li><span class="glyphicon glyphicon-exclamation-sign"></span> '.$lastActivityCount.' days of inactivity. (All GC have been lost !)</li>';
                }
              }
            ?>
            </ul>
          </div>
          </div>
          <?php if ($playerPage->coma == 0) { ?>
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
            <?php
            if ($playerPage->level <= 4) {
              $delta = 40+($playerPage->level*10);
            } else {
              $delta = 90;
            }
            $threshold = ($playerPage->level*10)+$delta;
            ?>
            <span class="badge" data-toggle="tooltip" title="Experience (Level <?php echo $playerPage->level; ?>)"><img src="<?php  echo $config->urls->templates?>img/star.png" alt="Experience" /> <?php echo $playerPage->XP; ?>/<?php echo $threshold; ?></span>
          </div>
          <div class="col-sm-10">
            <div class="progress progress-striped progress-lg" data-toggle="tooltip" title="Experience (Level <?php echo $playerPage->level; ?>)">
              <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo (100*$playerPage->XP)/$threshold; ?>%">
              </div>
            </div>
          </div>
          <?php } else { ?>
            <h4 class="text-center"><span class="label label-danger">You're in a COMA !</span></h4><h4 class="text-center"><span>Buy a healing potion to go back to normal state !</span></h4>
          <?php } ?>
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
                    echo "<li data-toggle='tooltip' data-html='true' title='{$equipment->title}<br />{$equipment->summary}'>";
                    if ($equipment->name == "memory-helmet") { // Direct link to training zone
                      echo '<a href="'.$pages->get('name=underground-training')->url.'" title="Go to the Training Zone"><img class="img-thumbnail" src="'.$thumb.'" /></a>';
                    } else {
                      echo "<img class='img-thumbnail' src='{$thumb}' />";
                    }
                    echo "</li>";
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
        <div class="panel-footer text-center">
          <?php 
            echo '<p><span class="glyphicon glyphicon-flash"></span> Fighting power : '.$playerPage->fighting_power.'</p>';
          ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
        <h4 class="panel-title"><span class="glyphicon glyphicon-headphones"></span> <span class="">Underground Training (U.T.) : <?php echo $playerPage->underground_training; ?> / <span class="glyphicon glyphicon-flash"></span> Monster fights</span></h4>
        </div>
        <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="helmetreport&playerId=<?php echo $playerPage->id; ?>">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
        <div class="panel-footer">
        <?php
          if ($user->isSuperuser() || ($user->isLoggedin() && $user->name === $playerPage->login)) { // Admin is logged or user
            if ($playerPage->equipment->get("name=memory-helmet")) {
              echo '<p><a href="'.$pages->get('name=underground-training')->url.'">→ Use the Memory Helmet (Training Zone)</a>.</p>';
              echo '<p><a href="'.$pages->get('name=fighting-zone')->url.'">→ Go to the Fighting Zone</a>.</p>';
            } else {
              echo 'Sorry, but at least one member in your group needs to buy the <a href="'.$pages->get('name=shop')->url.'details/memory-helmet">Memory Helmet</a> to be able to access the Underground Training zone.';
            }
          }
        ?>
        </div>
      </div>
    </div>

    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title"><span class=""><span class="glyphicon glyphicon-list"></span> Global Hall of Fames (Your positions)</span></h4>
        </div>
        <div class="panel-body">
          <ul>
          <?php 
            // Most influential (karma)
            list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'karma');
            if ($playerPos) {
              if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Most influential : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
            } else {
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Most influential : No ranking.</p></li>';
            }
            // Greatest # of places (places)
            list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'places');
            if ($playerPos) {
              if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Greatest # of places : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
            } else {
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Greatest # of places : No ranking.</p></li>';
            }
            // Greatest # of people (people)
            list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'people');
            if ($playerPos) {
              if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Greatest # of people : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
            } else {
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Greatest # of people : No ranking.</p></li>';
            }
            // Best warrior (fighting_power)
            list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'fighting_power');
            if ($playerPos) {
              if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Best warriors : '.$playerPos.'/'.$totalPlayers.' '. $star.'</p></li>';
            } else {
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Best warriors : No ranking.</p></li>';
            }
            // Best donators (donation)
            list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'donation');
            if ($playerPos) {
              if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Best donators : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
            } else {
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Best donators : No ranking.</p></li>';
            }
            // Most trained (underground_training)
            if ($playerPage->underground_training) {
              list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'underground_training');
              if ($playerPos) {
                if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
                echo '<li>';
                echo '<p>';
                echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
                echo 'Most trained : '.$playerPos.'/'.$totalPlayers.' '.$star;
                echo '</p></li>';
              } else {
                echo '<li>';
                echo '<p>';
                echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
                echo 'Most trained : No ranking.';
                echo '</p></li>';
              }
            }

            // Most active groups
            list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'group');
            if ($playerPos) {
              if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=group"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Most active groups : '.$playerPos.'/'.$totalPlayers.' '.$star;
              echo '</p></li>';
            } else {
              echo '<li>';
              echo '<p>';
              echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a> ';
              echo 'Most active groups : No ranking.';
              echo '</p></li>';
            }
          ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> Free elements : <?php echo $playerPlacesNb+$playerPeopleNb; ?></span></h4>
        </div>
          <h4 class="badge badge-info"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> Free places </span></h4>
        <div class="panel-body">
            <ul class="playerPlaces list-inline">
            <?php
              foreach($playerPage->places as $place) {
                $thumbImage = $place->photo->eq(0)->getCrop('thumbnail')->url;
                echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$place->title<br />$place->summary<br />[{$place->parent->title},{$place->parent->parent->title}]' /></a></li>";
              }
            ?>
            </ul>

            <?php
              if ($playerPage->rank && $playerPage->rank->is("name=4emes|3emes")) {
            ?>
            <h4 class="badge badge-info"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> Free people </span></h4>
            <ul class="playerPlaces list-inline">
            <?php
              foreach($playerPage->people as $p) {
                $thumbImage = $p->photo->eq(0)->getCrop('thumbnail')->url;
                echo "<li><a href='{$p->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$p->title<br />$p->summary' /></a></li>";
              }
            ?>
            </ul>
        </div>
        <?php } else { ?>
          <p class="badge badge-danger">People are available for 4emes and 3emes only.</p>
        <?php } ?>

        <div class="panel-footer">
            <span class="glyphicon glyphicon-star"></span>
            <?php
            if ($rightInvasions > 0 || $wrongInvasions > 0) {
              echo 'Defensive power : <span>'.round(($rightInvasions*100)/($wrongInvasions+$rightInvasions)).'%</span> (You have repelled '.$rightInvasions.' out of '.($rightInvasions+$wrongInvasions).' monster invasions)';
            } else {
              echo 'You have not faced any monster invasion yet.';
            }
            ?>
        </div>
      </div>
    </div>

  <?php
    if ($user->name === $playerPage->login || ($user->isSuperuser())) { // Logged-in user or Admin front-end
  ?>
      <div class="col-sm-12">
        <div class="panel panel-success">
          <div class="panel-heading">
            <h4>History</h4>
          </div>
          <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="history&playerId=<?php echo $playerPage->id; ?>>
            <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
          </div>
        </div>
      </div>
  <?php } ?>
</div>
