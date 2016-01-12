<?php

$out = '';

include("./head.inc"); 

$out .= '<div>';

/* $out .= '<h1>'.$page->title.'</h1>'; */
echo '<div class="well">';
echo '<h3>'.$page->summary.'</h3>';
echo '</div>';

$allMonsters = $page->children->sort('name');

/* $allCategories = $pages->find("parent.name=topics, sort=name"); */

?>

<table id="monstersTable" class="table table-condensed table-hover">
<thead>
  <tr>
  <th>Name</th>
  <th>Topic</th>
  <th>Level</th>
  <th>Type</th>
  <th>Summary</th>
</tr>
</thead>
<tbody>
<?php
  foreach ($allMonsters as $m) {
    $out .= '<tr>';
    $out .= '<td>';
    $out .= $m->title;
    // todo : Add image if exists
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

