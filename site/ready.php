<?php namespace ProcessWire;

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity
  $wire->addHook('LazyCron::everyDay', null, 'randomSpecial'); // Set random special monsters
  $wire->addHook('LazyCron::everyDay', null, 'emptyTmp'); // Empty /tmp subtree every night
  $wire->addHook('LazyCron::everyDay', null, 'cleanTest'); // Init test-team players
  $wire->addHook('LazyCron::everyDay', null, 'clearAdminTableCache');

  // Clean caches
  /* $wire->addHookAfter('Pages::saved', function($e) use($cache) { */
  /*   $page = $e->arguments(0); */
  /*   if ($page->is("template=team")) { */
  /*     $cache->delete('cache__freeworld-'.$page->name); */
  /*     $headTeacher = $page->teacher->eq(0); */
  /*     $cache->delete('cache__scores-'.$headTeacher->name); */
  /*   } */
  /* }); */
  // the next ensures that the following code will only run on front end (otherwise back end would get cached, too which results in problems)
  // make sure to place anything you need in the backend before this line or change it to your needs..
  /* if ((strpos($page->url, wire('config')->urls->admin) !== false) || ($page->id && $page->is('parent|has_parent=2'))) return; */

  // Manage redirections (restrictions in case of hard coded urls to try and access another user's page
  $homepage = $pages->get("/");
  /* wire()->addHookBefore("Page::render", function($event) use($homepage, $session, $pages, $user, $input, $page){ */
    /* switch($page->name) { */
      /* case 'main-office' : */
        /* if (($user->hasRole('player') && $input->urlSegment2 != 'player') || ($user->isGuest() && $input->urlSegment2 != 'player')) { */
          // TODO ? Test if player is on his or her own team's office ?
          /* $session->redirect($homepage->url); */
        /* } */
        /* break; */
      /* case 'newsboard' : */
        /* if (($user->hasRole('player') && $input->urlSegment1 != $user->name) || ($user->isGuest() && $input->urlSegment2 != '')) { */
        /*   $session->redirect($homepage->url); */
        /* } */
        /* break; */
      /* default: return; */
    /* } */
  /* }); */

  /* $wire->addHookBefore('Page::render', function($event) use($session, $user, $page, $input) { */
  /*   if ($user->isLoggedin()) { */
  /*     if ($page->name == 'newsboard' && $input->urlSegment1 != $user->name ) { */
  /*       $session->redirect($page->url.$user->name); */
  /*     } */
  /*   } else { // Guest pages */
  /*     if ($page->name == 'newsboard' && $input->urlSegment1 != '') { // Guest tries to see somebody's Newsboard */
  /*       $session->redirect($page->url); */
  /*     } */
  /*   } */
  /* }); */

  /* $session->alert = "<div class='alert alert-success closable expire'>All caches have been deleted. <i class='fa fa-close'></i></div>"; */

  /* if (!$user->isSuperuser() && $user->isLoggedin() && $page->template == 'shop' && !$user->isSuperuser()) { // Cache headTeacher shop */
    /* if ($user->hasRole('teacher')) { */
    /*   $cacheName = "cache__{$page->id}-{$page->template}-{$user->name}-{$user->language->name}"; */
    /* } else if ($user->hasRole('player')) { */
    /*   $playerPage = $pages->get("parent.name=players, login=$user->name"); */ 
    /*   $headTeacher = $playerPage->team->teacher->first(); */
    /*   $cacheName = "cache__{$page->id}-{$page->template}-{$headTeacher->name}-{$headTeacher->language->name}"; */
    /* } else { // Guest */
    /*   $cacheName = "cache__{$page->id}-{$page->template}-guest"; */
    /* } */

  // TODO : Test only markupCache (remove backend templateCache) ?
  
    /* bd($input->urlSegmentStr); */
    /* if ($input->urlSegmentStr) { $cacheName .= "-{$input->urlSegmentStr}"; } */
  /*   if ($input->urlSegment1) $cacheName .= "-{$input->urlSegment1}"; */
  /*   if ($input->urlSegment2) $cacheName .= "-{$input->urlSegment2}"; */
  /*   if ($input->urlSegment3) $cacheName .= "-{$input->urlSegment3}"; */
    /* if ($input->pageNum > 1) $cacheName .= "-page{$input->pageNum}"; */

    // if already cached exit here printing cached content (only 1 db query)
    /* $wire->addHookBefore('Page::render', function() use($cache, $cacheName) { */
      /* $cached = $cache->get($cacheName); */
      /* if ($cached) { */
      /*   exit($cached); */
      /* } */
    /* }); */

    // not cached so far, continue as usual but save generated content to cache
    /* $wire->addHookAfter('Page::render', function($event) use($cache, $cacheName, $config, $files) { */
    /*   $cached = $config->paths->cache.$cacheName; */
    /*   /1* $cached = $cache->get($cacheName); *1/ */
    /*   bd($cached); */
    /*   /1* if (!$cached) { *1/ */
    /*   if (!file_exists($cached)) { */
    /*     /1* $cache->save($cacheName, $event->return); *1/ */
    /*     $files->filePutContents($cached, $event->return); */ 
    /*   } */
    /*   unset($cached); */
    /* }); */
  /* } */

?>
