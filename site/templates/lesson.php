<?php namespace ProcessWire; /* lesson template */
  include("./head.inc");

  $out = '';

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser()) { // Test player login
    // Test if player has unlocked Book of Knowledge 
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $player->team->forceKnowledge == 1) {
      $access = $pages->get("name=knowledge");
    } else {
      $access = $player->equipment->get('knowledge');
    }
    if ($access) {
      $out .= '<div class="text-center">';
        $task = $page->task;
        $out .= '<span>Possible credit : '.$task->title.'</span>';
        // TODO : Calculate task delta according to player's equipment ?
        $out .= ' <span class="label label-default">+'.$task->XP.' XP</span>';
        $out .= ' <span class="label label-default">+'.$task->GC.' GC</span>';
        $out .= "<p>Copy in your copybook and show it in class to your teacher. You will get the credit if you don't make any spelling mistakes, if you write as best as you can and if you <u>underline</u> the title !</p>";
      $out .= '</div>';

      $out .= '<section class="copybook">';
        $out .= '<h1 class="text-center">'.$page->title.'</h1>';
        $out .= $page->body;
      $out .= '</section>';

      if (!$user->isSuperuser()) {
        $out .= '<p class="text-center"><button class="btn btn-primary" id="copied" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask" data-taskId="'.$task->id.'" data-lessonId="'.$page->id.'" data-playerId="'.$player->id.'">Copied in my copybook ! (Warn the teacher)</button></p>';
      } else {
        $out .= '<p class="text-center"><button class="btn btn-primary" id="copied" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask" data-taskId="'.$task->id.'" data-lessonId="'.$page->id.'" data-playerId="0">Copied in my copybook ! (Warn the teacher)</button></p>';
      }
    }
  } else {
    $out .= '<p class="alert alert-warning">Sorry, but you don\'t have access to this page. Contact the administrator if yoy think this is an error.</p> ';
  }
  //
  echo $out;

  include("./foot.inc");
?>
