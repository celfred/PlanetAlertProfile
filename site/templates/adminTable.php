<?php namespace ProcessWire;
/* adminTable template */

include("./head.inc"); 

// Nav tabs
$team = $pages->get("template=team, name=$input->urlSegment1");;
include("./tabList.inc"); 

$team = $input->urlSegment1;
$allCategories = $pages->get('/categories/')->children("name!=potions|place|shop|protections|weapons|manual-cat|planet-alert|group-items");
$allTasks = $pages->get('/tasks/')->children();
$allPlayers = $pages->find("template='player', team.name=$team, sort='title'");

?>

<form id="adminTableForm" name="adminTableForm" action="<?php echo $pages->get('name=submitforms')->url; ?>" method="post" class="" role="form">

<ul class="list-inline text-center">
  <ul class="list-inline">
    <li class="btn btn-info"><a href="#" class="toggle-vis" data-category="">All categories</a></li>
    <?php foreach ($allCategories as $cat) { ?>
      <li class="btn btn-info"><a href="#" class="toggle-vis" data-category="<?php echo $cat->name; ?>"><?php echo $cat->title; ?></a></li>
    <?php } ?>
  </ul>
</ul>

<input type="hidden" name="adminTableSubmit" value="Save" />
<input type="submit" name="adminTableSubmit" value="Save" class="btn btn-block btn-primary" disabled="disabled" />

<table id="adminTable" class="adminTable">
  <thead>
  <tr>
    <th>Groups</th>
    <th>Players</th>
    <?php foreach ($allTasks as $task) { ?>
    <th class="task" id="th_<?php echo $task->id; ?>" data-category="<?php echo $task->category->name; ?>" data-order="<?php echo $task->name; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $task->summary; ?>" data-keepVisible="">
      <div class="vertical-text">
        <div class="vertical-text__inner">
          <?php echo $task->title; ?>
        </div>
      </div>
    </th>
    <?php } ?>
  </tr>
  <tr>
    <td colspan="2">Display comments</td>
    <?php foreach ($allTasks as $task) { ?>
    <td data-toggle="tooltip" title="Display comments">
    <input type="checkbox" id="cc_<?php echo $task->id; ?>" class="commonComment" onclick="showComment(<?php echo $task->id; ?>)" />
    <input style="display: none;" type="text" id="commonComment_<?php echo $task->id; ?>" name="commonComment[<?php echo $task->id; ?>]" value="" placeholder="Common comment" onKeyUp="setCommonComment(<?php echo $task->id; ?>, $(this))" />
    </td>
    <?php } ?>
  </tr>
  <tr>
    <td colspan="2">Select all</td>
    <?php foreach ($allTasks as $task) { ?>
      <td data-toggle="tooltip" title="Select all"><input type="checkbox" id="csat_<?php echo $task->id; ?>" class="selectAll" onclick="selectAll(<?php echo $task->id; ?>)" /></td>
    <?php } ?>
  </tr>
  </thead>
  <tbody>
  <?php
    $today = mktime(0,0,0, date("m"), date("d"), date("Y"));
    foreach ($allPlayers as $player) { 
      $id = $player->id; 
      // See if absence already recorded the same day
      $abs = $player->get("name=history")->children()->get("template=event, task.name=absent, date>=$today");
  ?>
  <tr class="<?php if ($abs->id) { echo 'negative'; } ?>">
  <td><?php if (isset($player->group->id)) { echo $player->group->title; } ?></td>
  <th><?php echo $player->title; ?></th>
    <?php foreach ($allTasks as $task) {
      if ($task->HP < 0) { $type = 'negative'; } else { $type=''; }
      $taskId = $task->id;
    ?>
    <td class="<?php echo $type; ?>" data-toggle="tooltip" title="<?php echo $player->title.' - '.$task->title; ?>">
    <input type="checkbox" class="ctPlayer ct_<?php echo $taskId; ?>" id="" data-customId="<?php echo $id.'_'.$taskId; ?>" name="player[<?php echo $id.'_'.$taskId; ?>]" onChange="onCheck(<?php echo $taskId; ?>)" />
    <input style="display: none;" type="text" data-customId="<?php echo $id.'_'.$taskId; ?>" class="cc_<?php echo $taskId; ?>" name="comment_<?php echo $id.'_'.$taskId; ?>" value="" placeholder="Comment" />
    </td>
    <?php } ?>
  </tr>
  <?php } ?>
  </tbody>
</table>
<input type="submit" name="adminTableSubmit" value="Save" class="btn btn-block btn-primary" disabled="disabled" />
</form>

<?php
  include("./foot.inc"); 
?>
