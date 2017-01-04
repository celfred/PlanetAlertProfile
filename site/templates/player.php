<?php 
/* Player template */

$pdf = $input->get("pages2pdf");

if ($pdf != "1") {
  // Redirect to player_details
  if ($page->team) {
    $team = $page->team->name;
  } else {
    $team = 'no-team';
  }
  $session->redirect($pages->get('/players')->url.$sanitizer->name($team).'/'.$page->name);
}

?>
