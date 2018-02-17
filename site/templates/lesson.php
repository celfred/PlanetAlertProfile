<?php namespace ProcessWire; /* lesson template */
  include("./head.inc");

  $out = '';

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser()) { // Test player login
    // Test if player has unlocked Book of Knowledge 
    // or if admin has forced it in Team options
    if ($user->isSuperuser() || $player->team->forceKnowledge == 1) {
      $access = $pages->get("name=knowledge");
      $player = $pages->get("template=player, name=test");
    } else {
      $access = $player->equipment->get('knowledge');
      setDelta($player, $task);
    }
    if ($access) {
      $out .= '<div class="text-center">';
        $task = $page->task;
        $out .= '<span>Possible credit : '.$task->title.'</span>';
        // Calculate possible credit according to player's equipment
        $out .= ' <span class="label label-default">+'.$task->XP+$player->deltaXP.' XP</span>';
        $out .= ' <span class="label label-default">+'.$task->GC.' GC</span>';
        $out .= "<p>Copy in your copybook and show it in class to your teacher. You will get the credit if you don't make any spelling mistakes, if you write as best as you can and if you <u>underline</u> the title !</p>";
      $out .= '</div>';

      $out .= '<section class="copybook">';
        $out .= '<h1 class="text-center">'.$page->title.'</h1>';
        $out .= $page->body;
      $out .= '</section>';

      // 1 pending lesson at a time allowed for a player
      $already = $pages->get("name=book-knowledge, pendingLessons.player=$player");
      if (!$already || !$already->isTrash()) {
        $out .= '<p class="text-center"><button class="btn btn-primary btn-block" id="copied" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask" data-taskId="'.$task->id.'" data-lessonId="'.$page->id.'" data-playerId="0">✓ Copied in my copybook ! (Warn the teacher)</button></p>';
      } else {
        $out .= '<p class="text-center warning">Good job ! You jave already asked to validate  a copied lesson. You have to wait for the validation before asking for another one !</p>';
      }
    }
  } else {
    $out .= '<p class="alert alert-warning">Sorry, but you don\'t have access to this page. Contact the administrator if yoy think this is an error.</p> ';
  }
  //
  echo $out;

  include("./foot.inc");
?>
