<?php 
/* Player template */

// Redirect to player_details
$team = $page->playerTeam;
$session->redirect($pages->get('/players')->url.$sanitizer->name($team).'/'.$page->name);

?>
