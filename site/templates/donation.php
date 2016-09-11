<?php 
/* Donation template */

include("./head.inc"); 

$globalPlayers = $pages->find("template='player', sort=title, name!=test");

$out = '';

if ($user->isLoggedin() || $user->isSuperuser()) {
  $out .= '<h3 class="well text-center">Make a donation';
  $out .= '</h3>';
  $out .= "<section id='donationDiv' class='well text-center'>";
  $out .= '<form id="donateForm" name="donateForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="form-horizontal" role="form">';
  $out .= '<div class="form-group has-warning has-feedback">';
  $out .= '<label for="amount" class="col-sm-5 control-label">Amount (<img src="'.$config->urls->templates.'img/gold_mini.png" alt="" />&nbsp;'.$player->GC.' max.) : </label>';
  $out .= '<div class="col-sm-5">';
  $out .= '<input id="amount" name="amount" type="text" data-max="'.$player->GC.'" size="5" placeholder="0" class="form-control" />';
  $out .= '<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= '<div class="form-group has-warning has-feedback">';
  $out .= '<label for="receiver" class="col-sm-5 control-label">Donate to : </label>';
  $out .= '<div class="col-sm-5">';
  $out .= '<select class="form-control" id="receiver" name="receiver">';
    $out .= '<option value="0">Select a player</option>';
    foreach ($globalPlayers as $plyr) {
      if ($plyr->id != $player->id) {
        $out .= '<option value="'.$plyr->id.'">'.$plyr->title.' ['.$plyr->team->title.'] '.$plyr->GC.' GC</option>';
      }
    }
  $out .= '</select>';
  $out .= '<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>';
  $out .= '</div>';
  $out .= '</div>';
  $out .= ' <input id="donateFormSubmit" name="donateFormSubmit" type="submit" class="form-control btn btn-primary btn-sm" value="Donate !" disabled="true" />';
  $out .= '<input type="hidden" name="player" value="'.$player->id.'" />';
  $out .= '</form>';
  $out .= '</section>';

} else {
  $out .= '<p> You don\'t have permission to make a donation. If you think this is an error, please contact the Administrator.</p>';
}

echo $out;

include("./foot.inc"); 
?>
