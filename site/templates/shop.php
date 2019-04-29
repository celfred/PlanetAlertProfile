<?php namespace ProcessWire;

  include("./head.inc"); 

  if (!isset($headTeacher)) { $headTeacher = $pages->get("name=admin"); }

  if (isset($player)) { // Show player's mini-profile
    echo '<div class="row well text-center">';
      echo miniProfile($player, 'equipment');
    echo '</div>';
  }

  if ($input->urlSegment1 == '') { // Complete catalogue
    if ($user->isSuperuser() || $user->hasRole('teacher')) {
      echo '<div class="row">';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'?pages2pdf=1">'.__("Get PDF [Catalogue]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/weapons?pages2pdf=1">'.__("Get PDF [Weapons]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/protections?pages2pdf=1">'.__("Get PDF [Protections]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/items?pages2pdf=1">'.__("Get PDF [Potions]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/specialItems?pages2pdf=1">'.__("Get PDF [Special Potions]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/group-items?pages2pdf=1">'.__("Get PDF [Group items]").'</a>';
      echo '</div>';
    }
    $out = $cache->get('cache__shop-'.$headTeacher->name, 2678400, function($user, $pages, $config) use($headTeacher) {
      $out = '';
      if ($user->isLoggedin()) { // Limit to teacher's world
        if ($user->isSuperuser()) {
          $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort=title");
        } else {
          $allEquipments = $pages->get("/shop/")->find("(template=equipment) , (template=item, teacher=$headTeacher), sort='title'");
        }
      } else {
        $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort=title");
      }
      $allCategories = new PageArray();
      foreach ($allEquipments as $equipment) {
        $allCategories->add($equipment->category);
        $allCategories->sort("title");
      }
      $out .= '<div id="Filters" class="text-center" data-fcolindex="7">';
      $out .= '<ul class="list-inline well">';
        foreach ($allCategories as $category) {
          $out .= '<li><label for="'.$category->name.'" class="btn btn-primary btn-xs">'.$category->title.' <input type="checkbox" value="'.$category->title.'" class="categoryFilter" name="categoryFilter" id="'.$category->name.'"></label></li>';
        }
      $out .= '</ul>';
      $out .= '</div>';
      $out .= '<table id="mainShop" class="table table-hover table-condensed">';
      $out .= '<thead>';
      $out .= '<tr>';
      $out .= '<th>'.__("Item").'</th>';
      $out .= '<th></th>';
      $out .= '<th><span class="glyphicon glyphicon-signal"></span> '.__("Min level").'</th>';
      $out .= '<th><img src="'.$config->urls->templates.'img/globe.png" alt="globe." /> '.__("Min # of Free Acts").'</th>';
      $out .= '<th><img src="'.$config->urls->templates.'img/heart.png" alt="heart." /> '.__("HP").'</th>';
      $out .= '<th><img src="'.$config->urls->templates.'img/star.png" alt="star." /> '.__("XP").'</th>';
      $out .= '<th><img src="'.$config->urls->templates.'img/gold_mini.png" alt="gold coins." /> '.__("GC").'</th>';
      $out .= '<th>'.__("Category").'</th>';
      $out .= '</tr>';
      $out .= '</thead>';
      $out .= '<tbody>';
      foreach ($allEquipments as $item) {
        $out .= '<tr>';
          if ($item->image) {
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$item->image->getCrop('thumbnail')->url."\" alt=\"".$item->title.".\" />' src='".$item->image->getCrop('mini')->url."' alt='".$item->title.".' />";
          } else {
            $mini = '';
          }
          $out .= '<td data-order="'.$item->title.'" data-toggle="tooltip" title="'.nl2br($item->summary).'">';
            $out .= '<a data-toggle="tooltip" data-html="true" title="'.nl2br($item->summary).'" href="'.$item->url.'">'.$item->title.'</a>';
          $out .= '</td>';
          $out .= '<td>';
            $out .= $mini;
          $out .= '</td>';
          $out .= '<td>'.$item->level.'</td>';
          $out .= '<td>'.$item->freeActs.'</td>';
          $out .= '<td>'.$item->HP.'</td>';
          $out .= '<td>'.$item->XP.'</td>';
          $out .= '<td>'.$item->GC.'</td>';
          $out .= '<td>'.$item->category->title.'</td>';
        $out .= '</tr>';
      }
      $out .= '</tbody>';
      $out .= '</table>';
      return $out;
    });
    if ($user->hasRole('player')) {
      $out .= '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">'.__("Go to my marketplace").'</a>';
    }
  } else { // A class is selected
    $out = '';
    if ($user->hasRole('teacher') || $user->isSuperuser() ) {
      // Nav tabs
      $team = $allTeams->get("name=$input->urlSegment1");
      include("./tabList.inc"); 
      $out = '';
      $playerId = $input->get("playerId");
      $allPlayers = $pages->findMany("parent.name=players, template=player, team=$team, sort=title");
      // Select form
      $out .= '<select class="" id="shopSelect" name="shopSelect">';
        $out .= '<option value="">'.__('Select a player').'</option>';
        foreach ($allPlayers as $player) {
          // Build selectEquipment
          if ($player->id == $playerId) { $selected = 'selected="selected"'; bd($selected);} else { $selected = ''; }
          $out .= '<option value="'.$pages->get('/shop_generator')->url.$player->id.'" '.$selected.'>'.$player->title.' ['.$player->GC.__('GC').']</option>';
        }
      $out .= '</select>';

      // Display possible equipment/places for selected player
      if (!$playerId) {
        $out .= '<section id="possibleItems">';
        $out .= '</section>';
      } else {
        $out .= '<section id="possibleItems" class="ajaxContent" data-href="'.$pages->get('/shop_generator')->url.$playerId.'">';
        $out .= '<p class="text-center"><img src="'.$config->urls->templates.'img/hourglass.gif"></p>';
        $out .= '</section>';
      }
    } else {
      $session->redirect($shop->url);
    }
  }

  echo $out;

  include("./foot.inc");
?>
