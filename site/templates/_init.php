<?php

  include('./my-functions.inc'); // Planet Alert PHP functions

  $homepage = $pages->get("/"); 

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity once a day

?>
