<?php namespace ProcessWire;
  include('./my-functions.inc'); // Planet Alert PHP functions

  $homepage = $pages->get("/"); 
  $loginUrl = $pages->get("name=loginform")->url;
  $french = $languages->get('french');
  $noAuthMessage = '<p class="alert alert-warning">'.sprintf(__("Sorry, but you don't have access to this page (%d). If you think this is an error. Please, contact the administrator."), $page->name).'</p>';

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity
  $wire->addHook('LazyCron::everyDay', null, 'randomSpecial'); // Set random special monsters
  $wire->addHook('LazyCron::everyDay', null, 'emptyTmp'); // Empty /tmp subtree every night
?>
