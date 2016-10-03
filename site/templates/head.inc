<!DOCTYPE html>
<html lang="en">
<head>
  <?php
		date_default_timezone_set('Europe/Paris');
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
    $allPlayers = $pages->find("template=player")->sort("team->name, title");
		$allTeams = $pages->find("template=team")->sort("title");
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
                  $class = $team->name == $input->urlSegment1 ? " class='selected-left'" : "";
                  if ($user->isSuperuser()) { // Display direct access via submenu only for admin
										$teamPlayers = $allPlayers->find("team=$team");
                    echo "<li class='dropdown-submenu'>";
                    echo "<a$class href='{$homepage->url}players/{$sanitizer->pageName($team->name)}/'>{$team->title}</a>";
                    echo "<ul class='dropdown-menu'>";
                    echo "<li class='dropdown-submenu'><a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}'>{$team->title} team</a>";
                    echo "<ul class='dropdown-menu'>";
										foreach ($teamPlayers as $p) {
											echo "<li><a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}/{$p->name}'>{$p->title}</a></li>";
										}
                    echo "</ul>";
                    echo "</li>";
										if ($player->team != '') {
											echo "<li><a href='{$homepage->url}world/{$sanitizer->pageName($team->name)}'>{$team->title} world</a></li>";
										}
                    echo "<li><a href='{$homepage->url}shop/{$sanitizer->pageName($team->name)}'>{$team->title} marketplace</a></li>";
                    echo "<li><a href='{$homepage->url}adminTable/{$sanitizer->pageName($team->name)}'>{$team->title} adminTable</a></li>";
                    echo "<li><a href='{$homepage->url}quiz/{$sanitizer->pageName($team->name)}'>{$team->title} monster invasions</a></li>";
                    echo "</ul>";
                    echo "</li>";
                  } else {
                      echo "<li><a$class href='{$homepage->url}players/{$sanitizer->pageName($team->name)}/'>{$team->title}</a></li>";
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
              $class = $page->name == 'people' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/people')->url}'>The People</a></li>";
              $class = ($page->name == 'tasks' || $page->template == 'task') ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/tasks')->url}'>The Actions</a></li>";
              $class = $page->name == 'monsters' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/monsters')->url}'>The Monsters</a></li>";
              $class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/hall-of-fame')->url}'>Hall of Fame</a></li>";
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
						if ($user->isSuperuser()) { // Display Admin specific menu
					?>
						<div class="btn-group">
						<?php
							$class = $page->is("name=admin-actions") ? "on" : ""; ?>
							<button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Admin Zone<span class="caret"> </span></button>
							<ul class="dropdown-menu">
								<?php
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'team-options' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/team-options/'>Team options</a></li>";
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'reports' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/reports/'>Reports</a></li>";
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'ut-stats' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/ut-stats/'>UT Stats</a></li>";
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'task-report' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/task-report/'>Task report</a></li>";
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'recalculate' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/recalculate'>Edit history (and scores)</a></li>";
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'ut' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/ut/'>UT Hall of fame</a></li>";
								$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setScores' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/setScores/'>Set scores</a></li>";
								$class = $page->name == 'script' && $input->urlSegment1 == 'ut' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/script/'>Script</a></li>";
								?>
							</ul>
						</div>
					<?php
						}
					?>
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
        <div style=" color: #FFF;">
        <?php
          $player = $pages->get("template=player, login=$user->name");
					$bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
					$bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
          /* echo '<img class="" src="'.$player->avatar->url.'" width="95" alt="No avatar" />'; */
					echo '<span class="avatarContainer">';
					if ($player->avatar) {
						echo '<img class="avatar superpose" src="'.$player->avatar->getThumb("thumbnail").'" width="80" alt="Avatar" />';
					} else {
						echo '<Avatar>';
					}
					if ($bestWeapon->id && $bestWeapon->image) {
						echo '<img class="weapon mini superpose" src="'.$bestWeapon->image->getThumb("mini").'" alt="'.$bestWeapon->title.'" />';
					}
					if ($bestProtection->id && $bestProtection->image) {
						echo '<img class="protection mini superpose" src="'.$bestProtection->image->getThumb("mini").'" alt="'.$bestProtection->title.'" />';
					}
					echo '</span>';
        ?>
        </div>
        <ul>
        <div class="btn-group" role="group" aria-label="...">
          <?php
            $class = $page->name == 'newsboard' ? "on" : "";
            echo "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
            $class = ($player->team->name == $input->urlSegment1 && $input->urlSegment2 == $player->name ) ? "on" : "";
            echo "<a class='btn $class' href='{$pages->get('/players')->url}{$player->team->name}/{$player->name}'>My Profile</a>";
						if ($player->team != '') {
							$class = ($player->team->name == $input->urlSegment1 && $input->urlSegment2 == '') ? "on" : "";
							echo "<a class='btn $class' href='{$pages->get('/players')->url}{$player->team->name}'>My Team</a>";
						}
          ?>

          <div class="btn-group">
            <?php $class = $page->is("name=shop_generator|makedonation") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Actions <span class="caret"> </span></button>
            <ul class="dropdown-menu">
              <?php
							$class = $page->is("name=shop_generator") ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/shop_generator')->url}{$player->id}'><span class='glyphicon glyphicon-shopping-cart'></span> - Go to the Marketplace</a></li>";
              $class = $page->name == 'makedonation' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=makeDonation')->url}'><span class='glyphicon glyphicon-heart'></span> - Make a donation</a></li>";
              // Check if player can face Monster Invasions
							if ($player->places->count >= 3 || $player->people->count >= 3) {
								$class = $page->name == 'quiz' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=quiz')->url}'><span class='glyphicon glyphicon-star'></span> - Prepare my defense</a></li>";
							} else {
								echo "<li>&nbsp; <span class='badge badge-danger'>Locked</span></span> Prepare my defense</li>";
							}
              // Check if memory helmet accessible in player's group
              if ($player->team->forceHelmet == 1 || $player->equipment->get("name=memory-helmet")) {
								if ($player->team->forceHelmet == 1) {
									$class = $page->name == 'underground-training' ? " class='selected-left'" : "";
									echo "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=underground-training')->url}'><span class='glyphicon glyphicon-headphones'></span> - Use the memory helmet</a></li>";
									$class = $page->name == 'fighting-zone' ? " class='selected-left'" : "";
									echo "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=fighting-zone')->url}'><span class='glyphicon glyphicon-flash'></span> - Fight a monster</a></li>";
								} else {
									$class = $page->name == 'underground-training' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=underground-training')->url}'><span class='glyphicon glyphicon-headphones'></span> - Use the memory helmet</a></li>";
									$class = $page->name == 'fighting-zone' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=fighting-zone')->url}'><span class='glyphicon glyphicon-flash'></span> - Fight a monster</a></li>";
								}
              } else {
                echo "<li>&nbsp; <span class='badge badge-danger'>Locked</span> Use the Memory helmet</li>";
                echo "<li>&nbsp; <span class='badge badge-danger'>Locked</span> Fight a monster</li>";
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
              $class = $page->name == 'people' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/people')->url}'>The People</a></li>";
              $class = $page->name == 'tasks' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/tasks')->url}'>The Actions</a></li>";
              $class = $page->name == 'monsters' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/monsters')->url}'>The Monsters</a></li>";
              $class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/hall-of-fame')->url}'>The Hall of Fame</a></li>";
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
