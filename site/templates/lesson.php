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
    }
    if ($access) {
      $out .= '<div class="text-center">';
        $task = $page->task;
        // Calculate possible credit according to player's equipment
        setDelta($player, $task);
        $out .= "<p>Copy in your copybook and show it in class to your teacher to get the credit : ";
        $out .= '<span class="label label-primary">'.$task->title.'</span> → ';
        $out .= ' <span class="label label-default">+'.($task->GC+$player->deltaGC).' GC</span>';
        $out .= ' <span class="label label-default">+'.($task->XP+$player->deltaXP).' XP</span>';
        $out .= "<p>(No spelling mistakes, good writing and title is <u>underlined</u> to get the points !)</p>";
      $out .= '</div>';

      $out .= '<section class="copybook">';
        $out .= '<h1 class="text-center">'.$page->title.'</h1>';
        $out .= $page->body;

        $out .= '<hr />';
        $out .= '<p class="text-center"> Monsters related to this lesson : ';
          foreach ($page->linkedMonsters as $lm) {
            $out .= '<span class="label label-default"><img src="'.$lm->image->getCrop('mini')->url.'" alt="image" /> '.$lm->title.'</span> ';
          }
        $out .= '</p>';
      $out .= '</section>';

      // 1 pending lesson at a time allowed for a player
      $already = $pages->get("name=book-knowledge, pendingLessons.player=$player");
      if (!$already || !$already->isTrash()) {
        $out .= '<p class="text-center"><button class="btn btn-primary" id="copied" data-url="'.$pages->get('name=submitforms')->url.'?form=manualTask" data-taskId="'.$task->id.'" data-lessonId="'.$page->id.'" data-playerId="0">✓ Copied in my copybook ! (Alert the teacher)</button></p>';
      } else {
        $out .= '<p class="text-center warning">Good job ! You jave already asked to validate  a copied lesson. You have to wait for the validation before asking for another one !</p>';
      }

      $buyPdf = $pages->get("name=buy-pdf");
      if ($player->GC > $buyPdf->GC || $user->isSuperuser) {
        $out .= '<div class="text-center">';
        $out .= '<a href="'.$page->url.'?pages2pdf=1" class="btn btn-primary buyPdf" data-url="'.$pages->get("name=submitforms")->url.'?form=buyPdf" data-playerId="'.$player->id.'" data-lessonId="'.$page->id.'">Buy PDF to print ('.abs($buyPdf->GC).'GC)</a>';
        $out .= ' (No XP, no GC gained and you would have <span class="label label-danger">'.($player->GC+$buyPdf->GC).'GC</span> left)</p>';
        $out .= '<p class="text-center feedback"></p>';
        $out .= '</div>';
      }

    }
  } else {
    $out .= '<p class="alert alert-warning">Sorry, but you don\'t have access to this page. Contact the administrator if yoy think this is an error.</p> ';
  }
  //
  echo $out;

  include("./foot.inc");
?>
