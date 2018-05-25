<?php

$selected = $pages->get("id=$input->urlSegment2");
if ($user->isSuperuser() || $user->isLoggedin()) {
  if (!$config->ajax) {
    include("./head_report.inc"); 
  }

  $reportTitle = '';
  $category = $input->urlSegment1;
  $period = $pages->get("$input->urlSegment3");
  $sort = $input->get['sort'];

  $categories = $pages->find("parent='/categories/',sort=sort")->not("name=shop|potions|protections|place|weapons|manual-cat|oublis|group-items");

  if ($selected->template == 'player') { // Player's report
    if ($user->isSuperuser() || $selected->login == $user->name) {
      $player = $selected;
      $global = false;
      $reportTitle = '';
      $reportType = '';
      if ($category == 'all') { // Global report
      } else { // Category report
        $reportTitle .= "'".$category."' report.";
        switch ($category) {
          case 'participation' : $reportType = 'participation'; break;
          case 'planetAlert' : $reportType = 'planetAlert'; break;
          default: $reportType =  'test';
        }
      }
      $reportTitle .= ' for '.$selected->title.' '.$selected->lastName.' ('.$selected->team->title.')'; 
      $reportTitle .= '<br />';
      $reportTitle .= 'Period : '.$period->title;
      $reportTitle .= ' ('.date("d/m", $period->dateStart).' → '.date("d/m", $period->dateEnd).')';
    } else {
      echo "You can't see this page. If you think this is an error, contact the administrator.";
    }
  } else { // Team's report
    $global = true;
    $allPlayers = $pages->find("parent.name=players, team=$selected, template=player, sort=$sort");
    $reportTitle = '';
    $reportType = '';
    if ($category == 'all') { // Global report
      $reportTitle .= 'Global report';
    } else { // Category report
      $reportTitle .= "'".$category."' report";
      switch ($category) {
        case 'participation' : $reportType = 'participation'; break;
        case 'planetAlert' : $reportType = 'planetAlert'; break;
        default: break;
      }
    }
    $reportTitle .= ' ('.$selected->title .' team)'; 
    $reportTitle .= '<br />';
    $reportTitle .= 'Period : '.$period->title;
    $reportTitle .= ' ('.date("d/m", $period->dateStart).' → '.date("d/m", $period->dateEnd).')';
  }
   
  // PDF Download link
  if (!$input->get['pages2pdf'] && $user->isSuperuser()) {
    echo '<a class="pdfLink btn btn-info" href="' . $page->url.$input->urlSegment1.'/'.$input->urlSegment2.'/'.$input->urlSegment3. '?sort='.$sort.'&pages2pdf=1">Get PDF</a>';
  }

  if (!$global) { // Single Player report
    $player = $selected;
    switch($reportType) {
      default: 
        include('./singlePlayerReport_default.inc');
    }
  } else { // Team report
    switch($reportType) {
      case 'participation': include('./globalReport_participation.inc'); break;
      case 'planetAlert': include('./globalReport_planetAlert.inc'); break;
      default: include('./globalReport_default.inc');
    }
  }

  if (!$config->ajax) {
    include("./foot.inc"); 
  }

} else {
  echo "You need to log in to see the reports.";
}
?>
