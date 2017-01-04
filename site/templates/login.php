<?php
include("./head.inc"); 

// For redirection to previous page
if ( $input->get('sender') != 1) {
  $sender = $pages->get($input->get('sender'));
  $team = $input->get('team');
  $url = $sender->url.$team;
  $homepage = false;
} else {
  $homepage = $pages->get('/newsboard');
}

// check for login before outputting markup
if($input->post->user && $input->post->pass) {
  $user = $sanitizer->username($input->post->user);
  $pass = $input->post->pass; 
  if($session->login($user, $pass)) {
    if (!$homepage) {
      //$session->redirect($url); 
      // Redirect user to Newsboard
      $session->redirect($pages->get('/newsboard')->url);
    } else {
      $session->redirect($homepage->url); 
    }
  }
}

$logoUrl = $pages->get("name=home")->photo->eq(1)->url;
?>

<div class="row">
  <div class="col-md-10 text-center">
    <h1>Welcome to Planet Alert !</h1>
    <form class="form-horizontal loginForm" action="<?php echo $page->url; ?>?sender=<?php echo $input->get('sender').'&team='.$input->get('team'); ?>" method="post">
      <?php if($input->post->user) echo "<h3><span class='label label-danger'>Login failed...</span></h3>"; ?>
      <div class="form-group">
        <label for="user" class="col-sm-4 control-label">User :</label>
        <div class="col-sm-6">
          <input class="form-control" type="text" name="user" id="user" placeholder="Username" />
        </div>
      </div>
      <div class="form-group">
        <label for="pass" class="col-sm-4 control-label">Password :</label>
        <div class="col-sm-6">
          <input class="form-control" type="password" name="pass" id="pass" placeholder="Password" /></label></p>
        </div>
      </div>
      <input type='submit' class="btn btn-info" name='submit' value='Connect' />
    </form>
  </div>

  <div class="col-md-2">
    <img class="" width="200" src="<?php echo $logoUrl; ?>" />
  </div>

  <div class="push"><!--//--!></div>
</div>
<?php
  include("./foot.inc"); 
?>
