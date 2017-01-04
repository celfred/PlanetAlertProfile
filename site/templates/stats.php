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
$query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
$query->execute();
$totalNbUniqueVisitors = $query->fetchColumn();
// Get total # of logged players during the current school year
$query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
$query->execute();
$totalNbVisitors = $query->fetchColumn();
// Get total # of unique logged players since the very beginning
$query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username != 'admin' AND username != 'test' AND login_was_successful=1");   
$query->execute();
$grandTotalNbUniqueVisitors = $query->fetchColumn();

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
$stats .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">All times : '.$grandTotalNbUniqueVisitors.'</span>';
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
      $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']</li>';
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
      $stats .= '<li><a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']</li>';
    }
    $stats .= '</ul>';
  }
}

// Training sessions stats
$today = mktime(0,0,0, date("m"), date("d"), date("Y"));
$totalTrainingSessions = $pages->find("template=event, task.name=ut-action-v|ut-action-vv");
$todayTrainingSessions = $totalTrainingSessions->find("date>=$today");
$totalUt = 0;
$todayTrainedPlayers = [];
foreach( $todayTrainingSessions as $t) {
  $pl = $t->parent("template=player");
  if (!in_array($pl->id, $todayTrainedPlayers)) {
    array_push($todayTrainedPlayers, $pl->id);
  }
}
$trainedPlayers = $pages->find('template=player, underground_training>0');
foreach ($allPlayers as $p) {
  $totalUt += $p->underground_training;
}
$stats .= '<h3><span class="label label-default">Today Training sessions : ';
$stats .= $todayTrainingSessions->count().' with '.count($todayTrainedPlayers).' different player(s).';
$stats .= '</span></h3>';
$stats .= '<h3><span class="label label-default">Total Training sessions : ';
$stats .= $totalTrainingSessions->count.' with '.$trainedPlayers->count.' player(s).';
$stats .= ' ['.$totalUt.' UT points = '.($totalUt*10).' words, '.round(($totalUt/$totalTrainingSessions->count())*10).' words/session]';
$stats .= '</span></h3>';

// Last connected users (and dates)
$stats .= '<h3 class="text-center">Last logged dates</h3>';
$stats .= '<table id="loggedTable" class="table table-condensed table-hover">';
$stats .= '<thead>';
$stats .= '<tr>';
$stats .= '<th>Username</th>';
$stats .= '<th>Last logged date</th>';
$stats .= '</tr>';
$stats .= '</thead>';
$stats .= '<tbody>';
foreach ($allPlayers as $p) {
  $query = $database->prepare("SELECT login_timestamp FROM process_login_history WHERE username = :username AND login_was_successful=1 ORDER BY login_timestamp DESC LIMIT 1");   
  $query->execute(array(':username' => $p->name));
  $lastvisit = $query->fetchColumn();
  $stats .= '<tr><td>'.$p->title.' ['.$p->team->title.']</td><td>'. $lastvisit .'</td></tr>';
}
$stats .= '</tbody>';
$stats .= '</table>';

$stats .= '</div>';
$stats .= '</div>';

echo $stats;

include("./foot.inc"); 

?>
