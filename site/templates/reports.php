<?php // Team report template

include("./head.inc"); 

if ($user->isSuperuser()) {
  // List of teams (taken from fead.inc)
  echo '<span style="margin: 5px 20px;">';
    echo '<span>Team reports : </span>';
    echo '<label for="participation"><input type="checkbox" id="participation"> Participation</input></label>';
    echo '&nbsp;&nbsp;';
    echo '<label for="limit10"><input type="checkbox" id="limit10" disabled="disabled"> Limit 10</input></label>';
    echo '&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;';
    echo '<label for="firstName"><input type="radio" id="firstName" name="order" checked="checked"> First name</input></label>';
    echo '&nbsp;&nbsp;';
    echo '<label for="lastName"><input type="radio" id="lastName" name="order"> Last name</input></label>';
    echo '</span>';
    foreach($uniqueResults as $player) {
      echo "<span class='btn btn-primary'><a class='ajax reportButton' href='{$pages->get('/report_generator')->url}{$sanitizer->pageName($player->team)}'>{$player->team}</a></span> ";
    }
    echo '<span style="float: right;">';
    // List of players
    echo '<span>Player reports : </span>';
    echo '<select id="players_list">';
    foreach($players as $player) {
      echo "<option value='{$pages->get('/report_generator')->url}{$sanitizer->pageName($player->team)}/{$sanitizer->pageName($player->id)}'>{$player->title} ({$player->team})</a></option>";
    }
    echo '</select>';
    echo '<button id="report_button">Generate</button>';
  echo '</span>';


} else {
  echo '<p>Admin only ;)</p>';
}

echo '<div id="reportDiv">Select a report.</div>';

include("./foot.inc"); 
?>
