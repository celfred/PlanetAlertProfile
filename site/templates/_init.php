<?php

  include('./my-functions.inc'); // Planet Alert PHP functions

  $homepage = $pages->get("/"); 
  $loginUrl = $pages->get("name=loginform")->url;

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity once a day

?>
