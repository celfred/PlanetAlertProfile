<?php namespace ProcessWire;
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
  $playerNbEl = $playerPlacesNb+$playerPeopleNb;
  $allEvents = $playerPage->child("name=history")->find("template=event,sort=-date");
  $rightInvasions = $allEvents->find("task.name=right-invasion")->count();
  $wrongInvasions = $allEvents->find("task.name=wrong-invasion")->count();

  $karma = $playerPage->yearlyKarma;
  if (!$karma) $karma = 0;
  if ($karma > 0 && $playerPage->team->name != 'no-team') { // Team Position
    // Number of players having a better karma than current player
    $playerPos = $pages->count("template=player,team=$playerPage->team,yearlyKarma>$karma") + 1;
  } else {
    $playerPos = $playersTotalNb;
  }

  // Set details according to user profile
  if ($user->isSuperuser() || ($user->hasRole('teacher') && $playerPage->team->teacher->has("id=$user->id")) || ($user->isLoggedin() && $user->name == $playerPage->login)) {
    $showDetails = true;
  } else {
    $showDetails = false;
  }
  // Set no hk counter
  if ($showDetails) {
    $hkCount = '<span class="label label-danger" data-toggle="tooltip" title="No hk counter">'.$playerPage->hkcount.'</span>';
  } else {
    $hkCount = '<span class="label label-danger">Private!</span>';
  }
  // Get last activity # of days
  $lastEvent = lastEvent($playerPage);
  $lastActivityCount = daysFromToday($lastEvent);
  if ($lastActivityCount > 20 && $lastActivityCount < 30) {
    $helpAlert = true;
    $helpTitle = __("Watch out for inactivity !");
    $helpMessage = '<h4>'.__("You will lose all your GC if you don't do anything (UT, fight, donation, ...) before 30 days of inactivity !").'</h4>'; 
  }

  // helpAlert
  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    echo '<div class="row">';
    $tmpCache = $playerPage->children()->get("name=tmp");
    if ($tmpCache) {
      echo '<a class="pdfLink btn btn-danger" href="'.$tmpCache->url.'">'.__("See tmpCache").'</a>';
    } else {
      createTmpCache($playerPage);
      $tmpCache = $playerPage->children()->get("name=tmp");
      echo '<a class="pdfLink btn btn-danger" href="'.$tmpCache->url.'">'.__("See tmpCache").'</a>';
    }
    // Check nb of pages according to nb of freed items
    echo '<a class="pdfLink btn btn-info" href="'.$playerPage->url.'?index=-1&pages2pdf=1">'.__("Empty PDF").'</a>';
    echo '<a class="pdfLink btn btn-info" href="'.$playerPage->url.'?index=0&pages2pdf=1">PDF 1</a>';
    $nbPage = ceil($playerNbEl/10);
    for ($i=0; $i<$nbPage; $i++) {
      echo '<a class="pdfLink btn btn-info" href="'.$playerPage->url.'?index='.($i+1).'&pages2pdf=1">PDF '.($i+2).'</a>';
    }
    echo '</div>';
  }
