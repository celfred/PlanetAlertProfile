<?php // Team report template

include("./head.inc"); 

$allPlayers = $pages->get("/players")->children("team.count>0");

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
    foreach($allTeams as $team) {
      echo "<span class='btn btn-primary'><a class='ajax reportButton' href='{$pages->get('/report_generator')->url}{$sanitizer->pageName($team->name)}'>{$team->title}</a></span> ";
    }
    echo '<span style="float: right;">';
    // List of players
    echo '<span>Player reports : </span>';
    echo '<select id="players_list">';
    foreach($allPlayers as $player) {
      echo "<option value='{$pages->get('/report_generator')->url}{$sanitizer->pageName($player->team->name)}/{$sanitizer->pageName($player->id)}'>{$player->title} ({$player->team->title})</a></option>";
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
