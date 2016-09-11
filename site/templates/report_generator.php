<?php 

if ($user->isSuperuser() || $user->isLoggedin() ) {

  if (!$config->ajax) {
    include("./head_report.inc"); 
  }

  $reportTitle = '';
  $category = $input->urlSegment1;
  $selected = $pages->get("name=$input->urlSegment2");
  $period = $pages->get("$input->urlSegment3");
  $sort = $input->get['sort'];

  $categories = $pages->find("parent='/categories/',sort=sort")->not("name=shop|potions|protections|place|weapons|manual-cat|oublis");

  if ($selected->template == 'player') { // Player's report
    $player = $selected;
    $global = false;
    $reportTitle = '';
    if ($category == 'all') { // Global report
    } else { // Category report
      $reportTitle .= "'".$category."' report.";
      switch ($category) {
        case 'participation' : $reportType = 'participation'; break;
        case 'planetAlert' : $reportType = 'planetAlert'; break;
        default: break;
      }
    }
    $reportTitle .= ' for '.$selected->title.' '.$selected->lastName.' ('.$selected->team->title.')'; 
    $reportTitle .= '<br />';
    $reportTitle .= 'Period : '.$period->title;
  } else { // Team's report
    $global = true;
    $selected = strtoupper($input->urlSegment2);
    $allPlayers = $pages->find("team.name=$selected, template=player, sort=$sort");
    $reportTitle = '';
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
    $reportTitle .= ' ('.$selected.' team)'; 
    $reportTitle .= '<br />';
    $reportTitle .= 'Period : '.$period->title;
  }
   
  // PDF Download link
  if (!$input->get['pages2pdf'] && $user->isSuperuser()) {
    echo '<a class="pdfLink btn btn-info" href="' . $page->url.$input->urlSegment1.'/'.$input->urlSegment2.'/'.$input->urlSegment3. '?sort='.$sort.'&pages2pdf=1">Get PDF</a>';
  }

  if (!$global) { // Single Player report
    $player = $selected;
    switch($reportType) {
    default: 
      if ($user->isSuperuser() || $user->isLoggedin() == $player->login ) {
        include('./singlePlayerReport_default.inc');
      } else {
        echo 'Please log in to see this report.';
      }
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

}
?>
