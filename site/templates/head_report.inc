<?php namespace ProcessWire; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php
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
  <title>
    <?php
      if (count($input->post) > 0) {
        $reportSelected = $input->post->reportSelected;
      } else {
        $reportSelected = $input->urlSegment2;
      }
      $selected = $pages->get("id=$reportSelected");
      echo $page->get("title").' '.$selected->title;
    ?>
  </title>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo $page->summary; ?>" />
	<meta name="generator" content="ProcessWire <?php echo $config->version; ?>" />

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/main.css" />

	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/ie.css" />
	<![endif]-->	
</head>

<body>
  <div id="wrap">

	<?php
		if (!$input->get['pages2pdf'] && ($user->isSuperuser() || $user->hasRole('teacher'))) {
	?>
    <div id="masthead" class="masthead">
      <span id="bgtitle">Planet Alert</span>
    </div> <!-- /#masthead -->
	<?php } ?>

  <div class="container">
    <div class="row">
      <div id="content" class="col-sm-12">
