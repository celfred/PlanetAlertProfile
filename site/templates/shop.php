<?php 
/* Shop template */

include("./head.inc"); 

$allEquipments = $pages->get("/shop/")->find("template=equipment|item, sort='title'");
$allPlaces = $pages->get("/places/")->find("template='place', sort='title'");
$allCategories = new PageArray();
foreach ($allEquipments as $equipment) {
  $allCategories->add($equipment->category);
  $allCategories->sort("title");
}

?>

<div>
<?php

if ($input->urlSegment1 == '') { // Complete Shop if no classes is selected
 
  // Test if a player is connected
  if ($user->isLoggedin()) {
    if ($user->isSuperuser() == false ) { // Not admin
      echo '<div class="row well">';
      echo '<h4>';
      echo '<span class="lead label label-default"><span class="glyphicon glyphicon-signal"></span>'.$player->level.'&nbsp;&nbsp;<img src="'.$config->urls->templates.'img/gold_mini.png" alt="GC" />'.$player->GC.'</span>';
      echo '</h4>';
      echo '<img class="" src="'.$player->avatar->url.'" alt="No avatar" />';
      if ($player->equipment->count > 0) {
        $playerEquipment = [];
        foreach ($player->equipment as $equipment) {
          array_push($playerEquipment, $equipment->id);
          $mini = "<img class='img-thumbnail' data-toggle='tooltip' data-html='true' data-original-title='".$equipment->title."' src='".$equipment->image->url."' alt='avatar' />";
          echo $mini;
        }
      } else {
        echo '<span class="label label-info">No equipment.</span>';
      }
      echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>';
      echo '</div>';
    }
  }

  if ($user->isSuperuser() ) {
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF [Catalogue]</a>';
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'pictures/weapons?pages2pdf=1">Get PDF [Weapons]</a>';
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'pictures/protections?pages2pdf=1">Get PDF [Protections]</a>';
    echo '<a class="pdfLink btn btn-info" href="'. $page->url.'pictures/items?pages2pdf=1">Get PDF [Potions]</a>';
    echo '<br /><br /><br />';
  }
  ?>
 
  <div id="Filters" class="text-center" data-fcolindex="6">
    <ul class="list-inline well">
      <?php foreach ($allCategories as $category) { ?>
        <li><label for="<?php echo $category->name; ?>" class="btn btn-primary btn-xs"><?php echo $category->title; ?> <input type="checkbox" value="<?php echo $category->title; ?>" class="categoryFilter" name="categoryFilter" id="<?php echo $category->name; ?>"></label></li>
      <?php } ?> 
    </ul>
  </div>
  <table id="mainShop" class="table table-hover table-condensed">
    <thead>
      <tr>
        <th>Item</th>
        <th></th>
        <th><span class="glyphicon glyphicon-signal"></span> Minimum level</th>
        <th><img src="<?php  echo $config->urls->templates?>img/heart.png" alt="" /> HP</th>
        <th><img src="<?php  echo $config->urls->templates?>img/star.png" alt="" /> XP</th>
        <th><img src="<?php  echo $config->urls->templates?>img/gold_mini.png" alt="" /> GC</th>
        <th>Category</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($allEquipments as $item) {
        if ($item->image) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$item->image->url."\" alt=\"avatar\" />' src='".$item->image->getThumb('mini')."' alt='avatar' />";
        } else {
          $mini = '';
        }
        // Check item's availability for logged-in player
        if ( in_array($item->id, $playerEquipment) ) {
          if ($item->category->name !== 'potions') {
            $item->stat = 2;
          } else {
            if ($item->level <= $player->level && $item->GC <= $player->GC ) {
              $item->stat = 1;
            } else {
              $item->stat = 0;
            }
          }
        } else {
          if ($item->level <= $player->level && $item->GC <= $player->GC ) {
            $item->stat = 1;
          } else {
            $item->stat = 0;
          }
        }
      ?>
      <tr>
        <td data-order="<?php echo $item->title; ?>" data-toggle="tooltip" title="<?php echo $item->summary; ?>">
          <?php echo $item->title; ?>
        </td>
        <td>
          <?php echo $mini; ?>
        </td>
        <td><?php echo $item->level; ?></td>
        <td><?php echo $item->HP; ?></td>
        <td><?php echo $item->XP; ?></td>
        <td><?php echo $item->GC; ?></td>
        <td><?php echo $item->category->title; ?></td>
        <td><?php
        switch ($item->stat) {
        case 0 : echo '<span>Out of reach</span>'; break;
        case 1 : echo '<a class="buyButton label label-primary" href="'.$page->url.$player->team->name.'/'.$item->id.'">Buy</a>'; break;
        case 2 : echo '<span class="label label-success">Owned</span>'; break;
        default : echo 'Out of reach';
        } 
        ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo '<a class="btn btn-block btn-primary" href="'.$pages->get('/shop_generator')->url.$player->id.'">Go to the marketplace</a>'; ?>
<?php } else { 
  if ($input->urlSegment2 == '') { // A class is selected, display possible items
    if ($user->isSuperuser() ) {
      // Nav tabs
      include("./tabList.inc"); 

      $out = '';
      $team = $input->urlSegment1;
      $allPlayers = $pages->find("template='player', team=$team, sort='title'");
      // Select form
      $out .= '<select class="" id="shopSelect" name="shopSelect">';
        $out .= '<option value="">Select a player</option>';
        foreach ($allPlayers as $player) {
          // Build selectEquipment
          $out .= '<option value="'.$pages->get('/shop_generator')->url.$player->id.'">'.$player->title.' ['.$player->GC.'GC]</option>';
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
      echo "<h3> You will have ".($player->GC-$item->GC)." GC left. <span class='glyphicon glyphicon-piggy-bank'></h3>";
      echo '</div>';

      echo '<form id="buyForm" name="buyForm" action="'.$pages->get("name=submitforms")->url.'" method="post" class="" role="form">';
      echo '<input type="hidden" name="player" value="'.$player->id.'" />';
      echo '<input type="hidden" name="team" value="'.$player->team.'" />';
      echo '<input type="hidden" name="item" value="'.$item->id.'" />';
      echo '<div class="row well text-center">';
      echo '<a href="'.$page->url.'" class="btn btn-danger">No, I cancel the deal.</a>&nbsp;&nbsp;&nbsp;';
      //echo '<a href="'.$page->url.'" class="btn btn-info">Yes, I want to buy this item!</a>';
      echo '<input type="submit" name="buyFormSubmit" value="Yes, I want to buy this item !" class="btn btn-primary" />';
      echo '</div>';
      echo '</form>';
    }
  }
} ?>
</div>

<?php
  include("./foot.inc"); 
?>
