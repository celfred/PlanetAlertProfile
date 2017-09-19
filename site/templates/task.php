<?php 
/** Task template */

include("./head.inc"); 

$allTasks = $pages->get("/tasks/")->find("template=task, name!=manual|free|buy, sort='title'");
$allCategories = new PageArray();
foreach ($allTasks as $task) {
  $allCategories->add($task->category);
  $allCategories->sort("title");
}

// All tasks catalogue
$out = '';
if ($page->name == 'tasks') {
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
} else { // Task details
  $sign = '';
  $out .= '<div class="well">';
  $out .= '<span class="badge badge-default">'.$page->category->title.'</span>&nbsp;';
  $out .= '<br />';
  $out .= '<br />';
  $out .= '<h2 class="inline"><strong>'.$page->title.'</strong>&nbsp;&nbsp;';
  if ( $page->XP != 0) {
    if ($page->XP > 0) { $sign = '+'; }
    $page->GC > 0 ? $type = 'success' : $type = 'danger';
    $out .= '<span class="label label-'.$type.'">XP : '.$sign.$page->XP.'</span>&nbsp;';
  }
  if ( $page->HP != 0) {
    if ($page->HP > 0) { $sign = '+'; }
    $page->GC > 0 ? $type = 'success' : $type = 'danger';
    $out .= '<span class="label label-'.$type.'">HP : '.$sign.$page->HP.'</span>&nbsp;';
  }
  if ( $page->GC != 0) {
    if ($page->GC > 0) { $sign = '+'; }
    $page->GC > 0 ? $type = 'success' : $type = 'danger';
    $out .= '<span class="label label-'.$type.'">GC : '.$sign.$page->GC.'</span>&nbsp;';
  }
  $out .= '</h2>';
  if ( $page->GC != 0 || $page->HP != 0 || $page->XP != 0) {
    $out .= '<span>(Depending on your equipment!)</span>';
  }
  $out .= '<h2 class="">'.$page->summary;
  $out .= '</h2>';
  $out .= '<br />';
  $out .= '<a role="button" class="" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">[French version]</a>';
  $out .= '<div class="collapse" id="collapseDiv"><div class="well">';
  if ($page->frenchSummary != '') {
    $out .= $page->frenchSummary;
  } else {
    $out .= 'French version in preparation, sorry ;)';
  }
  $out .= '</div></div>';
  $out .= '</div>';

  $out .= '<a class="btn btn-block btn-primary" href="'.$pages->get('name=tasks')->url.'">Back to the Actions list.</a>';
  echo $out;
}
?>

<?php
  include("./foot.inc"); 
?>