?>
<div class="row">
  <div class="col-lg-6 panel panel-success panel-player">
    <div class="panel-heading">
      <h1 class="panel-title">
        <span class="">
          <?php 
            echo $playerPage->title;
            if ($user->isSuperuser() || ($user->hasRole('teacher') && $playerPage->team->teacher->has("id=$user->id"))) { echo $playerPage->feel(); }
          ?>
        </span>
        <?php 
          $showSkills = '';
          foreach($playerPage->skills as $s) {
            $showSkills .= '&nbsp;<span class="label label-primary">'.$s->title.'</span>';
          }
          echo $showSkills;
        ?>
      </h1>
    </div>
    <div class="panel-body">
      <div class="row">
        <div class="text-center col-sm-6">
          <img src="<?php if ($playerPage->avatar) echo $playerPage->avatar->url; ?>" alt="No avatar" height="200" />
        </div>
        <div class="col-sm-6">
          <ul class="player-details">
          <li><?php echo __("Karma"); ?> : <span class="label label-default"><?php echo $karma; ?></span> <?php if ($playerPage->team->name != 'no-team') {?><span data-toggle="tooltip" title="Team position">(<?php echo $playerPos; ?>/<?php echo $playersTotalNb; ?>)</span><?php } ?></li>
          <li><?php echo __("Reputation"); ?> : <span class="label label-default"><?php echo $playerPage->reputation; ?></span></li>
          <li><?php echo __("Level"); ?> : <?php echo $playerPage->level; ?></li>
          <li><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="GC" /> : <span class="label label-default" data-toggle="tooltip" data-html="true" title="<?php echo __('Gold Coins'); ?>">
            <?php echo $playerPage->GC.' '.__("GC"); ?>
          </span></li>
          <?php 
            if ($playerPage->rank && $playerPage->rank->index >= 6 || $playerPage->team->rank->index >= 6) {
              echo '<li><span class="glyphicon glyphicon-exclamation-sign"></span> '. __("Hk count") .' : '.$hkCount.'</li>';
            }
            if ($lastActivityCount >= 0 && $lastActivityCount < 30) { // Active players
              echo '<li><span class="glyphicon glyphicon-thumbs-up"></span> '.__("Active player !").'</li>';
            } else { // 30 days of inactivity > lose all GC
              if ($lastActivityCount >= 20 && $lastActivityCount <= 30) { // Warning 10 days before losing GC
                $delay = 31-$lastActivityCount;
                echo '<li>';
                echo '<span class="glyphicon glyphicon-exclamation-sign"></span> '.$lastActivityCount.' '.__("days of inactivity.");
                echo printf(_n("%d day before losing all GC !", "%d days before losing all GC !", $delay), $delay);
                echo '</li>';
              } else {
                if ($lastActivityCount != -1) {
                  echo '<li><span class="glyphicon glyphicon-exclamation-sign"></span> '.sprintf(__("%d days of inactivity."), $lastActivityCount);
                } else {
                  echo '<li><span class="glyphicon glyphicon-exclamation-sign"></span> '.__("Long inactivity period");
                }
                echo ' → '.__("All GC have been lost !").'</li>';
              }
            }
          ?>
          </ul>
        </div>
      </div>
      <div class="row">
        <?php if ($playerPage->coma == 0) { ?>
          <div class="col-sm-2 text-right">
            <span class="badge"><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="HP" /> <?php echo $playerPage->HP; ?>/50</span>
          </div>
          <div class="col-sm-10">
            <div class="progress progress-striped progress-lg" data-toggle="tooltip" title="<?php echo __("Health points"); ?>">
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
            <span class="badge"><img src="<?php echo $config->urls->templates; ?>/img/star.png" alt="XP" /> <?php echo $playerPage->XP; ?>/<?php echo $threshold; ?></span>
          </div>
          <div class="col-sm-10">
            <div class="progress progress-striped progress-lg" data-toggle="tooltip" title="<?php echo sprintf(__("Experience (Level %d)"), $playerPage->level); ?>">
              <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo round((100*$playerPage->XP)/$threshold); ?>%">
              </div>
            </div>
          </div>
        <?php } else { ?> // Coma state 
          <h4 class="text-center"><span class="label label-danger"><?php echo __("You're in a COMA !"); ?></span></h4>
          <h4 class="text-center"><span><?php echo __("Buy a healing potion to go back to normal state !"); ?></span></h4>
        <?php } ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5 panel panel-success">
    <div class="panel-heading">
      <h4 class="panel-title"><?php echo __("Equipment"); ?></h4>
    </div>
    <div class="panel-body text-center">
      <ul class="list-inline">
        <?php
          if ($playerPage->equipment->count > 0) {
            foreach ($playerPage->equipment as $equipment) {
              if ($equipment->image) {
                if ($equipment->image->width() > $equipment->image->height()) {
                  $thumb = $equipment->image->getCrop('small')->url;
                } else {
                  $thumb = $equipment->image->getCrop('thumbnail')->url;
                }
                echo "<li data-toggle='tooltip' data-html='true' title='{$equipment->title}<br />{$equipment->summary}'>";
                if ($equipment->name == "memory-helmet") { // Direct link to training zone
                  echo '<a href="'.$pages->get('name=underground-training')->url.'" title="Go to the Training Zone"><img class="img-thumbnail" src="'.$thumb.'" /></a>';
                } else if ($equipment->name == "electronic-visualizer") { // Direct link to Visualizer page
                  echo '<a href="'.$pages->get("name=visualizer")->url.'" title="Use the '.$equipment->title.'"><img class="img-thumbnail" src="'.$thumb.'" /></a>';
                } else if ($equipment->name == "book-knowledge-item") { // Direct link to Visualizer page
                  echo '<a href="'.$pages->get("name=book-knowledge")->url.'" title="Use the '.$equipment->title.'"><img class="img-thumbnail" src="'.$thumb.'" /></a>';
                } else {
                  echo '<img class="img-thumbnail" src="'.$thumb.'" />';
                }
                echo "</li>";
              } else {
                echo '<li data-toggle="tooltip" data-html="true" title="'.$equipment->title.'<br />'.$equipment->summary.'">'.$equipment->title.'</li>';
              }
              echo "</li>";
            }
          } else {
            echo '<p>'.__("No equipment").'</p>';
          }
        ?>
      </ul>
      <hr />
      <p class="label label-danger"><?php echo __("Items you need to use in class ↓"); ?></p>
      <ul class="list-inline">
      <?php
        if (count($playerPage->usabledItems)) {
          foreach($playerPage->usabledItems as $i) {
            if ($i->image) {
              echo '<li data-toggle="tooltip" title="'.$i->title.'"><img src="'.$i->image->getCrop("small")->url.'" alt="'.$i->title.'" /></li>';
            } else {
              echo '<li>'.$i->title.'</li>';
            }
          }
        } else {
          echo '<li>'.__("No items").'</li>';
        }
      ?>
      </ul>
      <?php 
            echo '<p><span class="label label-danger">'.__("Fight request").'</span> → ';
        if ($playerPage->fight_request != 0) {
          $monster = $pages->get($playerPage->fight_request);
          echo $monster->title;
        } else {
          echo '-';
        }
      ?>
    </div>
    <?php 
      if ($showDetails) {
    ?>
    <div class="panel-footer">
      <?php
        echo '<p><a href="'.$pages->get('name=shop_generator')->url.$playerPage->id.'">→ '.__("Go to the Marketplace").'</a>.</p>';
      ?>
    </div>
    <?php
      }
    ?>
  </div>
