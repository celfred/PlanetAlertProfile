<?php namespace ProcessWire; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    date_default_timezone_set('Europe/Paris');

    if ($input->get->logout == 1) {
      $session->logout();
      $session->redirect('./');
    }
  ?>
  <title>
    <?php
      $allTeams = $pages->find("template=team")->implode(',', '{name}');
      if (isset($input->urlSegment1) && stripos($allTeams, $input->urlSegment1)) {
        echo $page->get("title").' '.strtoupper($input->urlSegment1);
      } else {
        echo $page->get("title");
      }
    ?>
  </title>

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo $page->summary; ?>" />
  <meta name="generator" content="ProcessWire <?php echo $config->version; ?>" />

  <link rel="icon" type="image/png" href="<?php echo $config->urls->templates?>img/favicon.png">

  <link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/css/bootstrap-theme.min.css" />

  <link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/main.css" />

  <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css"> -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.css">

  <!--[if IE]>
  <link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/ie.css" />
  <![endif]-->	
</head>

<body>
  <?php
    $out = '';
    // Links
    $newsboardPage = $pages->get("/newsboard");
    $shop = $pages->get("name=shop");
    $places = $pages->get("name=places");
    $peoples = $pages->get("name=people");
    $tasks = $pages->get("name=tasks");
    $monsters = $pages->get("name=monsters");
    $scoreboards = $pages->get("name=the-scoreboards");
    $hall = $pages->get("name=hall-of-fame");
    $book = $pages->get("name=book-knowledge");
    $blog = $pages->get("name=blog");
    $contact = $pages->get("name=contact");
    $documentation = $pages->get("name=documentation");
    $office = $pages->get("name=main-office");
    $donation = $pages->get("name=makedonation");
    $quiz = $pages->get("name=quiz");
    $adminActions = $pages->get('name=admin-actions');
    if (!$user->isSuperuser()) {
      $headTeacher = getHeadTeacher($user);
    }

    $out .= '<div id="wrap">'; // Closed in foot.inc
    $out .= '<div id="masthead" class="masthead">';

    if ($user->hasRole('teacher') || $user->isSuperuser()) { // Teacher or Superuser is logged
      $profilePage = $pages->get("parent.name=teachers, singleTeacher=$user");
      if ($user->isSuperuser()) { // All teams for Admin
        $allTeams = $pages->find("template=team, sort=title");
        $allPlayers = $pages->find("template=player, parent.name=players, team.name!=no-team")->sort("team->name, title");
      } else { // Restrict to logged in Teacher's teams
        $allTeams = $pages->find("template=team, teacher=$user, sort=title");
        $allPlayers = $pages->find("template=player, parent.name=players, team.name!=no-team, (teacher=$user), (team.teacher=$user)")->sort("team->name, title");
      }

      // Official period is checked only when viewing a team page
      if ($page->is("name=main-office|players|quiz|adminTable|world|donation")) {
        $team = $pages->get("parent.name=teams, name=$input->urlSegment1");
        $headTeacher = $team->teacher->first();
        $out .= periodNotification($team, $headTeacher);
      }
      
      $out .= '<ul>';
        $out .= '<div class="btn-group" role="group" aria-label="...">';
          $class = $page->name == 'home' ? "on" : "";
          $out .= "<a class='btn $class' href='{$homepage->url}'>{$homepage->title}</a>";
          $class = $page->name == 'newsboard' ? "on" : "";
          $out .= "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
          $out .= '<div class="btn-group" role="group">';
            $class = $page->name == 'players' || $page->name == "world" || $page->template == 'main-office' ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("The Teams").' <span class="caret"></span></button>';
            $out .= '<ul class="dropdown-menu">';
            foreach($allTeams as $team) {
              $class = $team->name == $input->urlSegment1 ? " class='selected-left'" : "";
              $teamPlayers = $allPlayers->find("team=$team");
              $out .= "<li class='dropdown-submenu'>";
              if ($user->isSuperuser()) {
                if ($team->teacher->first()) {
                  $out .= "<a$class href='{$office->url}{$team->name}'>{$team->title} [{$team->teacher->eq(0)->name}]</a>";
                } else {
                  $out .= "<a$class href='{$office->url}{$team->name}'>{$team->title} [-]</a>";
                }
              } else {
                $out .= "<a$class href='{$office->url}{$team->name}'>{$team->title}</a>";
              }
              $out .= "<ul class='dropdown-menu'>";
              $out .=  "<li><a href='{$office->url}{$team->name}'>{$team->title} {$office->title}</a>";
              $out .=  "<li class='dropdown-submenu'><a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}'>{$team->title} ".__("team list")."</a>";
              $out .= "<ul class='dropdown-menu'>";
              foreach ($teamPlayers as $p) {
                $out .= "<li><a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}/{$p->name}'>{$p->title}</a></li>";
              }
              $out .= "</ul>";
              $out .= "</li>";
              $out .= "<li><a href='{$homepage->url}shop/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('marketplace')."</a></li>";
              $out .= "<li><a href='{$homepage->url}adminTable/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('adminTable')."</a></li>";
              $out .= "<li><a href='{$homepage->url}quiz/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('monster invasions')."</a></li>";
              $out .= "<li><a href='{$homepage->url}world/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('team world')."</a></li>";
              $out .= "</ul>";
              $out .= "</li>";
            }
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<div class="btn-group">';
            $class = $page->is("name=blog|documentation|contact") ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
            $out .= '<ul class="dropdown-menu">';
              $out .= '<li class="dropdown-submenu">';
              $class = $page->is("name=shop|places|people|tasks|monsters|hall-of-fame") ? "selected-left" : "";
              $out .= '<a class="'.$class.'" href=""><span class="glyphicon glyphicon-globe"></span> - '.__("Discover Planet Alert World").'</a>';
              $out .= '<ul class="dropdown-menu">';
                $class = $page->name == 'shop' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$shop->url}'>{$shop->title}</a></li>";
                $class = $page->name == 'places' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$places->url}'>{$places->title}</a></li>";
                $class = $page->name == 'people' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$peoples->url}'>{$peoples->title}</a></li>";
                $class = $page->name == 'tasks' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$tasks->url}'>{$tasks->title}</a></li>";
                $class = $page->name == 'monsters' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$monsters->url}'>{$monsters->title}</a></li>";
                $class = $page->name == 'the-scoreboards' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$scoreboards->url}'>{$scoreboards->title}</a></li>";
                $class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$hall->url}'>{$hall->title}</a></li>";
                $class = $page->name == 'book-knowledge' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$book->url}'>{$book->title}</a></li>";
              $out .= '</ul>';
            $out .= '</li>';
            if (!$user->isSuperuser()) {
              $class = $page->name == 'contact' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$contact->url}'><span class='glyphicon glyphicon-envelope'></span> - ".__('Contact Admin')."</a></li>";
            }
            $class = $page->name == 'blog' ? " class='selected-left'" : "";
            $out .= "<li><a$class href='{$blog->url}'><span class='glyphicon glyphicon-hand-up'></span> - {$blog->title}</a></li>";
            $class = $page->name == 'documentation' ? " class='selected-left'" : "";
            $out .= "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - {$documentation->title}</a></li>";
          $out .= '</ul>';
          $out .= '</div>';
            // Teacher Zone (and Admin)
            $out .= '<div class="btn-group">';
            $class = $page->is("name=admin-actions") ? "on" : "";
            if ($user->isSuperuser()) {
              $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("Admin Zone").'<span class="caret"> </span></button>';
            } else {
              $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("Teacher Zone").'<span class="caret"> </span></button>';
            }
            $out .= '<ul class="dropdown-menu">';
              $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'team-options' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$adminActions->url}/team-options/'><span class='glyphicon glyphicon-cog'></span> - ".__('Team options')."</a></li>";
              $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'announcements' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$adminActions->url}announcements/'><span class='glyphicon glyphicon-comment'></span> - ".__('Announcements')."</a></li>";
              $out .= '<li class="dropdown-submenu">';
                $class = $page->is("name=admin-actions") && strpos($input->urlSegment1, 'manage') !== false ? "selected-left" : "";
                $out .= '<a class="'.$class.'" href=""><span class="glyphicon glyphicon-cog"></span> - '.__("Planet Alert Settings").'</a>';
                $out .= '<ul class="dropdown-menu">';
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'manage-periods' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/manage-periods/'><span class='glyphicon glyphicon-calendar'></span> - ".__('Manage periods')."</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'manage-actions' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/manage-actions/'><span class='glyphicon glyphicon-flash'></span> - ".__('Manage actions')."</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'manage-categories' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/manage-categories/'><span class='glyphicon glyphicon-tag'></span> - ".__('Manage categories and topics')."</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'manage-lessons' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/manage-lessons/'><span class='glyphicon glyphicon-file'></span> - ".__('Manage lessons')."</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'manage-monsters' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/manage-monsters/'><span class='glyphicon glyphicon-headphones'></span> - ".__('Manage monsters')."</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'manage-shop' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/manage-shop/'><span class='glyphicon glyphicon-shopping-cart'></span> - ".__('Manage marketplace')."</a></li>";
                // Check if teacher has selected the Memory potion
                $memoryPotion = $pages->get("name=memory-potion");
                if ($user->isSuperuser() || $memoryPotion->teacher->has($user)) {
                  $class = $input->urlSegment2 == 'memory-potion' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$pages->get("name=shop")->url}details/memory-potion'><span class='glyphicon glyphicon-duplicate'></span> - ".__('Manage Memory potion')."</a></li>";
                } else {
                  $out .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> <span class='strikeText'>".__('Manage Memory potion')."</span></li>";
                }
                $out .= '</ul>';
              $out .= '</li>';
              $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'reports' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$adminActions->url}reports/' target='_blank'><span class='glyphicon glyphicon-file'></span> - ".__('Reports')."</a></li>";
              $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'users' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$adminActions->url}users'><span class='glyphicon glyphicon-user'></span> - ".__('Users / History')."</a></li>";
              $class = $page->name == 'statistics' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$pages->get("name=statistics")->url}' target='_blank'><span class='glyphicon glyphicon-stats'></span> - ".__('Statistics')."</a></li>";
              $class = $page->name == 'sacoche' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$pages->get("name=sacoche")->url}' target='_blank'><span class='glyphicon glyphicon-stats'></span> - ".__('Import SACoche')."</a></li>";
              
              // Add Admin Zone
              if ($user->isSuperuser()) {
                $out .= "<li role='separator' class='divider'></li>";
                $out .= "<li role='separator' class='divider'></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'ut' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/ut/'>UT Hall of fame</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setScores' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/setScores/'>Set scores</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setReputation' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/setReputation/'>Set reputation</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setYearlyKarma' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/setYearlyKarma/'>Set yearly karmas</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setCaptains' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/setCaptains/'>Set captains</a></li>";
                $class = $page->name == 'admin-actions' && $input->urlSegment1 == 'setCache' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}setCache/'>Set cache</a></li>";
                $class = $page->name == 'script' && $input->urlSegment1 == 'ut' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$adminActions->url}/script/'>Script</a></li>";
              }
            $out .= '</ul>';
            $out .= '</div>';
          $out .= '<a class="btn" href="'.$loginUrl.'?logout=1">'.__("Log out").'</a>';
        $out .= '</div>';
      $out .= '</ul>';
    }

    if ($user->hasRole('player')) { // Player's menu
      $player = $pages->get("parent.name=players, template=player, login=$user->name");
      $user->language = $headTeacher->language; // Teacher chooses the language for his students
      $allTeams = $pages->find("template=team, teacher=$headTeacher, sort=title");
      if ($player->team->name != 'no-team') {
        $allPlayers = $pages->find("parent.name=players, template=player, team=$player->team");
      }
      $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-XP")->first();
      $bestProtection = $player->equipment->find("parent.name=protections, sort=-HP")->first();
      // Avatar
      $out .= '<div style=" color: #FFF;">';
      $out .= '<span class="avatarContainer">';
      if ($player->avatar) {
        $out .= '<img class="avatar superpose" src="'.$player->avatar->getCrop("thumbnail")->url.'" width="80" alt="Avatar" />';
      } else {
        $out .= '<Avatar>';
      }
      if ($bestWeapon && $bestWeapon->image) {
        $out .= '<img class="weapon mini superpose" src="'.$bestWeapon->image->getCrop("mini")->url.'" alt="'.$bestWeapon->title.'" />';
      }
      if ($bestProtection && $bestProtection->image) {
        $out .= '<img class="protection mini superpose" src="'.$bestProtection->image->getCrop("mini")->url.'" alt="'.$bestProtection->title.'" />';
      }
      $out .= '</span>';
      $out .= '</div>';

      $out .= '<ul>';
        $out .= '<div class="btn-group" role="group" aria-label="...">';
          $class = $page->name == 'newsboard' ? "on" : "";
          $out .= "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
          $class = ($player->team->name == $input->urlSegment1 && $input->urlSegment2 == $player->name ) ? "on" : "";
          $out .= "<a class='btn $class' href='{$pages->get('/players')->url}{$player->team->name}/{$player->name}'>".__("My Profile")."</a>";
          $out .= '<div class="btn-group">';
            $class = ($page->is("name=players|main-office|world") && $input->urlSegment2 == '') ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("My Team").'<span class="caret"> </span></button>';
            $out .= '<ul class="dropdown-menu">';
              $class = $page->name == 'main-office' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$office->url}{$player->team->name}'><span class='glyphicon glyphicon-eye-open'></span> - {$office->title}</a></li>";
              $class = ($page->name == 'players' && $input->urlSegment2 == '') ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$pages->get('/players')->url}{$player->team->name}'><span class='glyphicon glyphicon-th-list'></span> - ".__("List")." </a></li>";
              $class = $page->name == 'world' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$homepage->url}world/{$player->team->name}'><span class='glyphicon glyphicon-flag'></span> - ".__('team world')."</a></li>";
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<div class="btn-group">';
            $class = $page->is("name=shop_generator|makedonation") ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("My Actions").'<span class="caret"> </span></button>';
            $out .= '<ul class="dropdown-menu">';
              $class = $page->is("name=shop_generator") ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$pages->get('/shop_generator')->url}{$player->id}'><span class='glyphicon glyphicon-shopping-cart'></span> - ".__("Go to the Marketplace")."</a></li>";
              $class = $page->name == 'makedonation' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$donation->url}'><span class='glyphicon glyphicon-heart'></span> - ".__("Make a donation")."</a></li>";
              // Check if player can face Monster Invasions
              if ($player->places->count >= 3 || $player->people->count >= 3) {
                $class = $page->name == 'quiz' ? " class='selected-left'" : "";
                $out .= "<li><a$class href='{$pages->get('name=quiz')->url}'><span class='glyphicon glyphicon-star'></span> - ".__("Prepare my defense")."</a></li>";
              } else {
                $out .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                $out .= "<span class='strikeText'>".__("Prepare my defense")."</span></li>";
              }
              // Check if memory helmet accessible in player's group
              if ($player && ($player->team->forceHelmet == 1 || $player->equipment->get("name=memory-helmet"))) {
                if ($player->team->forceHelmet == 1) {
                  $class = $page->name == 'underground-training' ? " class='selected-left'" : "";
                  $out .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=underground-training')->url}'><span class='glyphicon glyphicon-headphones'></span> - ".__("Use the Memory helmet")."</a></li>";
                  $class = $page->name == 'fighting-zone' ? " class='selected-left'" : "";
                  $out .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=fighting-zone')->url}'><span class='glyphicon glyphicon-flash'></span> - ".__("Fight a monster")."</a></li>";
                } else {
                  $class = $page->name == 'underground-training' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$pages->get('name=underground-training')->url}'><span class='glyphicon glyphicon-headphones'></span> - ".__("Use the Memory helmet")."</a></li>";
                  $class = $page->name == 'fighting-zone' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$pages->get('name=fighting-zone')->url}'><span class='glyphicon glyphicon-flash'></span> - ".__("Fight a monster")."</a></li>";
                }
              } else {
                $out .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                $out .= "<span class='strikeText'>".__("Use the Memory helmet")."</span></li>";
                $out .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                $out .= "<span class='strikeText'>".__("Fight a monster")."</span></li>";
              }
              // Check if Visualizer is accessible in player's group
              if ($player && ($player->team->forceVisualizer == 1 || $player->equipment->get("name=electronic-visualizer"))) {
                $class = $page->name == 'visualizer' ? " class='selected-left'" : "";
                if ($player->team->forceVisualizer == 1) {
                  $out .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=visualizer')->url}'><span class='glyphicon glyphicon-th'></span> - ".__("Use the Electronic Visualizer")."</a></li>";
                } else {
                  $out .= "<li><a$class href='{$pages->get('name=visualizer')->url}'><span class='glyphicon glyphicon-th'></span> - ".__("Use the Electronic Visualizer")."</a></li>";
                }
              } else {
                $out .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                $out .= "<span class='strikeText'>".__("Use the Electronic Visualizer")."</span></li>";
              }
              // Check if Book of Knowledge is accessible in player's group
              if ($player && ($player->team->forceKnowledge == 1 || $player->equipment->get("name~=book-knowledge"))) {
                $class = $page->name == 'book-knowledge' ? " class='selected-left'" : "";
                if ($player->team->forceKnowledge == 1) {
                  $out .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a$class href='{$pages->get('name=book-knowledge')->url}'><span class='glyphicon glyphicon-book'></span> - ".__("Read the Book of Knowledge")."</a></li>";
                } else {
                  $out .= "<li><a$class href='{$pages->get('name=book-knowledge')->url}'><span class='glyphicon glyphicon-book'></span> - ".__("Read the Book of Knowledge")."</a></li>";
                }
              } else {
                $out .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                $out .= "<span class='strikeText'>".__("Read the Book of Knowledge")."</span></li>";
              }
              // Contact teacher
              $class = $page->name == 'contact' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$contact->url}'><span class='glyphicon glyphicon-envelope'></span> - ".__("Contact my teacher")."</a></li>";
              // Hall of Fame
              $class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$hall->url}'><span class='glyphicon glyphicon-eye-open'></span> - ".__("Visit the Hall of Fame")."</a></li>";
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<div class="btn-group">';
            $class = $page->is("name=blog|documentation") ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
            $out .= '<ul class="dropdown-menu">';
              $out .= '<li class="dropdown-submenu">';
                $class = $page->is("name=shop|places|people|tasks|monsters|hall-of-fame") ? "selected-left" : "";
                $out .= '<a class="'.$class.'" href="'.$places->url.'"><span class="glyphicon glyphicon-globe"></span> - '.__("Discover Planet Alert World").'</span></a>';
                $out .= '<ul class="dropdown-menu">';
                  $class = $page->name == 'shop' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$shop->url}'>{$shop->title}</a></li>";
                  $class = $page->name == 'places' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$places->url}'>{$places->title}</a></li>";
                  if ($player->team->rank && $player->team->rank->is('index>=8')) {
                    $class = $page->name == 'people' ? " class='selected-left'" : "";
                    $out .= "<li><a$class href='{$peoples->url}'>{$peoples->title}</a></li>";
                  }
                  $class = $page->name == 'tasks' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$tasks->url}'>{$tasks->title}</a></li>";
                  $class = $page->name == 'monsters' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$monsters->url}'>{$monsters->title}</a></li>";
                  $class = $page->name == 'the-scoreboards' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$scoreboards->url}'>{$scoreboards->title}</a></li>";
                  $class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$hall->url}'>{$hall->title}</a></li>";
                  $class = $page->name == 'book-knowledge' ? " class='selected-left'" : "";
                  $out .= "<li><a$class href='{$book->url}'>{$book->title}</a></li>";
                $out .= '</ul>';
              $out .= '</li>';
              $class = $page->name == 'blog' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$blog->url}'><span class='glyphicon glyphicon-hand-up'></span> - {$blog->title}</a></li>";
              $class = $page->name == 'documentation' ? " class='selected-left'" : "";
              $out .= "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - {$documentation->title}</a></li>";
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<a class="btn" href="'.$loginUrl.'?logout=1">'.__("Log out").'</a>';
        $out .= '</div>';
      $out .= '</ul>';
    }

    if (!$user->isLoggedin()) { // Guest menu
      $allTeams = $pages->find("template=team, sort=title");
      $allPlayers = $pages->find("parent.name=players, template=player");
      $out .= '<ul>';
        $out .= '<div class="btn-group" role="group" aria-label="...">';
          $class = $page->name == 'home' ? "on" : "";
          $out .= "<a class='btn $class' href='{$homepage->url}'>{$homepage->title}</a>";
          $class = $page->name == 'newsboard' ? "on" : "";
          $out .= "<a class='btn $class' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
          $out .= '<div class="btn-group" role="group">';
            $class = $page->name == 'players' || $page->name == "world" || $page->template == 'main-office' ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("The Teams").' <span class="caret"></span></button>';
            $out .= '<ul class="dropdown-menu">';
            foreach($allTeams as $team) {
              $class = $team->name == $input->urlSegment1 ? " class='selected-left'" : "";
              $out .= "<li class=''>";
              $out .= "<a$class href='{$homepage->url}players/{$sanitizer->pageName($team->name)}'>{$team->title}</a>";
              $out .= "</li>";
            }
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<div class="btn-group">';
            $class = $page->is("name=shop|places|people|tasks|monsters|hall-of-fame") ? "selected-left" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("The World").'<span class="caret"> </span></button>';
            $out .= '<ul class="dropdown-menu">';
              $class = $page->name == 'shop' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$shop->url}'>{$shop->title}</a></li>";
              $class = $page->name == 'places' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$places->url}'>{$places->title}</a></li>";
              $class = $page->name == 'people' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$peoples->url}'>{$peoples->title}</a></li>";
              $class = $page->name == 'tasks' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$tasks->url}'>{$tasks->title}</a></li>";
              $class = $page->name == 'monsters' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$monsters->url}'>{$monsters->title}</a></li>";
              $class = $page->name == 'the-scoreboards' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$scoreboards->url}'>{$scoreboards->title}</a></li>";
              $class = $page->name == 'hall-of-fame' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$hall->url}'>{$hall->title}</a></li>";
              $class = $page->name == 'book-knowledge' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$book->url}'>{$book->title}</a></li>";
            $out .= '</ul>';
          $out .= '</div>';
          $out .= '<div class="btn-group">';
            $class = $page->is("name=blog|documentation|contact") ? "on" : "";
            $out .= '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
            $out .= '<ul class="dropdown-menu">';
            $class = $page->name == 'contact' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$contact->url}'><span class='glyphicon glyphicon-envelope'></span> - {$contact->title}</a></li>";
              $class = $page->name == 'blog' ? " class='selected-left'" : "";
              $out .= "<li><a$class href='{$blog->url}'><span class='glyphicon glyphicon-hand-up'></span> - {$blog->title}</a></li>";
              $class = $page->name == 'documentation' ? " class='selected-left'" : "";
              $out .= "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - {$documentation->title}</a></li>";
          $out .= '</ul>';
          $out .= '</div>';
          $class = $page->is("name=loginform") ? "on" : "";
          $out .= '<a class="btn '.$class.'" href="'.$loginUrl.'">'.__("Log in").'</a>';
        $out .= '</div>';
      $out .= '</ul>';
    }
    $out .= '</div>'; // /masthead
    
    echo $out;
  ?>

  <div class="container-fluid">
    <div class="row">
      <div id="content" class="col-sm-12">
      <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) { // IE detected
          echo $ieAlert;
        }
        // Any announcements ?
        if ($user->hasRole('teacher') && $page->is("template!=exercise")) {
          $adminId = $users->get("name=admin")->id;
          $announcements = $pages->find("template=announcement, publish=1, parent=$allTeams, created_users_id=$adminId")->sort("-date");
          if ($announcements->count()>0) {
            foreach($announcements as $a) {
              echo '<div class="alert alert-info alert-dismissible announcement" role="alert" data-href="'.$pages->get("name=ajax-content")->url.'?id=unPublish&announcementId='.$a->id.'&playerId='.$user->id.'">';
              echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
              echo '<span class="label label-danger"><span class="glyphicon glyphicon-comment"></span> '.__("Admin's message").' : '.$a->title.'</span>';
              echo $a->body;
              echo '</div>';
            }
          }
        }
        if ($user->hasRole('player') && $page->is("template!=exercise")) {
          $adminId = $users->get("name=admin")->id;
          $announcements = $pages->find("template=announcement, publish=1, (parent=$player->team, selectPlayers=0, created_users_id!=$adminId), (parent=$player->team, selectPlayers=1, playersList=$player, created_users_id!=$adminId)")->sort("-date");
          if ($announcements->count()>0) {
            foreach($announcements as $a) {
              echo '<div class="alert alert-info alert-dismissible announcement" role="alert" data-href="'.$pages->get("name=ajax-content")->url.'?id=unPublish&announcementId='.$a->id.'&playerId='.$player->id.'">';
              echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
              echo '<span class="label label-danger"><span class="glyphicon glyphicon-comment"></span> '.__("Teacher's message").' : '.$a->title.'</span>';
              echo $a->body;
              if ($page->is("name!=contact")) {
                echo '<p class="text-right">';
                echo '<a href="'.$contact->url.'" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-envelope"></span> - '.__("Reply to my teacher").'</a>';
                echo '</p>';
              }
              echo '</div>';
            }
          }
        }
      ?>
