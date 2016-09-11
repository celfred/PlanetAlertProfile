<?php 
/* Player template */

// Redirect to player_details
$session->redirect($pages->get('/players')->url.$sanitizer->name($page->team->name).'/'.$page->name);

?>
