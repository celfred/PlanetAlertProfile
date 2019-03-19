<?php namespace ProcessWire;
include("./head.inc");

// check for login before outputting markup
if($input->post->username && $input->post->pass) {
  $userName = $sanitizer->pageName($input->post->username);
  $pass = $input->post->pass; 
  if($session->login($userName, $pass)) {
    $session->redirect($newsboardPage->url.$userName); // Redirect logged user to newboard
  }
}

$logoUrl = $pages->get("name=home")->photo->eq(1)->url;
?>

<div class="row">
  <div class="col-md-12 text-center">
    <h1>Welcome to Planet Alert !</h1>
    <?php if (!$user->isLoggedin()) {
      if($input->post->username) echo "<h3><span class='label label-danger'>Login failed... (check user name or password)</span></h3>"; ?>
      <form class="form-horizontal loginForm" action="<?php echo $page->url; ?>" method="post">
        <div class="form-group">
          <label for="username" class="col-sm-4 control-label">User :</label>
          <div class="col-sm-6">
            <input class="form-control" type="text" name="username" id="username" placeholder="Username" />
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
    <?php
    } else {
      echo '<div class="well">'.__("You are already logged in. Have fun using Planet Alert ;)").'</div>';
      echo '<p>'.__("Log out in the menu above if needed.").'</p>';
    }
    ?>
  </div>
  <div class="push"><!--//--!></div>
</div>
<?php
  include("./foot.inc"); 
?>
