<?php namespace ProcessWire;
	$out .= '<div id="energyDiv" class="row text-center well">';
	// Monster's health points
	$out .= '<div class="row text-center">';
	$out .= '<div class="col-sm-3">';
	if ($page->image) {
		$out .= '<img class="pull-right" src="'.$page->image->getCrop("mini")->url.'" alt="Monster" />';
	}
	$out .= '</div>';
	$out .= '<div class="col-sm-6">';
	$out .= '<div class="progress progress-lg" data-toggle="tooltip" title="'.__("Health points").'">';
	$out .= '<div class="progress-bar progress-bar-striped progress-bar-danger active" role="progressbar" aria-valuenow="{{monsterHP}}" aria-valuemin="0" aria-valuemax="100" ng-attr-style="width:{{monsterHP}}%">';
	$out .= '</div>';
	$out .= '</div>';
	$out .= '</div>';
	$out .= '<div class="col-sm-3 text-left">';
	$out .= '<span class="label label-primary">'.$page->title.'</span>';
	$out .= '</div>';
	$out .= '</div>';
	// Player's health points
	$out .= '<div class="row text-center">';
	$out .= '<div class="col-sm-3">';
	if ($player->avatar) {
		$out .= '<img class="pull-right" src="'.$player->avatar->getCrop("mini")->url.'" alt="Avatar" />';
	} else {
		$out .= '<Avatar>';
	}
	$out .= '</div>';
	$out .= '<div class="col-sm-6">';
	$out .= '<div class="progress progress-lg" data-toggle="tooltip" title="'.__("Health points").'">';
	$out .= '<div class="progress-bar progress-bar-striped progress-bar-success active" role="progressbar" aria-valuenow="{{playerHP}}" aria-valuemin="0" aria-valuemax="100" ng-attr-style="width:{{playerHP}}%">';
	$out .= '</div>';
	$out .= '</div>';
	$out .= '</div>';
	$out .= '<div class="col-sm-3 text-left">';
	$out .= '<span class="label label-primary">'.$player->title.'</span>';
	$out .= '</div>';
	$out .= '</div>';
	$out .= '</div>';
?>
