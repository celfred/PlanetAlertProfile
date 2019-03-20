<?php namespace ProcessWire;

  $wire->addHook('LazyCron::everyDay', null, 'checkActivity'); // Check all players activity
  $wire->addHook('LazyCron::everyDay', null, 'randomSpecial'); // Set random special monsters
  $wire->addHook('LazyCron::everyDay', null, 'emptyTmp'); // Empty /tmp subtree every night
  $wire->addHook('LazyCron::everyDay', null, 'cleanTest'); // Init test-team players
  $wire->addHook('LazyCron::everyDay', null, 'clearAdminTableCache');

  // Manage redirections (restrictions in case of hard coded urls to try and access another user's page
  $homepage = $pages->get("/");
  wire()->addHookBefore("Page::render", function($event) use($homepage, $session, $pages, $user, $input, $page){
    switch($page->name) {
      case 'main-office' :
        if (($user->hasRole('player') && $input->urlSegment2 != 'player') || ($user->isGuest() && $input->urlSegment2 != 'player')) {
          // TODO ? Test if player is on his or her own team's office ?
          $session->redirect($homepage->url);
        }
        break;
      case 'newsboard' :
        if (($user->hasRole('player') && $input->urlSegment1 != $user->name) || ($user->isGuest() && $input->urlSegment2 != '')) {
          $session->redirect($homepage->url);
        }
        break;
      default: return;
    }
  });

  // the next ensures that the following code will only run on front end (otherwise back end would get cached, too which results in problems)
  // make sure to place anything you need in the backend before this line or change it to your needs..
  /* if ((strpos($page->url, wire('config')->urls->admin) !== false) || ($page->id && $page->is('parent|has_parent=2'))) return; */

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
  /*   if ($user->hasRole('teacher')) { */
  /*     $cacheName = "cache__{$page->id}-{$page->template}-{$user->name}-{$user->language->name}"; */
  /*   } else { */
  /*     $playerPage = $pages->get("parent.name=players, login=$user->name"); */ 
  /*     $headTeacher = $playerPage->team->teacher->first(); */
  /*     $cacheName = "cache__{$page->id}-{$page->template}-{$headTeacher->name}-{$headTeacher->language->name}"; */
  /*   } */

  /*   if ($input->urlSegment1) $cacheName .= "-{$input->urlSegment1}"; */
  /*   if ($input->urlSegment2) $cacheName .= "-{$input->urlSegment2}"; */
  /*   if ($input->urlSegment3) $cacheName .= "-{$input->urlSegment3}"; */
  /*   if ($input->pageNum > 1) $cacheName .= "-page{$input->pageNum}"; */

  /*   // if already cached exit here printing cached content (only 1 db query) */
  /*   $wire->addHookBefore('Page::render', function() use($cache, $cacheName) { */
  /*     $cached = $cache->get($cacheName); */
  /*     if ($cached) { */
  /*       exit($cached); */
  /*     } */
  /*   }); */

  /*   // not cached so far, continue as usual but save generated content to cache */
  /*   $wire->addHookAfter('Page::render', function($event) use($cache, $cacheName) { */
  /*     $cached = $cache->get($cacheName); */
  /*     if (!$cached) { */
  /*       $cache->save($cacheName, $event->return); */
  /*     } */
  /*     unset($cached); */
  /*   }); */
  /* } */

?>
