<?php

  include('./my-functions.inc'); // Planet Alert PHP functions

  $homepage = $pages->get("/"); 
  $loginUrl = $pages->get("name=loginform")->url;
  $currentPeriod = $pages->get("name=admin-actions")->periods;

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity once a day

?>
