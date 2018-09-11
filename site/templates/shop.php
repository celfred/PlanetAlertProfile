<?php namespace ProcessWire;
/* Shop template */

include("./head.inc"); 

$globalEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
if ($user->isLoggedin()) { // Limit to teacher's world
  if ($user->isSuperuser()) {
    $allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
  } else {
    $allEquipments = $pages->get("/shop/")->find("(template=equipment) , (template=item, teacher=$headTeacher), sort='title'");
  }
} else {
  $allEquipments = $globalEquipments;
}
$allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
$allCategories = new PageArray();
foreach ($allEquipments as $equipment) {
  $allCategories->add($equipment->category);
  $allCategories->sort("title");
}

?>

<div>
<?php

$out = '';
if ($input->urlSegment1 == '') { // Complete Shop if no classes is selected
  if ($page->name == 'shop') { // All shop catalogue
    if ($user->hasRole('player')) { // Show player's mini-profile
      echo '<div class="row well text-center">';
        echo miniProfile($player, 'equipment');
      echo '</div>';
    }

    if ($user->isSuperuser() || $user->hasRole('teacher')) {
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'?pages2pdf=1">'.__("Get PDF [Catalogue]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/weapons?pages2pdf=1">'.__("Get PDF [Weapons]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/protections?pages2pdf=1">'.__("Get PDF [Protections]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/items?pages2pdf=1">'.__("Get PDF [Potions]").'</a>';
      echo '<a class="pdfLink btn btn-sm btn-info" href="'. $page->url.'pictures/group-items?pages2pdf=1">'.__("Get PDF [Group items]").'</a>';
      echo '<br /><br />';
      echo '<p class="text-center">'.sprintf(__("Your Planet Alert marketplace contains %d items."), $allEquipments->count());
      echo ' ('.sprintf(__("Out of %d possible items"), $globalEquipments->count()).')</p>';
    }
    ?>
   
    <div id="Filters" class="text-center" data-fcolindex="7">
      <ul class="list-inline well">
        <?php foreach ($allCategories as $category) { ?>
          <li><label for="<?php echo $category->name; ?>" class="btn btn-primary btn-xs"><?php echo $category->title; ?> <input type="checkbox" value="<?php echo $category->title; ?>" class="categoryFilter" name="categoryFilter" id="<?php echo $category->name; ?>"></label></li>
        <?php } ?> 
      </ul>
    </div>
    <table id="mainShop" class="table table-hover table-condensed">
      <thead>
        <tr>
        <th><?php echo __("Item"); ?></th>
          <th></th>
          <th><span class="glyphicon glyphicon-signal"></span> <?php echo __("Min level"); ?></th>
          <th><img src="<?php  echo $config->urls->templates?>img/globe.png" alt="" /> <?php echo __("Min # of Free Acts"); ?></th>
          <th><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> <?php echo __("HP"); ?></th>
          <th><img src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> <?php echo __("XP"); ?></th>
          <th><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="" /> <?php echo __("GC"); ?></th>
          <th><?php echo __("Category"); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allEquipments as $item) {
          if ($item->image) {
            $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$item->image->url."\" alt=\"avatar\" />' src='".$item->image->getCrop('mini')->url."' alt='avatar' />";
          } else {
            $mini = '';
          }
        ?>
        <tr>
          <td data-order="<?php echo $item->title; ?>" data-toggle="tooltip" title="<?php echo nl2br($item->summary); ?>">
            <a data-toggle="tooltip" data-html="true" title="<?php echo nl2br($item->summary); ?>" href="<?php echo $page->url.'details/'.$item->name; ?>"><?php echo $item->title; ?></a>
          </td>
          <td>
            <?php echo $mini; ?>
          </td>
          <td><?php echo $item->level; ?></td>
          <td><?php echo $item->freeActs ? $item->freeActs : '0'; ?></td>
          <td><?php echo $item->HP; ?></td>
          <td><?php echo $item->XP; ?></td>
          <td><?php echo $item->GC; ?></td>
          <td><?php echo $item->category->title; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php 
      if ($user->hasRole('player')) {
        echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">'.__("Go to the marketplace").'</a>';
      }
    ?>
<?php 
  }
} else { 
    if ($input->urlSegment1 == 'details') { // Equipment detail
      $item = $pages->get("name=$input->urlSegment2");
      if ($user->hasRole('player')) {
        $out .= '<div class="well text-center">';
        $out .= miniProfile($player, 'equipment');
        $item = possibleElement($player, $item);
        switch($item->pb) {
          case 'possible' : 
            $out .= '<p class="lead">';
            $out .= __("You can buy this item.").' ⇒ ';
            $out .=  '<a class="btn btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">'.__("Go to the marketplace").'</a>';
            $out .= '</p>';
            break;
          case 'helmet' : 
            $out .= '<p class="lead">'.__("You must buy the Memory helmet first before buying this item.").'</p>';
            break;
          case 'already' : 
            $out .= "<p class='lead'>".__("You already have this item.")."</p>";
            break;
          case 'freeActs' : 
            $nbEl = $player->places->count()+$player->people->count();
            $out .= "<p class='lead'>".sprintf(__("This item requires %1$s free elements ! You have only %2$s free elements."), $item->freeActs, $nbEl)."</p>";
            break;
          case 'GC' : 
            $out .= "<p class='lead'>".sprintf(__("This item requires %1$s GC ! You have only %2$sGC."), $item->GC, $player->GC)."</p>";
            break;
          case 'level' : 
            $out .= "<p class='lead'>".sprintf(__("This item requires a level %1$s ! You are only at level %2$s."), $item->level, $player->level)."</p>";
            break;
          case 'maxToday' :
            $out .= "<p class='text-center alert alert-warning'>".__("You have reached the 3 items limit for today ! Come back tomorrow  to go to the Marketplace !")."</p>";
            break;
          default: 
            $out .= "<p class='lead'>".__("You can't buy this item for the moment. Sorry.")."</p>";
        }
        $out .= '</div>';
      }
      $out .= '<div class="well">';
      $out .= '<span class="badge badge-default">'.$item->category->title.'</span>';
      $out .= '<br />';
      $out .= '<br />';
      if ($item->image) {
        $out .= '<img class="img-thumbnail" src="'.$item->image->getCrop("big")->url.'" alt="Image" />&nbsp;&nbsp;';
      }
      $out .= '<h2 class="inline"><strong>'.$item->title.'</strong>';
      $out .= '</h2>';
      $out .= '<h4>';
      $out .= '<span class="label label-primary"><span class="glyphicon glyphicon-signal"></span> '.$item->level.'</span>';
      $out .= '&nbsp;&nbsp;';
      $out .= '<span class="label label-default"><img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" /> '.$item->GC.__('GC').'</span>';
      $out .= '&nbsp;&nbsp;';
      if ($item->HP !== 0) {
        if ($item->HP > 0) { $sign = '+'; } else { $sign = ''; }
        $out .= '<span class="label label-primary"><img src="'.$config->urls->templates.'img/heart.png" alt="HP" /> '.$sign.$item->HP.'HP</span>';
        $out .= '&nbsp;&nbsp;';
      }
      if ($item->XP !== 0) {
        if ($item->XP > 0) { $sign = '+'; } else { $sign = ''; }
        $out .= '<span class="label label-primary"><img src="'.$config->urls->templates.'img/star.png" alt="XP" /> '.$sign.$item->XP.'XP</span>';
      }
      $out .= '</h4>';
      $out .= '<h2 class="">'.nl2br($item->summary);
      $out .= '</h2>';
      if ($user->language->name == 'default') {
        $item->of(false);
        if ($item->summary->getLanguageValue($french) != '') {
          $out .= '<a class="" data-toggle="collapse" href="#collapseDiv" aria-expanded="false" aria-controls="collapseDiv">'.__("[French version]").'</a>';
          $out .= '<div class="collapse" id="collapseDiv">';
          $out .= '<div class="well">';
            $out .= nl2br($item->summary->getLanguageValue($french));
          $out .= '</div>';
          $out .= '</div>';
        }
      }
      if ($item->is("name=memory-potion")) {
        if ($user->isLoggedin()) {
          $out .= '<hr />';
          $out .= '<h4>'.__("Available texts").' :</h4>';
          $out .= '<ol class="list">';
          if ($user->isSuperuser()) {
            $allTexts = $pages->find("template=memory-text, include=all, sort=index");
          } else if ($user->hasRole('teacher')){
            $allTexts = $profilePage->memoryPotionTexts;
          } else if ($user->hasRole('player')) {
            $allTexts = $pages->get("parent.name=teachers, singleTeacher=$headTeacher")->memoryPotionTexts;
          } else {
            $allTexts = '';
          }
          if ($user->isSuperuser() || $user->hasRole('teacher')) { // Show all texts for admin
            if (count($allTexts) > 0) {
              foreach ($allTexts as $t) {
                $out .= '<li>';
                $out .= '<h4>';
                if ($user->isSuperuser() || $t->created_users_id == $user->id || $user->name == 'flieutaud') {
                  $out .= $t->feel(array(
                    'fields' => 'title,summary,task'
                  ));
                }
                $out .= ' <span>'.$t->title.'</span>';
                $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.nl2br($t->summary).'"></span>';
                $out .= ' ('.strlen($t->summary). ' caracters) ';
                if ($t->task) {
                  $out .= ' <span class="badge badge-default">'.$t->task->title.'</span>';
                }
                $out .= ' <a class="btn btn-info" href="'. $page->url.'/memory-potion/'.$t->id.'/?pages2pdf=1">'.__("[Get PDF]").'</a>';
                $out .= '</h4>';
                $out .= '</li>';
              }
            }
            if ($user->hasRole('teacher')) {
              $out .= $profilePage->feel(array(
                'text' => __("[Edit list]"),
                'fields'=>'memoryPotionTexts'
              ));
            }
            $out .= $pages->get("name=memory-potion-texts")->feel(array(
              'mode' => 'page-add',
              'text' => __("[Add a new text]"),
              'fields'=>'title,summary,task'
            ));
          } else { // Show possible texts for logged-in player
            $playerBoughtTexts = $player->find("template=event, refPage=$item, task.name=buy");
            if ($playerBoughtTexts->count() > 0) {
              foreach ($playerBoughtTexts as $bt) {
                if (count($allTexts) > 0) {
                  $t = $allTexts->eq($bt->linkedId);
                  $result = $player->find("template=event, linkedId=$t->id")->last();
                  $out .= '<li>';
                  $out .= '<h4>';
                  $out .= ' <span>'.$t->title.'</span>';
                  $out .= ' <span class="glyphicon glyphicon-eye-open" data-toggle="tooltip" data-html="true" title="'.nl2br($t->summary).'"></span>';
                  if ($result->id) {
                    if ($result->task->HP < 0) {
                      $out .= ' → <i class="glyphicon glyphicon-thumbs-down"></i>';
                    } else {
                      $out .= ' → <i class="glyphicon glyphicon-thumbs-up"></i>';
                    }
                  } else {
                    $out .= ' → <span>'.__("You have about 2 weeks to learn this text and tell your teacher when you are ready !").'</span>';
                    $out .= ' <span class="label label-danger">'.__('Bought on').' '.date('D, M. j', $t->created).'</span>';
                  }
                  $out .= '</h4>';
                  $out .= '</li>';
                } else {
                  $out .= '<li>';
                  $out .= __('Error ! Please contact the administrator.');
                  $out .= '</li>';
                }
              }
            } else {
              $out .= '<p>'.__("You haven't bought any Memory potions yet.").'</p>';
            }
          }
          $out .= "</ol>";
        }
      }
      $out .= '</div>';
      $out .= '<a class="btn btn-block btn-primary" href="'.$pages->get('name=shop')->url.'">'.__("Back to the Shop.").'</a>';
      echo $out;
    } else {
      if ($input->urlSegment2 == '') { // A class is selected, display possible items
        if ($user->hasRole('teacher') || $user->isSuperuser() ) {
          // Nav tabs
          $team = $pages->get("template=team, name=$input->urlSegment1");;
          include("./tabList.inc"); 

          $out = '';
          $team = $pages->find("name=$input->urlSegment1");
          $allPlayers = $pages->findMany("template=player, team=$team, sort=title");
          // Select form
          $out .= '<select class="" id="shopSelect" name="shopSelect">';
            $out .= '<option value="">'.__('Select a player').'</option>';
            foreach ($allPlayers as $player) {
              // Build selectEquipment
              $out .= '<option value="'.$pages->get('/shop_generator')->url.$player->id.'">'.$player->title.' ['.$player->GC.__('GC').']</option>';
            }
          $out .= '</select>';

          // Display possible equipment/places for selected player
          $out .= '<section id="possibleItems">';
          $out .= '</section>';

          echo $out;
        }
      } else { // An item is being bought
        if ($user->isLoggedin() && ($user->isSuperuser() == false)) {
          $item = $pages->get($input->urlSegment2);
          echo '<div class="row text-center">';
          echo "<h3>Buy ".$item->title." <img src='".$item->image->url."' alt='No image' /> for ".$item->GC." gold coins ?</h3>";
          echo "<h3>".sprintf(__("You will have %d GC left."), ($player->GC-$item->GC))." <span class='glyphicon glyphicon-piggy-bank'></h3>";
          echo '</div>';

          echo '<form id="buyForm" name="buyForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
          echo '<input type="hidden" name="player" value="'.$player->id.'" />';
          echo '<input type="hidden" name="item" value="'.$item->id.'" />';
          echo '<div class="row well text-center">';
          echo '<a href="'.$page->url.'" class="btn btn-danger">No</a>&nbsp;&nbsp;&nbsp;';
          echo '<input type="submit" id="buyFormSubmit" name="buyFormSubmit" value="Yes" class="btn btn-primary" />';
          echo '</div>';
          echo '</form>';
        }
      }
    }
} ?>
</div>

<?php
  include("./foot.inc"); 
?>
