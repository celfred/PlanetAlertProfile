<?php 

$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');

if ($user->isSuperuser()) {
  $tasks = $pages->get("/tasks/")->find("template=task, sort=category, sort=title");
} else {
  $tasks = $pages->get("/tasks/")->find("template=task, (owner.singleTeacher=$user), (adminOnly=1), sort=category, sort=title");
}

$out = '';

$out .= '<table>';
$out .= '<tr>';
$out .= '<td><img style="" src="'.$logo->url.'" /></td>';
$out .= '<td colspan="5"><h1>'.__("Planet Alert : The Actions").'</h1></td>';
$out .= '<td><img style="" src="'.$logo->url.'" /></td>';
$out .= '</tr>';
$out .= '<tr>';
$out .= '<th>'.__("Category").'</th>';
$out .= '<th>'.__("Action").'</th>';
$out .= '<th>'.__("Description").'</th>';
$out .= '<th>'.__("HP").'</th>';
$out .= '<th>'.__("XP").'</th>';
$out .= '<th>'.__("GC").'</th>';
$out .= '<th>+/-</th>';
$out .= '</tr>';
foreach($tasks as $task) {
  if ($task->HP < 0) {
    $className = 'negative';
    $type = '-';
  } else {
    $className = 'positive';
    $type = '+';
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
  $out .= $type;
  $out .= '</td>';
  $out .= '</tr>';
}
$out .= '</table>';

echo $out;

?>
