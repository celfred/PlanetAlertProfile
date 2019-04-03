<?php namespace ProcessWire;
  include("./head.inc"); 

  // Nav tabs
  $team = $selectedTeam;
  if ($user->isSuperuser()) {
    $headTeacher = $team->teacher->first();
  }
  include("./tabList.inc"); 

  $today = mktime(0,0,0, date("m"), date("d"), date("Y"));

  if ($user->hasRole('teacher') || $user->isSuperuser()) {
    $allCategories = $pages->find("parent.name=categories, template=category, adminOnly=1,sort=name");
    if ($user->isSuperuser()) {
      // Limit to selected team headTeacher
      if ($team->name == 'no-team') {
        $allTasks = $pages->find("template=task, adminOnly=0");
        $allPlayers = getAllPlayers($user, true);
        $pagination = $allPlayers->renderPager();
      } else {
        $allTasks = $pages->find("parent.name=tasks, owner.singleTeacher=$headTeacher")->sort("category.name, HP, XP");
        $allPlayers = getAllPlayers($user, false);
      }
    } else { // Limit to logged in teacher
      $allTasks = $pages->find("parent.name=tasks, owner.singleTeacher=$user")->sort("category.name, HP, XP");
      $allPlayers->filter("team=$team, team.teacher=$user");
    }
    // helpAlert for players having a high hccount
    /* if ($user->name == 'flieutaud' || $user->isSuperuser()) { */
    /*   $dangerPlayers = $allPlayers->find("hkcount>=1.5,sort=title"); */
    /*   if (count($dangerPlayers) > 0) { */
    /*     $players = $dangerPlayers->implode(', ', '{title}'); */
    /*     $helpAlert = true; */
    /*     $helpTitle =  '<span class="glyphicon glyphicon-warning-sign"></span> '; */
    /*     $helpTitle .= sprintf(__("Watch out for %s !"), $players); */
    /*   } */
    /*   include("./helpAlert.inc.php"); */
    /* } */
  }

    if ($allTasks->count() > 0) {
      if (isset($pagination)) { echo $pagination;}
  ?>


    <ul class="list-inline text-center">
      <ul class="list-inline">
        <li><button class="btn btn-primary toggle-vis" data-category=""><?php echo __('All categories'); ?></button></li>
        <?php
          foreach ($allCategories as $cat) {
            echo '<li><button class="btn btn-primary toggle-vis" data-category="'.$cat->name.'">'.$cat->title.'</button></li>';
          }
        ?>
      </ul>
    </ul>

    <form id="adminTableForm" name="adminTableForm" action="<?php echo $pages->get('name=submitforms')->url; ?>" method="post" class="" role="form">

    <input type="hidden" name="adminTableSubmit" value="Save" />
    <input type="submit" name="adminTableSubmit" value="<?php echo __("Save"); ?>" class="btn btn-block btn-primary" disabled="disabled" />

    <table id="adminTable" class="adminTable">
      <thead>
      <tr class="dark">
        <th><?php echo __('Groups'); ?></th>
        <th><?php echo __('Players'); ?></th>
        <?php foreach ($allTasks as $task) { 
          $task = checkModTask($task, $headTeacher);
        ?>
        <th class="task" id="th_<?php echo $task->id; ?>" data-category="<?php echo $task->category->name; ?>" data-order="<?php echo $task->name; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $task->summary; ?>" data-keepVisible="">
          <div class="vertical-text">
            <div class="vertical-text__inner">
              <?php if ($task->teacherTitle != '') { $task->title = $task->teacherTitle; }
                echo $task->title;
              ?>
            </div>
          </div>
        </th>
        <?php } ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo __("Display comments"); ?></td>
        <?php foreach ($allTasks as $task) { ?>
        <td data-toggle="tooltip" title="<?php echo __("Display comments"); ?>">
        <input type="checkbox" id="cc_<?php echo $task->id; ?>" class="commonComment" onclick="showComment(<?php echo $task->id; ?>)" />
        <input style="display: none;" type="text" id="commonComment_<?php echo $task->id; ?>" name="commonComment[<?php echo $task->id; ?>]" value="" placeholder="<?php echo __("Common comment"); ?>" onKeyUp="setCommonComment(<?php echo $task->id; ?>, $(this))" />
        </td>
        <?php } ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo __("Select all"); ?></td>
        <?php foreach ($allTasks as $task) { ?>
          <td data-toggle="tooltip" title="<?php echo __("Select all"); ?>"><input type="checkbox" id="csat_<?php echo $task->id; ?>" class="selectAll" onclick="selectAll(<?php echo $task->id; ?>)" /></td>
        <?php } ?>
      </tr>
      </thead>
      <tbody>
      <?php
        foreach ($allPlayers as $player) { 
          $id = $player->id; 
          // See if absence already recorded (last event)
          $abs = $player->get("name=history")->children("date>$today")->get("task.name=abs|absent");
      ?>
      <tr class="<?php if ($abs) { $disabled = 'disabled'; echo 'negative'; } else { $disabled = ''; } ?>">
      <td><?php if (isset($player->group->id)) { echo $player->group->title; } ?></td>
      <td class="dark"><?php echo $player->title; ?></td>
        <?php foreach ($allTasks as $task) {
          if ($task->HP < 0) { $type = 'negative'; } else { $type=''; }
          $taskId = $task->id;
        ?>
        <td class="<?php echo $type; ?>" data-toggle="tooltip" title="<?php echo $player->title.' - '.$task->title; ?>">
          <input type="checkbox" <?php echo $disabled; ?> class="ctPlayer ct_<?php echo $taskId; ?>" id="" data-customId="<?php echo $id.'_'.$taskId; ?>" name="player[<?php echo $id.'_'.$taskId; ?>]" onChange="onCheck(<?php echo $taskId; ?>)" />
          <input style="display: none;" <?php echo $disabled; ?> type="text" data-customId="<?php echo $id.'_'.$taskId; ?>" class="cc_<?php echo $taskId; ?>" name="comment_<?php echo $id.'_'.$taskId; ?>" value="" placeholder="<?php echo __("comment"); ?>" />
          <?php 
            if ($abs && $task->is("name=absent|abs")) { 
              echo "<a href='#' class='removeAbs' data-type='removeAbs' data-url='".$pages->get('name=submitforms')->url."?form=deleteForm&eventId=".$abs->id."'>[✗]</a>"; 
            } else {
              if ($disabled == 'disabled') { echo "<a href='#' class='toggleEnabled'>[◑]</a>"; } 
            }
          ?>
        </td>
        <?php } ?>
      </tr>
      <?php } ?>
      </tbody>
    </table>
    <input type="submit" name="adminTableSubmit" value="<?php echo __("Save"); ?>" class="btn btn-block btn-primary" disabled="disabled" />
    <fieldset>
      <?php
        $today = date('Y-m-d');
        echo '<legend><span class="glyphicon glyphicon-warning-sign"></span> '.__("More options").'</legend>';
        echo '<label for="customDate">'.__("Custom date").'</label>';
        echo '<input id="customDate" name="customDate" type="date" size="8" max="'.$today.'" value="'.$today.'" />';
        echo '&nbsp;&nbsp;';
        echo '<input type="checkbox" id="adminTableRedirection" name="adminTableRedirection" />';
        echo '&nbsp;';
        echo '<label for="adminTableRedirection">'.__("Stay on adminTable after saving").'</label>';
      ?>
    </fieldset>
    </form>

  <?php
      if (isset($pagination)) { echo $pagination;}
    } else { // No tasks
      echo '<p class="">'.__("You have no tasks set yet.").'</p>';
    }

  include("./foot.inc"); 
?>
