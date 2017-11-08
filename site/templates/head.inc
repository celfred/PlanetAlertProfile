<!DOCTYPE html>
<html lang="en">
<head>
  <?php
		date_default_timezone_set('Europe/Paris');

    if ($input->get->logout == 1) {
      $session->logout();
      $session->redirect('./');
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
		$newsboardPage = $pages->get("/newsboard");
		// Cache allTeams for faster access
		$template = $templates->get("team");
		$allTeams = $cache->get('allTeams', $template, function($pages) {
			return $pages->find("template=team")->sort("title");
		});
		// Cache allPlayers for faster access
		$template = $templates->get("player");
		$allPlayers = $cache->get("allPlayers", $template, function($pages) {
			return $pages->find("template=player, team.name!=no-team")->sort("team->name, title");
		});
    if ($user->isLoggedin()) {
			if (!$user->isSuperuser()) { // Player is logged in
				$player = $pages->get("template='player', login=$user->name");
				$bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
				$bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
			} else { // Admin is logged in
				// Check official period dates vs today's date
				$now = time();
        $officialPeriod = $pages->get("name=admin-actions")->periods;
				if ($now < $officialPeriod->dateStart || $now > $officialPeriod->dateEnd) {
					echo '<div class="notification alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Today\'s date is OUT OF the official period dates !</div>';
				}
			}
		}
	?>
  <div id="wrap">

    <div id="masthead" class="masthead">
      <?php 
        if ($user->isSuperuser()) { // Admin menu
					echo '<ul>';
						echo '<div class="btn-group" role="group" aria-label="...">';
							$class = $page->name == 'home' ? "on" : "";
							echo "<a class='btn $class' href='{$homepage->url}'>{$homepage->title}</a>";
							$class = $page->name == 'newsboard' ? "on" : "";
							echo "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
							echo '<div class="btn-group" role="group">';
								$class = $page->name == 'players' || $page->name == "world" || $page->template == 'main-office' ? "on" : "";
								echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">The Teams <span class="caret"></span></button>';
								echo '<ul class="dropdown-menu">';
								foreach($allTeams as $team) {
									$class = $team->name == $input->urlSegment1 ? " class='selected-left'" : "";
									$teamPlayers = $allPlayers->find("team=$team");
									echo "<li class='dropdown-submenu'>";
									echo "<a$class href='{$homepage->url}main-office/{$team->name}/'>{$team->title}</a>";
									echo "<ul class='dropdown-menu'>";
									echo  "<li><a href='{$homepage->url}main-office/{$team->name}'>{$team->title} main office</a>";
									echo  "<li class='dropdown-submenu'><a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}'>{$team->title} team</a>";
									echo "<ul class='dropdown-menu'>";
									foreach ($teamPlayers as $p) {
										echo "<li><a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}/{$p->name}'>{$p->title}</a></li>";
									}
									echo "</ul>";
									echo "</li>";
									/* if ($team != '') { */
									/* 	echo "<li><a href='{$homepage->url}world/{$sanitizer->pageName($team->name)}'>{$team->title} world</a></li>"; */
									/* } */
									echo "<li><a href='{$homepage->url}shop/{$sanitizer->pageName($team->name)}'>{$team->title} marketplace</a></li>";
									echo "<li><a href='{$homepage->url}adminTable/{$sanitizer->pageName($team->name)}'>{$team->title} adminTable</a></li>";
									echo "<li><a href='{$homepage->url}quiz/{$sanitizer->pageName($team->name)}'>{$team->title} monster invasions</a></li>";
									echo "<li><a href='{$homepage->url}world/{$sanitizer->pageName($team->name)}'>{$team->title} Free World</a></li>";
									echo "</ul>";
									echo "</li>";
								}
								echo '</ul>';
							echo '</div>';
							echo '<div class="btn-group">';
								$class = $page->is("name=blog|documentation|contact") ? "on" : "";
								echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
								echo '<ul class="dropdown-menu">';
									echo '<li class="dropdown-submenu">';
									$class = $page->is("name=shop|places|people|tasks|monsters|hall-of-fame") ? "selected-left" : "";
									echo '<a class="'.$class.'" href=""><span class="glyphicon glyphicon-globe"></span> - Discover Planet Alert World</a>';
									echo '<ul class="dropdown-menu">';
										$class = $page->name == 'shop' ? " class='selected-left'" : "";
										echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
										$class = $page->name == 'places' ? " class='selected-left'" : "";
										echo "<li><a$class href='{$pages->get('/places')->url}'>The Places</a></li>";
										$class = $page->name == 'people' ? " class='selected-left'" : "";
										echo "<li><a$class href='{$pages->get('/people')->url}'>The People</a></li>";
										$class = $page->name == 'tasks' ? " class='selected-left'" : "";
										echo "<li><a$class href='{$pages->get('/tasks')->url}'>The Actions</a></li>";
										$class = $page->name == 'monsters' ? " class='selected-left'" : "";
										echo "<li><a$class href='{$pages->get('/monsters')->url}'>The Monsters</a></li>";
										$class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
										echo "<li><a$class href='{$pages->get('/hall-of-fame')->url}'>The Hall of Fame</a></li>";
									echo '</ul>';
								echo '</li>';
								$class = $page->name == 'contact' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=contact')->url}'><span class='glyphicon glyphicon-envelope'></span> - Contact</a></li>";
								$class = $page->name == 'blog' ? " class='selected-left'" : "";
								echo "<li><a$class href='{$pages->get('name=blog')->url}'><span class='glyphicon glyphicon-hand-up'></span> - Admin Announcements</a></li>";
								$class = $page->name == 'documentation' ? " class='selected-left'" : "";
								echo "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - Documentation</a></li>";
							echo '</ul>';
							echo '</div>';
							echo '<div class="btn-group">';
								$class = $page->is("name=admin-actions") ? "on" : "";
								echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Admin Zone<span class="caret"> </span></button>';
								echo '<ul class="dropdown-menu">';
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'team-options' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/team-options/'><span class='glyphicon glyphicon-cog'></span> - Team options</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'reports' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/reports/'><span class='glyphicon glyphicon-file'></span> - Reports</a></li>";
									$class = $page->name == 'statistics' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=statistics')->url}'><span class='glyphicon glyphicon-stats'></span> - Statistics</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'recalculate' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/recalculate'><span class='glyphicon glyphicon-pencil'></span> - Edit history (and scores)</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'users' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/users'><span class='glyphicon glyphicon-user'></span> - Users</a></li>";
									echo "<li role='separator' class='divider'></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'ut-stats' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/ut-stats/'>UT Stats</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'fights-stats' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/fights-stats/'>Fights Stats</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'task-report' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/task-report/'>Task report</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'ut' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/ut/'>UT Hall of fame</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setScores' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/setScores/'>Set scores</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setKarma' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/setKarma/'>Set karmas</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setYearlyKarma' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/setYearlyKarma/'>Set yearly karmas</a></li>";
									$class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setCaptains' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/setCaptains/'>Set captains</a></li>";
									$class = $page->name == 'script' && $input->urlSegment1 == 'ut' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=admin-actions')->url}/script/'>Script</a></li>";
								echo '</ul>';
							echo '</div>';
							echo '<a class="btn" href="'.$loginUrl.'?logout=1">Log out</a>';
						echo '</div>';
					echo '</ul>';
				}

        if (!$user->isLoggedin()) { // Public menu
					echo '<ul>';
						echo '<div class="btn-group" role="group" aria-label="...">';
							echo $class = $page->name == 'home' ? "on" : "";
							echo "<a class='btn $class' href='{$homepage->url}'>{$homepage->title}</a>";
							$class = $page->name == 'newsboard' ? "on" : "";
							echo "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
							echo '<div class="btn-group" role="group">';
								$class = $page->name == 'players' || $page->name == "world" ? "on" : "";
								echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">The Teams <span class="caret"></span></button>';
								echo '<ul class="dropdown-menu">';
									foreach($allTeams as $team) {
										$class = $team->name == $input->urlSegment1 ? " class='selected-left'" : "";
										echo "<li><a$class href='{$homepage->url}players/{$team->name}/'>{$team->title}</a></li>";
									}
								echo '</ul>';
							echo '</div>';
							echo '<div class="btn-group">';
								$class = $page->is("name=shop|places|people|actions|monsters|hall-of-fame") ? "selected-left" : "";
								echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">The World <span class="caret"> </span></button>';
								echo '<ul class="dropdown-menu">';
									$class = $page->name == 'shop' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
									$class = $page->name == 'places' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/places')->url}'>The Places</a></li>";
									$class = $page->name == 'people' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/people')->url}'>The People</a></li>";
									$class = $page->name == 'tasks' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/tasks')->url}'>The Actions</a></li>";
									$class = $page->name == 'monsters' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/monsters')->url}'>The Monsters</a></li>";
									$class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/hall-of-fame')->url}'>The Hall of Fame</a></li>";
								echo '</ul>';
							echo '</div>';
							echo '<div class="btn-group">';
								$class = $page->is("name=blog|documentation|contact") ? "on" : "";
								echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
								echo '<ul class="dropdown-menu">';
									$class = $page->name == 'contact' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=contact')->url}'><span class='glyphicon glyphicon-envelope'></span> - Contact</a></li>";
									$class = $page->name == 'blog' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('name=blog')->url}'><span class='glyphicon glyphicon-hand-up'></span> - Admin Announcements</a></li>";
									$class = $page->name == 'documentation' ? " class='selected-left'" : "";
									echo "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - Documentation</a></li>";
								echo '</ul>';
							echo '</div>';
							$class = $page->name == 'login' ? "on" : "";
							echo "<a class='btn $class' href='".$loginUrl."?sender=".$page->id.'&team='.$input->urlSegment1."'>Login</a>";
						echo '</div>';
					echo '</ul>';
      } else { // Player Menu
				if (!$user->isSuperuser()) { // Not for admin !
			?>
        <div style=" color: #FFF;">
        <?php
					echo '<span class="avatarContainer">';
					if ($player->avatar) {
						echo '<img class="avatar superpose" src="'.$player->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />';
					} else {
						echo '<Avatar>';
					}
					if ($bestWeapon && $bestWeapon->image) {
						echo '<img class="weapon mini superpose" src="'.$bestWeapon->image->getCrop("mini")->url.'" alt="'.$bestWeapon->title.'" />';
					}
					if ($bestProtection && $bestProtection->image) {
						echo '<img class="protection mini superpose" src="'.$bestProtection->image->getCrop("mini")->url.'" alt="'.$bestProtection->title.'" />';
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
          ?>

          <div class="btn-group">
						<?php $class = ($page->is("name=players|main-office|world") && $input->urlSegment2 == '') ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Team <span class="caret"> </span></button>
            <ul class="dropdown-menu">
              <?php
              $class = $page->name == 'main-office' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=main-office')->url}{$player->team->name}'><span class='glyphicon glyphicon-eye-open'></span> - Main Office</a></li>";
              $class = $page->name == 'players' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('/players')->url}{$player->team->name}'><span class='glyphicon glyphicon-th-list'></span> - List </a></li>";
              $class = $page->name == 'world' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=world')->url}{$player->team->name}'><span class='glyphicon glyphicon-flag'></span> - Free World</a></li>";
							?>
            </ul>
          </div>

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
              if ($player && ($player->team->forceHelmet == 1 || $player->equipment->get("name=memory-helmet"))) {
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
              // Check if Visualizer is accessible in player's group
              if ($player && ($player->team->forceVisualizer == 1 || $player->equipment->get("name=electronic-visualizer"))) {
								$class = $page->name == 'visualizer' ? " class='selected-left'" : "";
								if ($player->team->forceVisualizer == 1) {
									echo "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=visualizer')->url}'><span class='glyphicon glyphicon-th'></span> - Use the Electronic Visualizer</a></li>";
								} else {
									echo "<li><a$class href='{$pages->get('name=visualizer')->url}'><span class='glyphicon glyphicon-th'></span> - Use the Electronic Visualizer</a></li>";
								}
              } else {
                echo "<li>&nbsp; <span class='badge badge-danger'>Locked</span> Use the Electronic Visualizer</li>";
              }
							// Hall of Fame
							$class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
							echo "<li><a$class href='{$pages->get('/hall-of-fame')->url}'><span class='glyphicon glyphicon-eye-open'></span> - Visit the Hall of Fame</a></li>";
							?>
							<li class="dropdown-submenu">
								<?php $class = $page->is("name=shop|places|people|tasks|monsters|hall-of-fame") ? "selected-left" : ""; ?>
								<a class="<?php echo $class; ?>" href=""><span class='glyphicon glyphicon-globe'></span> - Discover Planet Alert World</a>
								<ul class="dropdown-menu">
									<?php
									$class = $page->name == 'shop' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
									$class = $page->name == 'places' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/places')->url}'>The Places</a></li>";
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
							</li>
            </ul>
          </div>

          <div class="btn-group">
          <?php
            $class = $page->is("name=blog|documentation|contact") ? "on" : ""; ?>
            <button type="button" class="<?php echo $class; ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>
            <ul class="dropdown-menu">
							<li class="dropdown-submenu">
								<?php $class = $page->is("name=shop|places|people|actions|monsters|hall-of-fame") ? "selected-left" : ""; ?>
								<a class="<?php echo $class; ?>" href=""><span class='glyphicon glyphicon-globe'></span> - The World</a>
								<ul class="dropdown-menu">
									<?php
									$class = $page->name == 'shop' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
									$class = $page->name == 'places' ? " class='selected-left'" : "";
									echo "<li><a$class href='{$pages->get('/places')->url}'>The Places</a></li>";
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
							</li>
							<?php
							$class = $page->name == 'contact' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=contact')->url}'><span class='glyphicon glyphicon-envelope'></span> - Contact</a></li>";
							$class = $page->name == 'blog' ? " class='selected-left'" : "";
              echo "<li><a$class href='{$pages->get('name=blog')->url}'><span class='glyphicon glyphicon-hand-up'></span> - Admin Announcements</a></li>";
							$class = $page->name == 'documentation' ? " class='selected-left'" : "";
              echo "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - Documentation</a></li>";
              ?>
            </ul>
          </div>
					<?php echo '<a class="btn" href="'.$loginUrl.'?logout=1">Log out</a>'; ?>
        </div>
        </ul>
      <?php } ?> <!-- /!isSuperuser() -->
      <span id="bgtitle">Planet Alert</span>
			<?php } ?> <!-- /isLoggedin() -->
    </div> <!-- /#masthead -->

  <div class="container">
    <div class="row">
      <div id="content" class="col-sm-12">
