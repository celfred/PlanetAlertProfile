<!DOCTYPE html>
<!-- <html lang="en" ng-app="myApp"> -->
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
  ?>
	<title><?php echo $page->get("headline|title"); ?></title>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo $page->summary; ?>" />
	<meta name="generator" content="ProcessWire <?php echo $config->version; ?>" />

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
    $allTeams = $pages->get("/teams")->children->not("name=orphans");
  ?>
          
  <div id="wrap">

    <div id="masthead" class="masthead">
      <ul><?php
        foreach($children as $child) {
          $class = $child === $page->rootParent ? " class='on'" : '';
          if ($child->name == 'players') {
            foreach($allTeams as $team) {
              $class = $sanitizer->pageName($team->name) == $input->urlSegment1 ? " class='on'" : "";
              echo "<li><a$class href='{$child->url}{$sanitizer->pageName($team->name)}'>{$team->title}</a></li>";
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
      ?>
      </ul>
      <span id="bgtitle">Planet Alert</span>
      <?php if ($page->name != 'home') { ?>
      <?php } ?>
    </div> <!-- /#masthead -->

  <div class="container">
    <div class="row">
      <div id="content" class="col-sm-12">
