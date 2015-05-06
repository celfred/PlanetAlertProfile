<?php
include("./head.inc"); 

// For redirection to previous page
if ( $input->get('sender') != 1) {
  $sender = $pages->get($input->get('sender'));
  $team = $input->get('team');
  $url = $sender->url.$team;
  $homepage = false;
} else {
  $homepage = $pages->get('/');
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

<h1>Login form</h1>
<form class="form" action="<?php echo $page->url; ?>?sender=<?php echo $input->get('sender').'&team='.$input->get('team'); ?>" method="post">
      <?php if($input->post->user) echo "<h2 class='error'>Login failed</h2>"; ?>
      <p><label>User <input class="form-control" type='text' name='user' /></label></p>
      <p><label>Password <input class="form-control" type='password' name='pass' /></label></p>
      <p><input type='submit' class="btn btn-primary" name='submit' value='Login' /></p>
</form>

<div class="push"><!--//--!></div>

<?php
  include("./foot.inc"); 
?>