</div>

<div class="row">
  <div class="panel panel-success">
    <div class="panel-heading">
      <h4 class="panel-title"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo __("Free elements"); ?> : <?php echo $playerPlacesNb+$playerPeopleNb; ?></span></h4>
    </div>
    <div class="panel-body">
    <h4 class="badge badge-info"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo __("Free places"); ?> </span></h4>
      <ul class="playerPlaces list-inline">
      <?php
        foreach($playerPage->places as $place) {
          $thumbImage = $place->photo->eq(0)->getCrop('thumbnail')->url;
          echo "<li><a href='{$place->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$place->title<br />$place->summary<br />[{$place->parent->title},{$place->parent->parent->title}]' /></a></li>";
        }
      ?>
      </ul>

      <?php
        if ($playerPage->rank && $playerPage->rank->is("index>=8")) {
      ?>
        <h4 class="badge badge-info"><span class=""><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo __("Free people"); ?> </span></h4>
        <ul class="playerPlaces list-inline">
        <?php
          foreach($playerPage->people as $p) {
            $thumbImage = $p->photo->eq(0)->getCrop('thumbnail')->url;
            echo "<li><a href='{$p->url}'><img class='img-thumbnail' src='{$thumbImage}' alt='' data-toggle='tooltip' data-html='true' title='$p->title<br />$p->summary' /></a></li>";
          }
        ?>
        </ul>
      <?php } else {
        echo '<p class="badge badge-danger">'.__("People are available for 4emes and 3emes only.").'</p>';
      } ?>
    </div>
    <?php 
      if ($user->hasRole('teacher') || $user->isSuperuser() || ($user->hasRole('player') && $user->name === $playerPage->login)) { // Admin is logged or user
    ?>
    <div class="panel-footer">
        <span class="glyphicon glyphicon-star"></span>
        <?php
        if ($rightInvasions > 0 || $wrongInvasions > 0) {
          echo __('Defensive power').' : <span>'.round(($rightInvasions*100)/($wrongInvasions+$rightInvasions)).'%</span>';
          echo '('.sprintf(__('You have repelled %1$s out of %2$s monster invasions'), $rightInvasions, ($rightInvasions+$wrongInvasions)).')';
        } else {
          echo __('You have not faced any monster invasion yet.');
        }
        ?>
    </div>
    <?php 
      }
    ?>
  </div>

  <div class="panel panel-success">
    <div class="panel-heading">
    <h4 class="panel-title"><span class=""><span class="glyphicon glyphicon-list"></span> <?php echo __("The Scoreboards"); ?></span></h4>
    </div>
    <div class="panel-body">
      <ul>
      <?php 
        // Most influential (reputation)
        list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'reputation');
        if ($playerPos) {
          if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=reputation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Most influential').' : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
        } else {
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=reputation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Most influential').' : ';
          echo __('No ranking.');
          echo '</p></li>';
        }
        // Greatest # of places (places)
        list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'places');
        if ($playerPos) {
          if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Greatest # of places').' : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
        } else {
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Greatest # of places').' : ';
          echo __('No ranking.');
          echo '</p></li>';
        }
        // Greatest # of people (people)
        list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'people');
        if ($playerPos) {
          if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Greatest # of people').' : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
        } else {
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Greatest # of people').' : ';
          echo __('No ranking.');
          echo '</p></li>';
        }
        // Best warrior (fighting_power)
        list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'fighting_power');
        if ($playerPos) {
          if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Best warriors').' : '.$playerPos.'/'.$totalPlayers.' '. $star.'</p></li>';
        } else {
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Best warriors').' : ';
          echo __('No ranking.');
          echo '</p></li>';
        }
        // Best donators (donation)
        list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'donation');
        if ($playerPos) {
          if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Best donators').' : '.$playerPos.'/'.$totalPlayers.' '.$star.'</p></li>';
        } else {
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Best donators').' : ';
          echo __('No ranking.');
          echo '</p></li>';
        }
        // Most trained (underground_training)
        if ($playerPage->underground_training) {
          list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'underground_training');
          if ($playerPos) {
            if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
            echo '<li>';
            echo '<p>';
            echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
            echo __('Most trained').' : '.$playerPos.'/'.$totalPlayers.' '.$star;
            echo '</p></li>';
          } else {
            echo '<li>';
            echo '<p>';
            echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
            echo __('Most trained').' : ';
            echo __('No ranking.');
            echo '</p></li>';
          }
        }

        // Most active groups
        list($topPlayers, $prevPlayers, $playerPos, $totalPlayers) = getScoreboard($playerPage, 'group');
        if ($playerPos) {
          if ($playerPos === 1) { $star = '<span class="glyphicon glyphicon-star"></span>'; } else { $star=''; }
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=group"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Most active groups').' : '.$playerPos.'/'.$totalPlayers.' '.$star;
          echo '</p></li>';
        } else {
          echo '<li>';
          echo '<p>';
          echo '<a href="'.$pages->get('name=scoreboard')->url.'?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="'.__("See the complete scoreboard").'"></span></a> ';
          echo __('Most active groups').' : ';
          echo __('No ranking.');
          echo '</p></li>';
        }
      ?>
      </ul>
    </div>
  </div>

  <div class="panel panel-success">
    <div class="panel-heading">
    <h4 class="panel-title"><span class="glyphicon glyphicon-headphones"></span> <span class=""><?php echo __("Underground Training (UT)"); ?> : <?php echo $playerPage->underground_training; ?> / 
      <span class="glyphicon glyphicon-flash"></span> <?php echo __("Fighting Power (FP)"); ?> : <?php echo $playerPage->fighting_power; ?></span>&nbsp;
    </h4>
    </div>
    <?php 
      if ($showDetails) {
        if ($playerPage->equipment->has("name=memory-helmet")) {
    ?>
    <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="utreport&playerId=<?php echo $playerPage->id; ?>">
      <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
    </div>
    <div class="panel-body ajaxContent" data-priority="2" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="fightreport&playerId=<?php echo $playerPage->id; ?>">
      <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
    </div>
    <?php 
      } else {
        echo '<div class="panel-body">';
        echo '<p>'.__("You need to buy the Memory Helmet do Underground Training and be able to fight monsters").'</p>';
        echo '</div>';
      }  
    ?>
    <div class="panel-body ajaxContent" data-priority="3" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="battlereport&playerId=<?php echo $playerPage->id; ?>">
      <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
    </div>
    <div class="panel-footer">
      <?php
        if ($playerPage->equipment->get("name=memory-helmet")) {
          echo '→ <span class="glyphicon glyphicon-headphones"></span> <a href="'.$pages->get('name=underground-training')->url.'">'.__("Use the Memory Helmet (Training Zone)").'</a>&nbsp;&nbsp;&nbsp;';
          echo '→ <span class="glyphicon glyphicon-flash"></span> <a href="'.$pages->get('name=fighting-zone')->url.$playerPage->id.'">'.__("Go to the Fighting Zone").'</a>&nbsp;&nbsp;&nbsp;';
          if ($user->isSuperuser() || $user->hasRole('teacher') || ($user->hasRole('player') && $player->skills->has("name=fighter"))) {
            echo '→ <span class="glyphicon glyphicon-time"></span> <a href="'.$pages->get("name=fighters-playground")->url.$playerPage->name.'">'.__("Go to the Fighters playground").'</a>';
          }
        } else {
          $link = '<a href="'.$pages->get('name=shop')->url.'details/memory-helmet">'.$pages->get("template=item, name~=helmet")->title.'</a> ';
          echo sprintf(__("Sorry, but at least one member in your group needs to buy the %s to be able to access the Underground Training zone."), $link);
        }
      ?>
    </div>
    <?php
      } else {
        echo '<div class="panel-body"><p>'.__("Details are private.").'</p></div>';
      }
    ?>
  </div>

  <?php
    if ($showDetails) {
  ?>
      <div class="panel panel-success">
        <div class="panel-heading">
          <h4>
            <?php 
              echo __('History');
              if ($user->isSuperuser() || ($user->hasRole('teacher') && $playerPage->team->teacher->has("id=$user->id"))) {
                echo ' <a target="blank" href="'.$adminActions->url.'recalculate/'.$playerPage->id.'">'.__("[Edit history]").'</a>';
              }
            ?>
          </h4>
        </div>
        <div class="panel-body ajaxContent" data-priority="1" data-href="<?php echo $pages->get('name=ajax-content')->url; ?>" data-id="history&playerId=<?php echo $playerPage->id; ?>">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>
  <?php } ?>
</div>
