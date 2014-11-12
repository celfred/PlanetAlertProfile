<?php

$reportTitle = '';
if ($input->urlSegment1 && $input->urlSegment2 == '') { // Class report
  $team = $input->urlSegment1;
  $reportTitle = 'Suivi du travail ('.$team.')';
} else if ($input->urlSegment2 != '' && $input->urlSegment2 != 'participation') { // 1 player report
  $playerId = $input->urlSegment2;
  $selectedPlayer = $pages->get($playerId);
  echo '<h1>Report : '.$selectedPlayer->title .' ('. $selectedPlayer->team.')</h1>';
  $reportTitle = '<h3>Bilan de '.$selectedPlayer->title.' ('. $selectedPlayer->team.')</h3>';;
} else if ($input->urlSegment2 != '' && $input->urlSegment2 == 'participation') { // Team participation
  $team = $input->urlSegment1;
  $reportTitle = 'Participation ('.$team.')';
  if ($input->urlSegment3) {
    $reportTitle .= ' (10 derniers cours)';
  }
}

echo $page->title;
echo ' : '.$reportTitle;
echo ', généré le '.strftime("%d/%m/%Y à %T", $page->created);

?>
