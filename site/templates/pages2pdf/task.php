<?php 

$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');

$tasks = $pages->find("template='task', name!='tasks', sort='category'");

$out = '';

$out .= '<table>';
$out .= '<tr>';
$out .= '<td><img style="" src="'.$logo->url.'" /></td>';
$out .= '<td colspan="5"><h1>Planet Alert : The Actions</h1></td>';
$out .= '<td><img style="" src="'.$logo->url.'" /></td>';
$out .= '</tr>';
$out .= '<tr>';
$out .= '<th>Category</th>';
$out .= '<th>Action</th>';
$out .= '<th>Descriptions</th>';
$out .= '<th>HP</th>';
$out .= '<th>XP</th>';
$out .= '<th>GC</th>';
$out .= '<th>+/-</th>';
$out .= '</tr>';
foreach($tasks as $task) {
  if ($task->HP < 0) {
    $className = 'negative';
  } else {
    $className = 'positive';
  }
  $out .= '<tr class="'.$className.'">';
  $out .= '<td>';
  $out .= $task->category->title;
  $out .= '</td>';
  $out .= '<td>';
  $out .= $task->title;
  $out .= '</td>';
  $out .= '<td>';
  $out .= $task->summary;
  $out .= '</td>';
  $out .= '<td>';
  $out .= $task->HP;
  $out .= '</td>';
  $out .= '<td>';
  $out .= $task->XP;
  $out .= '</td>';
  $out .= '<td>';
  $out .= $task->GC;
  $out .= '</td>';
  $out .= '<td>';
  $out .= $className;
  $out .= '</td>';
  $out .= '</tr>';
}
$out .= '</table>';

echo $out;


?>

