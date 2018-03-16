<?php 

$out = '';

$logo = '<img style="float: left;" src="http://download.tuxfamily.org/planetalert/logo.png" width="100" height="100" /> ';
// Get user's avatar
if ($user->isLoggedin() || !$user->isSuperuser()) {
  $player = $pages->get("login=$user->name");
  if ($player->avatar) {
    $avatar =  '<img style="float: right;" src="'.$player->avatar->getCrop("thumbnail")->url.'" alt="Avatar" />';
  } else {
    $avatar = '<Avatar>';
  }
}

// Add today's date
$out .= '<p style="text-decoration: underline;">'.$logo.\date('l, F dS').$avatar.'</p>';

$out .= '<h1 style="text-decoration: underline;">'.$page->title.'</h1>';
$out .= '<h3 style="text-align: center;">'.$page->summary.'</h3>';

$out .= '<div class="copybook">';
$out .= $page->body;
$out .= '</div>';

echo $out;
?>
