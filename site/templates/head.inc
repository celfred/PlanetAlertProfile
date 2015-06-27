<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    include('./my-functions.inc'); // Planet Alert PHP functions
    $whitelist = array('127.0.0.1');
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
    $children = $homepage->children;
    $children->prepend($homepage); 
    $allTeams = $pages->get("/teams")->children->not("name=orphans")->sort('-name');
  ?>
          
  <div id="wrap">

    <div id="masthead" class="masthead">
      <?php 
        if ($user->isLoggedin() == false || ($user->isSuperuser())) { // No user logged : display all teams (public view)
      ?>
        <ul><?php
          foreach($children as $child) {
            $class = $child === $page->rootParent ? " class='on'" : '';
            if ($child->name == 'players') {
              foreach($allTeams as $team) {
                $class = $sanitizer->pageName($team->name) == $input->urlSegment1 ? " class='on'" : "";
                if ($user->isLoggedin() == false || ($user->isSuperuser())) { // No user logged : display all teams (public view)
                  echo "<li><a$class href='{$child->url}{$sanitizer->pageName($team->name)}'>{$team->title}</a></li>";
                } else { // A user is logged : display his team only
                  if ($team->title == $player->team->title) {
                    echo "<li><a$class href='{$child->url}{$sanitizer->pageName($team->name)}'>{$team->title}</a></li>";
                  }
                }
              }

            } else {
              if ($child->name != 'reports') {
                if ($child->name == 'quiz') {
                  if ($user->isSuperuser()) {
                    echo "<li><a$class href='{$child->url}'>{$child->title}</a></li>";
                  }
                } else {
                  echo "<li><a$class href='{$child->url}'>{$child->title}</a></li>";
                }
              } else {
                if ($user->isSuperuser()) {
                  echo "<li><a$class href='{$child->url}'>{$child->title}</a></li>";
                }
              }
            }
          }
          if ( $user->isLoggedin()) {
            if ($user->isSuperuser() === false) {
              echo '<li><a href="'.$pages->get('/players')->url.$player->team->name.'/'.$player->name.'"><span class="username">My profile ('.$user->name.')</span></a></li>';
            }
            echo '<li><a href="./?logout=1">Log out</a></li>';
          } else {
            if ($page->name=='login') { $class=" class='on'"; }
            echo "<li><a$class href='".$pages->get('/login')->url."?sender=".$page->id.'&team='.$input->urlSegment1."'>Login</a></li>";
          }
        ?>
        </ul>
      <?php } else { // Logged user's Menu ?>
        <div style="float:left; color: #FFF;">
        <?php
          echo '<img class="" src="'.$player->avatar->url.'" width="95" alt="No avatar" />';
          //echo '<span class="logged">'.$player->title.' ('.$player->team->title.')</span>';
        ?>
        </div>
        <ul>
          <?php
            $newsboardPage = $pages->get("/newsboard");
            $class = $page->name == 'newsboard' ? " class='on'" : "";
            echo "<li><a$class href='{$newsboardPage->url}'>{$newsboardPage->title}</a></li>";
            $class = ($player->team->name == $input->urlSegment1 && $player->name == $input->urlSegment2) ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/players')->url}{$player->team->name}{$player->name}'>My Profile</a></li>";
            $class = ($player->team->name == $input->urlSegment1 && $input->urlSegment2 == '') ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/players')->url}{$player->team->name}'>My Team</a></li>";
            $class = $page->name == 'shop_generator' ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/shop_generator')->url}{$player->id}'>The Marketplace</a></li>";
            $class = $page->name == 'shop' ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/shop')->url}'>The Shop</a></li>";
            $class = $page->name == 'places' ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/places')->url}'>The Map</a></li>";
            $class = $page->name == 'documentation' ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/documentation')->url}'>Documentation</a></li>";
            $class = $page->name == 'contact' ? " class='on'" : "";
            echo "<li><a$class href='{$pages->get('/contact')->url}'>Contact</a></li>";
            echo '<li><a href="./?logout=1">Log out ('.$player->login.')</a></li>';
          ?>
        </ul>
      <?php } ?>
      <span id="bgtitle">Planet Alert</span>
    </div> <!-- /#masthead -->

  <div class="container">
    <div class="row">
      <div id="content" class="col-sm-12">
