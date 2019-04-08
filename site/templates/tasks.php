<?php namespace ProcessWire;

include("./head.inc"); 

$out = '';
?>

<div>
  <?php
    if ($user->hasRole('teacher') || $user->isSuperuser()) {
      echo '<div class="row">';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'.$page->url.'?pages2pdf=1">'.__("Get PDF").'</a>';
      echo '</div>';
    }

    $out = $cache->get('cache__tasks-'.$headTeacher->name, 2678400, function($user, $pages, $config) use($headTeacher) {
      $out = '';
      if ($user->isLoggedin()) {
        if ($user->isSuperuser()) {
          $allTasks = $pages->get("/tasks/")->find("template=task, sort=title");
          $headTeacher = false;
        } else {
          $allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, (owner.singleTeacher=$headTeacher), (adminOnly=1), sort=title");
        }
      } else {
        $allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, sort=title");
      }
      $allCategories = new PageArray();
      foreach ($allTasks as $task) {
        $allCategories->add($task->category);
        $allCategories->sort("title");
      }
      $out .= '<div id="Filters" data-fcolindex="5" class="text-center">';
      $out .= '<ul class="list-inline well">';
      foreach ($allCategories as $category) {
        $out .= '<li><label for="'.$category->name.'" class="btn btn-primary btn-xs">'.$category->title.'<input type="checkbox" value="'.$category->title.'" class="categoryFilter" name="categoryFilter" id="'.$category->name.'"></label></li>';
      }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '<table id="taskTable" class="table table-hover table-condensed">';
        $out .= '<thead>';
          $out .= '<tr>';
          $out .= '<th>'.__('Name').'</th>';
          $out .= '<th><img src="'.$config->urls->templates.'img/heart.png" alt="heart." /> '.__('HP').'</th>';
          $out .= '<th><img src="'.$config->urls->templates.'img/star.png" alt="star." /> '.__('XP').'</th>';
          $out .= '<th><img src="'.$config->urls->templates.'img/gold_mini.png" alt="Gold Coins." width="20" height="20" /> '.__('GC').'</th>';
          $out .= '<th><span class="glyphicon glyphicon-plus"></span> / <span class="glyphicon glyphicon-minus"></span></th>';
          $out .= '<th>'.__('Category').'</th>';
          $out .= '</tr>';
        $out .= '</thead>';
        $out .= '<tbody>';
          foreach ($allTasks as $task) {
            if ($headTeacher) {
              $task = checkModTask($task); // Get personalized values according to logged in user
            }
            if ($task->HP < 0 || $task->XP < 0 || $task->GC < 0) {
              $task->type = 'negative';
            } else { 
              $task->type = 'positive';
            }
            $out .= '<tr class="'.$task->type.'">';
              $out .= '<td><a data-toggle="tooltip" data-html="true" title="'.$task->summary.'" href="'.$task->url.'">'.$task->title.'</a></td>';
              $out .= '<td>'.$task->HP.'</td>';
              $out .= '<td>'.$task->XP.'</td>';
              $out .= '<td>'.$task->GC.'</td>';
              $out .= '<td>'.$task->type.'</td>';
              $out .= '<td>'.$task->category->title.'</td>';
            $out .= '</tr>';
          }
        $out .= '</tbody>';
      $out .= '</table>';
      echo $out;
    });

echo $out;

echo '</div>';

  include("./foot.inc"); 
?>
