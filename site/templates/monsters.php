<?php

$out = '';

include("./head.inc"); 

$out .= '<div>';

/* $out .= '<h1>'.$page->title.'</h1>'; */
echo '<div class="well">';
echo '<h4>'.$page->summary.'</h4>';
echo '</div>';

if ($user->isSuperuser()) {
  $allMonsters = $page->children("include=all")->sort('name');
} else {
  $allMonsters = $page->children->sort('name');
}

/* $allCategories = $pages->find("parent.name=topics, sort=name"); */


  // Test player login
  if ($player && $user->isLoggedin() || $user->isSuperuser()) {
    // Test if player has unlocked Memory helmet (only training equipment for the moment)
    if ($user->isSuperuser()) {
      $helmet = $pages->get("name=memory-helmet");
    } else {
      $helmet = $player->equipment->get('memory-helmet');
    }
    if ($helmet) {
      echo '<div class="well text-center">';
      echo '<h4><a href="'.$pages->get("name=underground-training")->url.'">Go to the Underground Training Zone !</a></h4>';
      echo '</div>';
    } else {
      echo '<div class="well text-center">';
      echo 'You must buy the Memory Helmet if you want to do Underground Training.';
      echo '</div>';
    }
  }

?>

<table id="monstersTable" class="table table-condensed table-hover">
<thead>
  <tr>
  <th colspan="2">Name</th>
  <th>Topic</th>
  <th>Level</th>
  <th>Type</th>
  <th>Summary</th>
  <th># of words</th>
  <th>Most trained player</th>
</tr>
</thead>
<tbody>
<?php
  foreach ($allMonsters as $m) {
    $out .= '<tr>';
    if ($m->image) {
      $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$m->image->getThumb('thumbnail')."\" alt=\"image\" />' src='".$m->image->getThumb('mini')."' alt='image' />";
    } else {
      $mini = '';
    }
    $out .= '<td>'. $mini .'</td>';
    $out .= '<td>';
    $out .= $m->title;
    $out .= '';
    $out .= '</td>';
    $out .= '<td>';
    foreach ($m->topic as $t) {
      $out .= '<span class="label label-default">'.$t->title.'</span>';
    }
    $out .= '</td>';
    $out .= '<td>'.$m->level.'</td>';
    $out .= '<td>'.$m->type->title.'</td>';
    $out .= '<td>'.$m->summary.' <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="'.$m->frenchSummary.'"></span></td>';
    // Count # of words
    $exData = $m->exData;
    $allLines = preg_split('/$\r|\n/', $exData);
    /* Unused because triggers a bug with tooltip display */
    /* $out .= '<td data-sort="'.count($allLines).'">'; */
    $out .= '<td>';
    // Prepare list of French words
    switch ($m->type->name) {
      case 'translate' :
        $out .= count($allLines).' words';
        if (count($allLines)>15) {
          $listWords = '<strong>15 first words :</strong><br />';
          for($i=0; $i<15; $i++) {
            list($left, $right) = preg_split('/,/', $allLines[$i]);
            $listWords .= $right.'<br />';
          }
          $listWords .= '[...]';
        } else {
          $listWords = '';
          foreach($allLines as $line) {
            list($left, $right) = preg_split('/,/', $line);
            $listWords .= $right.'<br />';
          }
        }
        break;
      case 'quiz' :
        $out .= count($allLines).' questions';
        if (count($allLines)>15) {
          $listWords = '<strong>15 first questions :</strong><br />';
          for($i=0; $i<15; $i++) {
            list($left, $right) = preg_split('/\?/', $allLines[$i]);
            $listWords .= '- '.$left.'<br />';
          }
          $listWords .= '[...]';
        } else {
          $listWords = '';
          foreach($allLines as $line) {
            list($left, $right) = preg_split('/\?/', $line);
            $listWords .= '- '.$left.'<br />';
          }
        }
        break;
      default :
        $listWords = '';
        break;
    }
    $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.$listWords.'"></span>';
    $out .= '</td>';
    // Find best trained player on this monster
    if ($m->mostTrained) {
      $bestUt = utGain($m->id, $m->mostTrained);
      if ($m->mostTrained == $player) {
        $class = 'success';
      } else {
        $class = 'primary';
      }
    }
    $out .= '<td data-sort="'.$bestUt.'">';
    if ($m->mostTrained) {
      $out .= '<span class="label label-'.$class.'">'.$bestUt.' UT - '.$m->mostTrained->title.' ['.$m->mostTrained->playerTeam.']</span>';
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

