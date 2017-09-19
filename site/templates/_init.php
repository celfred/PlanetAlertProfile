<?php

  $homepage = $pages->get("/"); 
  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity once a day

?>
