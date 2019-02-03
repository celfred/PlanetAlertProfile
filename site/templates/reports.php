<?php
include("./head_report.inc"); 

$path = $config->paths->templates."reports/";
if ($input->get['pages2pdf']) {
  include("../my-functions.inc");
}

$reportTitle = '';
// Populate variables
if (count($input->post) > 0) {
  $reportCat = $input->post->reportCat;
  if ($reportCat == NULL) { // Individual report
    $reportCat = 'individual';
  }
  $reportSelected = $input->post->reportSelected;
  $reportPeriod = $input->post->reportPeriod;
  $startDate = $input->post->startDate;
  $endDate = $input->post->endDate;
  $reportSort = $input->post->reportSort;
  $taskId = $input->post->taskId;
  $monsterId = $input->post->monsterId;
  $categoryId = $input->post->categoryId;
} else {
  $reportCat = $input->urlSegment1;
  $reportSelected = $input->urlSegment2;
  $reportPeriod = $input->urlSegment3;
  $startDate = $input->get->startDate;
  $endDate = $input->get->endDate;
  $reportSort = $input->get->reportSort;
  $taskId = $input->get->taskId;
  $monsterId = $input->get->monsterId;
  $categoryId = $input->get->categoryId;
}
// Set common options
$selected = $pages->get("id=$reportSelected");

if ($user->isSuperuser() || $user->hasRole('teacher') || ($user->hasRole('player') && $user->name == $selected->login)) { // Logged-in teacher can see his or her individual report
  switch($reportPeriod) {
    case 'customDates' :
      $period = new Page();
      $period->of(false);
      $period->template = 'period';
      $period->title = __("Custom dates");
      if ($startDate != '') {
        $period->dateStart = $startDate.' 00:00:00';
      } else {
        $period->dateStart = date('2000-01-01 00:00:00');
      } 
      if ($endDate != '') {
        $period->dateEnd = $endDate.' 23:59:59';
      } else {
       $period->dateEnd = date('Y-m-d 23:59:59');
      }
      break;
    default : 
      $period = $pages->get("id=$reportPeriod");
  }
  // Set specific options
  switch($reportCat) {
    case 'task':
      $task = $pages->get("id=$taskId");
      break;
    case 'fight':
      $monster = $pages->get("id=$monsterId");
      break;
    case 'category':
      $category = $pages->get("id=$categoryId");
      break;
    default:
      $taskId = '-1';
      $monsterId = '-1';
      $categoryId = '-1';
  }

  // PDF Download link
  if (!$input->get['pages2pdf'] && ($user->isSuperuser() || $user->hasRole('teacher'))) {
    echo '<a class="pdfLink btn btn-info" href="' . $page->url.$reportCat.'/'.$reportSelected.'/'.$reportPeriod. '?sort='.$reportSort.'&taskId='.$taskId.'&monsterId='.$monsterId.'&categoryId='.$categoryId.'&pages2pdf=1">Get PDF</a>';
    echo '<div class="row"></div>';
  }

  $reportTitle = '';
  switch($reportCat) {
    case 'all':
      $reportTitle .= __('Complete report');
      break;
    case 'task':
      $reportTitle .= __("Task report").' ['.$task->title.']';
      break;
    case 'ut':
      $reportTitle .= __("Underground Training report");
      break;
    case 'fight':
      $reportTitle .= __("Battle report");
      break;
    case 'fight':
      if ($monsterId == '-1') {
        $subTitle = __('All monsters');
      } else {
        $subTitle = $monster->title;
      }
      $reportTitle .= __("Fight report").' ['.$subTitle.']';
      break;
    case 'category':
      $reportTitle .= __("Category report").' ['.$category->title.']';
      break;
    default : 
      $reportTitle .= sprintf(__("%s report"), ucfirst($reportCat));
  }
  $reportTitle .= ' ('.$selected->title .')'; 
  $reportTitle .= '<br />';
  $reportTitle .= 'Period : '.$period->title;
  $reportTitle .= ' ('.date("d/m/Y", $period->dateStart).' → '.date("d/m/Y", $period->dateEnd).')';

  // Manage individual vs team report
  if ($selected->template == 'player') { // Player report
    $allPlayers = new PageArray();
    $allPlayers->add($selected);
  } else {
    $allPlayers = $pages->find("parent.name=players, team=$selected, template=player")->sort("$reportSort");
  }
  switch($reportCat) {
    case 'participation': include($path.'report-participation.inc'); break;
    case 'planetAlert': include($path.'report-planetAlert.inc'); break;
    case 'cm1': include($path.'report-cm1.inc'); break;
    case 'task': include($path.'report-task.inc'); break;
    case 'ut': include($path.'report-ut.inc'); break;
    case 'fight': include($path.'report-fight.inc'); break;
    case 'battle': include($path.'report-battle.inc'); break;
    case 'category': include($path.'report-category.inc'); break;
    default: include($path.'report-complete.inc');
  }
} else {
  echo $noAuthMessage;
}

include("./foot.inc"); 
?>
