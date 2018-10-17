<?php
	if ($allPlayers->count() == 1) { // Individual report
		$out .= '<h3 class="report">';
		$out .= '<img src="'.$allPlayers->eq(0)->avatar->url.'" width="95" alt="No avatar" />';
		$out .= $reportTitle;
		$out .= '</h3>';
	} else {
		$out .= '<h3 class="report">'.$reportTitle.'</h3>';
	}
?>
