<?php 
// Stats template

include("./head.inc"); 

// Get current school year dates
$period = $pages->get("template='period', name='school-year'");
// Get today's unique logged players' names
$query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp >= CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
$query->execute();
$todaysPlayers = $query->fetchAll();
// Get yesterday's unique logged players' names
$query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()");   
$query->execute();
$yesterdaysPlayers = $query->fetchAll();
// Get total # of unique logged players during the last 7 days
$query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
$query->execute();
$totalNbUniqueVisitors7Days = $query->fetchColumn();
// Get total # of logged players during the last 7 days
$query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
$query->execute();
$totalNbVisitors7Days = $query->fetchColumn();
// Get total # of unique logged players during the current school year
$query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
$query->execute();
$totalNbUniqueVisitors = $query->fetchColumn();
// Get total # of logged players during the current school year
$query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
$query->execute();
$totalNbVisitors = $query->fetchColumn();
// Find NEVER logged players during the current school year
// Get logged names in current school year
$query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp BETWEEN ".$period->dateStart." AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
$query->execute();
$totalVisitors = $query->fetchAll();
$neverLogged = [];
// Compare to all players
foreach($allPlayers as $p) {
  if (in_array($p->id, $totalVisitors)) {
    array_push($neverLogged,$p);
  }
}

$stats = '<div id="" class="news panel panel-primary">';
$stats .= '<div class="panel-heading">';
$stats .= '<h2 class="panel-title">Planet Alert Statistics (started 17/09/2015)</h2>';
$stats .= '</div>';
$stats .= '<div class="panel-body">';
$stats .= '<p class="lead">';
$stats .= '&nbsp;&nbsp;&nbsp';
$stats .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">Today : '.count($todaysPlayers).'</span>';
$stats .= '&nbsp;&nbsp;&nbsp';
$stats .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">Yesterday : '.count($yesterdaysPlayers).'</span>';
$stats .= '&nbsp;&nbsp;&nbsp';
$stats .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">Last 7 days : '.$totalNbUniqueVisitors7Days.'/'.$totalNbVisitors7Days.'</span>';
$stats .= '&nbsp;&nbsp;&nbsp';
$stats .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">School Year : '.$totalNbUniqueVisitors.'/'.$totalNbVisitors.'</span>';
$stats .= '&nbsp;&nbsp;&nbsp';
$stats .= '</p>';
// Admin is logged in, show names
if ($user->isSuperuser()) {
  if ( count($todaysPlayers) > 0 ) {
    $stats .= '<ul class="list-inline list-unstyled">';
    $stats .= '<span>Today\'s players : </span>';
    foreach($todaysPlayers as $r) {
      // Get player's name
      $login = $r['username'];
      $player = $pages->get("template='player', login=$login");
      $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> ['.$player->playerTeam.']</li>';
    }
    $stats .= '</ul>';
  }
  if ( count($yesterdaysPlayers) > 0 ) {
    $stats .= '<span>Yesterday\'s players : </span>';
    $stats .= '<ul class="list-inline list-unstyled">';
    foreach($yesterdaysPlayers as $r) {
      // Get player's name
      $login = $r['username'];
      $player = $pages->get("template='player', login=$login");
      $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> ['.$player->playerTeam.']</li>';
    }
    $stats .= '</ul>';
  }
  if ($neverLogged->count > 0) {
    $stats .= '<ul class="list-inline list-unstyled">';
    $stats .= '<span>Never logged : </span>';
    foreach ($neverLogged as $nl) {
        $stats .= '<li><a href="'.$nl->url.'">'.$nl->title.'</a> ['.$nl->playerTeam.']</li>';
    };
    $stats .= '</ul>';
  };
}
$stats .= '</div>';
$stats .= '</div>';
echo $stats;

include("./foot.inc"); 

?>
