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
  echo '<a class="pdfLink btn btn-info" href="'. $page->url.'?pages2pdf=1">Get PDF [Catalogue]</a>';
  if ($user->isSuperuser() ) {
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
      </tr>
    </thead>
    <tbody>
      <?php foreach ($allEquipments as $item) {
        if ($item->image) {
          $mini = "<img data-toggle='tooltip' data-html='true' data-original-title='<img src=\"".$item->image->url."\" alt=\"avatar\" />' src='".$item->image->getThumb('mini')."' alt='avatar' />";
        } else {
          $mini = '';
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
      </tr>
      <?php } ?>
    </tbody>
  </table>
<?php } else { // A class is selected, display possible items
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
} ?>
</div>

<?php
  include("./foot.inc"); 
?>
