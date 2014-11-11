<?php

// For redirection to previous page
if ( $input->get('sender') != 1) {
  $sender = $pages->get($input->get('sender'));
  $team = $input->get('team');
  $url = $sender->url.$team;
  $homepage = false;
} else {
  $homepage = $pages->get('/');
}

if($user->isLoggedin()) {
  // user is already logged in, so they don't need to be here
  if (!$homepage) {
    $session->redirect($url); 
  } else { // Redirect to homepage
    $session->redirect($homepage->url); 
  }
}

// check for login before outputting markup
if($input->post->user && $input->post->pass) {
    $user = $sanitizer->username($input->post->user);
    $pass = $input->post->pass; 
    if($session->login($user, $pass)) {
      if (!$homepage) {
        $session->redirect($url); 
      } else {
        $session->redirect($homepage->url); 
      }
    }
}
?>

<html>
<head>

	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/css/bootstrap.min.css" />

	<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/main.css" />
</head>
<body>
  <div id="wrap">
  <div id="masthead" class="masthead">
    <span id="bgtitle">Planet Alert</span>
  </div>
  <div class="container">

  <h1>Login form (admin only...)</h1>
  <form class="form" action="./?sender=<?php echo $input->get('sender').'&team='.$input->get('team'); ?>" method="post">
        <?php if($input->post->user) echo "<h2 class='error'>Login failed</h2>"; ?>
        <p><label>User <input class="form-control" type='text' name='user' /></label></p>
        <p><label>Password <input class="form-control" type='password' name='pass' /></label></p>
        <p><input type='submit' class="btn btn-primary" name='submit' value='Login' /></p>
  </form>

  <div class="push"><!--//--!></div>
</div>
</div>

  <footer class="text-center">
      <p>Powered by <a href='http://processwire.com'>ProcessWire</a> OpenSource CMS - &copy; <?php echo date("Y"); ?> F.L. 
        <a href="about/">[À propos]</a>
        <?php if ($user->isSuperuser()) { ?>
          <a style="float: right;" href="<?php echo $config->urls->admin; ?>">[Backend access]</a>
        <?php } ?>
        <?php
          if($user->isLoggedin()) {
            echo "<a style='float: right; margin-right: 5px;' href='./?logout=1'>[Log out]</a>";
          } else {
            echo "<a style='float: right;' href='".$pages->get('/login')->url."?sender=".$page->id.'&team='.$input->urlSegment1."'>[Admin]</a>";
          }
        ?>
      </p>
	</footer>

</body>
</html>
