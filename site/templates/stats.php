<?php // Stats template

include("./head.inc"); 

if ($user->isSuperuser() || $user->hasRole('teacher')) {
  $excluded = $users->find("(roles=teacher|superuser), (name=test)")->implode("','", '{name}');

  // Sets $allPlayers according to logged in teacher
  if ($user->isSuperuser()) {
    $allPlayers = $pages->find("parent.name=players, template=player")->sort("title");
  } else {
    $allTeams = $pages->find("template=team, teacher=$user, sort=title");
    $allPlayers = $pages->find("template=player, parent.name=players, (teacher=$user), (team.teacher=$user)")->sort("team->name, title");
  }
  $allPlayersLogins = $allPlayers->implode("','", '{login}');

  // Get current school year dates
  $period = $pages->get("template=period, name=school-year");

  // Get today's unique logged players' names
  $query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp >= CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
  $query->execute();
  $todaysPlayers = $query->fetchAll();
  // Get yesterday's unique logged players' names
  $query = $database->prepare("SELECT DISTINCT username FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()");   
  $query->execute();
  $yesterdaysPlayers = $query->fetchAll();
  // Get total # of unique logged players during the last 7 days
  $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
  $query->execute();
  $totalNbUniqueVisitors7Days = $query->fetchColumn();
  // Get total # of logged players during the last 7 days
  $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
  $query->execute();
  $totalNbVisitors7Days = $query->fetchColumn();
  // Get total # of unique logged players during the current school year
  $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
  $query->execute();
  $totalNbUniqueVisitors = $query->fetchColumn();
  // Get total # of logged players during the current school year
  $query = $database->prepare("SELECT count(username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
  $query->execute();
  $totalNbVisitors = $query->fetchColumn();
  // Get total # of unique logged players since the very beginning
  $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1");   
  $query->execute();
  $grandTotalNbUniqueVisitors = $query->fetchColumn();

  $out = '<section class="news panel panel-primary">';
  $out .= '<div class="panel-heading">';
  $out .=   '<h2 class="panel-title">'.__("Planet Alert Statistics (started 17/09/2015)").'</h2>';
  $out .= '</div>';
  $out .= '<div class="panel-body">';
  $out .= '<p class="lead">';
  $out .= '&nbsp;&nbsp;&nbsp';
  $out .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">'.__("Today").' : '.count($todaysPlayers).'</span>';
  $out .= '&nbsp;&nbsp;&nbsp';
  $out .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">'.__("Yesterday").' : '.count($yesterdaysPlayers).'</span>';
  $out .= '&nbsp;&nbsp;&nbsp';
  $out .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">'.__("Last 7 days").' : '.$totalNbUniqueVisitors7Days.'/'.$totalNbVisitors7Days.'</span>';
  $out .= '&nbsp;&nbsp;&nbsp';
  $out .= '<span data-html="true" data-toggle="tooltip" title="unique/total" class="label label-success">'.__("School Year").' : '.$totalNbUniqueVisitors.'/'.$totalNbVisitors.'</span>';
  $out .= '&nbsp;&nbsp;&nbsp';
  $out .= '<span data-html="true" data-toggle="tooltip" title="unique" class="label label-success">'.__("All times").' : '.$grandTotalNbUniqueVisitors.'</span>';
  $out .= '&nbsp;&nbsp;&nbsp';
  $out .= '</p>';
  if (count($todaysPlayers) > 0 ) {
    $out .= '<ul class="list-inline list-unstyled">';
    $out .= '<span>'.__("Today's players").' : </span>';
    foreach($todaysPlayers as $r) {
      // Get player's name
      $login = $r['username'];
      $player = $allPlayers->get("template=player, login=$login");
      if ($player) {
        $out .= '<li>';
        $out .= '<a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']';
        $out .= '</li>';
      }
    }
    $out .= '</ul>';
  }
  if (count($yesterdaysPlayers) > 0 ) {
    $out .= '<span>Yesterday\'s players : </span>';
    $out .= '<ul class="list-inline list-unstyled">';
    foreach($yesterdaysPlayers as $r) {
      // Get player's name
      $login = $r['username'];
      $player = $allPlayers->get("template=player, login=$login");
      $out .= '<li>';
      $out .= '<a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']';
      $out .= '</li>';
    }
    $out .= '</ul>';
  }

  // Training sessions stats
  $totalTrainingSessions = $pages->find("has_parent=$allPlayers, template=event, task.name~=ut-action");
  // Today's training sessions
  $today = new DateTime("today");
  $limitDate = strtotime($today->format('Y-m-d'));
  $todayTrainingSessions = $totalTrainingSessions->find("date>=$limitDate");
  $todayTrainedPlayers = [];
  foreach($todayTrainingSessions as $t) {
    $pl = $t->parent("template=player");
    if (!in_array($pl->id, $todayTrainedPlayers)) {
      array_push($todayTrainedPlayers, $pl->id);
    }
  }
  $out .= '<h3><span class="label label-default">'.__("Today").' : ';
  $out .= sprintf(__('%1$s training sessions with %2$s different player·s'), $todayTrainingSessions->count(), count($todayTrainedPlayers));
  $out .= '</span></h3>';
  // 30 days training sessions
  $interval = new \DateInterval("P30D");
  $oldDate = $today->sub($interval);
  $limitDate = strtotime($oldDate->format('Y-m-d'));
  $thirtyDaysTrainingSessions = $totalTrainingSessions->find("date>=$limitDate");
  $totalUt = 0;
  $thirtyDaysTrainedPlayers = [];
  foreach($thirtyDaysTrainingSessions as $t) {
    $pl = $t->parent("template=player");
    if (!in_array($pl->id, $thirtyDaysTrainedPlayers)) {
      array_push($thirtyDaysTrainedPlayers, $pl->id);
    }
    preg_match("/\[\+([\d]+)U\.T\.\]/", $t->summary, $matches);
    if (isset($matches[0])) {
      $totalUt += (int) $matches[1];
    } else {
      $totalUt += 1;
    }
  }
  $out .= '<h3><span class="label label-default">'.__("Last 30 days").' : ';
  $out .= sprintf(__('%1$s training sessions with %2$s different player·s'), $thirtyDaysTrainingSessions->count(), count($thirtyDaysTrainedPlayers));
  if ($thirtyDaysTrainingSessions->count() > 0) {
    $out .= ' ['.$totalUt.__('UT').' = ';
    $out .= ($totalUt*10).__('words').', ';
    $out .= round(($totalUt/$thirtyDaysTrainingSessions->count())*10).' '.__('words/session').']';
  }
  $out .= '</span></h3>';
  // Total training sessions
  $totalUt = 0;
  $trainedPlayers = $allPlayers->find("underground_training>0");
  foreach ($allPlayers as $p) {
    $totalUt += $p->underground_training;
  }
  $out .= '<h3><span class="label label-default">'.__("All times").' : ';
  $out .= sprintf(__('%1$s training sessions with %2$s different player·s'), $totalTrainingSessions->count(), $trainedPlayers->count());
  if ($totalTrainingSessions->count() > 0) {
    $out .= ' ['.$totalUt.__('UT').' = ';
    $out .= ($totalUt*10).__('words').', ';
    $out .= round(($totalUt/$totalTrainingSessions->count())*10).' '.__('words/session').']';
  }
  $out .= '</span></h3>';

  $out .= '</div>';
  $out .= '</section>';
} else {
  $out = '<section>';
  $out .= $noAuthMessage;
  $out .= '</section>';
}

echo $out;

include("./foot.inc"); 

?>
