<?php namespace ProcessWire;
  include("./head.inc"); 

  $out = '';

  if ($user->isGuest()) {
    $allMonsters = $page->children->sort("level, name");
  } else {
    if ($user->isSuperuser()) {
      $allMonsters = $page->children("include=all")->sort("level, name");
    }
    if ($user->hasRole('teacher')) {
      $allMonsters = $page->children("(created_users_id=$user->id), (teacher=$user), publish=1")->sort("level, name");
    }
    if ($user->hasRole('player')) {
      $allMonsters = $page->children("(created_users_id=$headTeacher->id), (teacher=$headTeacher), publish=1")->sort("level, name");
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
        echo '<img class="" src="'.$helmet->image->getCrop('small')->url.'" alt="Helmet" />';
      }
      echo ' <a href="'.$pages->get("name=underground-training")->url.'">'.__("Go to the Underground Training Zone").'</a>   ';
    } else {
      echo '<div class="well text-center">';
      echo __("You must buy the Memory Helmet if you want to do Underground Training.");
      echo '</div>';
    }
    if ($visualizer) {
      if ($visualizer->image) {
        echo '<img class="" src="'.$visualizer->image->getCrop('small')->url.'" alt="Visualizer" />';
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
    echo '<div>';
    echo '<h4>'.$page->summary.'</h4>';
    echo '</div>';
  }


  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    $colIndex = 3;
  } else {
    $colIndex = 2;
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
    <th></th>
    <th><?php echo __('Name'); ?></th>
    <th><?php echo __('Topic'); ?></th>
    <th><?php echo __('Level'); ?></th>
    <!-- <th>Type</th> -->
    <th><?php echo __('Summary'); ?></th>
    <th><?php echo __('# of words'); ?></th>
    <th><?php echo __('Most trained player'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach ($allMonsters as $m) {
        $out .= '<tr>';
        if ($m->image) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getCrop('thumbnail')->url."\" alt=\"image\" />' src='".$m->image->getCrop('mini')->url."' alt='image' />";
        } else {
          $mini = '';
        }
        if ($user->isSuperuser() || $user->hasRole('teacher')) {
          $out .= '<td>';
          $out .= '<a class="pdfLink btn btn-info btn-xs" href="'.$page->url.'?id='.$m->id.'&pages2pdf=1">[PDF Fight]</a>';
          $out .= '<a class="pdfLink btn btn-info btn-xs" href="'.$page->url.'?id='.$m->id.'&thumbnail=1&pages2pdf=1">[PDF Image]</a>';
          $out .= '</td>';
        }
        $out .= '<td>'. $mini .'</td>';
        $out .= '<td>';
        if ($m->is(Page::statusUnpublished)) {
          $out .= '<span style="text-decoration: line-through">'.$m->title.'</span>';
        } else {
          $out .= $m->title;
        }
        $out .= '';
        $out .= '</td>';
        $out .= '<td>';
        $out .= '<span class="label label-default">'.$m->topic->implode(', ', '{title}').'</span>';
        $out .= '</td>';
        $out .= '<td>'.$m->level.'</td>';
        if ($user->language->name != 'french') {
          $m->of(false);
          $m->summary == '' ? $summary = '-' : $summary = $m->summary;
          $out .= '<td>'.$summary;
          if ($m->summary->getLanguageValue($french) != '') {
            $out .= ' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$m->summary->getLanguageValue($french).'"></span></td>';
          }
        } else {
          $out .= '<td>'.$m->summary.'</td>';
        }
        // Count # of words
        $exData = $m->exData;
        $allLines = preg_split('/$\r|\n/', $sanitizer->entitiesMarkdown($exData));
        /* Unused because triggers a bug with tooltip display */
        /* $out .= '<td data-sort="'.count($allLines).'">'; */
        $out .= '<td>';
        if ($m->type) {
          $listWords = prepareListWords($allLines, $m->type->name);
          switch ($m->type->name) {
            case 'translate' :
              $out .= count($allLines).' '.__("words");
              break;
            case 'quiz' :
              $out .= count($allLines).' '.__("questions");
              break;
            case 'image-map' :
              $out .= count($allLines).' '.__("words");
              break;
            case 'jumble' :
              $out .= count($allLines).' '.__("sentences");
              break;
            default : continue;
        }
        $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
        }
        $out .= '</td>';
        // Find best trained player on this monster
        if ($m->mostTrained) {
          if (isset($player) && $m->mostTrained == $player) {
            $class = 'success';
          } else {
            $class = 'primary';
          }
        }
        $out .= '<td data-sort="'.$m->best.'">';
        if ($m->mostTrained) {
          $out .= '<span class="label label-'.$class.'">'.$m->best.' UT - '.$m->mostTrained->title.' ['.$m->mostTrained->team->title.']</span>';
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

