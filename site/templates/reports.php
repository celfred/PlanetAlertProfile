<?php // Team report template

include("./head.inc"); 

// Nav tabs
include("./tabList.inc"); 

$selectedTeam = $input->urlSegment1;
$allPlayers = $pages->find("template=player, team=$selectedTeam, sort=name");
$allPeriods = $pages->find("template=period");

if ($user->isSuperuser()) {
?>
  <section class="well">
  <div>
    <span>Report category : </span>
      <!-- TODO : List all possible categories? -->
      <label for="allCat"><input type="radio" value="all" id="allCat" name="reportCat" checked="checked" class="reportCat"> All</input></label> &nbsp;&nbsp;
      <label for="participation"><input type="radio" value="participation" id="participation" name="reportCat" class="reportCat"> Participation</input></label> &nbsp;&nbsp;
      <label for="planetAlert"><input type="radio" value="planetAlert" id="planetAlert" name="reportCat" class="reportCat"> Planet Alert</input></label> &nbsp;&nbsp;
  </div>
  <div>
    <span>Ordering by : </span>
      <label for="firstName"><input type="radio" class="reportSort" id="firstName" name="order" checked="checked" value="title"> First name</input></label> &nbsp;&nbsp;
      <label for="lastName"><input type="radio" class="reportSort" id="lastName" name="order" value="lastName"> Last name</input></label>
  </div>
  <div>
    <span>Period : </span>
      <select id="periodId">
        <?php
          foreach($allPeriods as $period) {
            echo "<option value='{$period->id}'>{$period->title}</option>";
          }
        ?>
      </select>
  </div>
  <div>
  <span>Select a player : </span>
  <select id="reportPlayer">
  <?php
    echo "<option value='{$input->urlSegment1}'>The whole team</option>";
    foreach($allPlayers as $player) {
      echo "<option value='{$player->name}'>{$player->title}</option>";
    }
  echo '</select>';
  // reportUrl is based on url segments : all|category/team|player/periodId?sort=title|lastName
  echo '<p class="text-center"><a id="reportUrl_button" class="btn btn-primary" href="'. $pages->get('/report_generator')->url .'" data-reportUrl="'. $pages->get('/report_generator')->url .'" target="_blank">Generate report</a></p>';
  ?>
  </div>
  </section>

<?php
} else {
  echo '<p>Admin only ;)</p>';
}

include("./foot.inc"); 
?>
