<?php
  $playerPage = $pages->get("template=player,name=".$input->urlSegment2);
  $playersTotalNb = $pages->count("template=player,team=$playerPage->team");
  $playerPlacesNb = $playerPage->places->count();
  $playerPeopleNb = $playerPage->people->count();
  $allEvents = $playerPage->child("name=history")->find("template=event,sort=-date");
  $rightInvasions = $allEvents->find("task.name=right-invasion")->count();
  $wrongInvasions = $allEvents->find("task.name=wrong-invasion")->count();
  $allCategories = new PageArray();
  foreach ($allEvents as $task) {
    if ($task->category != '') {
      $allCategories->add($task->category);
      $allCategories->sort("title");
    }
  }

  $karma = $playerPage->karma;
  if (!$karma) $karma = 0;
  if ($karma > 0) { // Team Position
    // Number of players having a better karma than current player
    $playerPos = $pages->count("template=player,team=$playerPage->team,karma>$karma") + 1;
  } else {
    $playerPos = $playersTotalNb;
  }
  // Set no hk counter
  if ($user->isSuperuser() || ($user->isLoggedin() && $user->name == $playerPage->login)) { // Admin is logged or user
    $hkCount = '<span class="label label-danger" data-toggle="tooltip" title="No hk counter">'.checkHk($playerPage).'</span>';
  } else {
    $hkCount = '<span class="label label-danger">Private!</span>';
  }
?>

