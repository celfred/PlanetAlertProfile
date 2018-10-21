<?php
  if ($allPlayers->count() == 1) { // Individual report
    $out .= '<h3 class="report">';
    $out .= '<img src="'.$allPlayers->eq(0)->avatar->getCrop("thumbnail")->url.'" height="50" alt="No avatar" />';
    $out .= $reportTitle;
    $out .= '</h3>';
  } else {
    $out .= '<h3 class="report">'.$reportTitle.'</h3>';
  }
?>
