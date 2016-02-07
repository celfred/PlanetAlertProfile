<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    include('./my-functions.inc'); // Planet Alert PHP functions
    $whitelist = array('127.0.0.1', 'localhost');
    if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
      // Remote server
      echo "<base href='/index.html'>";
    } else {
      // Localhost
      echo "<base href='/PlanetAlert/index.html'>";
    }

    if ($input->get->logout == 1) {
      $session->logout();
      $session->redirect('./');
    }

    if ($user->isLoggedin()) {
      $player = $pages->get("template='player', login=$user->name");
    }
  ?>
	<title><?php echo $page->get("headline|title"); ?></title>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo $page->summary; ?>" />
	<meta name="generator" content="ProcessWire <?php echo $config->version; ?>" />

  <link rel="icon" type="image/png" href="<?php echo $config->urls->templates?>img/favicon.png">

	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/css/bootstrap-theme.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/datatables-plugins-bootstrap3/dist/css/datatables-plugins-bootstrap3.css" />

	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/main.css" />

	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/ie.css" />
	<![endif]-->	

  <?php if (($page->template == 'place' && $page->name!='places') || $page->name == 'map') { ?>
    <script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?sensor=false'></script>
  <?php } ?>
</head>

<body>
  <?php
    $homepage = $pages->get("/"); 
    $allPlayers = $pages->find("template=player");
    $allTeams = [];
    foreach ($allPlayers as $player) {
      if (!in_array($player->playerTeam, $allTeams)) {
        if ($player->playerTeam == '' && !in_array('No team', $allTeams)) {
          array_push($allTeams, 'No team'); 
        } else {
          if ($player->playerTeam != '' && $player->playerTeam != 'testTeam') {
            array_push($allTeams, $player->playerTeam); 
          }
        }
      }
    }
    arsort($allTeams);

  ?>
          
  <div id="wrap">

    <div id="masthead" class="masthead">
      <?php 
        $newsboardPage = $pages->get("/newsboard");
        if ($user->isLoggedin() == false || ($user->isSuperuser())) { // No user logged : display all teams (public view)
      ?>
        <ul>
        <div class="btn-group" role="group" aria-label="...">
          <?php
            $class = $page->name == 'home' ? "on" : "";
            echo "<a class='btn $class' href='{$homepage->url}'>{$homepage->title}</a>";
            $class = $page->name == 'newsboard' ? "on" : "";
            echo "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
          ?>

          <div class="btn-group" role="group">
            <?php $class = $page->name == 'players' || $page->name == "world" ? "on" : ""; ?>
            <button type="button" class="<?php echo $class;?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">The Teams <span class="caret"></span></button>
            <ul class="dropdown-menu">
              <?php
                foreach($allTeams as $team) {
                  $class = $team == $input->urlSegment1 ? " class='selected-left'" : "";
                  if ($user->isSuperuser()) { // Display direct access via submenu only for admin
                    echo "<li class='dropdown-submenu'>";
                    echo "<a$class href='{$homepage->url}players/{$sanitizer->pageName($team)}/'>{$team}</a>";
                    echo "<ul class='dropdown-menu'>";
                    echo "<li><a href='{$homepage->url}players/{$sanitizer->pageName($team)}'>{$team} team</a></li>";
                    echo "<li><a href='{$homepage->url}world/{$sanitizer->pageName($team)}'>{$team} world</a></li>";
                    echo "<li><a href='{$homepage->url}shop/{$sanitizer->pageName($team)}'>{$team} marketplace</a></li>";
                    echo "<li><a href='{$homepage->url}adminTable/{$sanitizer->pageName($team)}'>{$team} adminTable</a></li>";
                    echo "<li><a href='{$homepage->url}quiz/{$sanitizer->pageName($team)}'>{$team} monster invasions</a></li>";
                    echo "<li><a href='{$homepage->url}reports/{$sanitizer->pageName($team)}'>{$team} reports</a></li>";
                    echo "</ul>";
                    echo "</li>";
                  } else {
                      echo "<li><a$class href='{$homepage->url}players/{$sanitizer->pageName($team)}/'>{$team}</a></li>";
                  }
                }
              ?>
            </ul>
          </div>
          <div class="btn-group">
            <?php $class = $page->is("name=shop|places|tasks|task") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">The World <span class="caret"> </span></button>
            <ul class="dropdown-menu">
              <?php
              $class = $page->name == 'shop' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
              $class = $page->name == 'places' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/places')->url}'>The Map</a></li>";
              $class = ($page->name == 'tasks' || $page->template == 'task') ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/tasks')->url}'>The Actions</a></li>";
              $class = $page->name == 'monsters' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/monsters')->url}'>The Monsters</a></li>";
              ?>
            </ul>
          </div>
          <div class="btn-group">
          <?php
            $class = $page->is("name=blog|documentation|contact") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>
            <ul class="dropdown-menu">
							<?php
							$class = $page->name == 'contact' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=contact')->url}'>Contact</a></li>";
							$class = $page->name == 'blog' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=blog')->url}'>Newsboard Archives</a></li>";
							$class = $page->name == 'documentation' ? " class='selected-left'" : "";
              echo "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'>Documentation</a></li>";
              ?>
            </ul>
          </div>
					<?php
            if ( $user->isLoggedin()) {
              echo '<a class="btn" href="./?logout=1">Log out</a>';
            } else {
              $class = $page->name == 'login' ? "on" : "";
              echo "<a class='btn $class' href='".$pages->get('/login')->url."?sender=".$page->id.'&team='.$input->urlSegment1."'>Login</a>";
            }
          ?>
        </div>
        </ul>
      <?php } else { // Logged user's Menu ?>
        <div style="float:left; color: #FFF;">
        <?php
          $player = $pages->get("template=player, login=$user->name");
          echo '<img class="" src="'.$player->avatar->url.'" width="95" alt="No avatar" />';
        ?>
        </div>
        <ul>
        <div class="btn-group" role="group" aria-label="...">
          <?php
            $class = $page->name == 'newsboard' ? "on" : "";
            echo "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
            $class = ($player->playerTeam == $input->urlSegment1 && $input->urlSegment2 == $player->name ) ? "on" : "";
            echo "<a class='btn $class' href='{$pages->get('/players')->url}{$player->playerTeam}/{$player->name}'>My Profile</a>";
            $class = ($player->playerTeam == $input->urlSegment1 && $input->urlSegment2 == '') ? "on" : "";
            echo "<a class='btn $class' href='{$pages->get('/players')->url}{$player->playerTeam}'>My Team</a>";
          ?>

          <div class="btn-group">
            <?php $class = $page->is("name=shop_generator|makedonation") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Actions <span class="caret"> </span></button>
            <ul class="dropdown-menu">
              <?php
							$class = $page->is("name=shop_generator") ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/shop_generator')->url}{$player->id}'>Go to the Marketplace</a></li>";
              $class = $page->name == 'makedonation' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=makeDonation')->url}'>Make a donation</a></li>";
              $class = $page->name == 'underground-training' ? " class='selected-left'" : "";
              // Check if memory helmet accessible in player's group
              if ($player->equipment->get("name=memory-helmet")) {
                echo "<li><a$class href='{$pages->get('name=underground-training')->url}'>Use the memory helmet</a></li>";
              } else {
                echo "<li>&nbsp; <span class='badge badge-danger'>Locked</span> Memory helmet</li>";
              }
              /* $class = $page->name == 'places' ? " class='selected-left'" : ""; */
              /* echo "<li><a$class href='{$pages->get('/places')->url}'>Fight a monster</a></li>"; */
              ?>
            </ul>
          </div>

          <div class="btn-group">
            <?php $class = $page->is("name=shop|places|tasks|task") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">The World <span class="caret"> </span></button>
            <ul class="dropdown-menu">
              <?php
              $class = $page->name == 'shop' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
              $class = $page->name == 'places' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/places')->url}'>The Map</a></li>";
              $class = $page->name == 'tasks' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/tasks')->url}'>The Actions</a></li>";
              $class = $page->name == 'monsters' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/monsters')->url}'>The Monsters</a></li>";
              ?>
            </ul>
          </div>

          <div class="btn-group">
            <?php $class = ($page->name == 'report_generator') ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Reports <span class="caret"> </span></button>
            <ul class="dropdown-menu">
              <?php
              $class = $page->name == 'report_generator' ? " class='selected-left'" : "";
              $allPeriods = $pages->find("template=period, adminOnly=0");
              foreach($allPeriods as $period) {
                echo "<li><a$class target='_blank' href='{$pages->get('/report_generator')->url}all/{$player->name}/{$period->id}/'>{$period->title}</a></li>";
              }
              ?>
            </ul>
          </div>

          <div class="btn-group">
          <?php
            $class = $page->is("name=blog|documentation|contact") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>
            <ul class="dropdown-menu">
							<?php
							$class = $page->name == 'contact' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=contact')->url}'>Contact</a></li>";
							$class = $page->name == 'blog' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=blog')->url}'>Newsboard Archives</a></li>";
							$class = $page->name == 'documentation' ? " class='selected-left'" : "";
              echo "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'>Documentation</a></li>";
              ?>
            </ul>
          </div>
					<?php echo '<a class="btn" href="./?logout=1">Log out</a>'; ?>
        </div>
        </ul>
      <?php } ?>
      <span id="bgtitle">Planet Alert</span>
    </div> <!-- /#masthead -->

  <div class="container">
    <div class="row">
      <div id="content" class="col-sm-12">
