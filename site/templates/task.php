<?php 
/** Task template */

include("./head.inc"); 

$allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, sort='title'");
$allCategories = new PageArray();
foreach ($allTasks as $task) {
  $allCategories->add($task->category);
  $allCategories->sort("title");
}
?>

<div>
  
  <a class="pdfLink btn btn-info" href="<?php echo $page->url; ?>?pages2pdf=1">Get PDF</a>
  <br /><br /><br />

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
      <th>Name</th>
      <th><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> HP</th>
      <th><img src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> XP</th>
      <th><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="Gold Coins (GC)" width="20" height="20" /> GC</th>
      <th><span class="glyphicon glyphicon-plus"></span> / <span class="glyphicon glyphicon-minus"></span></th>
      <th>Category</th>
    </tr>
    </thead>
    <tbody>
      <?php foreach ($allTasks as $task) {
        if ($task->HP < 0) {
          $task->type = 'negative';
        } else { 
          $task->type = 'positive';
        }
      ?>
        <tr class="<?php echo $task->type; ?>">
          <td><span><?php echo $task->title; ?></span></td>
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
