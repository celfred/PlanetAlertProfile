<?php namespace ProcessWire;
/** All Tasks template */

include("./head.inc"); 

$out = '';
?>

<div>
  <?php
    if ($user->hasRole('teacher') || $user->isSuperuser()) {
      if ($user->isSuperuser()) {
        $allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, sort='title'");
        $headTeacher = false;
      } else {
        $allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, (owner.singleTeacher=$user), (adminOnly=1), sort=title");
        $headTeacher = $user;
      }
      $allCategories = new PageArray();
      foreach ($allTasks as $task) {
        $allCategories->add($task->category);
        $allCategories->sort("title");
      }

      echo '<a class="pdfLink btn btn-sm btn-info" href="'.$page->url.'?pages2pdf=1">'.__("Get PDF").'</a>';
      echo '<br /><br />';

    } else {
      if ($user->hasRole('player')) { // Limit to main teacher's tasks
        $allTasks = $pages->find("template=task, (owner.singleTeacher=$teacher), (adminOnly=1), name!=manual|free|buy, sort=title");
        $headTeacher = getHeadTeacher($user);
        $allCategories = new PageArray();
        foreach ($allTasks as $task) {
          $allCategories->add($task->category);
          $allCategories->sort("title");
        }
      } else { // Public actions
        $allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, sort='title'");
        $allCategories = new PageArray();
        $headTeacher = false;
        foreach ($allTasks as $task) {
          $allCategories->add($task->category);
          $allCategories->sort("title");
        }
      }
    }
  ?>

  <div id="Filters" data-fcolindex="5" class="text-center">
    <ul class="list-inline well">
      <?php foreach ($allCategories as $category) { ?>
      <li><label for="<?php echo $category->name; ?>" class="btn btn-primary btn-xs"><?php echo $category->title; ?> <input type="checkbox" value="<?php echo $category->title; ?>" class="categoryFilter" name="categoryFilter" id="<?php echo $category->name; ?>"></label></li>
      <?php } ?> 
    </ul>
  </div>

  <table id="taskTable" class="table table-hover table-condensed">
    <thead>
    <tr>
      <th><?php echo __('Name'); ?></th>
      <th><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> <?php echo __('HP'); ?></th>
      <th><img src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> <?php echo __('XP'); ?></th>
      <th><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="Gold Coins (GC)" width="20" height="20" /> <?php echo __('GC'); ?></th>
      <th><span class="glyphicon glyphicon-plus"></span> / <span class="glyphicon glyphicon-minus"></span></th>
      <th><?php echo __('Category'); ?></th>
    </tr>
    </thead>
    <tbody>
      <?php foreach ($allTasks as $task) {
        if ($headTeacher) {
          $task = checkModTask($task); // Get personalized values according to logged in user
        }
        if ($task->HP < 0 || $task->XP < 0 || $task->GC < 0) {
          $task->type = 'negative';
        } else { 
          $task->type = 'positive';
        }
      ?>
        <tr class="<?php echo $task->type; ?>">
          <td><a data-toggle="tooltip" data-html="true" title="<?php echo $task->summary; ?>" href="<?php echo $task->url; ?>"><?php echo $task->title; ?></a></td>
          <td><?php echo $task->HP; ?></td>
          <td><?php echo $task->XP; ?></td>
          <td><?php echo $task->GC; ?></td>
          <td><?php echo $task->type; ?></td>
          <td><?php echo $task->category->title; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php
  include("./foot.inc"); 
?>
