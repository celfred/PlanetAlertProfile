<?php namespace ProcessWire;
  include_once('./my-functions.inc'); // Planet Alert PHP functions

  $homepage = $pages->get("/"); 
  $loginUrl = $pages->get("name=loginform")->url;
  $french = $languages->get('french');
  $noAuthMessage = '<p class="alert alert-warning">'.__('Sorry, but you don\'t have access to this page. If you think this is an error, please contact the administrator.').'</p>';
  $ieAlert = '<p class="alert alert-warning">'.__("You are using Internet Explorer. Planet Alert is tested only with Firefox for the moment. Please, think of changing browser for a better user experience.").'</p>';
  $wrongBrowserMessage = '<p class="alert alert-warning">'.sprintf(__("Sorry, but this page (%d) is not accessible with your browser. Planet Alert is tested only with Firefox for the moment. If you think this is an error, please contact the administrator."), $page->name).'</p>';

  /* $wire->addHookAfter('Pages::saved', function(HookEvent $event) { // Actions after saving page */
  /*   $page = $event->arguments("page"); */
  /*   $user = wire('user'); */
  /*   if ($page->template == 'exercise') { // Create teacher in exerciseOwner repeater */
  /*     $already = $page->exerciseOwner->get("singleTeacher=$user"); */
  /*     if (isset($already)) return; */
  /*     $new = $page->exerciseOwner->getNew(); */
  /*     $new->singleTeacher = $user; */
  /*     $new->save(); */
  /*     $page->exerciseOwner->add($new); */
  /*     $page->of(false); */
  /*     $page->save(); */
  /*   } */
  /* }); */
?>
