<?php namespace ProcessWire;

  include("./head.inc"); 

  echo '<div class="row">';
  echo '<h1 class="text-center">Planet Alert : '.$page->title.'</h1>';
  echo '</div>';

  // Display team scores
  echo '<div class="row">';
    if (!($allTeams->count() == 1 && $allTeams->eq(0)->name == 'no-team')) { // Means Just no-team
      showScores($allTeams);
    }
  echo '</div>';
  
  echo '<div class="row">';

  echo '<div class="col-sm-3">';
    ?>
      <div class="panel panel-success">
        <div class="panel-heading">
        <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=karma"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
        <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most influential</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="karma">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div class="panel panel-success">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=places"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> Greatest # of Places</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="places">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

    <?php
      echo '</div>';
      echo '<div class="col-sm-3">';
    ?>
      <div class="panel panel-success">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=people"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="<?php echo __("See the complete scoreboard"); ?>"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/globe.png" alt="" /> <?php echo __("Greatest # of People"); ?></h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="people">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div id="" class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=donation"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/heart.png" alt="" /> Best donators</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="donation">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

    <?php
      echo '</div>';
      echo '<div class="col-sm-3">';
    ?>

      <div id="" class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=underground_training"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><i class="glyphicon glyphicon-headphones"></i> Most trained</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="underground_training">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

      <div class="panel panel-info">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=fighting_power"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><span class="glyphicon glyphicon-flash"></span> Best warriors</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="fighting_power">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>

    <?php
      echo '</div>';
      echo '<div class="col-sm-3">';
    ?>
      <div id="" class="panel panel-success">
        <div class="panel-heading">
          <a class="pull-right" href="<?php echo $pages->get('name=scoreboard')->url; ?>?field=group"><span class="glyphicon glyphicon-list" data-toggle="tooltip" title="See the complete scoreboard"></span></a>
          <h4 class="panel-title"><img src="<?php echo $config->urls->templates; ?>img/star.png" alt="" /> Most active groups</h4>
        </div>
        <div class="panel-body ajaxContent" data-href="<?php echo $pages->get('name=scoreboard')->url; ?>" data-id="group">
          <p class="text-center"><img src="<?php echo $config->urls->templates; ?>img/hourglass.gif"></p>
        </div>
      </div>
    <?php
      echo '</div>';
      echo '</div>';

  include("./foot.inc"); 
?>
