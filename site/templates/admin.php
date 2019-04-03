<?php namespace ProcessWire;

  $wire->addHookAfter('InputfieldPage::getSelectablePages', function(HookEvent $e) {
    $user = wire('user');
    $pages = wire('pages');
    if($e->object->name == 'group') {
      if ($user->isSuperuser()) {
        $selector = "template=group, sort=title";
      } else {
        $selector = "template=group, created_users_id=$user->id, sort=title";
      }
      $e->return = $e->pages->find($selector);
    }
  });
// set SelectablePages for page field "group" in case if user is superuser
/* wire()->addHookAfter('InputfieldPage::getSelectablePages', function(HookEvent $e){ */ 	
/*   // here we check desired page field only */
/*   if($e->object->name == 'group'){ */
/*     if( wire('user')->isSuperuser() ){ */
/*       $e->return = wire('pages')->find('template=group, sort=title'); */
/*     } */
/*   } */
/* }); */

/**
 * Admin template just loads the admin application controller, 
 * and admin is just an application built on top of ProcessWire. 
 *
 * This demonstrates how you can use ProcessWire as a front-end to another application. 
 *
 * Leave this file as-is, do not remove. 
 * 
 */

require($config->paths->adminTemplates . 'controller.php'); 
