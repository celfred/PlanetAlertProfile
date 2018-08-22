<?php namespace ProcessWire;
/* Donation template */

include("./head.inc"); 


if ($user->hasRole('teacher') || $user->isSuperuser()) {
  $team = $pages->get("template=team, name=$input->urlSegment1");;
  include("./tabList.inc");
}
if ($user->hasRole('player')) {
  $team = $player->team;
}

$out = '';

// Reload to include 'no-team' players
// TODO : Restrict to teacher's players
if ($user->isSuperuser()) {
  $globalPlayers = $pages->find("template=player, name!=test")->sort('team.name, title');
} else {
  $globalPlayers = $pages->find("template=player, team=$team")->sort('team.name, title');
}

if ($user->isLoggedin() || $user->isSuperuser()) {
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    if (isset($input->urlSegment2)) {
      $playerId = $input->urlSegment2;
      $hidden = '';
    } else {
      $playerId = 0;
      $hidden = 'hidden';
    }
    $maxAmount = 1000;
  } else {
    $playerId = $player->id;
    $maxAmount= $player->GC;
  }
  $out .= '<h3 class="well text-center">'.__("Make a donation").'</h3>';
  $out .= "<section id='donationDiv' class='well text-center'>";
  $out .= '<form id="donateForm" name="donateForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="form-horizontal" role="form">';
  $out .= '<div class="form-group has-warning has-feedback">';
  if ($user->isSuperuser() || $user->hasRole('teacher')) {
    $out .= '<label for="amount" class="col-sm-5 control-label">'.__("Amount").' (<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" />&nbsp;<span class="glyphicon glyphicon-warning-sign"></span> max.) : </label>';
  } else {
    $out .= '<label for="amount" class="col-sm-5 control-label">'.__('Amount').' (<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" />&nbsp;'.$maxAmount.' max.) : </label>';
  }
  $out .= '<div class="col-sm-5">';
  $out .= '<input id="amount" name="amount" type="text" data-max="'.$maxAmount.'" size="5" placeholder="0" class="form-control" />';
  $out .= '<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>';
  $out .= '</div>';
  $out .= '</div>';
  if ($user->isSuperuser() || $user->hasRole('teacher')) { // Donator selection
    $out .= '<div class="form-group has-warning has-feedback">';
    $out .= '<label for="donator" class="col-sm-5 control-label">'.__("Donation from").' : </label>';
    $out .= '<div class="col-sm-5">';
    $out .= '<select class="form-control" id="donator" name="donator">';
      $out .= '<option value="0">'.__("Select a player").'</option>';
      foreach ($globalPlayers as $plyr) {
        if ($plyr->id == $playerId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        if ($plyr->team->name != 'no-team') {
          $team = ' ['.$plyr->team->title.']';
        } else {
          $team = '';
        }
        $out .= '<option value="'.$plyr->id.'"'.$selected.'>'.$plyr->title.$team.' ('.$plyr->GC.' GC)</option>';
      }
    $out .= '</select>';
    $out .= '<span class="glyphicon glyphicon-warning-sign form-control-feedback '.$hidden.'" aria-hidden="true"></span>';
    $out .= '</div>';
    $out .= '</div>';
  } else {
    $out .= '<input type="hidden" id="donator" name="donator" value="'.$playerId.'" />';
  }
  $out .= '<div class="form-group has-warning has-feedback">';
  $out .= '<label for="receiver" class="col-sm-5 control-label">'.__("Donate to").' : </label>';
  $out .= '<div class="col-sm-5">';
  $out .= '<select class="form-control" id="receiver" name="receiver">';
    $out .= '<option value="0">'.__('Select a player').'</option>';
    foreach ($globalPlayers as $plyr) {
      if ($plyr->id != $playerId) {
        if ($plyr->team->name != 'no-team') { $team = ' ['.$plyr->team->title.']'; } else { $team = ''; }
        $out .= '<option value="'.$plyr->id.'">'.$plyr->title.$team.' ('.$plyr->GC.' GC)</option>';
      }
    }
  $out .= '</select>';
  $out .= '<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= ' <input id="donateFormSubmit" name="donateFormSubmit" type="submit" class="form-control btn btn-primary btn-sm" value="'.__('Donate !').'" disabled="true" />';
  $out .= '</form>';
  $out .= '</section>';

} else {
  $out .= '<p>'.__("You don't have permission to make a donation. If you think this is an error, please contact the Administrator.").'</p>';
}

echo $out;

include("./foot.inc"); 
?>
