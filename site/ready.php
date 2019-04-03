<?php namespace ProcessWire;

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity
  $wire->addHook('LazyCron::everyDay', null, 'randomSpecial'); // Set random special monsters
  $wire->addHook('LazyCron::everyDay', null, 'emptyTmp'); // Empty /tmp subtree every night
  $wire->addHook('LazyCron::everyDay', null, 'cleanTest'); // Init test-team players
  $wire->addHook('LazyCron::everyDay', null, 'clearAdminTableCache');

  // Clean caches
  $wire->addHookAfter('Pages::saved', function($e) use($cache) {
    $page = $e->arguments(0);
    if ($page->is("template=equipment|item")) {
      $cache->delete('cache__-'.$page->name.'-*');
    }
    if ($page->is("template=category")) {
      $cache->delete('cache__-allTrainCategories');
    }
    if ($page->is("template=place|city|country")) {
      $cache->delete('cache__-placesMenu');
      $cache->delete('cache__-allPlacesGallery-page*');
    }
  });

?>
