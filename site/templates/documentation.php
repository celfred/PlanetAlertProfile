<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<head>
  <?php
    $whitelist = array('127.0.0.1');
    if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
      // Remote server
      echo "<base href='/documentation/'>";
    } else {
      // Localhost
      echo "<base href='/private/planetalert/documentation/'>";
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

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">

  <!-- Optional theme -->
  <!-- <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css"> -->

  <!-- Latest compiled and minified JavaScript -->
  <!-- <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script> -->

	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/main.css" />

	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/ie.css" />
	<![endif]-->	

  <script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?sensor=false'></script>
</head>

<body>
  <?php
    $homepage = $pages->get("/"); 
    $children = $homepage->children;
    $children->prepend($homepage); 
    $players = $pages->find("template=player");
    $uniqueResults = array(); 
    foreach($players as $resultPage) {
      $uniqueResults[$resultPage->team] = $resultPage; 
    }
  ?>
          
  <div id="wrap">

    <div id="masthead" class="masthead">
      <span id="bgtitle">Planet Alert</span>
      <ul><?php
        foreach($children as $child) {
          $class = $child === $page->rootParent ? " class='on'" : '';
          if ($child->name == 'players') {
            foreach($uniqueResults as $player) {
              $class = $sanitizer->pageName($player->team) == $input->urlSegment1 ? " class='on'" : "";
              echo "<li><a$class href='{$child->url}{$sanitizer->pageName($player->team)}'>{$player->team}</a></li>";
            }

          } else {
            echo "<li><a$class href='{$child->url}'>{$child->title}</a></li>";
          }
        }
      ?>
      </ul>
      <?php if ($page->name != 'home') { ?>
      <?php } ?>
    </div> <!-- /#masthead -->

  <div class="container">
    <div class="row">
      <div id="content" class="col-sm-12">

<?php
//include ('./head.inc');
include ('./documentation/docPlanetAlert.html5');

include ('./foot.inc');
?>

