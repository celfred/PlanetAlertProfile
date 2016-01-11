<?php

$out = '';

include("./head.inc"); 

$out .= '<div>';

$out .= '<h1>'.$page->title.'</h1>';
$out .= '<h2 class="well">'.$page->summary.'</h2>';

$allMonsters = $page->children->sort('name');

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
    $out .= '<td>'.$m->summary.'</td>';
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