<?php
  if ($user->isSuperuser()) {
    echo '<div class="row">';
    echo '<a class="pdfLink btn btn-info" href="' . $page->url.$input->urlSegment1.'/'.$input->urlSegment2.'/'.$input->urlSegment3. '?pages2pdf=1">Get PDF</a>';
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
            </h1>
          </div>
          <div class="panel-body row">
          <div class="text-center col-sm-6">
            <img src="<?php if ($playerPage->avatar) echo $playerPage->avatar->url; ?>" alt="No avatar" />
          </div>
          <div class="col-sm-6">
            <ul class="player-details">
            <li>Karma : <span class="label label-default"><?php echo $karma; ?></span> <?php if ($playerPage->team->name != 'no-team') {?><span data-toggle="tooltip" title="Team position">(<?php echo $playerPos; ?>/<?php echo $playersTotalNb; ?>)</span><?php } ?></li>
            <li>Level : <?php echo $player->level; ?></li>
            <li><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="GC" /> : <span class="label label-default" data-toggle="tooltip" data-html="true" title="Gold Coins"><?php echo $playerPage->GC; ?> GC</span></li>
            <li><span class="glyphicon glyphicon-exclamation-sign"></span> Hk count : <?php echo $hkCount; ?></li>
            </ul>
          </div>
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
        <div class="panel-footer text-center">
          <?php 
            echo '<p>Fighting power : '.$playerPage->fighting_power.'</p>';
          ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4 class="panel-title"><span class="">Underground Training (U.T.) : <?php echo $playerPage->underground_training; ?></span></h4>
        </div>
        <div class="panel-body">
          <ul>
          <?php 
          // Find # of untrained pages
          $allPossible = $pages->find("template=exercise, type.name=translate");
          $allUt = $playerPage->find("template=event, task=ut-action-v|ut-action-vv");
          $refPages = [];
          $untrained = [];
          foreach ($allUt as $p) { // Build array of trained ids
            array_push($refPages, $p->refPage);
          }
          foreach ($allPossible as $p) { // Compare to all possible pages
            if (!in_array($p, $refPages)) {
              array_push($untrained, $p->id);
            }
          }
          if (count($allUt) > 0) {
            echo 'You have revised '.count($allUt).' times.';
            /* foreach ($allUt as $p) { */
            /*   echo '<li>'.$p->summary.'</li>'; */
            /* } */
          } else {
            echo 'You have NEVER used the Memory Helmet.';
          }
          ?>
          </ul>
        </div>
        <div class="panel-footer">
        <?php
          if ($playerPage->equipment->get("name=memory-helmet")) {
            echo 'You can use your <a href="'.$pages->get('name=underground-training')->url.'">Memory Helmet</a> to practise and improve your Underground Training rate :)';
          } else {
            echo 'Sorry, but at least one member in your group needs to buy the <a href="'.$pages->get('name=shop')->url.'details/memory-helmet">Memory Helmet</a> to be able to access the Underground Training zone.';
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
            list($playerPos, $totalPlayers) = getPosition($playerPage, 'karma');
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
            list($playerPos, $totalPlayers) = getPosition($playerPage, 'places');
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
            list($playerPos, $totalPlayers) = getPosition($playerPage, 'people');
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
            list($playerPos, $totalPlayers) = getPosition($playerPage, 'fighting_power');
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
            list($playerPos, $totalPlayers) = getPosition($playerPage, 'donation');
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
              list($playerPos, $totalPlayers) = getPosition($playerPage, 'underground_training');
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
            list($playerPos, $totalPlayers) = getPosition($player, 'group');
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
                $thumbImage = $place->photo->eq(0)->getThumb('thumbnail');
                echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$place->title<br />$place->summary<br />[{$place->parent->title},{$place->parent->parent->title}]' /></a></li>";
              }
            ?>
            </ul>

            <?php
              if ($playerPage->rank->name == '4emes' || $playerPage->rank->name == '3emes') {
            ?>
            <h4 class="badge badge-info"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> Free people </span></h4>
            <ul class="playerPlaces list-inline">
            <?php
              foreach($playerPage->people as $p) {
                $thumbImage = $p->photo->eq(0)->getThumb('thumbnail');
                echo "<li><a href='{$p->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$p->title<br />$p->summary' /></a></li>";
              }
            ?>
            </ul>
        </div>
        <?php } else { ?>
          <p class="badge badge-danger">People are available for 4emes and 3emes only.</p>
        <?php } ?>

        <div class="panel-footer">
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
  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4>History</h4>
        </div>
        <div class="panel-body">
          <div id="Filters" data-fcolindex="2" class="text-center">
            <ul class="list-inline well">
              <?php foreach ($allCategories as $category) { ?>
              <li><label for="<?php echo $category->name; ?>" class="btn btn-primary btn-xs"><?php echo $category->title; ?> <input type="checkbox" value="<?php echo $category->title; ?>" class="categoryFilter" name="categoryFilter" id="<?php echo $category->name; ?>"></label></li>
              <?php } ?> 
            </ul>
          </div>
            <table id="historyTable" class="table table-condensed table-hover">
              <thead>
                <tr>
                <th>Date</th>
                <th>+/-</th>
                <th>Category</th>
                <th>Title</th>
                <th>Comment</th>
              </tr>
              </thead>
              <tbody>
              <?php
                foreach($allEvents as $event) {
                  if ($event->task->XP > 0 || ($event->task->category->name === 'place' || $event->task->category->name === 'shop' || $event->task->name === 'positive-collective-alchemy') ) {
                    $class = '+';
                  } else {
                    $class = '-';
                  }
                  echo "<tr>";
                  echo "<td data-order='{$event->date}'>";
                  echo strftime("%d/%m", $event->date);
                  echo "</td>";
                  echo "<td>";
                  echo $class;
                  echo "</td>";
                  echo "<td>";
                  /* echo "{$event->task->category->title}"; */
                  if ($event->name == 'freeing') {
                    if ($event->refPage->template == 'place') { echo 'Place'; }
                    if ($event->refPage->template == 'people') { echo 'People'; }
                  }
                  echo "</td>";
                  if ($user->isSuperuser()) {
                    echo "<td>".$event->title."</td>";
                  } else {
                    echo "<td>".$event->task->title."</td>";
                  }
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
