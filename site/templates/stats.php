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

  // Get logged players during the last 7 days
  $query = $database->prepare("SELECT username, login_was_successful, login_timestamp FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_timestamp BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)");   
  $query->execute();
  $last7DaysVisitors = $query->fetchAll();
  $last7DaysSuccessful = [];
  $last7DaysFailed = [];
  $last7DaysUnique = [];
  $todayUnique = [];
  $yesterdayUnique = [];
  foreach($last7DaysVisitors as $v) {
    $todayStartTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    $yesterdayStartTime = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
    $yesterdayEndTime = mktime(23, 59, 59, date('m'), date('d')-1, date('Y'));
    $loggedTime = strtotime($v['login_timestamp']);
    $successful = $v['login_was_successful'];
    if ($loggedTime >= $todayStartTime && $successful == 1) {
      if (!in_array($v['username'], $todayUnique)) {
        array_push($todayUnique, $v['username']);
      }
    }
    if ($loggedTime >= $yesterdayStartTime && $loggedTime<= $yesterdayEndTime && $successful == 1) {
      if (!in_array($v['username'], $yesterdayUnique)) {
        array_push($yesterdayUnique, $v['username']);
      }
    }
    if ($successful == 1) {
      if (!in_array($v['username'], $last7DaysSuccessful)) {
        array_push($last7DaysSuccessful, $v['username']);
      }
    } else {
      if (!in_array($v['username'], $last7DaysFailed)) {
        array_push($last7DaysFailed, $v['username']);
      }
    }
  }
  // Get really failed logins (remove failed attempts that eventually succeeded)
  $reallyFailed = array_diff($last7DaysFailed, $last7DaysSuccessful);
  // Get total # of unique logged players during the current school year
  $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1 AND login_timestamp > FROM_UNIXTIME(".$period->dateStart.")");   
  $query->execute();
  $totalNbUniqueVisitors = $query->fetchColumn();
  if ($user->isSuperuser()) {
    // Get total # of unique logged players since the very beginning
    $query = $database->prepare("SELECT count(DISTINCT username) FROM process_login_history WHERE username IN ('".$allPlayersLogins."') AND username NOT IN ('".$excluded."') AND login_was_successful=1");   
    $query->execute();
    $grandTotalNbUniqueVisitors = $query->fetchColumn();
  }

  $out = '<section class="news panel panel-primary">';
  $out .= '<div class="panel-heading">';
    $out .=   '<h2 class="panel-title">';
    $out .= __("Planet Alert Statistics");
    if ($user->hasRole('teacher')) {
      $out .= ' ['.sprintf(__('Limited to %s user'), $user->name).']';
    }
    $out .= '</h2>';
  $out .= '</div>';
  $out .= '<div class="panel-body">';
    $out .= '<p class="lead">';
    $out .= '&nbsp;&nbsp;&nbsp';
    $out .= '<span class="label label-success">'.__("Today").' : '.count($todayUnique).'</span>';
    $out .= '&nbsp;&nbsp;&nbsp';
    $out .= '<span class="label label-success">'.__("Yesterday").' : '.count($yesterdayUnique).'</span>';
    $out .= '&nbsp;&nbsp;&nbsp';
    $out .= '<span class="label label-success">'.__("Last 7 days").' : '.count($last7DaysSuccessful).'</span>';
    $out .= '&nbsp;&nbsp;&nbsp';
    $out .= '<span class="label label-success">'.__("School Year").' : '.$totalNbUniqueVisitors.'</span>';
    if ($user->isSuperuser()) {
      $out .= '&nbsp;&nbsp;&nbsp';
      $out .= '<span class="label label-success">'.__("All times").' : '.$grandTotalNbUniqueVisitors.'</span>';
    }
    $out .= '</p>';
    if (count($todayUnique) > 0 ) {
      $out .= '<ul class="list-inline list-unstyled">';
      $out .= '<span class="label label-primary">'.__("Today's players").' : </span>';
      foreach($todayUnique as $login) {
        $player = $allPlayers->get("template=player, login=$login");
        if ($player) {
          $out .= '<li>';
          $out .= '<a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']';
          $out .= '</li>';
        }
      }
      $out .= '</ul>';
    }
    if (count($yesterdayUnique) > 0 ) {
      $out .= '<span class="label label-primary">'.__("Yesterday's players").' : </span>';
      $out .= '<ul class="list-inline list-unstyled">';
      foreach($yesterdayUnique as $login) {
        $player = $allPlayers->get("template=player, login=$login");
        $out .= '<li>';
        $out .= '<a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']';
        $out .= '</li>';
      }
      $out .= '</ul>';
    }

    // Failed logins
    if (count($reallyFailed) > 0) {
      $out .= '<span class="label label-danger">'.__("Last 7 days failed logins").'</span>';
      $out .= '<ul class="list-inline list-unstyled">';
      foreach($reallyFailed as $login) {
        $player = $allPlayers->get("template=player, login=$login");
        $out .= '<li>';
        $out .= '<a href="'.$player->url.'">'.$player->title.'</a> ['.$player->team->title.']';
        $out .= '</li>';
      }
      $out .= '</ul>';
    }

    // Training sessions stats
    $out .= '<h3><span class="label label-success">'.__("Training sessions with the Memory helmet").'</span></h3>';
    $totalTrainingSessions = $pages->findMany("has_parent=$allPlayers, template=event, task.name~=ut-action");
    $out .= '<ul>';
    // Today's training sessions
    $today = new DateTime("today");
    $limitDate = strtotime($today->format('Y-m-d'));
    $todayTrainingSessions = $pages->findMany("has_parent=$allPlayers, template=event, task.name~=ut-action, date>=$limitDate");
    $todayTrainedPlayers = [];
    foreach($todayTrainingSessions as $t) {
      $pl = $t->parent("template=player");
      if (!In_array($pl->id, $todayTrainedPlayers)) {
        array_push($todayTrainedPlayers, $pl->id);
      }
    }
    $out .= '<li><h4>'.__("Today").' : ';
    $out .= '<span class="label label-default">'.sprintf(__('%1$s sessions with %2$s different player·s'), $todayTrainingSessions->count(), count($todayTrainedPlayers));
    $out .= '</span></h4></li>';
    // 30 days training sessions
    $interval = new \DateInterval("P30D");
    $oldDate = $today->sub($interval);
    $limitDate = strtotime($oldDate->format('Y-m-d'));
    $thirtyDaysTrainingSessions = $pages->findMany("has_parent=$allPlayers, template=event, task.name~=ut-action, date>=$limitDate");
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
    $out .= '<li><h4>'.__("Last 30 days").' : ';
    $out .= '<span class="label label-default">'.sprintf(__('%1$s sessions with %2$s different player·s'), $thirtyDaysTrainingSessions->count(), count($thirtyDaysTrainedPlayers));
    $out .= '</span>';
    if ($thirtyDaysTrainingSessions->count() > 0) {
      $out .= ' ['.$totalUt.__('UT').' = ';
      $out .= ($totalUt*10).__('words').', ';
      $out .= round(($totalUt/$thirtyDaysTrainingSessions->count())*10).' '.__('words/session').']';
    }
    $out .= '</h4></li>';
    // Total training sessions
    $totalTrainingSessions = $pages->find("has_parent=$allPlayers, template=event, task.name~=ut-action");
    $totalUt = 0;
    $trainedPlayers = $allPlayers->find("underground_training>0");
    foreach ($allPlayers as $p) {
      $totalUt += $p->underground_training;
    }
    $out .= '<li><h4>'.__("All times").' : ';
    $out .= '<span class="label label-default">'.sprintf(__('%1$s sessions with %2$s different player·s'), $totalTrainingSessions->count(), $trainedPlayers->count());
    $out .= '</span>';
    if ($totalTrainingSessions->count() > 0) {
      $out .= ' ['.$totalUt.__('UT').' = ';
      $out .= ($totalUt*10).__('words').', ';
      $out .= round(($totalUt/$totalTrainingSessions->count())*10).' '.__('words/session').']';
    }
    $out .= '</h4></li>';
    $out .= '</ul>';
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
