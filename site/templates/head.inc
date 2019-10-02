<?php namespace ProcessWire; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    date_default_timezone_set('Europe/Paris');
    $testPlayer = $pages->get("name=test, template=player, parent.name=players");
    if ($input->get->logout == 1) {
      $session->logout();
      $session->redirect('./');
    }

    // All teams
    if ($user->isSuperuser() || $user->name == 'flieutaud') { // Test team is included for debug
      $allTeams = $pages->find("template=team")->sort("title");
    } else {
      $allTeams = $pages->find("template=team, name!=test-team")->sort("title");
    }
    $allTeamsNames = $allTeams->implode(',', '{name}');
  ?>
  <title>
    <?php
      if (isset($input->urlSegment1) && stripos($allTeamsNames, $input->urlSegment1) === false) {
        if ($page->name == 'home') {
          echo 'Planet Alert';
        } else {
          echo $page->get("title");
        }
        $selectedTeam = false;
      } else {
        echo $page->get("title").' '.strtoupper($input->urlSegment1);
        $selectedTeam = $allTeams->get("name=$input->urlSegment1");
      }
    ?>
  </title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo $page->summary; ?>" />
  <meta name="generator" content="ProcessWire <?php echo $config->version; ?>" />

  <link rel="icon" type="image/png" href="<?php echo $config->urls->templates?>img/favicon.png">

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  <link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>styles/main.css" />

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
    $fightingZone = $pages->get('name=fighting-zone');
    $trainingZone = $pages->get('name=underground-training');
    $playground = $pages->get("name=fighters-playground");
    $book = $pages->get("name=book-knowledge");
    $blog = $pages->get("name=blog");
    $contact = $pages->get("name=contact");
    $office = $pages->get("name=main-office");
    $donation = $pages->get("name=makedonation");
    $quiz = $pages->get("name=quiz");
    if ($user->hasRole('teacher') || $user->isSuperuser()) {
      $adminActions = $pages->get('name=admin-actions');
    }
    if (!$user->isSuperuser()) {
      $headTeacher = getHeadTeacher($user);
    } else {
      $headTeacher = $user;
    }

    $out .= '<div id="wrap">'; // Closed in foot.inc
    $out .= '<div id="masthead" class="masthead">';

    if ($user->hasRole('teacher') || $user->isSuperuser()) { // Teacher or Superuser is logged
      // Official period is checked only when viewing a team page
      if ($page->is("name=main-office|players|quiz|adminTable|world|donation")) {
        $headTeacher = $selectedTeam->teacher->first();
        $out .= periodNotification($selectedTeam, $headTeacher);
      }
      
      if ($session->allPlayers && $session->allTeams) {
        $allPlayers = $pages->find("id=$session->allPlayers");
        $allTeams = $pages->find("id=$session->allTeams");
      } else {
        if (!$user->isSuperuser()) { // Limit to logged in Teacher's teams
          $allTeams->filter("teacher=$user");
          $allPlayers = $pages->find("parent.name=players, template=player, team.name!=no-team|test-team, team.teacher=$user")->sort("team->name, title"); // Required for quick access to player in menu
        } else {
          $allPlayers = $pages->find("parent.name=players, template=player, team.name!=no-team")->sort("team->name, title"); // Required for quick access to player in menu
        }
        $session->allPlayers = (string) $allPlayers;
        $session->allTeams = (string) $allTeams;
      }

      if ($session->headMenu) { // Head menu already session cached
        $out .= $session->headMenu;
      } else { // Build head menu
        $headMenu = '<ul>';
          $headMenu .= '<div class="btn-group" role="group" aria-label="...">';
            $headMenu .= "<a class='btn' href='{$homepage->url}'>{$homepage->title}</a>";
            $headMenu .= "<a class='btn' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
            $headMenu .= '<div class="btn-group" role="group">';
              $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("The Teams").' <span class="caret"></span></button>';
              $headMenu .= '<ul class="dropdown-menu">';
              foreach($allTeams as $team) {
                $teamPlayers = $allPlayers->find("team=$team");
                $headMenu .= "<li class='dropdown-submenu'>";
                if ($user->isSuperuser()) {
                  if ($team->teacher->first()) {
                    $headMenu .= "<a href='{$office->url}{$team->name}'>{$team->title} [{$team->teacher->eq(0)->name}]</a>";
                  } else {
                    $headMenu .= "<a href='{$office->url}{$team->name}'>{$team->title} [-]</a>";
                  }
                } else {
                  if ($team->name != 'test-team') {
                    $headMenu .= "<a href='{$office->url}{$team->name}'>{$team->title}</a>";
                  }
                }
                  $headMenu .= "<ul class='dropdown-menu'>";
                $headMenu .=  "<li><a href='{$office->url}{$team->name}'>{$team->title} {$office->title}</a>";
                if ($team->name != 'no-team') {
                  $headMenu .=  "<li class='dropdown-submenu'>";
                  $headMenu .=  "<a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}'>{$team->title} ".__("team list")."</a>";
                  $headMenu .= "<ul class='dropdown-menu'>";
                  foreach ($teamPlayers as $p) {
                    $headMenu .= "<li><a href='{$p->url}'>{$p->title}</a></li>";
                  }
                  $headMenu .= "</ul>";
                  $headMenu .= "</li>";
                } else {
                  $headMenu .= "<li>";
                  $headMenu .=  "<a href='{$homepage->url}players/{$sanitizer->pageName($team->name)}'>{$team->title} ".__("team list")."</a>";
                  $headMenu .= "</li>";
                }
                $headMenu .= "<li><a href='{$homepage->url}shop/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('marketplace')."</a></li>";
                $headMenu .= "<li><a href='{$homepage->url}adminTable/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('adminTable')."</a></li>";
                $headMenu .= "<li><a href='{$homepage->url}quiz/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('monster invasions')."</a></li>";
                $headMenu .= "<li><a href='{$homepage->url}world/{$sanitizer->pageName($team->name)}'>{$team->title} ".__('team world')."</a></li>";
                $headMenu .= "</ul>";
                $headMenu .= "</li>";
              }
              $headMenu .= '</ul>';
            $headMenu .= '</div>';
            $headMenu .= '<div class="btn-group">';
              $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
              $headMenu .= '<ul class="dropdown-menu">';
                $headMenu .= '<li class="dropdown-submenu">';
                $headMenu .= '<a href=""><span class="glyphicon glyphicon-globe"></span> - '.__("Discover Planet Alert World").'</a>';
                $headMenu .= '<ul class="dropdown-menu">';
                  $headMenu .= "<li><a href='{$shop->url}'>{$shop->title}</a></li>";
                  $headMenu .= "<li><a href='{$places->url}'>{$places->title}</a></li>";
                  $headMenu .= "<li><a href='{$peoples->url}'>{$peoples->title}</a></li>";
                  $headMenu .= "<li><a href='{$tasks->url}'>{$tasks->title}</a></li>";
                  $headMenu .= "<li><a href='{$monsters->url}'>{$monsters->title}</a></li>";
                  $headMenu .= "<li><a href='{$scoreboards->url}'>{$scoreboards->title}</a></li>";
                  $headMenu .= "<li><a href='{$hall->url}'>{$hall->title}</a></li>";
                  $headMenu .= "<li><a href='{$book->url}'>{$book->title}</a></li>";
                $headMenu .= '</ul>';
              $headMenu .= '</li>';
              if (!$user->isSuperuser()) {
                $headMenu .= "<li><a href='{$contact->url}'><span class='glyphicon glyphicon-envelope'></span> - ".__('Contact Admin')."</a></li>";
              }
              $headMenu .= "<li><a href='{$blog->url}'><span class='glyphicon glyphicon-hand-up'></span> - {$blog->title}</a></li>";
              $headMenu .= "<li><a target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - ".__('Documentation')."</a></li>";
            $headMenu .= '</ul>';
            $headMenu .= '</div>';
              // Teacher Zone (and Admin)
              $headMenu .= '<div class="btn-group">';
              if ($user->isSuperuser()) {
                $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("Admin Zone").'<span class="caret"> </span></button>';
              } else {
                $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("Teacher Zone").'<span class="caret"> </span></button>';
              }
              $headMenu .= '<ul class="dropdown-menu">';
                $headMenu .= '<li class="dropdown-submenu">';
                  $headMenu .= "<a href='{$adminActions->url}team-options/'><span class='glyphicon glyphicon-cog'></span> - ".__('Team options')."</a>";
                  $headMenu .= '<ul class="dropdown-menu">';
                    foreach($allTeams as $team) {
                      $headMenu .= "<li>";
                      $headMenu .= "<a href='{$adminActions->url}team-options/{$team->id}?direct=true&type=team'>{$team->title}</a>";
                      $headMenu .= "</li>";
                    }
                  $headMenu .= '</ul>';
                $headMenu .= '</li>';
                $headMenu .= "<li><a href='{$adminActions->url}announcements/'><span class='glyphicon glyphicon-comment'></span> - ".__('Announcements')."</a></li>";
                $headMenu .= '<li class="dropdown-submenu">';
                  $headMenu .= '<a href=""><span class="glyphicon glyphicon-cog"></span> - '.__("Planet Alert Settings").'</a>';
                  $headMenu .= '<ul class="dropdown-menu">';
                    $headMenu .= "<li><a href='{$adminActions->url}manage-periods/'><span class='glyphicon glyphicon-calendar'></span> - ".__('Manage periods')."</a></li>";
                    $headMenu .= "<li><a href='{$adminActions->url}manage-groups/'><span class='glyphicon glyphicon-user'></span> - ".__('Manage group names')."</a></li>";
                    $headMenu .= "<li><a href='{$adminActions->url}manage-actions/'><span class='glyphicon glyphicon-flash'></span> - ".__('Manage actions')."</a></li>";
                    $headMenu .= "<li><a href='{$adminActions->url}manage-categories/'><span class='glyphicon glyphicon-tag'></span> - ".__('Manage categories and topics')."</a></li>";
                    $headMenu .= "<li><a href='{$adminActions->url}manage-lessons/'><span class='glyphicon glyphicon-file'></span> - ".__('Manage lessons')."</a></li>";
                    $headMenu .= "<li><a href='{$adminActions->url}manage-monsters/'><span class='glyphicon glyphicon-headphones'></span> - ".__('Manage monsters')."</a></li>";
                    $headMenu .= "<li><a href='{$adminActions->url}manage-shop/'><span class='glyphicon glyphicon-shopping-cart'></span> - ".__('Manage marketplace')."</a></li>";
                    // Check if teacher has selected the Memory potion
                    $memoryPotion = $pages->get("name=memory-potion");
                    if ($user->isSuperuser() || $memoryPotion->teacher->has($user)) {
                      $headMenu .= "<li><a href='{$pages->get("name=shop")->url}details/memory-potion'><span class='glyphicon glyphicon-duplicate'></span> - ".__('Manage Memory potion')."</a></li>";
                    } else {
                      $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> <span class='strikeText'>".__('Manage Memory potion')."</span></li>";
                    }
                  $headMenu .= '</ul>';
                $headMenu .= '</li>';
                $headMenu .= "<li><a href='{$adminActions->url}reports/' target='_blank'><span class='glyphicon glyphicon-file'></span> - ".__('Reports')."</a></li>";
                $headMenu .= "<li><a href='{$adminActions->url}users'><span class='glyphicon glyphicon-user'></span> - ".__('Users / History')."</a></li>";
                $headMenu .= "<li><a href='{$pages->get("name=statistics")->url}' target='_blank'><span class='glyphicon glyphicon-stats'></span> - ".__('Statistics')."</a></li>";
                $headMenu .= "<li><a href='{$pages->get("name=sacoche")->url}' target='_blank'><span class='glyphicon glyphicon-flash'></span> - ".__('Import SACoche')."</a></li>";
                $headMenu .= "<li><a href='{$adminActions->url}checkSkills'><span class='glyphicon glyphicon-check'></span> - ".__('Check Skills')."</a></li>";
                
                // Add Admin Zone
                if ($user->isSuperuser()) {
                  $headMenu .= "<li role='separator' class='divider'></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}ut/'>UT Hall of fame</a></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}setScores/'>Set scores</a></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}setReputation/'>Set reputation</a></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}setYearlyKarma/'>Set yearly karmas</a></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}checkSkills/'>Check skills</a></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}setCache/'>Set cache</a></li>";
                  $headMenu .= "<li><a href='{$adminActions->url}script/'>Script</a></li>";
                }
              $headMenu .= '</ul>';
              $headMenu .= '</div>';
            $headMenu .= '<a class="btn" href="'.$loginUrl.'?logout=1">'.__("Log out").'</a>';
          $headMenu .= '</div>';
        $headMenu .= '</ul>';
        $session->set("headMenu", $headMenu);
        $out .= $headMenu;
      }
    }

    // headMenu session cache for logged-in player (no use for guests since pages use template cache)
    if ($user->hasRole('player')) { // Player's menu
      $player = $pages->get("parent.name=players, template=player, login=$user->name");
      $user->language = $headTeacher->language; // teacher chooses the language for his students
      $allTeams = $allTeams->find("teacher=$headTeacher")->sort("title"); // limit to headteacher's teams
      $bestWeapon = $player->equipment->find("parent.name=weapons, sort=-xp")->first();
      $bestProtection = $player->equipment->find("parent.name=protections, sort=-hp")->first();
      if ($session->headMenu) { // Head menu already session cached
        $out .= $session->headMenu;
      } else { // Build head menu
        $headMenu = '';
        // Avatar
        $headMenu .= '<div style=" color: #FFF;">';
        $headMenu .= '<span class="avatarContainer" data-loggedId="'.$player->id.'">';
          if ($player->avatar) {
            $headMenu .= '<img class="avatar superpose" src="'.$player->avatar->getCrop("thumbnail")->url.'" width="80" alt="'.$player->title.'." />';
          } else {
            $headMenu .= '<Avatar>';
          }
          if ($bestWeapon && $bestWeapon->image) {
            $headMenu .= '<img class="weapon mini superpose" src="'.$bestWeapon->image->getCrop("mini")->url.'" alt="'.$bestWeapon->title.'." />';
          }
          if ($bestProtection && $bestProtection->image) {
            $headMenu .= '<img class="protection mini superpose" src="'.$bestProtection->image->getCrop("mini")->url.'" alt="'.$bestProtection->title.'." />';
          }
        $headMenu .= '</span>';
        $headMenu .= '</div>';

        $headMenu .= '<ul>';
          $headMenu .= '<div class="btn-group" role="group" aria-label="...">';
            $headMenu .= "<a class='btn' href='{$newsboardPage->url}'>{$newsboardPage->title}</a>";
            $headMenu .= "<a class='btn' href='{$player->url}'>".__("My Profile")."</a>";
            $headMenu .= '<div class="btn-group">';
              $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("My Team").'<span class="caret"> </span></button>';
              $headMenu .= '<ul class="dropdown-menu">';
                $headMenu .= "<li><a href='{$office->url}{$player->team->name}'><span class='glyphicon glyphicon-eye-open'></span> - {$office->title}</a></li>";
                $headMenu .= "<li><a href='{$pages->get('/players')->url}{$player->team->name}'><span class='glyphicon glyphicon-th-list'></span> - ".__("List")." </a></li>";
                $headMenu .= "<li><a href='{$homepage->url}world/{$player->team->name}'><span class='glyphicon glyphicon-flag'></span> - ".__('team world')."</a></li>";
              $headMenu .= '</ul>';
            $headMenu .= '</div>';
            $headMenu .= '<div class="btn-group">';
              $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("My Actions").'<span class="caret"> </span></button>';
              $headMenu .= '<ul class="dropdown-menu">';
                $headMenu .= "<li><a href='{$pages->get('/shop_generator')->url}{$player->id}'><span class='glyphicon glyphicon-shopping-cart'></span> - ".__("Go to the Marketplace")."</a></li>";
                $headMenu .= "<li><a href='{$donation->url}'><span class='glyphicon glyphicon-heart'></span> - ".__("Make a donation")."</a></li>";
                // Check if player can face Monster Invasions
                if ($player->places->count >= 3 || $player->people->count >= 3) {
                  $headMenu .= "<li><a href='{$pages->get('name=quiz')->url}'><span class='glyphicon glyphicon-star'></span> - ".__("Prepare my defense")."</a></li>";
                } else {
                  $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                  $headMenu .= "<span class='strikeText'>".__("Prepare my defense")."</span></li>";
                }
                // Check if memory helmet accessible in player's group
                if ($player && ($player->team->forceHelmet == 1 || $player->equipment->get("name=memory-helmet"))) {
                  if ($player->team->forceHelmet == 1) {
                    $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a href='{$pages->get('name=underground-training')->url}'><span class='glyphicon glyphicon-headphones'></span> - ".__("Use the Memory helmet")."</a></li>";
                    if ($player->team->lockFights == 0) {
                      $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a href='{$pages->get('name=fighting-zone')->url}'><span class='glyphicon glyphicon-flash'></span> - ".__("Fight a monster")."</a></li>";
                    } else {
                      $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                      $headMenu .= "<span class='strikeText'>".__("Fight a monster")."</span></li>";
                    }
                  } else {
                    $headMenu .= "<li><a href='{$pages->get('name=underground-training')->url}'><span class='glyphicon glyphicon-headphones'></span> - ".__("Use the Memory helmet")."</a></li>";
                    $headMenu .= "<li><a href='{$pages->get('name=fighting-zone')->url}'><span class='glyphicon glyphicon-flash'></span> - ".__("Fight a monster")."</a></li>";
                  }
                } else {
                  $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                  $headMenu .= "<span class='strikeText'>".__("Use the Memory helmet")."</span></li>";
                  $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                  $headMenu .= "<span class='strikeText'>".__("Fight a monster")."</span></li>";
                }
                // Fighters playground
                if ($player && ($player->skills->has("name=fighter"))) {
                  $headMenu .= "<li><a href='{$playground->url}{$player->name}'><span class='glyphicon glyphicon-time'></span> - ".__("Go to the Fighters Playground")."</a></li>";
                } else {
                  $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                  $headMenu .= "<span class='strikeText'>".__("Go to the Fighters Playground")."</span></li>";
                }
                // Check if Book of Knowledge is accessible in player's group
                if ($player && ($player->team->forceKnowledge == 1 || $player->equipment->get("name~=book-knowledge"))) {
                  if ($player->team->forceKnowledge == 1) {
                    $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>Special</span> <a href='{$pages->get('name=book-knowledge')->url}'><span class='glyphicon glyphicon-book'></span> - ".__("Read the Book of Knowledge")."</a></li>";
                  } else {
                    $headMenu .= "<li><a href='{$pages->get('name=book-knowledge')->url}'><span class='glyphicon glyphicon-book'></span> - ".__("Read the Book of Knowledge")."</a></li>";
                  }
                } else {
                  $headMenu .= "<li>&nbsp; <span class='badge badge-danger'>".__("Locked")."</span> ";
                  $headMenu .= "<span class='strikeText'>".__("Read the Book of Knowledge")."</span></li>";
                }
                // Contact teacher
                $headMenu .= "<li><a href='{$contact->url}'><span class='glyphicon glyphicon-envelope'></span> - ".__("Contact my teacher")."</a></li>";
                // Hall of Fame
                $headMenu .= "<li><a href='{$hall->url}'><span class='glyphicon glyphicon-eye-open'></span> - ".__("Visit the Hall of Fame")."</a></li>";
                if ($player->team->name != 'cm1') {
                  $headMenu .= '<li class="dropdown-submenu">';
                    $headMenu .= '<a href="#"><span class="glyphicon glyphicon-education"></span> - '.__("Go to school").'</span></a>';
                    $headMenu .= '<ul class="dropdown-menu">';
                      if ($player->team->name != 'no-team') {
                        $headMenu .= "<li><a href='https://flenglish.tuxfamily.org/articles/cahier-classe-".$player->team->name."' target='_blank'>See my online copybook</a></li>";
                      }
                      $headMenu .= "<li><a href='https://sacoche.sesamath.net/sacoche/index.php' target='_blank'>See SACoche</a></li>";
                      $headMenu .= "<li><a href='https://clg-pasteur-raon.monbureaunumerique.fr/' target='_blank'>Go to the 'ENT'</a></li>";
                    $headMenu .= '</ul>';
                  $headMenu .= '</li>';
                }
              $headMenu .= '</ul>';
            $headMenu .= '</div>';
            $headMenu .= '<div class="btn-group">';
              $headMenu .= '<button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Infos<span class="caret"> </span></button>';
              $headMenu .= '<ul class="dropdown-menu">';
                $headMenu .= '<li class="dropdown-submenu">';
                  $headMenu .= '<a href="'.$places->url.'"><span class="glyphicon glyphicon-globe"></span> - '.__("Discover Planet Alert World").'</span></a>';
                  $headMenu .= '<ul class="dropdown-menu">';
                    $headMenu .= "<li><a href='{$shop->url}'>{$shop->title}</a></li>";
                    $headMenu .= "<li><a href='{$places->url}'>{$places->title}</a></li>";
                    if ($player->team->rank && $player->team->rank->is('index>=8')) {
                      $headMenu .= "<li><a href='{$peoples->url}'>{$peoples->title}</a></li>";
                    }
                    $headMenu .= "<li><a href='{$tasks->url}'>{$tasks->title}</a></li>";
                    $headMenu .= "<li><a href='{$monsters->url}'>{$monsters->title}</a></li>";
                    $headMenu .= "<li><a href='{$scoreboards->url}'>{$scoreboards->title}</a></li>";
                    $headMenu .= "<li><a href='{$hall->url}'>{$hall->title}</a></li>";
                    $headMenu .= "<li><a href='{$book->url}'>{$book->title}</a></li>";
                  $headMenu .= '</ul>';
                $headMenu .= '</li>';
                $headMenu .= "<li><a href='{$blog->url}'><span class='glyphicon glyphicon-hand-up'></span> - {$blog->title}</a></li>";
                $headMenu .= "<li><a target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - ".__('Documentation')."</a></li>";
              $headMenu .= '</ul>';
            $headMenu .= '</div>';
            $headMenu .= '<a class="btn" href="'.$loginUrl.'?logout=1">'.__("Log out").'</a>';
          $headMenu .= '</div>';
        $headMenu .= '</ul>';
        $session->set("headMenu", $headMenu);
        $out .= $headMenu;
      }
    }

    if (!$user->isLoggedin()) { // Guest menu
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
              $out .= "<li><a$class target='_blank' href='https://celfred.gitbooks.io/planet-alert-documentation/content/fr/'><span class='glyphicon glyphicon-question-sign'></span> - ".__('Documentation')."</a></li>";
          $out .= '</ul>';
          $out .= '</div>';
          $class = $page->is("name=loginform") ? "on" : "";
          $out .= '<a class="btn '.$class.'" href="'.$loginUrl.'">'.__("Log in").'</a>';
        $out .= '</div>';
      $out .= '</ul>';
    }
    $out .= '</div>'; // /masthead
    
    echo $out;
    
    // Menu is ready, clean memory
    $pages->unCacheAll();
  ?>

  <div class="container-fluid">
    <div class="row">
      <div id="content" class="col-sm-12">
      <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) { // IE detected
          echo $ieAlert;
        }
        // Any announcements ?
        if ($user->hasRole('teacher') && $page->is("template!=exercise|speedQuiz")) {
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
        if ($user->hasRole('player') && $page->is("template!=exercise|speedQuiz")) {
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
