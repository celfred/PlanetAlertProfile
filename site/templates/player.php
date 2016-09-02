<?php 
/* Player template */

// Redirect to player_details
if ($page->playerTeam) {
  $team = $page->playerTeam;
} else {
  $team = 'no-team';
}
$session->redirect($pages->get('/players')->url.$sanitizer->name($team).'/'.$page->name);

?>
