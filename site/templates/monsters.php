<?php namespace ProcessWire;
  include("./head.inc"); 

  $out = '';

  if ($user->isGuest()) {
    $allMonsters = $page->children->sort("name");
  } else {
    if ($user->isSuperuser()) {
      $allMonsters = $page->children("include=all")->sort("name");
    }
    if ($user->hasRole('teacher')) {
      $allMonsters = $page->children("(created_users_id=$user->id), (exerciseOwner.singleTeacher=$user, exerciseOwner.publish=1)")->sort("level, name");
    }
    if ($user->hasRole('player')) {
      $allMonsters = $page->children("(created_users_id=$headTeacher->id), (exerciseOwner.singleTeacher=$headTeacher, exerciseOwner.publish=1)")->sort("level, name");
    }
  }

  $allCategories = new PageArray();
  foreach ($allMonsters as $m) {
    if ($m->topic->count() > 0) {
      foreach($m->topic as $t) {
        $allCategories->add($t);
      }
    }
    $allCategories->sort("title");
  }

  if (isset($player) && $user->isLoggedin() || $user->isSuperuser() || $user->hasRole('teacher')) {
    // Test if player has unlocked Memory helmet or Visualizer
    if ($user->isSuperuser() || $user->hasRole('teacher')) {
      $helmet = $pages->get("name=memory-helmet");
      $visualizer = $pages->get("name~=visualizer");
    } else {
      $helmet = $player->equipment->get('memory-helmet');
      $visualizer = $player->equipment->get('name~=visualizer');
    }
    echo '<div class="text-center">';
    echo '<h4>';
    if ($helmet) {
      if ($helmet->image) {
        echo '<img class="" src="'.$helmet->image->getCrop('small')->url.'" alt="Memory helmet." />';
      }
      echo ' <a href="'.$pages->get("name=underground-training")->url.'">'.__("Go to the Underground Training Zone").'</a>   ';
    } else {
      echo '<div class="well text-center">';
      echo __("You must buy the Memory Helmet if you want to do Underground Training.");
      echo '</div>';
    }
    if ($visualizer) {
      if ($visualizer->image) {
        echo '<img class="" src="'.$visualizer->image->getCrop('small')->url.'" alt="Electronic visualizer." />';
      }
      echo ' <a href="'.$pages->get("name=Visualizer")->url.'">'.__("Use the Electronic Visualizer").'</a>   ';
    } else {
      echo '<div class="well text-center">';
      echo __("You must buy the Electronic Visualizer to detect ALL monsters.");
      echo '</div>';
    }
    if ($user->hasRole('teacher') || $user->isSuperuser()) {
      echo '<span class="glyphicon glyphicon-flash"></span> ';
      echo ' <a href="'.$pages->get("name=fighting-zone")->url.'">'.__("Go to the Fighting zone").'</a>   ';
    }
    echo '</h4>';
    echo '</div>';
  } else {
    echo '<h2 class="text-center">'.__("Planet Alert Monsters/Exercises").'</h2>';
  }


  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    $colIndex = 1;
  } else {
    $colIndex = 0;
  }
?>

<div id="Filters" data-fcolindex="<?php echo $colIndex; ?>" class="text-center">
  <ul class="list-inline well">
    <?php foreach ($allCategories as $category) { ?>
    <li><label for="<?php echo $category->name; ?>" class="btn btn-primary btn-xs"><?php echo $category->title; ?> <input type="checkbox" value="<?php echo $category->title; ?>" class="categoryFilter" name="categoryFilter" id="<?php echo $category->name; ?>"></label></li>
    <?php } ?> 
  </ul>
</div>

<div>
<table id="monstersTable" class="table table-condensed table-hover">
  <thead>
    <tr>
    <?php
    if ($user->isSuperuser() || $user->hasRole('teacher')) {
      echo '<th></th>';
    }
    ?>
    <th><?php echo __('Name'); ?></th>
    <th><?php echo __('Level'); ?></th>
    <th style="width:250px"><?php echo __('Summary'); ?></th>
    <th><?php echo __('Most trained player'); ?></th>
    <th><?php echo __('Master time'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach ($allMonsters as $m) {
        $topics = $m->topic->implode(', ', '{title}');
        $out .= '<tr>';
        if ($m->image) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getCrop('thumbnail')->url."\" alt=\"".$m->title.".\" />' src='".$m->image->getCrop('mini')->url."' alt='".$m->title.".' />";
        } else {
          $mini = '';
        }
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $out .= '<td>';
          $out .= '<a class="pdfLink btn btn-info btn-xs" href="'.$page->url.'?id='.$m->id.'&pages2pdf=1">[PDF Fight]</a>';
          $out .= '<a class="pdfLink btn btn-info btn-xs" href="'.$page->url.'?id='.$m->id.'&thumbnail=1&pages2pdf=1">[PDF Image]</a>';
          $out .= '</td>';
        }
        $out .= '<td data-search="'.$topics.','.$m->name.'">';
        $out .= $mini;
        if ($m->is(Page::statusUnpublished)) {
          $out .= '<span style="text-decoration: line-through">'.$m->title.'</span>';
        } else {
          $out .= '<a href="'.$m->url.'train">'.$m->title.'</a>';
        }
        $out .= '';
        $out .= '</td>';
        $out .= '<td>'.$m->level.'</td>';
        $out .= '<td>';
        $m->of(false);
        if ($m->summary == '') {
          if ($m->summary->getLanguageValue($french) != '') {
            $out .= $m->summary->getLanguageValue($french);
          } else {
            $out .= '-';
          }
        } else {
          $out .= $m->summary;
          if ($user->language->name != 'french') {
            $m->of(false);
            if ($m->summary->getLanguageValue($french) != '') {
              $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-html="true" title="'.$m->summary->getLanguageValue($french).'"></span>';
            }
          }
        }
        // Data preview
        $exData = $m->exData;
        $allLines = preg_split('/$\r|\n/', $sanitizer->entitiesMarkdown($exData));
        $listWords = prepareListWords($allLines, $m->type->name);
        $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
        $out .= '</td>';
        // Most trained player
        if ($m->bestTrainedPlayerId != 0) {
          if (isset($player) && $m->bestTrainedPlayerId == $player->id) {
            $class = 'success';
          } else {
            $class = 'primary';
          }
        }
        $out .= '<td data-sort="'.$m->best.'">';
        if ($m->bestTrainedPlayerId != 0) {
          $bestTrained = $pages->get($m->bestTrainedPlayerId);
          $out .= '<span class="label label-'.$class.'">'.$m->best.' '.__('UT').' - '.$bestTrained->title.' ['.$bestTrained->team->title.']</span>';
        } else {
          $out .= '-';
        }
        $out .= '</td>';
        $out .= '<td data-sort="'.$m->bestTime.'">';
        if ($m->bestTime) {
          $master = $pages->get($m->bestTimePlayerId);
          $out .= ms2string($m->bestTime).' '.__('by').' '.$master->title.' ['.$master->team->title.']';
        } else {
          $out .= '-';
        }
        $out .= '</td>';
        $out .= '</tr>';
      }
      echo $out;
    ?>
  </tbody>
</table>
</div>

<?php
  include("./foot.inc"); 
?>

